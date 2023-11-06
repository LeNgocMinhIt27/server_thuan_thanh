<?php

namespace App\Helpers;

use App\Models\Setting;
use Cache;
use Illuminate\Support\Carbon;
use App\Models\Lop\Lop;
use App\Models\Lop\LanDiemDanh;

class SettingHelper
{
    public static function getAll()
    {
        return Cache::rememberForever('settings.all', function () {
            return Setting::all();
        });
    }
    public static function convertListToObject($items)
    {
        $data = [];
        $items->each(function ($item) use (&$data) {
            if (empty($data[$item['section_name']]))
                $data[$item['section_name']] = [];
            $data[$item['section_name']][$item['setting_name']] = $item->setting_value;
        });
        return $data;
    }
    public static function deleteAllFromCache()
    {
        return Cache::forget('settings.all');
    }
    public static function getConfig($key)
    {
        $keys = explode(".", $key);
        $settings = SettingHelper::getAll();
        $query = $settings->where('section_name', $keys[0]);
        if (isset($keys[1])) {
            return $query->where('setting_name', $keys[1])->first();
        }
        return $query->all();
    }
    public static function getLichHoc($input)
    {
        $tuans = explode(',', $input);
        $output = [];
        foreach ($tuans as $tuan) {
            if (strpos($tuan, '-') !== false) {
                list($start, $end) = explode('-', $tuan);
                $start = intval($start);
                $end = intval($end);
                for ($i = $start; $i <= $end; $i++) {
                    $output[] = $i;
                }
            } else {
                $output[] = intval($tuan);
            }
        }
        return $output;
    }
    public static function getDateFrame($ngay_bat_dau, $lich_hoc)
    {
        $list_dong_mo = [];
        $list_lich_hoc = SettingHelper::getLichHoc($lich_hoc);
        foreach ($list_lich_hoc as $key => $lich) {
            $ngay_bat_dau = Carbon::createFromFormat('d/m/Y', Carbon::parse($ngay_bat_dau)->format('d/m/Y'));
            $khoang_cach_ngay = ($lich - 1) * 7;
            $bat_dau = $ngay_bat_dau->copy();
            $ngay_dau_tuan = $bat_dau->addDays($khoang_cach_ngay);
            $current_day = $ngay_dau_tuan->copy();
            $ngay_cuoi_tuan = $current_day->addDays(7);
            $cuoi_tuan = $ngay_cuoi_tuan->format('d/m/Y');
            $dau_tuan = $ngay_dau_tuan->format('d/m/Y');
            $result = ['dau_tuan' => $dau_tuan, 'cuoi_tuan' => $cuoi_tuan, 'lich' => $list_lich_hoc[$key] ?? null];
            $list_dong_mo[] = $result;
        }
        return $list_dong_mo;
    }
    public static function getT2TuanHocThuN($ngay_bat_dau, $week): string
    {
        $t2_tuan = new Carbon($ngay_bat_dau);
        $t2_tuan->addWeeks($week - 1);
        return $t2_tuan->format('Y-m-d');
    }
    public static function getKhoangNgayChoLanDiemDanh(Lop $lop, $dot, LanDiemDanh $lan_diem_danh = null)
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
}
