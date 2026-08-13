<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BrandedResetPassword extends Notification
{
    use Queueable;

    /** @var string */
    public $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $locale = $this->locale ?: app()->getLocale();
        // app()->setLocale() mutates config('app.locale') in Laravel 6, so it
        // cannot be used to decide whether the storefront URL needs a prefix.
        $defaultLocale = app('laravellocalization')->getDefaultLocale();
        $supportedLocales = array_keys(config('laravellocalization.supportedLocales', []));

        if (!in_array($locale, $supportedLocales, true)) {
            $locale = $defaultLocale;
        }

        $resetPath = $locale === $defaultLocale
            ? route('password.reset', $this->token, false)
            : route('password.reset.localized', ['locale' => $locale, 'token' => $this->token], false);

        $resetUrl = url($resetPath)
            .'?email='.urlencode($notifiable->getEmailForPasswordReset());

        $broker = config('auth.defaults.passwords');
        $expires = (int) config("auth.passwords.{$broker}.expire", 60);

        return (new MailMessage())
            ->subject(__('passwords.reset_subject'))
            ->view('mail.password_reset', [
                'resetUrl' => $resetUrl,
                'expires' => $expires,
                'user' => $notifiable,
                'locale' => $locale,
            ]);
    }
}
