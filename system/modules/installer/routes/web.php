<?php

use Illuminate\Support\Facades\Route;
use Installer\Controllers\InstallController;

Route::get('install', [InstallController::class, 'home'])->name('install.home');
Route::post('install', [InstallController::class, 'install'])->name('install.install');
Route::post('install/migrate', [InstallController::class, 'migrate'])->name('install.migrate');
