<?php

namespace App\Http\Controllers;

use App\Models\GalleryType;
use DB;
use App;
use Exception;
use Str;
use URL;
use Mail;
use Cookie;
use Session;
use Storage;
use Validator;
use App\Models\User;
use App\Models\Stock;
use App\Models\Locale;
use App\Models\Orders;
use App\Entity\BasketType;
use App\Models\CountryTel;
use App\Models\GalleryBox;
use App\Models\GlobConfig;
use App\Models\GalleryItem;
use App\Models\OrderAction;
use Illuminate\Support\Arr;
use App\Models\BasketString;
use Illuminate\Http\Request;
use App\Models\AbandonedCart;
use App\Models\ADeliveryTown;
use App\Mail\SendUserRegister;
use App\Models\AProductionTime;
use App\Models\GalleryDecoration;
use App\Services\ImageSaverService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use App\Repositories\BasketRepository;
use App\Http\Requests\RemoveBasketItemRequest;
use App\Http\Requests\UpdateBasketCountRequest;
use App\Models\DeliveryPickupAtViarWorkshop;

class BasketController extends Controller
{
    private $galleryItem;
    private $imageSaverService;
    private $basketRepository;

    /**
     * BasketController constructor.
     * @param  ImageSaverService  $imageSaverService
     */
    public function __construct(ImageSaverService $imageSaverService)
    {
        $this->galleryItem = app(GalleryItem::class);
        $this->imageSaverService = $imageSaverService;
        $this->basketRepository = resolve(BasketRepository::class);
        //стартовый шаблон
        $this->template = env('THEME_RESOURCES') . '.index';
    }

    public function save_base64_image2(){
        $base64String = Storage::disk('local')->get('base64.txt');
        try {
            $url = $this->save_base64_image($base64String);
            echo "Изображение успешно сохранено: {$url}";
        } catch (\Throwable $e) {
            echo "Ошибка: " . $e->getMessage();
        }
        return "ok";
    }

    public function delimage(Request $request)
    {
        $basket = $this->basketRepository->normalizeBasket(request()->session()->get('basket'));
        request()->session()->put('basket', $basket);
        $cartId = $request["cartId"];
        $cartImgId = $request["cartImgId"];
        $imgSrc = $request["imgSrc"];
        $response = [];
        $response['success'] = 0;

        if (isset($cartId) && isset($cartImgId)) {
            if (isset($basket[$cartId]['orig_images']) && is_array($basket[$cartId]['orig_images'])) {
                if (($basket[$cartId]['orig_images'][$cartImgId] ?? null) == $imgSrc) {
                    unset($basket[$cartId]['orig_images'][$cartImgId]);
                    $response['success'] = 1;
                    if (!count($basket[$cartId]['orig_images'])) {
                        unset($basket[$cartId]['orig_images']);
                    }
                }
            } else if (isset($basket[$cartId]['savedImage'])) {
                //dd()
            }
        }
        session(['basket' => $basket]);
        $this->basketRepository->saveBasketToAbandonedCartModel($basket);
        return json_encode($response);
    }


    //// TODO: Функция обработки бонусов, после нажатия на кнопку "Использовать бонусы"
    public function submitBonuses()
    {
       $basket = $this->basketRepository->normalizeBasket(request()->session()->get('basket'));
       request()->session()->put('basket', $basket);
       Session::put('spend_bonus', '1');
       session(['basket' => $basket]);
       $this->basketRepository->saveBasketToAbandonedCartModel($basket);
       $response['success'] = 1;
       return json_encode($response);
    }

    //// TODO:  Нажатие на кнопку применить купон
    public function coupon_use(Request $request)
    {
        $cur_coupon = $request->couponData;
        $user = \Auth::user();
        if (!auth()->check()) {
            return response()->json([
                'finded' => "user",
            ]);
        }
        $coupon = DB::table('coupons')->where('text', $cur_coupon)->first();

        Session::put('coupon_type', 'none');

        if ($coupon)
        {
            Session::put('coupon_id', $coupon->id);
            Session::put('coupon_val', $coupon->value);

            if ($coupon->is_dates_sale)   {  Session::put('coupon_type', 'date');   }

            if ($coupon->is_30_40_free)   {  Session::put('coupon_type', '30_40');   }

            if ($coupon->is_universal)   {  Session::put('coupon_type', 'universal');   }

            if ($coupon->is_facebook)   {  Session::put('coupon_type', 'facebook');   }

            if ($coupon->is_1free)   {  Session::put('coupon_type', '1free');   }

            if ($coupon->free_delivery)   {  Session::put('coupon_type', 'free_delivery');   }

            if ($coupon->is_40_60)   {  Session::put('coupon_type', '40_60');   }

            if ($coupon->is_abandoned_basket)   {  Session::put('coupon_type', 'abandoned_basket');   }

            if ($coupon->is_giftcard)   {  Session::put('coupon_type', 'giftcard');   }

            if ($user->is_active_friend_inv==0 && Session::get('coupon_type')=='none'){
                $friend = DB::table('users')->where('inv_sale_code', $cur_coupon)->first();

                if ($friend && $friend->id==$coupon->user_id && $coupon->user_id!=$user->id ) {   Session::put('coupon_type', 'friend');   }
                else {
                    $user->active_coupon = null;
                    $user->save();
                    Session::put('coupon_id', -2);
                }

                }
            $user->active_coupon = $coupon->id;
            $user->save();
        }
        else
        {
            $user->active_coupon = null;
            $user->save();
            Session::put('coupon_id', -1);
        }

        return response()->json([
            'finded' => "1",
        ]);
    }

    //// TODO: Удаление уже выбраного Купона
    static function clearcart()
    {
        Session::put('coupon_id', 0);
        Session::put('coupon_val', 0);
        Session::put('coupon_type', "none");
        Session::put('spend_bonus', 0);
        // Session::forget('basket');

        $response = [];
        $response['success'] = 1;
        if (Auth::user()) {
            $user = \Auth::user();
            $bonuses=intval($user->bonuses);
            Session::put('bonus', $bonuses);
            $user->active_coupon = null;
            $user->is_coupon_dates = null;
            $user->save();
        }
        return json_encode($response);
    }

    public function updateimg(Request $request)
    {
        $img_base = $request->input('newImgSrc');
        $imgSrc = $request->input('imgSrc');
        $cartId = $request->input('cartId');
        $cartImgId = $request["cartImgId"];
        $response['success'] = 0;

        if (strpos($img_base, 'image/png') !== false) {
            $img = str_replace('data:image/png;base64,', '', $img_base);
            $ext = '.png';
        } else {
            $img = str_replace('data:image/jpeg;base64,', '', $img_base);
            $ext = '.jpeg';
        }

        $img = str_replace(' ', '+', $img);
        $img_data = base64_decode($img);
        $rand_name = Str::random(12);
        file_put_contents(public_path() . '/uploads/' . $rand_name . $ext, $img_data);

        $img = '/uploads/' . $rand_name . $ext;

        $basket = $this->basketRepository->normalizeBasket(request()->session()->get('basket'));
        request()->session()->put('basket', $basket);
        $response = [];

        if (isset($basket[$cartId]['activeImage']) && $basket[$cartId]['activeImage'] && $basket[$cartId]['activeImage'] == $imgSrc) {
            $basket[$cartId]['activeImage'] = $img;
            $response['success'] = 1;
        } else if (isset($basket[$cartId]['orig_images']) && $basket[$cartId]['orig_images']) {
            $response['success'] = 0;
            foreach ((array) $basket[$cartId]['orig_images'] as $item) {
                if ($item == $imgSrc) {
                    $basket[$cartId]['orig_images'][$cartImgId] = asset($img);
                    $response['success'] = 1;
                }
            }
        } else if ($cartImgId == 'savedImage') {
            $basket[$cartId]['savedImage'] = asset($img);
            $basket[$cartId]['save_active_img'] = 1;
            $response['success'] = 1;
        } else {
            $response['success'] = 1;
        }


        // session(['basket' => $basket]);

        if ($response['success']) {
            session(['basket' => $basket]);
            $this->basketRepository->saveBasketToAbandonedCartModel($basket);
        }

        return json_encode($response);
    }

    public function setmaking(Request $request)
    {
        $makeInfo = $request->input('makeInfo');
        $lang = $request->input('lang');

        $response = [];

        $basket = $this->basketRepository->normalizeBasket(request()->session()->get('basket'));
        request()->session()->put('basket', $basket);

        if (empty($basket)) {
            $response['success'] = 0;
            return json_encode($response);
        }
        //dd($basket);
        foreach ($basket as $index => $product) {

            if(isset($basket[$index]['pid']) && $basket[$index]['pid'] == BasketType::GIFT_CARD) {
                $count = (int)($basket[$index]['count'] ?? 0);
                $basket[$index]['total_item_price'] = ($count * $basket[$index]['price']);
                continue;
            }

            if(isset($basket[$index]['name']) && $basket[$index]['name'] == "Collage")
            {
                $AProductionTime = AProductionTime::where('category', 'collage')->first()->translate($lang, 'ru');
            }
            else if(isset($basket[$index]['name']) && $basket[$index]['name'] == "Canvas")
            {
                $AProductionTime = AProductionTime::where('category', 'canvas')->first()->translate($lang, 'ru');
            }
            else
            {
                $AProductionTime = AProductionTime::where('category', 'portrait')->first()->translate($lang, 'ru');
            }

            $text = $makeInfo."_text";
            $price = $makeInfo."_price";

            $basket[$index]['terms'] = $AProductionTime->$text . " " .$AProductionTime->$price . " €";
            $basket[$index]['terms_price'] = $AProductionTime->$price . "€";
            $count = (int)($basket[$index]['count'] ?? 0);
            $basket[$index]['total_item_price'] = ($count * $basket[$index]['price']) + $AProductionTime->$price;
        }

        session(['basket' => $basket]);
        $this->basketRepository->saveBasketToAbandonedCartModel($basket);

        //dd($basket);
        $response['success'] = 1;
        return json_encode($response);
    }

    public function setcoupon()
    {
        $coupon_spend = request()->session()->get('spend_coupon');

        if ($coupon_spend==false){
        session(['spend_coupon' => 1]);
        $response = [];
        $response['success'] = 1;
        return json_encode($response);

        }
    }

