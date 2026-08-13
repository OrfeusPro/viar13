<?php

namespace App\Mail;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use App\Models\Orders as Order;

class SendUserYourOrderGiven extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($basket, $user, $order_id, $locale)
    {
        $this->basket = $basket;
        $this->user = $user;
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
        if(isset($this->locale) && $this->locale)
        {
            App::setLocale($this->locale);
        }
        else
        {
            App::setLocale('ru');
        }

        $order = Order::getOrderByIdStatic($this->order_id);
        $order = (array) $order[0];
        $order['delivery'] = json_decode($order['delivery'], 1);
        $order['items'] = json_decode($order['items'], 1);
        $order['price'] = rtrim(rtrim(number_format($order['price'], 2, ',', ' '), '0'), ',') . ' €';

        $data = \App\Models\UserMessage::first()->get()->translate($this->user->preferredLocale(), 'ru')[0];

        $ts1 = \App\Models\StringTranlation::first()->get()->translate($this->user->preferredLocale(), 'ru')[0];

        $ts2 = \App\Models\StringTranlations2::first()->get()->translate($this->user->preferredLocale(), 'ru')[0];
        $top_sale = \App\Models\AMailTopSale::All()->random(2)->translate($this->user->preferredLocale(), 'ru');

        // $order2 = DB::table('orders')->where('id', $orderId)->first();
        // $basket = json_decode($order2->items, true) ?? [];

        return $this->subject($data['your_order_subject'])->view('mail.your_order_given')
            ->with('basket', $order['items'])
            ->with('order', $order)
            ->with('locale', $this->user->preferredLocale())
            ->with('trans', $data)
            ->with('top_sale', $top_sale)
            ->with('ts1', $ts1)
            ->with('ts2', $ts2)
            ->with('order_id', $this->order_id)
            ->with('base_url', $base_url);
    }
}
