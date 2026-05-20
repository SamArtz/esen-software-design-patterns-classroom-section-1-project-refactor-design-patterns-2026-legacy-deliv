<?php
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\OrderViewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/orders', [OrderViewController::class, 'index'])->name('orders.index');
Route::get('/orders/{order}', [OrderViewController::class, 'show'])->name('orders.show');
