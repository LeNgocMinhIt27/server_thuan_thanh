<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailNotifyTrongThiGV extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    public $user_data;
    public $title;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $user_data, $title)
    {
        $this->data = $data;
        $this->user_data = $user_data;
        $this->title = $title;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'))
            ->view('mails.mail-notify-trong-thi-gv')
            ->with([
                'data_info' => $this->data,
                'user_data' => $this->user_data
            ])
            ->subject($this->title);
    }
}
