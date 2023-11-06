<?php

namespace App\Http\Controllers\Api\BaoLoi;

use App\Constants\RoleCode;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BaoLoi\BaoLoi;
// use App\Traits\ResponseType;
use App\Library\QueryBuilder\Filters\AllowedFilter;
use App\Library\QueryBuilder\QueryBuilder;
use App\Library\QueryBuilder\Filters\Custom\FilterDateRange;
use App\Library\QueryBuilder\Filters\Custom\FilterLike;
use App\Library\QueryBuilder\Filters\Custom\FilterRelation;
use DB;
use Response;

class BaoLoiController extends Controller
{

    public function listErrorAll(Request $request)
    {
        $query = DB::query()->fromSub(function ($query) {
            $query->from('bao_lois')
                ->join('ph_lops', 'bao_lois.lop_id', '=', 'ph_lops.id')
                ->join('ph_lop_this', 'bao_lois.lop_thi_id', '=', 'ph_lop_this.id')
                ->join('u_sinh_viens', 'bao_lois.sinh_vien_id', '=', 'u_sinh_viens.id');
            $query->orderBy('bao_lois.id');
            $query->select([
                'bao_lois.id',
                'bao_lois.ki_hoc',
                'bao_lois.tieu_de',
                'bao_lois.ghi_chu',
                'bao_lois.trang_thai',
                'bao_lois.ly_do',
                'bao_lois.lop_id',
                'bao_lois.sinh_vien_id',
                DB::raw('ph_lops.id as id_lop'), //lop
                DB::raw('ph_lop_this.ma as ma_lop_thi'), //lop
                // DB::raw('ph_lops.ghi_chu as lop_ghi_chu'), //lop
                'ph_lops.ma',
                'ph_lops.ma_hp',
                'ph_lops.ten_hp',
                'ph_lops.phong',
                'ph_lops.ma_kem',
                'ph_lops.loai',
                'u_sinh_viens.mssv'
            ]);
        }, 'bang_diems');
        $query = QueryBuilder::for($query, $request)
            ->allowedAgGrid([])
            ->allowedSorts(['ki_hoc'])
            ->defaultSort('ki_hoc')
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
    public function listError(Request $request)
    {
        $user_id = $request->user();
        $query = DB::query()->fromSub(function ($query) {

            $query->from('bao_lois')
                ->join('ph_lops', 'bao_lois.lop_id', '=', 'ph_lops.id');
            $query->orderBy('bao_lois.id');
            $query->select([
                'bao_lois.id',
                'bao_lois.ki_hoc',
                'bao_lois.tieu_de',
                'bao_lois.ghi_chu',
                'bao_lois.trang_thai',
                'bao_lois.ly_do',
                'bao_lois.lop_id',
                'bao_lois.sinh_vien_id',
                DB::raw('ph_lops.id as id_lop'), //lop
                // DB::raw('ph_lops.ghi_chu as lop_ghi_chu'), //lop
                'ph_lops.ma',
                'ph_lops.ma_hp',
                'ph_lops.ten_hp',
                'ph_lops.phong',
                'ph_lops.ma_kem',
                'ph_lops.loai',
            ]);
        }, 'bang_diems');
        if ($user_id->is_sinh_vien) {
            $query->where('sinh_vien_id', $user_id->info_id);
        }

        $query = QueryBuilder::for($query, $request)
            ->allowedAgGrid([])
            ->allowedSorts(['ki_hoc'])
            ->defaultSort('ki_hoc')
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
    public function store(Request $request)
    {

        $user =  $request->user();
        if (!$user->is_sinh_vien) {
            abort(403);
        }
        $request->validate([
            'ki_hoc' => 'required|string|max:255|min:0',
            'lop_id' => 'required|int',
            'lop_thi_id' => 'required|int',
            'tieu_de' => 'required|string|max:255|min:0',
            'ly_do' => 'string',
        ]);
        $data = $request->all();
        $data['sinh_vien_id'] = $user->info_id;
        $data = BaoLoi::create($data);
        return response()->json(['message' =>  __('bao-loi.message.success_desc_uploadForm')]);
    }
    public function update(Request $request, $id)
    {
        $user = $request->user();
        // if (!$user->allow(RoleCode::ADMIN) || !$user->allow(RoleCode::ASSISTANT)) {
        //     abort(403);
        // }
        $user = BaoLoi::where('id', $id)
            ->firstOrFail();
        $user->trang_thai = $request->trang_thai;
        $user->save();
        return response()->json(['message' => __('bao-loi.message.update_trang_thai')]);
    }

    public function destroy(Request $request, string $id)
    {
        $query = BaoLoi::query();
        $user = $request->user();
        if ($user->is_sinh_vien) {
            $query->where('sinh_vien_id', $user->info_id);
        } else if (!$user->allow(RoleCode::ADMIN) || !$user->allow(RoleCode::ASSISTANT)) {
            abort(403);
        }
        $baoLoi = $query->findOrFail($id);
        $baoLoi->delete();
        return response()->json(['message' => __('bao-loi.message.success_delete')]);
    }
    public function adminDelete(Request $request, string $id)
    {
        $query = BaoLoi::query();
        $baoLoi = $query->findOrFail($id);
        $baoLoi->delete();
        return response()->json(['message' => __('bao-loi.message.success_delete')]);
    }
}
