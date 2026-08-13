<?php

namespace App\Http;

use App\Http\Middleware\ModifyUrl;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\LastModified;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\CacheControls;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\SetDefaultLang;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\NormalizeSlashes;
use App\Http\Middleware\RedirectIndexPhp;
use Illuminate\Auth\Middleware\Authorize;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\StripTrailingSlash;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Middleware\CheckForMaintenanceMode;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        NormalizeSlashes::class,
        TrustProxies::class,

        CheckForMaintenanceMode::class,

        ValidatePostSize::class,

        TrimStrings::class,

        ConvertEmptyStringsToNull::class,

        'check.webp' => \App\Http\Middleware\CheckWebpSupport::class,

    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [

        'web' => [
            RedirectIndexPhp::class,
            StripTrailingSlash::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,

        ],

        'api' => [

            'throttle:60,1',

            SubstituteBindings::class,

        ],

    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [

        'auth' => Authenticate::class,

        'mod_url' => ModifyUrl::class,

        'cache_controls' => CacheControls::class,

        'preventBackHistory' => PreventBackHistory::class,

        'auth.basic' => AuthenticateWithBasicAuth::class,

        'bindings' => SubstituteBindings::class,

        'cache.headers' => SetCacheHeaders::class,

        'can' => Authorize::class,

        'guest' => RedirectIfAuthenticated::class,

        'password.confirm' => RequirePassword::class,

        'signed' => ValidateSignature::class,

        'throttle' => ThrottleRequests::class,

        'verified' => EnsureEmailIsVerified::class,

        'setLocale' => SetLocale::class,

        'setLang' => SetDefaultLang::class,

        'lastModified' => LastModified::class,

        'resetPlLocale' => \App\Http\Middleware\ResetPlLocale::class,

        'localize'                => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes::class,
        'localizationRedirect'    => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
        'localeSessionRedirect'   => \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
        'localeCookieRedirect'    => \Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect::class,
        'localeViewPath'          => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath::class
        ,
        'integration.api_key' => \App\Http\Middleware\VerifyIntegrationApiKey::class

    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [

        StartSession::class,

        ShareErrorsFromSession::class,

        Authenticate::class,

        ThrottleRequests::class,

        AuthenticateSession::class,

        SubstituteBindings::class,

        Authorize::class,

    ];
}
