<?php

namespace App\Http\Controllers\Api\Import;

use App\Http\Controllers\Controller;
use App\Models\Lop\Lop;
use App\Models\Lop\LopThi;
use App\Models\Lop\LopThiSinhVien;
use App\Models\User\SinhVien;
use Illuminate\Http\Request;
use DB;
use Log;
use Spatie\ResponseCache\Facades\ResponseCache;


class ImportSinhVienLopThiController extends Controller
{
    public function importSinhVienLopThi(Request $request)
    {
        $request->validate([
            'items' => ['required', 'array'],
            'fields' => ['required'],
            'fields.ma_lop' => ['required', 'string'],
            // 'fields.ma_lop_thi' => ['required', 'string'],
            // 'fields.nhom' => ['required', 'string'],
            'fields.mssv' => ['required', 'string']
        ]);
        $items = $request->get('items');
        $fields = $request->get('fields');
        $ki_hoc = $request->get('ki_hoc');
        $loai = $request->get('loai');
        if ($loai === "CK" && !isset($fields['ma_lop_thi'])) {
            abort(400, 'Cuối kỳ vui lòng chọn thêm mã lớp thi');
        } elseif (str_contains(strtolower($loai), "gk") && !isset($fields['nhom'])) {
            abort(400, 'Giữa kỳ vui lòng chọn thêm nhóm');
        }
        try {
            DB::beginTransaction();
            $stt = 1;
            $old_class_exam = '';
            $lops = Lop::where('ki_hoc', $ki_hoc)->get(['id', 'ma'])->mapWithKeys(function ($item, $key) {
                return [$item['ma'] => $item];
            });
            $sinh_viens = SinhVien::whereHas('lops', function ($query) use ($ki_hoc) {
                $query->where('ki_hoc', $ki_hoc);
            })->get(['id', 'mssv'])->mapWithKeys(function ($item, $key) {
                return [$item['mssv'] => $item['id']];
            });
            $lop_this = LopThi::where('loai', '=', $loai)->whereHas('lop', function ($query) use ($ki_hoc) {
                $query->where('ki_hoc', $ki_hoc);
            })->get(['id', 'ma'])->mapWithKeys(function ($item, $key) {
                return [$item['ma'] => $item['id']];
            });
            $data_insert = [];
            foreach ($items as $item) {
                $res = ImportHelper::convertTime($fields, $item);
                if (empty($res['mssv'])) {
                    continue;
                }
                $student = $sinh_viens[$res['mssv']] ?? '';
                if (!$student) {
                    $mssv = $res['mssv'];
                    return response()->json(['message' => "Sinh viên $mssv không tồn tại trong dữ liệu"], 404);
                }

                if ((empty($res['ma_lop_thi']) && $loai === 'CK') || $loai !== 'CK') {
                    $ma_lop_thi = $res['ma_lop'] . '-' . $res['nhom'];
                } else {
                    $ma_lop_thi = $res['ma_lop_thi'];
                }

                $lop_thi = $lop_this[$ma_lop_thi] ?? '';
                $lop_hoc = $lops[$res['ma_lop']] ?? null;
                if (!$lop_hoc) {
                    $ma_lop_hoc = $res['ma_lop'];
                    return response()->json(['message' => "Lớp $ma_lop_hoc không tồn tại trong dữ liệu"], 404);
                }

                if (!empty($old_class_exam) && $lop_thi != $old_class_exam) {
                    $stt = 1;
                    LopThiSinhVien::where('lop_thi_id', $old_class_exam)->delete();
                    LopThiSinhVien::insert($data_insert);
                    $data_insert = [];
                }

                if (!$lop_thi) {
                    return response()->json(['message' => "Lớp thi $ma_lop_thi không tồn tại trong dữ liệu"], 404);
                }
                Log::debug(json_encode(['rest' => $res, '$data_insert' => $data_insert]));
                $data_insert[] = ['lop_thi_id' => $lop_thi, "sinh_vien_id" => $student, 'stt' => $stt];
                $stt++;
                $old_class_exam = $lop_thi;
            }
            if (count($data_insert) > 0) {
                LopThiSinhVien::where('lop_thi_id', $lop_thi)->delete();
                LopThiSinhVien::insert($data_insert);
                $data_insert = [];
                $stt = 1;
            }
            DB::commit();
            ResponseCache::clear();
            return $this->responseSuccess();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
