<?php

use App\Constants\RoleCode;
use App\Http\Controllers\Api\Diem\DiemLopThiController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum', 'api.access.routeNeedsPermission:' . RoleCode::ADMIN . ";" . RoleCode::TEACHER . ';' . RoleCode::ASSISTANT]], function () {
    Route::post('diem-lop-thi/{id}', [DiemLopThiController::class, 'indexAgGrid']);
});
