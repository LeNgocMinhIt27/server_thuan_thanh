<?php

use App\Constants\RoleCode;
use App\Http\Controllers\Api\BaoLoi\BaoLoiController;

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum', 'api.access.routeNeedsPermission:' . RoleCode::ADMIN . ";" . RoleCode::TEACHER . ';' . RoleCode::ASSISTANT]], function () {
    Route::post('bao-loi-list', [BaoLoiController::class, 'listErrorAll']);  //xem danh sach bao loi
    Route::put('bao-loi-update/{id}', [BaoLoiController::class, 'update']);  //cap nhat trang thai
    Route::delete('bao-loi-id/{id}', [BaoLoiController::class, 'adminDelete']);   //xoa bao loi cua 1 sinh vien

});
Route::group(['middleware' => ['auth:sanctum', 'api.access.routeNeedsPermission:' . RoleCode::STUDENT]], function () {
    Route::post('bao-loi-sv', [BaoLoiController::class, 'listError']);  // danh sach bao loi cua sinh vien do
    Route::post('bao-loi', [BaoLoiController::class, 'store']);                      // Post bao loi
    Route::delete('bao-loi/{id}', [BaoLoiController::class, 'destroy']);               // Xoa 1 bao loi cua list bao loi


});
