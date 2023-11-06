<?php

/**
 * User Controllers
 */

use App\Constants\RoleCode;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'System'], function () {
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('checkPassword', 'UserController@checkPassword');
        Route::group(['middleware' => ['api.access.routeNeedsPermission:' . RoleCode::ADMIN]], function () {
            Route::apiResource('users', 'UserController');
            Route::post('/users/{id}/reset-password', 'UserController@updatePassword');
            Route::post('users-list', 'UserController@indexAgGird');
            Route::put('/users/{id}/active', 'UserController@activeUser');
            Route::put('/users/{id}/inactive', 'UserController@inactiveUser');
            Route::post('/editAdmin/profile', 'UserController@updateAdmin');
        });
    });
});
