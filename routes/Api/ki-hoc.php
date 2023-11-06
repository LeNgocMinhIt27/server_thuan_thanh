<?php

use App\Http\Controllers\Api\KiHocController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['cacheResponse:600']], function () {
    Route::get('ki-hocs', [KiHocController::class, 'index']);
});
