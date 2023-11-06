<?php

namespace App\Http\Controllers\Api\Lop;

use App\Http\Controllers\Controller;
use App\Models\Lop\LopSinhVien;
use App\Models\Lop\LopThi;
use App\Models\Lop\LopThiSinhVien;
use Illuminate\Http\Request;
use App\Library\QueryBuilder\QueryBuilder;
use App\Models\Diem\Diem;
use App\Models\Lop\Lop;
use Illuminate\Validation\Rule;
use DB;
use Exception;

class LopThiSinhVienController extends Controller
{
    protected $includes = ['lopThi', 'sinhVien'];
    public function lopThiSinhVien($id)
    {
        $query = LopThiSinhVien::query()->where('sinh_vien_id', $id)->with(['lopThi', 'sinhVien']);
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
    public function listLopThiSinhVien(Request $request, $id)
    {
        $query = LopThiSinhVien::query()
            ->where('lop_thi_id', $id);
        $query = QueryBuilder::for($query, $request)
            ->allowedIncludes($this->includes);
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }

    //     public function addStudent(Request $request, $id)
    //     {
    //         $req = $request->all();
    //         $student = $req['sinh_vien_id'];
    //         $lop = Lop::findOrFail($id);
    //     }
    public function addStudentLopThi(Request $request)
    {
        $sinhVienId = $request->input('sinh_vien_id');
        $lopThiId = $request->input('lop_thi_id');
        $lopId = LopThi::where('id', $lopThiId)->pluck('lop_id');
        $listSinhVien = LopSinhVien::where('lop_id', $lopId)->pluck('sinh_vien_id')->toArray();
        if (!in_array($sinhVienId, $listSinhVien)) {
            return response()->json(['message' => 'Sinh viên không tồn tại trong lớp '], 404);
        }


        $exists = DB::table('ph_lop_thi_sinh_viens')
            ->where('sinh_vien_id', $sinhVienId)
            ->where('lop_thi_id', $lopThiId)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Sinh viên và lớp thi đã tồn tại trong bảng'], 404);
        }

        $request->validate(
            [
                'required' => 'Bạn đã quên nhập :attribute',
                'stt.integer' => ':attribute phải là số nguyên',
            ],
            [
                'stt' => 'Số thứ tự',
                'sinh_vien_id' => 'Sinh viên',
            ]
        );
        try {
            DB::beginTransaction();
            $data = $request->all();
            $next_sv_greater = LopThiSinhVien::where('lop_thi_id', $data['lop_thi_id'])->where('stt', '=', $data['stt'] + 1)->exists();
            $next_sv_equal = LopThiSinhVien::where('lop_thi_id', $data['lop_thi_id'])->where('stt', '=', $data['stt'])->exists();
            // dd($next_sv->get()->toArray());
            $lop_thi = LopThiSinhVien::where('lop_thi_id', $data['lop_thi_id']);
            if ($next_sv_equal && $next_sv_greater) {
                $lop_thi->where('stt', '>=', $data['stt'])->increment('stt');
            } elseif ($next_sv_equal && !$next_sv_greater) {
                $lop_thi->where('stt', '=', $data['stt'])->increment('stt');
            }
            $data = LopThiSinhVien::create($data);
            DB::commit();
            return $this->responseSuccess($data);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->responseError([], $e->getMessage());
        }
    }
    public function deleteSinhVienLopThi(Request $request, $id)
    {
        // $ki_hoc = $request->get('ki_hoc');
        // $loai = $request->get('loai');
        $lop_thi_id = $request->get('lop_thi_id');

        try {
            DB::beginTransaction();
            $sinh_vien_delete = LopThiSinhVien::query()->where('lop_thi_id', $lop_thi_id)->where('sinh_vien_id', $id)->first();

            $has_diem = Diem::where('lop_thi_id', $lop_thi_id)
                ->where('sinh_vien_id', $sinh_vien_delete->sinh_vien_id)
                ->exists();

            if ($has_diem) {
                abort(400, 'Không được phép xoá sinh viên đã có điểm.');
            }

            $result = LopThiSinhVien::query()->where('lop_thi_id', $lop_thi_id)->where('sinh_vien_id', $id)->delete();

            // lấy sinh viên lớn hơn và bằng
            $next_sv_greater = LopThiSinhVien::query()->where('lop_thi_id', $lop_thi_id)->where('stt', '>', $sinh_vien_delete->stt)->get()->toArray();
            $is_exist_sv_equal = LopThiSinhVien::query()->where('lop_thi_id', $lop_thi_id)->where('stt', '=', $sinh_vien_delete->stt)->exists();

            if (!empty($next_sv_greater) && ($next_sv_greater[0]['stt'] > $sinh_vien_delete->stt && !$is_exist_sv_equal)) {
                LopThiSinhVien::query()->where('lop_thi_id', $lop_thi_id)->where('stt', '>', $sinh_vien_delete->stt)->decrement('stt');
            }
            DB::commit();
            return $this->responseSuccess($result);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->responseError([], $e->getMessage());
        }
    }
}
