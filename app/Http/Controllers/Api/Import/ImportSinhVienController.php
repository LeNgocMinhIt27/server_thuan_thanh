<?php

namespace App\Http\Controllers\Api\Import;

use App\Http\Controllers\Controller;
use App\Models\Lop\Lop;
use App\Models\User\SinhVien;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Spatie\ResponseCache\Facades\ResponseCache;


class ImportSinhVienController extends Controller
{
    public function import(Request $request)
    {
        // chỉ sinh user ở giáo viên lần đầu tiên import, và trả về mật khẩu cho những giáo viên đó
        $request->validate([
            'items' => ['required', 'array'],
            'fields' => ['required'],
            'fields.ma_lop' => ['required', 'string'],
            'fields.ma_hp' => ['required', 'string'],
            'fields.ten_hp' => ['required', 'string'],
            'fields.sinh_vien_id' => ['required', 'string'],
            'fields.sinh_vien_name' => ['nullable', 'string'],
            'fields.sinh_vien_birthday' => ['nullable', 'string'],
            'fields.sinh_vien_lop' => ['nullable', 'string'],
            'fields.sinh_vien_nhom' => ['required', 'string']
        ]);
        $items = $request->get('items');
        $fields = $request->get('fields');
        $ki_hoc = $request->get('ki_hoc');
        try {
            DB::beginTransaction();
            $items_return = [];
            $stt_current_ma = '';
            $stt = 1;
            $old_class = '';
            $lop_cache = [];
            $lops = Lop::where('ki_hoc', $ki_hoc)->get(['id', 'ma']);
            $sinh_vien_cache = [];
            $sinh_viens = SinhVien::get(['id', 'mssv'])->mapWithKeys(function ($item, $key) {
                return [$item['mssv'] => ['id' => $item['id'], 'group' => $item['group']]];
            });
            $data_insert = [];
            foreach ($items as $item) {
                $res = ImportHelper::convertTime($fields, $item);

                if (empty($lop_cache[$res['ma_lop']])) {
                    $temp = $lops->where('ma', $res['ma_lop'])->first();
                    if (empty($temp)) {
                        $temp = Lop::create([
                            'ma' => $res['ma_lop'],
                            'ma_hp' => $res['ma_hp'],
                            'ten_hp' => $res['ten_hp'],
                            'ki_hoc' => $ki_hoc
                        ]);
                    }
                    if (isset($temp))
                        $lop_cache[$res['ma_lop']] = $temp->getKey();
                }
                $lop_id = $lop_cache[$res['ma_lop']];

                if (empty($sinh_vien_cache[$res['sinh_vien_id']])) {
                    $temp = $sinh_viens[$res['sinh_vien_id']] ?? null;
                    $birthday = !empty($res['sinh_vien_birthday']) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($res['sinh_vien_birthday'] ?? '') : '';
                    $date = $birthday instanceof DateTime ? $birthday->format('Y-m-d') : $birthday;
                    if (empty($temp)) {
                        $temp = SinhVien::updateOrCreate([
                            'mssv' => $res['sinh_vien_id'],
                        ], [
                            'name' => $res['sinh_vien_name'] ?? '',
                            'birthday' => $date,
                            'group' => $res['sinh_vien_lop'] ?? ''
                        ]);
                        $temp = $temp->getKey();
                    } else if (empty($temp['group']) || $temp['group'] != $res['sinh_vien_lop']) {
                        $temp = SinhVien::updateOrCreate([
                            'mssv' => $res['sinh_vien_id'],
                        ], [
                            'name' => $res['sinh_vien_name'] ?? '',
                            'birthday' => $date,
                            'group' => $res['sinh_vien_lop'] ?? ''
                        ]);
                        $temp = $temp->getKey();
                    }

                    if (isset($temp))
                        $sinh_vien_cache[$res['sinh_vien_id']] = $temp;
                }

                if (empty($stt_current_ma) || $stt_current_ma != $res['ma_lop']) {
                    $stt = 1;
                    $stt_current_ma = $res['ma_lop'];
                    DB::table('ph_lop_sinh_viens')->where('lop_id', $lop_id)->delete();
                    DB::table('ph_lop_sinh_viens')->insert($data_insert);
                    $data_insert = [];
                }


                $sinh_vien_id = $sinh_vien_cache[$res['sinh_vien_id']];
                $data_insert[] = [
                    'lop_id' => $lop_id,
                    'sinh_vien_id' => $sinh_vien_id,
                    'stt' => $stt,
                    'nhom' => $res['sinh_vien_nhom']
                ];
                $old_class = $lop_id;
                $stt++;
            }

            if (count($data_insert) > 0) {
                DB::table('ph_lop_sinh_viens')->where('lop_id', $lop_id)->delete();
                DB::table('ph_lop_sinh_viens')->insert($data_insert);
                $data_insert = [];
            }
            DB::commit();
            ResponseCache::clear();
            return $this->responseSuccess($items_return);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
