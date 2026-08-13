<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIndexPhp
{
    public function handle(Request $request, Closure $next)
    {
        if (!in_array($request->method(), ['GET', 'HEAD'])) {
            return $next($request);
        }

        // Берём исходный путь клиента (а не PathInfo, который Nginx может "сплюснуть")
        $uri  = (string) $request->server('REQUEST_URI');   // напр. "/index.php?utm=x"
        $path = (string) parse_url($uri, PHP_URL_PATH);     // "/index.php" или "/ru/index.php"
        $qs   = $request->getQueryString();
        $q    = $qs ? "?$qs" : '';

        // /index.php -> /
        if ($path === '/index.php') {
            // ВАЖНО: вручную шлём Location: /..., без UrlGenerator (никаких URL::forceRootUrl)
            return response('', 301)->header('Location', '/' . $q);
        }

        // /{locale}/index.php -> /{locale}/  (двухбуквенные локали)
        if (preg_match('~^/([a-z]{2})/index\.php$~i', $path, $m)) {
            return response('', 301)->header('Location', '/' . $m[1] . '/' . $q);
        }

        return $next($request);
    }
}
