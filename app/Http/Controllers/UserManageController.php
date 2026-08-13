<?php

namespace App\Http\Controllers;

use App;
use App\Helpers\UserFormHelper;
use App\Http\Controllers\Admin\OrdersController;
use App\Http\Requests\RegisterStoreRequest;
use App\Mail\QuizSendToUser;
use App\Mail\SendUserRegister;
use App\Models\GlobConfig;
use App\Models\OrderAction;
use App\Models\Orders;
use App\Models\OurWork;
use App\Models\User;
use App\Repositories\BasketRepository;
use App\Support\StorefrontLocale;
use DB;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Mail;
use Redirect;
use Storage;
use Str;
use URL;
use Validator;

class UserManageController extends Controller
{

    public function __construct(\Illuminate\Support\Facades\Request $request, BasketRepository $basketRepository)
    {
        parent::__construct($request);
        $this->basketRepository = $basketRepository;
    }

    public function custom_register_ajax(RegisterStoreRequest $request)
    {
        $user = new User();
        $user->first_name = $request->name;
        $user->last_name = $request->surname;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $settings = $user->settings;
        $settings['locale'] = app()->getLocale();
        $user->settings = $settings;

        $user->save();
        Auth::login($user);
        session()->put('email', $user->email);

        Mail::to($user->email)->send(new SendUserRegister($user, $request->password));
        $cart = request()->session()->get('basket', []);
        $this->basketRepository->saveBasketToAbandonedCartModel($cart, true);

        return response()->json(true);
    }

    public function custom_login_ajax(Request $request)
    {
        $credentials = $request->only('email', 'password');


        if (!Auth::attempt($credentials)) {

            return response()->json([
                'status' => false,
                'errors' => __('homepage_new_login_reg.error_login'),
            ]);
        }
        $request->session()->regenerate();
        $cart = request()->session()->get('basket');
        $this->basketRepository->saveBasketToAbandonedCartModel($cart);
        session()->put('email', auth()->user()->email);
        return response()->json([
            'status' => true,
        ]);
    }

    public function reset_password(Request $request, PasswordBroker $passwords)
    {
        if ($request->ajax()) {
            $locale = StorefrontLocale::fromRequest($request);
            app()->setLocale($locale);
            $request->setLocale($locale);
            $request->session()->put('locale', $locale);

            $response = $passwords->sendResetLink($request->only('email'));

            switch ($response) {
                case PasswordBroker::RESET_LINK_SENT:
                    return [
                        'error' => 'false',
                        'msg' => __('homepage_new_login_reg.reset_link_sent'),
                    ];

                case PasswordBroker::INVALID_USER:
                    return [
                        'error' => 'true',
                        'msg' => __('homepage_new_login_reg.invalid_user'),
                    ];
            }
        }

        return false;
    }

    public function user_send_rev(Request $request)
    {
        if ($request->has('avatar_photo')) {
            $av_path = $request->avatar_photo->store('uploads', 'public');
            $avatar_photo_url = $av_path;
        } else {
            $avatar_photo_url = '';
        }

        $promo_foto_url = '';
        if ($request->has('promo_foto')) {
            $pf_path = $request->promo_foto->store('uploads', 'public');
            $promo_foto_url = $pf_path;
        }

        if ($request->has('audio_file')) {
            $aa_path = $request->audio_file->store('uploads', 'public');
            $fn = $request->audio_file->getClientOriginalName();
            $aa_audio_url = $aa_path;
        } else {
            $aa_audio_url = null;
            $fn = null;
        }

        $work = OurWork::create([
            'name' => $request->name,
            'text' => $request->review_text,
            'email' => $request->email,
            'a_player' => '[{"download_link":"' . $aa_audio_url . '","original_name": "' . $fn . '"}]',
            'img' => $promo_foto_url,
            'avatar' => $avatar_photo_url,
            'orig_locale' => $request->locace,
            'active' => 0,
        ]);

        if ($work) {
            $admin_data = GlobConfig::first()->get()->translate(App::getLocale(), 'ru')[0];
            $admin_data_mail = $admin_data['admin_email'];

            $data['to'] = $admin_data_mail;

            $data['subject'] = 'VIARCANVAS – новый отзыв';
            $data['content'] = "Пользователь: {$request->name} <br> ";
            $data['content'] .= 'Отправил отзыв о нас';

            Mail::send([], [], function ($message) use ($data) {
                $message->to($data['to']);
                $message->subject($data['subject']);
                $message->setBody($data['content'], 'text/html');
            });
        }

        return back()->with('success', 1);
    }

