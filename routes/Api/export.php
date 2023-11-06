<?php

use App\Constants\RoleCode;
use App\Http\Controllers\Api\Export\ExportExcelController;
use App\Http\Controllers\Api\Export\ExportPdfController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'export', 'middleware' => ['auth:sanctum', 'api.access.routeNeedsPermission:' . RoleCode::ADMIN . ";" . RoleCode::ASSISTANT . ";" . RoleCode::TEACHER]], function () {
    Route::post('lop-sinh-vien/{id}/sinh-viens',  [ExportPdfController::class, 'exportLopSv']);
    Route::post('lop-li-thuyet/{id}/lop-lt',  [ExportPdfController::class, 'exportLopLt']);
    Route::post('lop-sinh-vien/{id}/excel',  [ExportExcelController::class, 'exportDiemDanh']);
    Route::post('sinh-vien-lop/{id}/excel',  [ExportExcelController::class, 'exportSinhVien']);
    Route::post('lop-sinh-vien-all/{id}/excel',  [ExportExcelController::class, 'exportDiemDanhAll']);
    Route::post('sinh-vien-lop-all/{id}/excel',  [ExportExcelController::class, 'exportSinhVienAll']);
    Route::post('diem-thanh-tich/{id}/pdf',  [ExportPdfController::class, 'exportDiemThanhTich']);
    Route::post('diem-thanh-tich-all/{id}/pdf',  [ExportPdfController::class, 'exportAllDiemThanhTich']);
    Route::post('phuc-khao/excel',  [ExportExcelController::class, 'exportPhucKhao']);
    Route::post('xep-lich-thi-gv/excel',  [ExportExcelController::class, 'exportLopCoiThiGV']);
});
