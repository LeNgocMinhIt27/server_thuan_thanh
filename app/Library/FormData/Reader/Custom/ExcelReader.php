<?php

namespace App\Library\FormData\Reader\Custom;

use App\Imports\TableGetData;
use Maatwebsite\Excel\Facades\Excel;

class ExcelReader implements IHandleFile
{
    protected $files;
    protected $records;
    protected $headers;
    public function __construct(array $files)
    {
        $this->files = $files;
        $excel = Excel::toArray(new TableGetData, $files[0]);
        $data = $excel[0]; // only first sheet
        $this->headers = $data[0];
        $this->records = $data;
    }
    public function getFields()
    {
        return $this->headers;
    }
    public function getRecords()
    {
        $data = [];
        foreach ($this->records as $index => $record) {
            try {
                if ($index === 0) {
                    continue;
                }
                $data[] = array_combine($this->headers, $record);
            } catch (\Throwable $th) {
            }
        }
        return $data;
    }
    public function getTotal()
    {
        return count($this->records);
    }
}
