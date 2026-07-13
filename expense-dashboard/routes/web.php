<?php

use App\Http\Controllers\ConstructionLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeExpectationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavingsGoalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Construction Tracker (WP-backed) - kept as-is, will be repurposed later.
Route::get('/construction', [DashboardController::class, 'index'])->name('construction.dashboard');
Route::get('/logs', [ConstructionLogController::class, 'index']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('expenses', ExpenseController::class)->except('show');
    Route::resource('income-expectations', IncomeExpectationController::class)->except('show');
    Route::resource('savings-goals', SavingsGoalController::class)->except('show');
});

require __DIR__.'/auth.php';
