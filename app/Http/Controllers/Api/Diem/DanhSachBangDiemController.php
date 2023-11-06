<?php

namespace App\Http\Controllers\Api\Diem;

use App\Constants\RoleCode;
use App\Http\Controllers\Controller;
use App\Models\Diem\BangDiem;
use Illuminate\Http\Request;
use App\Library\QueryBuilder\QueryBuilder;
use App\Models\Diem\DiemNhanDienLopThi;
use Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use DateTime;
use Carbon\Carbon;
use App\Library\FormData\Reader\Reader;
use App\Models\Diem\Diem;
use App\Models\Lop\Lop;
use App\Models\Lop\LopThi;
use App\Models\User\SinhVien;
use DB;


class DanhSachBangDiemController extends Controller
{
    public function indexAgGrid(Request $request)
    {
        $query = BangDiem::query();
        $query = QueryBuilder::for($query, $request)
            ->allowedAgGrid([])
            ->defaultSort('id')
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
    public function store(Request $request)
    {
        $request->validate([
            'ma_hp' => 'required|string|max:255|min:1',
            'ten_hp' => 'required|string|max:255|min:1',
            'ki_hoc' => 'required|string',
            'loai' => 'required',
            'ki_thi' => 'required',
            'file' => ['required', 'file', 'mimes:pdf'],
        ]);
        $uploadedFile = $request->file('file');
        // get page pdf
        $pdf = file_get_contents($uploadedFile);
        $number = preg_match_all("/\/Page\W/", $pdf, $dummy) ?? null;
        $user = $request->user();
        $file_name = Carbon::now()->format('Ymdhms') . '-' . $uploadedFile->getClientOriginalName();
        $disk = Storage::disk('bang-diem');
        $folder_path = 'danh-sach/';
        if (!$disk->exists($folder_path)) {
            $disk->makeDirectory($folder_path);
        }
        $disk->putFileAs($folder_path, $uploadedFile, $file_name);
        $file_url = $folder_path . $file_name;
        $data = $request->all();
        $data['duong_dan_tap_tin'] = $file_url;
        $data['meta'] = ['so_trang' => $number];
        $data['nguoi_tao_id'] = $user->id;
        $result = BangDiem::create($data);
        // $result->meta = array_merge($result->meta, ['so_trang' => $number]);
        // $result->save();
        return $this->responseSuccess($result);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'ma_hp' => 'required|string|max:255|min:1',
            'ten_hp' => 'required|string|max:255|min:1',
            'ki_hoc' => 'required|string',
            'loai' => 'required',
            'ki_thi' => 'required',
        ]);
        $disk = Storage::disk('bang-diem');
        $folder_path = 'danh-sach/';
        $bang_diem = BangDiem::findOrFail($id);
        $old_file_path = $bang_diem['duong_dan_tap_tin'];
        $data = $request->all();
        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            $file_name = Carbon::now()->format('Ymdhms') . '-' . $uploadedFile->getClientOriginalName();
            // kiểm tra file có đang tồn tại không trước khi xoá
            if ($disk->exists($old_file_path)) {
                $disk->delete($old_file_path);
            }
            $disk->putFileAs($folder_path, $uploadedFile, $file_name);
            $file_url = $folder_path . $file_name;
            $data['duong_dan_tap_tin'] = $file_url;
        } else {
            $data['duong_dan_tap_tin'] = '';
        }
        $result = $bang_diem->update($data);
        return $this->responseSuccess($result);
    }
    public function destroy(Request $request, $id)
    {
        $bang_diem = BangDiem::findOrFail($id);
        $user = $request->user();
        if ($bang_diem->ngay_cong_khai) {
            abort(400, 'Bảng điểm này đã được công bố nên không thể xoá');
        }
        if ($user->allow(RoleCode::TEACHER) && !$user->allow(RoleCode::ASSISTANT) && $user->id !== $bang_diem['nguoi_tao_id']) {
            abort(400, 'Bạn không được phép xoá bảng điểm của người khác');
        }
        $old_file_path = $bang_diem['duong_dan_tap_tin'];
        $disk = Storage::disk('bang-diem');

        if ($disk->exists($old_file_path)) {
            $disk->delete($old_file_path);
        }
        $bang_diem->delete();

        return $this->responseSuccess();
    }
    public function showPdf($id)
    {
        $disk = Storage::disk('bang-diem');
        $bang_diem = BangDiem::findOrFail($id);
        $url = $bang_diem['duong_dan_tap_tin'];
        $content = $disk->get($url);

        return response($content)
            ->header('Content-Type', 'application/pdf');
    }
    public function slicePdf(Request $request, $id)
    {
        $request->validate([
            "start_page" => "required|integer",
            "end_page" => "required|integer",
            "lop_thi_id" => "required|integer"
        ]);
        $disk = Storage::disk('bang-diem');
        $bang_diem = BangDiem::findOrFail($id);
        $url = $bang_diem['duong_dan_tap_tin'];
        $input_path = $disk->path($url);
        $folder_path = 'slice-pdf/';

        if (!$disk->exists($folder_path)) {
            $disk->makeDirectory($folder_path);
        }

        $start_page = $request->get('start_page');
        $end_page = $request->get('end_page');

        if ($end_page - $start_page <= 0) {
            return response()->json([
                'message' => "Số trang không hợp lệ",
            ], 400);
        }
        $file_name = Str::uuid()->toString() . ".pdf";
        $output_pdf_path = $disk->path($folder_path) . $file_name;

        $process = new Process([config("app.pdftk_path"), "$input_path", "cat", "$start_page-$end_page", "output", "$output_pdf_path"]);
        $process->run();

        $result = DiemNhanDienLopThi::create([
            'bang_diem_id' => $id,
            'page' => "$start_page" . "," . "$end_page",
            'duong_dan_anh' => "$folder_path" . "$file_name",
            'lop_thi_id' => $request->get('lop_thi_id')
        ]);

        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        return response()->json([
            'message' => 'Thành công',
            'data' => $result,
        ], 200);
    }
    public function showSlicePdf($id)
    {
        $disk = Storage::disk('bang-diem');
        $diem_lop = DiemNhanDienLopThi::findOrFail($id);
        $url = $diem_lop['duong_dan_anh'];
        $content = $disk->get($url);

        return response($content)
            ->header('Content-Type', 'application/pdf');
    }
    public function congBoDiem(Request $request, $id)
    {
        $request->validate(
            [
                'ngay_cong_khai' => ['nullable', 'string'],
                'ngay_ket_thuc_phuc_khao' => ['nullable', 'string'],
            ]
        );
        $req = $request->all();
        $bang_diem = BangDiem::findOrFail($id);
        $lop_this = DiemNhanDienLopThi::withCount([
            'diems',
            'diems as diem_count_not_null' => function ($query) {
                $query->whereNotNull('diem');
            },
            'diems as diem_count_null' => function ($query) {
                $query->whereNull('diem');
            }
        ])->where('bang_diem_id', '=', $id)->get();
        // if ($lop_this) {
        //     $lop_thi_arr = $lop_this->toArray();
        //     foreach ($lop_thi_arr as $item) {
        //         if ($item['diems_count'] === 0 || $item['diem_count_not_null'] > 0) {
        //             abort(400, 'Bảng điểm này vẫn còn lớp thi chưa nhập đủ điểm nên chưa thể công bố');
        //         }
        //     }
        // }
        if (!$lop_this->toArray()) {
            abort(400, 'Bảng điểm môn này chưa có điểm nên chưa thể công bố');
        }

        if ($req['ngay_cong_khai']) {
            $ngay_cong_khai = new DateTime($req['ngay_cong_khai']);
            $req['ngay_cong_khai'] = $ngay_cong_khai->format('Y-m-d');
        };
        if ($req['ngay_ket_thuc_phuc_khao']) {
            $ngay_ket_thuc_phuc_khao = new DateTime($req['ngay_ket_thuc_phuc_khao']);
            $req['ngay_ket_thuc_phuc_khao'] = $ngay_ket_thuc_phuc_khao->format('Y-m-d');
        };
        if ($req['ngay_cong_khai'] > $req['ngay_ket_thuc_phuc_khao']) {
            abort(400, 'Ngày kết thúc phúc khảo không được phép nhỏ hơn ngày công khai');
        }

        $bang_diem->update([
            'ngay_cong_khai' => $req['ngay_cong_khai'],
            'ngay_ket_thuc_phuc_khao' => $req['ngay_ket_thuc_phuc_khao']
        ]);
        return $this->responseSuccess();
    }
    public function show($id)
    {
        $is_cong_khai = false;
        $now =  Carbon::now();
        $query = BangDiem::findOrFail($id);

        if ($now->greaterThanOrEqualTo($query['ngay_cong_khai'])) {
            $is_cong_khai = true;
        }
        $responseData = $query->toArray();
        $responseData['is_cong_khai'] = $is_cong_khai;

        return response()->json($responseData);
    }
    public function bangDiemExcel(Request $request)
    {
        $request->validate([
            'ki_hoc' => 'required|string',
            'loai' => 'required',
            'ki_thi' => 'required',
        ]);
        $user = $request->user();
        $req = $request->all();
        $lop_id = intval($request->get('lop_hoc'));
        $lop = Lop::findOrFail($lop_id);
        if ($lop->ki_hoc !== $req['ki_hoc']) {
            $ki_hoc_ht = $req['ki_hoc'];
            abort(400, "Lớp $lop->ma kỳ $lop->ki_hoc không phù hợp với kỳ $ki_hoc_ht");
        }
        $uploadedFile = $request->file('file');
        $ki_thi = $request->get('ki_thi');
        $req['duong_dan_tap_tin'] = '';
        // lấy lớp thi của giáo viên theo kỳ
        $query_lop_thi = LopThi::with('sinhViens')->join('ph_lops', 'ph_lops.id', '=', 'ph_lop_this.lop_id')
            ->where('ph_lops.id', '=', $lop_id)
            ->where('ph_lop_this.loai', '=', $req['ki_thi']);
        $query_lop_thi->select(['ph_lop_this.id', 'ph_lop_this.lop_id', 'ph_lops.ki_hoc', 'ph_lops.ma_hp', 'ph_lop_this.loai', 'ph_lop_this.ma']);
        $lop_thi_giaoviens = $query_lop_thi->get();
        DB::beginTransaction();
        try {
            $diem_cache = [];
            if ($uploadedFile) {
                $reader = new Reader('excel', [$uploadedFile]);
                $items = $reader->getRecords();
                $header = $reader->getFields();
                $ma_lop_thi = '';
                foreach ($items as $key => $item) {
                    if (empty($item[$header[0]])) {
                        continue;
                    }
                    if (empty($header[4])) {
                        $ma_lop_thi = $item[$header[2]] . '-' . $item[$header[3]];
                    } else {
                        $ma_lop_thi = $item[$header[4]];
                    }
                    if (empty($diem_cache[$ma_lop_thi])) {
                        $diem_cache[$ma_lop_thi] = [];
                    }
                    if (!empty($diem_cache[$ma_lop_thi][$item[$header[0]]])) {
                        $mssv = $item[$header[0]];
                        abort(400, "Sinh viên $mssv đang tồn tại nhiều lần trong bảng điểm lớp thi $ma_lop_thi");
                    }
                    $diem_cache[$ma_lop_thi][$item[$header[0]]] = is_numeric($item[$header[1]]) ? $item[$header[1]] : 0;
                }
            }
            $exist_bang_diem = BangDiem::query()->where('ma_hp', $lop->ma_hp)->where('ki_hoc', $req['ki_hoc'])->where('loai', $req['loai']);
            if ($exist_bang_diem->count()) {
                abort(400, 'Bảng điểm nhập tay môn này đã tồn tại');
            }
            if (!$lop_thi_giaoviens) {
                abort(400, 'Lớp học này không có lớp thi nào đang tồn tại');
            }
            // tạo bảng điểm
            $req['ma_hp'] = $lop->ma_hp;
            $req['ten_hp'] = $lop->ten_hp;
            $req['nguoi_tao_id'] = $user->id;

            $bang_diem = BangDiem::create($req);

            foreach ($lop_thi_giaoviens as $lop_thi_gv) {
                $exitst_bang_diem_lop_thi = Diem::join('ph_lop_this', 'ph_lop_this.id', '=', 'ph_diems.lop_thi_id')
                    ->where('ph_diems.lop_thi_id', '=', $lop_thi_gv->id)
                    ->where('ph_lop_this.loai', $ki_thi)->get()->toArray();
                if (count($exitst_bang_diem_lop_thi) > 0) {
                    $ma_lop_thi = $lop_thi_gv->ma;
                    abort(400, "Lớp thi $ma_lop_thi trong lớp này đã có điểm");
                }
                DiemNhanDienLopThi::create(["lop_thi_id" => $lop_thi_gv->id, "bang_diem_id" => $bang_diem->id]);
                foreach ($lop_thi_gv->sinhViens as $sinh_vien) {
                    Diem::updateOrCreate([
                        'sinh_vien_id' => $sinh_vien->id,
                        'lop_thi_id' => $lop_thi_gv->id,
                    ], [
                        'bang_diem_id' => $bang_diem->id,
                        'nguoi_nhap_id' => $user->id,
                        'diem' => $diem_cache[$lop_thi_gv->ma][$sinh_vien->mssv] ?? null
                    ]);
                }
            }
            DB::commit();
            return $this->responseSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function updateBangDiemExcel(Request $request, $id)
    {
        $request->validate([
            'ma_hp' => 'required|string|max:255|min:1',
            'ten_hp' => 'required|string|max:255|min:1',
            'ki_hoc' => 'required|string',
            'loai' => 'required',
            'ki_thi' => 'required',
        ]);
        $req = $request->all();
        $lop_id = intval($request->get('lop_hoc'));
        $lop = Lop::findOrFail($lop_id);

        $uploadedFile = $request->file('file');
        $ki_thi = $request->get('ki_thi');
        $req['duong_dan_tap_tin'] = '';
        $user = $request->get('user');
        // lấy lớp thi của giáo viên theo kỳ
        $query_lop_thi = LopThi::with('sinhViens')->join('ph_lops', 'ph_lops.id', '=', 'ph_lop_this.lop_id')
            ->where('ph_lops.id', '=', $lop_id)
            ->where('ph_lop_this.loai', '=', $req['ki_thi']);
        $query_lop_thi->select(['ph_lop_this.id', 'ph_lop_this.lop_id', 'ph_lops.ki_hoc', 'ph_lops.ma_hp', 'ph_lop_this.loai', 'ph_lop_this.ma']);
        $lop_thi_giaoviens = $query_lop_thi->get();
        if (!$lop_thi_giaoviens) {
            abort(400, 'Lớp học này không có lớp thi nào đang tồn tại');
        }
        DiemNhanDienLopThi::where('bang_diem_id', $id)->delete();
        DB::beginTransaction();
        try {
            $diem_cache = [];
            if ($uploadedFile) {
                $reader = new Reader('excel', [$uploadedFile]);
                $items = $reader->getRecords();
                $header = $reader->getFields();
                foreach ($items as $key => $item) {
                    if (empty($item[$header[0]])) {
                        continue;
                    }
                    if (empty($header[4])) {
                        $ma_lop_thi = $item[$header[2]] . '-' . $item[$header[3]];
                    } else {
                        $ma_lop_thi = $item[$header[4]];
                    }
                    if (empty($diem_cache[$ma_lop_thi])) {
                        $diem_cache[$ma_lop_thi] = [];
                    }
                    $diem_cache[$ma_lop_thi][$item[$header[0]]] = $item[$header[1]];
                }
            }
            $bang_diem = BangDiem::findOrFail($id);
            $req['ma_hp'] = $lop->ma_hp;
            $req['ten_hp'] = $lop->ten_hp;
            $bang_diem->update($req);
            foreach ($lop_thi_giaoviens as $lop_thi_gv) {
                DiemNhanDienLopThi::create(["lop_thi_id" => $lop_thi_gv->id, "bang_diem_id" => $bang_diem->id]);
                foreach ($lop_thi_gv->sinhViens as $sinh_vien) {
                    Diem::updateOrCreate([
                        'sinh_vien_id' => $sinh_vien->id,
                        'bang_diem_id' => $bang_diem->id,
                        'lop_thi_id' => $lop_thi_gv->id,
                    ], [
                        'nguoi_nhap_id' => $user['id'],
                        'diem' => $diem_cache[$lop_thi_gv->ma][$sinh_vien->mssv] ?? null
                    ]);
                }
            }
            DB::commit();
            return $this->responseSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function countDiemLopThi($id)
    {
        $diem = Diem::query()->where('bang_diem_id', $id);
        if ($diem->count() > 0) {
            $exist = true;
            $count = $diem->count();
        } else {
            $exist = false;
            $count = $diem->count();
        }
        return response()->json([
            'count' => $count,
            'exist' => $exist
        ]);
    }
}
