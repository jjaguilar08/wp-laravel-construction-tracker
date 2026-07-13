<?php

namespace App\Policies;

use App\Models\IncomeExpectation;
use App\Models\User;

/**
 * Auto-discovered by Laravel's `IncomeExpectation` → `IncomeExpectationPolicy`
 * naming convention; checked via `$this->authorize()` in
 * `IncomeExpectationController` to keep users from editing/deleting each
 * other's entries.
 */
class IncomeExpectationPolicy
{
    public function view(User $user, IncomeExpectation $incomeExpectation): bool
    {
        return $user->id === $incomeExpectation->user_id;
    }

    public function update(User $user, IncomeExpectation $incomeExpectation): bool
    {
        return $user->id === $incomeExpectation->user_id;
    }

    public function delete(User $user, IncomeExpectation $incomeExpectation): bool
    {
        return $user->id === $incomeExpectation->user_id;
    }
}
