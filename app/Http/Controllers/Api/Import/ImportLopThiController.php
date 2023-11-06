<?php

namespace App\Http\Controllers\Api\Import;

use App\Http\Controllers\Controller;
use App\Models\Lop\Lop;
use App\Models\Lop\LopThi;
use App\Models\User\SinhVien;
use Carbon\Carbon;
use DB;
use DateTime;
use Illuminate\Http\Request;
use Spatie\ResponseCache\Facades\ResponseCache;

class ImportLopThiController extends Controller
{
    public function importLopThi(Request $request)
    {
        $request->validate([
            'items' => ['required', 'array'],
            'fields' => ['required'],
            'fields.ma_lop' => ['required', 'string'],
            'fields.nhom' => ['required', 'string'],
            'fields.ngay_thi' => ['required', 'string'],
            'fields.kip_thi' => ['required', 'string'],
            'fields.phong_thi' => ['required', 'string'],

        ]);
        $items = $request->get('items');
        $fields = $request->get('fields');
        $ki_hoc = $request->get('ki_hoc');
        $loai = $request->get('loai');
        $lop_thi = LopThi::where('loai', '=', $loai)->get(['id', 'ma'])->mapWithKeys(function ($item, $key) {
            return [$item['ma'] => $item['id']];
        });
        try {
            DB::beginTransaction();
            $lop_thi_cache = [];
            $lops = Lop::where('ki_hoc', $ki_hoc)->get(['id', 'ma']);
            foreach ($items as $item) {
                $res = ImportHelper::convertTime($fields, $item);
                if ((empty($res['ma_lop_thi']) && $loai === 'CK') || $loai !== 'CK') {
                    $ma_lop_thi = $res['ma_lop'] . '-' . $res['nhom'];
                } else {
                    $ma_lop_thi = $res['ma_lop_thi'];
                }

                if (!empty($lop_thi_cache[$ma_lop_thi])) {
                    continue;
                }
                $temp = $lop_thi[$ma_lop_thi] ?? null;
                $ma_lop_hoc = $res['ma_lop'];
                $lop = $lops->where('ma', $res['ma_lop'])->first();
                if (!$lop) {
                    return response()->json(['message' => "Lớp $ma_lop_hoc không tồn tại trong dữ liệu"], 404);
                }
                $ngay_thi = !empty($res['ngay_thi']) ? Carbon::createFromFormat('d/m/Y', $res['ngay_thi']) : null;
                if (!empty($ngay_thi))

                    $temp = LopThi::updateOrCreate([
                        'loai' => $loai,
                        "ma" => $ma_lop_thi,
                        'lop_id' => $lop['id'],
                    ], [
                        'ngay_thi' => $ngay_thi ?? null,
                        'phong_thi' => $res['phong_thi'],
                        'kip_thi' => $res['kip_thi'],
                    ]);
                $temp = $temp->getKey();
                if (isset($temp)) {
                    $lop_thi_cache[$ma_lop_thi] = $temp;
                }
            }

            DB::commit();
            ResponseCache::clear();
            return $this->responseSuccess();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
