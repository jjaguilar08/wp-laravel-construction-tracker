<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Renders the authenticated user's total spend per month for the last 6
 * months, including the current month (`GET /trends`, route name `trends`).
 */
class TrendController extends Controller
{
    /**
     * @return View The `trends` view, given `monthlyTotals`: a Collection of
     *              6 entries, oldest to newest, ending with the current
     *              month - each `['month' => Carbon, 'total' => int|string]`.
     *              A month with no expenses still gets an entry with a `0`
     *              total rather than being omitted, so the chart/table stay
     *              continuous across all 6 months.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $start = Carbon::now()->startOfMonth()->subMonths(5);
        $end = Carbon::now()->endOfMonth();

        $totalsByMonth = $user->expenses()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn ($expense) => $expense->date->format('Y-m'))
            ->map(fn ($group) => $group->sum('amount'));

        $monthlyTotals = collect(range(5, 0))->map(function ($monthsAgo) use ($totalsByMonth) {
            $month = Carbon::now()->startOfMonth()->subMonths($monthsAgo);

            return [
                'month' => $month,
                'total' => $totalsByMonth->get($month->format('Y-m'), 0),
            ];
        });

        return view('trends', ['monthlyTotals' => $monthlyTotals]);
    }
}
