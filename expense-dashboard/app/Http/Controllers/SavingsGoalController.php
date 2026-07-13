<?php

namespace App\Http\Controllers;

use App\Models\SavingsGoal;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * CRUD for the authenticated user's per-month savings goals
 * (`/savings-goals`, `except('show')`).
 *
 * One row per user per month is enforced both at the DB level (unique
 * `(user_id, month)` index) and in `validated()`, since the DB constraint
 * alone would surface as an unhandled `QueryException` rather than a clean
 * validation error.
 */
class SavingsGoalController extends Controller
{
    /**
     * @return View The `savings-goals.index` view, given `savingsGoals`:
     *              the user's goals, newest month first.
     */
    public function index(Request $request): View
    {
        $savingsGoals = $request->user()->savingsGoals()->orderByDesc('month')->get();

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
     * The `<input type="month">` field submits `"YYYY-MM"`; normalized here
     * to the first of the month before validating. The uniqueness check
     * uses `whereDate()` rather than a `Rule::unique()` column comparison
     * because Eloquent/SQLite persists the `date`-cast `month` column as a
     * full datetime string (e.g. `"2026-07-01 00:00:00"`), which a bare
     * `"2026-07-01"` string comparison would never match. When updating,
     * `$savingsGoal` is excluded from the check so keeping the same month
     * doesn't falsely conflict with itself.
     *
     * @return array{month: string, target_amount: string}
     */
    private function validated(Request $request, ?SavingsGoal $savingsGoal = null): array
    {
        $request->merge(['month' => $request->input('month').'-01']);

        return $request->validate([
            'month' => [
                'required',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) use ($request, $savingsGoal) {
                    $exists = $request->user()->savingsGoals()
                        ->whereDate('month', $value)
                        ->when($savingsGoal, fn ($query) => $query->whereKeyNot($savingsGoal))
                        ->exists();

                    if ($exists) {
                        $fail('You already have a savings goal for this month.');
                    }
                },
            ],
            'target_amount' => ['required', 'numeric', 'min:0'],
        ]);
    }
}
