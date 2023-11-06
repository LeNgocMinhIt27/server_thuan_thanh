<?php

namespace App\Http\Controllers\Api\Diem;

use App\Helpers\DiemHelper;
use App\Http\Controllers\Controller;
use App\Library\QueryBuilder\Filters\AllowedFilter;
use App\Library\QueryBuilder\Filters\Custom\FilterLike;
use App\Library\QueryBuilder\Filters\Custom\FilterRelation;
use App\Library\QueryBuilder\QueryBuilder;
use App\Models\Diem\Diem;
use Illuminate\Http\Request;
use DB;

class DiemController extends Controller
{
    protected $includes = ['bangDiem', 'lopThi', 'sinhVien', 'user'];

    public function indexDiemSinhVien(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->is_sinh_vien) {
            abort(403);
        }
        $check_phuc_khao = $request->boolean('check_phuc_khao');
        $query = DiemHelper::getQueryDiem(['check_phuc_khao' => $check_phuc_khao]);
        $query->where('sinh_vien_id', $user->info_id);
        $query->where('is_cong_khai', true);
        $query->where('id', $id);
        $diem = $query->first();
        if (empty($diem)) {
            abort(404);
        }
        return response()->json($diem, 200, []);
    }

    public function indexAgGrid(Request $request)
    {
        $user = $request->user();
        $sinh_vien_id = $user->info_id;

        $query = DiemHelper::getQueryDiem([]);
        $query->where('sinh_vien_id', $sinh_vien_id);
        $query->where('is_cong_khai', true);
        $query = QueryBuilder::for($query, $request)
            ->allowedAgGrid([])
            ->defaultSort('id')
            ->allowedFilters([
                AllowedFilter::custom('ph_lop_giao_viens', new FilterRelation('giaoViens', 'id')),
                AllowedFilter::custom('ma_hp', new FilterLike()),
                AllowedFilter::custom('mssv', new FilterLike()),
                'ki_hoc'
            ])
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->paginate()), 200, []);
    }

    public function indexForLopThi($id)
    {
        $query = DB::table('ph_lop_thi_sinh_viens')
            ->leftJoin('ph_diems',  function ($join) {
                $join->on('ph_lop_thi_sinh_viens.sinh_vien_id',  'ph_diems.sinh_vien_id');
                $join->on('ph_lop_thi_sinh_viens.lop_thi_id',  'ph_diems.lop_thi_id');
            })
            ->join('u_sinh_viens', 'ph_lop_thi_sinh_viens.sinh_vien_id', '=', 'u_sinh_viens.id');
        $query->orderBy('ph_lop_thi_sinh_viens.stt');
        $query->select([
            'ph_lop_thi_sinh_viens.stt',
            DB::raw('ph_diems.id as diem_id'),
            'ph_lop_thi_sinh_viens.sinh_vien_id',
            'u_sinh_viens.name',
            'u_sinh_viens.mssv',
            'u_sinh_viens.group',
            'ph_diems.diem',
            'ph_diems.ghi_chu'
        ]);
        $query->where('ph_lop_thi_sinh_viens.lop_thi_id', $id);
        return $query->get();
    }
}
