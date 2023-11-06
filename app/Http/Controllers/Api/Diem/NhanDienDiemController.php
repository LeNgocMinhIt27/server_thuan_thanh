<?php

namespace App\Http\Controllers\Api\Diem;

use App\Http\Controllers\Controller;
use App\Jobs\AutoNhanDienDiem;
use App\Models\Diem\BangDiem;
use App\Models\Diem\Diem;
use App\Models\Diem\DiemNhanDien;
use App\Models\Diem\DiemNhanDienLopThi;
use App\Models\Lop\LopThi;
use App\Models\Lop\LopThiSinhVien;
use App\Models\Task;
use App\Models\User\SinhVien;
use Symfony\Component\Process\Process;
use Carbon\Carbon;
use DB;
use Http;
use Illuminate\Http\Request;
use Storage;
use Str;

class NhanDienDiemController extends Controller
{
    public function nhanDienDiem(Request $request, $id)
    {
        $bang_diem = BangDiem::findOrFail($id);
        $disk = Storage::disk('bang-diem');
        $file_path = $bang_diem->duong_dan_tap_tin;
        $file = $disk->get($file_path);
        $api_url = $request->get('url_origin', url()) . "/sohoa/api/bang-diem/{$id}/nhan-dien";
        // $api_url = "http://192.168.4.186:8000/api/bang-diem/{$id}/nhan-dien";
        // $api_detex = "http://192.168.4.218:5001/submit_job";
        $info = $request->all();
        $user = $request->get('user');
        $api_detex = config('app.detext_api') . '/submit_job';
        $info['bang_diem_id'] = $id;
        $formData = [
            'apiWebhook' => $api_url,
            'ki_thi' => $bang_diem->ki_thi
        ];
        try {
            $response =  Http::attach('file', $file, basename($bang_diem->duong_dan_tap_tin))
                ->post($api_detex, $formData)->object();
            $job_id = $response->job_id;
        } catch (\Throwable $th) {
            abort(503, 'Sever tự động nhập đang bận');
        }

        $info['api_detex'] = $api_detex;
        $info['formData'] = $formData;
        $info['ki_hoc'] = $bang_diem['ki_hoc'];

        $bang_diem->update(['trang_thai_nhan_dien' => 'processing']);

        $task = Task::create([
            'status' => 1,
            'task_type' => 'detex-pdf',
            'user_id' =>  $user['id'],
            'job_id' => $job_id,
            'reference' => $info,
            'start_at' => Carbon::now()
        ]);

        return $this->responseSuccess($task);
    }
    public function detectPdf(Request $request, $id)
    {
        $data = $request->all();
        $bang_diem = BangDiem::findOrFail($id);
        if (!Storage::disk()->exists("bang-diem/$id")) {
            Storage::disk()->makeDirectory("bang-diem/$id");
        }
        Storage::disk()->put("bang-diem/$id/request.json", json_encode($data));
        $job_id = $request->get('job_id');

        $task = Task::where('job_id', $job_id)->where('task_type', 'detex-pdf')->first();
        if (empty($task)) {
            abort(404, 'Không tìm thấy tiến trình');
        }
        // dd($bang_diem['loai']);
        $scores_class = json_decode($request->get('score'), true);

        AutoNhanDienDiem::dispatch($job_id, $bang_diem, $scores_class, $task);

        return $this->responseSuccess('succes');
    }
    public function detectPdfByJob(Request $request)
    {
        $data = $request->all();

        Storage::disk()->put("bang-diem/request.json", json_encode($data));
        $job_id = $request->get('job_id');
        $task = Task::where('job_id', $job_id)->where('task_type', 'detex-pdf')->first();
        $id = $task['reference']['bang_diem_id'] ?? null;
        if (empty($task)) {
            abort(404, 'Không tìm thấy tiến trình');
        }
        $bang_diem = BangDiem::findOrFail($id);
        $scores_page = json_decode($request->get('score'), true);
        try {
            DB::beginTransaction();
            DiemNhanDien::where('bang_diem_id', $id)->delete();
            foreach ($scores_page as $page => $scores) {
                foreach ($scores as $score) {
                    DiemNhanDien::insert([
                        'bang_diem_id' => $id,
                        'page' => $page,
                        'stt' => $score['STT'] ?? null,
                        'mssv' => $score['MSSV'] ?? null,
                        'diem' => $score['DIEM'] ?? null,
                    ]);
                }
            }
            $task->update(['status' => 2]);
            $bang_diem->update(['trang_thai_nhan_dien' => 'success']);
            DB::commit();
            return $this->responseSuccess($task);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
