<?php

namespace App\Library\FormData\Writer\Custom;

use App\Helpers\TempDiskHelper;
use App\Library\FormData\Writer\BaseWriter;
use Carbon\Carbon;
use File;
use Storage;

class JsonWriter extends BaseWriter
{
    public function write($datas, $headers, $fileName = 'data', $options = [])
    {
        $full_name_file = TempDiskHelper::setPrefix($fileName . '.json');
        File::put(TempDiskHelper::getPath($full_name_file), json_encode($datas));
        return $full_name_file;
    }
}
