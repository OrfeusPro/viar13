<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\App;
use Illuminate\Queue\SerializesModels;

class Payment_successful extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $locale = "ru")
    {
        $this->data = $data;
        $this->locale = $locale;
    }

    /**
     * Build the message.
     *
     * @return $this    `
     */
    public function build()
    {
        $base_url = \URL::to('/');

        if(isset($this->locale) && $this->locale) {
            App::setLocale($this->locale);
        } else {
            App::setLocale('ru');
        }

        $subject= trans('mail.payment_h1');
        $data=$this->data;
        return $this->subject($subject)->view('mail_new.Payment_successful')->with('base_url', $base_url)->with('order_id',$data );
    }
}
