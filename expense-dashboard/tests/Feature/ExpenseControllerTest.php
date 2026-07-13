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
