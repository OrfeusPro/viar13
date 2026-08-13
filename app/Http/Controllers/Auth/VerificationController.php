<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Request $request)
    {
        return $request->user()->hasVerifiedEmail() ? redirect('/') : view('auth.verify');
    }

    public function verify(Request $request, int $id, string $hash)
    {
        abort_unless((int) $request->user()->getKey() === $id && hash_equals(sha1($request->user()->getEmailForVerification()), $hash), 403);
        if (! $request->user()->hasVerifiedEmail() && $request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }
        return redirect('/');
    }

    public function resend(Request $request)
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->sendEmailVerificationNotification();
        }
        return back()->with('resent', true);
    }
}
