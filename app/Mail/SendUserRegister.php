<?php

namespace App\Mail;

use App\Models\AbandonedCart;
use App\Models\UserMessage;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendUserRegister extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct($user, $pass, $locale = null)
    {
        $this->user = $user;

        $this->pass = $pass;

        $this->locale = $locale;
    }

    public function build()
    {
        $userRepository = app(UserRepository::class);
        $base_url = \URL::to('/');

        if ($this->locale != null) {
            $msg = UserMessage::first()->get()->translate($this->locale)[0];
        } else {
            $msg = UserMessage::first()->get()->translate(\App::getLocale(), 'ru')[0];
        }
        $now = Carbon::now();
        $abandoned = AbandonedCart::where('email', $this->user->email)
            ->where('is_send_email_twelve_hours', true)
            ->where('token_expires_at', '>', $now )
            ->first();
        $coupon = null;
        if ($abandoned) {
            $coupon = $userRepository->generateCouponUser($this->user->email);
        }

        return $this->subject($msg['reg_sub'])->view('mail.send_user_register')
            ->with('data', $msg)
            ->with('base_url', $base_url)
            ->with('pass', $this->pass)
            ->with('coupon', $coupon)
            ->with('email', $this->user->email);
    }
}
