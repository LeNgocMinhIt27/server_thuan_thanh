<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetExportDiemDanh implements WithMultipleSheets
{
    use Exportable;
    private $file = 'diem_danh.xlsx';
    private $data;
    private $sub_data;
    private $sheet_title;

    public function __construct($data, $sub_data, $sheet_title)
    {
        $this->file = 'diem_danh.xlsx';
        $this->data = $data;
        $this->sub_data = $sub_data;
        $this->sheet_title = $sheet_title;
    }
    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->sheet_title as $key => $item) {
            $sheets[] = new DiemDanhExport($this->data[$key], $this->sub_data, $item);
        }

        return $sheets;
    }
}
