<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Sale_30_40_new extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $base_url = \URL::to('/');
        return $this->subject($this->data['subject'])->view('mail.sale_30_40_new')->with('data', $this->data)->with('base_url', $base_url);
    }
}
