<?php

namespace App\Http\Controllers\Api\PhucKhao;

use App\Helpers\DiemHelper;
use App\Http\Controllers\Controller;
use App\Library\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Models\PhucKhao\PhucKhao;
use App\Library\QueryBuilder\Filters\AllowedFilter;
use App\Library\QueryBuilder\Filters\Custom\FilterLike;
use App\Library\QueryBuilder\Filters\Custom\FilterRelation;
use App\Models\PhucKhao\MaThanhToan;
use DB;

class PhucKhaoStudentController extends Controller
{
    protected $includes = ['sinhVien', 'lop', 'lopThi', 'maThanhToan', 'bangDiem', 'lopThi', 'sinhVien', 'user'];


    public function store(Request $request)
    {
        $user =  $request->user();
        if (!$user->is_sinh_vien) {
            abort(403);
        }
        $request->validate([
            'lop_id' => 'required|int',
            'lop_thi_id' => 'required|int',
            'ki_hoc' => 'required|string|max:255|min:1',
        ], [], [
            'lop_id' => __('phuc-khao.field.lop_id'),
            'lop_thi_id' => __('phuc-khao.field.lop_thi_id'),
            'ki_hoc' => __('phuc-khao.field.ki_hoc'),
            'ma_thanh_toan' => __('phuc-khao.field.ma_thanh_toan'),
        ]);
        $sinh_vien_id = $user->info_id;
        $data = $request->all();
        $data['trang_thai'] = 'chua_thanh_toan';
        $query = DiemHelper::getQueryDiem([]);
        $query->where('sinh_vien_id', $sinh_vien_id);
        $query->where('is_cong_khai', true);
        $query->where('lop_thi_id', $request->get('lop_thi_id'));
        $diem = $query->first();
        if (empty($diem)) {
            abort(404);
        }
        if (!$diem->is_phuc_khao) {
            abort(400, 'Hết hạn phúc khảo');
        }
        $payment_code = MaThanhToan::where('trang_thai', false)->firstOrFail();
        $data['ma_thanh_toan'] = $payment_code->ma;
        $data = PhucKhao::create($data);
        $payment_code->update([
            'trang_thai' => true
        ]);
        return response()->json(['message' =>  __('phuc-khao.message.success_add'), 'data' => $data]);
    }
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->is_sinh_vien) {
            abort(403);
        }
        $query = PhucKhao::query();
        $query->with('sinhVien', 'lop', 'lopThi');
        $query->where('sinh_vien_id', $user->info_id);
        $result = $query->findOrFail($id);
        return response()->json($result, 200, []);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->is_sinh_vien) {
            abort(403);
        }
        $query = PhucKhao::query();
        $query->with('sinhVien', 'lop', 'lopThi');
        $query->where('sinh_vien_id', $user->info_id);
        $phuckhao = $query->findOrFail($id);
        $phuckhao->delete();
        return response()->json(['message' =>  __('phuc-khao.message.success_delete')]);
    }
    public function indexThanhToan()
    {
        $data = [
            'ten_ngan_hang' => config('app.bank.ten'),
            'so_tai_khoan' => config('app.bank.so_tai_khoan'),
            'ten_tai_khoan' => config('app.bank.ten_tai_khoan'),
            'so_tien' => config('app.bank.so_tien'),
            'image' => config('app.bank.image'),
        ];

        return response()->json($data);
    }
    public function destroyMany(Request $request)
    {
        $ids = $request->input('ids');
        if (!$ids) {
            return response()->json(['message' => 'Không có ID nào được cung cấp.'], 400);
        }

        PhucKhao::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Xóa thành công.']);
    }

    public function indexAgGrid(Request $request)
    {
        $user = $request->user();
        if (!$user->is_sinh_vien) {
            abort(403);
        }
        $user = $request->user();
        $sinh_vien_id = $user->info_id;
        $query = DB::query()->fromSub(function ($query) {
            $query->from('pk_phuc_khaos')
                ->join('u_sinh_viens', 'pk_phuc_khaos.sinh_vien_id', '=', 'u_sinh_viens.id')
                ->join('ph_lop_this', 'pk_phuc_khaos.lop_thi_id', '=', 'ph_lop_this.id')
                ->join('ph_lops', 'pk_phuc_khaos.lop_id', '=', 'ph_lops.id');
            $query->orderBy('pk_phuc_khaos.id');
            $query->select([
                'pk_phuc_khaos.id',
                'pk_phuc_khaos.sinh_vien_id',
                'u_sinh_viens.mssv',
                'pk_phuc_khaos.ki_hoc',
                'ph_lops.ma',
                'ph_lop_this.ma',
                'pk_phuc_khaos.ma_thanh_toan',
                'ph_lops.ma as ma_lop', //lop
                'ph_lop_this.ma as ma_lop_thi', //lop thi
                'pk_phuc_khaos.trang_thai',
            ]);
        }, 'phuc_khaos');
        $query->where('sinh_vien_id', $sinh_vien_id);
        $query = QueryBuilder::for($query, $request)
            ->allowedAgGrid([])
            ->defaultSort('id')
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->paginate()), 200, []);
    }
}
