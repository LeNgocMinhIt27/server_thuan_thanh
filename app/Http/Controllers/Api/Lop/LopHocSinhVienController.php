<?php

namespace App\Http\Controllers\Api\Lop;

use App\Http\Controllers\Controller;
use App\Models\Lop\Lop;
use DB;
use App\Models\Lop\LopSinhVien;
use Illuminate\Http\Request;
use App\Library\QueryBuilder\QueryBuilder;
use App\Models\Diem\Diem;
use App\Models\Lop\LopThi;
use App\Models\Lop\LopThiSinhVien;

class LopHocSinhVienController extends Controller
{
    protected $includes = ['lop', 'sinhVien'];

    public function index($id)
    {
        $query = Lop::query()->with('sinhViens');
        $lop = $query->findOrFail($id);
        return response()->json($lop->sinhViens, 200, []);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'stt' => 'required|integer|min:1',
            'sinh_viens' => 'required|integer'
        ], [
            'stt.min' => 'Trường STT không được phép nhỏ hơn 1',
            'stt.interger' => 'Trường STT phải là số nguyên'
        ]);
        try {
            DB::transaction();
            $sinh_vien_ids = $request->input('sinh_viens');
            $stt = $request->input('stt');
            if ($stt < 1) {
                abort(400, 'Trường STT sinh viên không được phép nhỏ hơn 1');
            }
            $lop = Lop::findOrFail($id);
            $nhom = $request->get('nhom');
            $sinh_vien_lop = $lop->sinhViens()->where('id', $sinh_vien_ids)->first()->toArray();

            if ($stt < $sinh_vien_lop['pivot']['stt']) {
                LopSinhVien::where('lop_id', $lop->id)->where('stt', '>=', $stt)->where('stt', '<', $sinh_vien_lop['pivot']['stt'])->increment('stt');
            } elseif ($stt > $sinh_vien_lop['pivot']['stt']) {
                LopSinhVien::where('lop_id', $lop->id)->where('stt', '<=', $stt)->where('stt', '>', $sinh_vien_lop['pivot']['stt'])->decrement('stt');
            }
            $lop->sinhViens()->syncWithoutDetaching([
                $sinh_vien_ids => ['stt' => $stt, 'nhom' => $nhom],

            ]);
            DB::commit();
            return $this->responseSuccess($lop);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseError([], $th->getMessage());
        }
    }
    public function destroy(Request $request, $id)
    {
        $sinh_vien_id = $request->input('sinh_vien_id');
        $lop = Lop::findOrFail($id);
        $sinh_viens = $lop->sinhViens()->where('id', $sinh_vien_id)->first()->toArray();

        if (!$sinh_viens) {
            return response()->json(['message' => 'Sinh viên không tồn tại trong lớp'], 404);
        }
        $is_exist_diem_sinh_vien = Diem::join('ph_lop_this', 'ph_lop_this.id', '=', 'ph_diems.lop_thi_id')
            ->join('ph_lops', 'ph_lops.id', '=', 'ph_lop_this.lop_id')
            ->where('sinh_vien_id', '=', $sinh_vien_id)
            ->where('ph_lops.id', $id)->exists();
        if ($is_exist_diem_sinh_vien) {
            abort(400, 'Sinh viên này đã có điểm nên không được phép xoá');
        }
        try {
            DB::transaction();
            $lop->sinhViens()->detach($sinh_vien_id);
            $sinh_vien_greater = LopSinhVien::where('lop_id', $lop->id)->where('stt', $sinh_viens['pivot']['stt'] + 1)->exists();
            $sinh_vien_equal = LopSinhVien::where('lop_id', $lop->id)->where('stt', $sinh_viens['pivot']['stt'])->exists();
            if ($sinh_vien_greater && !$sinh_vien_equal) {
                LopSinhVien::where('lop_id', $lop->id)->where('stt', '>', $sinh_viens['pivot']['stt'])->decrement('stt');
            }
            DB::commit();
            return $this->responseSuccess($lop);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseError([], $th->getMessage());
        }
    }
    public function indexSinhVienLopThi(Request $request, $id)
    {
        $query = LopSinhVien::query()->where('lop_id', $id);
        $query = QueryBuilder::for($query, $request)
            ->allowedIncludes($this->includes);
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
    public function diemYThuc(Request $request)
    {
        $data =  $request->all();
        $count = count($data);
        for ($i = 0; $i < $count; $i++) {
            $value = $data[$i];

            if ($value['diem_y_thuc'] > 1 || $value['diem_y_thuc'] < -1) {
                return response()->json(['message' => 'Điểm ý thức cần phải phải lớn hơn -1 và nhỏ hơn 1'], 404);
            } elseif (!is_numeric($value['diem_y_thuc'])) {
                return response()->json(['message' => 'Điểm ý thức không đúng định dạng'], 404);
            } else {

                switch (true) {
                    case (-1 < $value['diem_y_thuc'] && $value['diem_y_thuc'] <= -0.75):
                        DB::table('ph_lop_sinh_viens')
                            ->where('sinh_vien_id', $value['pivot']['sinh_vien_id'])
                            ->where('lop_id', $request->id)
                            ->update(['diem_y_thuc' => -1]);
                        break;
                    case (-0.75 < $value['diem_y_thuc'] && $value['diem_y_thuc'] < -0.5):
                        DB::table('ph_lop_sinh_viens')
                            ->where('sinh_vien_id', $value['pivot']['sinh_vien_id'])
                            ->where('lop_id', $request->id)
                            ->update(['diem_y_thuc' => -0.5]);
                        break;
                    case (-0.5 < $value['diem_y_thuc'] && $value['diem_y_thuc'] <= -0.25):
                        DB::table('ph_lop_sinh_viens')
                            ->where('sinh_vien_id', $value['pivot']['sinh_vien_id'])
                            ->where('lop_id', $request->id)
                            ->update(['diem_y_thuc' => -0.5]);
                        break;

                    case (-0.25 < $value['diem_y_thuc'] && $value['diem_y_thuc'] < 0):
                        DB::table('ph_lop_sinh_viens')
                            ->where('sinh_vien_id', $value['pivot']['sinh_vien_id'])
                            ->where('lop_id', $request->id)
                            ->update(['diem_y_thuc' => 0]);
                        break;

                    case (0 < $value['diem_y_thuc'] && $value['diem_y_thuc'] < 0.25):
                        DB::table('ph_lop_sinh_viens')
                            ->where('sinh_vien_id', $value['pivot']['sinh_vien_id'])
                            ->where('lop_id', $request->id)
                            ->update(['diem_y_thuc' => 0]);
                        break;

                    case (0.25 <= $value['diem_y_thuc']  && $value['diem_y_thuc'] < 0.5):
                        DB::table('ph_lop_sinh_viens')
                            ->where('sinh_vien_id', $value['pivot']['sinh_vien_id'])
                            ->where('lop_id', $request->id)
                            ->update(['diem_y_thuc' => 0.5]);
                        break;
                    case (0.5 < $value['diem_y_thuc']  && $value['diem_y_thuc'] < 0.75):
                        DB::table('ph_lop_sinh_viens')
                            ->where('sinh_vien_id', $value['pivot']['sinh_vien_id'])
                            ->where('lop_id', $request->id)
                            ->update(['diem_y_thuc' => 0.5]);
                        break;
                    case (0.75 <= $value['diem_y_thuc']  && $value['diem_y_thuc'] < 1):
                        DB::table('ph_lop_sinh_viens')
                            ->where('sinh_vien_id', $value['pivot']['sinh_vien_id'])
                            ->where('lop_id', $request->id)
                            ->update(['diem_y_thuc' => 1]);
                        break;

                    default:
                        DB::table('ph_lop_sinh_viens')
                            ->where('sinh_vien_id', $value['pivot']['sinh_vien_id'])
                            ->where('lop_id', $request->id)
                            ->update(['diem_y_thuc' => $value['diem_y_thuc']]);
                }
            }
        }
    }
}
