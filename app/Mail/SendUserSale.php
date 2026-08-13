<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendUserSale extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $base_url = \URL::to('/');

        $data = \App\Models\UserMessage::first()->get()->translate($this->user->preferredLocale(), 'ru')[0];

        return $this->subject($data['sale_added'])->view('mail.you_got_sale')
            ->with('data', $data)
            ->with('base_url', $base_url);
    }
}
