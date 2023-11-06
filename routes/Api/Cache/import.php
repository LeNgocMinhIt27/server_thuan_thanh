<?php

use App\Http\Controllers\Api\Import\ImportReadController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['cacheResponse:600', 'auth:sanctum'], 'prefix' => 'cache/import'], function () {
    Route::get('suggest', [ImportReadController::class, 'suggest']);
});
