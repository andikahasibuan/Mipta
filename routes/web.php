<?php

use App\Http\Controllers\HelpController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);
Route::get('pusat-bantuan', HelpController::class)->name('help');

require __DIR__.'/auth.php';
require __DIR__.'/notifications.php';
require __DIR__.'/admin.php';
require __DIR__.'/kepala-dinas.php';
require __DIR__.'/uptd.php';
require __DIR__.'/distributor.php';
