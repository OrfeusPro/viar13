<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\BrandedResetPassword;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Support\StorefrontLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BrandedPasswordResetTest extends TestCase
{
    /** @test */
    public function test_user_uses_the_branded_password_reset_notification()
    {
        Notification::fake();

        $user = new User();
        $user->email = 'customer@example.test';
        $user->first_name = 'Valentin';

        $user->sendPasswordResetNotification('test-reset-token');

        Notification::assertSentTo($user, BrandedResetPassword::class, function ($notification) {
            return $notification->token === 'test-reset-token'
                && $notification->locale === 'ru';
        });
    }

    /** @test */
    public function test_branded_notification_contains_localized_subject_and_valid_reset_url()
    {
        app()->setLocale('ru');

        $user = new User();
        $user->email = 'customer@example.test';
        $user->first_name = 'Valentin';

        $message = (new BrandedResetPassword('test-reset-token'))->toMail($user);

        $this->assertSame(trans('passwords.reset_subject'), $message->subject);
        $this->assertSame('mail.password_reset', $message->view);
        $this->assertSame(60, $message->viewData['expires']);
        $this->assertStringContainsString('/password/reset/test-reset-token', $message->viewData['resetUrl']);
        $this->assertStringContainsString('email=customer%40example.test', $message->viewData['resetUrl']);

        $html = view($message->view, $message->viewData)->render();
        $this->assertStringContainsString('#FA7846', $html);
        $this->assertStringContainsString('Viar Art Canvas', $html);
        $this->assertStringContainsString(trans('passwords.reset_action'), $html);
        $this->assertStringContainsString("@include('.mail.main_head_orange')", file_get_contents(resource_path('views/mail/password_reset.blade.php')));
        $this->assertStringContainsString('https://viarcanvas.com/letters/new/whatsap-icon.png', $html);
        $this->assertStringContainsString(trans('mail.questions'), $html);
        $this->assertStringContainsString(trans('mail.social_networks'), $html);
        $this->assertStringNotContainsString(trans('mail.unsubscribe_2'), $html);
    }

    /** @test */
    public function test_reset_notification_uses_the_current_site_language()
    {
        Notification::fake();
        app()->setLocale('lv');

        $user = new User();
        $user->email = 'customer@example.test';
        $user->first_name = 'Valentin';
        $user->settings = collect(['locale' => 'ru']);

        $user->sendPasswordResetNotification('latvian-token');

        Notification::assertSentTo($user, BrandedResetPassword::class, function ($notification) use ($user) {
            $this->assertSame('lv', $notification->locale);

            app()->setLocale($notification->locale);
            $message = $notification->toMail($user);
            $html = view($message->view, $message->viewData)->render();

            $this->assertSame(trans('passwords.reset_subject', [], 'lv'), $message->subject);
            $this->assertStringContainsString(trans('passwords.reset_action', [], 'lv'), $html);
            $this->assertStringContainsString(trans('mail.questions', [], 'lv'), $html);

            return true;
        });
    }

    /** @test */
    public function test_reset_request_keeps_explicit_storefront_language_even_when_application_locale_is_stale()
    {
        app()->setLocale('ru');

        $request = Request::create('/user/forget_email', 'POST', [
            'email' => 'customer@example.test',
            'locale' => 'lv',
        ], [], [], [
            'HTTP_REFERER' => 'https://viarcanvas.com/ru/cart',
        ]);

        $this->assertSame('lv', StorefrontLocale::fromRequest($request));
    }

    /** @test */
    public function test_reset_request_recovers_storefront_language_from_referer_for_legacy_forms()
    {
        app()->setLocale('ru');

        $request = Request::create('/user/forget_email', 'POST', [
            'email' => 'customer@example.test',
        ], [], [], [
            'HTTP_REFERER' => 'https://viarcanvas.com/de/cart',
        ]);

        $this->assertSame('de', StorefrontLocale::fromRequest($request));
    }

    /** @test */
    public function test_branded_reset_email_renders_in_every_storefront_language()
    {
        $user = new User();
        $user->email = 'customer@example.test';
        $user->first_name = 'Valentin';
        $defaultLocale = app('laravellocalization')->getDefaultLocale();

        foreach (array_keys(config('laravellocalization.supportedLocales')) as $locale) {
            app()->setLocale($locale);

            $message = (new BrandedResetPassword('localized-token'))->toMail($user);
            $html = view($message->view, $message->viewData)->render();

            $this->assertSame(trans('passwords.reset_subject', [], $locale), $message->subject, $locale);
            $this->assertStringContainsString(trans('passwords.reset_action', [], $locale), $html, $locale);
            $this->assertStringContainsString(trans('passwords.reset_page_title', [], $locale), $html, $locale);
            $this->assertStringContainsString(trans('mail.questions', [], $locale), $html, $locale);
            $this->assertStringContainsString(trans('mail.social_networks', [], $locale), $html, $locale);

            if ($locale === $defaultLocale) {
                $this->assertStringNotContainsString('/'.$locale.'/password/reset/', $message->viewData['resetUrl'], $locale);
            } else {
                $this->assertStringContainsString('/'.$locale.'/password/reset/localized-token', $message->viewData['resetUrl'], $locale);
            }
        }
    }

    /** @test */
    public function test_german_email_keeps_the_german_prefix_after_laravel_changes_the_runtime_locale()
    {
        app()->setLocale('de');

        $user = new User();
        $user->email = 'customer@example.test';

        $message = (new BrandedResetPassword('german-token'))->locale('de')->toMail($user);

        $this->assertSame('de', config('app.locale'));
        $this->assertStringContainsString(
            '/de/password/reset/german-token?email=customer%40example.test',
            $message->viewData['resetUrl']
        );
    }

    /** @test */
    public function test_localized_reset_route_sets_page_language_and_keeps_locale_on_submit()
    {
        $controller = app(ResetPasswordController::class);

        foreach (array_diff(array_keys(config('laravellocalization.supportedLocales')), [config('app.locale')]) as $locale) {
            $request = Request::create('/'.$locale.'/password/reset/test-token', 'GET', [
                'email' => 'customer@example.test',
            ]);
            $view = $controller->showLocalizedResetForm($request, $locale, 'test-token');

            $this->assertSame($locale, app()->getLocale(), $locale);
            $this->assertSame('auth.passwords.reset', $view->getName(), $locale);
            $this->assertSame('test-token', $view->getData()['token'], $locale);
            $this->assertSame(
                trans('passwords.reset_page_title', [], $locale),
                trans('passwords.reset_page_title'),
                $locale
            );
            $this->assertStringContainsString(
                '/'.$locale.'/password/reset',
                route('password.update.localized', ['locale' => $locale]),
                $locale
            );

            $getRoute = app('router')->getRoutes()->match(
                Request::create('/'.$locale.'/password/reset/test-token', 'GET')
            );
            $postRoute = app('router')->getRoutes()->match(
                Request::create('/'.$locale.'/password/reset', 'POST')
            );
            $this->assertSame('password.reset.localized', $getRoute->getName(), $locale);
            $this->assertSame('password.update.localized', $postRoute->getName(), $locale);
        }

        $template = file_get_contents(resource_path('views/auth/passwords/reset.blade.php'));
        $this->assertStringContainsString("route('password.update.localized', ['locale' => \$resetLocale])", $template);
    }

    /** @test */
    public function test_reset_page_uses_the_viarcanvas_card_instead_of_legacy_bootstrap_cdn()
    {
        $template = file_get_contents(resource_path('views/auth/passwords/reset.blade.php'));

        $this->assertStringContainsString('viar-password-reset__card', $template);
        $this->assertStringContainsString("trans('passwords.reset_action')", str_replace('@lang', 'trans', $template));
        $this->assertStringContainsString('#fa7846', strtolower($template));
        $this->assertStringContainsString('.phone-mobile-btn { display: none !important; }', $template);
        $this->assertStringNotContainsString('maxcdn.bootstrapcdn.com', $template);
    }
}
