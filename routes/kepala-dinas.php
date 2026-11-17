<?php

use App\Http\Controllers\KepalaDinas\DashboardController;
use App\Http\Controllers\KepalaDinas\DistributionController;
use App\Http\Controllers\KepalaDinas\SupervisionController;
use Illuminate\Support\Facades\Route;

Route::prefix('kepala-dinas')->name('kepala-dinas.')->middleware(['auth', 'role:kepala_dinas'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('distribution', [DistributionController::class, 'index'])->name('distribution.index');
    Route::get('supervision', [SupervisionController::class, 'index'])->name('supervision.index');
});
