<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendUserRevWasAdded extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $base_url = \URL::to('/');

        $data = \App\Models\UserMessage::first()->get()->translate($this->model->orig_locale, 'ru')[0];

        return $this->subject($data['your_rev__was_added_subject'])->view('mail.rev_was_sended')
            ->with('data', $data)->with('base_url', $base_url);
    }
}
