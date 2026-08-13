<?php

namespace App\Http\Middleware;

use Closure;

class SetDefaultLang
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $lng = preg_split('/,|;/', \Request::server('HTTP_ACCEPT_LANGUAGE'));

        try {
            $first = $lng[0];
        } catch (\Throwable $th) {
            $first = 'lv';
        }

        $langs = ['en', 'ee', 'lt', 'lv', 'pl', 'de', 'ru'];
        if (!in_array($first, $langs)) {
            $first = 'lv';
        }

        if (!\Session::has('locale')) {
            \Session::put('locale', $first);
            \App::setLocale($first);
        }

        return $next($request);
    }
}
