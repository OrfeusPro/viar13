<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class StripTrailingSlash
{
    public function handle($request, Closure $next)
    {
        $path = $request->getPathInfo(); // например: /cat/dir/
        if ($path !== '/' && Str::endsWith($path, '/')) {
            $new = rtrim($path, '/');
            $qs  = $request->getQueryString();
            return redirect()->to($new . ($qs ? "?$qs" : ''), 301);
        }
        return $next($request);
    }
}
