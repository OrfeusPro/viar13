<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\App;
use Illuminate\Queue\SerializesModels;

class pickup_at_workshop extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $locale = null)
    {
        $this->data = $data;
        $this->locale = $locale;
    }

    /**
     * Build the message.
     *
     */
    public function build()
    {
        $base_url = \URL::to('/');

        if(isset($this->locale) && $this->locale)
        {
            App::setLocale($this->locale);
        }
        else
        {
            App::setLocale('ru');
        }


        $subject= trans('mail.send_to_pick_up', ['order_id' => $this->data['order_id']]);
        return $this->subject($subject)->view('mail_new.pickup_at_workshop')->with('data', $this->data)->with('base_url', $base_url);
    }
}
