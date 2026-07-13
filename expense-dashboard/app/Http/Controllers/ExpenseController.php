<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * CRUD for the authenticated user's expenses (`/expenses`, `except('show')`).
 *
 * All queries are scoped to `$request->user()` rather than relying on global
 * route-model binding, so one user can never list another's expenses; the
 * `update`/`delete` policy checks in `ExpensePolicy` guard the
 * edit/update/destroy actions where an `Expense` is resolved by ID alone.
 */
class ExpenseController extends Controller
{
    /**
     * List the authenticated user's expenses, newest date first.
     *
     * @return View The `expenses.index` view, given `expenses`: the user's
     *              `Expense` models.
     */
    public function index(Request $request): View
    {
        $expenses = $request->user()->expenses()->orderByDesc('date')->get();

        return view('expenses.index', ['expenses' => $expenses]);
    }

    /**
     * @return View The `expenses.create` view, given `categories`: the
     *              fixed category list for the select input.
     */
    public function create(): View
    {
        return view('expenses.create', ['categories' => Expense::CATEGORIES]);
    }

    /**
     * Validate and create a new expense owned by the authenticated user.
     *
     * @throws ValidationException If `amount`,
     *                             `category`, or `date` are missing/invalid (see `validated()`).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->user()->expenses()->create($this->validated($request));

        return redirect()->route('expenses.index')->with('status', 'Expense added.');
    }

    /**
     * @throws AuthorizationException If the expense
     *                                doesn't belong to the authenticated user.
     */
    public function edit(Expense $expense): View
    {
        $this->authorize('update', $expense);

        return view('expenses.edit', ['expense' => $expense, 'categories' => Expense::CATEGORIES]);
    }

    /**
     * @throws AuthorizationException If the expense
     *                                doesn't belong to the authenticated user.
     * @throws ValidationException If the submitted
     *                             fields fail validation (see `validated()`).
     */
    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorize('update', $expense);

        $expense->update($this->validated($request));

        return redirect()->route('expenses.index')->with('status', 'Expense updated.');
    }

    /**
     * @throws AuthorizationException If the expense
     *                                doesn't belong to the authenticated user.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return redirect()->route('expenses.index')->with('status', 'Expense deleted.');
    }

    /**
     * Shared validation rules for `store()` and `update()`.
     *
     * @return array{amount: string, category: string, date: string, notes: ?string}
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category' => ['required', 'in:'.implode(',', Expense::CATEGORIES)],
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
