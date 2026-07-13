<?php

namespace App\Http\Controllers;

use App\Services\ConstructionLogService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;

/**
 * Raw-JSON debug endpoint for the construction log data (`GET /logs`).
 *
 * Not used by the dashboard UI; exists so the WP-backed data can be
 * inspected directly (e.g. via curl/Postman) without the dashboard's
 * filtering/aggregation applied.
 */
class ConstructionLogController extends Controller
{
    /**
     * Return every construction log entry as JSON.
     *
     * @param  ConstructionLogService  $service  Injected by the container.
     * @return Collection A collection of entries (each an
     *                    associative array: id, title, entry_date, category, amount, notes),
     *                    auto-serialized to a JSON array response.
     *
     * @throws ConnectionException If WordPress is unreachable.
     * @throws RequestException If WordPress returns a 4xx/5xx response.
     */
    public function index(ConstructionLogService $service)
    {
        return $service->getLogs();
    }
}
