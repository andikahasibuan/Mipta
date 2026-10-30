<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DistributionController;
use App\Http\Controllers\Admin\DistributorController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SupervisionController;
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
    Route::get('distributors', [DistributorController::class, 'index'])->name('distributors.index');
    Route::get('distribution', [DistributionController::class, 'index'])->name('distribution.index');
    Route::get('monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('supervision', [SupervisionController::class, 'index'])->name('supervision.index');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
});
