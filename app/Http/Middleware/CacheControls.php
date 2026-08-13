<?php

namespace App\Http\Middleware;

use Closure;
use DateTime;

class CacheControls
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

        // If this was a GET request...

        if ($request->isMethod('get')) {
            $response = $next($request);

            $etag = md5($response->getContent());

            $requestEtag = str_replace('"', '', $request->getETags());

            if ($requestEtag && $requestEtag[0] == $etag) {
                $response->setNotModified();
            }

            $t = new DateTime('now');

            $t24 = $t->modify('+1 day');

            $response
                ->setEtag($etag)
                ->setDate($t)
                ->setExpires($t24)
                ->setVary('User-Agent')
                ->header('Cache-Control', ' private, max-age=86400');

            return $response;
        } else {
            return $next($request);
        }
    }
}
