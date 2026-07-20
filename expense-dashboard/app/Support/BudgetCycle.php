<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Resolves a user's custom budget-cycle periods (e.g. "the 20th of one month
 * to the 19th of the next") from their `cycle_start_day` (1-31). A day that
 * doesn't exist in a given month (e.g. 31 in February) clamps to that
 * month's last day, recomputed fresh per month rather than chained from a
 * previously clamped date - so a cycle_start_day of 31 correctly starts on
 * Feb 28, then back to Mar 31, instead of getting stuck at 28 going forward.
 *
 * `cycle_start_day = 1` (the default for every existing user) makes every
 * period exactly a calendar month, so behavior is unchanged for anyone who
 * hasn't customized it.
 */
class BudgetCycle
{
    /**
     * The cycle-start date for the month containing $year/$month, clamped
     * to that month's last day if $cycleStartDay doesn't exist in it.
     */
    public static function startForYearMonth(int $year, int $month, int $cycleStartDay): Carbon
    {
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

        return Carbon::create($year, $month, min($cycleStartDay, $daysInMonth))->startOfDay();
    }

    /**
     * The start/end of the cycle period that $date falls within.
     *
     * @return array{start: Carbon, end: Carbon}
     */
    public static function periodContaining(Carbon $date, int $cycleStartDay): array
    {
        $thisMonthStart = self::startForYearMonth($date->year, $date->month, $cycleStartDay);

        if ($date->lt($thisMonthStart)) {
            $prev = $date->copy()->subMonthNoOverflow();
            $start = self::startForYearMonth($prev->year, $prev->month, $cycleStartDay);
        } else {
            $start = $thisMonthStart;
        }

        return [
            'start' => $start,
            'end' => self::endFor($start, $cycleStartDay),
        ];
    }

    /**
     * The last moment of the period that starts on $periodStart, given
     * $cycleStartDay - i.e. the day before the next occurrence of
     * $cycleStartDay after $periodStart's month.
     */
    public static function endFor(Carbon $periodStart, int $cycleStartDay): Carbon
    {
        $next = $periodStart->copy()->addMonthNoOverflow();
        $nextStart = self::startForYearMonth($next->year, $next->month, $cycleStartDay);

        return $nextStart->copy()->subDay()->endOfDay();
    }

    /**
     * @return array{start: Carbon, end: Carbon} The period containing "now".
     */
    public static function current(int $cycleStartDay, ?Carbon $now = null): array
    {
        return self::periodContaining($now ?? Carbon::now(), $cycleStartDay);
    }

    /**
     * The last $count periods, oldest first, ending with the period
     * containing "now". Each period is computed independently from its own
     * (year, month) rather than by chaining off the previous one, so a
     * clamped short month doesn't bleed into a later, longer one.
     *
     * @return Collection<int, array{start: Carbon, end: Carbon}>
     */
    public static function recentPeriods(int $count, int $cycleStartDay, ?Carbon $now = null): Collection
    {
        $current = self::current($cycleStartDay, $now);
        $currentYear = $current['start']->year;
        $currentMonth = $current['start']->month;

        return collect(range($count - 1, 0))->map(function ($periodsAgo) use ($currentYear, $currentMonth, $cycleStartDay) {
            $ref = Carbon::create($currentYear, $currentMonth, 1)->subMonthsNoOverflow($periodsAgo);
            $start = self::startForYearMonth($ref->year, $ref->month, $cycleStartDay);

            return [
                'start' => $start,
                'end' => self::endFor($start, $cycleStartDay),
            ];
        });
    }

    /**
     * A human label for the period, e.g. "July 2026" when it's exactly a
     * calendar month (the common default case), or "Jun 20 - Jul 19, 2026"
     * when the cycle doesn't align to one.
     */
    public static function label(Carbon $start, Carbon $end): string
    {
        if (self::isCalendarMonth($start, $end)) {
            return $start->format('F Y');
        }

        return $start->year === $end->year
            ? $start->format('M j').' - '.$end->format('M j, Y')
            : $start->format('M j, Y').' - '.$end->format('M j, Y');
    }

    /**
     * A compact label for chart axes, e.g. "Jul 2026" when calendar-aligned,
     * otherwise just the start date, e.g. "Jun 20".
     */
    public static function shortLabel(Carbon $start, Carbon $end): string
    {
        return self::isCalendarMonth($start, $end)
            ? $start->format('M Y')
            : $start->format('M j');
    }

    private static function isCalendarMonth(Carbon $start, Carbon $end): bool
    {
        return $start->day === 1 && $end->isSameDay($start->copy()->endOfMonth());
    }
}
