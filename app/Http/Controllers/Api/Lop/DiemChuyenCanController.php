<?php

namespace App\Http\Controllers\Api\Lop;

use App\Helpers\DiemChuyenCanHelper;
use App\Http\Controllers\Controller;
use App\Library\QueryBuilder\QueryBuilder;
use App\Models\Lop\Lop;
use Illuminate\Http\Request;

class DiemChuyenCanController extends Controller
{
    protected $includes = ['diemDanhs', 'lop', 'diemDanhs.sinhVien', 'lop.children'];
    public function index(Request $request, $id)
    {
        $query = Lop::query()->with('sinhViens');
        $lop = $query->findOrFail($id);
        foreach ($lop->sinhViens as $sinh_vien) {
            $diem_chuyen_can = DiemChuyenCanHelper::tinhDiemChuyenCan($sinh_vien->id, $lop->id);
            $sinh_vien->pivot->diem = $diem_chuyen_can;
            $lop->sinhViens()->syncWithoutDetaching([
                $sinh_vien->getKey() => ['diem' => $diem_chuyen_can],
            ]);
        }
        return response()->json($lop->sinhViens, 200, []);
    }
}
