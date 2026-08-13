<?php

namespace App\Http\Middleware;

use Closure;
use Cookie;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = $request->segment(1);

        $langPrefix = ltrim($request->route()->getPrefix(), '/');

        if ($langPrefix) {
            App::setLocale($langPrefix);
        }

        // if ($locale == 'ru') {

        //     $url = $this->getRedirectPath($request);

        //     return redirect($url, 301);

        // }

        $userLangs = preg_split('/,|;/', $request->server('HTTP_ACCEPT_LANGUAGE'));

        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        if (!Cookie::has('user_locale')) {
            if (!empty($userLangs) && $referer != null) {
                $lang = $userLangs[0];

                $availableLangs = ['en', 'lv', 'ee', 'lt'];

                if (in_array($lang, $availableLangs)) {
                    App::setLocale($lang);

                    Cookie::queue('user_locale', $lang, 60 * 24 * 30 * 12);

                    $segments = str_replace(url('/'), '', url()->previous());

                    $segments = array_filter(explode('/', $segments));

                    array_shift($segments);

                    array_unshift($segments, $lang);

                    return redirect()->to(implode('/', $segments));
                }
            }
        }

        return $next($request);
    }

    public function getRedirectPath($request)
    {
        $path = str_replace('ru', '', $request->path());

        return $path;
    }
}
