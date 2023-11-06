<?php

use App\Http\Controllers\Api\GetKiHienGioController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'cache/config'], function () {
    Route::get('ki-hien-gio', [GetKiHienGioController::class, 'kiHoc']);
    Route::get('hust-hien-gio', [GetKiHienGioController::class, 'index']);
});
