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
use Carbon\Carbon;


class LopCoiThiGiaoVienExport implements WithEvents, WithStyles
{
    use Exportable;
    private $data;
    private $sub_data;
    private $title;
    private $headers;
    public function __construct($data, $title)
    {
        $this->data = $data;
        $this->title = $title;
        $this->headers = ['Mã lớp', 'Mã HP', 'Tên HP', 'Mã lớp thi', 'Ghi chú', 'Nhóm', 'Ngày', 'Kíp thi', 'Phòng thi', 'SL', 'CBCT', 'Số bài/Tờ', 'Ký'];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:M')->getFont()->setSize(14);
        $sheet->getStyle('A:M')->getFont()->setName('Times New Roman');
        $sheet->getStyle('A:M')->getFont()->getColor()->setARGB(Color::COLOR_BLACK);
        $sheet->getStyle('I')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('I')->getAlignment()->setVertical('center');
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
                $event->sheet->getColumnDimension("A")->setWidth(15);
                $event->sheet->getRowDimension('1')->setRowHeight(60);
                $event->sheet->getColumnDimension("B")->setWidth(15);
                $event->sheet->getColumnDimension("C")->setWidth(25);
                $event->sheet->getColumnDimension("D")->setWidth(25);
                $event->sheet->getColumnDimension("E")->setWidth(40);
                $event->sheet->getColumnDimension("F")->setWidth(25);
                $event->sheet->getColumnDimension("G")->setWidth(25);
                $event->sheet->getColumnDimension("H")->setWidth(15);
                $event->sheet->getColumnDimension("I")->setWidth(25);
                $event->sheet->getColumnDimension("j")->setWidth(25);
                $event->sheet->getColumnDimension("K")->setWidth(25);
                $event->sheet->getColumnDimension("L")->setWidth(25);
                $event->sheet->getColumnDimension("M")->setWidth(25);


                $event->sheet->mergeCells("A1:M1");
                $event->sheet->getDelegate()->getStyle("A1:M1")->applyFromArray($title_style);
                // set style sub_title

                $event->sheet->setCellValue("A1", $this->title);
                // $event->sheet->mergeCells("B5:F5");
                // $event->sheet->setCellValue("A5", 'Ngày:');
                for ($i = 1; $i <= count($this->headers); $i++) {
                    $col = Coordinate::stringFromColumnIndex($i);
                    $event->sheet->setCellValue("{$col}2", $this->headers[$i - 1]);
                }
                $event->sheet->getDelegate()->getStyle("A2:M2")->applyFromArray($sub_title_style);

                $count = 3;
                $old_phong_thi = '';
                foreach ($this->data as $key => $item) {
                    $idx = $key + 3;

                    if ($old_phong_thi !== $item->phong_thi && $old_phong_thi !== '' && ($key + 1) !== count($this->data)) {
                        $idx_to_merg = $idx - 1;
                        $event->sheet->mergeCells("I{$count}:I{$idx_to_merg}");
                        $event->sheet->setCellValue("I{$count}", $old_phong_thi);
                        $count = $idx;
                    }
                    if (($key + 1) === count($this->data)) {
                        $event->sheet->mergeCells("I{$count}:I{$idx}");
                        $event->sheet->setCellValue("I{$count}", $old_phong_thi);
                        $count = $idx;
                    }
                    $event->sheet->setCellValue("A{$idx}", $item->ma_lop_hoc ?? '');
                    $event->sheet->setCellValue("B{$idx}", $item->ma_hp ?? "");
                    $event->sheet->setCellValue("C{$idx}", $item->ten_hp ?? "");
                    $event->sheet->setCellValue("D{$idx}", $item->ma ?? "");
                    $event->sheet->setCellValue("E{$idx}", $item->ghi_chu ?? "");
                    $event->sheet->setCellValue("F{$idx}", explode('-', $item->ma)[1] ?? "");
                    $event->sheet->setCellValue("G{$idx}", Carbon::parse($item->ngay_thi ?? '')->format('d-m-Y'));
                    $event->sheet->setCellValue("H{$idx}", $item->kip_thi ?? "");
                    $event->sheet->setCellValue("J{$idx}", $item->sl_sinh_vien ?? "");
                    $columnCBCT = "K{$idx}";

                    // Kiểm tra xem có giáo viên nào không
                    if (count($item->giao_viens) > 0) {
                        $giao_vien_names = [];
                        foreach ($item->giao_viens as $giao_vien) {
                            $giao_vien_names[] = $giao_vien['name'] ?? "";
                        }

                        // Sử dụng implode để kết hợp tất cả tên giáo viên thành một chuỗi và đặt giá trị cột 'K' ở dòng hiện tại
                        $event->sheet->setCellValue($columnCBCT, implode(", ", $giao_vien_names));
                    } else {
                        // Nếu không có giáo viên, đặt giá trị cột 'K' ở dòng hiện tại thành rỗng
                        $event->sheet->setCellValue($columnCBCT, "");
                    }
                    $old_phong_thi = $item->phong_thi;
                };
            }
        ];
    }
}
