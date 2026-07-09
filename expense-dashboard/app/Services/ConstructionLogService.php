<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConstructionLogService
{
    /**
     * @throws ConnectionException|RequestException
     */
    public function getLogs(): Collection
    {
        try {
            return Http::baseUrl(config('services.wp_tracker.base_url'))
                ->timeout(5)
                ->get('/wp-json/wp-tracker/v1/logs')
                ->throw()
                ->collect();
        } catch (ConnectionException|RequestException $e) {
            Log::error('Failed to fetch construction logs from WordPress: '.$e->getMessage());

            throw $e;
        }
    }
}
