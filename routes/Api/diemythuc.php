<?php

use App\Constants\RoleCode;
use App\Http\Controllers\Api\Diem\DiemYThucController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum', 'api.access.routeNeedsPermission:' . RoleCode::ADMIN . ";" . RoleCode::ASSISTANT . ";" . RoleCode::TEACHER]], function () {
    Route::post('import/diem-y-thuc', [DiemYThucController::class, 'diemYThucExcel']);
});
