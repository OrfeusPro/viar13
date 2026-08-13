<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendUserYourOrderWasSend extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($cur_order, $data, $locale)
    {
        $this->cur_order = $cur_order;
        $this->data = $data;
        $this->locale = $locale;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $date_form_now = \Carbon\Carbon::now();

        $now = $date_form_now->format('Y-m-d');

        $created_at = \Carbon\Carbon::parse($this->cur_order->created_at)->format('Y-m-d');
        $top_sale = \App\Models\AMailTopSale::All()->random(3)->translate($this->locale, 'ru');
		//dd($top_sale);
        return $this->subject($this->data->sended_subject)->view('mail.your_order_send')
            ->with('order', $this->cur_order)
			->with('data', $this->data)
            ->with('top_sale', $top_sale)
			->with('now', $now)
			->with('created_at', $created_at);
    }
}
