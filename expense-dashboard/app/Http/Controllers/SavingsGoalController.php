<?php

namespace App\Http\Controllers;

use App\Models\SavingsGoal;
use App\Support\BudgetCycle;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * CRUD for the authenticated user's per-period savings goals
 * (`/savings-goals`, `except('show')`).
 *
 * One row per user per budget-cycle period is enforced both at the DB level
 * (unique `(user_id, period_start)` index) and in `validated()`, since the
 * DB constraint alone would surface as an unhandled `QueryException` rather
 * than a clean validation error.
 */
class SavingsGoalController extends Controller
{
    /**
     * @return View The `savings-goals.index` view, given `savingsGoals`: the
     *              user's goals, newest period first, each decorated with a
     *              display-only `period_label` (see
     *              `App\Support\BudgetCycle::label()`) - a month name when the
     *              period is calendar-aligned, otherwise a date range.
     */
    public function index(Request $request): View
    {
        $cycleStartDay = $request->user()->cycle_start_day;

        $savingsGoals = $request->user()->savingsGoals()->orderByDesc('period_start')->get()
            ->each(function ($savingsGoal) use ($cycleStartDay) {
                $savingsGoal->period_label = BudgetCycle::label(
                    $savingsGoal->period_start,
                    BudgetCycle::endFor($savingsGoal->period_start, $cycleStartDay)
                );
            });

        return view('savings-goals.index', ['savingsGoals' => $savingsGoals]);
    }

    public function create(): View
    {
        return view('savings-goals.create');
    }

    /**
     * @throws ValidationException If `month` is
     *                             missing/invalid or already has a goal for this user, or
     *                             `target_amount` is missing/invalid (see `validated()`).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->user()->savingsGoals()->create($this->validated($request));

        return redirect()->route('savings-goals.index')->with('status', 'Savings goal set.');
    }

    /**
     * @throws AuthorizationException If the goal
     *                                doesn't belong to the authenticated user.
     */
    public function edit(SavingsGoal $savingsGoal): View
    {
        $this->authorize('update', $savingsGoal);

        return view('savings-goals.edit', ['savingsGoal' => $savingsGoal]);
    }

    /**
     * @throws AuthorizationException If the goal
     *                                doesn't belong to the authenticated user.
     * @throws ValidationException If the submitted
     *                             fields fail validation (see `validated()`).
     */
    public function update(Request $request, SavingsGoal $savingsGoal): RedirectResponse
    {
        $this->authorize('update', $savingsGoal);

        $savingsGoal->update($this->validated($request, $savingsGoal));

        return redirect()->route('savings-goals.index')->with('status', 'Savings goal updated.');
    }

    /**
     * @throws AuthorizationException If the goal
     *                                doesn't belong to the authenticated user.
     */
    public function destroy(SavingsGoal $savingsGoal): RedirectResponse
    {
        $this->authorize('delete', $savingsGoal);

        $savingsGoal->delete();

        return redirect()->route('savings-goals.index')->with('status', 'Savings goal removed.');
    }

    /**
     * Shared validation rules for `store()` and `update()`.
     *
     * The `<input type="month">` field submits `"YYYY-MM"` - a native HTML
     * widget, so it can only pick a calendar month, never an arbitrary
     * cycle-aligned date. What that selection actually means is "the period
     * whose start falls in this month": the chosen year/month is resolved
     * to this user's actual `period_start` via `BudgetCycle::startForYearMonth()`
     * using their `cycle_start_day` (the 1st of the month by default). The
     * uniqueness check uses `whereDate()` rather than a `Rule::unique()`
     * column comparison because Eloquent/SQLite persists the `date`-cast
     * `period_start` column as a full datetime string (e.g.
     * `"2026-07-01 00:00:00"`), which a bare `"2026-07-01"` string
     * comparison would never match. When updating, `$savingsGoal` is
     * excluded from the check so keeping the same period doesn't falsely
     * conflict with itself.
     *
     * `target_amount`'s `max` matches the `decimal(10,2)` column's true
     * ceiling; the `regex` rejects more than 2 decimal places (which
     * `decimal:2` would otherwise silently round on save) and scientific
     * notation (which `numeric` alone allows).
     *
     * @return array{period_start: string, target_amount: string}
     */
    private function validated(Request $request, ?SavingsGoal $savingsGoal = null): array
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'target_amount' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'regex:/^\d+(\.\d{1,2})?$/'],
        ], [
            'target_amount.regex' => 'The target amount must be a plain number with at most 2 decimal places (no scientific notation).',
        ]);

        [$year, $monthNumber] = explode('-', $validated['month']);
        $periodStart = BudgetCycle::startForYearMonth((int) $year, (int) $monthNumber, $request->user()->cycle_start_day);

        // Kept under the "month" attribute key (rather than "period_start")
        // so the failure surfaces next to the form's only date input.
        $request->validate([
            'month' => [
                function ($attribute, $value, $fail) use ($request, $savingsGoal, $periodStart) {
                    $exists = $request->user()->savingsGoals()
                        ->whereDate('period_start', $periodStart->toDateString())
                        ->when($savingsGoal, fn ($query) => $query->whereKeyNot($savingsGoal))
                        ->exists();

                    if ($exists) {
                        $fail('You already have a savings goal for this period.');
                    }
                },
            ],
        ]);

        return [
            'period_start' => $periodStart->toDateString(),
            'target_amount' => $validated['target_amount'],
        ];
    }
}
