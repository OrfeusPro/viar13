<?php

namespace App\Mail;

use App\Models\Orders as Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use DB;

class ApproveUserCheckoutMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($pdf, $updated_at, $items, $data, $order_id, $locale)
    {
        $this->updated_at = $updated_at;
        $this->pdf = $pdf;
		
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
        if(isset($this->locale) && $this->locale)
        {
            App::setLocale($this->locale);
        }
        else
        {
            App::setLocale('ru');
        }

        $upd_date = preg_replace('/\D/', '', $this->updated_at);

        $data['hello_text'] = trans('gl.hello_text');
        $data['checkout_text'] = trans('gl.checkout_text');

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

	$order_vrv = DB::table('vr_numbers')->where('order_id', $this->order_id)->first();

        return $this->subject(trans('gl.thansk_for_order'))->view('mail.approve_user_checkout')
            ->with('data', $this->data)
            ->with('order_vrv', $order_vrv)
            ->with('order', $order)
            ->with('locale', $this->locale)
            ->with('base_url', $base_url)
            ->with('top_sale', $top_sale)
            ->with('ts1', $ts1)
            ->with('ts2', $ts2)
            ->with('ts3', $ts3)
            ->with('now', $now)
            ->with('order_id', $this->order_id)
            ->attach($this->pdf, [
                'as' => 'order_details-' . $upd_date . '.pdf',
                'mime' => 'text/pdf',
            ]);
    }
}
