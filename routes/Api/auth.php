<?php



use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Auth'], function () {
    Route::post('web-authenticate', 'AuthenticateController@webAuthenticate');
    Route::group(['middleware' => ['web'], 'prefix' => 'auth'], function () {
        Route::get('getMicrosoftLoginUrl', 'AuthenticateController@redirectToMicrosoftAzure');
        Route::get('loginWithMicrosoftCode', 'AuthenticateController@handleMicrosoftAzureCallback');
    });
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('logout', 'AuthenticateController@logout');
        Route::put('me/password', 'ProfileController@updatePassword');
        Route::get('me', 'ProfileController@me');
        Route::get('check', 'ProfileController@checkToken');
        Route::post('edit/profile', 'ProfileController@updateMe');
    });
});
