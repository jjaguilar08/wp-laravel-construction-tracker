<?php

namespace App\Http\Controllers;

use App\Services\ConstructionLogService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const CATEGORIES = ['materials', 'payroll', 'permits', 'hauling', 'equipment'];

    public function index(Request $request, ConstructionLogService $service): View
    {
        $filters = $request->only(['category', 'from', 'to']);

        try {
            $entries = $service->getLogs();
        } catch (ConnectionException|RequestException) {
            return view('dashboard', [
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

        return view('dashboard', [
            'entries' => $filtered->sortByDesc('entry_date')->values(),
            'categoryTotals' => $categoryTotals,
            'totalSpend' => $filtered->sum('amount'),
            'categories' => self::CATEGORIES,
            'filters' => $filters,
            'error' => null,
        ]);
    }
}
