<?php

namespace Tests\Unit\Support;

use App\Support\BudgetCycle;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BudgetCycleTest extends TestCase
{
    public function test_default_cycle_start_day_of_1_matches_the_calendar_month(): void
    {
        $period = BudgetCycle::periodContaining(Carbon::create(2026, 7, 15), 1);

        $this->assertTrue($period['start']->isSameDay(Carbon::create(2026, 7, 1)));
        $this->assertTrue($period['end']->isSameDay(Carbon::create(2026, 7, 31)));
        $this->assertSame('July 2026', BudgetCycle::label($period['start'], $period['end']));
    }

    public function test_a_custom_cycle_start_day_spans_two_calendar_months(): void
    {
        // The 15th, on a cycle that starts on the 20th, falls into the
        // period that started the previous month.
        $period = BudgetCycle::periodContaining(Carbon::create(2026, 7, 15), 20);

        $this->assertTrue($period['start']->isSameDay(Carbon::create(2026, 6, 20)));
        $this->assertTrue($period['end']->isSameDay(Carbon::create(2026, 7, 19)));
        $this->assertSame('Jun 20 - Jul 19, 2026', BudgetCycle::label($period['start'], $period['end']));
    }

    public function test_a_date_on_or_after_the_cycle_start_day_falls_in_the_current_months_period(): void
    {
        $period = BudgetCycle::periodContaining(Carbon::create(2026, 7, 20), 20);

        $this->assertTrue($period['start']->isSameDay(Carbon::create(2026, 7, 20)));
        $this->assertTrue($period['end']->isSameDay(Carbon::create(2026, 8, 19)));
    }

    public function test_cycle_start_day_31_clamps_correctly_across_short_months_without_bleeding_into_later_ones(): void
    {
        // Jan 31 -> next period starts Feb 28 (2026 is not a leap year),
        // clamped for that month only - not carried forward as "day 28"
        // into March, which does have a 31st.
        $janPeriod = BudgetCycle::periodContaining(Carbon::create(2026, 1, 31), 31);
        $this->assertTrue($janPeriod['start']->isSameDay(Carbon::create(2026, 1, 31)));
        $this->assertTrue($janPeriod['end']->isSameDay(Carbon::create(2026, 2, 27)));

        $febPeriod = BudgetCycle::periodContaining(Carbon::create(2026, 2, 28), 31);
        $this->assertTrue($febPeriod['start']->isSameDay(Carbon::create(2026, 2, 28)));
        // The period after clamped Feb 28 must resolve to Mar 31, not Mar 28.
        $this->assertTrue($febPeriod['end']->isSameDay(Carbon::create(2026, 3, 30)));

        $marPeriod = BudgetCycle::periodContaining(Carbon::create(2026, 3, 31), 31);
        $this->assertTrue($marPeriod['start']->isSameDay(Carbon::create(2026, 3, 31)));
    }

    public function test_recent_periods_returns_count_periods_oldest_first_ending_with_now(): void
    {
        $periods = BudgetCycle::recentPeriods(6, 1, Carbon::create(2026, 7, 15));

        $this->assertCount(6, $periods);
        $this->assertTrue($periods->first()['start']->isSameDay(Carbon::create(2026, 2, 1)));
        $this->assertTrue($periods->last()['start']->isSameDay(Carbon::create(2026, 7, 1)));
    }

    public function test_recent_periods_with_a_custom_cycle_start_day_does_not_bleed_clamped_days_across_months(): void
    {
        // cycle_start_day 31: with "now" on Jul 15, the current period
        // actually started Jun 30 (Jul 15 hasn't reached this month's day-31
        // boundary yet, and June only has 30 days). Walking back must give
        // each earlier period its own correctly clamped start (independently
        // derived), not a value chained/clamped down from a shorter month.
        $periods = BudgetCycle::recentPeriods(6, 31, Carbon::create(2026, 7, 15));

        $starts = $periods->map(fn ($period) => $period['start']->toDateString())->values()->all();

        $this->assertSame([
            '2026-01-31',
            '2026-02-28', // Feb has 28 days in 2026
            '2026-03-31',
            '2026-04-30',
            '2026-05-31',
            '2026-06-30', // "now" (Jul 15) is still within the period that started here
        ], $starts);
    }

    public function test_label_shows_a_date_range_when_not_calendar_aligned(): void
    {
        $start = Carbon::create(2026, 12, 20);
        $end = Carbon::create(2027, 1, 19);

        $this->assertSame('Dec 20, 2026 - Jan 19, 2027', BudgetCycle::label($start, $end));
    }

    public function test_short_label_for_charts(): void
    {
        $calendarPeriod = BudgetCycle::periodContaining(Carbon::create(2026, 7, 15), 1);
        $this->assertSame('Jul 2026', BudgetCycle::shortLabel($calendarPeriod['start'], $calendarPeriod['end']));

        $customPeriod = BudgetCycle::periodContaining(Carbon::create(2026, 7, 15), 20);
        $this->assertSame('Jun 20', BudgetCycle::shortLabel($customPeriod['start'], $customPeriod['end']));
    }
}
