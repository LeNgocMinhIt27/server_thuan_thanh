<?php

use App\Constants\RoleCode;
use App\Http\Controllers\Api\Diem\BangDiemNhanDienController;
use App\Http\Controllers\Api\Diem\DanhSachBangDiemController;
use App\Http\Controllers\Api\Diem\DiemPhucKhaoController;
use App\Http\Controllers\Api\Diem\NhanDienDiemController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum', 'api.access.routeNeedsPermission:' . RoleCode::ADMIN . ";" . RoleCode::ASSISTANT . ";" . RoleCode::TEACHER]], function () {
    Route::post('bang-diem-list', [DanhSachBangDiemController::class, 'indexAgGrid']);
    Route::post('bang-diem/add', [DanhSachBangDiemController::class, 'store']);
    Route::post('bang-diem/update/{id}', [DanhSachBangDiemController::class, 'update']);
    Route::put('bang-diem/delete/{id}', [DanhSachBangDiemController::class, 'destroy']);
    Route::post('bang-diem/slice-pdf/{id}', [DanhSachBangDiemController::class, 'slicePdf']);
    Route::post('bang-diem/cong-bo-diem/{id}', [DanhSachBangDiemController::class, 'congBoDiem']);
    Route::get('bang-diem/show/{id}', [DanhSachBangDiemController::class, 'show']);
    Route::post('bang-diem/add/excel', [DanhSachBangDiemController::class, 'bangDiemExcel']);
    Route::post('bang-diem/update/{id}/excel', [DanhSachBangDiemController::class, 'updateBangDiemExcel']);
    Route::get('bang-diem/count/{id}', [DanhSachBangDiemController::class, 'countDiemLopThi']);

    Route::get('bang-diem/{id}/chua-nhan-dien', [BangDiemNhanDienController::class, 'getTrangChuaNhanDien']);

    Route::get('bang-diem/{id}/lop-this', [BangDiemNhanDienController::class, 'getLopThi']);
    Route::put('bang-diem/{id}/nhan_diens', [BangDiemNhanDienController::class, 'updateLopThi']);
});

Route::group(['middleware' => ['auth:sanctum', 'api.access.routeNeedsPermission:' . RoleCode::ADMIN . ";" . RoleCode::ASSISTANT]], function () {
    Route::post('diem-phuc-khao-list', [DiemPhucKhaoController::class, 'indexAgGrid']);
});

Route::group(['middleware' => []], function () {
    Route::get('bang-diem/show-pdf/{id}', [DanhSachBangDiemController::class, 'showPdf']);
    Route::get('bang-diem/{id}/nhan-dien', [NhanDienDiemController::class, 'nhanDienDiem']);
    Route::post('bang-diem/{id}/nhan-dien', [NhanDienDiemController::class, 'detectPdf']);
    Route::post('bang-diem-nhan-dien', [NhanDienDiemController::class, 'detectPdfByJob']);
    Route::get('bang-diem/show-slice-pdf/{id}', [DanhSachBangDiemController::class, 'showSlicePdf']);
});
