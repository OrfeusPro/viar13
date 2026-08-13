<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckWebpSupport
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the 'Accept' header contains 'image/webp'
        if (strpos($request->header('Accept'), 'image/webp') !== false) {
            $request->attributes->set('supportsWebp', true);
        } else {
            $request->attributes->set('supportsWebp', false);
        }

        return $next($request);
    }
}
