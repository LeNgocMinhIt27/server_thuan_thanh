<?php

namespace App\Jobs;

use App\Mail\MailNotifyTrongThiGV;
use App\Models\Lop\LopThi;
use App\Models\Lop\LopThiGiaoVien;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;


class SendMailTrongThiGV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // $data_gv = $this->data['info'];
        $lop_this = LopThi::join('ph_lops', 'ph_lop_this.lop_id', '=', 'ph_lops.id')->where('ph_lop_this.loai', $this->data['loai'])->get(['ph_lop_this.id', 'ph_lop_this.ma'])->mapWithKeys(function ($item, $key) {
            return [$item['ma'] => $item['id']];
        });
        $ki_hoc = $this->data['ki_hoc'];
        $loai = $this->data['loai'];
        $title = $this->data['title'] ?? "Lịch coi thi";
        $lop_thi_gv = LopThiGiaoVien::join('ph_lop_this', 'ph_lop_this.id', '=', 'ph_lop_thi_giao_viens.lop_thi_id')
            ->join('ph_lops', 'ph_lops.id', '=', 'ph_lop_this.lop_id')
            ->where('ph_lops.ki_hoc', $ki_hoc)->where('ph_lop_this.loai', $loai);
        if ($lop_thi_gv->get()->toArray()) {
            $lop_thi_gv->delete();
        }
        foreach ($this->data['info'] as $item) {
            // $lop_thi_id = $item->lop_thi;
            $giao_vien_id = $item['giao_vien_id'];
            $giao_vien_email = $item['email'];
            if (config('app.debug'))
                $giao_vien_email = 'lvt888664@gmail.com';
            foreach ($item['lop_thi'] as $lop_thi) {
                // dd($lop_thi);

                LopThiGiaoVien::updateOrCreate(["lop_thi_id" => $lop_this[$lop_thi['ma_lop_thi']], "giao_vien_id" => $giao_vien_id]);
            }
            Mail::to($giao_vien_email)->send(new MailNotifyTrongThiGV($item['lop_thi'], $item, $title));
        }
    }
}