    /// TODO: 30-40 функция выполняется при сабмите формы на странице акций
    /// TODO: Facebook функция выполняется при сабмите формы на странице акций
    public function send_screen(Request $request)
    {

        if (Auth::check() && $request->hasFile('image')) {
            $cur_user_id = Auth::id();

            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:png,bmp,jpg,jpeg,heic,heif|max:20240',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first();

                return response()->json([
                    'message' => $error,
                    'errors' => $validator,
                ]);
            }

            $path = $request->image->store('uploads', 'public');
            $screen_url = URL::to('/') . '/storage/' . $path;

            $user = User::where('id', $cur_user_id)->firstOrFail();

            if ( $request->screen=='free') {
                $user->screenshot2 = $screen_url;
                $data['subject'] = 'VIARCANVAS – прислали фото с картиной 30x40';
                $sale_link = URL::to('/') . '/admin/user/' . $user->id . '/set_sale/2/';
                $deny_link = URL::to('/') . '/admin/user/' . $user->id . '/cancel_sale/2/';
            }

            if ( $request->screen=='facebook') {
                $user->screenshot = $screen_url;
                $data['subject'] = 'VIARCANVAS – прислали PrintScreen Facebook';
                $sale_link = URL::to('/') . '/admin/user/' . $user->id . '/set_sale/1/';
                $deny_link = URL::to('/') . '/admin/user/' . $user->id . '/cancel_sale/1/';
            }

            //$user->screenshot = $screen_url;
            $user->save();





            $admin_data = GlobConfig::first()->get()->translate(App::getLocale(), 'ru')[0];

            /// TODO: Временно ставим свой email для отправки теста письма админу
            $admin_data_mail = $admin_data['admin_email'];
            // $admin_data_mail = 'fanx01@gmail.com';


            $data['to'] = $admin_data_mail;

            $data['content'] = "Пользователь: {$user->email} <br> ";
            $data['content'] .= "Скрин: {$screen_url} <br> ";
            $data['content'] .= "<a href='{$sale_link}'>Дать скидку</a><br>";
            $data['content'] .= "<a href='{$deny_link}'>Отказать в скидке</a><br>";


            Mail::send([], [], function ($message) use ($data) {
                $message->to($data['to']);
                $message->subject($data['subject']);
                $message->setBody($data['content'], 'text/html');
            });

            if ( $request->screen=='free') {
                OrderAction::create([
                    'user' => $user->email,
                    'activity' => 'Прислали фото с картиной 30x40:<br> ' . $data['content'],
                ]);
            }

            if ( $request->screen=='facebook') {
                OrderAction::create([
                    'user' => $user->email,
                    'activity' => 'VIARCANVAS – прислали PrintScreen Facebook:<br> ' . $data['content'],
                ]);
            }

            return response()->json([
                'message' => 'Success',
                'file' => URL::to('/') . '/' . $path,
            ]);
        }

    }



