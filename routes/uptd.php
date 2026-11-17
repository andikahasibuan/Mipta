<?php

use App\Http\Controllers\Uptd\ContributorController;
use App\Http\Controllers\Uptd\DashboardController;
use App\Http\Controllers\Uptd\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:uptd_pasar'])->group(function () {
    Route::get('uptd/contributors', [ContributorController::class, 'index'])->name('uptd.contributors.index');
    Route::post('uptd/contributors', [ContributorController::class, 'select'])->name('uptd.contributors.select');
    Route::get('uptd/contributors/{contributor}/login', [ContributorController::class, 'showLogin'])->name('uptd.contributors.login');
    Route::post('uptd/contributors/{contributor}/login', [ContributorController::class, 'login'])->name('uptd.contributors.login.store');
});

Route::prefix('uptd')->name('uptd.')->middleware(['auth', 'role:uptd_pasar', 'uptd.linked'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('reports/{report}/edit', [ReportController::class, 'edit'])->name('reports.edit');
    Route::put('reports/{report}', [ReportController::class, 'update'])->name('reports.update');
    Route::delete('reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
});
