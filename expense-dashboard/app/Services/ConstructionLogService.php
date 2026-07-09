<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConstructionLogService
{
    private const CACHE_KEY = 'construction_logs';

    private const CACHE_TTL_SECONDS = 60;

    /**
     * @throws ConnectionException|RequestException
     */
    public function getLogs(): Collection
    {
        // Cache the raw array, not a Collection: the cache config's
        // `serializable_classes` allow-list blocks object unserialization
        // by default, which would silently turn a cached Collection into
        // a __PHP_Incomplete_Class on the next read.
        $entries = Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            try {
                return Http::baseUrl(config('services.wp_tracker.base_url'))
                    ->timeout(5)
                    ->get('/wp-json/wp-tracker/v1/logs')
                    ->throw()
                    ->json();
            } catch (ConnectionException|RequestException $e) {
                Log::error('Failed to fetch construction logs from WordPress: '.$e->getMessage());

                throw $e;
            }
        });

        return collect($entries);
    }
}
