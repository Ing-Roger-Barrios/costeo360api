<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\LicenseController;
use Illuminate\Support\Facades\Auth;

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');