<?php

namespace App\Mail;

use App\Models\Coupon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Helpers\GalleryTopMail;
use Illuminate\Support\Facades\App;
use Illuminate\Queue\SerializesModels;

class GiftCard extends Mailable
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
        $controller = new GalleryTopMail;

        $this->data = $data;
        $this->locale = $locale;
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

        if(isset($this->locale) && $this->locale) {
            App::setLocale($this->locale);
        } else {
            App::setLocale('ru');
        }

        $coupons = Coupon::where('order_id',$this->data['order_id'])->get();

        return $this->subject($this->data['subject'])->view('mail.giftcard')->with('data', $this->data)->with('base_url', $base_url)->with('top_mail', $this->top_mail)->with('mult', $this->mult)->with('coupons',$coupons);
    }
}
