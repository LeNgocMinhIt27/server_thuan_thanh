<?php

namespace App\Http\Controllers\Api\Export;

use App\Exports\DiemDanhExport;
use App\Exports\LopCoiThiGiaoVienExport;
use App\Exports\MultiSheetExportDiemDanh;
use App\Exports\MultiSheetExportSinhVien;
use App\Exports\SinhVienExport;
use App\Exports\PhucKhaoExport;
use App\Http\Controllers\Controller;
use App\Models\Lop\Lop;
use App\Models\PhucKhao\PhucKhao;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Excel;
use Storage;
use App\Helpers\ZipHelper;
use App\Helpers\System\DownloadFileHelper;
use DB;
use File;

class ExportExcelController extends Controller
{
    public function exportDiemDanh(Request $request, $id)
    {
        $query = Lop::query();
        $query->with('sinhViens');
        $result = $query->findOrFail($id);
        $sub_data = $request->all();
        $sinhViens = $result['sinhViens']->toArray();
        $filename = Carbon::now()->format('Ymdhms') . 'diem_danh.xlsx';
        Excel::store(new DiemDanhExport($sinhViens, $sub_data, $sub_data['ma']), $filename);
        $fullPath = Storage::disk('local')->path($filename);
        return response()->download($fullPath)->deleteFileAfterSend(true);
    }

    public function exportSinhVien(Request $request, $id)
    {
        $query = Lop::query();
        $query->with('sinhViens');
        $result = $query->findOrFail($id);
        $sub_data = $request->all();
        $sinhViens = $result['sinhViens']->toArray();
        $filename = Carbon::now()->format('Ymdhms') . 'sinh_vien.xlsx';
        Excel::store(new SinhVienExport($sinhViens, $sub_data, $sub_data['ma']), $filename);
        $fullPath = Storage::disk('local')->path($filename);
        return response()->download($fullPath)->deleteFileAfterSend(true);
    }

    public function exportSinhVienAll(Request $request, $id)
    {
        $sub_data = $request->all();
        $children = $sub_data['children'];
        $new_arr = collect();
        $array_name = collect();
        foreach ($children as $key => $child) {
            $classId = $child['id'];
            $result = Lop::with('sinhViens')->findOrFail($classId);

            $sinhViens = $result['sinhViens']->toArray();
            $new_arr->push($sinhViens);
            $array_name->push($child['ma']);
        }
        $filename = Carbon::now()->format('Ymdhms') . 'danh_sach_sinh_vien.xlsx';
        Excel::store(new MultiSheetExportSinhVien($new_arr, $sub_data, $array_name), $filename);
        $fullPath = Storage::disk('local')->path($filename);
        return response()->download($fullPath)->deleteFileAfterSend(true);
    }

    public function exportDiemDanhAll(Request $request, $id)
    {
        $sub_data = $request->all();
        $children = $sub_data['children'];
        $new_arr = collect();
        $array_name = collect();
        foreach ($children as $key => $child) {
            $classId = $child['id'];
            $result = Lop::with('sinhViens')->findOrFail($classId);

            $sinhViens = $result['sinhViens']->toArray();
            $new_arr->push($sinhViens);
            $array_name->push($child['ma']);
        }
        $filename = Carbon::now()->format('Ymdhms') . 'Danh-sach-diem-danh.xlsx';
        Excel::store(new MultiSheetExportDiemDanh($new_arr, $sub_data, $array_name), $filename);
        $fullPath = Storage::disk('local')->path($filename);
        return response()->download($fullPath)->deleteFileAfterSend(true);
    }

    public function exportPhucKhao(Request $request)
    {
        $sub_data = $request->all();
        $phuc_khaos = DB::table('pk_phuc_khaos')
            ->join('ph_lops', 'ph_lops.id', '=', 'pk_phuc_khaos.lop_id')
            ->join('ph_lop_this', 'ph_lop_this.id', '=', 'pk_phuc_khaos.lop_thi_id')
            ->join('u_sinh_viens', 'u_sinh_viens.id', '=', 'pk_phuc_khaos.sinh_vien_id')
            ->leftJoin('ph_diems', function ($join) {
                $join->on('ph_diems.sinh_vien_id', '=', 'pk_phuc_khaos.sinh_vien_id')
                    ->on('ph_diems.lop_thi_id', '=', 'ph_lop_this.id');
            });
        $phuc_khaos->select(
            DB::raw('ph_lops.ma as ma_lop_hoc'),
            DB::raw('ph_lop_this.ma as ma_lop_thi'),
            'pk_phuc_khaos.ki_hoc',
            'pk_phuc_khaos.trang_thai',
            'pk_phuc_khaos.ma_thanh_toan',
            'u_sinh_viens.name',
            'u_sinh_viens.mssv',
            'ph_diems.diem'
        );
        if ($request['ki_hoc']) {
            $phuc_khaos->where('pk_phuc_khaos.ki_hoc', $request['ki_hoc']);
        }
        $filename = Carbon::now()->format('Ymdhms') . 'phuc_khao.xlsx';
        Excel::store(new PhucKhaoExport($phuc_khaos->get()->toArray(), $sub_data, 'phuc_khao'), $filename);
        $fullPath = Storage::disk('local')->path($filename);
        return response()->download($fullPath)->deleteFileAfterSend(true);
    }

