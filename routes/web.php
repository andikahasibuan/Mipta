<?php

use App\Enums\Role;
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
use App\Http\Controllers\Distributor\DashboardController as DistributorDashboardController;
use App\Http\Controllers\Distributor\DistributionReportController as DistributorDistributionReportController;
use App\Http\Controllers\Distributor\ProfileController as DistributorProfileController;
use App\Http\Controllers\Distributor\ReportHistoryController as DistributorReportHistoryController;
use App\Http\Controllers\Distributor\StoreController as DistributorStoreController;
use App\Http\Controllers\KepalaDinas\DashboardController as KepalaDinasDashboardController;
use App\Http\Controllers\KepalaDinas\DistributionController as KepalaDinasDistributionController;
use App\Http\Controllers\KepalaDinas\ReportController as KepalaDinasReportController;
use App\Http\Controllers\KepalaDinas\SupervisionController as KepalaDinasSupervisionController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    if (! $user) {
        return redirect()->route('login');
    }

    if ($user->role === Role::Distributor && $user->distributor_id === null) {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'identifier' => 'Akun distributor belum dikaitkan ke PT. Hubungi administrator.',
        ]);
    }

    $home = $user->role->homeRoute();

    if ($home === null) {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }

    return redirect()->route($home);
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])
    ->middleware('auth')
    ->name('notifications.read-all');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

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

    Route::get('uptd-contributors', [UptdContributorController::class, 'index'])->name('uptd-contributors.index');
    Route::get('uptd-contributors/create', [UptdContributorController::class, 'create'])->name('uptd-contributors.create');
    Route::post('uptd-contributors', [UptdContributorController::class, 'store'])->name('uptd-contributors.store');
    Route::get('uptd-contributors/{contributor}/edit', [UptdContributorController::class, 'edit'])->name('uptd-contributors.edit');
    Route::put('uptd-contributors/{contributor}', [UptdContributorController::class, 'update'])->name('uptd-contributors.update');
    Route::delete('uptd-contributors/{contributor}', [UptdContributorController::class, 'destroy'])->name('uptd-contributors.destroy');
    Route::get('distribution', [DistributionController::class, 'index'])->name('distribution.index');
    Route::delete('distribution/{distribution}', [DistributionController::class, 'destroy'])->name('distribution.destroy');
    Route::get('monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('supervision', [SupervisionController::class, 'index'])->name('supervision.index');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
});

Route::prefix('kepala-dinas')->name('kepala-dinas.')->middleware(['auth', 'role:kepala_dinas'])->group(function () {
    Route::get('/', [KepalaDinasDashboardController::class, 'index'])->name('dashboard');
    Route::get('distribution', [KepalaDinasDistributionController::class, 'index'])->name('distribution.index');
    Route::get('supervision', [KepalaDinasSupervisionController::class, 'index'])->name('supervision.index');
    Route::get('reports', [KepalaDinasReportController::class, 'index'])->name('reports.index');
});

Route::prefix('distributor')->name('distributor.')->middleware(['auth', 'role:distributor', 'distributor.linked'])->group(function () {
    Route::get('/', [DistributorDashboardController::class, 'index'])->name('dashboard');
    Route::get('stores', [DistributorStoreController::class, 'index'])->name('stores.index');
    Route::get('stores/create', [DistributorStoreController::class, 'create'])->name('stores.create');
    Route::post('stores', [DistributorStoreController::class, 'store'])->name('stores.store');
    Route::get('stores/{store}/edit', [DistributorStoreController::class, 'edit'])->name('stores.edit');
    Route::put('stores/{store}', [DistributorStoreController::class, 'update'])->name('stores.update');
    Route::get('distribution-reports/create', [DistributorDistributionReportController::class, 'create'])->name('distribution-reports.create');
    Route::post('distribution-reports', [DistributorDistributionReportController::class, 'store'])->name('distribution-reports.store');
    Route::get('distribution-reports/{distribution}/edit', [DistributorDistributionReportController::class, 'edit'])->name('distribution-reports.edit');
    Route::put('distribution-reports/{distribution}', [DistributorDistributionReportController::class, 'update'])->name('distribution-reports.update');
    Route::get('report-history', [DistributorReportHistoryController::class, 'index'])->name('report-history.index');
    Route::delete('report-history/{distribution}', [DistributorReportHistoryController::class, 'destroy'])->name('report-history.destroy');
    Route::get('profile', [DistributorProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [DistributorProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [DistributorProfileController::class, 'updatePassword'])->name('profile.password.update');
});
