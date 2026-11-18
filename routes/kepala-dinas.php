<?php

use App\Http\Controllers\KepalaDinas\DashboardController;
use App\Http\Controllers\KepalaDinas\DistributionController;
use App\Http\Controllers\KepalaDinas\SupervisionController;
use Illuminate\Support\Facades\Route;

Route::prefix('kepala-dinas')->name('kepala-dinas.')->middleware(['auth', 'role:kepala_dinas'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('distribution', [DistributionController::class, 'index'])->name('distribution.index');
    // Rute Ekspor Penyaluran (Kepala Dinas)
    Route::get('distribution/export/excel', [DistributionController::class, 'exportExcel'])->name('distribution.export.excel');
    Route::get('distribution/export/pdf', [DistributionController::class, 'exportPdf'])->name('distribution.export.pdf');

    Route::get('supervision', [SupervisionController::class, 'index'])->name('supervision.index');
    // Rute Ekspor Pengawasan (Kepala Dinas)
    Route::get('supervision/export/excel', [SupervisionController::class, 'exportExcel'])->name('supervision.export.excel');
    Route::get('supervision/export/pdf', [SupervisionController::class, 'exportPdf'])->name('supervision.export.pdf');
});