    public function setdelivery(Request $request)
    {
        $workingdays=null;

        $cartDate = $request->input('cartDate');

        if($cartDate)
        {
            $holidays = array();

            $cartDate = substr($cartDate,3,2)."/".substr($cartDate,0,2)."/".substr($cartDate,6,4);

            $workingdays = $this->getWorkingDays(date("Y-m-d H:i:s"), date("Y-m-d", strtotime($cartDate)), $holidays);

            if($workingdays < 2 && $workingdays != null)
            {
                $request['makeInfo'] = "express";
                $this->setmaking($request);
            }
        }

        $response = [];

        $country = $request->input('country');
        $price = (float)$request->input('price');
        $delivery_type = $request->input('delivery_type');
        $cartComment = $request->input('cartComment');
        $cartCommentImage = $request->input('cartCommentImage');
        if(isset($cartCommentImage['src']) && $cartCommentImage['src'])
        {
            $cartCommentImage = $cartCommentImage['src'];
        }
        else
        {
            $cartCommentImage = '';
        }

        if($cartCommentImage)
        {
            if (strpos($cartCommentImage, 'image/png') !== false) {
                $img = str_replace('data:image/png;base64,', '', $cartCommentImage);
                $ext = '.png';
            } else {
                $img = str_replace('data:image/jpeg;base64,', '', $cartCommentImage);
                $ext = '.jpeg';
            }

            $img = str_replace(' ', '+', $img);
            $img_data = base64_decode($img);
            $rand_name = Str::random(12);
            file_put_contents(public_path() . '/uploads/' . $rand_name . $ext, $img_data);

            $cartCommentImage = '/uploads/' . $rand_name . $ext;
        }

        $city = $request->input('city');
        $index = $request->input('index');
        $pickup = $request->input('pickup');
        $pickupWorkshopId = (int)$request->input('pickup_workshop_id', 0);
        $deliveryTownId = (int)$request->input('delivery_town_id', 0);
        if($pickup)
        {
            $address = $pickup;
        }
        else
        {
            $address = $request->input('address');
        }

        if($delivery_type == 'pickup_at_viar_workshop')
        {
            $address = $request->input('city');
            $city = null;
            $deliveryTownId = 0;
        }

         if($delivery_type == 'city_delivery')
         {
             $index = null;
             $pickupWorkshopId = 0;
         }


        $pickup = $request->input('pickup');

        // $cart_delivery = request()->session()->get('cart_delivery');
        // Session::forget('cart_delivery');
        if ($delivery_type === null)
        {
            //email delivery
            $cart_delivery = [];
            $cart_delivery['delivery_type'] = 'email';
            $cart_delivery['country'] = $country;
            $cart_delivery['price'] = $price;
            $cart_delivery['date'] = $cartDate;
            $cart_delivery['cartComment'] = $cartComment;
            $cart_delivery['cartCommentImage'] = $cartCommentImage;
            $cart_delivery['city'] = $city;
            $cart_delivery['index'] = $index;
            $cart_delivery['address'] = $address;
            $cart_delivery['pickup'] = $pickup;
            $cart_delivery['pickup_workshop_id'] = $pickupWorkshopId > 0 ? $pickupWorkshopId : null;
            $cart_delivery['delivery_town_id'] = $deliveryTownId > 0 ? $deliveryTownId : null;

            session(['cart_delivery' => $cart_delivery]);

            return response()->json(['success' => 1]);
        }


        $cart_delivery = [];
        $cart_delivery['delivery_type'] = $delivery_type;
        $cart_delivery['country'] = $country;
        $cart_delivery['price'] = $price;
        $cart_delivery['date'] = $cartDate;
        $cart_delivery['cartComment'] = $cartComment;
        $cart_delivery['cartCommentImage'] = $cartCommentImage;
        $cart_delivery['city'] = $city;
        $cart_delivery['index'] = $index;
        $cart_delivery['address'] = $address;
        $cart_delivery['pickup'] = $pickup;
        $cart_delivery['pickup_workshop_id'] = $pickupWorkshopId > 0 ? $pickupWorkshopId : null;
        $cart_delivery['delivery_town_id'] = $deliveryTownId > 0 ? $deliveryTownId : null;

        session(['cart_delivery' => $cart_delivery]);

        $response['success'] = 1;
        return json_encode($response);
    }

    function getWorkingDays($startDate,$endDate,$holidays)
    {
        $endDate = strtotime($endDate);
        $startDate = strtotime($startDate);

        $days = ($endDate - $startDate) / 86400 + 1;

        $no_full_weeks = floor($days / 7);
        $no_remaining_days = fmod($days, 7);

        $the_first_day_of_week = date("N", $startDate);
        $the_last_day_of_week = date("N", $endDate);

        if ($the_first_day_of_week <= $the_last_day_of_week) {
            if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
            if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
        }
        else {
            if ($the_first_day_of_week == 7) {
                $no_remaining_days--;

                if ($the_last_day_of_week == 6) {
                    $no_remaining_days--;
                }
            }
            else {
                $no_remaining_days -= 2;
            }
        }

        $workingDays = $no_full_weeks * 5;
        if ($no_remaining_days > 0 )
        {
            $workingDays += $no_remaining_days;
        }

        foreach($holidays as $holiday){
            $time_stamp=strtotime($holiday);
            if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
                $workingDays--;
        }

        return $workingDays;
     }

    public function setuser(Request $request)
    {
        $response = [];

        if(isset($request['phone']))
        {
            $request['phone'] = str_replace(" ","", $request['phone']);
            $request['phone'] = str_replace(")","", $request['phone']);
            $request['phone'] = str_replace("(","", $request['phone']);
            $request['phone'] = str_replace("-","", $request['phone']);
        }

        $phoneRec = $request->input('phone_rec');
        if ($phoneRec !== null) {
            $phoneRec = str_replace(" ","", $phoneRec);
            $phoneRec = str_replace(")","", $phoneRec);
            $phoneRec = str_replace("(","", $phoneRec);
            $phoneRec = str_replace("-","", $phoneRec);
            $phoneRec = trim($phoneRec);

            if ($phoneRec === '') {
                Session::forget('phone_rec');
            } else {
                Session::put('phone_rec', $phoneRec);
            }
        }

        if ($request['ur_name'] == "true") {
            Session::put('ur_name', "on");
            Session::put('ur_name_l', $request['ur_name_l']);
            Session::put('ur_reg_num', $request['ur_reg_num']);
            Session::put('ur_legal_addr', $request['ur_legal_addr']);
            Session::put('ur_pnr_nr', $request['ur_pnr_nr']);
            Session::put('ur_bank_name', $request['ur_bank_name']);
            Session::put('ur_bank_code', $request['ur_bank_code']);
            Session::put('ur_bank_acc_code', $request['ur_bank_acc_code']);
        } else {
            Session::forget('ur_name');
            Session::put('ur_name', null);
            Session::put('ur_name_l', null);
            Session::put('ur_reg_num', null);
            Session::put('ur_legal_addr', null);
            Session::put('ur_pnr_nr', null);
            Session::put('ur_bank_name', null);
            Session::put('ur_bank_code', null);
            Session::put('ur_bank_acc_code', null);
        }

        $request->validate([
            'phone' => 'required|min:7|regex:/^\+?[0-9\s()-]+$/'
        ]);

        // create user
        if(isset($request['email']) && $request['email'] && isset($request['phone']) && $request['phone'])
        {
            // keep abandoned-cart email in sync with the latest input
            $request['email'] = trim($request['email']);
            Session::put('email', $request['email']);

            $check_user = \App\Models\User::where('email', $request['email'])->first();

            $random_pass = '';
            if (!$check_user) {
                $random_pass = Str::random(8);
                $user = new User();
                $user->first_name = $request['name'] ?? '';
                $user->last_name = $request['surname'] ?? '';
                $user->email = $request['email'];
                $user->phone = $request['phone'] ?? '';
                $user->address = null;
                $user->postal_index = null;
                $user->country = $request['country_code'] ?? '';
                $user->client_data = 'NO';
                $user->news = 'YES';
                $user->role_id = 2;
                $user->avatar = 'users/default.png';
                $user->active_coupon = null;
                $user->password = Hash::make($random_pass);
                $cur_loc = strtolower($request['country_code']);
                $settings = $user->settings;
                $settings['locale'] = $cur_loc;
                $user->settings = $settings;
                $user->save();

                // send user notify
                Mail::to($user->email)->send(new SendUserRegister($user, $random_pass, $cur_loc));

                Auth::attempt(['email' => $request['email'], 'password' => $random_pass]);
                //$this->authenticated($request, $this->guard()->user()) ?: json_encode(['status' => 'ok'])
                $response['success'] = 1;
            } else {

                if(isset(\Auth::user()->id))
                {
                    $user = \Auth::user();
                    if(!$user->country)
                    {
                        $user->country = $request['country_code'];
                        $cur_loc = strtolower($request['country_code']);
                        $settings = $user->settings;
                        $settings['locale'] = $cur_loc;
                        $user->settings = $settings;
                    }



                    $user->first_name = $request['name'] ?? '';
                    $user->last_name = $request['surname'] ?? '';
                    $user->phone = $request['phone'] ?? '';
                    $user->save();

                    $response['success'] = 1;
                }
                else
                {
                    $response['success'] = 0;
                    $response['reason'] = 'existing_user';
                }
            }
        }
        else
        {
            $response['success'] = 0;
        }


        return json_encode($response);
    }

    public function setpay(Request $request)
    {
        $response = [];
        $cart_pay_type = [];

        if($request->input('paymentData'))
        {
            $cart_pay_type['type'] = $request->input('paymentData');
            session(['cart_pay_type' => $cart_pay_type]);

            $response['success'] = 1;
        }
        else
        {
            $response['success'] = 0;
        }
        return json_encode($response);
    }

