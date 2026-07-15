<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\IncomeExpectation;
use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_shows_totals_income_and_savings_progress_for_the_current_month(): void
    {
        $this->travelTo(Carbon::create(2026, 7, 15));

        $user = User::factory()->create();

        Expense::factory()->for($user)->create(['category' => 'food', 'amount' => 300, 'date' => '2026-07-05']);
        Expense::factory()->for($user)->create(['category' => 'transport', 'amount' => 200, 'date' => '2026-07-10']);
        // Outside the current month - must not count toward the total.
        Expense::factory()->for($user)->create(['category' => 'food', 'amount' => 9999, 'date' => '2026-06-20']);

        IncomeExpectation::factory()->for($user)->create(['month' => '2026-07-01', 'expected_amount' => 1000]);
        SavingsGoal::factory()->for($user)->create(['month' => '2026-07-01', 'target_amount' => 400]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('500.00'); // total spent: 300 + 200
        $response->assertSee('1,000.00'); // expected income
        $response->assertSee('400.00'); // savings goal target
        // actual savings = 1000 - 500 = 500, which exceeds the 400 target,
        // so progress is clamped to 100% rather than reading 125%.
        $response->assertSee('100%');
        $response->assertDontSee('9,999.00');
    }

    public function test_dashboard_does_not_divide_by_zero_when_the_savings_goal_target_is_zero(): void
    {
        $this->travelTo(Carbon::create(2026, 7, 15));

        $user = User::factory()->create();
        Expense::factory()->for($user)->create(['amount' => 100, 'date' => '2026-07-05']);
        IncomeExpectation::factory()->for($user)->create(['month' => '2026-07-01', 'expected_amount' => 500]);
        SavingsGoal::factory()->for($user)->create(['month' => '2026-07-01', 'target_amount' => 0]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    }

    public function test_dashboard_clamps_progress_to_zero_when_overspent(): void
    {
        $this->travelTo(Carbon::create(2026, 7, 15));

        $user = User::factory()->create();
        Expense::factory()->for($user)->create(['amount' => 900, 'date' => '2026-07-05']);
        IncomeExpectation::factory()->for($user)->create(['month' => '2026-07-01', 'expected_amount' => 500]);
        SavingsGoal::factory()->for($user)->create(['month' => '2026-07-01', 'target_amount' => 400]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        // actual savings = 500 - 900 = -400, which is below zero, so
        // progress must clamp to 0% rather than a negative percentage.
        $response->assertSee('0%');
        $response->assertDontSee('-100%');
    }

    public function test_dashboard_prompts_for_missing_income_and_goal_instead_of_breaking(): void
    {
        $this->travelTo(Carbon::create(2026, 7, 15));

        $user = User::factory()->create();
        Expense::factory()->for($user)->create(['amount' => 50, 'date' => '2026-07-05']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee("You haven't set expected income for this month yet.", false);
        $response->assertSee("You haven't set a savings goal for this month yet.", false);
        $response->assertDontSee('%'); // no progress bar can be computed without both figures
    }

    public function test_category_totals_for_the_dashboard_are_grouped_and_summed_correctly(): void
    {
        $this->travelTo(Carbon::create(2026, 7, 15));

        $user = User::factory()->create();
        Expense::factory()->for($user)->create(['category' => 'food', 'amount' => 100, 'date' => '2026-07-01']);
        Expense::factory()->for($user)->create(['category' => 'food', 'amount' => 50, 'date' => '2026-07-02']);
        Expense::factory()->for($user)->create(['category' => 'transport', 'amount' => 75, 'date' => '2026-07-03']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        // food: 150, transport: 75, total spent: 225.
        $response->assertSee('225.00');
        $response->assertSee(json_encode(['Food', 'Transport']), false);
        $response->assertSee(json_encode([150, 75]), false);
    }

    public function test_a_user_only_sees_their_own_data_on_the_dashboard(): void
    {
        $this->travelTo(Carbon::create(2026, 7, 15));

        $user = User::factory()->create();
        $other = User::factory()->create();

        Expense::factory()->for($user)->create(['amount' => 100, 'date' => '2026-07-05']);
        Expense::factory()->for($other)->create(['amount' => 99999, 'date' => '2026-07-05']);
        IncomeExpectation::factory()->for($other)->create(['month' => '2026-07-01', 'expected_amount' => 55555]);
        SavingsGoal::factory()->for($other)->create(['month' => '2026-07-01', 'target_amount' => 44444]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('100.00');
        $response->assertDontSee('99,999.00');
        $response->assertDontSee('55,555.00');
        $response->assertDontSee('44,444.00');
        // The user has no income expectation or savings goal of their own
        // for this month, so the prompts should show despite $other's rows existing.
        $response->assertSee("You haven't set expected income for this month yet.", false);
        $response->assertSee("You haven't set a savings goal for this month yet.", false);
    }
}
