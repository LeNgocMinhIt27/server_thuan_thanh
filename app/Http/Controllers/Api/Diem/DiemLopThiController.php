<?php

namespace App\Http\Controllers\Api\Diem;

use App\Http\Controllers\Controller;
use App\Models\Diem\DiemNhanDienLopThi;
use App\Library\QueryBuilder\QueryBuilder;
use App\Models\Diem\BangDiem;
use App\Models\Diem\Diem;
use App\Models\Diem\DiemNhanDien;
use App\Models\Lop\LopSinhVien;
use App\Models\Lop\LopThi;
use DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DiemLopThiController extends Controller
{
    public function indexAgGrid($id)
    {
        $nhan_dien_lop = DiemNhanDienLopThi::findOrFail($id);
        $lop_thi_id = $nhan_dien_lop->lop_thi_id;
        $data_return = [];
        $diem = DB::table('ph_lop_thi_sinh_viens')
            ->join('ph_diems',  function ($join) {
                $join->on('ph_lop_thi_sinh_viens.sinh_vien_id',  'ph_diems.sinh_vien_id');
                $join->on('ph_lop_thi_sinh_viens.lop_thi_id',  'ph_diems.lop_thi_id');
            })
            ->join('u_sinh_viens', 'ph_diems.sinh_vien_id', '=', 'u_sinh_viens.id')
            ->join('ph_lop_this', 'ph_lop_thi_sinh_viens.lop_thi_id', '=', 'ph_lop_this.id')
            ->join('users', 'ph_diems.nguoi_nhap_id', '=', 'users.id')->orderBy('ph_lop_thi_sinh_viens.stt');
        $diem->select([
            'ph_lop_thi_sinh_viens.stt',
            DB::raw('ph_diems.id as diem_id'),
            'ph_lop_this.ma',
            'ph_lop_this.id as lop_thi_id',
            'ph_lop_this.loai',
            'ph_diems.sinh_vien_id',
            'u_sinh_viens.name',
            'u_sinh_viens.mssv',
            'u_sinh_viens.group',
            'ph_diems.diem',
            'ph_diems.ghi_chu',
            'users.id as nguoi_nhap_id'
        ]);
        $diem->where('ph_lop_thi_sinh_viens.lop_thi_id', $lop_thi_id);
        if (count($diem->get()) <= 0) {
            $query = LopThi::join('ph_lop_thi_sinh_viens', 'ph_lop_thi_sinh_viens.lop_thi_id', '=', 'ph_lop_this.id')
                ->join('u_sinh_viens', 'u_sinh_viens.id', '=', 'ph_lop_thi_sinh_viens.sinh_vien_id')
                ->leftjoin('ph_diems', 'u_sinh_viens.id', '=', 'ph_diems.sinh_vien_id')
                ->where('ph_lop_this.id', $lop_thi_id);
            $query->select(
                'u_sinh_viens.mssv',
                'ph_diems.diem',
                'ph_lop_thi_sinh_viens.stt',
                DB::raw('u_sinh_viens.id as sinh_vien_id'),
                DB::raw('ph_lop_this.id as lop_thi_id'),
                DB::raw('ph_lop_this.ma as ma_lop_thi'),
            );
            // dd($query->get()->toArray());
            $query;
            $data_return[] = ["diem" => $query->get(), "had_diem" => false];
        } else {
            $data_return[] = ["diem" => $diem->get(), "had_diem" => true];
        }

        return response()->json(new \App\Http\Resources\Items($data_return), 200, []);
    }
    public function diemNhanDienList($id)
    {
        $nhan_dien_lop = DiemNhanDienLopThi::findOrFail($id);
        $bang_diem_id = $nhan_dien_lop->bang_diem_id;
        $pages = explode(',', $nhan_dien_lop->page);
        $diem_nhan_dien = DiemNhanDien::query()->where('bang_diem_id', '=', $bang_diem_id);

        $diem_nd = $diem_nhan_dien
            ->where(function ($query) use ($pages) {
                foreach ($pages as $page) {
                    $query->orWhere('page', $page);
                }
            })
            ->get();
        return $this->responseSuccess($diem_nd);
    }
    public function luuDiem(Request $request, $id)
    {
        $now =  Carbon::now();
        $bang_diem = BangDiem::findOrFail($id);
        $nguoi_nhap = $request->get('user');
        $diems = $request->get('diem');
        if ($now->greaterThanOrEqualTo($bang_diem['ngay_cong_khai'])) {
            abort(400, 'Bạn không thể sửa điểm khi đã công bố');
        }
        try {
            DB::beginTransaction();
            foreach ($diems as $diem) {
                Diem::updateOrCreate(
                    [
                        'bang_diem_id' => $id,
                        'lop_thi_id' => $diem['lop_thi_id'],
                        'sinh_vien_id' => $diem['sinh_vien_id'],
                    ],
                    [
                        'nguoi_nhap_id' => $nguoi_nhap['id'],
                        'diem' => $diem['diem'],
                    ]
                );
            };
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            $this->failed($th);
        }

        return $this->responseSuccess();
    }
}