    /// TODO: Функция для получения данных корзины
    public function get_cart()
    {

        $locale = request()->session()->get('locale');

        if (isset($locale)) {
            App::setLocale($locale);
        }

        $response = [];
        $response['success'] = 1;

        $basket = $this->get_basket();

        $terms = [];
        if (!empty($basket)) {
            $total_item_counts = 0;
            foreach ($basket as $basketIndex => $product) {
                if (!is_array($product) || !isset($product['sumPrice'])) {
                    continue;
                }

                $count = (int)($product['count'] ?? 0);
                $total_item_counts += $count;

                //получение total_item_price для общей цены изготовления и стоимости
                if(isset($basket[$basketIndex]['terms_price']))
                {
                    $basket[$basketIndex]['total_item_price'] = ($count * $basket[$basketIndex]['price']) + (float)$basket[$basketIndex]['terms_price'];
                }
                else
                {
                    $basket[$basketIndex]['total_item_price'] = $count * $basket[$basketIndex]['price'];
                }

                if (isset($basket[$basketIndex]['terms'])) {
                    $terms[$basketIndex]["id"] = $basketIndex;
                    $terms[$basketIndex]["value"] = $basket[$basketIndex]['terms'];
                }
            }
        } else {
            $total_item_counts = 0;
        }

        $basket_country = request()->session()->get('basket_country');
        $contry_mult = 1;
        if ($basket_country) {
            $contry_mult = DB::table('country_tels')->where('country_code', $basket_country)->pluck('price_country_mltpr')->first();
            if (!$contry_mult || $contry_mult == null) {
                $contry_mult = 1;
            }
        }

        $recommendedItems = $this->basketRepository->getRecommendedItems($basket, 3, $contry_mult);

        $content = view(env('THEME_RESOURCES') . '.cart.sidebar')
            ->with('basket', $basket)
            ->with('btn', trans("cart_new.general_checkout"))
            ->with('btn_class', 'cart_send_products')
            ->with('action', route('cart.step2'))
            ->with('step', 1)
            ->with('recommendedItems', $recommendedItems)
            ->render();

        $response['terms'] = $terms;
        $response['html'] = $content;
        $response['total_item_counts'] = $total_item_counts;

        return json_encode($response);
    }

    public function get_basket()
    {
        $basket = $this->basketRepository->normalizeBasket(request()->session()->get('basket'));
        request()->session()->put('basket', $basket);
        $lang = app()->getLocale();

        if (empty($basket)){
            $this->basketRepository->restoreBasketToAbandonedCartModel();
            $this->clearcart();

            $basket = $this->basketRepository->normalizeBasket(request()->session()->get('basket'));
            request()->session()->put('basket', $basket);
        } else {
            $this->basketRepository->saveBasketToAbandonedCartModel($basket);
            foreach ($basket as $index => &$item) {
                if (isset($item["pid"])) {
                    $galleryItem =
                        GalleryItem::withTranslation(App::getLocale(),
                            false)
                            ->where('id', $item["pid"])
                            ->first();

                    if ($galleryItem) {
                        $translated =
                            $galleryItem->translate(App::getLocale());
                        $item['name'] = $translated->name;
                        if (isset($item["decor_id"]) && $item["decor_id"] > 0) {
                            $decorations =
                                GalleryDecoration::withTranslation(App::getLocale(),
                                    false)
                                    ->where('id',
                                        $item["decor_id"])
                                    ->first();
                            $decoration =
                                $decorations->translate(App::getLocale());
                            $item['hud_of'] =
                                $decoration->name;
                        }
                    }

                    if (isset($item['pack'])) {
                        $box = GalleryBox::where('name', $item['pack'])->orWhereHas('translations', function ($query) use ($item) {
                            $query->where('table_name', 'gallery_boxes')
                                ->where('column_name', 'name')
                                ->where('value', $item['pack']);
                        })->first();

                        if(isset($box))
                        {
                            $basket[$index]['pack'] = $box->translate($lang, 'ru')->name ?? $basket[$index]['pack'];
                        }
                    }

                    if (isset($item['terms'])) {
                        $basket[$index]['terms'] = GalleryItem::getTermsByPrice($item['terms']) . ($item['terms_price'] === 0.0 ? '' : ' '.intval($item['terms_price']). ' €');
                    }

                    if (isset($item['type'])) {
                        $basket[$index]['type'] = $this->reverseTranslateAcrossLocales($basket[$index]['type'], $lang, 'gallery') ?? $basket[$index]['type'];
                    }
                }
            }
            unset($item);
        }
        $basket_country = request()->session()->get('basket_country');

        if ($basket_country) {
            $contry_mult = DB::table('country_tels')->where(
                'country_code',
                $basket_country
            )->pluck('price_country_mltpr')->first();

            if (!$contry_mult || $contry_mult == null) {
                $contry_mult = 1;
            }
            $basket = $this->basketRepository->getBasketProperties($basket, $contry_mult);
        } else {
            $basket = $this->basketRepository->getBasketProperties($basket);
        }


        if (isset($basket['coupon_id']) && $basket['coupon_id']>0)
        {
            if ((float)$basket['coupon_val']>0) {
                if (strpos($basket['coupon_val'], '%') === false) {
              $basket['coupon_val'] = $basket['coupon_val'] . "€";
            } } else $basket['coupon_val']="";
        }


        return $basket;
    }

