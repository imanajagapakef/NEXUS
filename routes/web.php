<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Expense\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () { return view('welcome'); });

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::post('/select-organization', [AuthController::class, 'selectOrganization'])->middleware('auth');

Route::middleware(['auth', \App\Http\Middleware\EnsureOrganizationContext::class])->group(function () {
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::get('/expenses/{id}', [ExpenseController::class, 'show']);
    Route::post('/expenses/{id}/submit', [ExpenseController::class, 'submit']);
    Route::post('/expenses/{id}/review', [ExpenseController::class, 'review']);
    Route::post('/expenses/{id}/approve', [ExpenseController::class, 'approve']);
    Route::post('/expenses/{id}/reject', [ExpenseController::class, 'reject']);
    Route::post('/expenses/{id}/complete', [ExpenseController::class, 'complete']);
});
