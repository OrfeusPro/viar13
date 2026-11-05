<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TranslationManagerAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('filament')->user();
        if (! $user) {
            if ($request->expectsJson()) {
                abort(401);
            }

            return redirect()->route('filament.admin.auth.login');
        }
        abort_unless(method_exists($user, 'hasPermission') && $user->hasPermission('browse_admin'), 403);
        Auth::shouldUse('filament');

        return $next($request);
    }
}
