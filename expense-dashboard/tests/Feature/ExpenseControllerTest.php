<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/expenses')->assertRedirect('/login');
    }

    public function test_index_only_lists_the_authenticated_users_expenses(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Expense::factory()->for($user)->create(['category' => 'food', 'notes' => 'Mine']);
        Expense::factory()->for($other)->create(['category' => 'food', 'notes' => 'Not mine']);

        $response = $this->actingAs($user)->get('/expenses');

        $response->assertOk();
        $response->assertSee('Mine');
        $response->assertDontSee('Not mine');
    }

    public function test_an_expense_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/expenses', [
            'amount' => '42.50',
            'category' => 'food',
            'date' => '2026-07-01',
            'notes' => 'Groceries',
        ]);

        $response->assertRedirect('/expenses');
        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'amount' => 42.50,
            'category' => 'food',
            'notes' => 'Groceries',
        ]);
    }

    public function test_creating_an_expense_requires_a_valid_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/expenses', [
            'amount' => '10',
            'category' => 'not-a-real-category',
            'date' => '2026-07-01',
        ]);

        $response->assertSessionHasErrors('category');
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_category_validation_is_case_sensitive(): void
    {
        // The `in:` rule and the DB enum are both lowercase; a wrong-case
        // value must not slip through as if it were a valid category.
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/expenses', [
            'amount' => '10',
            'category' => 'FOOD',
            'date' => '2026-07-01',
        ]);

        $response->assertSessionHasErrors('category');
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_creating_an_expense_rejects_a_zero_amount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/expenses', [
            'amount' => '0.00',
            'category' => 'food',
            'date' => '2026-07-01',
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_creating_an_expense_accepts_the_minimum_valid_amount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/expenses', [
            'amount' => '0.01',
            'category' => 'food',
            'date' => '2026-07-01',
        ]);

        $response->assertRedirect('/expenses');
        $this->assertDatabaseHas('expenses', ['user_id' => $user->id, 'amount' => 0.01]);
    }

    public function test_creating_an_expense_rejects_a_negative_amount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/expenses', [
            'amount' => '-5',
            'category' => 'food',
            'date' => '2026-07-01',
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_creating_an_expense_rejects_an_oversized_amount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/expenses', [
            'amount' => '100000000.00',
            'category' => 'food',
            'date' => '2026-07-01',
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_creating_an_expense_accepts_the_maximum_valid_amount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/expenses', [
            'amount' => '99999999.99',
            'category' => 'food',
            'date' => '2026-07-01',
        ]);

        $response->assertRedirect('/expenses');
        $this->assertDatabaseHas('expenses', ['user_id' => $user->id, 'amount' => 99999999.99]);
    }

    public function test_creating_an_expense_rejects_more_than_two_decimal_places(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/expenses', [
            'amount' => '42.995',
            'category' => 'food',
            'date' => '2026-07-01',
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_creating_an_expense_accepts_exactly_two_decimal_places(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/expenses', [
            'amount' => '42.99',
            'category' => 'food',
            'date' => '2026-07-01',
        ]);

        $response->assertRedirect('/expenses');
        $this->assertDatabaseHas('expenses', ['user_id' => $user->id, 'amount' => 42.99]);
    }

    public function test_creating_an_expense_rejects_scientific_notation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/expenses', [
            'amount' => '1e2',
            'category' => 'food',
            'date' => '2026-07-01',
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_a_spoofed_user_id_in_the_request_is_ignored(): void
    {
        // The create form has no user_id field, but nothing stops a
        // crafted request from including one - it must never let an
        // attacker attach an expense to someone else's account.
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->post('/expenses', [
            'user_id' => $other->id,
            'amount' => '10',
            'category' => 'food',
            'date' => '2026-07-01',
        ]);

        $this->assertDatabaseHas('expenses', ['user_id' => $user->id, 'amount' => 10]);
        $this->assertDatabaseMissing('expenses', ['user_id' => $other->id]);
    }

    public function test_a_user_can_update_their_own_expense(): void
    {
        $user = User::factory()->create();
        $expense = Expense::factory()->for($user)->create(['amount' => 10]);

        $response = $this->actingAs($user)->put("/expenses/{$expense->id}", [
            'amount' => '99.99',
            'category' => $expense->category,
            'date' => $expense->date->format('Y-m-d'),
            'notes' => 'Updated',
        ]);

        $response->assertRedirect('/expenses');
        $this->assertSame('99.99', $expense->fresh()->amount);
        $this->assertSame('Updated', $expense->fresh()->notes);
    }

    public function test_a_user_cannot_view_another_users_edit_form(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $expense = Expense::factory()->for($owner)->create();

        $response = $this->actingAs($intruder)->get("/expenses/{$expense->id}/edit");

        $response->assertForbidden();
    }

    public function test_a_user_cannot_update_another_users_expense(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $expense = Expense::factory()->for($owner)->create(['amount' => 10]);

        $response = $this->actingAs($intruder)->put("/expenses/{$expense->id}", [
            'amount' => '999',
            'category' => $expense->category,
            'date' => $expense->date->format('Y-m-d'),
        ]);

        $response->assertForbidden();
        $this->assertSame('10.00', $expense->fresh()->amount);
    }

    public function test_a_user_cannot_delete_another_users_expense(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $expense = Expense::factory()->for($owner)->create();

        $response = $this->actingAs($intruder)->delete("/expenses/{$expense->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('expenses', ['id' => $expense->id]);
    }

    public function test_a_user_can_delete_their_own_expense(): void
    {
        $user = User::factory()->create();
        $expense = Expense::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete("/expenses/{$expense->id}");

        $response->assertRedirect('/expenses');
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_deleting_a_user_cascades_to_their_expenses(): void
    {
        $user = User::factory()->create();
        $expense = Expense::factory()->for($user)->create();

        $user->delete();

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }
}
