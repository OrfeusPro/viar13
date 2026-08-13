<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApproveUserCheckout extends Notification
{
    use Queueable;

    /**

     * Create a new notification instance.

     *

     * @return void

     */
    public function __construct($pdf)
    {
        $this->pdf = $pdf;
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

        return (new MailMessage())
        ->subject(trans('gl.thansk_for_order'))
        ->greeting(trans('gl.hello_text'))
        ->line(trans('gl.checkout_text'))
        ->salutation(trans('gl.viar_team'))
        ->attach($this->pdf, [
            'as'   => 'order_details.pdf',
            'mime' => 'text/pdf',
        ]);
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
