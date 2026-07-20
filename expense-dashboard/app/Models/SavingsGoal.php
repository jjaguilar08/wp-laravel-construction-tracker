<?php

namespace App\Models;

use Database\Factories\SavingsGoalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The savings target a user set for a given budget-cycle period; one row
 * per `(user_id, period_start)`, enforced by a DB unique index and by
 * `SavingsGoalController::validated()`.
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon $period_start The start of the cycle period (see
 *                                `App\Support\BudgetCycle`) - the 1st of the month for a user with the
 *                                default `cycle_start_day` of 1, otherwise whatever day their cycle starts
 *                                on.
 * @property string $target_amount
 */
#[Fillable(['user_id', 'period_start', 'target_amount'])]
class SavingsGoal extends Model
{
    /** @use HasFactory<SavingsGoalFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'target_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