    private function reverseTranslateAcrossLocales(string $translatedText, string $toLocale, string $file): ?string
    {
        $locale = new Locale();
        $locales = [];

        foreach ($locale->getLocales() as $item) {
            $locales[] = $item->prefix;
        }

        foreach ($locales as $locale) {
            $path = resource_path("lang/{$locale}/{$file}.php");
            if (!file_exists($path)) {
                continue;
            }

            $translations = require $path;

            foreach ($translations as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subKey => $subVal) {
                        if ($subVal === $translatedText) {
                            $fullKey = "{$key}.{$subKey}";
                            return __("{$file}.{$fullKey}", [], $toLocale);
                        }
                    }
                } else {
                    if ($value === $translatedText) {
                        return __("$file.$key", [], $toLocale);
                    }
                }
            }
        }

        return null;
    }

    public function cart()
    {
        $basket = $this->get_basket();

        $stocks = Stock::first()->select('facebook_sale', 'friend_sale', 'date_1_sale', 'date_2_sale')
            ->get()->translate(App::getLocale(), 'ru')[0];
        $data = BasketString::first()->translate(App::getLocale(), 'ru');
        $c_tels = CountryTel::orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');
        $friend_sale_count = DB::table('stocks')->where('id', 1)->pluck('friend_sale')->first();

        $basket_country = request()->session()->get('basket_country');
        $contry_mult = 1;
        if ($basket_country) {
            $contry_mult = DB::table('country_tels')->where('country_code', $basket_country)->pluck('price_country_mltpr')->first();
            if (!$contry_mult || $contry_mult == null) {
                $contry_mult = 1;
            }
        }

        $recommendedItems = $this->basketRepository->getRecommendedItems($basket, 3, $contry_mult);

        $content = view(env('THEME_RESOURCES') . '.cart.step1')
            ->with('basket', $basket)
            ->with('data', $data)
            ->with('friend_sale_count', $friend_sale_count)
            ->with('c_tels', $c_tels)
            ->with('stocks', $stocks)
            ->with('empty', $this->chek_for_empty($basket))
            ->with('recommendedItems', $recommendedItems)
            ->render();

        $cart_popup = view(env('THEME_RESOURCES') . '.cart.cart_popup')
            ->render();

        $cart_popup = null;

        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'modals', $cart_popup);
        $this->vars = Arr::add($this->vars, 'title', __('header.cart'));
        return $this->renderOutput();
    }

    public function cart_total_item_counts($basket)
    {
        $basket = $this->basketRepository->normalizeBasket($basket);
        $total_item_counts = 0;
			if (!empty($basket))
        {
				foreach ($basket as $basketIndex => $product)
	            {
                    if (!is_array($product) || !isset($product['sumPrice']))
	                {
						continue;
	                }

                    $count = (int)($product['count'] ?? 0);
					$total_item_counts += $count;

	                if(isset($basket[$basketIndex]['terms_price']))
	                {
	                    $basket[$basketIndex]['total_item_price'] = ($count * $basket[$basketIndex]['price']) + (float)$basket[$basketIndex]['terms_price'];
	                }
	                else
	                {
	                    $basket[$basketIndex]['total_item_price'] = $count * $basket[$basketIndex]['price'];
	                }
	            }

        }

        return $total_item_counts;
    }

    public function cart_step2()
    {
        $user = \Auth::user();
        $basket = $this->get_basket();

        $cart_total_item_counts = $this->cart_total_item_counts($basket);

        if(!$cart_total_item_counts)
        {
            return redirect()->route('cart.index');
        }

        $stocks = Stock::first()->select('facebook_sale', 'friend_sale', 'date_1_sale', 'date_2_sale')
            ->get()->translate(App::getLocale(), 'ru')[0];
        $data = BasketString::first()->translate(App::getLocale(), 'ru');
        $c_tels = CountryTel::orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');
        $friend_sale_count = DB::table('stocks')->where('id', 1)->pluck('friend_sale')->first();

        $alternativeSizeData = null;
        $recommendationData = null;
        $showModal = false;

        $modalShown = Session::get('cart_step2_modal_shown', false);
        if (!$modalShown) {
            $realItemsCount = 0;
            $firstItemKey = null;
            $firstItem = null;

            foreach ($basket as $key => $item) {
                if (is_array($item) && isset($item['pid'])) {
                    $realItemsCount++;
                    if ($realItemsCount === 1) {
                        $firstItemKey = $key;
                        $firstItem = $item;
                    }
                }
            }

            if ($realItemsCount === 1 && $firstItem) {
                $hasSpecialLabel = $firstItem['has_special_label'] ?? false;

                if (!$hasSpecialLabel) {
                    $alternativeSizeData = $this->basketRepository->getAlternativeSizeWithDiscount($firstItem);

                    if ($alternativeSizeData) {
                        $alternativeSizeData['basket_key'] = $firstItemKey;
                        $showModal = true;
                    }
                }
            }

            $hasRecommendationInBasket = false;
            foreach ($basket as $item) {
                if (is_array($item) && !empty($item['is_recommendation'])) {
                    $hasRecommendationInBasket = true;
                    break;
                }
            }

            if ($realItemsCount > 1 && !$alternativeSizeData && !$hasRecommendationInBasket) {
                $basket_country = request()->session()->get('basket_country');
                $contry_mult = 1;
                if ($basket_country) {
                    $contry_mult = DB::table('country_tels')->where('country_code', $basket_country)->pluck('price_country_mltpr')->first();
                    if (!$contry_mult || $contry_mult == null) {
                        $contry_mult = 1;
                    }
                }

                $recommendedItems = $this->basketRepository->getRecommendedItems($basket, 1, $contry_mult);

                if (!empty($recommendedItems)) {
                    $recommendationData = $recommendedItems[0];
                    $showModal = true;

                    Session::put('recommendation_discount_' . $recommendationData['id'], 30);
                }
            }

            if ($showModal) {
                Session::put('cart_step2_modal_shown', true);
            }
        }

        $content = view(env('THEME_RESOURCES') . '.cart.step2_data')
            ->with('basket', $basket)
            ->with('data', $data)
            ->with('friend_sale_count', $friend_sale_count)
            ->with('c_tels', $c_tels)
            ->with('stocks', $stocks)
            ->with('empty', $this->chek_for_empty($basket))
            ->with('user', $user)
            ->with('alternativeSizeData', $alternativeSizeData)
            ->with('recommendationData', $recommendationData)
            ->render();

        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'title', __('header.cart'));
        return $this->renderOutput();
    }

    public function cart_step3()
    {
        $user = \Auth::user();
        $basket = $this->get_basket();

        $onlyGiftCardOnline = true;
        foreach ($basket as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (isset($item['pid']) && $item['pid'] != BasketType::GIFT_CARD || ($item['card_type'] ?? '') !== 'online') {
                $onlyGiftCardOnline = false;
                break;
            }

            $onlyGiftCardOnline = true;
        }

        $cart_total_item_counts = $this->cart_total_item_counts($basket);

        if(!$cart_total_item_counts)
        {
            return redirect()->route('cart.index');
        }

        if(!$user)
        {
            return redirect()->route('cart.step2');
        }

        $stocks = Stock::first()->select('facebook_sale', 'friend_sale', 'date_1_sale', 'date_2_sale')
            ->get()->translate(App::getLocale(), 'ru')[0];
        $data = BasketString::first()->translate(App::getLocale(), 'ru');


        $c_tels = CountryTel::orderBy('sort', 'asc')->get()->translate(strtolower(App::getLocale()), 'ru');
        $DeliveryPickupAtViarWorkshop = DeliveryPickupAtViarWorkshop::where("is_show",1)->orderBy('sort', 'asc')->get()->translate(strtolower(App::getLocale()), 'ru');
        $friend_sale_count = DB::table('stocks')->where('id', 1)->pluck('friend_sale')->first();

        $current_locale = app()->getLocale();

// ToDo: Вставить выборку для городов
        $sale_towns= ADeliveryTown::with(['translations' => function ($query) use ($current_locale) {
            $query->where('locale', $current_locale);
        }])->get();

//        $allSessionData = session()->all();
//        echo '<pre>';
//        var_dump($allSessionData);
//        echo '</pre>';
 //       $citys=  $this->get_citys('LT');


        $content = view(env('THEME_RESOURCES') . '.cart.step3_delivery')
           ->with('citys', $this->get_citys(strtolower($user['country'])))
//            ->with('citys', $citys)
            ->with('warehouses', $this->get_warehouse(strtolower($user['country'])))
            ->with('basket', $basket)
            ->with('data', $data)
            ->with('friend_sale_count', $friend_sale_count)
            ->with('c_tels', $c_tels)
            ->with('DeliveryPickupAtViarWorkshop', $DeliveryPickupAtViarWorkshop)
            ->with('user', $user)
            ->with('stocks', $stocks)
            ->with('sale_towns', $sale_towns)
            ->with('onlyGiftCardOnline', $onlyGiftCardOnline)
            ->with('empty', $this->chek_for_empty($basket))
            ->render();



        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'title', __('header.cart'));
        return $this->renderOutput();
    }

    public function cart_step4()
    {
        $user = \Auth::user();
        $basket = $this->get_basket();

        $cart_total_item_counts = $this->cart_total_item_counts($basket);

        if(!$cart_total_item_counts)
        {
            return redirect()->route('cart.index');
        }

        if(!$user)
        {
            return redirect()->route('cart.step2');
        }

        $cart_delivery = request()->session()->get('cart_delivery');


        $stocks = Stock::first()->select('facebook_sale', 'friend_sale', 'date_1_sale', 'date_2_sale')
            ->get()->translate(App::getLocale(), 'ru')[0];
        $data = BasketString::first()->translate(App::getLocale(), 'ru');
        $c_tels = CountryTel::orderBy('sort', 'asc')->where('country_code',$cart_delivery['country'])->first()->translate(App::getLocale(), 'ru');
        $friend_sale_count = DB::table('stocks')->where('id', 1)->pluck('friend_sale')->first();

        //dd($cart_delivery);



        $content = view(env('THEME_RESOURCES') . '.cart.step4_payment')

            ->with('basket', $basket)
            ->with('data', $data)
            ->with('friend_sale_count', $friend_sale_count)
            ->with('c_tels', $c_tels)
            ->with('cart_delivery', $cart_delivery)
            ->with('stocks', $stocks)
            ->with('empty', $this->chek_for_empty($basket))
            ->render();

        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'title', __('header.cart'));
        return $this->renderOutput();
    }

    public function get_citys($country)
    {
        $arr = array();
        $arr['country'] = $country;
        if($country == 'ee') {
            $arr['country'] = 'et'; //убираем метод доставки для EE страны
        }

        $get_pickup_points_list = 'https://go.venipak.lt/ws/get_pickup_points?' . http_build_query($arr);
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $get_pickup_points_list,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
        ]);

        $response = curl_exec($curl);
        $r_data = json_decode($response, true);

        curl_close($curl);

        $response = collect($r_data);

        $response = $response->filter(function ($value, $key) {
            if ($value['type'] == '1') {
                return $value;
            }
        });
        $unique = $response->unique('city');

        return $unique;
    }

    public function get_warehouse($country)
    {
        $arr = array();
        $arr['country'] = $country;
        if($country == 'ee') {
            $arr['country'] = 'et'; //убираем метод доставки для EE страны
        }

        $get_pickup_points_list = 'https://go.venipak.lt/ws/get_pickup_points?' . http_build_query($arr);
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $get_pickup_points_list,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
        ]);

        $response = curl_exec($curl);
        $r_data = json_decode($response, true);

        curl_close($curl);

        $response = collect($r_data);

        $filtered = $response->filter(function ($value, $key) {
            if ($value['type'] == '1') {
                return $value;
            }
        });

        return $filtered;
    }

    /**
     * Display basket page
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        $basket = $this->get_basket();

        $stocks = Stock::first()->select('facebook_sale', 'friend_sale', 'date_1_sale', 'date_2_sale')
            ->get()->translate(App::getLocale(), 'ru')[0];
        $data = BasketString::first()->translate(App::getLocale(), 'ru');
        $c_tels = CountryTel::orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');
        $friend_sale_count = DB::table('stocks')->where('id', 1)->pluck('friend_sale')->first();

        return view('basket', [
            'basket' => $basket,
            'data' => $data,
            'friend_sale_count' => $friend_sale_count,
            'c_tels' => $c_tels,
            'stocks' => $stocks,
            'empty' => $this->chek_for_empty($basket),
        ]);
    }

    private function chek_for_empty(array $basket): bool
    {
        foreach ($basket as $key => $el) {
            if (is_array($el)) {
                return false;
            }
        }

        return true;
    }

    public function update_prices(Request $request)
    {
        Session::put('basket_country', $request->country);
    }

    public function renderThanksPage()
    {

        $orders = app(Orders::class);

        if (Auth::check()) {
            $basket_country = request()->session()->get('basket_country');

            $contry_mult = 1;
            if ($basket_country) {
                $contry_mult = DB::table('country_tels')->where(
                    'country_code',
                    $basket_country
                )->pluck('price_country_mltpr')->first();

                if (!$contry_mult || $contry_mult == null) {
                    $contry_mult = 1;
                }
            }

            $last_order = $orders->getLastOrderByCurrentUser();
            $order = (array) $last_order;
            $del_info = json_decode($order['delivery'], 1);
            $user_phone = $del_info['phone'];
            $user_addr = $del_info['address'];
            $order_date = $del_info['when_send'];
        }
        else
        {
            $last_order = null;
            $user_phone = null;
            $user_addr = null;
            $order_date = null;
        }

        $data = BasketString::first()->translate(App::getLocale(), 'ru');

        /// We want to get current order id
        $order_id = $last_order->id;

        // We want to check if the $payed is exist and if it is true
        if (isset($payed) && $payed)
        {

            // i want to check delivery data in this order by id , i have a function in Order model called getDeliveryData()
            // i want to check if has_invited_sale=1 then i want to know id of the user who invited this sale

            $has_invited_sale= $order->getDeliveryData($order_id,'has_invited_sale');

            //if has_invited_sale=1 then i want to know id of the user who invited this sale
            if($has_invited_sale==1)
            {
                /// We want to find a user in database, who invited this sale
                /// We need to find a user who has inv_sale_code = $order->inv_sale_code
                $invited_user = User::where('inv_sale_code', $order->inv_sale_code)->first();

                /// Get user id of invited user
                $invited_user_id = $invited_user->id;

                ///Assign bonus to invited user i have functio addBonusToUser() in Order model
                $order->addBonusToUser($invited_user_id, 5);
            }




        }

        ///  find used_coupon from orders table
        $used_coupon = DB::table('orders')->where('id', $order_id)->first();
        $used_coupon = $used_coupon->used_coupon;



        /// delete used coupon from coupons table



        /// We want to clear session data about coupon
        /// TODO: Сбрасываем данные о купоне и бонусах

        if (Auth::check()) {
            $user = \Auth::user();
            $user->active_coupon = null;
            $user->save();

            $user = \Auth::user();

        }

        // We want to clear session data about bonus and coupon
        session(['spend_bonus' => 0]);
        Session::forget('spend_bonus');

        session(['spend_coupon' => false]);
        Session::forget('spend_coupon');

        session(['ur_name' => false]);
        Session::forget('ur_name');

        session(['ur_reg_num' => false]);
        Session::forget('ur_reg_num');

        session(['ur_name_l' => false]);
        Session::forget('ur_name_l');

        session(['ur_legal_addr' => false]);
        Session::forget('ur_legal_addr');

        session(['ur_pnr_nr' => false]);
        Session::forget('ur_pnr_nr');

        session(['ur_bank_name' => false]);
        Session::forget('ur_bank_name');

        session(['ur_bank_code' => false]);
        Session::forget('ur_bank_code');

        session(['ur_bank_acc_code' => false]);
        Session::forget('ur_bank_acc_code');

        Cookie::queue(
            Cookie::forget('basket')
        );

        Session::forget('basket');


        $emailToClean = null;
        if (Auth::check()) {
            $emailToClean = Auth::user()->email;
        } elseif (session()->has('email')) {
            $emailToClean = session('email');
        }
        if ($emailToClean) {
            AbandonedCart::where('email', $emailToClean)->delete();
        }

        return view('thanks')
            ->with([
                'last_order' => $last_order,
                'contry_mult' => $contry_mult ?? 1,
                'user_phone' => $user_phone,
                'user_addr' => $user_addr,
                'order_date' => $order_date,
                'data' => $data,
            ]);

    }

    public function addToBasket()
    {
        Log::info('BasketController@addToBasket: начало вызова', [
            'user_id' => auth()->id(),
            'request' => request()->all(),
        ]);

        try {
            $basketCount = $this->basketRepository->addToBasket();

            Log::info('Controller@addToBasket: успешно добавлено в корзину', [
                'user_id'      => auth()->id(),
                'new_count'    => $basketCount,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('basket.succ_add'),
                'count'   => $basketCount,
            ]);

        } catch (\Throwable $e) {
            Log::error('Controller@addToBasket: ошибка при добавлении в корзину', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function removeFromBasket(RemoveBasketItemRequest $request)
    {
        $response = [];
        $item_id = $request['basketId'];
        $basket = $this->basketRepository->normalizeBasket(request()->session()->get('basket'));
        request()->session()->put('basket', $basket);

        $itemToRemove = $basket[$item_id] ?? null;
        if ($itemToRemove && is_array($itemToRemove)) {
            $isRecommendedItem = isset($itemToRemove['is_recommendation']) && $itemToRemove['is_recommendation'];

            if (!$isRecommendedItem) {
                $hasRecommendationInBasket = false;
                foreach ($basket as $index => $product) {
                    if ($index != $item_id && is_array($product) && !empty($product['is_recommendation'])) {
                        $hasRecommendationInBasket = true;
                        break;
                    }
                }

                if ($hasRecommendationInBasket) {
                    $response['success'] = 0;
                    $response['error'] = trans('cart_new.cannot_remove_main_product');
                    return json_encode($response);
                }
            }
        }

        foreach ($basket as $index => $product) {
            if ($index == $item_id) {
                unset($basket[$index]);
            }
        }

        session(['basket' => $basket]);
        $this->basketRepository->saveBasketToAbandonedCartModel($basket);
        $response['success'] = 1;

        return json_encode($response);
    }

    public function updateCount(UpdateBasketCountRequest $request)
    {
        $req['count'] = intval($request->input('count'));
        $req['index'] = intval($request->input('index'));
        $basket = $this->basketRepository->normalizeBasket(request()->session()->get('basket'));
        request()->session()->put('basket', $basket);

        foreach ($basket as $index => $product) {
            if ($index == $req['index']) {
                if (!is_array($product)) {
                    return response()->json(['success' => 0], 422);
                }
                $basket[$index]['count'] = $req['count'];
                $response['success'] = 1;
                $response['price'] = ($basket[$index]['price'] * $req['count']) . "€";

                if(isset($basket[$index]['terms_price']))
                {
                    $basket[$index]['total_item_price'] = ($basket[$index]['count'] * $basket[$index]['price']) + (float)$basket[$index]['terms_price'];
                }
                else
                {
                    $basket[$index]['total_item_price'] = $basket[$index]['count'] * $basket[$index]['price'];
                }

                session(['basket' => $basket]);
                $this->basketRepository->saveBasketToAbandonedCartModel($basket);

                return json_encode($response);
            }
        }
        return response()->json(['success' => 0], 422);
    }


    public function replaceItemSize(Request $request)
    {
        try {
            $basketKey = $request->input('basket_key');
            $itemId = $request->input('item_id');
            $newSize = $request->input('new_size');

            if ($basketKey === null || $basketKey === '' || !$newSize) {
                return response()->json([
                    'success' => false,
                    'message' => trans('cart_new.invalid_parameters')
                ]);
            }

            $basket = $this->basketRepository->normalizeBasket(request()->session()->get('basket'));
            request()->session()->put('basket', $basket);

            if (!isset($basket[$basketKey])) {
                return response()->json([
                    'success' => false,
                    'message' => trans('cart_new.item_not_found')
                ]);
            }

            $basketItem = $basket[$basketKey];
            if (!is_array($basketItem)) {
                return response()->json([
                    'success' => false,
                    'message' => trans('cart_new.item_not_found')
                ]);
            }
            $basketType = $basketItem['basketType'] ?? null;
            $isCanvasInter = isset($basketItem['is_canvas_inter']);
            $isCanvasCollage = isset($basketItem['is_canvas_collage']);
            $hasPid = isset($basketItem['pid']) && $basketItem['pid'];

            $newPrice = null;

            if ($isCanvasInter || $isCanvasCollage || ($basketType == '1' && !$hasPid)) {
                $newPrice = $this->getCanvasSizePrice($newSize);
            } elseif ($hasPid) {
                $item = GalleryItem::find($basketItem['pid']);
                if ($item) {
                    $newPrice = $this->getGalleryItemSizePrice($item, $newSize);
                }
            }

            if ($newPrice === null) {
                return response()->json([
                    'success' => false,
                    'message' => trans('cart_new.size_not_found')
                ]);
            }

            $cleanSize = preg_replace('/[htsr]$/i', '', $newSize);
            $basket[$basketKey]['size'] = $cleanSize;

            if (isset($basketItem['sizeId'])) {
                $basket[$basketKey]['sizeId'] = $cleanSize;
                unset($basket[$basketKey]['size_name']);
            } else {
                $basket[$basketKey]['size_name'] = $cleanSize;
                unset($basket[$basketKey]['sizeId']);
            }
            $basket[$basketKey]['price'] = $newPrice;
            $basket[$basketKey]['has_special_label'] = hasSpecialLabel($newSize);

            if ($basket[$basketKey]['has_special_label']) {
                $basket[$basketKey]['label_type'] = getLabelType($newSize);
            } else {
                unset($basket[$basketKey]['label_type']);
            }

            if (isset($basket[$basketKey]['terms_price'])) {
                $basket[$basketKey]['total_item_price'] = ($basket[$basketKey]['count'] * $newPrice) + (float)$basket[$basketKey]['terms_price'];
            } else {
                $basket[$basketKey]['total_item_price'] = $basket[$basketKey]['count'] * $newPrice;
            }

            session(['basket' => $basket]);
            $this->basketRepository->saveBasketToAbandonedCartModel($basket);

            return response()->json([
                'success' => true,
                'message' => trans('cart_new.size_replaced_successfully')
            ]);

        } catch (Exception $e) {
            \Log::error('Error replacing item size: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => trans('cart_new.error_replacing_size')
            ]);
        }
    }

    public function addRecommendedItem(Request $request)
    {
        try {
            $itemId = $request->input('item_id');
            $discountedPrice = $request->input('price');

            if (!$itemId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('cart_new.item_not_found')
                ]);
            }

            $item = GalleryItem::find($itemId);
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => trans('cart_new.item_not_found')
                ]);
            }

            $itemType = GalleryType::find($item->id_type);

            $images = json_decode($item->image, true);
            $imageUrl = is_array($images) && !empty($images) ? $images[0] : $item->image;

            $firstSize = '30x40';
            if ($item->custom_size_prices) {
                $sizesArray = explode(',', $item->custom_size_prices);
                if (!empty($sizesArray[0])) {
                    preg_match('/^(.+?)\[/', trim($sizesArray[0]), $matches);
                    if ($matches) {
                        $firstSize = $matches[1];
                    }
                }
            }

            $basket = session('basket', []);

            $basketItem = [
                'pid' => $item->id,
                'name' => $item->name,
                'size' => $firstSize,
                'size_name' => $firstSize,
                'price' => (float)$discountedPrice,
                'count' => 1,
                'total_item_price' => (float)$discountedPrice,
                'activeImage' => 'storage/' . $imageUrl,
                'savedImage' => 'storage/' . $imageUrl,
                'type' => $itemType ? $itemType->url : 'canvas',
                'basketType' => '1',
                'is_def_product' => true,
                'is_port_product' => true,
                'is_recommendation' => true,
                'discount_percent' => 30,
                'has_special_label' => true,
                'label_type' => 'recommendation',
                'terms' => '',
                'terms_price' => 0,
            ];

            $basket[] = $basketItem;

            session(['basket' => $basket]);
            $this->basketRepository->saveBasketToAbandonedCartModel($basket);

            return response()->json([
                'success' => true,
                'message' => trans('cart_new.item_added_successfully')
            ]);

        } catch (Exception $e) {
            \Log::error('Error adding recommended item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => trans('cart_new.error_adding')
            ]);
        }
    }


    private function getCanvasSizePrice($size)
    {
        $sizes_30x40 = \DB::table('canvas_header')->pluck('sizes_30x40')->first();

        if (!$sizes_30x40) {
            return null;
        }

        $sizesArray = explode(',', $sizes_30x40);
        foreach ($sizesArray as $sizeWithPrice) {
            if (empty($sizeWithPrice)) continue;

            preg_match('/^(.+?)\[(\d+(?:\.\d+)?(?:-\d+(?:\.\d+)?)?)\]([a-zA-Z]*)$/', trim($sizeWithPrice), $matches);

            if ($matches) {
                $sizeStr = $matches[1] . $matches[3];
                if ($sizeStr === $size) {
                    $price = explode('-', $matches[2]);
                    return count($price) > 1 ? (float)$price[1] : (float)$price[0];
                }
            }
        }

        return null;
    }


    private function getGalleryItemSizePrice($item, $size)
    {
        if ($item->custom_size_prices) {
            $sizesArray = explode(',', $item->custom_size_prices);
            foreach ($sizesArray as $sizeWithPrice) {
                if (empty($sizeWithPrice)) continue;

                preg_match('/^(.+?)\[(\d+(?:\.\d+)?(?:-\d+(?:\.\d+)?)?)\]([a-zA-Z]*)$/', trim($sizeWithPrice), $matches);

                if ($matches) {
                    $sizeStr = $matches[1] . $matches[3];
                    $priceArr = explode('-', $matches[2]);
                    $discountedPrice = count($priceArr) > 1 ? (float)$priceArr[1] : (float)$priceArr[0];

                    if ($sizeStr === $size) {
                        return $discountedPrice;
                    }
                }
            }
        }

        if ($item->sizes_cals) {
            $sizesCals = json_decode($item->sizes_cals, true);
            if (is_array($sizesCals)) {
                foreach ($sizesCals as $sizeData) {
                    if (isset($sizeData['size']) && $sizeData['size'] === $size) {
                        return (float)$sizeData['price'];
                    }
                }
            }
        }

        return null;
    }

    public function addToBasketArt(Request $request)
    {
        $files = $request->images;

        $validator = Validator::make($request->all(), [
            'images' => 'max:15',
            'images.*' => 'image|mimes:png,bmp,jpg,jpeg,heic,heif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid filesize or extension.',
                'errors' => $validator,
            ]);
        }

        if ($files && !empty($files)) {
            $data['name'] = $request->input('name');
            $data['tel'] = $request->input('tel');
            $data['email'] = $request->input('email');
            $data['img_links'] = [];

            $i = -1;
            foreach ($files as $file) {
                $i++;
                $file_name = Storage::disk('uploads')->put('uploads', $file);
                $data['img_links'][$i] = URL::to('/') . '/' . $file_name;
            }

            $admin_data = GlobConfig::first()->get()->translate(App::getLocale(), 'ru')[0];
            $admin_data_mail = $admin_data['admin_email'];
            $data['to'] = $admin_data_mail;

            $data['subject'] = trans('gl.z_mak_subj');
            $data['content'] = trans('gl.z_mak_name') . ' ' . $data['name'] . '<br>';
            $data['content'] .= trans('gl.z_mak_tel') . " {$data['tel']}<br>";
            $data['content'] .= "<p>Email: {$data['email']}:</p><br>";
            $data['content'] .= "<p>" . trans('gl.z_mak_imgs') . "</p><br>";

            $i = 0;
            foreach ($data['img_links'] as $link) {
                $i++;
                $data['content'] .= "<a href='{$link}'>#{$i}</a> ";
            }

            OrderAction::create([
                'user' => $data['name'],
                'activity' => "Отправил форму 'получить макет сегодня':<br> {$data['content']}",
            ]);

            try {
                Mail::send([], [], function ($message) use ($data) {
                    $message->to($data['to']);
                    $message->subject($data['subject']);
                    $message->setBody($data['content'], 'text/html');
                });
            } catch (Throwable $e) {
                return response()->json([
                    'error' => $e,
                ]);
            }

            // send user notify
            $data['content'] = trans('gl.z_mak_usr_thanks');

            try {
                Mail::send([], [], function ($message) use ($data) {
                    $message->to($data['email']);
                    $message->subject($data['subject']);
                    $message->setBody($data['content'], 'text/html');
                });
            } catch (Throwable $e) {
                return response()->json([
                    'error' => $e,
                ]);
            }

            return response()->json([
                'message' => 'Success',
            ]);
        } else {
            return response()->json([
                'message' => 'Media missing.',
            ]);
        }
    }

    public function addToBasketInterier(Request $request)
    {
        $img_base = $request->input('image');

        if (strpos($img_base, 'image/png') !== false) {
            $img = str_replace('data:image/png;base64,', '', $img_base);
            $ext = '.png';
        } else {
            $img = str_replace('data:image/jpeg;base64,', '', $img_base);
            $ext = '.jpeg';
        }

        $img = str_replace(' ', '+', $img);
        $img_data = base64_decode($img);
        $rand_name = Str::random(12);
        file_put_contents(public_path() . '/uploads/' . $rand_name . $ext, $img_data);

        if ($request->has('base_64_img')) {
            $img_base_2 = $request->input('base_64_img');
            if (strpos($img_base_2, 'image/png') !== false) {
                $img = str_replace('data:image/png;base64,', '', $img_base_2);
                $ext = '.png';
            } else {
                $img = str_replace('data:image/jpeg;base64,', '', $img_base_2);
                $ext = '.jpeg';
            }

            $img = str_replace(' ', '+', $img);
            $img_data = base64_decode($img);
            $rand_name2 = Str::random(12);
            file_put_contents(public_path() . '/uploads/' . $rand_name2 . $ext, $img_data);
        }

        $data = [];
        if (request()->hasFile('photo_ex')) {
            $photo_ex = request()->file('photo_ex');
            $photo_ex_file_name = Storage::disk('uploads')->put('uploads', $photo_ex);
            $photo_ex = URL::to('/') . '/' . $photo_ex_file_name;
        } else {
            $photo_ex = '';
        }

        $data['photo_ex'] = $photo_ex;
        $data['name'] = $request->input('name');
        $data['is_canvas_inter'] = '1';
        $data['basketType'] = '1';
        $data['add_price'] = $request->input('price');
        $data['formId'] = $request->input('formId');
        $data['sizeId'] = $request->input('sizeId');
        $data['execution'] = $request->input('execution');
        $data['pack'] = $request->input('pack');
        $data['canvasId'] = $request->input('canvasId');
        $data['decorationId'] = $request->input('decorationId');
        $data['executionId'] = $request->input('executionId');
        $data['canvas'] = $request->input('canvas');
        $data['canvasId'] = $request->input('canvasId');
        $data['userComment'] = $request->input('userComment');
        $data['_url'] = 'inter';
        $data['boxIds'] = $request->input('boxIds');
        $data['activeImage'] = '/uploads/' . $rand_name . $ext;
        $data['savedImage'] = '/uploads/' . $rand_name2 . $ext;
        $data['count'] = 1;
        $data['terms'] = $request->input('terms');
        $data['terms_price'] = $request->input('terms_price');
        $data['ram_id'] = $request->input('ram_id');
        $data['wall_size'] = $request->input('w1_size') . 'x' . $request->input('w2_size');
        $data['pic_size'] = $request->input('c1_size') . 'x' . $request->input('c2_size');

        if ($request->has('is_canvas_collage')) {
            $data['is_canvas_collage'] = 1;
        }

        // Проверка на спеціальні позначки (HIT, TOP, SUPER DEAL)
        if (isset($data['sizeId'])) {
            $data['has_special_label'] = hasSpecialLabel($data['sizeId']);
            $data['label_type'] = getLabelType($data['sizeId']);
        } else {
            $data['has_special_label'] = false;
            $data['label_type'] = null;
        }

        if (isset($data['pid']) && request()->session()->has('recommendation_discount_' . $data['pid'])) {
            $recommendationDiscount = /*request()->session()->get('recommendation_discount_' . $data['pid'])*/30;
            $data['original_price'] = $data['add_price'];
            $data['add_price'] = round($data['add_price'] * (1 - $recommendationDiscount / 100), 2);
            $data['recommendation_discount'] = $recommendationDiscount;
            $data['is_recommendation'] = true;
            request()->session()->forget('recommendation_discount_' . $data['pid']);
        }

        $basket = $this->basketRepository->normalizeBasket(request()->session()->get('basket'));
        request()->session()->put('basket', $basket);
        request()->session()->push('basket', $data);
        $this->basketRepository->saveBasketToAbandonedCartModel($data, true);

        $response['count'] = 1;
        $response['success'] = __('basket.succ_add');

        return json_encode($response);
    }

    public function addToBasketModule(Request $request)
    {
        $img_base = $request->input('image');

        if (strpos($img_base, 'image/png') !== false) {
            $img = str_replace('data:image/png;base64,', '', $img_base);

            $ext = '.png';
        } else {
            $img = str_replace('data:image/jpeg;base64,', '', $img_base);

            $ext = '.jpeg';
        }

        $img = str_replace(' ', '+', $img);
        $img_data = base64_decode($img);
        $rand_name = Str::random(12);

        file_put_contents(public_path() . '/uploads/' . $rand_name . $ext, $img_data);
        $data = [];

        if (request()->hasFile('photo_ex')) {
            $photo_ex = request()->file('photo_ex');
            $photo_ex_file_name = Storage::disk('uploads')->put('uploads', $photo_ex);
            $photo_ex = URL::to('/') . '/' . $photo_ex_file_name;
        } else {
            $photo_ex = '';
        }

        $data['photo_ex'] = $photo_ex;
        $data['is_modular_inter'] = '1';
        $data['name'] = $request->input('name');
        $data['basketType'] = '2';
        $data['size'] = $request->input('size');
        $data['executionId'] = $request->input('executionId');
        $data['canvasId'] = $request->input('canvasId');
        $data['decorationId'] = $request->input('decorationId');
        $data['boxIds'] = $request->input('boxIds');
        $data['userComment'] = $request->input('userComment');
        $data['add_to_price'] = $request->input('add_to_price');
        $data['executionId'] = $request->input('executionId');
        $data['canvasId'] = $request->input('canvasId');
        $data['formId'] = $request->input('formId');
        $data['_url'] = 'modular_inter';
        $data['terms'] = $request->input('terms');
        $data['terms_price'] = $request->input('terms_price');
        $data['activeImage'] = '/uploads/' . $rand_name . $ext;
        $data['count'] = 1;
        $data['ram_id'] = $request->input('ram_id');
        $data['wall_size'] = $request->input('wall_size') . 'x' . $request->input('wall_size');

        if ($request->has('is_canvas_collage')) {
            $data['is_canvas_collage'] = 1;
        }

        // Проверка на спеціальні позначки (HIT, TOP, SUPER DEAL)
        if (isset($data['size'])) {
            $data['has_special_label'] = hasSpecialLabel($data['size']);
            $data['label_type'] = getLabelType($data['size']);
        } else {
            $data['has_special_label'] = false;
            $data['label_type'] = null;
        }

        if (isset($data['pid']) && request()->session()->has('recommendation_discount_' . $data['pid'])) {
            $recommendationDiscount = 30;
            $data['original_price'] = $data['add_to_price'];
            $data['add_to_price'] = round($data['add_to_price'] * (1 - $recommendationDiscount / 100), 2);
            $data['recommendation_discount'] = $recommendationDiscount;
            $data['is_recommendation'] = true;
            request()->session()->forget('recommendation_discount_' . $data['pid']);
        }

        $basket = $this->basketRepository->normalizeBasket(request()->session()->get('basket'));
        request()->session()->put('basket', $basket);
        request()->session()->push('basket', $data);
        $this->basketRepository->saveBasketToAbandonedCartModel($data, true);
        $response['count'] = 1;
        $response['success'] = __('basket.succ_add');

        return json_encode($response);
    }

    public function addToBasketConstruct(Request $request)
    {
        ini_set('memory_limit', '512M');

        if ($request->has('image_offset')) {
            $img_name = $this->save_base64_image($request->input('image_offset'));

            $data['image_uploads'] = 1;

            $data['activeImage'] = $img_name;
        } elseif (!$request->has('is_orig_file')) {
            $img = $request->input('image');
            $img_name = $this->save_base64_image($request->input('image_offset'));
            $data['activeImage'] = $img_name;
        } else {
            // modular-pictures
            $yfn = md5(date('Y-m-d H:i:s:u'));
            $filename = $request->image->getClientOriginalName();
            $data['orig_file_name'] = $filename;
            $filename = preg_replace("/\s+/", '', $filename);
            $data['file_hash'] = $request->input('collageSvgImage_hash');
            $img_mova = $request->image->move(public_path('uploads'), $data['file_hash'] . $filename);
            $screen_url = URL::to('/') . '/uploads/' . $data['file_hash'] . $filename;
            $data['activeImage'] = $screen_url;

            if ($request->has('collageSvgImage')) {
                $svg = $request->input('collageSvgImage');
                $data['collageSvgImage'] = 1;
                $f = ['jpg', 'png', 'jpeg', 'heic', 'heif'];
                $r = ['svg', 'svg', 'svg', 'svg', 'svg'];
                $fn = str_replace($f, $r, $filename);
                file_put_contents(public_path() . '/uploads/' . $data['file_hash'] . $fn, $svg);
            }
        }

        if (request()->hasFile('photo_ex')) {
            $photo_ex = request()->file('photo_ex');
            $photo_ex_file_name = Storage::disk('uploads')->put('uploads', $photo_ex);
            $photo_ex = URL::to('/') . '/' . $photo_ex_file_name;
        } else {
            $photo_ex = '';
        }

        $files = $request->fon;

        if ($files && !empty($files)) {
            $data['fon_images'] = [];
            $i = -1;
            foreach ($files as $file) {
                $i++;
                $file_name = Storage::disk('uploads')->put('uploads', $file);
                $data['fon_images'][$i] = URL::to('/') . '/' . $file_name;
            }
        }

        $data['photo_ex'] = $photo_ex;
        $data['name'] = $request->input('name');
        $data['price'] = $request->input('price');
        $data['count'] = 1;
        $data['pid'] = $request->input('pid');
        $data['size_name'] = $request->input('size');

        if ($request->has(['ram_id'])) {
            $data['ram_id'] = $request->input('ram_id');
        }

        $data['userComment'] = $request->input('userComment');
        $data['terms'] = $request->input('dost_time');
        $data['terms_price'] = $request->input('terms_price');
        $data['holst_id'] = $request->input('holst_id');
        $data['formId'] = $request->input('formId');
        $data['type'] = $request->input('type');
        $data['hud_of'] = $request->input('hud_of');
        $data['pack'] = $request->input('pack');
        $packagingId = $request->input('boxIds', $request->input('compl_id'));
        $data['boxIds'] = $packagingId;
        $data['compl_id'] = $packagingId;
        $data['wall_size_mod'] = $request->input('wall_size_mod');
        $data['basketType'] = '1';
        $data['is_port_product'] = '1';
        $data['is_def_product'] = '1';
        $data['is_construct'] = '1';

        if((float)$request->input('terms_price'))
        {
            $data['price'] = (float)$request->input('price') - (float)$request->input('terms_price');
        }
        else
        {
            $data['price'] = $request->input('price');
        }

        if ($request->has('is_canvas_collage')) {
            $data['is_canvas_collage'] = 1;
            $data['sizeId'] = $request->input('sizeId');
        }

        // Проверка на спеціальні позначки (HIT, TOP, SUPER DEAL)
        if (isset($data['size_name'])) {
            $data['has_special_label'] = hasSpecialLabel($data['size_name']);
            $data['label_type'] = getLabelType($data['size_name']);
        } else {
            $data['has_special_label'] = false;
            $data['label_type'] = null;
        }

        if (isset($data['pid']) && request()->session()->has('recommendation_discount_' . $data['pid'])) {
            $recommendationDiscount = 30/*request()->session()->get('recommendation_discount_' . $data['pid'])*/;
            $data['original_price'] = $data['price'];
            $data['price'] = round($data['price'] * (1 - $recommendationDiscount / 100), 2);
            $data['recommendation_discount'] = $recommendationDiscount;
            $data['is_recommendation'] = true;
            request()->session()->forget('recommendation_discount_' . $data['pid']);
        }

        $validator = Validator::make($request->all(), [
            'orig_images.*' => 'image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid filesize or extension.',
                'errors' => $validator,
            ]);
        }

        $files = $request->orig_images;

        if ($files && !empty($files)) {
            $data['orig_images'] = [];
            $i = -1;
            foreach ($files as $file) {
                $i++;
                $file_name = Storage::disk('uploads')->put('uploads', $file);
                $data['orig_images'][$i] = URL::to('/') . '/' . $file_name;
            }
        }

        $basket = $this->basketRepository->normalizeBasket(request()->session()->get('basket'));
        request()->session()->put('basket', $basket);
        request()->session()->push('basket', $data);
        $this->basketRepository->saveBasketToAbandonedCartModel($data, true);
        $response['count'] = 1;
        $response['success'] = __('basket.succ_add');

        return json_encode($response);
    }




    private function save_base64_image($image)
    {
        $path = public_path() . '/uploads';

        if (!is_dir($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        $rand_name = Str::random(12);
        $image = str_replace(' ', '+', $image);

        if (strpos($image, 'jpeg;') !== false) {
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $imageName = $rand_name . '.' . 'jpeg';
        } else {
            $image = str_replace('data:image/png;base64,', '', $image);
            $imageName = $rand_name . '.' . 'png';
        }

        file_put_contents(public_path() . '/uploads/' . $imageName, base64_decode($image));

        return '/uploads/' . $imageName;
    }

    public function addToBasketPortrait(Request $request)
    {
        $data = [];
        $data['is_port_product'] = 1;
        $data['is_gall_with_img'] = 0;
        $data['pid'] = $request->input('pid');

        if ($request->input('is_gall_with_img') == 1) {
            $data['is_gall_with_img'] = 1;
            $img_base = $request->input('image');

            if (strpos($img_base, 'image/png') !== false) {
                $img_n = str_replace('data:image/png;base64,', '', $img_base);
                $ext = '.png';
            } else {
                $img_n = str_replace('data:image/jpeg;base64,', '', $img_base);
                $ext = '.jpeg';
            }

            $img = str_replace(' ', '+', $img_n);
            $img_data = base64_decode($img);
            $rand_name = Str::random(12);
            file_put_contents(public_path() . '/uploads/' . $rand_name . $ext, $img_data);

            $data['activeImage'] = '/uploads/' . $rand_name . $ext;
        } elseif ($data['pid'] != 'undefined' && $data['pid'] != '') {
            if ($request->input('is_uploaded_img')) {
                $data['is_gall_with_img'] = 1;
                $img_base = $request->input('image');
                if ($request->has('is_png_base')) {
                    $ext = '.png';
                } else {
                    $ext = '.jpeg';
                }

                $img = str_replace(' ', '+', $img_base);
                $img_data = base64_decode($img);
                $rand_name = Str::random(12);
                file_put_contents(public_path() . '/uploads/' . $rand_name . $ext, $img_data);
                $data['activeImage'] = '/uploads/' . $rand_name . $ext;
            } else {
                $images = DB::table('gallery_items')->where('id', $data['pid'])->pluck('images')->first();

                if ($images) {
                    $data['activeImage'] = json_decode($images)[0];
                }
            }
        } else {
            // is oil portrait
            $img_base = $request->input('image');

            if (strpos($img_base, 'image/png') !== false) {
                $img = str_replace('data:image/png;base64,', '', $img_base);
                $ext = '.png';
            } else {
                $img = str_replace('data:image/jpeg;base64,', '', $img_base);
                $ext = '.jpeg';
            }

            $img = str_replace(' ', '+', $img);
            $img_data = base64_decode($img);
            $rand_name = Str::random(12);
            file_put_contents(public_path() . '/uploads/' . $rand_name . $ext, $img_data);
            $data['activeImage'] = '/uploads/' . $rand_name . $ext;
            $data['is_oil_portrait'] = 1;
        }

        $files = $request->orig_images;
        if ($files) {
            $data['orig_images'] = [];
            $i = -1;

            foreach ($files as $file) {
                $i++;
                $file_name = Storage::disk('uploads')->put('uploads', $file);
                $data['orig_images'][$i] = URL::to('/') . '/' . $file_name;
            }
        }

        if (request()->hasFile('photo_ex')) {
            $photo_ex = request()->file('photo_ex');
            $photo_ex_file_name = Storage::disk('uploads')->put('uploads', $photo_ex);
            $photo_ex = URL::to('/') . '/' . $photo_ex_file_name;
        } else {
            $photo_ex = '';
        }

        $data['photo_ex'] = $photo_ex;
        $data['name'] = $request->input('name');

        if((float)$request->input('terms_price'))
        {
            $data['price'] = (float)$request->input('price') - (float)$request->input('terms_price');
        }
        else
        {
            $data['price'] = $request->input('price');
        }

        $data['is_def_product'] = 1;
        $data['count'] = 1;
        $data['pack'] = $request->input('pack');
        $data['terms'] = $request->input('dost_time');
        $data['forma_id'] = $request->input('forma_id');
        $data['size_name'] = $request->input('size'); // 30x40 etc
        $data['users_count'] = $request->input('users_count');
        $data['type'] = $request->input('type');
        $data['holst_id'] = $request->input('holst_id');
        $data['hud_of'] = $request->input('hud_of');
        $data['decor_id'] = $request->input('decor_id');
        $data['ram_id'] = $request->input('ram_id');
        $data['compl_id'] = $request->input('compl_id');
        $data['userComment'] = $request->input('userComment');
        $data['terms'] = $request->input('terms');
        $data['terms_price'] = (float)$request->input('terms_price');
        $data['basket_type'] = '1';

        if ($request->has('is_canvas_collage')) {
            $data['is_canvas_collage'] = 1;
        }

        if ($request->has('fon')) {
            $data['fon'] = $request->input('fon');
        }
        if ($request->has('obraz')) {
            $data['obraz'] = $request->input('obraz');
        }

        if ($request->has('obraz_title')) {
            $data['obraz_title'] = $request->input('obraz_title');
        }

        if ($request->has('obraz_img')) {
            $data['obraz_img'] = $request->input('obraz_img');
        }

        // Зберігаємо позначку окремо якщо передана
        if ($request->has('priceLabel') && $request->input('priceLabel')) {
            $data['price_label'] = strtolower($request->input('priceLabel'));
        }

        // Проверка на спеціальні позначки (HIT, TOP, SUPER DEAL)
        if (isset($data['price_label']) && $data['price_label']) {
            // Якщо є окрема позначка - використовуємо її
            $data['has_special_label'] = in_array($data['price_label'], ['h', 's', 't']);
            switch ($data['price_label']) {
                case 'h':
                    $data['label_type'] = 'hit';
                    break;
                case 's':
                    $data['label_type'] = 'super_deal';
                    break;
                case 't':
                    $data['label_type'] = 'top';
                    break;
                default:
                    $data['label_type'] = null;
            }
        } elseif ($request->has('full_size') && $request->input('full_size')) {
            $data['has_special_label'] = hasSpecialLabel($request->input('full_size'));
            $data['label_type'] = getLabelType($request->input('full_size'));
        } elseif (isset($data['size_name'])) {
            // Якщо немає окремої позначки - перевіряємо розмір (backwards compatibility)
            $data['has_special_label'] = hasSpecialLabel($data['size_name']);
            $data['label_type'] = getLabelType($data['size_name']);
        } else {
            $data['has_special_label'] = false;
            $data['label_type'] = null;
        }

        if (isset($data['pid']) && request()->session()->has('recommendation_discount_' . $data['pid'])) {
            $recommendationDiscount = 30/*request()->session()->get('recommendation_discount_' . $data['pid'])*/;
            $data['original_price'] = $data['price'];
            $data['price'] = round($data['price'] * (1 - $recommendationDiscount / 100), 2);
            $data['recommendation_discount'] = $recommendationDiscount;
            $data['is_recommendation'] = true;
            request()->session()->forget('recommendation_discount_' . $data['pid']);
        }

        $basket = $this->basketRepository->normalizeBasket(request()->session()->get('basket'));
        request()->session()->put('basket', $basket);
        request()->session()->push('basket', $data);
        $this->basketRepository->saveBasketToAbandonedCartModel($data, true);

        $response['count'] = 1;
        $response['success'] = __('basket.succ_add');

        return json_encode($response);
    }

    public function send_gift_card(Request $request)
    {
        $data = [];
        $data['pid'] = 5; //gift card
        $data['price'] = (int) $request->summ;
        $data['basketType'] = '5';
        $data['count'] = 1;
        $data['terms'] = 0;
        $data['terms_price'] = 0;
        $data['name'] = __("gift_card.gift_cart");
        $data['whom'] =  $request->whom;
        $data['card_type'] = $request->card_type;

        $basket = $this->basketRepository->normalizeBasket(request()->session()->get('basket'));
        request()->session()->put('basket', $basket);
        request()->session()->push('basket', $data);
        $this->basketRepository->saveBasketToAbandonedCartModel($data, true);
        $response['count'] = 1;
        $response['success'] = __('basket.succ_add');

        return json_encode($response);
    }


    public function send_gift_card_old(Request $request)
    {
        dd($request);
        exit;
        $request->validate([
            'name' => 'required',
            'sender' => 'required',
            'res' => 'required',
            'torjname' => 'required',
            'torjtext' => 'required',
            'summ' => 'required|integer',
            'date' => 'nullable|date',
        ]);

        $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';

        $coupon_code = mb_substr(str_shuffle($rand_code), 0, 10);
        DB::table('coupons')->insert([
            'text' => $coupon_code,
            'value' => $request->summ,
        ]);

        $data = [];
        $data['is_gift_card'] = 1;
        $data['name'] = $request->name;
        $data['gift_code'] = $coupon_code;
        $data['code'] = $request->name;
        $data['sender'] = $request->sender;
        $data['reseiver'] = $request->res;
        $data['price'] = (int) $request->summ;
        $data['count'] = 1;
        $data['date'] = $request->date;
        $data['torjname'] = $request->torjname;
        $data['torjtext'] = $request->torjtext;
        $data['card_type'] = $request->card_type;
        $data['basketType'] = 'GiftCard';
        $data['hide_nom'] = $request->hide_nom;

        $basket = $this->basketRepository->normalizeBasket(request()->session()->get('basket'));
        request()->session()->put('basket', $basket);
        request()->session()->push('basket', $data);
        $this->basketRepository->saveBasketToAbandonedCartModel($data, true);
        $response['count'] = 1;
        $response['success'] = __('basket.succ_add');

        return json_encode($response);
    }

    public function addRecommendedToBasket(Request $request)
    {
        try {
            $itemId = $request->input('item_id');

            $item = GalleryItem::find($itemId);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => __('basket.item_not_found'),
                ], 404);
            }

            $sessionKey = 'recommendation_discount_' . $itemId;
            $recommendationDiscount = $request->session()->get($sessionKey);


            if (!$recommendationDiscount) {
                return response()->json([
                    'success' => false,
                    'message' => trans('cart_new.discount_not_found'),
                ], 400);
            }
            $recommendationDiscount = 30;

            $originalPrice = $request->input('price') ? floatval($request->input('price')) : ($item->price_from ?? 0);

            $discountedPrice = round($originalPrice/* * (1 - $recommendationDiscount / 100)*/, 2);


            $basketItem = [
                'pid' => $item->id,
                'name' => $item->name,
                'price' => $discountedPrice,
                'original_price' => floatval($originalPrice),
                'count' => 1,
                'sumPrice' => $discountedPrice,
                'activeImage' => $item->images,
                'savedImage' => $item->images,
                'orig_images' => [$item->images],
                'basketType' => '1',
                'is_def_product' => 1,
                'is_recommendation' => true,
                'recommendation_discount' => $recommendationDiscount,
                'recommendation_original_price' => floatval($originalPrice),
                'has_special_label' => true,
                'label_type' => 'recommendation',
                'decor_id' => 0,
                'formId' => 0,
                'sizeId' => 0,
                'canvasId' => 0,
                'executionId' => 0,
                'decorationId' => 0,
                'boxIds' => [],
                'terms' => '',
                'terms_price' => 0,
                'pack' => '',
                'type' => '',
                'userComment' => '',
            ];


            $basket = $request->session()->get('basket', []);
            $basket[] = $basketItem;
            $request->session()->put('basket', $basket);

            $request->session()->forget($sessionKey);


            $this->basketRepository->saveBasketToAbandonedCartModel($basket);

            return response()->json([
                'success' => true,
                'message' => __('basket.succ_add'),
                'count' => count($basket),
            ]);

        } catch (Exception $e) {
            \Log::error('❌ BasketController@addRecommendedToBasket:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('basket.error_add'),
            ], 500);
        }
    }

    public function addCanvasRecommendation(Request $request)
    {
        try {
            $size = $request->input('size');
            $full_size = $request->input('full_size');
            $price = $request->input('price');

            if (!$size || !$price) {
                return response()->json([
                    'success' => false,
                    'message' => __('cart_new.missing_data'),
                ], 400);
            }

            if (!$request->hasFile('userImage')) {
                return response()->json([
                    'success' => false,
                    'message' => __('gl.error_image_canvas'),
                ], 400);
            }

            $savedImagePath = \Storage::disk('uploads')->putFile('uploads', $request->file('userImage'));
            $savedImage = \URL::to('/') . '/' . $savedImagePath;

            $lang = app()->getLocale();
            $AProductionTime = AProductionTime::where('category', 'canvas')->first()->translate($lang, 'ru');

            $basket = $request->session()->get('basket', []);

            $hasSpecialLabel = hasSpecialLabel($full_size);
            $labelType = getLabelType($full_size);

            $basketItem = [
                'name'                     => 'Canvas',
                'basketType'               => '1',
                'sizeId'                   => $size,
                'price'                    => (float)$price,
                'savedImage'               => $savedImage,
                'activeImage'              => $savedImage,
                'formId'                   => "1",
                'canvasId'                 => "2",
                'executionId'              => "2",
                'boxIds'                   => [3],
                'terms'                    => $AProductionTime->standart_text . ' ' . $AProductionTime->standart_price . ' €',
                'terms_price'              => $AProductionTime->standart_price,
                'pack'                     => '',
                'type'                     => '',
                'userComment'              => '',
                'count'                    => 1,
                'is_canvas_recommendation' => true,
                'improve_photo'            => null,
                'photo_ex'                 => '',
                'is_canvas_collage'        => 1,
                'is_with_orig_image'       => "1",
                "pid"                      => "1",
                'has_special_label'        => $hasSpecialLabel,
                'label_type'               => $labelType,
            ];

            $basket[] = $basketItem;
            $request->session()->put('basket', $basket);

            $this->basketRepository->saveBasketToAbandonedCartModel($basket);

            return response()->json([
                'success' => true,
                'message' => __('basket.succ_add'),
                'count' => count($basket),
            ]);

        } catch (Exception $e) {
            \Log::error('❌ BasketController@addCanvasRecommendation:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('basket.error_add'),
            ], 500);
        }
    }
}
