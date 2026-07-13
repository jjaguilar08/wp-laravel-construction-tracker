<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches construction log entries from the WordPress "Construction Tracker"
 * plugin's REST endpoint (`GET /wp-json/wp-tracker/v1/logs`), documented in
 * `PROJECT_NOTES.md`.
 */
class ConstructionLogService
{
    /** Cache key the decoded WP response is stored under. */
    private const CACHE_KEY = 'construction_logs';

    /** How long a successful response is cached before WordPress is hit again. */
    private const CACHE_TTL_SECONDS = 60;

    /**
     * Get all construction log entries, served from a short-lived cache
     * where possible.
     *
     * On a cache miss, calls the WP REST endpoint (5s timeout) and caches
     * the raw decoded array for `CACHE_TTL_SECONDS`. A failed fetch is not
     * cached, so the next call retries against WordPress rather than
     * replaying the failure.
     *
     * @return Collection Each element is an associative array with keys:
     *                    `id`, `title`, `entry_date` (`YYYY-MM-DD`), `category`, `amount`, `notes`.
     *
     * @throws ConnectionException If WordPress is unreachable (e.g. container is down).
     * @throws RequestException If WordPress responds with a 4xx/5xx status.
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
