<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

/**
 * Auto-discovered by Laravel's `Expense` → `ExpensePolicy` naming
 * convention; checked via `$this->authorize()` in `ExpenseController` to
 * keep users from editing/deleting each other's expenses.
 */
class ExpensePolicy
{
    public function view(User $user, Expense $expense): bool
    {
        return $user->id === $expense->user_id;
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->id === $expense->user_id;
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->id === $expense->user_id;
    }
}
