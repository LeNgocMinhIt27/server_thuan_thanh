<?php

namespace App\Http\Controllers\Api\Diem;

use App\Http\Controllers\Controller;
use App\Models\Lop\LopSinhVien;
use Illuminate\Http\Request;
use App\Models\User\SinhVien;
use DB;

class DiemYThucController extends Controller
{
    public function diemYThucExcel(Request $request)
    {

        $sinh_viens = SinhVien::get(['id', 'mssv'])->mapWithKeys(function ($item, $key) {
            return [$item['mssv'] => $item['id']];
        });
        foreach ($request['items'] as $item) {
            $stt = $item[$request['fields']['stt']];
            $mssv = $item[$request['fields']['mssv']];
            $diem_y_thuc = $item[$request['fields']['diem_y_thuc']];
            $lop_id = $request['fields']['lop_id'];

            $sinh_vien_id = $sinh_viens[$mssv];
            if (!is_numeric($diem_y_thuc)) {
                return response()->json([
                    'message' => "Cột điểm ý thức cần được định dạng kiểu số",
                ], 400);
            }
            if (!$sinh_vien_id) {
                continue;
            }


            switch (true) {
                case ($diem_y_thuc < -1):
                    DB::table('ph_lop_sinh_viens')->where('lop_id', $lop_id)
                        ->where('sinh_vien_id', $sinh_vien_id)
                        ->update(['diem_y_thuc' => -1]);
                    break;
                case (-1 < $diem_y_thuc && $diem_y_thuc <= -0.75):
                    DB::table('ph_lop_sinh_viens')->where('lop_id', $lop_id)
                        ->where('sinh_vien_id', $sinh_vien_id)
                        ->update(['diem_y_thuc' => -1]);
                    break;
                case (-0.75 < $diem_y_thuc && $diem_y_thuc < -0.5):
                    DB::table('ph_lop_sinh_viens')->where('lop_id', $lop_id)
                        ->where('sinh_vien_id', $sinh_vien_id)
                        ->update(['diem_y_thuc' => -0.5]);
                    break;
                case (-0.5 < $diem_y_thuc && $diem_y_thuc <= -0.25):
                    DB::table('ph_lop_sinh_viens')->where('lop_id', $lop_id)
                        ->where('sinh_vien_id', $sinh_vien_id)
                        ->update(['diem_y_thuc' => -0.5]);
                    break;

                case (-0.25 < $diem_y_thuc && $diem_y_thuc < 0):
                    DB::table('ph_lop_sinh_viens')->where('lop_id', $lop_id)
                        ->where('sinh_vien_id', $sinh_vien_id)
                        ->update(['diem_y_thuc' => 0]);
                    break;

                case (0 < $diem_y_thuc && $diem_y_thuc < 0.25):
                    DB::table('ph_lop_sinh_viens')->where('lop_id', $lop_id)
                        ->where('sinh_vien_id', $sinh_vien_id)
                        ->update(['diem_y_thuc' => 0]);
                    break;

                case (0.25 <= $diem_y_thuc  && $diem_y_thuc < 0.5):
                    DB::table('ph_lop_sinh_viens')->where('lop_id', $lop_id)
                        ->where('sinh_vien_id', $sinh_vien_id)
                        ->update(['diem_y_thuc' => 0.5]);
                    break;
                case (0.5 < $diem_y_thuc  && $diem_y_thuc < 0.75):
                    DB::table('ph_lop_sinh_viens')->where('lop_id', $lop_id)
                        ->where('sinh_vien_id', $sinh_vien_id)
                        ->update(['diem_y_thuc' => 0.5]);
                    break;
                case (0.75 <= $diem_y_thuc  && $diem_y_thuc < 1):
                    DB::table('ph_lop_sinh_viens')->where('lop_id', $lop_id)
                        ->where('sinh_vien_id', $sinh_vien_id)
                        ->update(['diem_y_thuc' => 1]);
                    break;
                case ($diem_y_thuc > 1):
                    DB::table('ph_lop_sinh_viens')->where('lop_id', $lop_id)
                        ->where('sinh_vien_id', $sinh_vien_id)
                        ->update(['diem_y_thuc' => 1]);
                    break;
                default:
                    DB::table('ph_lop_sinh_viens')->where('lop_id', $lop_id)
                        ->where('sinh_vien_id', $sinh_vien_id)
                        ->update(['diem_y_thuc' => $diem_y_thuc]);
            }
        }
    }
}
