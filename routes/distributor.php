<?php

use App\Http\Controllers\Distributor\DashboardController;
use App\Http\Controllers\Distributor\DistributionReportController;
use App\Http\Controllers\Distributor\ProfileController;
use App\Http\Controllers\Distributor\ReportHistoryController;
use App\Http\Controllers\Distributor\StoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('distributor')->name('distributor.')->middleware(['auth', 'role:distributor', 'distributor.linked'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('stores', [StoreController::class, 'index'])->name('stores.index');
    Route::get('stores/create', [StoreController::class, 'create'])->name('stores.create');
    Route::post('stores', [StoreController::class, 'store'])->name('stores.store');
    Route::get('stores/{store}/edit', [StoreController::class, 'edit'])->name('stores.edit');
    Route::put('stores/{store}', [StoreController::class, 'update'])->name('stores.update');
    Route::get('distribution-reports/create', [DistributionReportController::class, 'create'])->name('distribution-reports.create');
    Route::post('distribution-reports', [DistributionReportController::class, 'store'])->name('distribution-reports.store');
    Route::get('distribution-reports/{distribution}/edit', [DistributionReportController::class, 'edit'])->name('distribution-reports.edit');
    Route::put('distribution-reports/{distribution}', [DistributionReportController::class, 'update'])->name('distribution-reports.update');
    Route::get('report-history', [ReportHistoryController::class, 'index'])->name('report-history.index');
    Route::delete('report-history/{distribution}', [ReportHistoryController::class, 'destroy'])->name('report-history.destroy');
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});
