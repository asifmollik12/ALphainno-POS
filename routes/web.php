<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SsoLoginController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/sso/login', SsoLoginController::class)->name('sso.login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

    Route::get('products/print', [ProductController::class, 'print'])->name('products.print');
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    Route::resource('products', ProductController::class)->except(['show']);
    Route::get('/inventory/shortage', [InventoryController::class, 'shortage'])->name('inventory.shortage');
    Route::get('/inventory/shortage/print', [InventoryController::class, 'shortagePrint'])->name('inventory.shortage.print');
    Route::get('/inventory/shortage/export', [InventoryController::class, 'shortageExport'])->name('inventory.shortage.export');

    Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('suppliers', SupplierController::class)->except(['show']);
    Route::resource('purchase-returns', PurchaseReturnController::class)->only(['index', 'create', 'store']);
    Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'create', 'store']);

    Route::resource('sales', SaleController::class)->only(['index', 'show']);
    Route::get('/sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
    Route::resource('customers', CustomerController::class)->except(['show']);
    Route::resource('sale-returns', SaleReturnController::class)->only(['index', 'create', 'store']);

    Route::get('/accounts/reports/trial-balance', [AccountController::class, 'trialBalance'])->name('accounts.trial-balance');
    Route::get('/accounts/reports/balance-sheet', [AccountController::class, 'balanceSheet'])->name('accounts.balance-sheet');
    Route::get('/accounts/reports/income-statement', [AccountController::class, 'incomeStatement'])->name('accounts.income-statement');
    Route::resource('accounts', AccountController::class)->except(['show', 'destroy']);

    Route::resource('transactions', TransactionController::class)->only(['index', 'create', 'store']);

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/purchase', [ReportController::class, 'purchase'])->name('purchase');
        Route::get('/sale', [ReportController::class, 'sale'])->name('sale');
        Route::get('/supplier', [ReportController::class, 'supplier'])->name('supplier');
        Route::get('/customer', [ReportController::class, 'customer'])->name('customer');
        Route::get('/payment', [ReportController::class, 'payment'])->name('payment');
    });

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