// TODO: 30-40
    public function give_user_sale(Request $request)
    {
        if (Auth::check() && $request->has('id')) {
            return view('admin.give_user_sale');
        }
    }

    public function cancel_user_sale(Request $request)
    {
        if (Auth::check() && $request->has('id')) {
            return view('admin.cancel_user_sale');
        }
    }

    // TODO: Функция быстрого заказа
    public function send_photo_portrait_form(Request $request, OrdersController $adm_order_contr)
    {
        if(isset($request['phone']))
        {
            $request['phone'] = str_replace(" ","", $request['phone']);
            $request['phone'] = str_replace(")","", $request['phone']);
            $request['phone'] = str_replace("(","", $request['phone']);
            $request['phone'] = str_replace("-","", $request['phone']);
        }

        $request->validate([
            'email' => 'required',
            'phone' => 'required|min:7|regex:/^\+?[0-9\s()-]+$/',
            'file' => 'required|array',
            'file.*' => 'file|mimes:jpeg,jpg,png,gif,bmp,tiff,webp,pdf,heic,heif',
        ]);

        $data = UserFormHelper::send_photo_portrait_form_helper($request);
        $request['basket_item'] = (array)"1";



        if($request['new_people_count'])
        {
            $request['users_count'] = (array)($request['new_people_count']);
        }
        else
        {
            $request['users_count'] = (array)(1);
        }

        $request['basket_price'] =  (array)$request['overall_price'];
        $request['basket_size'] =  (array)$request['new_size'];
        $request['basket_comment'] =  (array)"";
        $request['pack'] =  (array)$request['box'];
        $request['basket_terms'] =  (array)'';
        $request['sumPrice'] =  (array)$request['overall_price'];
        $request['sumFormatedPrice'] =  (array)($request['overall_price']);
        $request['formatedPrice'] =  (array)($request['overall_price']);

        $request['totalPrice'] = $request['overall_price'];
        $request['saved_price'] = $request['overall_price'];
        $request['formatedTotalPrice'] = $request['overall_price'];
        $request['price'] = $request['overall_price'];
        $request['sale_price'] = $request['overall_price'];

        if(request()->session()->get('locale'))
        {
            $request['country'] = Str::upper(request()->session()->get('locale'));
        }
        else
        {
            $request['country'] = '';
        }

        $request['a_order_from'] = 1;
        $request['payment'] = '';
        $request['quiz_orig_images'] = $data['orig_images'];
        $request['quiz_order'] = 1;
        $request['new_catid'] =  $request['new_catid'];


        $analytic_params = $adm_order_contr->create_admin_order($request);

        // Переименовываем временное изображение
        Orders::renameUploadsPhoto($analytic_params['order_id']);



        return Redirect::route('thanks', ['order_id' => $analytic_params['order_id']]);

    }

    public function send_photo_form_old(Request $request, OrdersController $adm_order_contr)
    {

        if(isset($request['phone']))
        {
            $request['phone'] = str_replace(" ","", $request['phone']);
            $request['phone'] = str_replace(")","", $request['phone']);
            $request['phone'] = str_replace("(","", $request['phone']);
            $request['phone'] = str_replace("-","", $request['phone']);
        }

        $request->validate([
            'email' => 'required',
            'phone' => 'required|min:7|regex:/^\+?[0-9\s()-]+$/'
        ]);

        $data = UserFormHelper::send_photo_portrait_form_helper($request);

        $request['basket_item'] = (array)"1";

        $request['basket_name'] = (array)$request['new_name'];

        //$request['basket_price'] =  (array)$request['new_price'];
        $request['basket_price'] =  (array)$request['new_price'];
        $request['basket_size'] =  (array)$request['new_size'];
        $request['basket_comment'] =  (array)"";
        $request['basket_terms'] =  (array)"";
        $request['pack'] =  (array)$request['box'];

        $request['sumPrice'] =  (array)$request['new_price'];
        $request['sumFormatedPrice'] =(array)$request['new_price'];
        $request['formatedPrice'] = (array)$request['new_price'];




        $request['totalPrice'] = $request['new_price'];
        $request['saved_price'] = $request['new_price'];
        $request['formatedTotalPrice'] = $request['new_price'];
        $request['price'] = $request['new_price'];
        $request['sale_price'] = $request['new_price'];

        if(request()->session()->get('locale'))
        {
            $request['country'] = Str::upper(request()->session()->get('locale'));
        }
        else
        {
            $request['country'] = '';
        }

        $request['a_order_from'] = 9;
        $request['payment'] = '';
        $request['quiz_orig_images'] = $data['orig_images'];
        $request['quiz_order'] = 1;
        $request['new_catid'] =  $request['new_catid'];


        $analytic_params = $adm_order_contr->create_admin_order($request);

        // Переименовываем временное изображение
        Orders::renameUploadsPhoto($analytic_params['order_id']);


        return Redirect::route('thanks', ['order_id' => $analytic_params['order_id']]);
        //return back()->with('success_photo', 1);
    }

    public function send_photo_form(Request $request)
    {
        $rawPhone   = (string) ($request->input('phone') ?? '');
        $cleanPhone = preg_replace('/[()\s-]+/', '', $rawPhone);
        $request->merge(['phone' => $cleanPhone]);

        $request->merge([
            'website' => (string) $request->input('website', ''),
            'form_ts' => (int) ($request->input('form_ts', now()->subSeconds(10)->timestamp)),
        ]);

        $request->validate([
            'image' => 'image|mimes:png,bmp,jpg,jpeg,webp,psd,heic,heif|min:50',
            'phone' => [
                'required',
                'regex:/^\+?\d{7,15}$/',
                function($attr, $value, $fail) {
                    $digits = preg_replace('/\D/', '', (string)$value);
                    if (strlen($digits) < 7 || strlen($digits) > 15) {
                        $fail('Invalid phone length.');
                    }
                },
            ],
            'email'   => 'required|email:rfc|max:150',

            'comment' => ['nullable','string','max:1500','not_regex:/https?:\/\/|www\./i'],

            'website' => ['present','size:0'],

            'form_ts' => ['required','integer', function($attr,$value,$fail){
                try {
                    $delta = now()->diffInSeconds(\Carbon\Carbon::createFromTimestamp((int)$value));
                    if ($delta < 3) $fail('Too fast.');
                } catch (\Throwable $e) {
                    $fail('Invalid timestamp.');
                }
            }],
        ]);

        if ($request->email === "testing@example.com") {
            echo "spam";
            return Redirect::route('thanks');
        }

        if ($request->email === "paouqua@mailbox.in.ua") {
            echo "spam";
            return Redirect::route('thanks');
        }
        
        if (preg_replace('/\D/', '', $request->phone) === '5556660606') {
            echo "spam";
            return Redirect::route('thanks');
        }

        $data = UserFormHelper::send_photo_form_helper($request);

        OrderAction::create([
            'user'     => $request->email,
            'activity' => "Отправил расчет портрета:<br> {$data['content']}",
        ]);

        $basket = [];
        $basket[] = [
            'name'    => 'Расчет портрета',
            'content' => $data['content'],
        ];

        $delivery = [];
        $delivery['deliv_price']  = $request->input('deliv_price');
        $delivery['when_send']    = $request->input('when_send');
        $delivery['email']        = $request->input('email');
        $delivery['first_name']   = $request->input('first_name');
        $delivery['last_name']    = $request->input('last_name');
        $delivery['phone']        = $request->input('phone');
        $delivery['address']      = $request->input('address');
        $delivery['postal_index'] = $request->input('postal_index');
        $delivery['country']      = $request->input('country');
        $delivery['a_order_from'] = $request->input('a_order_from');
        $delivery['city']         = $request->input('city');
        $delivery['sposob']       = $request->input('sposob');
        $delivery['payment']      = $request->input('spopaymentsob', '');

        $cleanComment = \Illuminate\Support\Str::of($request->input('comment', ''))
            ->stripTags()->squish()->limit(1500)->toString();

        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            $random_pass         = \Illuminate\Support\Str::random(8);
            $user                = new User();
            $user->first_name    = '';
            $user->last_name     = '';
            $user->email         = $request->input('email');
            $user->phone         = $request->input('phone', '');
            $user->address       = '';
            $user->postal_index  = '';
            $user->country       = $request->input('country', '');
            $user->client_data   = 'NO';
            $user->news          = 'YES';
            $user->role_id       = 2;
            $user->avatar        = 'users/default.png';
            $user->active_coupon = null;
            $user->password      = \Illuminate\Support\Facades\Hash::make($random_pass);

            $cur_loc             = strtolower(app()->getLocale());
            $settings            = (array)($user->settings ?? []);
            $settings['locale']  = $cur_loc;
            $user->settings      = $settings;
            $user->save();

            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new SendUserRegister($user, $random_pass, $cur_loc));
        }

        \Illuminate\Support\Facades\DB::table('orders')->insertGetId([
            'user_id'        => $user->id,
            'price'          => 0,
            'country'        => '',
            'delivery'       => json_encode($delivery, JSON_UNESCAPED_UNICODE),
            'payment'        => '',
            'status'         => 'watching',
            'payment_status' => 'not_payed',
            'is_admin_order' => 2,
            'items'          => json_encode($basket, JSON_UNESCAPED_UNICODE),
            'comment'        => $cleanComment ?: null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Письма
        Mail::send([], [], function ($message) use ($data) {
            $message->to($data['to']);
            $message->subject($data['subject']);
            $message->setBody($data['content'], 'text/html');
        });

        if ($request->filled('email')) {
            $dataUser            = $data;
            $dataUser['subject'] = __('mail.mail_your_order_received_title_1');
            Mail::to($request->input('email'))->send(new QuizSendToUser($dataUser));
        }

        return Redirect::route('thanks');
    }




    public function send_all_styles_form(Request $request, OrdersController $adm_order_contr)
    {
        $request['sumPrice'] = (array)0;
        $request['sumFormatedPrice'] =  (array)0;
        $request['formatedPrice'] =  (array)0;

        $request['totalPrice'] = 0;
        $request['saved_price'] = 0;
        $request['formatedTotalPrice'] = 0;
        $request['price'] = 0;
        $request['sale_price'] = 0;
        //$request['quiz_orig_images'] = $data['orig_images'];
        $request['basket_terms'] =  (array)'';


        if(isset($request['phone']))
        {
            $request['phone'] = str_replace(" ","", $request['phone']);
            $request['phone'] = str_replace(")","", $request['phone']);
            $request['phone'] = str_replace("(","", $request['phone']);
            $request['phone'] = str_replace("-","", $request['phone']);
        }

        $request->validate([
            'email' => 'required',
            'phone' => 'required|min:7|regex:/^\+?[0-9\s()-]+$/',
            'file' => 'required|array',
            'file.*' => 'file|mimes:jpeg,jpg,png,gif,bmp,tiff,webp,pdf,heic,heif',
        ]);

        $data = UserFormHelper::send_photo_portrait_form_helper($request);

        $request['basket_item'] = (array)"1";
        if($request['new_people_count'])
        {
            $request['users_count'] = (array)($request['new_people_count']);
        }
        else
        {
            $request['users_count'] = (array)(1);
        }
        $request['basket_price'] =  (array)$request['new_price'];
        $request['basket_price'] =  (array)$request['overall_price'];

        if ( empty($request['basket_price'])){   $request['basket_price'] =  (array)0; }
        if ( empty($request['basket_size'])){   $request['basket_size'] = (array) 0; }

        $request['basket_comment'] =  (array)"";
        $request['pack'] =  (array)$request['box'];
        $request['basket_terms'] =  (array)'';
        $request['sumPrice'] =  (array)$request['overall_price'];
        $request['sumFormatedPrice'] =  (array)($request['overall_price']);
        $request['formatedPrice'] =  (array)($request['overall_price']);

        $request['totalPrice'] = $request['overall_price'];
        $request['saved_price'] = $request['overall_price'];
        $request['formatedTotalPrice'] = $request['overall_price'];
        $request['price'] = (float)$request['overall_price'];
        $request['sale_price'] = $request['overall_price'];

        if(request()->session()->get('locale'))
        {
            $request['country'] = Str::upper(request()->session()->get('locale'));
        }
        else
        {
            $request['country'] = '';
        }

        $request['a_order_from'] = 1;
        $request['payment'] = '';
        $request['quiz_orig_images'] = $data['orig_images'];
        $request['quiz_order'] = 1;
        $request['new_catid'] =  $request['new_catid'];



        $analytic_params = $adm_order_contr->create_admin_order($request);

        // Переименовываем временное изображение
        Orders::renameUploadsPhoto($analytic_params['order_id']);

        $data['to'] = env('ADMIN_MAIL');
        $data['subject'] = 'Быстрый заказ №' . $analytic_params['order_id'];

        Mail::send([], [], function ($message) use ($data) {
            $message->to($data['to']);
            $message->subject($data['subject']);
            $message->setBody($data['content'], 'text/html');
        });

        return Redirect::route('thanks', ['order_id' => $analytic_params['order_id']]);

    }






    private function add_date_coupon_user($user_id,$date,$name,$i)
    {
        User::where('id', $user_id)->update([
            'date'.$i => $date,
            'torj'.$i => $name,
        ]);

    }

    private function add_date_coupon_table_coupons($user_id,$date)
    {
        $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
        $coupon_code = mb_substr(str_shuffle($rand_code), 0, 10);
        DB::table('coupons')->insert([
            [
                'text' => $coupon_code,
                'created_at' => now(),
                'updated_at' => now(),
                'value' => 0,
                'is_active' => 1,
                'is_multiuse' => 0,
                'user_id' => $user_id,
                'sale_date' => $date,
                'is_dates_sale'=> 1,
            ]
        ]);
        return $coupon_code;
    }
    //// TODO: 2 даты Редактирование купона если он уже существует
    private function edit_coupon_if_exists($user_id,$date,$name,$i)
    {
        $old_date = DB::table('users')
            ->where('id', $user_id)
            ->first();

        if($i==1){

            DB::table('coupons')
                ->where('user_id', $user_id)
                ->where('is_dates_sale',1)
                ->delete();

            $this->add_date_coupon_user($user_id,$date,$name,$i);
            return $this->add_date_coupon_table_coupons($user_id,$date);


        }
        if($i==2){
            $coupons = DB::table('coupons')
                ->where('user_id', $user_id)
                ->where('sale_date', $old_date->date2)
                ->first();

            if ($coupons){
                DB::table('coupons')
                    ->where('user_id', $user_id)
                    ->where('sale_date',$old_date->date2)
                    ->delete();
                $this->add_date_coupon_user($user_id,$date,$name,$i);
                return $this->add_date_coupon_table_coupons($user_id,$date);
            }
        }
    }



    public function delete_coupon_date($user_id)
    {
        DB::table('coupons')
            ->where('user_id', $user_id)
//            ->where('sale_date', '!=', null)
            ->where('is_dates_sale', 1)
            ->delete();
    }


    /// TODO: 2 даты акция, функция  обработки
    public function send_dates(Request $request)
    {
        $locale = $request->input('locale');
        session(['locale' => $locale]);
        app()->setLocale($locale);
        $request->setLocale($locale);

        if (Auth::check()) {
            $date1 = $request->date1;
            $torj1 = $request->torj1;
            $date2 = $request->date2;
            $torj2 = $request->torj2;

            $cur_user_id = Auth::id();
            // Удаляем старые купоны из таблицы coupons
            $this->delete_coupon_date($cur_user_id);

            // Обновляем даты в таблице users
            $this->add_date_coupon_user($cur_user_id,$date1,$torj1,1);
            $this->add_date_coupon_user($cur_user_id,$date2,$torj2,2);

            // Добавляем купоны в таблицу coupons
            $this->add_date_coupon_table_coupons($cur_user_id,$date1);
            $this->add_date_coupon_table_coupons($cur_user_id,$date2);

            $return[1]['suxess'] = 'true';
            $return[1]['message'] = trans('header.popup-date-suxess', [], $locale);
            $return[2]['suxess'] = 'true';
            $return[2]['message'] = trans('header.popup-date-suxess', [], $locale);
            return response()->json($return);

        }

    }


    /// TODO: 2 даты акция, старя функция обработки
    public function send_dates_old(Request $request)
    {
        $locale = $request->input('locale');

        session(['locale' => $locale]);
        app()->setLocale($locale);
        $request->setLocale($locale);

        if (Auth::check()) {
            $date1 = $request->date1;
            $torj1 = $request->torj1;
            $date2 = $request->date2;
            $torj2 = $request->torj2;
            $cur_user_id = Auth::id();
            $usr_email = User::where('id', $cur_user_id)->pluck('email')->first();
            $user_dates = DB::table('users')->where('id', $cur_user_id)->first();

            $return[1]['suxess'] = 'false';
            $return[2]['suxess'] = 'false';

            // Add new coupons
            if ($user_dates->date1 === NULL) {
                $this->add_date_coupon_user($cur_user_id, $date1, $torj1, 1);
                $return[1]['code'] = $this->add_date_coupon_table_coupons($cur_user_id, $date1);
                $return[1]['suxess'] = 'true';
            } else {
//                if ($user_dates->date1 > now() && ($date1 !== NULL) && $date1 != "") {
                $return[1]['code'] = $this->edit_coupon_if_exists($cur_user_id, $date1, $torj1, 1);
//                    if ($return[1]['code']) {
                $return[1]['suxess'] = 'true';
//                    }
//                }
            }

            if ($user_dates->date2 === NULL) {
                $this->add_date_coupon_user($cur_user_id, $date2, $torj2, 2);
                $return[2]['code'] = $this->add_date_coupon_table_coupons($cur_user_id, $date2);
                $return[2]['suxess'] = 'true';
            } else {
//                if ($user_dates->date2 > now() && ($date2 !== NULL) && $date2 != "") {
                $return[2]['code'] = $this->edit_coupon_if_exists($cur_user_id, $date2, $torj2, 2);
//                    if ($return[2]['code']) {

                $return[2]['suxess'] = 'true';

//                    }
//                }
            }

            if ($return[1]['suxess'] == 'true') {
                $return[1]['message'] = trans('header.popup-date-suxess', [], $locale);

            } else {
                $return[1]['message'] = trans('header.popup-date-error-expired', [], $locale);
            }

            if ($return[2]['suxess'] == 'true') {
                $return[2]['message'] = trans('header.popup-date-suxess', [], $locale);
            } else {
                $return[2]['message'] = trans('header.popup-date-error-expired', [], $locale);
            }

            if ($date1 == "") {
                $return[1]['code'] = "";
                $return[1]['message'] = __('header.popup-date-error-none', [], $locale);
            }
            if ($date2 == "") {
                $return[2]['code'] = "";
                $return[2]['message'] = __('header.popup-date-error-none', [], $locale);
            }

            return response()->json($return);
        }
    }


    public function send_free_image(Request $request)
    {
        if (Auth::check() && $request->hasFile('images')) {
            $cur_user_id = Auth::id();

            // картинки
            $files = $request->images;

            $validator = Validator::make($request->all(), [
                'images' => 'max:5',
                'images.*' => 'image|mimes:png,bmp,jpg,jpeg,heic,heif',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Invalid filesize or extension.',
                    'errors' => $validator,
                ]);
            }

            if ($files && !empty($files)) {
                $data['img_links'] = [];
                $i = -1;

                foreach ($files as $file) {
                    $i++;
                    $file_name = Storage::disk('uploads')->put('uploads', $file);
                    $data['img_links'][$i] = URL::to('/') . '/' . $file_name;
                }
            }

            // данные
            $user = User::where('id', $cur_user_id)->firstOrFail();
            $admin_data = GlobConfig::first()->get()->translate(App::getLocale(), 'ru')[0];
            $admin_data_mail = $admin_data['admin_email'];

            // письмо
            $data['to'] = $admin_data_mail;
            $data['subject'] = trans('gl.act_30_40_title');
            $data['content'] = "Email: {$user->email} <br>";
            if ($user->first_name) {
                $data['content'] .= trans('gl.z_mak_name') . " {$user->first_name} <br>";
            }
            if ($user->phone) {
                $data['content'] .= trans('gl.z_mak_tel') . " {$user->phone} <br>";
            }

            $sale_url = URL::to('/') . '/user/' . $user->id . '/set_sale30_40';
            $data['content'] .= "Дать скидку 30x40: <a href='{$sale_url}'>{$sale_url}</a>";

            $data['content'] .= "<p>" . trans('gl.z_mak_imgs') . "</p><br>";

            // картинки
            $i = 0;
            foreach ($data['img_links'] as $link) {
                $i++;
                $data['content'] .= "<a href='{$link}'>#{$i}</a> ";
            }

            OrderAction::create([
                'user' => $user->email,
                'activity' => "Отправил фото картиной:<br> {$data['content']}",
            ]);

            //

            Mail::send([], [], function ($message) use ($data) {
                $message->to($data['to']);
                $message->subject($data['subject']);
                $message->setBody($data['content'], 'text/html');
            });

            return response()->json([
                'message' => 'Success',
            ]);
        }
    }





    public function unsubscribe(Request $request)
    {
        DB::table('users')->where('id', $request->id)->update(
            ['news' => 'NO']
        );

        return redirect('/');
    }
}
