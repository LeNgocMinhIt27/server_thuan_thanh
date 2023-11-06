<?php

namespace App\Http\Controllers\Api\Lop;

use App\Helpers\DiemChuyenCanHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\DiemDanhRequest;
use App\Constants\RoleCode;
use App\Jobs\SendEmailDiemDanh;
use App\Library\QueryBuilder\QueryBuilder;
use App\Models\Lop\DiemDanh;
use App\Models\Lop\LanDiemDanh;
use App\Helpers\SettingHelper;
use App\Jobs\DiemChuyenCan;
use App\Models\Lop\Lop;
use App\Models\Lop\LopSinhVien;
use App\Models\User\SinhVien;
use App\Models\Setting;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Storage;

class DiemDanhController extends Controller
{
    public function index(Request $request)
    {
        $query = DiemDanh::query();
        $query = QueryBuilder::for($query, $request)
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->paginate()), 200, []);
    }
    public function show(Request $request, $id)
    {
        $query = DiemDanh::query();
        $query = QueryBuilder::for($query, $request);
        return response()->json([
            'data' => $query->findOrFail($id)
        ], 200, []);
    }
    public function indexForLanDiemDanh(Request $request, $id)
    {
        $query = DB::table('ph_diem_danhs')
            ->join('ph_lop_sinh_viens', function ($join) {
                $join->on('ph_diem_danhs.sinh_vien_id',  'ph_lop_sinh_viens.sinh_vien_id');
                $join->on('ph_diem_danhs.lop_id',  'ph_lop_sinh_viens.lop_id');
            })
            ->join('u_sinh_viens', 'ph_diem_danhs.sinh_vien_id', '=', 'u_sinh_viens.id');
        $query->orderBy('ph_diem_danhs.ma_lop');
        $query->orderBy('ph_diem_danhs.lop_id');
        $query->orderBy('ph_lop_sinh_viens.stt');
        $query->select([
            'ph_diem_danhs.lan_diem_danh_id',
            DB::raw('ph_diem_danhs.id as diem_danh_id'),
            'ph_lop_sinh_viens.sinh_vien_id',
            'ph_lop_sinh_viens.stt',
            'ph_lop_sinh_viens.nhom',
            'u_sinh_viens.name',
            'u_sinh_viens.mssv',
            'u_sinh_viens.group',
            'ph_diem_danhs.co_mat',
            'ph_diem_danhs.ghi_chu'
        ]);
        $query->where('ph_diem_danhs.lan_diem_danh_id', $id);
        if ($request->has('lop_id')) {
            $query->where('ph_diem_danhs.lop_id', $request->get('lop_id'));
        }
        return $query->get();
    }

    public function store(DiemDanhRequest $diemdanhrequest)
    {
        $info = $diemdanhrequest->all();
        $co_mat = $diemdanhrequest->get('co_mat');
        try {
            DB::beginTransaction();
            $diemdanh = DiemDanh::create($info);
            if (!$co_mat) {
                $message = [
                    'type' => 'Gửi mail điểm danh',
                    'content' => 'Thông báo về việc điểm danh lại của sinh viên',
                ];
                $landiemdanh = LanDiemDanh::where('id', $info['lan_diem_danh_id'])->first();
                $lopData = " "; // Default value
                $lopParent = " ";
                if (!empty($landiemdanh)) {
                    $lop = Lop::with("parent")->where('id', $landiemdanh->lop_id)->first();
                    if (!empty($lop)) {
                        $lopData = $lop->toArray();
                        $lopParent = $lop->parent;
                    }
                }

                $sinh_vien = SinhVien::find($info['sinh_vien_id'])->toArray();
                $users = auth()->user()->toArray();
                Log::debug(print_r([$diemdanh->toArray(), $lopdata], true));
                SendEmailDiemDanh::dispatch($diemdanh->toArray(), $sinh_vien, $lopData, $users, $message, $lopParent)->delay(now()->addSeconds(10));
            }
            DB::commit();
            return $this->responseSuccess($diemdanh);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
    public function update(Request $request, $id)
    {
        $request->validate(
            [
                'co_mat' => ['nullable', 'boolean'],
                'ghi_chu' => ['nullable', 'string'],
            ]
        );
        $diem_danh = DiemDanh::with('lanDiemDanh', 'sinhVien', 'lop')->findOrFail($id);
        $now = Carbon::now();
        if (!empty($diem_danh->lanDiemDanh->ngay_mo_diem_danh) && $now->lessThan($diem_danh->lanDiemDanh->ngay_mo_diem_danh)) {
            abort(400, 'Lần điểm danh chưa mở');
        }
        if (!empty($diem_danh->lanDiemDanh->ngay_dong_diem_danh) && $now->greaterThan($diem_danh->lanDiemDanh->ngay_dong_diem_danh)) {
            abort(400, 'Lần điểm danh đã hết hạn');
        }
        $info = [];
        if ($request->has('co_mat')) {
            $info['co_mat'] = $request->boolean('co_mat');
        }
        if ($request->has('ghi_chu')) {
            $info['ghi_chu'] = $request->get('ghi_chu');
        }
        $diem_danh->update($info);
        $diem_chuyen_can = DiemChuyenCanHelper::tinhDiemChuyenCan($diem_danh->sinh_vien_id, $diem_danh->lop_id);
        LopSinhVien::where('lop_id', $diem_danh->lop_id)
            ->where('sinh_vien_id', $diem_danh->sinh_vien_id)
            ->update(['diem' => $diem_chuyen_can]);

        if ($request->has('co_mat')) {
            if (!$diem_danh->co_mat) {
                $message = [
                    'type' => 'Gửi mail điểm danh',
                    'content' => 'Thông báo về việc điểm danh lại của sinh viên',
                ];
                $user = auth()->user();
                $giao_vien = $user->info;
                if ($giao_vien) {
                    $giao_vien = $giao_vien->toArray();
                }
                SendEmailDiemDanh::dispatch($diem_danh->toArray(), $diem_danh->sinhVien->toArray(), $diem_danh->lop->toArray(), $giao_vien ?? $user->toArray(), $message, $lopParent->toArray())->delay(now()->addSeconds(10));
            }
        }
        return $this->responseSuccess($diem_danh);
    }

    public function updateDiemdanh(Request $request)
    {
        $info_diem_danhs = $request->all();
        $user = $request->user();
        $ngay_tao = Carbon::now();
        $ngay_bat_dau = Setting::where('setting_name', 'day_start_week_1')->first();
        foreach ($info_diem_danhs as $info_diem_danh) {
            $lan_diem_danh = LanDiemDanh::where('id', $info_diem_danh)->first();
            $lop = Lop::with(['sinhViens', 'children.sinhViens', 'giaoViens'])->findOrFail($lan_diem_danh->lop_id);
            // if ($user->allow(RoleCode::ADMIN) || $user->allow(RoleCode::ASSISTANT)) {
            //     $lan = $lan_diem_danh->lan;
            //     [$result_mo, $result_dong] = $this->getKhoangNgayChoLanDiemDanh($lop, $lan, $lan_diem_danh);
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
                $ngay_dong = $lan_diem_danh->ngay_dong_diem_danh;
                $lan = $lan_diem_danh->lan;
                if (Setting::where('setting_name', 'dong_diem_danh_lan_' . $lan)->where('ki_hoc', $lop->ki_hoc)->exists()) {
                    if (!isset($ngay_dong)) {
                        $lich_hoc = $lop->tuan_hoc;
                        [$result_mo, $result_dong, $tuan_mo, $tuan_dong] = $this->getKhoangNgayChoLanDiemDanh($lop, $lan);
                        if (isset($result_mo) && $result_mo > $ngay_tao) {
                            abort(400, "Không thể tạo điểm danh khi chưa đến thời gian tạo điểm danh lần $lan. Tuần điểm danh đợt $lan là tuần $tuan_mo tới tuần $tuan_dong. Hãy liên hệ với trợ lý");
                        }
                        if (isset($result_dong) && $ngay_tao > $result_dong) {
                            abort(400, "Không thể tạo điểm danh khi đã quá hạn lần $lan. Hạn điểm danh từ lần $lan tuần $tuan_mo đến $tuan_dong. Hãy liên hệ với trợ lý");
                        }
                    }
                }
            }
            $diem_danh = DiemDanh::with('lanDiemDanh', 'lanDiemDanh.lop', 'sinhVien', 'lop')->findOrFail($info_diem_danh['diem_danh_id']);
            $lop = $diem_danh->lanDiemDanh->lop;
            $lanDiemDanh = $diem_danh->lanDiemDanh;
            $sinhVien = $diem_danh->sinhVien;
            $old_diem_danh = $diem_danh->co_mat;
            $now = Carbon::now();
            if (!empty($lanDiemDanh->ngay_mo_diem_danh) && $now->lessThan($lanDiemDanh->ngay_mo_diem_danh)) {
                abort(400, 'Lần điểm danh chưa mở');
            }
            if (!empty($lanDiemDanh->ngay_dong_diem_danh) && $now->greaterThan($lanDiemDanh->ngay_dong_diem_danh)) {
                abort(400, 'Lần điểm danh đã hết hạn');
            }
            $info = [];
            $info['co_mat'] = $info_diem_danh['co_mat'];
            $info['ghi_chu'] = $info_diem_danh['ghi_chu'];
            $diem_danh->update($info);
            DiemChuyenCan::dispatch($lop);
            if (!$diem_danh->co_mat && $old_diem_danh != $info_diem_danh['co_mat']) {
                $user = $request->user();
                $info_email =
                    "USER_ID: " .  $user->getKey() . "MSSV: " .  $sinhVien->mssv . "- LOP:" . $lop->ma . " - LOAI:" . $lop->loai . "- DATE: " . Carbon::now()->format('Y-m-d-hh-mm-ss') . "- Status: " . ($info_diem_danh['co_mat'] ? 'True' : "False");
                Log::channel('email')->debug($info_email);
                $message = [
                    'type' => 'Gửi mail điểm danh',
                    'content' => 'Thông báo về việc điểm danh lại của sinh viên',
                ];
                $giao_vien = $user->info;
                if ($giao_vien) {
                    $giao_vien = $giao_vien->toArray();
                }
                SendEmailDiemDanh::dispatch($lanDiemDanh->toArray(), $sinhVien->toArray(), $lop->toArray(), $giao_vien ?? $user->toArray(), $message)->delay(now()->addSeconds(10));
            }
        };
        return $this->responseSuccess();
    }


    public function delete(Request $request, $id)
    {
        $diemdanh = DiemDanh::find($id)->delete($id);
        return $this->responseSuccess($diemdanh);
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
}
