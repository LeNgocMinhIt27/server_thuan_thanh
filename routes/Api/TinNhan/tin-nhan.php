<?php

use App\Constants\RoleCode;
use App\Http\Controllers\Api\TinNhan\TinNhanController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum', 'api.access.routeNeedsPermission:' . RoleCode::ADMIN]], function () {
    Route::post('tin-nhan', [TinNhanController::class, 'index']);
});

Route::post('sms-banks', [TinNhanController::class, 'store']);
