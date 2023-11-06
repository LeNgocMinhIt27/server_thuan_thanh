<?php

namespace App\Http\Controllers\Api\TinNhan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TinNhan\TinNhan;
use App\Library\QueryBuilder\QueryBuilder;
use App\Models\PhucKhao\PhucKhao;
use Exception;

class TinNhanController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = TinNhan::query();
        $query = QueryBuilder::for($query, $request)
            ->allowedAgGrid([])
            ->defaultSort('id')
            ->allowedSearch(['ngay_nhan', 'created_at', 'gia'])
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required',
            'receivedAt' => 'date_format:Y-m-d'
        ]);
        $key = 'fae62262-b741-4660-9a5b-e3eca4f827cd';
        $api_key = $request->apiKey;
        if ($api_key != $key) {
            return response('Không thể gửi tin nhắn', 401);
        }
        preg_match('/SAMI\d+/', $request->message, $matches);
        if ($matches == null | count($matches) == 0) {
            throw new Exception('Bạn cần phải gửi mã chuyển khoản');
        }
        $payment_code = preg_replace('/\D+/', '', $matches[0]);
        $ma_phuc_khao = PhucKhao::where('ma_thanh_toan', $payment_code)->firstOrFail();
        preg_match('/\+(\d+,\d+)VND/', $request->message, $transtionMatch);
        $transtion_amount  = $transtionMatch ? $transtionMatch[1] : '0';
        $phi = preg_replace('/,/', '', $transtion_amount);
        if ($phi >= 20000) {
            $ma_phuc_khao->update([
                'trang_thai' => "Thành công"
            ]);
        } else {
            $ma_phuc_khao->update([
                'trang_thai' => "Thiếu"
            ]);
        }
        $tin_nhan = TinNhan::create([
            'tin_nhan' => $request->message,
            'ngay_nhan' => $request->receivedAt,
            'trang_thai' => 'Thành công',
            'gia' => $phi,
            'ma_thanh_toan' => $payment_code,
        ]);
        return $this->responseSuccess($tin_nhan);
    }
}
