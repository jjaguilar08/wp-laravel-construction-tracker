<?php

namespace Tests\Unit\Services;

use App\Services\HelpArticleService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class HelpArticleServiceTest extends TestCase
{
    /**
     * Hand-maintained mimic of the real WP `wp-tracker/v1/articles` response, not
     * derived from it — if the WP plugin's response shape changes (fields
     * added/renamed/removed), update this fixture to match, or these tests will
     * keep passing against a stale shape instead of catching the drift.
     */
    private function fixtureArticles(): array
    {
        return [
            [
                'id' => 17,
                'title' => 'How to Log an Expense',
                'content' => "<p>Go to the Expenses page and click 'Add Expense'&hellip;</p>\n",
                'slug' => 'how-to-log-an-expense',
                'featured_image' => 'http://localhost:8080/wp-content/uploads/2026/07/expense.png',
            ],
            [
                'id' => 18,
                'title' => 'Getting Started',
                'content' => '<p>Welcome to the dashboard.</p>',
                'slug' => 'getting-started',
                'featured_image' => false,
            ],
        ];
    }

    /**
     * Decoded expectation for fixtureArticles(): only `title` is expected to
     * change, since HelpArticleService::getArticles() runs html_entity_decode()
     * on titles but leaves `content` (already rendered HTML) untouched.
     */
    private function decodedFixtureArticles(): array
    {
        $articles = $this->fixtureArticles();
        $articles[0]['title'] = html_entity_decode($articles[0]['title']);
        $articles[1]['title'] = html_entity_decode($articles[1]['title']);

        return $articles;
    }

    public function test_it_returns_the_expected_collection_of_articles_on_success(): void
    {
        Http::fake([
            '*/wp-json/wp-tracker/v1/articles' => Http::response($this->fixtureArticles()),
        ]);

        $result = app(HelpArticleService::class)->getArticles();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($this->decodedFixtureArticles(), $result->toArray());
    }

    public function test_it_decodes_html_entities_in_the_title(): void
    {
        Http::fake([
            '*/wp-json/wp-tracker/v1/articles' => Http::response([
                [
                    'id' => 1,
                    'title' => 'FAQs &amp; Tips',
                    'content' => '<p>...</p>',
                    'slug' => 'faqs-tips',
                    'featured_image' => false,
                ],
            ]),
        ]);

        $result = app(HelpArticleService::class)->getArticles();

        $this->assertSame('FAQs & Tips', $result->first()['title']);
    }

    public function test_it_caches_articles_and_does_not_corrupt_them_on_a_second_read(): void
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
            '*/wp-json/wp-tracker/v1/articles' => Http::response($this->fixtureArticles()),
        ]);

        $service = app(HelpArticleService::class);

        $first = $service->getArticles();
        $second = $service->getArticles();

        Http::assertSentCount(1);
        $this->assertSame($this->decodedFixtureArticles(), $first->toArray());
        $this->assertSame($this->decodedFixtureArticles(), $second->toArray());
    }

    public function test_it_logs_and_rethrows_on_a_connection_exception(): void
    {
        Http::fake([
            '*/wp-json/wp-tracker/v1/articles' => Http::failedConnection(),
        ]);

        Log::shouldReceive('error')
            ->once()
            ->with(Mockery::pattern('/^Failed to fetch help articles from WordPress:/'));

        $this->expectException(ConnectionException::class);

        app(HelpArticleService::class)->getArticles();
    }

    public function test_it_logs_and_rethrows_on_a_request_exception(): void
    {
        Http::fake([
            '*/wp-json/wp-tracker/v1/articles' => Http::response(['message' => 'Internal Server Error'], 500),
        ]);

        Log::shouldReceive('error')
            ->once()
            ->with(Mockery::pattern('/^Failed to fetch help articles from WordPress:/'));

        $this->expectException(RequestException::class);

        app(HelpArticleService::class)->getArticles();
    }

    public function test_a_failed_fetch_is_not_cached(): void
    {
        Http::fake([
            '*/wp-json/wp-tracker/v1/articles' => Http::sequence()
                ->pushStatus(500)
                ->push($this->fixtureArticles()),
        ]);

        Log::shouldReceive('error')->once();

        try {
            app(HelpArticleService::class)->getArticles();
            $this->fail('Expected a RequestException to be thrown.');
        } catch (RequestException) {
            // expected on the first, failed attempt
        }

        $result = app(HelpArticleService::class)->getArticles();

        Http::assertSentCount(2);
        $this->assertSame($this->decodedFixtureArticles(), $result->toArray());
    }
}
