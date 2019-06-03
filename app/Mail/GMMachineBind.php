<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class GMMachineBind extends Mailable
{
    use Queueable, SerializesModels;

    public $file;
    /**
     * Create a new message instance.
     * @param $file
     *
     * @return void
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('gm')
            ->subject('古茗设备绑定详情')
            ->attach($this->file);
    }
}
