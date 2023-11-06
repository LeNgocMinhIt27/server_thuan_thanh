<?php

use App\Constants\RoleCode;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'config'], function () {
    Route::post('hust', [SettingController::class, 'updateHust']);
    Route::post('dong-diem-danh', [SettingController::class, 'ngayDongDiemDanh']);
    Route::post('list-dong-diem-danh', [SettingController::class, 'listDongDiemDanh']);
    Route::put('dong-diem-danh/{id}', [SettingController::class, 'updateDongDiemDanh']);
    Route::delete('dong-diem-danh/{id}', [SettingController::class, 'destroyDongDiemDanh']);
    Route::post('lich-hoc', [SettingController::class, 'timKiemLichHoc']);
    Route::get('lich-hoc', [SettingController::class, 'timKiemLichHoc']);
    Route::group([
        'middleware' => ['api.access.routeNeedsPermission:' . RoleCode::ADMIN . ';' . RoleCode::ASSISTANT, 'auth:sanctum']
    ], function () {
        Route::get('setting', [SettingController::class, 'index']);
    });
});
