<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class AdminMailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $subject;

    public $greetings;

    public $line;

    public $salutation;

    public ?string $campaignRecipientEmail = null;

    public function shouldSend($notifiable, string $channel): bool
    {
        return $this->campaignRecipientEmail === null
            || (config('admin_migration.bulk_email_enabled') && $notifiable->news === 'YES'
                && $notifiable->email === $this->campaignRecipientEmail);
    }

    /**

     * Create a new notification instance.

     *

     * @param $subject

     * @param $greetings

     * @param $line

     * @param $salutation

     *

     * @return void

     */
    public function __construct($subject, $greetings, $line, $salutation)
    {
        $this->subject = $subject;

        $this->greetings = $greetings;

        $this->line = $line;

        $this->salutation = $salutation;
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
        $base_url = \URL::to('/');

        $uns_text = \App\Models\UserMessage::query()->orderBy('id')->first()
            ?->translate($notifiable->preferredLocale() ?: 'ru', 'ru');
        if (trim((string) ($uns_text?->uns_text ?? '')) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages(['line' => 'Не задан текст отписки UserMessage.uns_text.']);
        }

        $unsubscribe_link = '<br><a href="'.e($base_url.'/user/'.$notifiable->id.'/unsubscribe').'">'.e($uns_text->uns_text).'</a>';

        return (new MailMessage())

            ->subject($this->subject)

            ->greeting($this->greetings)

            ->line(new HtmlString($this->line))

            ->line(new HtmlString($unsubscribe_link))
            ->salutation($this->salutation);

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
