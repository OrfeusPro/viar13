<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SendUserRegister;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $user = User::create([
            'invited' => $request->input('invited', ''),
            'news' => 'YES',
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'settings' => ['locale' => app()->getLocale()],
        ]);
        Auth::login($user);
        $request->session()->regenerate();
        Mail::to($user->email)->send(new SendUserRegister($user, ''));

        return $request->expectsJson() ? response()->json(['status' => 'ok']) : redirect('/');
    }
}
