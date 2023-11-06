<?php

namespace App\Helpers;

use App\Models\Lop\DiemDanh;

class DiemChuyenCanHelper
{
    public static function tinhDiemChuyenCan($sinh_vien_id, $lop_id)
    {
        $query = DiemDanh::query()
            ->where('sinh_vien_id', $sinh_vien_id)
            ->where('lop_id', $lop_id);
        $tong_so_buoi = $query->count();
        if ($tong_so_buoi == 0) {
            return 10;
        }
        $vang_mat = $query->where('co_mat', false)->count();
        $diem_tru =  ($vang_mat / 8) * 10;

        $diem_chuyen_can = 10 - $diem_tru;

        return $diem_chuyen_can;
    }
}
