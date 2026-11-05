<?php

namespace App\Http\Middleware;

use Closure;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;

class ArticleLocaleSessionRedirect extends LocaleSessionRedirect
{
    public function handle($request, Closure $next)
    {
        $route = $request->route();
        if ($route && $route->getName() === 'blog_inner' && $request->segment(1) === 'blog') {
            // An unprefixed article URL is the default (RU) article, not a
            // language preference. Keep the visitor's session for other pages.
            $localization = app('laravellocalization');
            $localization->setLocale($localization->getDefaultLocale());

            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
