<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DistributionController;
use App\Http\Controllers\Admin\DistributorController;
use App\Http\Controllers\Admin\MarketController;
use App\Http\Controllers\Admin\RecipientStoreController;
use App\Http\Controllers\Admin\SupervisionController;
use App\Http\Controllers\Admin\UptdContributorController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('users/uptd-contributors', [UptdContributorController::class, 'index'])->name('users.uptd-contributors.index');
    Route::get('users/uptd-contributors/create', [UptdContributorController::class, 'create'])->name('users.uptd-contributors.create');
    Route::post('users/uptd-contributors', [UptdContributorController::class, 'store'])->name('users.uptd-contributors.store');
    Route::get('users/uptd-contributors/{contributor}/edit', [UptdContributorController::class, 'edit'])->name('users.uptd-contributors.edit');
    Route::put('users/uptd-contributors/{contributor}', [UptdContributorController::class, 'update'])->name('users.uptd-contributors.update');
    Route::delete('users/uptd-contributors/{contributor}', [UptdContributorController::class, 'destroy'])->name('users.uptd-contributors.destroy');

    Route::get('distributors', [DistributorController::class, 'index'])->name('distributors.index');
    Route::get('distributors/create', [DistributorController::class, 'create'])->name('distributors.create');
    Route::post('distributors', [DistributorController::class, 'store'])->name('distributors.store');
    Route::get('distributors/{distributor}/edit', [DistributorController::class, 'edit'])->name('distributors.edit');
    Route::put('distributors/{distributor}', [DistributorController::class, 'update'])->name('distributors.update');
    Route::delete('distributors/{distributor}', [DistributorController::class, 'destroy'])->name('distributors.destroy');

    Route::get('markets', [MarketController::class, 'index'])->name('markets.index');
    Route::get('markets/create', [MarketController::class, 'create'])->name('markets.create');
    Route::post('markets', [MarketController::class, 'store'])->name('markets.store');
    Route::get('markets/{market}/edit', [MarketController::class, 'edit'])->name('markets.edit');
    Route::put('markets/{market}', [MarketController::class, 'update'])->name('markets.update');
    Route::delete('markets/{market}', [MarketController::class, 'destroy'])->name('markets.destroy');

    Route::get('recipient-stores', [RecipientStoreController::class, 'index'])->name('recipient-stores.index');
    Route::get('recipient-stores/create', [RecipientStoreController::class, 'create'])->name('recipient-stores.create');
    Route::post('recipient-stores', [RecipientStoreController::class, 'store'])->name('recipient-stores.store');
    Route::get('recipient-stores/{store}/edit', [RecipientStoreController::class, 'edit'])->name('recipient-stores.edit');
    Route::put('recipient-stores/{store}', [RecipientStoreController::class, 'update'])->name('recipient-stores.update');
    Route::delete('recipient-stores/{store}', [RecipientStoreController::class, 'destroy'])->name('recipient-stores.destroy');

    Route::get('distribution', [DistributionController::class, 'index'])->name('distribution.index');
    Route::get('supervision', [SupervisionController::class, 'index'])->name('supervision.index');
});
