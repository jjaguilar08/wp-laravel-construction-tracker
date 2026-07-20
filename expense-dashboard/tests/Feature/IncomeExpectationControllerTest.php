<?php

namespace Tests\Feature;

use App\Models\IncomeExpectation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeExpectationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/income-expectations')->assertRedirect('/login');
    }

    public function test_index_only_lists_the_authenticated_users_entries(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        IncomeExpectation::factory()->for($user)->create(['period_start' => '2026-07-01', 'expected_amount' => 5000]);
        IncomeExpectation::factory()->for($other)->create(['period_start' => '2026-07-01', 'expected_amount' => 9999]);

        $response = $this->actingAs($user)->get('/income-expectations');

        $response->assertOk();
        $response->assertSee('5,000.00');
        $response->assertDontSee('9,999.00');
    }

    public function test_expected_income_can_be_set_for_a_month(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/income-expectations', [
            'month' => '2026-07',
            'expected_amount' => '5000',
        ]);

        $response->assertRedirect('/income-expectations');
        $this->assertDatabaseHas('income_expectations', [
            'user_id' => $user->id,
            'period_start' => '2026-07-01 00:00:00',
            'expected_amount' => 5000,
        ]);
    }

    public function test_setting_expected_income_rejects_a_negative_amount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/income-expectations', [
            'month' => '2026-07',
            'expected_amount' => '-100',
        ]);

        $response->assertSessionHasErrors('expected_amount');
        $this->assertDatabaseCount('income_expectations', 0);
    }

    public function test_setting_expected_income_accepts_zero(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/income-expectations', [
            'month' => '2026-07',
            'expected_amount' => '0',
        ]);

        $response->assertRedirect('/income-expectations');
        $this->assertDatabaseHas('income_expectations', ['user_id' => $user->id, 'expected_amount' => 0]);
    }

    public function test_setting_expected_income_rejects_an_oversized_amount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/income-expectations', [
            'month' => '2026-07',
            'expected_amount' => '100000000.00',
        ]);

        $response->assertSessionHasErrors('expected_amount');
        $this->assertDatabaseCount('income_expectations', 0);
    }

    public function test_setting_expected_income_accepts_the_maximum_valid_amount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/income-expectations', [
            'month' => '2026-07',
            'expected_amount' => '99999999.99',
        ]);

        $response->assertRedirect('/income-expectations');
        $this->assertDatabaseHas('income_expectations', ['user_id' => $user->id, 'expected_amount' => 99999999.99]);
    }

    public function test_setting_expected_income_rejects_more_than_two_decimal_places(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/income-expectations', [
            'month' => '2026-07',
            'expected_amount' => '42.995',
        ]);

        $response->assertSessionHasErrors('expected_amount');
        $this->assertDatabaseCount('income_expectations', 0);
    }

    public function test_setting_expected_income_accepts_exactly_two_decimal_places(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/income-expectations', [
            'month' => '2026-07',
            'expected_amount' => '42.99',
        ]);

        $response->assertRedirect('/income-expectations');
        $this->assertDatabaseHas('income_expectations', ['user_id' => $user->id, 'expected_amount' => 42.99]);
    }

    public function test_setting_expected_income_rejects_scientific_notation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/income-expectations', [
            'month' => '2026-07',
            'expected_amount' => '1e2',
        ]);

        $response->assertSessionHasErrors('expected_amount');
        $this->assertDatabaseCount('income_expectations', 0);
    }

    public function test_a_malformed_month_value_is_rejected(): void
    {
        // Bypasses the <input type="month"> picker to POST a raw, invalid
        // value directly. Regression guard: Laravel's date_format rule
        // round-trips the parsed date and compares it back against the
        // original string, so an out-of-range month like "13" must not
        // silently roll over into the following year instead of failing.
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/income-expectations', [
            'month' => '2026-13',
            'expected_amount' => '1000',
        ]);

        $response->assertSessionHasErrors('month');
        $this->assertDatabaseCount('income_expectations', 0);
    }

    public function test_only_one_entry_is_allowed_per_user_per_month(): void
    {
        $user = User::factory()->create();
        IncomeExpectation::factory()->for($user)->create(['period_start' => '2026-07-01']);

        $response = $this->actingAs($user)->post('/income-expectations', [
            'month' => '2026-07',
            'expected_amount' => '1000',
        ]);

        $response->assertSessionHasErrors('month');
        $this->assertDatabaseCount('income_expectations', 1);
    }

    public function test_a_custom_cycle_start_day_resolves_the_month_picker_to_the_correct_period_start(): void
    {
        $user = User::factory()->create(['cycle_start_day' => 20]);

        $response = $this->actingAs($user)->post('/income-expectations', [
            'month' => '2026-07',
            'expected_amount' => '1000',
        ]);

        $response->assertRedirect('/income-expectations');
        // "July" on a cycle that starts on the 20th means the period that
        // starts Jul 20, not the 1st.
        $this->assertDatabaseHas('income_expectations', [
            'user_id' => $user->id,
            'period_start' => '2026-07-20 00:00:00',
        ]);
    }

    public function test_another_users_cycle_start_day_does_not_affect_this_users_uniqueness_check(): void
    {
        $user = User::factory()->create(['cycle_start_day' => 1]);
        $other = User::factory()->create(['cycle_start_day' => 20]);

        // $other's "July" resolves to period_start 2026-07-20; if that
        // leaked into $user's uniqueness check somehow, it would never
        // collide with $user's own calendar-month period_start of
        // 2026-07-01 anyway - this confirms $user can still set their own
        // July entry without being blocked by $other's row.
        IncomeExpectation::factory()->for($other)->create(['period_start' => '2026-07-20']);

        $response = $this->actingAs($user)->post('/income-expectations', [
            'month' => '2026-07',
            'expected_amount' => '1000',
        ]);

        $response->assertRedirect('/income-expectations');
        $this->assertDatabaseHas('income_expectations', [
            'user_id' => $user->id,
            'period_start' => '2026-07-01 00:00:00',
        ]);
    }

    public function test_a_user_can_update_their_own_entry(): void
    {
        $user = User::factory()->create();
        $entry = IncomeExpectation::factory()->for($user)->create(['period_start' => '2026-07-01', 'expected_amount' => 5000]);

        $response = $this->actingAs($user)->put("/income-expectations/{$entry->id}", [
            'month' => '2026-07',
            'expected_amount' => '6000',
        ]);

        $response->assertRedirect('/income-expectations');
        $this->assertSame('6000.00', $entry->fresh()->expected_amount);
    }

    public function test_updating_an_entry_can_keep_the_same_month(): void
    {
        // Regression guard: the unique-month check must ignore the entry being updated.
        $user = User::factory()->create();
        $entry = IncomeExpectation::factory()->for($user)->create(['period_start' => '2026-07-01', 'expected_amount' => 5000]);

        $response = $this->actingAs($user)->put("/income-expectations/{$entry->id}", [
            'month' => '2026-07',
            'expected_amount' => '7500',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('7500.00', $entry->fresh()->expected_amount);
    }

    public function test_a_user_cannot_view_another_users_edit_form(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $entry = IncomeExpectation::factory()->for($owner)->create();

        $response = $this->actingAs($intruder)->get("/income-expectations/{$entry->id}/edit");

        $response->assertForbidden();
    }

    public function test_a_user_cannot_update_another_users_entry(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $entry = IncomeExpectation::factory()->for($owner)->create(['expected_amount' => 5000]);

        $response = $this->actingAs($intruder)->put("/income-expectations/{$entry->id}", [
            'month' => '2026-08',
            'expected_amount' => '1',
        ]);

        $response->assertForbidden();
        $this->assertSame('5000.00', $entry->fresh()->expected_amount);
    }

    public function test_a_user_can_delete_their_own_entry(): void
    {
        $user = User::factory()->create();
        $entry = IncomeExpectation::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete("/income-expectations/{$entry->id}");

        $response->assertRedirect('/income-expectations');
        $this->assertDatabaseMissing('income_expectations', ['id' => $entry->id]);
    }

    public function test_a_user_cannot_delete_another_users_entry(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $entry = IncomeExpectation::factory()->for($owner)->create();

        $response = $this->actingAs($intruder)->delete("/income-expectations/{$entry->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('income_expectations', ['id' => $entry->id]);
    }

    public function test_deleting_a_user_cascades_to_their_income_expectations(): void
    {
        $user = User::factory()->create();
        $entry = IncomeExpectation::factory()->for($user)->create();

        $user->delete();

        $this->assertDatabaseMissing('income_expectations', ['id' => $entry->id]);
    }
}
