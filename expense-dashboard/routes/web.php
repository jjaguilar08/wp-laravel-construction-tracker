<?php

use App\Http\Controllers\ConstructionLogController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index']);

Route::get('/logs', [ConstructionLogController::class, 'index']);
