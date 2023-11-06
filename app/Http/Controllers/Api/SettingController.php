<?php

namespace App\Http\Controllers\Api;

use App\Helpers\HustHelper;
use App\Helpers\SettingHelper;
use App\Http\Controllers\Controller;
use App\Models\KiHoc;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\Lop\LanDiemDanh;
use App\Models\Lop\Lop;
use Carbon\Carbon;

class SettingController extends Controller
{
    public function index()
    {
        $data = [];
        $items = SettingHelper::getAll();
        $data = SettingHelper::convertListToObject($items);
        return response()->json($data);
    }
    public function updateHust(Request $request)
    {
        $info = $request->all();
        if (isset($info['day_start_week_1'])) {
            $ngay_bat_dau = Carbon::parse($info['day_start_week_1'])->toDateString();
            $config = SettingHelper::getConfig('config.day_start_week_1');
            $config->update(['setting_value' => $ngay_bat_dau]);
        }
        if (isset($info['so_lan_diem_danh_toi_da'])) {
            $config = SettingHelper::getConfig('config.so_lan_diem_danh_toi_da');
            $config->update(['setting_value' => $info['so_lan_diem_danh_toi_da']]);
        }
        if (isset($info['ki_hoc'])) {
            $config = Setting::where('setting_name', 'ki_hoc')->first();
            $config->update(['setting_value' => $info['ki_hoc']]);
        }
        if (isset($info['tuan_diem_danh'])) {
            $key_in = [];
            foreach ($info['tuan_diem_danh'] as $key => $value) {
                $key_in[] = $key;
                $setting = Setting::where('section_name', 'tuan_diem_danh')->where('setting_name', $key)->first();
                if (isset($setting)) {
                    $setting->update(['setting_value' => $value]);
                } else {
                    Setting::create([
                        'section_name' => 'tuan_diem_danh',
                        'setting_name' => $key,
                        'setting_value' => json_encode($value),
                        'setting_type' => 'json',
                    ]);
                }
            }
            Setting::where('section_name', 'tuan_diem_danh')->whereNotIn('setting_name', $key_in)->delete();
        }
        SettingHelper::deleteAllFromCache();
        return $this->responseSuccess();
    }

    public function listDongDiemDanh(Request $request)
    {
        $ki_hoc = $request->input('ki_hoc');
        $query = Setting::where('setting_name', 'LIKE', '%dong_diem_danh_lan_%')->where('ki_hoc', $ki_hoc)->orderBy('id')->get();
        $ngay_bat_dau = Setting::where('setting_name', 'day_start_week_1')->first();

        $data_collect = $query->map(function ($data) use ($ngay_bat_dau) {
            $convert_dong_mo = preg_replace_callback('/(\d+)-(\d+),(\d+)/', function ($matches) {
                return $matches[1] . '-' . ($matches[2] + $matches[3]);
            }, $data['setting_value']);

            $list_tuan = SettingHelper::getDateFrame($ngay_bat_dau->setting_value, $convert_dong_mo);
            $tuan_dau = reset($list_tuan);
            $result_mo = Carbon::createFromFormat('d/m/Y', $tuan_dau['dau_tuan'])->format('Y-m-d');
            $tuan_cuoi = end($list_tuan);
            $result_dong = Carbon::createFromFormat('d/m/Y', $tuan_cuoi['cuoi_tuan'])->format('Y-m-d');

            $data['ngay_dong'] = $result_dong;
            $data['ngay_mo'] = $result_mo;

            return $data;
        });
        return response()->json(new \App\Http\Resources\Items($query), 200, []);
    }

    public function ngayDongDiemDanh(Request $request)
    {
        $request->validate([
            'mo_diem_danh' => 'required|numeric|min:1',
            'dong_diem_danh' => 'required|numeric|gt:mo_diem_danh|min:1',
        ]);
        $dong = $request->input('dong_diem_danh');
        $mo = $request->input('mo_diem_danh');
        $ki_hoc = $request->input('ki_hoc');
        $dong_tre = $request->input('dong_tre');
        if (!isset($dong_tre)) {
            $dong_tre = 0;
        }
        $khoang_dong_diem_danh = $mo . '-' . $dong . ',' . $dong_tre;
        if ($dong_tre != 0) {
            $dong = $request->input('dong_diem_danh') + $dong_tre;
        }
        $lan_toi_da = Setting::select('setting_value')->where('setting_name', 'so_lan_diem_danh_toi_da')->firstOrFail();
        $list = Setting::where('setting_name', 'LIKE', '%dong_diem_danh_lan_%')->where('ki_hoc', $ki_hoc)->count();
        $lan_tiep_theo = $list + 1;
        $name = 'dong_diem_danh_lan_' . "$lan_tiep_theo";
        if (intval($lan_toi_da->setting_value) == $list) {
            abort(400, "Đã quá số lần đóng điểm danh");
        }
        Setting::create([
            'section_name' => 'config',
            'setting_name' => $name,
            'setting_value' => $khoang_dong_diem_danh,
            'setting_type' => 'string',
            'ki_hoc' => $ki_hoc
        ]);
        return $this->responseSuccess();
    }

