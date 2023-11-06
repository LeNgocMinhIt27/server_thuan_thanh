<?php

namespace App\Library\FormData\Writer\Custom;

use App\Constants\LayerType;
use App\Exports\DynamicTableExport;
use App\Exports\ExportLayer;
use App\Helpers\Data\LayerHelper;
use App\Helpers\ReportHelper;
use App\Helpers\TempDiskHelper;
use App\Library\FormData\Writer\BaseWriter;
use App\Models\GroupOfLayer;
use Carbon\Carbon;
use DB;
use Excel;

use function Symfony\Component\String\b;

class ExcelWriter extends BaseWriter
{
    public function write($data, $headers, $name = 'data',  $options = [])
    {
        if (count($data) < 1) {
            abort(500, 'Không có dữ liệu');
        }
        $title = $options['title'] ?? $name;
        $sheet_name = $options['sheet_name'] ?? $name;
        $file_name = $name;
        $full_name_file = TempDiskHelper::setPrefix($file_name . '.xlsx');
        Excel::store(new DynamicTableExport(
            $sheet_name,
            $data,
            $headers,
            function ($data) use ($headers, $options) {
                if (isset($options['is_origin']) && $options['is_origin']) {
                    return $data;
                }
                $item = [];
                foreach ($headers as $key => $header) {
                    $key_value = $header['value'] ?? $header;
                    $value = \Arr::get($data, $key_value, '');
                    if (is_string($value)) {
                        $value = strip_tags(\Arr::get($data, $key_value, ''));
                    }
                    $item[] = $value;
                }

                return $item;
            },
            $title
        ), $full_name_file, 'temp');
        return $full_name_file;
    }
}
