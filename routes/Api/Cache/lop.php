<?php

use App\Constants\RoleCode;
use App\Http\Controllers\Api\Lop\LopHocController;
use App\Http\Controllers\Api\Lop\LopThiController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'cache', 'middleware' => 'auth:sanctum'], function () {
    Route::get('lop', [LopHocController::class, 'index']);
    Route::get('lop-thi', [LopThiController::class, 'index']);
    Route::post('lop-thi-mon/{id}', [LopThiController::class, 'cacheLopThiMon']);
});
