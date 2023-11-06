<?php

use App\Constants\RoleCode;
use App\Http\Controllers\Api\QuenMatKhau\QuenMatKhauController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => []], function () {
    Route::post('quen-mat-khau', [QuenMatKhauController::class, 'postEmail']);
    Route::post('kiem-tra-token', [QuenMatKhauController::class, 'checkToken']);
    Route::post('luu-mat-khau-moi', [QuenMatKhauController::class, 'resetPassword']);

});