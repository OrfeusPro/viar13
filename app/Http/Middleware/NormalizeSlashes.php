<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NormalizeSlashes
{
    public function handle(Request $request, Closure $next)
    {
        $raw = $request->server('REQUEST_URI') ?? '/';

        $qpos = strpos($raw, '?');
        $pathRaw = ($qpos === false) ? $raw : substr($raw, 0, $qpos);
        $qs     = ($qpos === false) ? ''   : substr($raw, $qpos); // уже с ведущим '?'

        $base = rtrim($request->getBaseUrl(), '/'); // '' или '/app'

        $normalizedPath = preg_replace('#/+#', '/', $pathRaw);

        if ($normalizedPath === '' || $normalizedPath[0] !== '/') {
            $normalizedPath = '/'.$normalizedPath;
        }

        if ($normalizedPath !== '/' && substr($normalizedPath, -1) === '/') {
            $normalizedPath = rtrim($normalizedPath, '/');
        }

        if ($base !== '') {
            $normalizedPath = preg_replace('#^('.preg_quote($base, '#').')+#', $base, $normalizedPath);
            if (strpos($normalizedPath, $base.'/') !== 0 && $normalizedPath !== $base) {
                $normalizedPath = $base . ($normalizedPath === '/' ? '' : $normalizedPath);
            }
        }

        if ($normalizedPath !== $pathRaw) {
            $url = $request->getSchemeAndHttpHost() . $normalizedPath . $qs;
            return redirect()->to($url, 301);
        }

        return $next($request);
    }
}
