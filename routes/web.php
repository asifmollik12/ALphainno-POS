<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SsoLoginController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/sso/login', SsoLoginController::class)->name('sso.login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', fn () => redirect()->route('pos.index'));
    Route::get('/dashboard', fn () => redirect()->route('pos.index'))->name('dashboard');

    Route::resource('products', ProductController::class)->except(['show']);

    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
});
