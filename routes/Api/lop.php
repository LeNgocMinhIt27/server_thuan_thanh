<?php

use App\Constants\RoleCode;
use App\Http\Controllers\Api\Lop\GiaoVienLopController;
use App\Http\Controllers\Api\Lop\LopHocController;
use App\Http\Controllers\Api\Lop\SinhVienLopController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::group(['middleware' => ['api.access.routeNeedsPermission:' . RoleCode::TEACHER . ';' . RoleCode::ASSISTANT . ';' . RoleCode::ADMIN]], function () {
        Route::post('teacher-lop-list', [GiaoVienLopController::class, 'indexAgGird']);
        Route::get('teacher-lop-list/{id}', [GiaoVienLopController::class, 'show']);
        Route::get('teacher-lop-list/{id}/sinh-viens', [GiaoVienLopController::class, 'indexSinhVien']);
        Route::get('lop-teacher/{id}', [LopHocController::class, 'lopGiaoVien']);
    });
    Route::group(['middleware' => ['api.access.routeNeedsPermission:' . RoleCode::STUDENT]], function () {
        Route::post('student-lop-list', [SinhVienLopController::class, 'indexAgGird']);
        Route::get('student-lop-list/{id}', [SinhVienLopController::class, 'show']);
        Route::get('student-diem-danh-list/{id}', [SinhVienLopController::class, 'diemDanh']);
        Route::get('student-lop-list-item/{id}', [SinhVienLopController::class, 'showItem']);
    });
});

Route::group(['middleware' => 'auth:sanctum', 'namespace' => 'Lop', 'api.access.routeNeedsPermission:' . RoleCode::ADMIN . ';' . RoleCode::ASSISTANT], function () {
    Route::apiResource('lop', 'LopHocController')->except(['index']);
    Route::get('lop/{id}/sinh-viens', 'LopHocSinhVienController@index');
    Route::post('lop-diem-danh-search', 'LopHocController@listLopDiemDanh');
    Route::get('lop-list-student', 'LopHocController@index');
    Route::get('lop/{id}', 'LopHocController@show');
    Route::post('lop-list', 'LopHocController@indexAgGrid');
    Route::post('lop-detail/{id}', 'LopHocController@showDetail');
    Route::post('lop-detail/add-student/{id}', 'LopHocController@addSinhVien');
    Route::post('lop-detail/update-student/{id}', 'LopHocSinhVienController@update');
    Route::post('lop-detail/remove-student/{id}', 'LopHocSinhVienController@destroy');
    Route::put('lop/{id}/diem-y-thuc', 'LopHocSinhVienController@diemYThuc');
    Route::post('lop-list-diem-danh', 'LopHocController@listLopDiemDanh');
});