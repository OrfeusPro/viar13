<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class AdminToPainterComment extends Mailable
{
    public function __construct(public string $messageText, public string $subjectText) {}

    public function build(): static
    {
        return $this->subject($this->subjectText)->html(nl2br(e($this->messageText)));
    }
}
