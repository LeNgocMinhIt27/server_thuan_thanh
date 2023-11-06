<?php

use Illuminate\Support\Facades\Route;
use App\Constants\RoleCode;

Route::group(['middleware' => 'auth:sanctum', 'namespace' => 'User', 'api.access.routeNeedsPermission:' . RoleCode::ADMIN . ';' . RoleCode::ASSISTANT], function () {
    Route::apiResource('sinh-vien', 'SinhVienController')->except(['index']);
    Route::post('sinh-vien-list', 'SinhVienController@indexAgGrid');
    Route::post('sinh-vien-filter', 'SinhVienController@sinhVienFilter');
    Route::get('list-sinh-vien-many/{id}', 'SinhVienController@listSinhVienMany');
});
