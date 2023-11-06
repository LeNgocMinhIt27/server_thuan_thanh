<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TinNhan\PkSmsBankController;

Route::group([
    'namespace' => 'Api',
], function () {
    includeRouteFiles(__DIR__ . '/Api/');
});