    public function exportLopCoiThiGV(Request $request)
    {
        $query = DB::query()->fromSub(
            function ($query) use ($request) {
                $query->from('ph_lop_this')
                    ->join('ph_lops', 'ph_lops.id', '=', 'ph_lop_this.lop_id')
                    // ->leftJoin('ph_lop_sinh_viens', 'ph_lops.id', '=', 'ph_lop_sinh_viens.lop_id')
                    ->leftJoin(DB::raw('(SELECT COUNT(sinh_vien_id) as sl_sinh_vien, lop_thi_id FROM ph_lop_thi_sinh_viens GROUP BY lop_thi_id) as c'), 'ph_lop_this.id', '=', 'c.lop_thi_id')
                    ->orderBy('ph_lop_this.phong_thi', 'asc')
                    ->orderBy('ph_lop_this.ma', 'asc');
                $query->select([
                    'ph_lops.ki_hoc',
                    'ph_lops.ma_hp',
                    'ph_lops.ten_hp',
                    'ph_lops.ghi_chu',
                    'ph_lops.id as lop_id',
                    'ph_lop_this.id as lop_thi_id',
                    'ph_lop_this.loai',
                    'ph_lop_this.ma',
                    'ph_lop_this.ngay_thi',
                    'ph_lop_this.phong_thi',
                    'ph_lop_this.kip_thi',
                    // 'ph_lop_sinh_viens.nhom',
                    'c.sl_sinh_vien',
                    DB::raw('ph_lops.ma as ma_lop_hoc')
                ]);
                if (!empty($request['loai'])) {
                    $query->where('ph_lop_this.loai', $request['loai']);
                }
                if (!empty($request['ki_hoc'])) {
                    $query->where('ph_lops.ki_hoc', $request['ki_hoc']);
                }
                if (!empty($request['ngay_thi'])) {
                    $query->where('ph_lop_this.ngay_thi', $request['ngay_thi']);
                }
                if (!empty($request['kip_thi'])) {
                    $query->where('ph_lop_this.kip_thi', $request['kip_thi']);
                }
            },
            'lop_this'
        );
        $title = $request->get('title');
        $query_gv = DB::query()->fromSub(
            function ($query) use ($request) {
                $query->from('ph_lop_thi_giao_viens')->join('u_giao_viens', 'u_giao_viens.id', '=', 'ph_lop_thi_giao_viens.giao_vien_id');
                $query->select(['u_giao_viens.id', 'u_giao_viens.name', 'u_giao_viens.email', 'ph_lop_thi_giao_viens.lop_thi_id']);
            },
            'lop_this'
        );
        $items = $query->get();
        $items_gv = [];
        foreach ($query_gv->get() as $key => $item_gv) {
            $items_gv[$item_gv->lop_thi_id][] = ['id' => $item_gv->id, 'name' => $item_gv->name, 'email' => $item_gv->email];
        }
        $items = array_map(function ($item) use ($items_gv) {
            if (isset($items_gv[$item->lop_thi_id])) {
                $item->giao_viens = $items_gv[$item->lop_thi_id];
            } else $item->giao_viens = [];
            return $item;
        }, $items->toArray());
        $items = collect($items);
        $filename = Carbon::now()->format('Ymdhms') . 'xep-lich-thi-giao-vien.xlsx';
        Excel::store(new LopCoiThiGiaoVienExport($items, $title ?? 'ĐIỀU HÀNH THI'), $filename);
        $fullPath = Storage::disk('local')->path($filename);
        return response()->download($fullPath)->deleteFileAfterSend(true);
    }
}
