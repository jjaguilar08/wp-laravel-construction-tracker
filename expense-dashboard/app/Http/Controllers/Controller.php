<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Laravel 11+ no longer includes {@see AuthorizesRequests} on the base
 * controller by default; re-added here so `$this->authorize()` is
 * available to the Expense/IncomeExpectation/SavingsGoal controllers.
 */
abstract class Controller
{
    use AuthorizesRequests;
}
