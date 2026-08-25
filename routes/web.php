<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/trend', [DashboardController::class, 'trendApi'])->name('dashboard.trend');

    // Master Items (Obat & BHP)
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::get('/items/{item}/card', [ItemController::class, 'stockCard'])->name('items.card');

    // Stock Movements (Inbound, Outbound FEFO, Opname)
    Route::get('/movements', [MovementController::class, 'index'])->name('movements.index');
    Route::post('/movements/inbound', [MovementController::class, 'storeInbound'])->name('movements.inbound');
    Route::post('/movements/outbound', [MovementController::class, 'storeOutbound'])->name('movements.outbound');
    Route::post('/movements/opname', [MovementController::class, 'storeOpname'])->name('movements.opname');

    // Fixed Assets
    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
    Route::put('/assets/{asset}', [AssetController::class, 'update'])->name('assets.update');
    Route::delete('/assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');
    Route::post('/assets/{asset}/action', [AssetController::class, 'action'])->name('assets.action');
    Route::get('/assets/{asset}/timeline', [AssetController::class, 'timeline'])->name('assets.timeline');

    // Inter-Warehouse Transfers
    Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
    Route::post('/transfers/{transfer}/receive', [TransferController::class, 'receive'])->name('transfers.receive');
    Route::post('/transfers/{transfer}/cancel', [TransferController::class, 'cancel'])->name('transfers.cancel');

    // Cross-Division Asset Loans
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    Route::post('/loans/{loan}/return', [LoanController::class, 'returnLoan'])->name('loans.return');

    // Reports (LPJ)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/print', [ReportController::class, 'printView'])->name('reports.print');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Super Admin Only
    Route::middleware('role:super_admin')->group(function () {
        // Users Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Organization & Logo Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
