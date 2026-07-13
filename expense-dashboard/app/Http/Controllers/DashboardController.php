<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Renders the authenticated user's finance overview for the current
 * calendar month (`GET /dashboard`, route name `dashboard`).
 */
class DashboardController extends Controller
{
    /**
     * @return View The `dashboard` view, given:
     *              - `month`: the current month, for display and for the quick-add
     *              form's default date
     *              - `totalSpent`: sum of the user's expenses dated within this month
     *              - `categoryTotals`: Collection of category => summed amount for
     *              this month, sorted descending
     *              - `categories`: the fixed category list, for the quick-add form
     *              - `incomeExpectation`: this month's `IncomeExpectation`, or null if
     *              not set yet
     *              - `savingsGoal`: this month's `SavingsGoal`, or null if not set yet
     *              - `actualSavings`: expected income minus total spent, or null if no
     *              expected income is set (see the comment in the method body for
     *              why this can't be derived any other way)
     *              - `savingsProgress`: `actualSavings` as a percentage of the savings
     *              goal's target, clamped to [0, 100] for the progress bar, or null
     *              if either figure is missing
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $month = Carbon::now()->startOfMonth();

        $monthExpenses = $user->expenses()
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->get();

        $totalSpent = $monthExpenses->sum('amount');

        $categoryTotals = $monthExpenses->groupBy('category')
            ->map(fn ($group) => $group->sum('amount'))
            ->sortDesc();

        $incomeExpectation = $user->incomeExpectations()->whereDate('month', $month->toDateString())->first();
        $savingsGoal = $user->savingsGoals()->whereDate('month', $month->toDateString())->first();

        // "Actual savings" for the month is expected income minus what's
        // actually been spent so far - i.e. the money left over that could
        // go toward the goal. This needs expected income as a baseline: with
        // no income set we have no way to know what "leftover" even means,
        // so we leave this null (prompting the user to set one) rather than
        // treating unset income as $0, which would misleadingly read as
        // "you overspent" before the user has entered anything.
        $actualSavings = $incomeExpectation
            ? $incomeExpectation->expected_amount - $totalSpent
            : null;

        $savingsProgress = ($savingsGoal && $actualSavings !== null && $savingsGoal->target_amount > 0)
            ? max(0, min(100, round(($actualSavings / $savingsGoal->target_amount) * 100)))
            : null;

        return view('dashboard', [
            'month' => $month,
            'totalSpent' => $totalSpent,
            'categoryTotals' => $categoryTotals,
            'categories' => Expense::CATEGORIES,
            'incomeExpectation' => $incomeExpectation,
            'savingsGoal' => $savingsGoal,
            'actualSavings' => $actualSavings,
            'savingsProgress' => $savingsProgress,
        ]);
    }
}
