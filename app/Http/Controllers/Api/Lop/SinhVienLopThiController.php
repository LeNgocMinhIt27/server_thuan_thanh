<?php

namespace App\Http\Controllers\Api\Lop;

use App\Http\Controllers\Controller;
use App\Library\QueryBuilder\Filters\AllowedFilter;
use App\Library\QueryBuilder\Filters\Custom\FilterLike;
use App\Library\QueryBuilder\Filters\Custom\FilterRelation;
use App\Library\QueryBuilder\QueryBuilder;
use App\Models\Lop\LopThi;
use App\Models\User\SinhVien;
use DB;
use Illuminate\Http\Request;

class SinhVienLopThiController extends Controller
{
    protected $includes = ['sinhViens', 'giaoViens', 'lopThiSinhVien', 'lop'];
    public function indexAgGird(Request $request)
{
    $user = $request->user();

    $query = DB::query()->fromSub(function ($query) use ($user) {
        $query->from('ph_lop_this')
            ->join('ph_lops', 'ph_lop_this.lop_id', '=', 'ph_lops.id')
            ->orderBy('ph_lop_this.id')
            ->select([
                'ph_lop_this.id',
                'ph_lop_this.ma',
                'ph_lop_this.ngay_thi',
                'ph_lop_this.loai',
                'ph_lop_this.phong_thi',
                'ph_lop_this.lop_id',
                'ph_lop_this.kip_thi',
                'ph_lops.ki_hoc',
            ])
            ->whereExists(function ($subquery) use ($user) {
                $subquery->from('ph_lop_thi_sinh_viens')
                    ->whereColumn('ph_lop_thi_sinh_viens.lop_thi_id', 'ph_lop_this.id')
                    ->where('ph_lop_thi_sinh_viens.sinh_vien_id', $user->info_id);
            });
    }, 'lop_thi');

    $query = $query->orderByRaw(
        "CAST(substring(substring(kip_thi from '^(\\d+)'), '^(\\d+)') as INTEGER)"
    );

    $query = QueryBuilder::for($query, $request)
        ->allowedSearch(['ma', 'loai', 'ngay_thi', 'kip_thi', 'phong_thi', "ki_hoc"])
        ->allowedAgGrid([])
        ->allowedFilters([
            AllowedFilter::custom('kip_thi', new FilterLike()),
            AllowedFilter::custom('phong_thi', new FilterLike()),
            AllowedFilter::custom('ma', new FilterLike()),
            'ki_hoc' 
            ])
        ->allowedSorts(['ki_hoc'])
        ->defaultSort('ki_hoc')
        ->allowedPagination();

    return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
}
}
