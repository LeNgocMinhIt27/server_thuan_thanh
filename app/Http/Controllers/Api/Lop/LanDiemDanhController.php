<?php

namespace App\Http\Controllers\Api\Lop;

use App\Constants\RoleCode;
use App\Helpers\DiemChuyenCanHelper;
use App\Helpers\HustHelper;
use App\Helpers\SettingHelper;
use App\Http\Controllers\Controller;
use App\Jobs\DiemChuyenCan;
use App\Library\QueryBuilder\QueryBuilder;
use App\Models\Lop\DiemDanh;
use App\Models\Lop\LanDiemDanh;
use App\Models\Lop\Lop;
use App\Models\Setting;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use DateTime;

class LanDiemDanhController extends Controller
{
    protected $includes = ['diemDanhs', 'lop', 'diemDanhs.sinhVien', 'lop.children'];
    public function index(Request $request)
    {
        $query = LanDiemDanh::query();
        $query = QueryBuilder::for($query, $request)
            ->allowedIncludes($this->includes)
            ->allowedFilters(['lop_id'])
            ->allowedSorts(['lop_id', 'lan', 'ngay_diem_danh'])
            ->defaultSort('created_at')
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->paginate()), 200, []);
    }
    public function show(Request $request, $id)
    {
        $query = LanDiemDanh::query();
        $query = QueryBuilder::for($query, $request)
            ->allowedIncludes($this->includes);
        return response()->json($query->findOrFail($id)->setAppends(['is_qua_han']), 200, []);
    }
    public function store(Request $request)
    {
        $request->validate([
            'lop_id' => ['required', 'integer'],
        ], [
            'lop_id.required' => 'Mã lớp là bắt buộc',
            'lop_id.integer' => 'Giá trị của lớp phải là số nguyên'
        ]);
        $user = $request->user();
        $lop_id = $request->get('lop_id');
        $lop = Lop::with(['sinhViens', 'children.sinhViens', 'giaoViens'])->findOrFail($lop_id);
        $ki_hoc_lop = $lop->ki_hoc;
        $ki_hoc_setting = Setting::where('setting_name', 'ki_hoc')->first();
        $loai_lop = $lop->loai;
        $lop_children = $lop->children;
        $is_have_child = !empty($lop_children) && count($lop_children) > 0;
        if ($user->allow(RoleCode::TEACHER) && $ki_hoc_lop != $ki_hoc_setting->setting_value) {
            abort(400, "Bạn không thể tạo mới khi môn khác kì học $ki_hoc_setting->setting_value");
        }
        if (!$is_have_child && count($lop->sinhViens) == 0) {
            return response()->json(['message' => 'Lớp hiện tại không có sinh viên, không thể điểm danh'], 400);
        }
        try {
            DB::beginTransaction();
            $maxLan = LanDiemDanh::select('lop_id', DB::raw('MAX(lan) as max_lan'))
                ->groupBy('lop_id')
                ->where('lop_id', $lop_id)
                ->get();

            $so_lan_diem_danh_toi_da = SettingHelper::getConfig('config.so_lan_diem_danh_toi_da')->setting_value ?? null;
            $ngay_tao = Carbon::now();
            $foundItems = collect($maxLan)->filter(function ($item) use ($lop_id) {
                return $item['lop_id'] === $lop_id;
            });
            $foundItems = array_values($foundItems->toArray());
            $lan =  ($foundItems[0]['max_lan'] ?? 0) + 1;
            $count_diem_danh = LanDiemDanh::where('lop_id', $lop_id)->count();
            if ($loai_lop == "LT+BT") {
                $lan = ceil(($count_diem_danh + 1) / 2);
            }
            if (isset($so_lan_diem_danh_toi_da) && $lan > $so_lan_diem_danh_toi_da) {
                abort(400, 'Đã quá số lần điểm danh cho phép');
            }
            $info = [
                'lop_id' => $lop_id,
                'lan' => $lan,
                'ngay_diem_danh' => Carbon::now(),
            ];
            [$result_mo, $result_dong, $tuan_mo, $tuan_dong] = $this->getKhoangNgayChoLanDiemDanh($lop, $lan);
            if ($user->allow(RoleCode::ADMIN) || $user->allow(RoleCode::ASSISTANT)) {
                // foreach ($lop->giaoViens as $giaoVien) {
                //     if ($giaoVien->id == $user->info_id) {
                //         if (isset($result_mo) && $result_mo > $ngay_tao) {
                //             abort(400, "Không thể tạo điểm danh khi chưa đến thời gian tạo điểm danh $lan. Hãy liên hệ với trợ lý");
                //         }
                //         if (isset($result_dong) && $ngay_tao > $result_dong) {
                //             abort(400, "Không thể tạo điểm danh khi đã quá hạn. Hãy liên hệ với trợ lý");
                //         }
                //         if ($request->has('ngay_diem_danh')) {
                //             $info['ngay_diem_danh'] = new Carbon($request->get('ngay_diem_danh'));
                //         }
                //     }
                // }
                $info['ngay_diem_danh'] = $request->input('ngay_diem_danh');
                $info['ngay_mo_diem_danh'] = $request->input('ngay_mo_diem_danh');
                $info['ngay_dong_diem_danh'] = $request->input('ngay_dong_diem_danh');
            } else if ($user->role_code == RoleCode::TEACHER) {
                if (isset($result_mo) && $result_mo > $ngay_tao) {
                    abort(400, "Không thể tạo điểm danh khi chưa đến thời gian tạo điểm danh lần $lan. Tuần điểm danh đợt $lan là tuần $tuan_mo tới tuần $tuan_dong. Hãy liên hệ với trợ lý");
                }
                if (isset($result_dong) && $ngay_tao > $result_dong) {
                    abort(400, "Không thể tạo điểm danh khi đã quá hạn lần $lan. Hạn điểm danh từ lần $lan tuần $tuan_mo đến $tuan_dong. Hãy liên hệ với trợ lý");
                }
                if ($request->has('ngay_diem_danh')) {
                    $info['ngay_diem_danh'] = new Carbon($request->get('ngay_diem_danh'));
                }
            }
            $lan_diem_danh = LanDiemDanh::create($info);
            if ($lop_children && count($lop_children) > 0) {
                foreach ($lop_children as $child) {
                    foreach ($child->sinhViens as $key => $sinh_vien) {
                        DiemDanh::create([
                            'lan_diem_danh_id' => $lan_diem_danh->id,
                            'sinh_vien_id' => $sinh_vien->id,
                            'lop_id' => $child->id,
                            'ma_lop' => $child->ma,
                            'co_mat' => true
                        ]);
                    }
                }
            } else {
                foreach ($lop->sinhViens as $key => $sinh_vien) {
                    DiemDanh::create([
                        'lan_diem_danh_id' => $lan_diem_danh->id,
                        'sinh_vien_id' => $sinh_vien->id,
                        'lop_id' => $lop->id,
                        'ma_lop' => $lop->ma,
                        'co_mat' => true
                    ]);
                }
            }
            DB::commit();
            return $this->responseSuccess($lan_diem_danh);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $lan_diem_danh = LanDiemDanh::findOrFail($id);
        $ngay_tao = Carbon::now();
        $request->validate([
            'lop_id' => ['required', 'integer'],
            'ngay_diem_danh' => ['nullable', 'string'],
            'ngay_mo_diem_danh' => ['nullable', 'string'],
            'ngay_dong_diem_danh' => ['nullable', 'string'],
        ], [
            'lop_id.required' => 'Mã lớp là bắt buộc',
            'lop_id.integer' => 'Giá trị của lớp phải là số nguyên',
        ]);
        $info = $request->except(['lan']);
        $lop_id = $request->get('lop_id');
        $lop = Lop::with(['sinhViens', 'children.sinhViens', 'giaoViens'])->findOrFail($lop_id);
        $ki_hoc_lop = $lop->ki_hoc;
        $ki_hoc_setting = Setting::where('setting_name', 'ki_hoc')->first();
        if ($user->role_code == RoleCode::TEACHER && $ki_hoc_lop != $ki_hoc_setting->setting_value) {
            abort(400, "Bạn không thể chỉnh sửa khi môn khác kì học $ki_hoc_setting->setting_value");
        }
        $lan = $lan_diem_danh->lan;
        [$result_mo, $result_dong, $tuan_mo, $tuan_dong] = $this->getKhoangNgayChoLanDiemDanh($lop, $lan);
        // if ($user->allow(RoleCode::ADMIN) || $user->allow(RoleCode::ASSISTANT)) {
        //     foreach ($lop->giaoViens as $giaoVien) {
        //         if ($giaoVien->id == $user->info_id) {
        //             if (isset($result_mo) && $result_mo > $ngay_tao) {
        //                 abort(400, "Không thể tạo điểm danh khi chưa đến thời gian tạo điểm danh $lan. Hãy liên hệ với trợ lý");
        //             }
        //             if (isset($result_dong) && $ngay_tao > $result_dong) {
        //                 abort(400, "Không thể tạo điểm danh khi đã quá hạn. Hãy liên hệ với trợ lý");
        //             }
        //             if ($request->has('ngay_diem_danh')) {
        //                 $info['ngay_diem_danh'] = new Carbon($request->get('ngay_diem_danh'));
        //             }
        //         }
        //     }
        // }
        if ($user->role_code == RoleCode::TEACHER) {
            if (isset($result_mo) && $result_mo > $ngay_tao) {
                abort(400, "Không thể tạo điểm danh khi chưa đến thời gian tạo điểm danh lần $lan. Tuần điểm danh đợt $lan là tuần $tuan_mo tới tuần $tuan_dong. Hãy liên hệ với trợ lý");
            }
            if (isset($result_dong) && $ngay_tao > $result_dong) {
                abort(400, "Không thể tạo điểm danh khi đã quá hạn lần $lan. Hạn điểm danh từ lần $lan tuần $tuan_mo đến $tuan_dong. Hãy liên hệ với trợ lý");
            }
        }
        try {
            DB::beginTransaction();
            if ($user->role_code == RoleCode::TEACHER) {
                $lan_diem_danh->update([
                    'ngay_diem_danh' => $request->ngay_diem_danh,
                ]);
            } else if ($user->allow(RoleCode::ADMIN) || $user->allow(RoleCode::ASSISTANT)) {
                $info = $request->all();
                if ($info['ngay_diem_danh']) {
                    $ngay_diem_danh = new DateTime($info['ngay_diem_danh']);
                    $info['ngay_diem_danh'] = $ngay_diem_danh->format('Y-m-d');
                }

                if ($info['ngay_mo_diem_danh']) {
                    $ngay_mo_diem_danh = new DateTime($info['ngay_mo_diem_danh']);
                    $info['ngay_mo_diem_danh'] = $ngay_mo_diem_danh->format('Y-m-d');
                }
                if ($info['ngay_dong_diem_danh']) {
                    $ngay_dong_diem_danh = new DateTime($info['ngay_dong_diem_danh']);
                    $info['ngay_dong_diem_danh'] = $ngay_dong_diem_danh->format('Y-m-d');
                }
                $lan_diem_danh->update([
                    'lan' => $request->lan,
                    'ngay_diem_danh' => $info['ngay_diem_danh'],
                    'ngay_mo_diem_danh' => $info['ngay_mo_diem_danh'],
                    'ngay_dong_diem_danh' => $info['ngay_dong_diem_danh']
                ]);
            }
            DB::commit();
            return $this->responseSuccess($lan_diem_danh);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }

    public function delete(Request $request, $id)
    {
        $user = $request->user();
        $lan_diem_danh = LanDiemDanh::findOrFail($id);
        $ngay_tao = Carbon::now();
        $ngay_bat_dau = Setting::where('setting_name', 'day_start_week_1')->first();
        $lop = Lop::findOrFail($request->input('lop_id'))->load(['sinhViens', 'children.sinhViens', 'giaoViens']);
        $ki_hoc_lop = $lop->ki_hoc;
        $lan = $lan_diem_danh->lan;
        // [$result_mo, $result_dong] = $this->getKhoangNgayChoLanDiemDanh($lop, $lan, $lan_diem_danh);
        // if ($user->allow(RoleCode::ADMIN) || $user->allow(RoleCode::ASSISTANT)) {
        //     foreach ($lop->giaoViens as $giaoVien) {
        //         if ($giaoVien->id == $user->info_id) {
        //             if (isset($result_mo) && $result_mo > $ngay_tao) {
        //                 abort(400, "Không thể tạo điểm danh khi chưa đến thời gian tạo điểm danh $lan. Hãy liên hệ với trợ lý");
        //             }
        //             if (isset($result_dong) && $ngay_tao > $result_dong) {
        //                 abort(400, "Không thể tạo điểm danh khi đã quá hạn. Hãy liên hệ với trợ lý");
        //             }
        //             if ($request->has('ngay_diem_danh')) {
        //                 $info['ngay_diem_danh'] = new Carbon($request->get('ngay_diem_danh'));
        //             }
        //         }
        //     }
        // }
        if ($user->role_code == RoleCode::TEACHER) {
            $ngay_tao = Carbon::now();
            [$result_mo, $result_dong, $tuan_mo, $tuan_dong] = $this->getKhoangNgayChoLanDiemDanh($lop, $lan);
            if (isset($result_mo) && $result_mo > $ngay_tao) {
                abort(400, "Không thể tạo điểm danh khi chưa đến thời gian tạo điểm danh lần $lan. Tuần điểm danh đợt $lan là tuần $tuan_mo tới tuần $tuan_dong. Hãy liên hệ với trợ lý");
            }
            if (isset($result_dong) && $ngay_tao > $result_dong) {
                abort(400, "Không thể tạo điểm danh khi đã quá hạn lần $lan. Hạn điểm danh từ lần $lan tuần $tuan_mo đến $tuan_dong. Hãy liên hệ với trợ lý");
            }
        }
        $ki_hoc_setting = Setting::where('setting_name', 'ki_hoc')->first();
        if (($user->role_code == RoleCode::TEACHER) && $ki_hoc_lop != $ki_hoc_setting->setting_value) {
            abort(400, "Bạn không thể xóa khi môn khác kì học $ki_hoc_setting->setting_value");
        }
        $lop_childrens = $lop->children;

        if (empty($lop_childrens->count())) {
            $lop_childrens = [$lop];
        };
        $lan_diem_danh_moi_nhat = $lop->lanDiemDanhMoiNhat;
        if (empty($lan_diem_danh_moi_nhat)) {
            abort(400, 'Không tìm thấy lần điểm danh mới nhất');
        }
        if ($lan_diem_danh_moi_nhat->id === intval($id)) {
            $lan_diem_danh_moi_nhat->delete();
            foreach ($lop_childrens as $lop_child) {
                $diem_chuyen_can = DiemChuyenCan::dispatch($lop_child);
            }
        } else {
            abort(400, 'Bạn chỉ có thể xóa lần điểm danh mới nhất!');
        }
        return $this->responseSuccess();
    }
    private function getKhoangNgayChoLanDiemDanh(Lop $lop, $dot, LanDiemDanh $lan_diem_danh = null)
    {
        $tuan_hoc = $lop->tuan_hoc;
        if (empty($tuan_hoc)) {
            return [];
        }
        if (!$lop->is_dai_cuong) {
            return [];
        }
        $ngay_bat_dau = Setting::where('setting_name', 'day_start_week_1')->first();
        if (empty($ngay_bat_dau)) {
            return [];
        }
        $lich = SettingHelper::getLichHoc($tuan_hoc);
        $ngay_bat_dau = $ngay_bat_dau->setting_value;
        $ngay_dong = null;
        $ngay_mo = null;
        $tuan_mo = null;
        $tuan_dong = null;
        if (isset($lan_diem_danh)) {
            $ngay_dong = $lan_diem_danh->ngay_dong_diem_danh;
            $ngay_mo = $lan_diem_danh->ngay_mo_diem_danh;
        }
        if (empty($ngay_dong) || empty($ngay_mo)) {
            if (Setting::where('setting_name', 'dong_diem_danh_lan_' . $dot)->where('ki_hoc', $lop->ki_hoc)->exists()) {
                $dong_lan = Setting::where('setting_name', 'dong_diem_danh_lan_' . $dot)->where('ki_hoc', $lop->ki_hoc)->first();
                $tuan_dong_mo = $dong_lan->setting_value;
                $convert_dong_mo = explode("-", preg_replace_callback('/(\d+)-(\d+),(\d+)/', function ($matches) {
                    return $matches[1] . '-' . ($matches[2] + $matches[3]);
                }, $tuan_dong_mo));
                if (empty($ngay_mo)) {
                    $ngay_mo =
                        !empty($lich[$convert_dong_mo[0] - 1]) ? SettingHelper::getT2TuanHocThuN($ngay_bat_dau, $lich[$convert_dong_mo[0] - 1]) : null;
                }
                if (empty($ngay_dong)) {
                    $ngay_dong =
                        !empty($lich[$convert_dong_mo[1] - 1]) ? SettingHelper::getT2TuanHocThuN($ngay_bat_dau, (int)$lich[$convert_dong_mo[1] - 1] + 1) : null;
                }
                if (empty($tuan_mo)) {
                    $tuan_mo = !empty($lich[$convert_dong_mo[0] - 1]) ?  $lich[$convert_dong_mo[0] - 1] : null;
                }
                if (empty($tuan_dong)) {
                    $tuan_dong = !empty($lich[$convert_dong_mo[1] - 1]) ? $lich[$convert_dong_mo[1] - 1] : null;
                }
            }
        }
        return [$ngay_mo, $ngay_dong, $tuan_mo, $tuan_dong];
    }
    // public function thongBaoDiemDanh(Request $request)
    // {
    //     dd($request->all());
    // }
}
