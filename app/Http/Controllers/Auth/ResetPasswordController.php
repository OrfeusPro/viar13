<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.passwords.reset', ['token' => $token, 'email' => $request->email]);
    }

    public function reset(Request $request)
    {
        $credentials = $request->validate([
            'token' => ['required'], 'email' => ['required', 'email'], 'password' => ['required', 'confirmed', 'min:8'],
        ]);
        $status = Password::reset($credentials, function (User $user, string $password): void {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', trans($status))
            : back()->withErrors(['email' => trans($status)]);
    }

    public function showLocalizedResetForm(Request $request, string $locale, string $token)
    {
        app()->setLocale($locale);
        return $this->showResetForm($request, $token);
    }

    public function resetLocalized(Request $request, string $locale)
    {
        app()->setLocale($locale);
        return $this->reset($request);
    }
}
