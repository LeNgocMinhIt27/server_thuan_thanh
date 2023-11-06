<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetExportSinhVien implements WithMultipleSheets
{
    use Exportable;
    private $file = 'diem_danh.xlsx';
    private $data;
    private $sub_data;
    private $arr_title;

    public function __construct($data, $sub_data, $arr_title)
    {
        $this->file = 'diem_danh.xlsx';
        $this->data = $data;
        $this->sub_data = $sub_data;
        $this->arr_title = $arr_title;
    }
    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->arr_title as $key => $item) {
            $sheets[] = new SinhVienExport($this->data[$key], $this->sub_data, $item);
        }

        return $sheets;
    }
}
