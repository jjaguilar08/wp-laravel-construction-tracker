<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HelpControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Hand-maintained mimic of the real WP `wp-tracker/v1/articles` response, not
     * derived from it — if the WP plugin's response shape changes (fields
     * added/renamed/removed), update this fixture (and its counterpart in
     * HelpArticleServiceTest) to match, or these tests will keep passing against
     * a stale shape instead of catching the drift.
     */
    private function fixtureArticles(): array
    {
        return [
            [
                'id' => 17,
                'title' => 'How to Log an Expense',
                'content' => "<p>Go to the Expenses page and click 'Add Expense'.</p>",
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
            [
                'id' => 19,
                'title' => 'FAQs &amp; Tips',
                'content' => '<p>Frequently asked questions.</p>',
                'slug' => 'faqs-tips',
                'featured_image' => false,
            ],
        ];
    }

    private function fakeWordPress(): void
    {
        Http::fake([
            '*/wp-json/wp-tracker/v1/articles' => Http::response($this->fixtureArticles()),
        ]);
    }

    public function test_it_shows_title_content_and_featured_image_for_each_article(): void
    {
        $this->fakeWordPress();

        $response = $this->get('/help');

        $response->assertOk();

        foreach ($this->fixtureArticles() as $article) {
            if ($article['title'] === 'FAQs &amp; Tips') {
                continue; // asserted separately below, decoded.
            }

            $response->assertSee($article['title']);
        }

        $response->assertSee('Go to the Expenses page and click', false);
        $response->assertSee('Welcome to the dashboard.', false);

        // Only the article with a featured image should render an <img>.
        $response->assertSee('http://localhost:8080/wp-content/uploads/2026/07/expense.png');
    }

    public function test_it_decodes_html_entities_in_article_titles(): void
    {
        $this->fakeWordPress();

        $response = $this->get('/help');

        $response->assertOk();
        $response->assertSee('FAQs & Tips');
        $response->assertDontSee('FAQs &amp; Tips');
    }

    public function test_it_shows_a_friendly_error_banner_instead_of_crashing_when_wordpress_is_unreachable(): void
    {
        Http::fake([
            '*/wp-json/wp-tracker/v1/articles' => Http::failedConnection(),
        ]);

        $response = $this->get('/help');

        $response->assertOk();
        $response->assertSee('Unable to connect to the WordPress API. Make sure it is running and try again.');

        foreach ($this->fixtureArticles() as $article) {
            $response->assertDontSee($article['title']);
        }
    }

    public function test_guests_can_load_help_without_the_shared_nav_crashing(): void
    {
        // /help is intentionally not auth-gated, but shares the same nav partial as the
        // authenticated pages (layouts.navigation), which assumes Auth::user() is present
        // (e.g. Auth::user()->name in the settings dropdown). Guards this regression.
        $this->fakeWordPress();

        $response = $this->get('/help');

        $response->assertOk();
        $response->assertSee(__('Log In'));
        $response->assertDontSee(__('Log Out'));
    }

    public function test_authenticated_users_see_the_shared_nav_with_help_highlighted_active(): void
    {
        $this->fakeWordPress();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/help');

        $response->assertOk();
        $response->assertSee(__('Dashboard'));
        $response->assertSee(__('Log Out'));
        $response->assertSee('border-indigo-400', false); // active-state class on the Help nav-link
    }
}
