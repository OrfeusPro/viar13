<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ConfirmPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showConfirmForm()
    {
        return view('auth.passwords.confirm');
    }

    public function confirm(Request $request)
    {
        $request->validate(['password' => ['required', 'string']]);
        if (! Hash::check((string) $request->string('password'), $request->user()->password)) {
            throw ValidationException::withMessages(['password' => [trans('auth.password')]]);
        }
        $request->session()->passwordConfirmed();

        return redirect()->intended('/');
    }
}
