<?php

namespace Tests\Feature;

use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavingsGoalControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/savings-goals')->assertRedirect('/login');
    }

    public function test_index_only_lists_the_authenticated_users_goals(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        SavingsGoal::factory()->for($user)->create(['month' => '2026-07-01', 'target_amount' => 500]);
        SavingsGoal::factory()->for($other)->create(['month' => '2026-07-01', 'target_amount' => 999]);

        $response = $this->actingAs($user)->get('/savings-goals');

        $response->assertOk();
        $response->assertSee('500.00');
        $response->assertDontSee('999.00');
    }

    public function test_a_savings_goal_can_be_set_for_a_month(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/savings-goals', [
            'month' => '2026-07',
            'target_amount' => '500',
        ]);

        $response->assertRedirect('/savings-goals');
        $this->assertDatabaseHas('savings_goals', [
            'user_id' => $user->id,
            'month' => '2026-07-01 00:00:00',
            'target_amount' => 500,
        ]);
    }

    public function test_setting_a_savings_goal_rejects_a_negative_amount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/savings-goals', [
            'month' => '2026-07',
            'target_amount' => '-100',
        ]);

        $response->assertSessionHasErrors('target_amount');
        $this->assertDatabaseCount('savings_goals', 0);
    }

    public function test_setting_a_savings_goal_accepts_a_zero_target(): void
    {
        // A $0 target is a valid (if unusual) input - the dashboard's
        // progress-bar math must guard against dividing by this later
        // rather than the form rejecting it outright.
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/savings-goals', [
            'month' => '2026-07',
            'target_amount' => '0',
        ]);

        $response->assertRedirect('/savings-goals');
        $this->assertDatabaseHas('savings_goals', ['user_id' => $user->id, 'target_amount' => 0]);
    }

    public function test_a_malformed_month_value_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/savings-goals', [
            'month' => '2026-13',
            'target_amount' => '500',
        ]);

        $response->assertSessionHasErrors('month');
        $this->assertDatabaseCount('savings_goals', 0);
    }

    public function test_only_one_goal_is_allowed_per_user_per_month(): void
    {
        $user = User::factory()->create();
        SavingsGoal::factory()->for($user)->create(['month' => '2026-07-01']);

        $response = $this->actingAs($user)->post('/savings-goals', [
            'month' => '2026-07',
            'target_amount' => '100',
        ]);

        $response->assertSessionHasErrors('month');
        $this->assertDatabaseCount('savings_goals', 1);
    }

    public function test_a_user_can_update_their_own_goal(): void
    {
        $user = User::factory()->create();
        $goal = SavingsGoal::factory()->for($user)->create(['month' => '2026-07-01', 'target_amount' => 500]);

        $response = $this->actingAs($user)->put("/savings-goals/{$goal->id}", [
            'month' => '2026-07',
            'target_amount' => '750',
        ]);

        $response->assertRedirect('/savings-goals');
        $this->assertSame('750.00', $goal->fresh()->target_amount);
    }

    public function test_updating_a_goal_can_keep_the_same_month(): void
    {
        $user = User::factory()->create();
        $goal = SavingsGoal::factory()->for($user)->create(['month' => '2026-07-01', 'target_amount' => 500]);

        $response = $this->actingAs($user)->put("/savings-goals/{$goal->id}", [
            'month' => '2026-07',
            'target_amount' => '800',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('800.00', $goal->fresh()->target_amount);
    }

    public function test_a_user_cannot_view_another_users_edit_form(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $goal = SavingsGoal::factory()->for($owner)->create();

        $response = $this->actingAs($intruder)->get("/savings-goals/{$goal->id}/edit");

        $response->assertForbidden();
    }

    public function test_a_user_cannot_update_another_users_goal(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $goal = SavingsGoal::factory()->for($owner)->create(['target_amount' => 500]);

        $response = $this->actingAs($intruder)->put("/savings-goals/{$goal->id}", [
            'month' => '2026-08',
            'target_amount' => '1',
        ]);

        $response->assertForbidden();
        $this->assertSame('500.00', $goal->fresh()->target_amount);
    }

    public function test_a_user_can_delete_their_own_goal(): void
    {
        $user = User::factory()->create();
        $goal = SavingsGoal::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete("/savings-goals/{$goal->id}");

        $response->assertRedirect('/savings-goals');
        $this->assertDatabaseMissing('savings_goals', ['id' => $goal->id]);
    }

    public function test_a_user_cannot_delete_another_users_goal(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $goal = SavingsGoal::factory()->for($owner)->create();

        $response = $this->actingAs($intruder)->delete("/savings-goals/{$goal->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('savings_goals', ['id' => $goal->id]);
    }

    public function test_deleting_a_user_cascades_to_their_savings_goals(): void
    {
        $user = User::factory()->create();
        $goal = SavingsGoal::factory()->for($user)->create();

        $user->delete();

        $this->assertDatabaseMissing('savings_goals', ['id' => $goal->id]);
    }
}
