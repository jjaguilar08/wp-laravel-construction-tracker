<?php

namespace App\Models;

use Database\Factories\IncomeExpectationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The expected income a user set for a given month; one row per
 * `(user_id, month)`, enforced by a DB unique index and by
 * `IncomeExpectationController::validated()`.
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon $month Always the 1st of the month.
 * @property string $expected_amount
 */
#[Fillable(['user_id', 'month', 'expected_amount'])]
class IncomeExpectation extends Model
{
    /** @use HasFactory<IncomeExpectationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'month' => 'date',
            'expected_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
