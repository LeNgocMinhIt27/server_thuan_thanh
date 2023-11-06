<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['cacheResponse:600'], 'namespace' => 'Translation'], function () {
    Route::get('translations/{language}/{file}', 'TranslationController@index');
});
