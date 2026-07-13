<?php

namespace App\Policies;

use App\Models\SavingsGoal;
use App\Models\User;

/**
 * Auto-discovered by Laravel's `SavingsGoal` → `SavingsGoalPolicy` naming
 * convention; checked via `$this->authorize()` in `SavingsGoalController`
 * to keep users from editing/deleting each other's goals.
 */
class SavingsGoalPolicy
{
    public function view(User $user, SavingsGoal $savingsGoal): bool
    {
        return $user->id === $savingsGoal->user_id;
    }

    public function update(User $user, SavingsGoal $savingsGoal): bool
    {
        return $user->id === $savingsGoal->user_id;
    }

    public function delete(User $user, SavingsGoal $savingsGoal): bool
    {
        return $user->id === $savingsGoal->user_id;
    }
}
