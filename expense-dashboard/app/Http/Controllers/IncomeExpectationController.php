<?php

namespace App\Http\Controllers;

use App\Models\IncomeExpectation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * CRUD for the authenticated user's per-month expected income
 * (`/income-expectations`, `except('show')`).
 *
 * One row per user per month is enforced both at the DB level (unique
 * `(user_id, month)` index) and in `validated()`, since the DB constraint
 * alone would surface as an unhandled `QueryException` rather than a clean
 * validation error.
 */
class IncomeExpectationController extends Controller
{
    /**
     * @return View The `income-expectations.index` view, given
     *              `incomeExpectations`: the user's entries, newest month first.
     */
    public function index(Request $request): View
    {
        $incomeExpectations = $request->user()->incomeExpectations()->orderByDesc('month')->get();

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
     * The `<input type="month">` field submits `"YYYY-MM"`; normalized here
     * to the first of the month before validating. The uniqueness check
     * uses `whereDate()` rather than a `Rule::unique()` column comparison
     * because Eloquent/SQLite persists the `date`-cast `month` column as a
     * full datetime string (e.g. `"2026-07-01 00:00:00"`), which a bare
     * `"2026-07-01"` string comparison would never match. When updating,
     * `$incomeExpectation` is excluded from the check so keeping the same
     * month doesn't falsely conflict with itself.
     *
     * `expected_amount`'s `max` matches the `decimal(10,2)` column's true
     * ceiling; the `regex` rejects more than 2 decimal places (which
     * `decimal:2` would otherwise silently round on save) and scientific
     * notation (which `numeric` alone allows).
     *
     * @return array{month: string, expected_amount: string}
     */
    private function validated(Request $request, ?IncomeExpectation $incomeExpectation = null): array
    {
        $request->merge(['month' => $request->input('month').'-01']);

        return $request->validate([
            'month' => [
                'required',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) use ($request, $incomeExpectation) {
                    $exists = $request->user()->incomeExpectations()
                        ->whereDate('month', $value)
                        ->when($incomeExpectation, fn ($query) => $query->whereKeyNot($incomeExpectation))
                        ->exists();

                    if ($exists) {
                        $fail('You already have an expected income entry for this month.');
                    }
                },
            ],
            'expected_amount' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'regex:/^\d+(\.\d{1,2})?$/'],
        ], [
            'expected_amount.regex' => 'The expected amount must be a plain number with at most 2 decimal places (no scientific notation).',
        ]);
    }
}
