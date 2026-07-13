<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    /**
     * Six entries spanning three months and all five categories, so filter
     * combinations produce distinct, easily-asserted subsets.
     *
     * Hand-maintained mimic of the real WP `wp-tracker/v1/logs` response, not
     * derived from it — if the WP plugin's response shape changes (fields
     * added/renamed/removed), update this fixture (and its counterpart in
     * ConstructionLogServiceTest) to match, or these tests will keep passing
     * against a stale shape instead of catching the drift.
     */
    private function fixtureLogs(): array
    {
        return [
            ['id' => 1, 'title' => 'Cement Delivery', 'entry_date' => '2026-01-15', 'category' => 'materials', 'amount' => 1000, 'notes' => ''],
            ['id' => 2, 'title' => 'Crew Payroll', 'entry_date' => '2026-01-20', 'category' => 'payroll', 'amount' => 2500, 'notes' => ''],
            ['id' => 3, 'title' => 'Building Permit', 'entry_date' => '2026-02-01', 'category' => 'permits', 'amount' => 300, 'notes' => ''],
            ['id' => 4, 'title' => 'Gravel Hauling', 'entry_date' => '2026-02-10', 'category' => 'hauling', 'amount' => 450, 'notes' => ''],
            ['id' => 5, 'title' => 'Excavator Rental', 'entry_date' => '2026-03-05', 'category' => 'equipment', 'amount' => 1200, 'notes' => ''],
            ['id' => 6, 'title' => 'Lumber Order', 'entry_date' => '2026-03-15', 'category' => 'materials', 'amount' => 800, 'notes' => ''],
        ];
    }

    private function fakeWordPress(): void
    {
        Http::fake([
            '*/wp-json/wp-tracker/v1/logs' => Http::response($this->fixtureLogs()),
        ]);
    }

    public function test_dashboard_with_no_filters_shows_every_entry_and_the_correct_totals(): void
    {
        $this->fakeWordPress();

        $response = $this->get('/construction');

        $response->assertOk();

        foreach ($this->fixtureLogs() as $entry) {
            $response->assertSee($entry['title']);
        }

        // Category totals.
        $response->assertSeeInOrder(['materials', '$1,800.00']);
        $response->assertSee('$2,500.00'); // payroll
        $response->assertSee('$300.00'); // permits
        $response->assertSee('$450.00'); // hauling
        $response->assertSee('$1,200.00'); // equipment

        // Total spend across all six entries.
        $response->assertSee('$6,250.00');
    }

    public function test_filtering_by_category_only_shows_matching_entries_and_totals(): void
    {
        $this->fakeWordPress();

        $response = $this->get('/construction?category=materials');

        $response->assertOk();
        $response->assertSee('Cement Delivery');
        $response->assertSee('Lumber Order');
        $response->assertDontSee('Crew Payroll');
        $response->assertDontSee('Building Permit');
        $response->assertDontSee('Gravel Hauling');
        $response->assertDontSee('Excavator Rental');

        // Only the materials category total is shown, and it's the filtered total spend too.
        $response->assertSee('$1,800.00');
    }

    public function test_filtering_by_date_range_only_shows_matching_entries_and_totals(): void
    {
        $this->fakeWordPress();

        $response = $this->get('/construction?from=2026-02-01&to=2026-02-28');

        $response->assertOk();
        $response->assertSee('Building Permit');
        $response->assertSee('Gravel Hauling');
        $response->assertDontSee('Cement Delivery');
        $response->assertDontSee('Crew Payroll');
        $response->assertDontSee('Excavator Rental');
        $response->assertDontSee('Lumber Order');

        $response->assertSee('$300.00'); // permits total
        $response->assertSee('$450.00'); // hauling total
        $response->assertSee('$750.00'); // total spend for the range
    }

    public function test_combining_category_and_date_range_filters_narrows_to_the_intersection(): void
    {
        $this->fakeWordPress();

        $response = $this->get('/construction?category=materials&from=2026-03-01&to=2026-03-31');

        $response->assertOk();
        $response->assertSee('Lumber Order');
        $response->assertDontSee('Cement Delivery'); // materials, but outside the date range
        $response->assertDontSee('Crew Payroll');
        $response->assertDontSee('Building Permit');
        $response->assertDontSee('Gravel Hauling');
        $response->assertDontSee('Excavator Rental');

        // Only one $800 materials entry falls in range, so category total and total spend match.
        $response->assertSee('$800.00');
    }

    public function test_a_filter_matching_nothing_shows_the_empty_state_instead_of_stale_data(): void
    {
        $this->fakeWordPress();

        $response = $this->get('/construction?category=permits&from=2026-03-01&to=2026-03-31');

        $response->assertOk();
        $response->assertSee('No entries match these filters.');
        $response->assertSee('No entries found.');
        $response->assertSee('$0.00');

        foreach ($this->fixtureLogs() as $entry) {
            $response->assertDontSee($entry['title']);
        }
    }
}
