<?php

namespace App\Http\Controllers;

use App\Services\ConstructionLogService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Renders the construction expense dashboard (`GET /construction`, route name `construction.dashboard`).
 */
class ConstructionDashboardController extends Controller
{
    /**
     * The category options offered in the filter dropdown, and the fixed
     * display order used wherever categories are listed.
     */
    private const CATEGORIES = ['materials', 'payroll', 'permits', 'hauling', 'equipment'];

    /**
     * Fetch construction log entries from WordPress, apply any category/date
     * filters from the query string, and render the dashboard view with the
     * filtered entries, per-category totals, and total spend.
     *
     * If WordPress is unreachable, renders the same view with empty data and
     * an `error` message instead of letting the exception propagate (see
     * `ConstructionLogService::getLogs()` for what can throw).
     *
     * @param  Request  $request  Expects optional `category`, `from`, and `to`
     *                            (ISO `YYYY-MM-DD`) query params; all are passed straight to the view
     *                            to repopulate the filter form.
     * @param  ConstructionLogService  $service  Injected by the container.
     * @return View The `construction-dashboard` view, given:
     *              - `entries`: filtered log entries, newest `entry_date` first
     *              - `categoryTotals`: Collection of category => summed amount, sorted descending
     *              - `totalSpend`: sum of `amount` across the filtered entries
     *              - `categories`: the fixed category list, for the filter dropdown
     *              - `filters`: the raw query params, to repopulate the filter form
     *              - `error`: a user-facing message if WordPress was unreachable, else null
     */
    public function index(Request $request, ConstructionLogService $service): View
    {
        $filters = $request->only(['category', 'from', 'to']);

        try {
            $entries = $service->getLogs();
        } catch (ConnectionException|RequestException) {
            return view('construction-dashboard', [
                'entries' => collect(),
                'categoryTotals' => collect(),
                'totalSpend' => 0,
                'categories' => self::CATEGORIES,
                'filters' => $filters,
                'error' => 'Unable to connect to the WordPress API. Make sure it is running and try again.',
            ]);
        }

        $filtered = $entries
            ->when($filters['category'] ?? null, fn ($rows, $category) => $rows->where('category', $category))
            ->when($filters['from'] ?? null, fn ($rows, $from) => $rows->where('entry_date', '>=', $from))
            ->when($filters['to'] ?? null, fn ($rows, $to) => $rows->where('entry_date', '<=', $to));

        $categoryTotals = $filtered->groupBy('category')
            ->map(fn ($group) => $group->sum('amount'))
            ->sortDesc();

        return view('construction-dashboard', [
            'entries' => $filtered->sortByDesc('entry_date')->values(),
            'categoryTotals' => $categoryTotals,
            'totalSpend' => $filtered->sum('amount'),
            'categories' => self::CATEGORIES,
            'filters' => $filters,
            'error' => null,
        ]);
    }
}
