<?php

namespace App\Helpers\System;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    private static function genKey($type, $key_of_type)
    {
        return config('app.key') . '-' . $type . '-' . $key_of_type;
    }
    public static function put($type = 'default', $key_of_type, $value, $expires_at = 480)
    {
        return Cache::put(CacheHelper::genKey($type, $key_of_type), $value, $expires_at);
    }
    public static function get($type = 'default', $key_of_type)
    {
        return Cache::get(CacheHelper::genKey($type, $key_of_type));
    }
    public static function forever($type = 'default', $key_of_type, $value)
    {
        return Cache::forever(CacheHelper::genKey($type, $key_of_type), $value);
    }

    public static function forget($type = 'default', $key_of_type)
    {
        return Cache::forget($key_of_type);
    }
    public static function flush($type = 'default')
    {
        return Cache::flush();
    }
    public static function getDataCache($type = 'default', $key_of_type, callable $cb)
    {
        if (Cache::has(CacheHelper::genKey($type, $key_of_type))) {
            return CacheHelper::get($type, $key_of_type);
        }
        $data = $cb();
        CacheHelper::put($type, $key_of_type, $data);
        return $data;
    }
}
