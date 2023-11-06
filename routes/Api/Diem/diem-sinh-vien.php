<?php

use App\Constants\RoleCode;
use App\Http\Controllers\Api\Diem\DiemController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum', 'namespace' => 'Diem', "api.access.routeNeedsPermission:" . RoleCode::STUDENT], function () {
    Route::post('diem-sinh-vien-list', [DiemController::class, 'indexAgGrid']);
    Route::get('diem-sinh-vien/{id}', 'DiemController@indexDiemSinhVien');
});
