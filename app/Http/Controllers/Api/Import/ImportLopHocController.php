<?php

namespace App\Http\Controllers\Api\Import;

use App\Http\Controllers\Controller;
use App\Models\Lop\Lop;
use App\Models\User\GiaoVien;
use Arr;
use DB;
use Illuminate\Http\Request;
use Spatie\ResponseCache\Facades\ResponseCache;


class ImportLopHocController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'items' => ['required', 'array'],
            'fields' => ['required'],
            'ki_hoc' => ['required', 'string'],
            'fields.ma' => ['required', 'string'],
            'fields.giao_vien_email' => ['required', 'string'],
            'fields.lop_thu' => ['nullable', 'string'],
            'fields.lop_thoigian' => ['nullable', 'string'],
            'fields.lop_phong' => ['nullable', 'string'],
            'fields.lop_kip' => ['nullable', 'string'],
            'fields.tuan_hoc' => ['nullable', 'string'],

        ]);
        $items = $request->get('items');
        $fields = $request->get('fields');
        $ki_hoc = $request->get('ki_hoc');
        $is_dai_cuong = $request->get('is_dai_cuong');

        try {
            DB::beginTransaction();
            DB::commit();
            if (isset($fields['giao_vien_email'])) {
                $key_giao_vien = $fields['giao_vien_email'];
            }
            if (isset($fields['lop_thu']) && isset($fields['lop_thoigian']) && isset($fields['lop_phong'])) {
                $lop_hoc = [
                    'lop_thu' => $fields['lop_thu'],
                    'lop_thoigian' => $fields['lop_thoigian'],
                    'lop_phong' => $fields['lop_phong'],
                    'lop_kip' => $fields['lop_kip'] ?? null,
                ];
            }
            $phongs = [];
            foreach ($items as $item) {
                $res = ImportHelper::convertTime($fields, $item);
                $res['ki_hoc'] = $ki_hoc;
                $lop = Lop::updateOrCreate(['ma' => $res['ma'], 'ki_hoc' => $ki_hoc], array_merge($res, ['is_dai_cuong' => $is_dai_cuong]));
                if (isset($key_giao_vien)) {
                    $giao_vien_emails = explode(",", $item[$key_giao_vien]);
                    $giao_vien_emails = array_reduce($giao_vien_emails, function ($acc, $cur) {
                        $cur = trim($cur);
                        if (!empty($cur)) {
                            $acc[] = $cur;
                        }
                        return $acc;
                    }, []);
                    $giao_viens_ids = GiaoVien::whereIn('email', $giao_vien_emails)->get('id')->pluck('id')->all();
                }
                if (isset($giao_viens_ids)) {
                    $lop->giaoViens()->syncWithoutDetaching($giao_viens_ids);
                }
                if (isset($lop_hoc)) {
                    if (empty($phongs[$res['ma']]))
                        $phongs[$res['ma']] = [];
                    $phongs[$res['ma']][] = $this->handlePhong($item, $lop_hoc);
                }
            }
            foreach ($phongs as $key => $value) {
                Lop::where('ma', $key)->where('ki_hoc', $ki_hoc)->first()->update(['phong' => implode(";", $value)]);
            }
            ResponseCache::clear();
            return $this->responseSuccess();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
    private function handlePhong($item, $key)
    {
        $lop_thu = Arr::get($item, $key['lop_thu'], '');
        $lop_thoigian = Arr::get($item, $key['lop_thoigian'], '');
        // $lop_kip = Arr::get($item, $key['lop_kip'], '');
        $lop_phong = Arr::get($item, $key['lop_phong'], '');
        $result = "$lop_phong:$lop_thoigian:$lop_thu";
        return trim($result);
    }
}
