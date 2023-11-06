<?php

use App\Constants\RoleCode;
use App\Http\Controllers\Api\Lop\LopThiSinhVienController;
use App\Http\Controllers\Api\Lop\LopHocSinhVienController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum', 'api.access.routeNeedsPermission:' . RoleCode::ADMIN . ';' . RoleCode::ASSISTANT]], function () {
    Route::post('lop-thi-sinh-vien/{id}', [LopThiSinhVienController::class, 'lopThiSinhVien']);
    Route::get('list-sinh-vien-lop-thi/{id}', [LopThiSinhVienController::class, 'listLopThiSinhVien']);
    Route::get('sinh-vien-lop-thi/{id}', [LopHocSinhVienController::class, 'indexSinhVienLopThi']);
    Route::post('add-sinh-vien-lop-thi', [LopThiSinhVienController::class, 'addStudentLopThi']);
    Route::delete('delete-sinh-vien-lop-thi/{id}', [LopThiSinhVienController::class, 'deleteSinhVienLopThi']);
});
