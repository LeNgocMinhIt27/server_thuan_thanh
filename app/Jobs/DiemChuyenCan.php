<?php

namespace App\Jobs;

use App\Helpers\DiemChuyenCanHelper;
use App\Models\Lop\Lop;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Exception;

class DiemChuyenCan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $lop;
    public function __construct(Lop $lop)
    {
        $this->lop = $lop;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $lop = $this->lop;
        try {
            DB::beginTransaction();
            foreach ($lop->sinhViens as $key => $sinh_vien) {
                $diem_chuyen_can = DiemChuyenCanHelper::tinhDiemChuyenCan($sinh_vien->id, $lop->id);
                $lop->sinhViens()->syncWithoutDetaching([
                    $sinh_vien->getKey() => ['diem' => $diem_chuyen_can],
                ]);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
    }
}
