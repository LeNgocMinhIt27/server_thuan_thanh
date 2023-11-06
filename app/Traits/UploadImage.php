<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

trait UploadImage
{
    protected $pathStorage = '/storage/images/';
    public function setDefautlImage($model)
    {
        if ($model->getKey()) {
            $filename = $model->getKey() . '-' . "avatar.png";
        } else {
            $filename = time() . '-' . "avatar.png";
        }
        $url = $this->pathStorage . 'default/' . $filename;
        $path = str_replace('storage', 'public', substr($url, 1));
        $avatar = \Avatar::create(strtoupper($model->name))->setTheme('avatar');
        //\Avatar::create(strtoupper($model->name))->getImageObject()->save(public_path($url));
        //$avatar->getImageObject()->save(public_path($url));
        Storage::put($path, $avatar->getImageObject()->stream('png'));
        return $url;
    }
    public function updateImage($request, $models, $field, $fieldRequest = 'file')
    {
        $pathStorage = $this->pathStorage;
        $url = $request[$field];
        if ($request->hasFile($fieldRequest)) {
            $path = public_path($models[$field]);
            if (File::exists($path)) {
                File::delete(public_path($models[$field]));
            }
            $file = $request->file($fieldRequest);
            $nameI = time() . '-' . $file->getClientOriginalName();

            $url = $pathStorage . $nameI;
            $file->move(public_path($pathStorage), $nameI);
        } else {
            if (!empty($models[$field])) {
                $path = public_path($models[$field]);
                if (File::exists($path)) {
                    return;
                }
            }
            $url = $this->setDefautlImage($models);
        }
        return $url;
    }
}
