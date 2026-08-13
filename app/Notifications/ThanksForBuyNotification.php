<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ThanksForBuyNotification extends Notification implements ShouldQueue
{
    use Queueable;
    private $pdf;
    private $has_pdf;

    public function __construct($pdf, $has_pdf)
    {
        $this->pdf = $pdf;
        $this->has_pdf = $has_pdf;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if ($this->has_pdf == false) {

            return (new MailMessage())
            ->subject(trans('gl.thansk_for_order'))
            ->greeting(trans('gl.hello_text'))
            ->line(trans('gl.string_thanks'))
            ->salutation(trans('gl.viar_team'));
        } else {

            return (new MailMessage())
            ->subject(trans('gl.thansk_for_order'))
            ->greeting(trans('gl.hello_text'))
            ->line(trans('gl.string_thanks'))
            ->salutation(trans('gl.viar_team'))
            ->attach($this->pdf, [
                'as'   => 'order_details.pdf',
                'mime' => 'text/pdf',
            ]);
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
