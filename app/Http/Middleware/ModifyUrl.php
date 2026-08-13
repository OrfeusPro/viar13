<?php

namespace App\Http\Middleware;

use Closure;

class ModifyUrl
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

        // get protocol

        if ((!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') ||

            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (!empty($_SERVER['SERVER_PORT'])

                && $_SERVER['SERVER_PORT'] == '443')) {
            $host_protocol = 'https://';
        } else {
            $host_protocol = 'http://';
        }

        // get url

        $url = $_SERVER['REQUEST_URI'];

        // get sitename

        $host = $_SERVER['HTTP_HOST'];

        // multiple trails pattern

        $pattern = '@(\/\/|\?)@';

        // is url match pattern

        if (preg_match($pattern, $url)) {
            $replaced_url = rtrim(preg_replace($pattern, '/', $url));

            $full_url = $host_protocol . $host . $replaced_url;

            return redirect($full_url);
        }

        return $next($request);
    }
}
