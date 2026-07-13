<?php

namespace App\Models;

use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A single dated spend entry, always owned by exactly one user.
 *
 * @property int $id
 * @property int $user_id
 * @property string $amount
 * @property string $category One of self::CATEGORIES.
 * @property Carbon $date
 * @property ?string $notes
 */
#[Fillable(['user_id', 'amount', 'category', 'date', 'notes'])]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    /**
     * The fixed category options, enforced both by this validation-side
     * list (`in:` rule) and the `category` column's DB-level enum check.
     */
    public const CATEGORIES = ['food', 'housing', 'transport', 'healthcare', 'bills', 'entertainment', 'savings', 'other'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
