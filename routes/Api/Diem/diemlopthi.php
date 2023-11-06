<?php

use App\Constants\RoleCode;
use App\Http\Controllers\Api\Diem\DiemLopThiController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum', 'api.access.routeNeedsPermission:' . RoleCode::TEACHER . ";" . RoleCode::ASSISTANT . ";" . RoleCode::ADMIN]], function () {
    Route::post('diem-lop-thi-list/{id}', [DiemLopThiController::class, 'indexAgGrid']);
    Route::post('diem-nhan-dien-list/{id}', [DiemLopThiController::class, 'diemNhanDienList']);
    Route::post('diem-lop-thi/save/{id}', [DiemLopThiController::class, 'luuDiem']);
});
