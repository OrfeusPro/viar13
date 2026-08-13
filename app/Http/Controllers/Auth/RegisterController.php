<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SendUserRegister;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mail;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [

            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],

            'password' => ['required', 'string', 'min:8', 'confirmed'],

        ]);
    }

    protected function create(array $data)
    {
        $user = User::create([

            'invited' => $data['invited'] ?? '',

            'news' => 'YES',

            'email' => $data['email'],

            'password' => Hash::make($data['password']),

        ]);

        $settings = $user->settings;

        $settings['locale'] = app()->getLocale();

        $user->settings = $settings;

        $user->save();

        Mail::to($user->email)->send(new SendUserRegister($user, ''));

        return $user;
    }

    protected function registered(Request $request, $user)
    {
        return json_encode(['status' => 'ok']);
    }
}
