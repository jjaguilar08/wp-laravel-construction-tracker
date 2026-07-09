<?php

namespace App\Http\Controllers;

use App\Services\ConstructionLogService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(ConstructionLogService $service): View
    {
        try {
            $entries = $service->getLogs();
        } catch (ConnectionException|RequestException) {
            return view('dashboard', [
                'entries' => collect(),
                'categoryTotals' => collect(),
                'totalSpend' => 0,
                'error' => 'Unable to reach the WordPress API. Make sure it is running and try again.',
            ]);
        }

        $categoryTotals = $entries->groupBy('category')
            ->map(fn ($group) => $group->sum('amount'))
            ->sortDesc();

        return view('dashboard', [
            'entries' => $entries->sortByDesc('entry_date')->values(),
            'categoryTotals' => $categoryTotals,
            'totalSpend' => $entries->sum('amount'),
            'error' => null,
        ]);
    }
}
