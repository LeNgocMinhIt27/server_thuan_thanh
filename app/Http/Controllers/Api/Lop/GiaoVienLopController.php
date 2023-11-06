<?php

namespace App\Http\Controllers\Api\Lop;

use App\Http\Controllers\Controller;
use App\Library\QueryBuilder\QueryBuilder;
use App\Models\Lop\Lop;
use App\Models\User\GiaoVien;
use Illuminate\Http\Request;

class GiaoVienLopController extends Controller
{
    public function indexAgGird(Request $request)
    {
        $user = $request->user();
        $query = Lop::query();
        if (isset($user->info_id))
            $query->whereHas('giaoViens', function ($query) use ($user) {
                $query->where('id', $user->info_id);
            });
        $query = QueryBuilder::for($query, $request)
            ->allowedSearch(['ma', 'ma_kem', 'ma_hp', 'ten_hp', 'ki_hoc'])
            ->allowedAgGrid([])
            ->allowedFilters(['ma', 'ma_kem', 'ma_hp', 'ten_hp', 'ki_hoc'])
            ->defaultSort('ma')
            ->allowedIncludes(Lop::INCLUDE)
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $query = Lop::query();
        if (isset($user->info_id))
            $query->whereHas('giaoViens', function ($query) use ($user) {
                $query->where('id', $user->info_id);
            });
        $query = QueryBuilder::for($query, $request)
            ->allowedIncludes(Lop::INCLUDE);
        return response()->json($query->findOrFail($id), 200, []);
    }
    public function indexSinhVien(Request $request, $id)
    {
        // $user = $request->user();
        $query = Lop::query()->with('sinhViens');
        // $query->whereHas('giaoViens', function ($query) use ($user) {
        //     $query->where('id', $user->info_id);
        // });
        $lop = $query->find($id);
        return response()->json($lop->sinhViens, 200, []);
    }
    public function listThongKe(Request $request)
    {
        $user = $request->user();
        $query = GiaoVien::query();
        if (isset($user->info_id))
            $query->whereHas('giaoViens', function ($query) use ($user) {
                $query->where('id', $user->info_id);
            });
        $query = QueryBuilder::for($query, $request)
            ->allowedSearch(['ma', 'ma_kem', 'ma_hp', 'ten_hp', 'ki_hoc'])
            ->allowedAgGrid([])
            ->allowedFilters(['ma', 'ma_kem', 'ma_hp', 'ten_hp', 'ki_hoc'])
            ->defaultSort('ma')
            ->allowedIncludes(Lop::INCLUDE)
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
}
