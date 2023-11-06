<?php

use App\Constants\RoleCode;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum', 'namespace' => 'PhucKhao', "api.access.routeNeedsPermission:" . RoleCode::ADMIN . ';' . RoleCode::ASSISTANT], function () {
    Route::apiResource('admin/phuc-khao', 'PhucKhaoAdminController');
    Route::post('admin/phuc-khao', 'PhucKhaoAdminController@indexAgGrid'); //danh sách toàn bộ sinh viên phúc khảo
    Route::put('admin/phuc-khao/{id}', 'PhucKhaoAdminController@update'); //update thành đã thanh toán
    Route::delete('admin/phuc-khao/{id}', 'PhucKhaoAdminController@destroy');
});
Route::group(['middleware' => 'auth:sanctum', 'namespace' => 'PhucKhao', "api.access.routeNeedsPermission:" . RoleCode::STUDENT], function () {
    Route::get('sinh-vien-phuc-khao/{id}', 'PhucKhaoStudentController@show');
    Route::delete('sinh-vien-phuc-khao/{id}', 'PhucKhaoStudentController@destroy');
    Route::post('sinh-vien-phuc-khao', 'PhucKhaoStudentController@store');
    Route::post('sinh-vien-phuc-khao-list', 'PhucKhaoStudentController@indexAgGrid'); //danh sách phúc khảo của sinh viên
});
