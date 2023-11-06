<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PhucKhaoExport implements WithEvents, WithStyles
{
    use Exportable;
    private $file = 'danh_sach_phuc_khao.xlsx';
    private $data;
    private $sub_data;
    private $sheet_name;
    private $headers;
    public function __construct($data, $sub_data, $sheet_name)
    {
        $this->file = 'danh_sach_phuc_khao.xlsx';
        $this->data = $data;
        $this->sub_data = $sub_data;
        $this->sheet_name = $sheet_name;
        $this->headers = ['MSSV', 'Kỳ học', 'Mã lớp học', 'Mã lớp thi', 'Mã thanh toán', 'Điểm', 'Trạng thái'];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:G')->getFont()->setSize(14);
        $sheet->getStyle('A:G')->getFont()->setName('Times New Roman');
        $sheet->getStyle('A:G')->getFont()->getColor()->setARGB(Color::COLOR_BLACK);
    }
    public function registerEvents(): array
    {

        return [
            AfterSheet::class => function (AfterSheet $event) {

                $title_style = [
                    'font' => [
                        'size'      =>  22,
                        'name' => 'Times New Roman',
                        'bold' => true
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],

                ];
                $sub_title_style = [
                    'font' => [
                        'size'      =>  16,
                        'name' => 'Times New Roman',
                        'bold' => true
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];

                // set cao rộng cột,hàng
                $event->sheet->getColumnDimension("A")->setWidth(25);
                $event->sheet->getRowDimension('1')->setRowHeight(60);
                $event->sheet->getColumnDimension("B")->setWidth(25);
                $event->sheet->getColumnDimension("C")->setWidth(25);
                $event->sheet->getColumnDimension("D")->setWidth(25);
                $event->sheet->getColumnDimension("E")->setWidth(25);
                $event->sheet->getColumnDimension("F")->setWidth(25);
                $event->sheet->getColumnDimension("G")->setWidth(25);

                $event->sheet->mergeCells("A1:G1");
                $event->sheet->getDelegate()->getStyle("A1:G1")->applyFromArray($title_style);
                // set style sub_title

                $event->sheet->setTitle($this->sheet_name);
                $event->sheet->setCellValue("A1", "Danh sách phúc khảo");
                // $event->sheet->mergeCells("B5:F5");
                // $event->sheet->setCellValue("A5", 'Ngày:');
                for ($i = 1; $i <= 7; $i++) {
                    $col = Coordinate::stringFromColumnIndex($i);
                    $event->sheet->setCellValue("{$col}2", $this->headers[$i - 1]);
                }
                $event->sheet->getDelegate()->getStyle("A2:G2")->applyFromArray($sub_title_style);

                foreach ($this->data as $key => $item) {
                    $idx = $key + 3;
                    $event->sheet->setCellValue("A{$idx}", $item->mssv);
                    $event->sheet->setCellValue("B{$idx}", $item->ki_hoc);
                    $event->sheet->setCellValue("C{$idx}", $item->ma_lop_hoc);
                    $event->sheet->setCellValue("D{$idx}", $item->ma_lop_thi);
                    $event->sheet->setCellValue("E{$idx}", $item->ma_thanh_toan);
                    $event->sheet->setCellValue("F{$idx}", $item->diem ?? '');
                    $event->sheet->setCellValue("G{$idx}", $item->trang_thai);
                };
            }
        ];
    }
}
