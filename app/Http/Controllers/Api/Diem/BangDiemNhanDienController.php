<?php

namespace App\Http\Controllers\Api\Diem;

use App\Http\Controllers\Controller;
use App\Models\Diem\BangDiem;
use App\Models\Diem\Diem;
use App\Models\Diem\DiemNhanDien;
use App\Models\Diem\DiemNhanDienLopThi;
use App\Models\Lop\LopThi;
use App\Models\Lop\LopThiSinhVien;
use App\Models\User\SinhVien;
use DB;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Illuminate\Support\Str;
use Storage;


class BangDiemNhanDienController extends Controller
{
    public function getTrangChuaNhanDien($id)
    {
        $bang_diem = BangDiem::findOrFail($id);
        // $lop_this = DiemNhanDienLopThi::where('bang_diem_id', $id)->get();
        // $pages = DiemNhanDien::select('page')->groupBy('page')->get()->pluck('page');
        $pdf_pages = $bang_diem->meta["so_trang"];
        $pages = range(1, $pdf_pages);

        // foreach ($lop_this as $lop_thi) {
        //     $pages = array_filter($pages, function ($value) use ($lop_thi) {
        //         return !in_array($value, $lop_thi->pages);
        //     });
        // }


        return response()->json(array_values($pages));
    }
    public function getLopThi($id)
    {
        $bang_diem = BangDiem::findOrFail($id);
        $lop_thi = LopThi::with('lop')->where('loai', $bang_diem->ki_thi)->whereHas('lop', function ($query) use ($bang_diem) {
            $query->where('ma_hp', $bang_diem->ma_hp)->where('ki_hoc', $bang_diem->ki_hoc);
        })->get();

        return response()->json($lop_thi);
    }
    public function updateLopThi(Request $request, $id)
    {
        $request->validate(['lop_thi_id' => ['required'], 'pages' => ['required', 'array'], 'user_id' => ['required', 'integer']]);
        $bang_diem = BangDiem::findOrFail($id);
        $lop_thi = LopThi::findOrFail($request->get('lop_thi_id'));

        $pages = $request->get('pages');

        $result = $this->slicePDf($pages, $bang_diem['duong_dan_tap_tin'], $id, $lop_thi->getKey());

        return $this->responseCreated($result);
    }
    private function slicePDf($pages, $file_url, $bang_diem_id, $lop_thi_id)
    {

        $disk = Storage::disk('bang-diem');
        $url = $file_url;
        $input_path = $disk->path($url);
        $folder_path = 'slice-pdf/';

        if (!$disk->exists($folder_path)) {
            $disk->makeDirectory($folder_path);
        }
        $start_page = $pages[0];
        $end_page = $pages[count($pages) - 1];
        $diem_nhan_dien_lt_curr = DiemNhanDienLopThi::where('bang_diem_id', $bang_diem_id)->where('lop_thi_id', $lop_thi_id)->first();
        $old_file = $diem_nhan_dien_lt_curr->duong_dan_anh;
        if ($old_file) {
            $disk->delete($old_file);
        }
        $file_name = Str::uuid()->toString() . ".pdf";
        $output_pdf_path = $disk->path($folder_path) . $file_name;
        $process = new Process([config("app.pdftk_path"), "$input_path", "cat", "$start_page-$end_page", "output", "$output_pdf_path"]);
        $process->run();

        $result = DiemNhanDienLopThi::updateOrCreate([
            'bang_diem_id' => $bang_diem_id,
            'lop_thi_id' => $lop_thi_id
        ], [
            'page' => join(',', $pages),
            'duong_dan_anh' => "$folder_path" . "$file_name",

        ]);
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        return $result;
    }
}
