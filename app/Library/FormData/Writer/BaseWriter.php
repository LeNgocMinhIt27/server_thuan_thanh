<?php

namespace App\Library\FormData\Writer;

use ErrorException;
use Illuminate\Support\Collection;

class BaseWriter
{
    public function write(Collection $datas, $headers, $fileName = 'data', $options = [])
    {
        throw new ErrorException('Must implement write method');
    }
}
