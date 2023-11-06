<?php

namespace App\Helpers;

use Storage;

class TempDiskHelper
{
    public static function setPrefix($path)
    {
        $perfix = time();
        if (!Storage::disk('temp')->exists($perfix)) {
            Storage::disk('temp')->makeDirectory($perfix);
        }
        return "$perfix/$path";
    }
    public static function getPath($path)
    {
        return Storage::disk('temp')->path("$path");
    }
    public static function put($path, $content)
    {
        return Storage::disk('temp')->put($path, $content);
    }
}
