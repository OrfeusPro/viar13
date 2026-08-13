<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\StorefrontLocale;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails {
        sendResetLinkEmail as protected sendResetLinkEmailUsingTrait;
    }

    public function sendResetLinkEmail(Request $request)
    {
        $locale = StorefrontLocale::fromRequest($request);
        app()->setLocale($locale);
        $request->setLocale($locale);
        $request->session()->put('locale', $locale);

        return $this->sendResetLinkEmailUsingTrait($request);
    }
}
