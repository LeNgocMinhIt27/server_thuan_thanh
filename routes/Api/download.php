<?php



use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'download'], function () {
    Route::post('chart',  'DownloadController@downloadChart');
    Route::get('data/{link}',  'DownloadController@downloadFile');
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('excel',  'DownloadController@downloadExcel');
    });
});
