<?php

use App\Constants\RoleCode;
use App\Http\Controllers\Api\Lop\DiemDanhController;
use App\Http\Controllers\Api\Lop\LanDiemDanhController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum', 'api.access.routeNeedsPermission:' . RoleCode::TEACHER . ';' . RoleCode::ADMIN . ';' . RoleCode::ASSISTANT]], function () {
    Route::post('lan-diem-danh', [LanDiemDanhController::class, 'store']);
    Route::get('lan-diem-danh', [LanDiemDanhController::class, 'index']);
    Route::get('lan-diem-danh/{id}', [LanDiemDanhController::class, 'show']);
    Route::put('lan-diem-danh/{id}', [LanDiemDanhController::class, 'update']);
    Route::get('lan-diem-danh/{id}/diem-danhs', [DiemDanhController::class, 'indexForLanDiemDanh']);
    Route::put('diem-danh/{diem_danh_id}', [DiemDanhController::class, 'update']);
    Route::post('thong-bao-dong-mo', [LanDiemDanhController::class, 'thongBaoDiemDanh']);
});

Route::group(['middleware' => ['auth:sanctum', 'api.access.routeNeedsPermission:' . RoleCode::TEACHER . ';' . RoleCode::ADMIN . ';' . RoleCode::ASSISTANT]], function () {
    Route::delete('lan-diem-danh/{id}', [LanDiemDanhController::class, 'delete']);
});
