<?php

namespace App\Models;

use Database\Factories\PeriodSummaryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A cached AI-generated overview of a user's spending for a budget-cycle
 * period; one row per `(user_id, period_start)`, enforced by a DB unique
 * index - regenerating overwrites the existing row (see
 * `AiOverviewController`) rather than accumulating history.
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon $period_start
 * @property string $summary
 */
#[Fillable(['user_id', 'period_start', 'summary'])]
class PeriodSummary extends Model
{
    /** @use HasFactory<PeriodSummaryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
