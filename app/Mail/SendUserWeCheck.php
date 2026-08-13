<?php

namespace App\Mail;

use App\Models\Orders as Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendUserWeCheck extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($items, $data, $order_id, $locale)
    {
        $this->items = $items;

        $this->data = $data;

        $this->order_id = $order_id;

        $this->locale = $locale;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $base_url = \URL::to('/');

        $basket = $this->items;

        $order = Order::getOrderByIdStatic($this->order_id);

        $order = (array) $order[0];

        $order['delivery'] = json_decode($order['delivery'], 1);

        $order['items'] = json_decode($order['items'], 1);

        $order['price'] = rtrim(rtrim(number_format($order['price'], 2, ',', ' '), '0'), ',') . ' €';

        $ts1 = \App\Models\StringTranlation::first()->get()->translate($this->locale, 'ru')[0];

        $ts2 = \App\Models\StringTranlations2::first()->get()->translate($this->locale, 'ru')[0];

        $ts3 = \App\Models\UserMessage::first()->get()->translate($this->locale, 'ru')[0];

        $now = \Carbon\Carbon::now()->format('d.m.Y');
        $top_sale = \App\Models\AMailTopSale::All()->random(2)->translate($this->locale, 'ru');

        return $this->subject($this->data['we_check_title'])->view('mail.we_check_order')
            ->with('order', $order)
            ->with('locale', $this->locale)
            ->with('base_url', $base_url)
            ->with('data', $this->data)
            ->with('top_sale', $top_sale)
            ->with('ts1', $ts1)
            ->with('ts2', $ts2)
            ->with('ts3', $ts3)
            ->with('now', $now)
            ->with('order_id', $this->order_id);
    }
}