    public function updateDongDiemDanh(Request $request, $id)
    {
        $request->validate([
            'mo_diem_danh' => 'required|numeric|min:1',
            'dong_diem_danh' => 'required|numeric|gt:mo_diem_danh|min:1',
        ]);
        $dong = $request->input('dong_diem_danh');
        $mo = $request->input('mo_diem_danh');
        $dong_tre = $request->input('dong_tre');
        $setting = Setting::findOrFail($id);
        preg_match('/\d+/', $setting->setting_name, $matches);
        if (!isset($dong_tre)) {
            $dong_tre = 0;
        }
        $khoang_dong_diem_danh = $mo . '-' . $dong . ','  . $dong_tre;
        if ($dong_tre != 0) {
            $dong = $request->input('dong_diem_danh') + $dong_tre;
        }
        $setting->update([
            'setting_value' => $khoang_dong_diem_danh,
        ]);
        return $this->responseSuccess();
    }

    public function destroyDongDiemDanh($id)
    {
        $dong_diem_danh = Setting::findOrFail($id);
        $dong_diem_danh->delete();

        return $this->responseSuccess();
    }
    public function timKiemLichHoc(Request $request)
    {
        $request->validate([
            'search' => 'required'
        ]);
        $query = $request->input('search');
        $ngay_bat_dau = Setting::where('setting_name', 'day_start_week_1')->first();
        $ki_hien_tai = Setting::where('setting_name', 'ki_hoc')->first();
        $lop = Lop::where('ma', $query)->first();
        if (!empty($lop)) {
            $tuan = $lop->tuan_hoc;
        }
        if (empty($lop)) {
            $tuan = $query;
        }
        if (empty($ngay_bat_dau)) {
            return response()->json([], 200, []);
        }
        $ngay_bat_dau = $ngay_bat_dau->setting_value;
        $lich = SettingHelper::getLichHoc($tuan);
        $results = [];
        $so_lan_diem_danh_toi_da = SettingHelper::getConfig('config.so_lan_diem_danh_toi_da')->setting_value ?? 4;
        for ($i = 0; $i < $so_lan_diem_danh_toi_da; $i++) {
            $dong_lan = Setting::where('setting_name', 'dong_diem_danh_lan_' . ($i + 1))->where('ki_hoc', $ki_hien_tai->setting_value)->first();
            if (empty($dong_lan)) {
                break;
            }
            $tuan_dong_mo = $dong_lan->setting_value;
            $convert_dong_mo = explode("-", preg_replace_callback('/(\d+)-(\d+),(\d+)/', function ($matches) {
                return $matches[1] . '-' . ($matches[2] + $matches[3]);
            }, $tuan_dong_mo));
            $results[] = [
                'lan' => $i + 1,
                'tuan_hoc_mo' => $convert_dong_mo[0],
                'tuan_hoc_dong' => $convert_dong_mo[1],
                'tuan_ki_mo' => !empty($lich[$convert_dong_mo[0] - 1]) ?  $lich[$convert_dong_mo[0] - 1] : null,
                'tuan_ki_dong' => !empty($lich[$convert_dong_mo[1] - 1]) ? $lich[$convert_dong_mo[1] - 1] : null,
                'ngay_mo' => !empty($lich[$convert_dong_mo[0] - 1]) ? SettingHelper::getT2TuanHocThuN($ngay_bat_dau, $lich[$convert_dong_mo[0] - 1]) : null,
                'ngay_dong' => !empty($lich[$convert_dong_mo[1] - 1]) ? SettingHelper::getT2TuanHocThuN($ngay_bat_dau, (int)$lich[$convert_dong_mo[1] - 1] + 1) : null //  them 1 tuan, dong vao cuoi cn cua tuan day, day sang t2 tuan sau,
            ];
        }
        return response()->json($results, 200, []);
    }
}
