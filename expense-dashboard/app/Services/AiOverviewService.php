<?php

namespace App\Services;

use App\Models\IncomeExpectation;
use App\Models\SavingsGoal;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generates a short natural-language overview of a user's spending for a
 * budget-cycle period via the Anthropic Messages API (Claude Haiku 4.5).
 *
 * Only ever sends the aggregate figures already computed by
 * `App\Support\DashboardAggregates` (category totals, total spent, expected
 * income, savings goal/progress, period label) - never raw expense notes or
 * line items, since those never appear in `$aggregates` to begin with.
 */
class AiOverviewService
{
    private const MODEL = 'claude-haiku-4-5-20251001';

    private const MAX_TOKENS = 200;

    private const TIMEOUT_SECONDS = 5;

    /**
     * @param  array{
     *     periodLabel: string,
     *     totalSpent: string|float,
     *     categoryTotals: Collection<string, string>,
     *     incomeExpectation: ?IncomeExpectation,
     *     savingsGoal: ?SavingsGoal,
     *     savingsProgress: ?int,
     * }  $aggregates
     *
     * @throws ConnectionException If the Anthropic API is unreachable.
     * @throws RequestException If the Anthropic API responds with a 4xx/5xx status.
     */
    public function summarize(User $user, array $aggregates): string
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
            ])
                ->timeout(self::TIMEOUT_SECONDS)
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => self::MODEL,
                    'max_tokens' => self::MAX_TOKENS,
                    'messages' => [
                        ['role' => 'user', 'content' => $this->buildPrompt($user, $aggregates)],
                    ],
                ])
                ->throw()
                ->json();
        } catch (ConnectionException|RequestException $e) {
            Log::error('Failed to generate AI spending overview: '.$e->getMessage());

            throw $e;
        }

        return $this->stripMarkdownArtifacts(trim($response['content'][0]['text'] ?? ''));
    }

    /**
     * Removes common Markdown artifacts (heading `#`s, `**`/`__` bold,
     * `*`/`_` italics, `` ` `` inline code) from the model's response.
     *
     * The prompt already asks for plain text, but that's an instruction,
     * not a guarantee - the model doesn't always fully comply, and this
     * text is rendered as-is in the dashboard view, so it's stripped
     * defensively here rather than trusted.
     */
    private function stripMarkdownArtifacts(string $text): string
    {
        // Heading markers ("# ", "## ", ... up to "###### ") at the start of a line.
        $text = preg_replace('/^#{1,6}\s+/m', '', $text);

        // Bold/italic emphasis - unwrap to the inner text rather than
        // leaving the marker characters in place. Order matters: ** / __
        // (bold) before * / _ (italic), so a bold span's outer markers
        // aren't first mistaken for two italic markers.
        $text = preg_replace('/\*\*(.+?)\*\*/s', '$1', $text);
        $text = preg_replace('/__(.+?)__/s', '$1', $text);
        $text = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '$1', $text);
        $text = preg_replace('/(?<!_)_(?!_)(.+?)(?<!_)_(?!_)/s', '$1', $text);

        // Inline code spans.
        $text = preg_replace('/`([^`]+)`/', '$1', $text);

        return trim($text);
    }

    /**
     * Builds the prompt from aggregate figures only - see the class docblock
     * for why raw expense data never reaches here.
     */
    private function buildPrompt(User $user, array $aggregates): string
    {
        $currency = $user->currency;

        $categoryLines = $aggregates['categoryTotals']->isEmpty()
            ? '(none logged this period)'
            : $aggregates['categoryTotals']
                ->map(fn ($amount, $category) => "- {$category}: ".Money::format($amount, $currency))
                ->implode("\n");

        $income = $aggregates['incomeExpectation']
            ? Money::format($aggregates['incomeExpectation']->expected_amount, $currency)
            : 'not set';

        $goal = $aggregates['savingsGoal']
            ? Money::format($aggregates['savingsGoal']->target_amount, $currency)
            : 'not set';

        $progress = $aggregates['savingsProgress'] !== null
            ? $aggregates['savingsProgress'].'%'
            : 'n/a';

        $totalSpent = Money::format($aggregates['totalSpent'], $currency);

        return <<<PROMPT
            You are writing a short spending overview for a personal finance app. Using ONLY the figures below, write one encouraging but honest paragraph (about 150-200 words) summarizing this person's spending for {$aggregates['periodLabel']}. Mention the total spent, the biggest spending category, and how they're tracking against their savings goal if one is set. Do not invent any transactions, merchants, or details beyond what's given below. Respond in plain text only - no Markdown formatting (no headings, no "**" bold or "_" italics, no backticks, no bullet points).

            Total spent: {$totalSpent}
            Spending by category:
            {$categoryLines}
            Expected income: {$income}
            Savings goal: {$goal}
            Progress toward savings goal: {$progress}
            PROMPT;
    }
}
