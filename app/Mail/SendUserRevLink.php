<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendUserRevLink extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {

        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        // return $this->subject('Вам присвоена скидка')->view('mail.rev_was_sended');
    }
}
