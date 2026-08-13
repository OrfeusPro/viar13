<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $swiftTransport = Mail::getSwiftMailer()->getTransport();
        if ($swiftTransport instanceof \Swift_SmtpTransport) {
            $localDomain = config('mail.host');
            $swiftTransport->setLocalDomain($localDomain);
        }
    }
}
