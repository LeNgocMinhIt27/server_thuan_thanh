<?php

namespace App\Http\Controllers\Api\Lop;

use App\Constants\RoleCode;
use App\Helpers\DiemChuyenCanHelper;
use App\Helpers\SettingHelper;
use App\Traits\ResponseType;
use App\Http\Controllers\Controller;
use App\Models\Lop\Lop;
use Illuminate\Http\Request;
use App\Library\QueryBuilder\QueryBuilder;
use App\Library\QueryBuilder\Filters\AllowedFilter;
use App\Library\QueryBuilder\Filters\Custom\FilterLike;
use App\Library\QueryBuilder\Filters\Custom\FilterRelation;
use App\Models\Lop\LopSinhVien;
use App\Models\Lop\DiemDanh;
use App\Models\Lop\LanDiemDanh;
use DB;
use Illuminate\Validation\Rule;

class LopHocController extends Controller
{
    use ResponseType;
    protected $includes = ['giaoViens', 'sinhViens', 'lanDiemDanhs'];
    public function index(Request $request)
    {
        $query = Lop::query();
        $query = QueryBuilder::for($query, $request)
            ->allowedSearch(['ma', 'ma_kem', 'ma_hp', 'ten_hp', 'ki_hoc'])
            ->allowedFilters([
                AllowedFilter::custom('ph_lop_giao_viens', new FilterRelation('giaoViens', 'id')),
                AllowedFilter::custom('ma_kem', new FilterLike()),
                AllowedFilter::custom('ma_hp', new FilterLike()),
                AllowedFilter::custom('ten_hp', new FilterLike()),
                'ki_hoc'
            ])
            ->allowedIncludes(Lop::INCLUDE)
            ->defaultSorts(['-ki_hoc', 'ma'])
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }

