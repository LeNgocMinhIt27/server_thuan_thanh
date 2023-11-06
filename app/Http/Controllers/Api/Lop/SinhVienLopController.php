<?php

namespace App\Http\Controllers\Api\Lop;

use App\Http\Controllers\Controller;
use App\Library\QueryBuilder\Filters\AllowedFilter;
use App\Library\QueryBuilder\Filters\Custom\FilterLike;
use App\Library\QueryBuilder\Filters\Custom\FilterRelation;
use App\Library\QueryBuilder\QueryBuilder;
use App\Models\Lop\Lop;
use App\Models\User\SinhVien;
use DB;
use Illuminate\Http\Request;

class SinhVienLopController extends Controller
{
    protected $includes = ['giaoViens', 'sinhViens', 'lanDiemDanhs'];
    public function indexAgGird(Request $request)
    {
        $user = $request->user();
        $query = Lop::query();
        if (!$user->is_sinh_vien) {
            abort(403);
        }
        $query->whereHas('sinhViens', function ($query) use ($user) {
            $query->where('id', $user->info_id);
        });
        $query = QueryBuilder::for($query, $request)
            ->allowedSearch(['ma', 'ma_kem', 'ma_hp', 'ten_hp', 'ki_hoc'])
            ->allowedAgGrid([])
            ->allowedFilters([
                AllowedFilter::custom('ph_lop_giao_viens', new FilterRelation('giaoViens', 'id')),
                AllowedFilter::custom('ma_kem', new FilterLike()),
                AllowedFilter::custom('ma_hp', new FilterLike()),
                AllowedFilter::custom('ten_hp', new FilterLike()),
                AllowedFilter::custom('ma', new FilterLike()),
                'ki_hoc'
            ])
            ->defaultSort('ma')
            ->allowedIncludes($this->includes)
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->is_sinh_vien) {
            abort(403);
        }
        $query = Lop::query()->with(['giaoViens', 'lanDiemDanhs', 'lanDiemDanhs.diemDanhs' => function ($query) use ($user) {
            $query->where('sinh_vien_id', $user->info_id);
        }, 'sinhViens' => function ($query) use ($user) {
            $query->where('sinh_vien_id', $user->info_id);
        }]);
        $query->whereHas('sinhViens', function ($query) use ($user) {
            $query->where('id', $user->info_id);
        });
        $query = QueryBuilder::for($query, $request)
            ->allowedIncludes($this->includes);
        return response()->json($query->findOrFail($id), 200, []);
    }

    public function diemDanh(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->is_sinh_vien) {
            abort(403);
        }

        $query = DB::table('ph_diem_danhs')
            ->join('ph_lan_diem_danhs', 'ph_diem_danhs.lan_diem_danh_id', '=', 'ph_lan_diem_danhs.id')
            ->join('ph_lops', 'ph_lan_diem_danhs.lop_id', '=', 'ph_lops.id')
            ->join('u_sinh_viens', 'ph_diem_danhs.sinh_vien_id', '=', 'u_sinh_viens.id');

        $query->select([
            'ph_diem_danhs.id',
            DB::raw('ph_lops.ma as ma_lop'),
            'ph_lops.loai',
            'ph_lan_diem_danhs.lan',
            'ph_lan_diem_danhs.ngay_diem_danh',
            'u_sinh_viens.mssv',
            'ph_diem_danhs.co_mat',
            'ph_diem_danhs.ghi_chu'
        ]);
        $query->orderBy('ph_lan_diem_danhs.ngay_diem_danh');
        $query->where('ph_diem_danhs.sinh_vien_id', $user->info_id)
            ->where('ph_diem_danhs.lop_id', $id);

        return response()->json($query->get(), 200, []);
    }
    public function showItem(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->is_sinh_vien) {
            abort(403);
        }
        $query = Lop::find($id);
        return response()->json($query->findOrFail($id), 200, []);
    }
}
