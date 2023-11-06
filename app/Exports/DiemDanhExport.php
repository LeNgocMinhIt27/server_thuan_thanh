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

class DiemDanhExport implements WithEvents, WithStyles
{
    use Exportable;
    private $file = 'danh_sach_lop.xlsx';
    private $data;
    private $sub_data;
    private $sheet_name;
    private $headers;
    public function __construct($data, $sub_data, $sheet_name)
    {
        $this->file = 'danh_sach_lop.xlsx';
        $this->data = $data;
        $this->sub_data = $sub_data;
        $this->sheet_name = $sheet_name;
        $this->headers = ['STT', 'MSSV', 'Họ và tên', 'Chữ ký', 'Ghi chú'];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:E')->getFont()->setSize(14);
        $sheet->getStyle('A:E')->getFont()->setName('Times New Roman');
        $sheet->getStyle('A:E')->getFont()->getColor()->setARGB(Color::COLOR_BLACK);
        $sheet->getStyle('A1:E5')
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('ffffff');
        $sheet->getStyle('E1:E5')
            ->getBorders()
            ->getRight()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new Color('D3D3D3'));
        $sheet->getStyle('A5:E5')
            ->getBorders()
            ->getBottom()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new Color('D3D3D3'));
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $title_style = [
                    'font' => [
                        'size'      =>  22,
                        'name' => 'Times New Roman',
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
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    ],
                ];

                // set cao rộng cột,hàng
                for ($i = 2; $i <= 6; $i++) {
                    $event->sheet->getRowDimension("$i")->setRowHeight(30);
                }
                $event->sheet->getColumnDimension("A")->setWidth(15);
                $event->sheet->getRowDimension('1')->setRowHeight(60);
                $event->sheet->getColumnDimension("B")->setWidth(15);
                $event->sheet->getColumnDimension("C")->setWidth(30);
                $event->sheet->getColumnDimension("D")->setWidth(20);
                $event->sheet->getColumnDimension("E")->setWidth(30);
                $event->sheet->mergeCells("A1:E1");
                $event->sheet->getDelegate()->getStyle("A1:E1")->applyFromArray($title_style);
                $event->sheet->getDelegate()->getStyle("A6:E6")->applyFromArray(['font' => ['bold' => true]]);

                // set style sub_title
                for ($i = 2; $i <= 6; $i++) {
                    $event->sheet->getDelegate()->getStyle("A$i:E$i")->applyFromArray($sub_title_style);
                }
                $event->sheet->setTitle($this->sheet_name);
                $event->sheet->mergeCells("B5:E5");
                $event->sheet->setCellValue("A1", "Danh sách sinh viên");
                $subtitles = [["Học phần:", "Giảng viên:", "Mã lớp:"], ["Mã học phần:", "Lớp:", "Địa điểm:"]];
                foreach ($subtitles as $key => $subs) {
                    foreach ($subs as $sKey => $sub) {

                        $row_idx = $sKey + 2;
                        if ($key == 0) {
                            $event->sheet->setCellValue("A$row_idx", $sub);
                            $event->sheet->mergeCells("B$row_idx:C$row_idx");
                        } else $event->sheet->setCellValue("D$row_idx", $sub);
                    }
                }
                $event->sheet->setCellValue("B2", $this->sub_data['ten_hp'] ?? '');
                $event->sheet->setCellValue("B3", $this->sub_data['username'] ?? '');
                $event->sheet->setCellValue("B4", $this->sub_data['ma'] ?? '');
                $event->sheet->setCellValue("E2", $this->sub_data['ma_hp'] ?? '');
                $event->sheet->setCellValue("E3", $this->sub_data['loai'] ?? '');
                $event->sheet->setCellValue("E4", $this->sub_data['class'] ?? '');
                $event->sheet->setCellValue("B5", $this->sub_data['date'] ?? '');
                $event->sheet->setCellValue("A5", 'Ngày:');
                for ($i = 1; $i <= 5; $i++) {
                    $col = Coordinate::stringFromColumnIndex($i);
                    $event->sheet->setCellValue("{$col}6", $this->headers[$i - 1]);
                }
                foreach ($this->data as $key => $item) {

                    $idx = $key + 7;
                    $event->sheet->setCellValue("A{$idx}", $item['pivot']['stt']);
                    $event->sheet->setCellValue("B{$idx}", $item['mssv']);
                };
            }
        ];
    }
}
