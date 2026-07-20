<?php

namespace App\Http\Controllers;

use App\Models\IncomeExpectation;
use App\Support\BudgetCycle;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * CRUD for the authenticated user's per-period expected income
 * (`/income-expectations`, `except('show')`).
 *
 * One row per user per budget-cycle period is enforced both at the DB level
 * (unique `(user_id, period_start)` index) and in `validated()`, since the
 * DB constraint alone would surface as an unhandled `QueryException` rather
 * than a clean validation error.
 */
class IncomeExpectationController extends Controller
{
    /**
     * @return View The `income-expectations.index` view, given
     *              `incomeExpectations`: the user's entries, newest period first, each
     *              decorated with a display-only `period_label` (see
     *              `App\Support\BudgetCycle::label()`) - a month name when the
     *              period is calendar-aligned, otherwise a date range.
     */
    public function index(Request $request): View
    {
        $cycleStartDay = $request->user()->cycle_start_day;

        $incomeExpectations = $request->user()->incomeExpectations()->orderByDesc('period_start')->get()
            ->each(function ($incomeExpectation) use ($cycleStartDay) {
                $incomeExpectation->period_label = BudgetCycle::label(
                    $incomeExpectation->period_start,
                    BudgetCycle::endFor($incomeExpectation->period_start, $cycleStartDay)
                );
            });

        return view('income-expectations.index', ['incomeExpectations' => $incomeExpectations]);
    }

    public function create(): View
    {
        return view('income-expectations.create');
    }

    /**
     * @throws ValidationException If `month` is
     *                             missing/invalid or already has an entry for this user, or
     *                             `expected_amount` is missing/invalid (see `validated()`).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $request->user()->incomeExpectations()->create($validated);

        return redirect()->route('income-expectations.index')->with('status', 'Expected income set.');
    }

    /**
     * @throws AuthorizationException If the entry
     *                                doesn't belong to the authenticated user.
     */
    public function edit(IncomeExpectation $incomeExpectation): View
    {
        $this->authorize('update', $incomeExpectation);

        return view('income-expectations.edit', ['incomeExpectation' => $incomeExpectation]);
    }

    /**
     * @throws AuthorizationException If the entry
     *                                doesn't belong to the authenticated user.
     * @throws ValidationException If the submitted
     *                             fields fail validation (see `validated()`).
     */
    public function update(Request $request, IncomeExpectation $incomeExpectation): RedirectResponse
    {
        $this->authorize('update', $incomeExpectation);

        $incomeExpectation->update($this->validated($request, $incomeExpectation));

        return redirect()->route('income-expectations.index')->with('status', 'Expected income updated.');
    }

    /**
     * @throws AuthorizationException If the entry
     *                                doesn't belong to the authenticated user.
     */
    public function destroy(IncomeExpectation $incomeExpectation): RedirectResponse
    {
        $this->authorize('delete', $incomeExpectation);

        $incomeExpectation->delete();

        return redirect()->route('income-expectations.index')->with('status', 'Expected income removed.');
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
     * comparison would never match. When updating, `$incomeExpectation` is
     * excluded from the check so keeping the same period doesn't falsely
     * conflict with itself.
     *
     * `expected_amount`'s `max` matches the `decimal(10,2)` column's true
     * ceiling; the `regex` rejects more than 2 decimal places (which
     * `decimal:2` would otherwise silently round on save) and scientific
     * notation (which `numeric` alone allows).
     *
     * @return array{period_start: string, expected_amount: string}
     */
    private function validated(Request $request, ?IncomeExpectation $incomeExpectation = null): array
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'expected_amount' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'regex:/^\d+(\.\d{1,2})?$/'],
        ], [
            'expected_amount.regex' => 'The expected amount must be a plain number with at most 2 decimal places (no scientific notation).',
        ]);

        [$year, $monthNumber] = explode('-', $validated['month']);
        $periodStart = BudgetCycle::startForYearMonth((int) $year, (int) $monthNumber, $request->user()->cycle_start_day);

        // Kept under the "month" attribute key (rather than "period_start")
        // so the failure surfaces next to the form's only date input.
        $request->validate([
            'month' => [
                function ($attribute, $value, $fail) use ($request, $incomeExpectation, $periodStart) {
                    $exists = $request->user()->incomeExpectations()
                        ->whereDate('period_start', $periodStart->toDateString())
                        ->when($incomeExpectation, fn ($query) => $query->whereKeyNot($incomeExpectation))
                        ->exists();

                    if ($exists) {
                        $fail('You already have an expected income entry for this period.');
                    }
                },
            ],
        ]);

        return [
            'period_start' => $periodStart->toDateString(),
            'expected_amount' => $validated['expected_amount'],
        ];
    }
}
