<?php

namespace App\Http\Controllers\Api\Import;

use App\Constants\RoleCode;
use App\Http\Controllers\Controller;
use App\Models\User\GiaoVien;
use DB;
use Hash;
use Illuminate\Http\Request;
use Str;
use Spatie\ResponseCache\Facades\ResponseCache;


class ImportGiaoVienController extends Controller
{
    public function import(Request $request)
    {
        // chỉ sinh user ở giáo viên lần đầu tiên import, và trả về mật khẩu cho những giáo viên đó
        $request->validate([
            'items' => ['required', 'array'],
            'fields' => ['required'],
            'fields.name' => ['required', 'string'],
            'fields.email' => ['required', 'string'],
        ]);
        $items = $request->get('items');
        $fields = $request->get('fields');
        try {
            DB::beginTransaction();
            $giao_viens = GiaoVien::with(['user'])->get();
            $items_return = [];
            foreach ($items as $item) {
                $res = ImportHelper::convertTime($fields, $item);
                $giao_vien = $giao_viens->where('email', $res['email'])->first();
                if (empty($giao_vien)) {
                    $giao_vien = GiaoVien::create($res);
                }
                if (empty($giao_vien->user)) {
                    $res['password'] = Str::random(8);
                    $giao_vien->user()->create(['username' => $res['email'], 'role_code' => RoleCode::TEACHER, 'password' => Hash::make($res['password'])]);
                }
                $items_return[] = $res;
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
