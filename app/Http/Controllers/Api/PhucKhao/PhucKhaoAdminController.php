<?php

namespace App\Http\Controllers\Api\PhucKhao;

use App\Helpers\DiemHelper;
use App\Http\Controllers\Controller;
use App\Library\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Models\PhucKhao\PhucKhao;
use App\Library\QueryBuilder\Filters\AllowedFilter;
use App\Library\QueryBuilder\Filters\Custom\FilterRelation;
use App\Models\PhucKhao\MaThanhToan;
use DB;

class PhucKhaoAdminController extends Controller
{
    public function indexAgGrid(Request $request)

    {
        $query = PhucKhao::query()->with(['sinhVien', 'lop', 'lopThi']);
        $query = QueryBuilder::for($query, $request)
            ->allowedSorts(['sinh_vien_id', 'ki_hoc', 'lop_id', 'lop_thi_id', 'trang_thai'])
            ->allowedIncludes(['sinhVien', 'lop', 'lopThi'])
            ->defaultSort('created_at')
            ->allowedPagination()
            ->allowedAgGrid([]);
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
    public function update(Request $request, $id)
    {
        $phuckhao = PhucKhao::with('sinhVien', 'lop', 'lopThi')->findOrFail($id);
        $phuckhao->trang_thai = $request->get('trang_thai', 'chua_thanh_toan');
        $phuckhao->save();
        return response()->json(['message' =>  __('phuc-khao.message.success_edit')]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $query = PhucKhao::query();
        $query->with('sinhVien', 'lop', 'lopThi');
        $result = $query->findOrFail($id);
        return response()->json($result, 200, []);
    }
    public function destroy(Request $request, $id)
    {
        $query = PhucKhao::query();
        $phuckhao = $query->findOrFail($id);
        $phuckhao->delete();
        return response()->json(['message' =>  __('phuc-khao.message.success_delete')]);
    }
}
