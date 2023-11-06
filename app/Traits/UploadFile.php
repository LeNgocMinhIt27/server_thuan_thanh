<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

trait UploadFile
{
    public static function updateFile($request, $models, $field, $isCreate = false, $fieldRequest = 'file')
    {
        $pathStorage = "/storage/data-editor/";
        $url = $request[$field];
        if ($request->hasFile($fieldRequest)) {
            if (!$isCreate) {
                $path = public_path($models[$field]);
                if (File::exists($path)) {
                    File::delete($path);
                }
            }
            $file = $request->file($fieldRequest);
            $ext = $file->extension();
            $nameI = uniqid() .  ".$ext";

            $url = $pathStorage . $nameI;
            $file->move(public_path($pathStorage), $nameI);
        } else {
            if (empty($url) && !empty($models[$field])) {
                $path = public_path($models[$field]);
                if (File::exists($path)) {
                    return;
                }
            }
        }
        return $url;
    }
    public function uploadFile($request, $field, $path)
    {
        $pathStorage = "/storage/$path/";
        $url = "";
        if ($request->hasFile($field)) {
            $file = $request->file($field);
            $ext = $file->extension();
            $nameI = uniqid() .  ".$ext";

            $url = $pathStorage . $nameI;
            $file->move(public_path($pathStorage), $nameI);
            return $url;
        }
        return $url;
    }

    public function removeFile($path)
    {
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
