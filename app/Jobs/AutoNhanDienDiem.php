<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\Diem\DiemNhanDienLopThi;
use App\Models\Diem\BangDiem;
use App\Models\Diem\Diem;
use App\Models\Diem\DiemNhanDien;
use App\Models\Lop\LopThi;
use App\Models\Lop\LopThiSinhVien;
use App\Models\User\SinhVien;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;
use Illuminate\Support\Str;
use Storage;

class AutoNhanDienDiem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $job_id;
    protected $bang_diem;
    protected $score_data;
    protected $task;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($job_id, BangDiem $bang_diem, $score_data, Task $task)
    {
        $this->job_id = $job_id;
        $this->score_data = $score_data;
        $this->bang_diem = $bang_diem;
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {
            DB::beginTransaction();
            $bang_diem = $this->bang_diem;
            DiemNhanDien::where('bang_diem_id', $bang_diem->id)->delete();
            // $sinh_vien_lists = SinhVien::whereHas('lops', function ($query) use ($bang_diem) {
            //     $query->where('ki_hoc', $bang_diem->ki_hoc);
            //     $query->where('ma_hp', $bang_diem->ma_hp);
            // })
            //     ->join('ph_lop_thi_sinh_viens', 'ph_lop_thi_sinh_viens.sinh_vien_id', '=', 'u_sinh_viens.id')
            //     ->get(['u_sinh_viens.id', 'u_sinh_viens.mssv', 'ph_lop_thi_sinh_viens.stt']);
            // $sinh_viens = $sinh_vien_lists->mapWithKeys(function ($item, $key) {
            //     return [$item['mssv'] => $item['id']];
            // });

            $lop_thi = LopThi::join('ph_lops', 'ph_lop_this.lop_id', '=', 'ph_lops.id')
                ->where('ph_lops.ki_hoc', '=', $this->task['reference']['ki_hoc'])
                ->where('ph_lop_this.loai', '=', $bang_diem['ki_thi'])
                ->get(['ph_lop_this.id', 'ph_lop_this.ma'])
                ->mapWithKeys(function ($item, $key) {
                    return [$item['ma'] => $item['id']];
                });

            foreach ($this->score_data as $class) {
                $ma_lop_thi = $class["ma_lop_thi"] ?? '';
                $ma_lop_hoc = $class["ma_lop_hoc"] ?? '';
                $nhom = $class["nhom"] ?? '';
                $diems_page = $class["diem"];
                $pages = [];
                $score = [];

                if ($nhom) {
                    $nhom = str_replace("Nhom", "Nhóm", $nhom);
                }
                if (empty($ma_lop_thi) && !empty($ma_lop_hoc) && !empty($nhom)) {
                    $ma_lop_thi = $ma_lop_hoc . '-' . $nhom;
                }
                if (empty($ma_lop_thi)) {
                    continue;
                }
                $lop_thi_id = $lop_thi[$ma_lop_thi] ?? '';
                if (!$lop_thi_id) {
                    continue;
                }
                // lưu điểm vào  db
                print "Bắt đầu cắt lớp thi $ma_lop_thi \n";
                foreach ($diems_page as $page => $diems) {
                    $pages[] = $page;
                    foreach ($diems as $diem) {
                        $score[$diem['MSSV']] = [
                            'bang_diem_id' => $bang_diem->id,
                            'page' => $page,
                            'stt' => $diem['STT'] ?? null,
                            'mssv' => $diem['MSSV'] ?? null,
                            'diem' => $diem['DIEM'] ?? null,
                        ];
                    }
                }
                // $lop_thi_sinh_viens = LopThi::with('sinhViens')->find($lop_thi_id)->toArray();
                // foreach ($lop_thi_sinh_viens['sinh_viens'] as $sinh_vien) {
                //     $sinh_vien_id = $sinh_viens[$sinh_vien['mssv']] ?? '';
                //     if (!$sinh_vien_id) {
                //         continue;
                //     };
                //     Diem::firstOrCreate([
                //         'sinh_vien_id' => $sinh_viens[$sinh_vien['mssv']],
                //         'bang_diem_id' => $bang_diem->id,
                //         'lop_thi_id' => $lop_thi_id,
                //         'nguoi_nhap_id' => $this->task['user_id']
                //     ], [
                //         'diem' => $score[$sinh_vien['mssv']]['diem'] ?? null,
                //     ]);
                // }
                DiemNhanDien::insert($score);
                $score = [];
                $this->autoSlicePdf($pages, $bang_diem['duong_dan_tap_tin'], $bang_diem->id, $lop_thi[$ma_lop_thi]);
                $pages = [];
                print "Cắt lớp thi $ma_lop_thi thành công \n";
            }
            $this->task->update(['status' => 2]);
            $bang_diem->update(['trang_thai_nhan_dien' => 'success']);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            $this->failed($th);
        }
    }
    public function failed()
    {
        print "Cắt thất bại \n";
        $this->task->update(['status' => 3]);
        $this->bang_diem->update(['trang_thai_nhan_dien' => 'failed']);
    }
    private function autoSlicePdf($pages, $file_url, $bang_diem_id, $lop_thi_id)
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

        $file_name = Str::uuid()->toString() . ".pdf";
        $output_pdf_path = $disk->path($folder_path) . $file_name;
        $process = new Process([config("app.pdftk_path"), "$input_path", "cat", "$start_page-$end_page", "output", "$output_pdf_path"]);
        $process->run();
        $result = DiemNhanDienLopThi::updateOrCreate([
            'bang_diem_id' => $bang_diem_id,
            'lop_thi_id' => $lop_thi_id
        ], [
            'page' => join(",", $pages),
            'duong_dan_anh' => "$folder_path" . "$file_name",

        ]);

        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }
        return $result;
    }
}
