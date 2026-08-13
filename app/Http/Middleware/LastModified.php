<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;

class LastModified
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
        $response = $next($request);

        if (isset($response->original) && !empty($response->original->updated_at)) {
            $lastModified = Carbon::parse($response->original->updated_at);

            if ($request->hasHeader('If-Modified-Since')) {
                $ifModifiedSince = Carbon::parse($request->header('If-Modified-Since'));

                if ($ifModifiedSince >= $lastModified) {
                    return response()->noContent(304);
                }
            }

            $response->header('Last-Modified', $lastModified->toRfc7231String());
        }

        return $response;
    }
}
