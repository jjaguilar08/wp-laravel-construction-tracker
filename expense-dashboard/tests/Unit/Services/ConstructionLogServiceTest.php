<?php

namespace Tests\Unit\Services;

use App\Services\ConstructionLogService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class ConstructionLogServiceTest extends TestCase
{
    /**
     * Hand-maintained mimic of the real WP `wp-tracker/v1/logs` response, not
     * derived from it — if the WP plugin's response shape changes (fields
     * added/renamed/removed), update this fixture (and its counterpart in
     * ConstructionDashboardControllerTest) to match, or these tests will keep passing
     * against a stale shape instead of catching the drift.
     */
    private function fixtureLogs(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Cement Delivery',
                'entry_date' => '2026-01-15',
                'category' => 'materials',
                'amount' => 1000,
                'notes' => "Gian's Hardware",
            ],
            [
                'id' => 2,
                'title' => 'Crew Payroll',
                'entry_date' => '2026-01-20',
                'category' => 'payroll',
                'amount' => 2500,
                'notes' => '',
            ],
        ];
    }

    public function test_it_returns_the_expected_collection_of_logs_on_success(): void
    {
        Http::fake([
            '*/wp-json/wp-tracker/v1/logs' => Http::response($this->fixtureLogs()),
        ]);

        $result = app(ConstructionLogService::class)->getLogs();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($this->fixtureLogs(), $result->toArray());
    }

    public function test_it_caches_logs_and_does_not_corrupt_them_on_a_second_read(): void
    {
        // Force the array store to actually serialize/unserialize values (like the
        // 'file'/'database' stores do in production) instead of just holding them in
        // memory. This reproduces the Day 4 caching bug: caching a Collection directly
        // gets mangled into __PHP_Incomplete_Class on the second read once
        // config('cache.serializable_classes') = false blocks object reconstruction. The
        // service is expected to cache the raw array and re-wrap it in collect() on every
        // read, which survives this round trip.
        config(['cache.stores.array.serialize' => true]);
        app('cache')->purge('array');

        Http::fake([
            '*/wp-json/wp-tracker/v1/logs' => Http::response($this->fixtureLogs()),
        ]);

        $service = app(ConstructionLogService::class);

        $first = $service->getLogs();
        $second = $service->getLogs();

        Http::assertSentCount(1);
        $this->assertSame($this->fixtureLogs(), $first->toArray());
        $this->assertSame($this->fixtureLogs(), $second->toArray());
    }

    public function test_it_logs_and_rethrows_on_a_connection_exception(): void
    {
        Http::fake([
            '*/wp-json/wp-tracker/v1/logs' => Http::failedConnection(),
        ]);

        Log::shouldReceive('error')
            ->once()
            ->with(Mockery::pattern('/^Failed to fetch construction logs from WordPress:/'));

        $this->expectException(ConnectionException::class);

        app(ConstructionLogService::class)->getLogs();
    }

    public function test_it_logs_and_rethrows_on_a_request_exception(): void
    {
        Http::fake([
            '*/wp-json/wp-tracker/v1/logs' => Http::response(['message' => 'Internal Server Error'], 500),
        ]);

        Log::shouldReceive('error')
            ->once()
            ->with(Mockery::pattern('/^Failed to fetch construction logs from WordPress:/'));

        $this->expectException(RequestException::class);

        app(ConstructionLogService::class)->getLogs();
    }

    public function test_a_failed_fetch_is_not_cached(): void
    {
        Http::fake([
            '*/wp-json/wp-tracker/v1/logs' => Http::sequence()
                ->pushStatus(500)
                ->push($this->fixtureLogs()),
        ]);

        Log::shouldReceive('error')->once();

        try {
            app(ConstructionLogService::class)->getLogs();
            $this->fail('Expected a RequestException to be thrown.');
        } catch (RequestException) {
            // expected on the first, failed attempt
        }

        $result = app(ConstructionLogService::class)->getLogs();

        Http::assertSentCount(2);
        $this->assertSame($this->fixtureLogs(), $result->toArray());
    }
}
