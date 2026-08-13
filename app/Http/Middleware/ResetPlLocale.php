<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class ResetPlLocale
{
    public function handle($request, Closure $next)
    {
        $path = ltrim($request->path(), '/');

        // If session locale is PL but user goes to non-PL URL on viarcanvas.com,
        // drop the locale to avoid forced redirect back to /pl.
        if (session('locale') === 'pl' && !Str::startsWith($path, 'pl')) {
            session()->forget('locale');
        }

        return $next($request);
    }
}
