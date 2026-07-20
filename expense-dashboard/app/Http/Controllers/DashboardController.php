<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Support\DashboardAggregates;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Renders the authenticated user's finance overview for their current
 * budget-cycle period (`GET /dashboard`, route name `dashboard`) - a
 * calendar month for a user with the default `cycle_start_day` of 1,
 * otherwise whatever custom period their `cycle_start_day` defines (see
 * `App\Support\BudgetCycle`).
 */
class DashboardController extends Controller
{
    /**
     * @return View The `dashboard` view, given:
     *              - `periodStart`/`periodEnd`: the current period's bounds
     *              - `periodLabel`: a display label for the period (a month name if it's
     *              calendar-aligned, otherwise a date range)
     *              - `totalSpent`: sum of the user's expenses dated within this period
     *              - `categoryTotals`: Collection of category => summed amount for
     *              this period, sorted descending
     *              - `categories`: the fixed category list, for the quick-add form
     *              - `incomeExpectation`: this period's `IncomeExpectation`, or null if
     *              not set yet
     *              - `savingsGoal`: this period's `SavingsGoal`, or null if not set yet
     *              - `actualSavings`: expected income minus total spent, or null if no
     *              expected income is set (see the comment in the method body for
     *              why this can't be derived any other way)
     *              - `savingsProgress`: `actualSavings` as a percentage of the savings
     *              goal's target, clamped to [0, 100] for the progress bar, or null
     *              if either figure is missing
     *              - `recentExpenses`: the user's 5 most recent `Expense` models
     *              (by `date` desc, `created_at` desc as a tiebreaker), regardless
     *              of period - a recent-activity feed, not scoped to the current
     *              period like the totals above
     *              - `periodSummary`: the cached `PeriodSummary` (AI-generated spending
     *              overview) for this period, or null if one hasn't been generated yet
     *              (see `AiOverviewController`)
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $aggregates = DashboardAggregates::forUser($user);

        $recentExpenses = $user->expenses()
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $periodSummary = $user->periodSummaries()
            ->whereDate('period_start', $aggregates['periodStart']->toDateString())
            ->first();

        return view('dashboard', [
            ...$aggregates,
            'categories' => Expense::CATEGORIES,
            'recentExpenses' => $recentExpenses,
            'periodSummary' => $periodSummary,
        ]);
    }
}
