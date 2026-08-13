<?php

namespace App\Mail;

use App\Helpers\GalleryTopMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Sale30_40 extends Mailable
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
        $controller = new GalleryTopMail;

        $this->data = $data;
        $this->top_mail = $controller->get_topmail_data(true);
        $this->mult=$controller->get_gallery_price();

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $base_url = \URL::to('/');
        return $this->subject($this->data['subject'])->view('mail.sale_30_40_new')->with('data', $this->data)->with('base_url', $base_url)->with('top_mail', $this->top_mail)->with('mult', $this->mult);;
    }
}
