<?php

namespace App\Mail;

use App\Models\Orders;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class OrderOverdueDelayMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $order;
    public $delivery;
    public $locale;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Orders $order, array $delivery, string $locale = 'ru')
    {
        $this->order = $order;
        $this->delivery = $delivery;
        $this->locale = $locale ?: 'ru';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        App::setLocale($this->locale);

        return $this->subject(__('mail.order_overdue_delay_subject', ['order_id' => $this->order->id]))
            ->view('mail.order_overdue_delay')
            ->with('order', $this->order)
            ->with('delivery', $this->delivery)
            ->with('locale', $this->locale);
    }
}
