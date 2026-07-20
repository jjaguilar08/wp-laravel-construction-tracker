<?php

namespace App\Http\Controllers;

use App\Services\AiOverviewService;
use App\Support\DashboardAggregates;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Generates (or regenerates) the AI spending overview for the authenticated
 * user's current budget-cycle period (`POST /dashboard/ai-overview`, route
 * name `ai-overview.store`).
 *
 * A hard ceiling of `MAX_ATTEMPTS_PER_DAY` explicit clicks per user per day
 * is enforced via `RateLimiter`, independent of the `period_summaries`
 * cache row - the cache only stops `DashboardController` from re-hitting
 * the API on every page load, it does nothing to stop repeated explicit
 * regenerate clicks, which is what the rate limit is for.
 */
class AiOverviewController extends Controller
{
    /** Max explicit generate/regenerate clicks allowed per user per day. */
    private const MAX_ATTEMPTS_PER_DAY = 5;

    private const DECAY_SECONDS = 86400;

    /**
     * If unreachable or erroring, the Anthropic API's failure is caught here
     * and turned into a friendly flash message - never a raw exception to
     * the user - same pattern `ConstructionLogService`/`DashboardController`
     * used for the old WordPress integration.
     */
    public function store(Request $request, AiOverviewService $service): RedirectResponse
    {
        $user = $request->user();
        $throttleKey = 'ai-overview:'.$user->id;

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS_PER_DAY)) {
            return redirect()->route('dashboard')
                ->with('aiOverviewError', "You've reached today's limit for generating AI overviews - please try again tomorrow.");
        }

        RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

        $aggregates = DashboardAggregates::forUser($user);

        try {
            $summary = $service->summarize($user, $aggregates);
        } catch (ConnectionException|RequestException) {
            return redirect()->route('dashboard')
                ->with('aiOverviewError', "Couldn't generate an AI overview right now. Please try again later.");
        }

        // A plain updateOrCreate() search-array match won't find the
        // existing row here: Eloquent/SQLite persists the `date`-cast
        // `period_start` column as a full datetime string (e.g.
        // "2026-07-01 00:00:00"), which a bare "2026-07-01" comparison
        // never matches - the same gotcha IncomeExpectationController's
        // uniqueness check works around with whereDate().
        $periodSummary = $user->periodSummaries()
            ->whereDate('period_start', $aggregates['periodStart']->toDateString())
            ->first();

        if ($periodSummary) {
            $periodSummary->update(['summary' => $summary]);
        } else {
            $user->periodSummaries()->create([
                'period_start' => $aggregates['periodStart']->toDateString(),
                'summary' => $summary,
            ]);
        }

        return redirect()->route('dashboard');
    }
}
