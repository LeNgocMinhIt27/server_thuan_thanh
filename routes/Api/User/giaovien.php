<?php

use App\Constants\RoleCode;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum', 'namespace' => 'GiaoVien', "api.access.routeNeedsPermission:" . RoleCode::ADMIN], function () {
    Route::apiResource('giao-vien', 'GiaoVienController');
    Route::post('giao-vien-list', 'GiaoVienController@indexAgGrid');
});

Route::group(['middleware' => 'auth:sanctum', 'namespace' => 'GiaoVien', "api.access.routeNeedsPermission:" . RoleCode::ASSISTANT], function () {
    Route::apiResource('giao-vien', 'GiaoVienController')->except(['store', 'update', 'destroy']);
    Route::post('giao-vien-list', 'GiaoVienController@indexAgGrid');
});

Route::group(['middleware' => 'auth:sanctum', 'namespace' => 'GiaoVien', "api.access.routeNeedsPermission:" . RoleCode::ASSISTANT  . ";" . RoleCode::ADMIN], function () {
    Route::get('giao-vien-detail/{id}', 'GiaoVienController@detail');
});
