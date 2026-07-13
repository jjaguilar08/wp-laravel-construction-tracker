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

        IncomeExpectation::factory()->for($user)->create(['month' => '2026-07-01', 'expected_amount' => 5000]);
        IncomeExpectation::factory()->for($other)->create(['month' => '2026-07-01', 'expected_amount' => 9999]);

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
            'month' => '2026-07-01 00:00:00',
            'expected_amount' => 5000,
        ]);
    }

    public function test_only_one_entry_is_allowed_per_user_per_month(): void
    {
        $user = User::factory()->create();
        IncomeExpectation::factory()->for($user)->create(['month' => '2026-07-01']);

        $response = $this->actingAs($user)->post('/income-expectations', [
            'month' => '2026-07',
            'expected_amount' => '1000',
        ]);

        $response->assertSessionHasErrors('month');
        $this->assertDatabaseCount('income_expectations', 1);
    }

    public function test_a_user_can_update_their_own_entry(): void
    {
        $user = User::factory()->create();
        $entry = IncomeExpectation::factory()->for($user)->create(['month' => '2026-07-01', 'expected_amount' => 5000]);

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
        $entry = IncomeExpectation::factory()->for($user)->create(['month' => '2026-07-01', 'expected_amount' => 5000]);

        $response = $this->actingAs($user)->put("/income-expectations/{$entry->id}", [
            'month' => '2026-07',
            'expected_amount' => '7500',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('7500.00', $entry->fresh()->expected_amount);
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
}
