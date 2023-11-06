<?php

namespace App\Http\Controllers\Api\Import;

use App\Constants\RoleCode;
use App\Http\Controllers\Controller;
use App\Models\User\GiaoVien;
use App\Models\User\SinhVien;
use App\Models\Lop\LopThi;
use App\Models\Lop\Lop;
use App\Models\Lop\DiemPhucKhao;
use DB;
use Hash;
use Illuminate\Http\Request;
use Str;

class ImportPhucKhaoController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'items' => ['required', 'array'],
            'fields' => ['required'],
            'fields.sinh_vien_id' => ['required', 'string'],
            'fields.ma_hp' => ['required', 'string'],
            'fields.ma_lop' => ['required', 'string'],
            'fields.nhom' => ['required'],
            'fields.ma_lop_thi' => ['required', 'string'],
            'fields.diem' => ['required', 'string'],
            'fields.diem_moi' => ['required', 'string'],
            'fields.ghi_chu' => ['required', 'string']
        ]);
        $user = $request->user();
        $items = $request->get('items');
        $fields = $request->get('fields');
        $ki_hoc = $request->get('ki_hoc');
        try {
            DB::beginTransaction();
            DB::commit();
            $items_return = [];
            foreach ($items as $item) {
                $res = ImportHelper::convertTime($fields, $item);
                $res['ki_hoc'] = $ki_hoc;
                $malopthi = $res['ma_lop'] . '-' . $res['nhom'];
                $lophocs = Lop::where('ma_hp', $res['ma_hp'])->where('ma', $res['ma_lop'])->get();
                foreach ($lophocs as $lophoc) {
                    $lopthi = LopThi::where('lop_id', $lophoc->id)->where('ma', $malopthi)->first();
                    if (!$lopthi) {
                        return response()->json(['message' => "Mã lớp thi $malopthi không tồn tại"], 404);
                    }
                }
                $sinhviens = SinhVien::get();
                foreach ($sinhviens as $sinhvien) {
                    $id_sinh_vien = $sinhvien::where('mssv', $res['sinh_vien_id'])->first();
                    if (!$id_sinh_vien) {
                        $mssv = $res['sinh_vien_id'];
                        return response()->json(['message' => "Sinh viên $mssv  không tồn tại"], 404);
                    }
                }
                $diem = $res['diem'];
                if ($res['diem_moi'] != "Không thay đổi") {
                    $diem = $res['diem_moi'];
                }
                $is_exists = DiemPhucKhao::where('lop_thi_id', $lopthi->id)->where('sinh_vien_id', $id_sinh_vien->id)->exists();
                if ($is_exists) {
                    abort(400, 'Điểm phúc khảo môn này đã tồn tại trong kỳ này');
                }
                $result = DiemPhucKhao::create([
                    'lop_thi_id' => $lopthi->id,
                    'sinh_vien_id' => $id_sinh_vien->id,
                    'diem' => $diem,
                    'nguoi_nhap_id' => $user->id,
                    'ghi_chu' => $res['ghi_chu'],
                ]);
            }
            return $this->responseSuccess($items_return);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
