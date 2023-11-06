<?php

namespace App\Http\Controllers\Api;

use App\Helpers\SettingHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\ResponseCache\Facades\ResponseCache;

class CacheController extends Controller
{
    public function clear()
    {
        ResponseCache::clear();
        SettingHelper::deleteAllFromCache();
        return response()->json([
            'status_code' => 200,
            'message' => 'Cache has been cleared',
        ]);
    }
}
