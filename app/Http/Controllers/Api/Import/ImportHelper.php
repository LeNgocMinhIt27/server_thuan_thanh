<?php

namespace App\Http\Controllers\Api\Import;

class ImportHelper
{
    public static function convertTime(array $fields, $item = [])
    {
        $res = [];
        foreach ($fields as $key => $value) {
            $res[$key] = $item[$value] ?? null;
            if (!empty($res[$key])) {
                $res[$key] = trim($res[$key]);
            }
        }
        return $res;
    }
}
