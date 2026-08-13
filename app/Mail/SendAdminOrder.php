<?php

namespace App\Mail;

use App\Models\Orders as Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendAdminOrder extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($locale, $order_id, $random_pass = '')
    {
        $this->locale = $locale;
        $this->order_id = $order_id;
        $this->random_pass = $random_pass;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $base_url = \URL::to('/');
/*
        if ($this->locale != null) {
            $msg = \App\Models\UserMessage::first()->get()->translate($this->locale)[0];
        } else {
            $msg = \App\Models\UserMessage::first()->get()->translate(\App::getLocale(), 'ru')[0];
        }
*/

        $order = Order::getOrderByIdStatic($this->order_id);
        $order = (array) $order[0];
        $order['delivery'] = json_decode($order['delivery'], 1);
        $order['items'] = json_decode($order['items'], 1);

        $order['price'] = rtrim(rtrim(number_format($order['price'], 2, ',', ' '), '0'), ',') . ' €';
        $ts1 = \App\Models\StringTranlation::first()->get()->translate($this->locale, 'ru')[0];
        $ts2 = \App\Models\StringTranlations2::first()->get()->translate($this->locale, 'ru')[0];
        $ts3 = \App\Models\UserMessage::first()->get()->translate($this->locale, 'ru')[0];
        $data = \App\Models\UserMessage::first()->get()->translate($this->locale, 'ru')[0];

        $now = \Carbon\Carbon::now()->format('d.m.Y');
        $top_sale = \App\Models\AMailTopSale::All()->random(2)->translate($this->locale, 'ru');

        return $this->subject(trans('mail.admin_order_subject', [], $this->locale))->view('mail.your_order_given')
            ->with('basket', $order['items'])
            ->with('order', $order)
            ->with('locale', $this->locale)
            ->with('trans', $data)
            ->with('top_sale', $top_sale)
            ->with('ts1', $ts1)
            ->with('ts2', $ts2)
            ->with('ts3', $ts3)
            ->with('order_id', $this->order_id)
            ->with('base_url', $base_url);

/*
        return $this->subject(trans('mail.admin_order_subject', [], $this->locale))->view('mail.admin_order_notify')
            ->with('admin_order_text', trans('mail.admin_order_text', [], $this->locale))
            ->with('base_url', $base_url)
            ->with('order_id', $this->order_id)
            ->with('random_pass', $this->random_pass)
            ->with('reg_enter_pass', $msg['reg_enter_pass']);*/
    }
}
