<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DistributionController;
use App\Http\Controllers\Admin\DistributorController;
use App\Http\Controllers\Admin\MarketController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\RecipientStoreController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SupervisionController;
use App\Http\Controllers\Admin\UptdContributorController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.status');
    Route::get('distributors', [DistributorController::class, 'index'])->name('distributors.index');
    Route::get('distributors/create', [DistributorController::class, 'create'])->name('distributors.create');
    Route::post('distributors', [DistributorController::class, 'store'])->name('distributors.store');
    Route::get('distributors/{distributor}/edit', [DistributorController::class, 'edit'])->name('distributors.edit');
    Route::put('distributors/{distributor}', [DistributorController::class, 'update'])->name('distributors.update');
    Route::get('markets', [MarketController::class, 'index'])->name('markets.index');
    Route::get('markets/create', [MarketController::class, 'create'])->name('markets.create');
    Route::post('markets', [MarketController::class, 'store'])->name('markets.store');
    Route::get('markets/{market}/edit', [MarketController::class, 'edit'])->name('markets.edit');
    Route::put('markets/{market}', [MarketController::class, 'update'])->name('markets.update');
    Route::get('recipient-stores', [RecipientStoreController::class, 'index'])->name('recipient-stores.index');
    Route::get('recipient-stores/create', [RecipientStoreController::class, 'create'])->name('recipient-stores.create');
    Route::post('recipient-stores', [RecipientStoreController::class, 'store'])->name('recipient-stores.store');
    Route::get('recipient-stores/{recipientStore}/edit', [RecipientStoreController::class, 'edit'])->name('recipient-stores.edit');
    Route::put('recipient-stores/{recipientStore}', [RecipientStoreController::class, 'update'])->name('recipient-stores.update');
    Route::get('uptd-contributors', [UptdContributorController::class, 'index'])->name('uptd-contributors.index');
    Route::get('distribution', [DistributionController::class, 'index'])->name('distribution.index');
    Route::get('monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('supervision', [SupervisionController::class, 'index'])->name('supervision.index');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
});
