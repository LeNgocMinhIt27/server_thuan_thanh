<?php

namespace App\Jobs;

use App\Mail\MailNotifyDiemDanh;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;
use Illuminate\Support\Facades\Log;

class SendEmailDiemDanh implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $diemdanh;
    protected $data;
    protected $lopdata;
    protected $users;
    protected $message;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($diemdanh, $data, $lopdata, $users, $message)
    {
        $this->diemdanh = $diemdanh;
        $this->data = $data;
        $this->lopdata = $lopdata;
        $this->users = $users;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->data['email'])->send(new MailNotifyDiemDanh([
            'diemdanh' => $this->diemdanh,
            'sinhvien' => $this->data,
            'lopdata' => $this->lopdata,
            'giaovien' => $this->users,
            'message' => $this->message,
        ]));
    }
}