    public function indexAgGrid(Request $request)
    {
        $query = Lop::query();
        $query = QueryBuilder::for($query, $request)
            ->allowedSearch(['ma', 'ma_kem', 'ma_hp', 'ten_hp', 'ki_hoc'])
            ->allowedAgGrid([])
            ->allowedFilters([
                AllowedFilter::custom('ph_lop_giao_viens', new FilterRelation('giaoViens', 'id')),
                // AllowedFilter::custom('ma_kem', new FilterLike()),
                // AllowedFilter::custom('ma_hp', new FilterLike()),
                // AllowedFilter::custom('ten_hp', new FilterLike()),
                // 'ki_hoc'
            ])
            ->allowedIncludes(Lop::INCLUDE)
            ->defaultSorts(['-ki_hoc', 'ma'])
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
    public function show(Request $request, $id)
    {
        $query = Lop::query();
        $query = QueryBuilder::for($query, $request)
            ->allowedIncludes(Lop::INCLUDE);
        return response()->json($query->findOrFail($id), 200, []);
    }
    public function store(Request $request)
    {
        $request->validate([
            'ma' => [
                'required', 'string', 'max:255', 'min:1', Rule::unique('ph_lops')->where(
                    function ($query) use ($request) {
                        $query->where('ki_hoc', $request->ki_hoc);
                    }
                )
            ],
            'ma_hp' => 'required|string|max:255|min:1',
            'ten_hp' => 'required|string|max:255|min:1',
            'ki_hoc' => 'required|string'
        ], [], [
            'ma' => __('lop.field.ma'),
            'ma_hp' => __('lop.field.ma_hp'),
            'ten_hp' => __('lop.field.ten_hp'),
            'ki_hoc' => __('lop.field.ki_hoc')
        ]);
        $giao_viens_ids = $request->input('giao_viens');
        $info = $request->all();
        $lop = Lop::create($info);
        $lop->giaoViens()->sync($giao_viens_ids);
        return $this->responseSuccess($lop);
    }
    public function showDetail(Request $request, $id)
    {
        $query = Lop::query();
        $query->with('giaoViens', 'sinhViens');
        $result = $query->find($id);
        return response()->json($result, 200, []);
    }
    public function update(Request $request, $id)
    {
        // return ($request);
        $request->validate([
            'ma' => [
                'required', 'string', 'max:255', 'min:1', Rule::unique('ph_lops')->ignore($id)->where(
                    function ($query) use ($request) {
                        $query->where('ki_hoc', $request->ki_hoc);
                    }
                )
            ],
            'ma_hp' => 'required|string|max:255|min:1',
            'ten_hp' => 'required|string|max:255|min:1',
            'ki_hoc' => 'required|string|max:255|min:1',
            'is_dai_cuong' => 'required|boolean'
        ], [], [
            'ma' => __('lop.field.ma'),
            'ma_hp' => __('lop.field.ma_hp'),
            'ten_hp' => __('lop.field.ten_hp'),
            'ki_hoc' => __('lop.field.ki_hoc'),
        ]);

        $giao_viens_ids = $request->input('giao_viens');
        // $sinh_vien_ids = $request->input('sinh_viens');
        $info = $request->all();
        $lop = Lop::findOrFail($id);
        $lop->giaoViens()->sync($giao_viens_ids);
        // $lop->sinhViens()->sync($sinh_vien_ids);
        $result = $lop->update($info);
        return $this->responseSuccess($result);
    }
    public function addSinhVien(Request $request, $id)
    {
        $request->validate([
            'stt' => 'required|integer|min:1',
            'sinh_viens' => 'required|integer',
            'nhom' => 'required',
        ], [
            'stt.min' => 'Trường stt không được phép nhỏ hơn 1',
            'stt.interger' => 'Trường stt phải là số nguyên',
        ]);
        $sinh_vien_ids = $request->get('sinh_viens');
        $stt = $request->get('stt');
        $nhom = $request->get('nhom');
        $children = $request->children;

        try {
            DB::beginTransaction();

            foreach ($children as $item) {
                $other_class = LopSinhVien::query()->where("lop_id", $item['id'])->where('sinh_vien_id', $sinh_vien_ids);
                $ma_other_class = $item['ma'];
                if ($other_class->get()->count()) {
                    return response()->json([

                        "message" => "The given data was invalid.",
                        "errors" => [
                            "sinh_viens" => [
                                "sinh viên này đã tồn tại trong lớp $ma_other_class"
                            ]
                        ],
                    ], 422);
                }
            }
            if ($stt < 1) {
                abort(400, 'Trường stt sinh viên không được phép nhỏ hơn 1');
            }
            $lop = Lop::findOrFail($id);

            $list_stt = LopSinhVien::where('lop_id', $lop->id)->pluck('stt');
            if ($stt == null) {
                $stt = $list_stt->max() + 1;
            }

            $sinhvien_exists = LopSinhVien::where('lop_id', $lop->id)->where('sinh_vien_id', $sinh_vien_ids)->exists();
            $next_sinh_vien_greater = LopSinhVien::where('lop_id', $lop->id)->where('stt', $stt + 1)->exists();
            $next_sinh_vien_equal = LopSinhVien::where('lop_id', $lop->id)->where('stt', $stt)->exists();

            if ($sinhvien_exists) {
                return response()->json(['error' => 'Sinh viên đã tồn tại trong lớp'], 409);
            }
            $lop->sinhViens()->syncWithoutDetaching([
                $sinh_vien_ids => ['stt' => $stt, 'nhom' => $nhom],
            ]);
            if ($next_sinh_vien_greater && $next_sinh_vien_equal) {
                LopSinhVien::where('lop_id', $lop->id)->where('stt', '>=', $stt)->increment('stt');
            } elseif (!$next_sinh_vien_greater && $next_sinh_vien_equal) {
                LopSinhVien::where('lop_id', $lop->id)->where('stt', '=', $stt)->increment('stt');
            }
            $lop_parent = $lop->parent;

            $lan_diem_danhs = $lop->lanDiemDanhs;
            if (!$lan_diem_danhs->isEmpty()) {
                foreach ($lan_diem_danhs as $lan_diem_danh) {
                    DiemDanh::create([
                        'lan_diem_danh_id' => $lan_diem_danh->id,
                        'sinh_vien_id' => $sinh_vien_ids,
                        'lop_id' => $lop->id,
                        'ma_lop' => $lop->ma,
                        'co_mat' => true
                    ]);
                }
            }
            if (!empty($lop_parent)) {
                $lan_diem_danhs = $lop_parent->lanDiemDanhs;
                if (!$lan_diem_danhs->isEmpty()) {
                    foreach ($lan_diem_danhs as $lan_diem_danh) {
                        DiemDanh::create([
                            'lan_diem_danh_id' => $lan_diem_danh->id,
                            'sinh_vien_id' => $sinh_vien_ids,
                            'lop_id' => $lop->id,
                            'ma_lop' => $lop->ma,
                            'co_mat' => true
                        ]);

                        $lop->sinhViens()->syncWithoutDetaching([
                            $sinh_vien_ids => ['stt' => $stt, 'nhom' => $nhom],
                        ]);
                    }
                }
            }
            DB::commit();
            //Tính điểm chuyên cần cho sinh viên

            $diem_chuyen_can = DiemChuyenCanHelper::tinhDiemChuyenCan($sinh_vien_ids, $lop->id);
            $lop->sinhViens()->syncWithoutDetaching([
                $sinh_vien_ids => ['stt' => $stt, 'diem' => $diem_chuyen_can],
            ]);
            return $this->responseSuccess($lop);
        } catch (\Throwable $th) {
            DB::beginTransaction();
            throw $th;
            abort(400, 'Không thể thêm sinh viên vào các lần điểm danh trước');
        }
    }
    // public function updateSinhVien(Request $request, $id)
    // {
    //     $sinh_vien_ids = $request->input('sinh_viens');
    //     $stt = $request->input('stt');
    //     $lop = Lop::findOrFail($id);

    //     $lop->sinhViens()->syncWithoutDetaching([
    //         $sinh_vien_ids => ['stt' => $stt],
    //     ]);
    //     return $this->responseSuccess($lop);
    // }
    public function destroy($id)
    {
        $lop = Lop::findOrFail($id);
        $result = $lop->delete($lop);
        return $this->responseSuccess($result);
    }
    public function lopGiaoVien(Request $request)
    {
        $user = $request->user();
        $ki_hoc = $request->get('ki_hoc');

        $query = Lop::query()->whereHas('giaoViens', function ($query) use ($user) {
            if ($user->allow(RoleCode::ADMIN) || $user->allow(RoleCode::ASSISTANT)) {
                $query->select('*');
            } else {
                $query->where('id', $user->info->id);
            }
        })->where('ki_hoc', $ki_hoc);

        return $this->responseSuccess($query->get());
    }
    public function listLopDiemDanh(Request $request)
    {
        $request->validate([
            'ki_hoc' => 'required',
            'lan_diem_danh' => 'required|numeric',
            'loai_lop' => 'required',
            'tuan_diem_danh' => 'required|numeric'
        ]);
        $ki_hoc = $request->ki_hoc;
        $lan_diem_danh = $request->lan_diem_danh;
        $filter_tuan = $request->tuan_diem_danh;
        if (isset($ki_hoc)) { // Kiểm tra xem $ki_hoc đã được đặt giá trị hay chưa
            $query = Lop::with('giaoViens', 'lanDiemDanhs')->where('ki_hoc', $ki_hoc)->where('is_dai_cuong', $request->loai_lop)->get();
            $list_lop = [];
            foreach ($query as $tuan_hoc) {
                $lich_hoc = SettingHelper::getLichHoc($tuan_hoc->tuan_hoc);
                if (in_array($filter_tuan, $lich_hoc)) {
                    $list_lop[] = $tuan_hoc;
                }
            }
            foreach ($list_lop as $diem_danh) {
                $count = $diem_danh->lanDiemDanhs->where('lan', $lan_diem_danh)->count();
                [$result_mo, $result_dong, $tuan_mo, $tuan_dong] = SettingHelper::getKhoangNgayChoLanDiemDanh($diem_danh, $lan_diem_danh);
                $diem_danh['count'] = $count;
                $diem_danh['tuan_mo'] = $tuan_mo;
                $diem_danh['tuan_dong'] = $tuan_dong;
                $diem_danh['ngay_mo_setting'] = $result_mo;
                $diem_danh['ngay_dong_setting'] = $result_dong;
            }
            return response()->json(new \App\Http\Resources\Items($list_lop), 200, []);
        }
    }
}