<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;

class VerifyIntegrationApiKey
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
        $expectedApiKey = (string) config('services.sa_integration.api_key', '');
        $providedApiKey = (string) $request->header('X-Api-Key', '');

        if ($expectedApiKey === '' || $providedApiKey === '' || !hash_equals($expectedApiKey, $providedApiKey)) {
            return new JsonResponse([
                'status' => 'error',
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Invalid API key',
                    'details' => [],
                ],
            ], 401);
        }

        return $next($request);
    }
}

