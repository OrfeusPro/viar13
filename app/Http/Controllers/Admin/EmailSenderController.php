<?php

namespace App\Http\Controllers\Admin;

use App\Entity\UserType;
use App\Models\Locale;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class EmailSenderController extends \Illuminate\Routing\Controller
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index(Request $request)
    {
        if ($request->has('id')) {
            $user = User::find($request->id);
            $data = json_decode($user->settings, true);
            return view('vendor.voyager.email-sender.send_one_mail', [
                'user_email' => $user->email,
                'user_name' => $user->first_name,
                'user_lastname' => $user->last_name,
                'user_id' => $user->id,
                'user_locale' => $data['locale'] ,
            ]);


        } else {
            return view('vendor.voyager.email-sender.index', [
                'locales' => Locale::all(),
                'users' => User::all(),
                'userTypes' => UserType::all(),
            ]);
        }

    }

    public function send(Request $request)
    {
        $users = $this->userRepository->getForAdminMailSender();
        foreach ($users as $user) {
            if (filter_var($user->email, FILTER_VALIDATE_EMAIL) && $user->news == 'YES') {
                $user->sendAdminMail($request);
                if (env('MAIL_HOST', false) == 'smtp.mailtrap.io') {
                    sleep(5);
                }
            }
        }
        return back()->with('status', 'Рассылка успешно завершена');
    }
}
