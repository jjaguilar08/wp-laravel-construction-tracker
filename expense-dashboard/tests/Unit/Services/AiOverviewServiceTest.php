<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AiOverviewService;
use App\Support\DashboardAggregates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiOverviewServiceTest extends TestCase
{
    use RefreshDatabase;

    private function fakeAnthropicText(string $text): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['type' => 'text', 'text' => $text]],
            ], 200),
        ]);
    }

    public function test_strips_a_leading_markdown_heading(): void
    {
        $this->fakeAnthropicText("# July 2026 Spending Overview\n\nYou spent a modest amount this period.");

        $user = User::factory()->create();

        $summary = (new AiOverviewService)->summarize($user, DashboardAggregates::forUser($user));

        $this->assertStringNotContainsString('#', $summary);
        $this->assertStringStartsWith('July 2026 Spending Overview', $summary);
    }

    public function test_strips_bold_and_italic_markers(): void
    {
        $this->fakeAnthropicText('You spent **a lot** on food and _stayed_ under budget.');

        $user = User::factory()->create();

        $summary = (new AiOverviewService)->summarize($user, DashboardAggregates::forUser($user));

        $this->assertSame('You spent a lot on food and stayed under budget.', $summary);
    }

    public function test_strips_inline_code_backticks(): void
    {
        $this->fakeAnthropicText('Your `food` category was the largest.');

        $user = User::factory()->create();

        $summary = (new AiOverviewService)->summarize($user, DashboardAggregates::forUser($user));

        $this->assertSame('Your food category was the largest.', $summary);
    }

    public function test_leaves_plain_text_untouched(): void
    {
        $this->fakeAnthropicText('You spent $350 this period, mostly on food.');

        $user = User::factory()->create();

        $summary = (new AiOverviewService)->summarize($user, DashboardAggregates::forUser($user));

        $this->assertSame('You spent $350 this period, mostly on food.', $summary);
    }
}
