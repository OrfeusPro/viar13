<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            // ... other providers
        \SocialiteProviders\Facebook\FacebookExtendSocialite::class . '@handle',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Event::listen(MessageSending::class, function (MessageSending $event) {
            Log::channel('mail')->info('Начало отправки письма', [
                'to'      => array_keys($event->message->getTo() ?? []),
                'subject' => $event->message->getSubject(),
            ]);
        });

        // После успешной отправки
        Event::listen(MessageSent::class, function (MessageSent $event) {
            Log::channel('mail')->info('Письмо успешно отправлено', [
                'to'      => array_keys($event->message->getTo() ?? []),
                'subject' => $event->message->getSubject(),
            ]);
        });
    }
}
