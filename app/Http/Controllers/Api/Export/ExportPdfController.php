<?php

namespace App\Http\Controllers\Api\Export;

use App\Helpers\System\DownloadFileHelper;
use PDF;
use App\Http\Controllers\Controller;
use App\Models\Lop\Lop;
use Illuminate\Http\Request;
use Storage;
use App\Helpers\ZipHelper;
use Carbon\Carbon;

class ExportPdfController extends Controller
{
    public function exportLopSv(Request $request, $id)
    {
        $query = Lop::query();
        $query->with('sinhViens');
        $result = $query->findOrFail($id);
        $sub_data = $request->all();
        $sinhViens = $result['sinhViens']->toArray();
        $chunkedSinhViens = [];
        $length_table = 18;

        $ten_hp = $sub_data["ten_hp"];
        $gv_ten = $sub_data["username"] ?? '';
        $dia_diem = $sub_data["class"] ?? '';
        $dia_diem_arr = preg_split('//u', $dia_diem, -1, PREG_SPLIT_NO_EMPTY);
        $gv_ten_arr = preg_split('//u', $gv_ten, -1, PREG_SPLIT_NO_EMPTY);
        $ten_hp_arr = preg_split('//u', $ten_hp, -1, PREG_SPLIT_NO_EMPTY);
        $dia_diem_length = count($dia_diem_arr);
        $gv_ten_length = count($gv_ten_arr);
        $ten_hp_length = count($ten_hp_arr);
        $length_table = 18;
        if ($ten_hp_length > 22 && $gv_ten_length > 21 && $dia_diem_length > 30) {
            $length_table = 16;
        } elseif ($ten_hp_length > 22 && $gv_ten_length > 21) {
            $length_table = 16;
        } elseif ($ten_hp_length > 22 && $dia_diem_length > 30) {
            $length_table = 16;
        } elseif ($gv_ten_length > 21 && $dia_diem_length > 30) {
            $length_table = 16;
        } elseif (($ten_hp_length > 22 && $gv_ten_length < 21 && $dia_diem_length < 30) || ($gv_ten_length > 21 && $dia_diem_length < 30 && $ten_hp_length < 22) || ($dia_diem_length > 30 && $ten_hp_length < 22 && $gv_ten_length < 21)) {
            $length_table = 17;
        }

        $firstChunk = array_slice($sinhViens, 0, $length_table);
        $chunkedSinhViens[] = $firstChunk;

        $startIndex = $length_table;

        while ($startIndex < count($sinhViens)) {
            $chunk = array_slice($sinhViens, $startIndex, 23);
            $chunkedSinhViens[] = $chunk;
            $startIndex += 23;
        }
        $pdf = Pdf::loadView('pdf.lop-hoc', ['subData' => $sub_data, 'studentData' => $chunkedSinhViens]);
        return $pdf->download('Danh-sach-diem-danh.pdf');
    }
    public function exportLopLt(Request $request, $id)
    {
        $fileName = "danh-sach-diem-danh.zip";
        $sub_data = $request->all();
        $children = $sub_data['children'];
        $zip = new ZipHelper();
        $disk = Storage::disk('export');
        $now = Carbon::now()->format('Ymdhms');
        $folder_path =  "lop-hoc/$now";
        $file_path = "$folder_path/$fileName";
        $pdfs = array();

        if ($disk->exists($file_path)) $disk->delete($file_path);
        foreach ($children as $key => $child) {
            $classId = $child['id'];
            $result = Lop::with('sinhViens')->findOrFail($classId);
            $sinhViens = $result['sinhViens']->toArray();
            $chunkedSinhViens = [];

            $ten_hp = $sub_data["ten_hp"];
            $gv_ten = $sub_data["username"] ?? '';
            $dia_diem = $sub_data["class"] ?? '';
            $dia_diem_arr = preg_split('//u', $dia_diem, -1, PREG_SPLIT_NO_EMPTY);
            $gv_ten_arr = preg_split('//u', $gv_ten, -1, PREG_SPLIT_NO_EMPTY);
            $ten_hp_arr = preg_split('//u', $ten_hp, -1, PREG_SPLIT_NO_EMPTY);
            $dia_diem_length = count($dia_diem_arr);
            $gv_ten_length = count($gv_ten_arr);
            $ten_hp_length = count($ten_hp_arr);
            $length_table = 18;
            if ($ten_hp_length > 22 && $gv_ten_length > 21 && $dia_diem_length > 30) {
                $length_table = 16;
            } elseif ($ten_hp_length > 22 && $gv_ten_length > 21) {
                $length_table = 16;
            } elseif ($ten_hp_length > 22 && $dia_diem_length > 30) {
                $length_table = 16;
            } elseif ($gv_ten_length > 21 && $dia_diem_length > 30) {
                $length_table = 16;
            } elseif (($ten_hp_length > 22 && $gv_ten_length < 21 && $dia_diem_length < 30) || ($gv_ten_length > 21 && $dia_diem_length < 30 && $ten_hp_length < 22) || ($dia_diem_length > 30 && $ten_hp_length < 22 && $gv_ten_length < 21)) {
                $length_table = 17;
            }

            $firstChunk = array_slice($sinhViens, 0, $length_table);
            $chunkedSinhViens[] = $firstChunk;

            $startIndex = $length_table;

            while ($startIndex < count($sinhViens)) {
                $chunk = array_slice($sinhViens, $startIndex, 23);
                $chunkedSinhViens[] = $chunk;
                $startIndex += 23;
            }

            if ($sinhViens) {
                $pdf_[$key] = Pdf::loadView('pdf.lop-hoc', ['subData' => $child, 'studentData' => $chunkedSinhViens]);
                $name = "danh-sach-diem-danh-{$classId}";
                $pdfs[$key] = "$folder_path/$name.pdf";
                $disk->put("$folder_path/$name.pdf", $pdf_[$key]->output());
            }
        }
        $full_dir_temp = storage_path('export/' . $folder_path);
        $path = $fileName;
        $zip->addFolder($full_dir_temp);
        $zip->zip($disk->path('') . '/' . $path);
        $builder = new DownloadFileHelper;
        $builder->setPath($disk->path($path));
        $builder->setIsFullPath();
        $ma_lop = $sub_data['ma'];
        $builder->setFileName("diem_danh_$ma_lop.zip");
        return $this->responseSuccess($builder->build());
    }
    public function exportDiemThanhTich(Request $request, $id)
    {
        $query = Lop::query();
        $query->with('sinhViens');
        $result = $query->findOrFail($id);
        $sub_data = $request->all();
        $sinhViens = $result['sinhViens']->toArray();
        $chunkedSinhViens = [];
        $pdf = Pdf::loadView('pdf.diem-thanh-tich', ['subData' => $sub_data, 'studentData' => $sinhViens]);
        return $pdf->download('Danh-sach.pdf');
    }
    public function exportAllDiemThanhTich(Request $request, $id)
    {
        $fileName = "diem-thanh-tich.zip";
        $sub_data = $request->all();
        $children = $sub_data['children'];
        $zip = new ZipHelper();
        $disk = Storage::disk('export');
        $now = Carbon::now()->format('Ymdhms');
        $folder_path =  "diem-tt/$now";
        $file_path = "$folder_path/$fileName";
        $pdfs = array();

        if ($disk->exists($file_path)) $disk->delete($file_path);
        foreach ($children as $key => $child) {
            $classId = $child['id'];
            $result = Lop::with('sinhViens')->findOrFail($classId);
            $sinhViens = $result['sinhViens']->toArray();

            if ($sinhViens) {
                $pdf_[$key] = Pdf::loadView('pdf.diem-thanh-tich', ['subData' => $child, 'studentData' => $sinhViens]);
                $ma = $child['ma'];
                $name = "Danh-sach_{$ma}";
                $pdfs[$key] = "$folder_path/$name.pdf";
                $disk->put("$folder_path/$name.pdf", $pdf_[$key]->output());
            }
        }
        $full_dir_temp = storage_path('export/' . $folder_path);
        $path = $fileName;
        $zip->addFolder($full_dir_temp);
        $zip->zip($disk->path('') . '/' . $path);
        $builder = new DownloadFileHelper;
        $builder->setPath($disk->path($path));
        $builder->setIsFullPath();
        $ma_lop = $sub_data['ma'];
        $builder->setFileName("Danh-sach_$ma_lop.zip");
        return $this->responseSuccess($builder->build());
    }
}
