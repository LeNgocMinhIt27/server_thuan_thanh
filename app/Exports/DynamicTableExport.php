<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class DynamicTableExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithEvents, WithBackgroundColor
{
    protected $sheet;
    protected $title;
    protected $subtitle;
    protected $columnFormats;
    protected $data;
    protected $headings;
    protected $cb_mapping_data;
    protected $length;
    use Exportable;
    public function backgroundColor()
    {
        return 'ffffff';
    }
    public function title(): string
    {
        return $this->sheet;
    }
    public function __construct($sheet, $data, $headings, $cb_mapping_data, $title = '')
    {
        $this->sheet = $sheet;
        $this->title = mb_strtoupper($title);
        $this->subtitle = ['Phiên bản'];
        $this->data = $data;
        $this->headings = $headings;
        $this->cb_mapping_data = $cb_mapping_data;
    }
    public function collection()
    {
        $this->length = count($this->data);
        ++$this->length;
        return collect($this->data);
    }
    public function headings(): array
    {
        return array_map(function ($header) {
            return  $header['text'] ?? $header;
        }, $this->headings);
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                $last_column = Coordinate::stringFromColumnIndex(count($this->headings));
                $before_last_column = Coordinate::stringFromColumnIndex(count($this->headings) - 1);

                $event->sheet->insertNewRowBefore(1, 4);
                $last_row = $this->length + 4 + 1;
                $subtitleRange = "A3:{$last_column}4";
                $event->sheet->setCellValue('A3', $this->subtitle[0]);
                $event->sheet->setCellValue("B3", config('app.export_version'));

                $cellRange = 'A5:' . getexcelcolumnname(count($this->headings) - 1) . '5'; // All headers
                $cellRest = 'A6:' . getexcelcolumnname(count($this->headings) - 1) . ($this->length + 4);

                $event->sheet->getRowDimension(5)->setRowHeight(25);

                $nameRange = 'A1:' . $last_column . '1';
                $event->sheet->mergeCells(sprintf('A1:%s1', $last_column));
                $event->sheet->setCellValue('A1', config('app.export_name'));

                $titleRange = 'A2:' . $last_column . '2';
                $event->sheet->mergeCells(sprintf('A2:%s2', $last_column));
                $event->sheet->setCellValue('A2', $this->title);

                // Style header
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000'],
                        ],
                    ],
                    'font' => [
                        'name' => 'Times New Roman',
                        'size' => 12,
                        'bold' => true,
                        'color' => ['argb' => '000'],
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ];

                #Style name
                $styleName = $styleArray;
                $styleName['font']['italic'] = true;
                $styleName['alignment']['horizontal'] = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
                $styleName['fill'] = [
                    'color' => ['rgb' => 'F2F2F2']
                ];

                #Style title
                $styleTitle = $styleArray;
                $styleTitle['font']['size'] = 14;
                $styleTitle['alignment']['horizontal'] = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;

                $styleArray['fill'] = [
                    'color' => ['rgb' => '0E9F6E']
                ];
                $styleArray['font']['color'] = ['rgb' => 'fff'];
                $event->sheet->getRowDimension(1)->setRowHeight(25);
                if (!empty($this->title)) $event->sheet->getRowDimension(2)->setRowHeight(35);
                if (!empty($footerRange)) {
                    $event->sheet->getRowDimension($last_row)->setRowHeight(25);
                    $styleFooter = $styleArray;
                    $styleFooter['alignment']['horizontal'] = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
                    $event->sheet->getDelegate()->getStyle($footerRange)->applyFromArray($styleFooter);
                }
                $event->sheet->getDelegate()->getStyle($nameRange)->applyFromArray($styleName);
                $event->sheet->getDelegate()->getStyle($titleRange)->applyFromArray($styleTitle);
                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);

                $styleSubtitle
                    = [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000'],
                            ],
                        ],
                        'font' => [
                            'name' => 'Times New Roman',
                            'italic' => true,
                            'color' => ['argb' => '000'],
                        ],
                        'alignment' => [
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
                        ],
                    ];
                $event->sheet->getDelegate()->getStyle($subtitleRange)->applyFromArray($styleSubtitle);

                $styleCell = $styleSubtitle;
                $styleCell['alignment']['horizontal'] = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
                $styleCell['font']['color'] = ['rgb' => 'C00000'];
                $styleCell['font']['bold'] = true;
                if (is_a($this->data, 'Illuminate\Database\Eloquent\Collection')) {
                    $event->sheet->getDelegate()->getStyle("{$last_column}4")->applyFromArray($styleCell);
                } else {
                    $event->sheet->getDelegate()->getStyle("B3")->applyFromArray($styleCell);
                }

                //style rest
                $styleArrayRest = [
                    'font' => [
                        'name' => 'Times New Roman',
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000'],
                        ],
                    ],
                ];
                $event->sheet->getDelegate()->getStyle($cellRest)->applyFromArray($styleArrayRest);
            },
        ];
    }
    public function map($data): array
    {
        $cb = $this->cb_mapping_data;
        return $cb($data);
    }
}
