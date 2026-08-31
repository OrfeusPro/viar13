<?php

namespace App\Models;

use Str;
use URL;
use Auth;
use File;
use Hash;
use Session;
use Storage;
use Eloquent;
use Validator;
use App\Mail\SendUserRegister;
use App\Models\OrderUserImages;
use App\Models\OrderUserComments;
use App\Models\OrderPainterImages;
use App\Services\SynvolveWebhookService;
use App\Services\BestEffortMailService;
use Illuminate\Support\Facades\DB;
use App\Mail\SendUserYourOrderGiven;
use App\Repositories\BasketRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

/**
 * App\Models\Orders
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property int $user_id
 * @property string $items
 * @property string $price
 * @property string $country
 * @property string $delivery
 * @property string $payment
 * @property string|null $photo
 * @property string|null $comment
 * @property string|null $admin_comment
 * @property string $status
 * @property string $payment_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $labels
 * @property string|null $sale_price
 * @property string|null $ur_name
 * @property string|null $ur_reg_num
 * @property string|null $ur_legal_addr
 * @property string|null $ur_pnr_nr
 * @property string|null $ur_bank_code
 * @property string|null $ur_bank_acc_code
 * @property string|null $is_ur
 * @property string|null $ur_bank_name
 * @property string|null $pdf_link
 * @property int|null $pdf_approved
 * @property string|null $order_image
 * @property string|null $_url
 * @property string|null $admin_coment
 * @property int|null $is_admin_order
 * @property string|null $painter_images
 * @property string|null $painter_endtime
 * @property int|null $painter_payed
 * @property int|null $has_pdf
 * @property string|null $painter_comment
 * @property int|null $is_show_painter_images
 * @property string|null $client_images
 * @property string|null $client_comment
 * @property string|null $approved_date
 * @property int|null $sale_percent
 * @property int|null $sale_eur
 * @property int|null $all_sales
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OrdersChats[] $orders_chats
 * @property-read int|null $orders_chats_count
 * @method static Builder|Orders newModelQuery()
 * @method static Builder|Orders newQuery()
 * @method static Builder|Orders query()
 * @method static Builder|Orders whereAdminComent($value)
 * @method static Builder|Orders whereAdminComment($value)
 * @method static Builder|Orders whereAllSales($value)
 * @method static Builder|Orders whereApprovedDate($value)
 * @method static Builder|Orders whereClientComment($value)
 * @method static Builder|Orders whereClientImages($value)
 * @method static Builder|Orders whereComment($value)
 * @method static Builder|Orders whereCountry($value)
 * @method static Builder|Orders whereCreatedAt($value)
 * @method static Builder|Orders whereDelivery($value)
 * @method static Builder|Orders whereHasPdf($value)
 * @method static Builder|Orders whereId($value)
 * @method static Builder|Orders whereIsAdminOrder($value)
 * @method static Builder|Orders whereIsShowPainterImages($value)
 * @method static Builder|Orders whereIsUr($value)
 * @method static Builder|Orders whereItems($value)
 * @method static Builder|Orders whereLabels($value)
 * @method static Builder|Orders whereOrderImage($value)
 * @method static Builder|Orders wherePainterComment($value)
 * @method static Builder|Orders wherePainterEndtime($value)
 * @method static Builder|Orders wherePainterImages($value)
 * @method static Builder|Orders wherePainterPayed($value)
 * @method static Builder|Orders wherePayment($value)
 * @method static Builder|Orders wherePaymentStatus($value)
 * @method static Builder|Orders wherePdfApproved($value)
 * @method static Builder|Orders wherePdfLink($value)
 * @method static Builder|Orders wherePhoto($value)
 * @method static Builder|Orders wherePrice($value)
 * @method static Builder|Orders whereSaleEur($value)
 * @method static Builder|Orders whereSalePercent($value)
 * @method static Builder|Orders whereSalePrice($value)
 * @method static Builder|Orders whereStatus($value)
 * @method static Builder|Orders whereUpdatedAt($value)
 * @method static Builder|Orders whereUrBankAccCode($value)
 * @method static Builder|Orders whereUrBankCode($value)
 * @method static Builder|Orders whereUrBankName($value)
 * @method static Builder|Orders whereUrLegalAddr($value)
 * @method static Builder|Orders whereUrName($value)
 * @method static Builder|Orders whereUrPnrNr($value)
 * @method static Builder|Orders whereUrRegNum($value)
 * @method static Builder|Orders whereUrl($value)
 * @method static Builder|Orders whereUserId($value)
 */
class Orders extends Model
{
    // Делимитер для названий файлов изображений заказа.
    // Используем "_" чтобы коды доставки могли содержать "-" (например "o-D").
    const DELIMITER_FILENAME = '_';
    // Коды лака: L0 - нету, L1 - Dammar varnish, L2 - Art gel.
    // Коды мазков: P0 - нету, P1 - мазки маслом, P2 - полностью маслом.

    protected $fillable = [

    ];
    private $basketRepository;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->basketRepository = resolve(BasketRepository::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function painterAssignment()
    {
        return $this->hasOne(PainterOrder::class, 'order_id');
    }

    public function printingAssignment()
    {
        return $this->hasOne(PrintingOrder::class, 'order_id');
    }

    public function vrNumber()
    {
        return $this->hasOne(VrNumber::class, 'order_id');
    }

    public function adminChats()
    {
        return $this->hasMany(AdminChats::class, 'orders_id');
    }

    public function saMessages()
    {
        return $this->hasMany(SaMessage::class, 'orders_id');
    }

    public function saConversations()
    {
        return $this->hasMany(SaConversation::class, 'orders_id');
    }

    // This function is used to get a delivery data form orders table
    public static function getDeliveryData($id, $data)
    {
        $delivery = DB::table('orders')->where('id', $id)->pluck('delivery')->first();

        // $delivery is a json string
        //  Return a data from $delivery with a key $data
        if(isset(json_decode($delivery, true)[$data]))
        {
            return json_decode($delivery, true)[$data];
        }
    }

    // This function is used to add bonus to a user
    public static function addBonusToUser($id, $bonus)
    {
        $user = User::find($id);

        $user->bonuses = $user->bonuses + $bonus;

        $user->save();
    }




    public static function getOrderByIdStatic($id)
    {
        $orders = DB::table('orders')->where('id', $id)->orderBy('created_at', 'desc')->get()->toArray();

        return $orders;
    }

    public static function getVRById($order_id)
    {
        $vrv_1 = DB::table('vr_numbers')->where('order_id', intval($order_id))->pluck('vrv_1')->first();

        $vrv_2 = DB::table('vr_numbers')->where('order_id', intval($order_id))->pluck('vrv_2')->first();

        $vrv_3 = DB::table('vr_numbers')->where('order_id', intval($order_id))->pluck('vrv_3')->first();
        
        $vrv_4 = DB::table('vr_numbers')->where('order_id', intval($order_id))->pluck('vrv_4')->first();

        if ($vrv_1 != null) {
            return 'VR00' . $vrv_1;
        }

        if ($vrv_2 != null) {
            return 'BAW' . $vrv_2;
        }


        if ($vrv_3 != null) {
            if($vrv_3<10){
                $vrv_3_mod = '00'.$vrv_3;
            }
            else if($vrv_3<100 && $vrv_3>9){
                $vrv_3_mod = '0'.$vrv_3;
            } else{
                $vrv_3_mod = $vrv_3;
            }
            return 'VRR445' . $vrv_3_mod;
        }

        if ($vrv_4 != null) {
            return 'DS020' . $vrv_4;
        }

        return null;
    }

    public function getOrderByCurrentUser()
    {
        $orders = DB::table('orders')->where('user_id', Auth::id())->orderBy(
            'created_at',
            'desc'
        )->get()->toArray();

        return $orders;
    }

    public function getOrderByCurrentUserPaginate($paginate_per_page = 5)
    {
        $orders = DB::table('orders')->where('user_id', Auth::id())->orderBy(
            'created_at',
            'desc'
        )->paginate($paginate_per_page);

        return $orders;
    }

    public function orders_chats()
    {
        return $this->hasMany(OrdersChats::class);
    }

    public function order_painter_images()
    {
        return $this->hasMany(OrderPainterImages::class, "order_id");
    }

    public function order_user_images()
    {
        return $this->hasMany(OrderUserImages::class, "order_id");
    }

    public function order_user_comments()
    {
        return $this->hasMany(OrderUserComments::class, "order_id");
    }

    public function order_painter_comments()
    {
        return $this->hasMany(OrderPainterComment::class, 'order_id');
    }

    public function order_payment_requests()
    {
        return $this->hasMany(OrderPaymentRequest::class, 'order_id');
    }

    public function getLastOrderByCurrentUser()
    {
        $orders = DB::table('orders')->where('user_id', Auth::id())->orderBy('created_at', 'desc')->first();

        return $orders;
    }

    public function getOrderById($id)
    {
        $orders = DB::table('orders')->where('id', $id)->orderBy('created_at', 'desc')->get()->toArray();

        return $orders;
    }

    public function getAllOrders()
    {
        return DB::table('orders')->orderBy('created_at', 'desc')->get()->toArray();
    }

    public function getOrdersByUserIdAndOrderType($user_id, $payment_status)
    {
        return DB::table('orders')->where('user_id', $user_id)->where(
            'payment_status',
            $payment_status
        )->orderBy('created_at', 'desc')->get()->toArray();
    }

    public function deleteOrder($id)
    {

       // Мы хотим вернуть бонусы, если заказ был удален
       $order = DB::table('orders')->where('id', $id)->first();
       $useBonus=$order->use_bonus;

       //
       if ($useBonus==1){
       $total=$order->price;
       $real_price=$order->sale_price;
       $bonuses_used=$total-$real_price;
       $user_id=$order->user_id;

       $users = DB::table('users')->where('id', $user_id)->first();
       $bonuses=$users->bonuses;
       $new_bonuses=$bonuses+$bonuses_used;

       DB::table('users')->where('id', $user_id)->update(['bonuses' => $new_bonuses]);
       }
       DB::table('orders')->where('id', $id)->delete();
       return true;
    }

    public function changeOrderStatus($id, $status)
    {
        DB::table('orders')->where('id', $id)->update(['status' => $status]);
        return true;
    }

    public function updateOrder($id, $params)
    {
        if (isset($params['delivery']) && is_array($params['delivery'])) {
            $params['delivery'] = json_encode($params['delivery']);
        }

        DB::table('orders')->where('id', $id)->update($params);
        $order = DB::table('orders')->where('id', $id)->first();
        $jsonString=$order->delivery;
        $array = json_decode($jsonString, true);


        return true;
    }


    public function saveOrder($basket, $checkoutParams)
    {

        if(isset($checkoutParams['phone']))
        {
            $checkoutParams['phone'] = str_replace(" ","", $checkoutParams['phone']);
            $checkoutParams['phone'] = str_replace(")","", $checkoutParams['phone']);
            $checkoutParams['phone'] = str_replace("(","", $checkoutParams['phone']);
            $checkoutParams['phone'] = str_replace("-","", $checkoutParams['phone']);
        }

        if(isset($checkoutParams['phone_rec']))
        {
            $checkoutParams['phone_rec'] = str_replace(" ","", $checkoutParams['phone_rec']);
            $checkoutParams['phone_rec'] = str_replace(")","", $checkoutParams['phone_rec']);
            $checkoutParams['phone_rec'] = str_replace("(","", $checkoutParams['phone_rec']);
            $checkoutParams['phone_rec'] = str_replace("-","", $checkoutParams['phone_rec']);
        }

        if (auth()->check()) {
            $user = Auth::user();

            $user_email = $user->email;

//            $coup_code = DB::table('coupons')->where('id', $user->active_coupon)->pluck('id')->first();
//
//            if ($user->is_active_friend_inv != null) {
//                $has_invited_sale = 1;
//
//                $user->is_active_friend_inv = null;
//            } else {
//                $has_invited_sale = 0;
//            }
//
//            if ($user->active_coupon != null) {
//                $has_coupon = 1;
//
//                $coup_code = DB::table('coupons')->where('id', $user->active_coupon)->pluck('text')->first();
//
//                $coupon_id=$user->active_coupon;
//
//                $coup_val = DB::table('coupons')->where('id', $user->active_coupon)->pluck('value')->first();
//
//
//
//                //$user->active_coupon = null;
//
//            } else {
//                $has_coupon = 0;
//            }

            if ($user->address == null) {
                $user->address = $checkoutParams['address'];
            }

            if ($user->first_name == null) {
                $user->first_name = $checkoutParams['name'];
            }

            if ($user->last_name == null) {
                $user->last_name = $checkoutParams['last_name'];
            }

            if ($user->postal_index == null) {
                $user->postal_index = $checkoutParams['postal_index'];
            }

            if ($user->phone == null) {
                $user->phone = $checkoutParams['phone'];
            }

            $user->save();


        }
        else
        {

            $has_coupon = 0;
            $random_pass = Str::random(8);
            $pass = Hash::make($random_pass);


            if(isset($checkoutParams['phone']))
            {
                $checkoutParams['phone'] = str_replace(" ","", $checkoutParams['phone']);
                $checkoutParams['phone'] = str_replace(")","", $checkoutParams['phone']);
                $checkoutParams['phone'] = str_replace("(","", $checkoutParams['phone']);
                $checkoutParams['phone'] = str_replace("-","", $checkoutParams['phone']);
            }

            $checkoutParams->validate([
                'phone' => 'required|min:7|regex:/^\+?[0-9\s()-]+$/'
            ]);

            $user = new User();
            $user->password = $pass;
            $user->email = $checkoutParams['email'];
            $user->first_name = $checkoutParams['name'];
            $user->last_name = $checkoutParams['last_name'];
            $user->phone = $checkoutParams['phone'];
            $user->address = $checkoutParams['address'];
            $user->postal_index = $checkoutParams['postal_index'];
            $user->country = $checkoutParams['country'];
            $user->news = 'YES';
            $user->ad = 'NO';
            $user->client_data = 'NO';
            $user->role_id = 2;
            $user->avatar = 'users/default.png';
            $user->active_coupon = null;
            $settings = $user->settings;
            $settings['locale'] = app()->getLocale();
            $user->settings = $settings;
            $user->save();

            Auth::login($user);

            app(BestEffortMailService::class)->send(
                $user->email,
                new SendUserRegister($user, $random_pass),
                'checkout_user_registration',
                ['user_id' => $user->id]
            );

            $user_email = $checkoutParams['email'];

        }

        // картинка в заказе

        if (isset($checkoutParams['photo'])) {
            $photo = $checkoutParams['photo'];

            $validator = Validator::make($checkoutParams, [
                'photo' => 'image|mimes:png,bmp,jpg,jpeg,heic,heif',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Invalid filesize or extension.',
                    'errors' => $validator,
                ]);
            }

            $file = Storage::disk('uploads')->put('uploads', $photo);

            $file_uploaded = URL::to('/') . '/' . $file;
        } else {
            $file_uploaded = null;
        }

        $delivery = [];

        $delivery['deliv_price'] = $checkoutParams['deliv_price'];

        $delivery['when_send'] = $checkoutParams['when_send'];

        $delivery['email'] = $checkoutParams['email'];
        $delivery['payer_phone'] = $checkoutParams['phone'];

        $recipientPhone = $checkoutParams['phone_rec'] ?? null;
        if (is_string($recipientPhone)) {
            $recipientPhone = trim($recipientPhone);
        }

        $hasRecipientData = (
            $checkoutParams['name_rec'] != '' &&
            $checkoutParams['last_name_rec'] != '' &&
            $checkoutParams['phone_rec'] != '' &&
            $checkoutParams['address_rec'] != '' &&
            $checkoutParams['postal_index_rec'] != ''
        );

        if ($hasRecipientData) {
            $delivery['is_no_payer'] = 1;

            $delivery['first_name'] = $checkoutParams['name_rec'];

            $delivery['last_name'] = $checkoutParams['last_name_rec'];

            $delivery['phone'] = $checkoutParams['phone_rec'];

            $delivery['address'] = $checkoutParams['address_rec'];

            $delivery['postal_index'] = $checkoutParams['postal_index_rec'];
        } else {
            $delivery['first_name'] = $checkoutParams['name'];

            $delivery['last_name'] = $checkoutParams['last_name'];

            $delivery['phone'] = $checkoutParams['phone'];

            $delivery['address'] = $checkoutParams['address'];

            $delivery['postal_index'] = $checkoutParams['postal_index'];

        }

        if (!$hasRecipientData && $recipientPhone) {
            $delivery['phone'] = $recipientPhone;
        }


        $delivery['country'] = $checkoutParams['country'];

        $delivery['city'] = $checkoutParams['city'];

        $delivery['sposob'] = $checkoutParams['delivery'];

        $pickupWorkshopId = isset($checkoutParams['pickup_workshop_id']) ? (int)$checkoutParams['pickup_workshop_id'] : 0;
        $deliveryTownId = isset($checkoutParams['delivery_town_id']) ? (int)$checkoutParams['delivery_town_id'] : 0;
        $delivery['pickup_workshop_id'] = $pickupWorkshopId > 0 ? $pickupWorkshopId : null;
        $delivery['delivery_town_id'] = $deliveryTownId > 0 ? $deliveryTownId : null;
        $delivery['delivery_photo_short_code'] = null;
        if (isset($checkoutParams['delivery_photo_short_code']) && is_string($checkoutParams['delivery_photo_short_code'])) {
            $deliveryPhotoShortCode = trim($checkoutParams['delivery_photo_short_code']);
            $delivery['delivery_photo_short_code'] = $deliveryPhotoShortCode !== '' ? $deliveryPhotoShortCode : null;
        }
        if ($delivery['delivery_photo_short_code'] === null) {
            if ($delivery['sposob'] === 'pickup_at_viar_workshop' && $delivery['pickup_workshop_id']) {
                $delivery['delivery_photo_short_code'] = self::resolveDeliveryPhotoShortCodeById(
                    'delivery_pickup_at_viar_workshop',
                    $delivery['pickup_workshop_id'],
                    $delivery['country']
                );
            } elseif ($delivery['sposob'] === 'city_delivery' && $delivery['delivery_town_id']) {
                $delivery['delivery_photo_short_code'] = self::resolveDeliveryPhotoShortCodeById(
                    'a_delivery_towns',
                    $delivery['delivery_town_id'],
                    $delivery['country']
                );
            }
        }

        $delivery['payment'] = $checkoutParams['payment'];

        $delivery['comment'] = $checkoutParams['comment'];

        if (isset($checkoutParams['ur_name'])) {
            $ur_name = $checkoutParams['ur_name'];
        } else {
            $ur_name = '';
        }

        if (isset($checkoutParams['ur_reg_num'])) {
            $ur_reg_num = $checkoutParams['ur_reg_num'];
        } else {
            $ur_reg_num = '';
        }

        if (isset($checkoutParams['ur_name_l'])) {
            $ur_name_l = $checkoutParams['ur_name_l'];
        } else {
            $ur_name_l = '';
        }

        if (isset($checkoutParams['ur_legal_addr'])) {
            $ur_legal_addr = $checkoutParams['ur_legal_addr'];
        } else {
            $ur_legal_addr = '';
        }

        if (isset($checkoutParams['ur_pnr_nr'])) {
            $ur_pnr_nr = $checkoutParams['ur_pnr_nr'];
        } else {
            $ur_pnr_nr = '';
        }

        if (isset($checkoutParams['ur_bank_code'])) {
            $ur_bank_code = $checkoutParams['ur_bank_code'];
        } else {
            $ur_bank_code = '';
        }

        if (isset($checkoutParams['ur_bank_acc_code'])) {
            $ur_bank_acc_code = $checkoutParams['ur_bank_acc_code'];
        } else {
            $ur_bank_acc_code = '';
        }

        if (isset($checkoutParams['ur_bank_name'])) {
            $ur_bank_name = $checkoutParams['ur_bank_name'];
        } else {
            $ur_bank_name = '';
        }



        if (isset($basket['sale_price'])) {
            $ttp = $basket['sale_price'];
        } else {
            $ttp = $basket['totalPrice'];
        }

        $svp = $basket['totalPrice'];

        $terms_price = 0;
		if (!empty($basket))
        {
			foreach ($basket as $basketIndex => $product)
            {
				if (!isset($product['sumPrice']))
                {
					continue;
                }
				$terms_price = (float)$terms_price + (float)$product['terms_price'];
            }
            $basket['total_terms_price'] = $terms_price;
        }



        $z = 0;
        foreach ($basket as $okey => $itm) {
            if (isset($basket[$okey]['savedImage'])) {
                $z++;
                $basket[$okey]['savedImage'] = $this->renameAndRemoveImage($basket[$okey]['savedImage'], $z);
            }
            if (isset($itm['orig_images']) && $itm['orig_images']) {
                foreach ($itm['orig_images'] as $key => $image) {
                    $z++;
                    $basket[$okey]['orig_images'][$key] = $this->renameAndRemoveImage($image, $z);
                }
            }
        }



        if (isset($basket['coupon_id']) && $basket['coupon_id']>0) {
            $delivery['coupon_type']=$basket['coupon_type'];
        }

        if (isset($basket['spend_bonus']) && $basket['spend_bonus']==1 )
        {

            $delivery['coupon_type']='bonus';
            $use_bonus=1;
            $used_bonus=$svp-$ttp;

            $user->bonuses = $user->bonuses-$used_bonus;

            $user->save();


        } else {
            $use_bonus=0;
        }



        $order_id = DB::table('orders')->insertGetId([
            'user_id' => $user->id,
            'price' => $svp,
            'sale_price' => $ttp,
            'items' => json_encode($basket),
            'country' => $checkoutParams['country'],
            'delivery' => json_encode($delivery),
            'payment' => $checkoutParams['payment'],
            'payment_status' => 'not_payed',
            'comment' => $checkoutParams['comment'],
            'status' => 'watching',
            'order_image' => $file_uploaded,
            'photo' => null,
            'ur_name' => $ur_name,
            'ur_name_l' => $ur_name_l,
            'ur_reg_num' => $ur_reg_num,
            'ur_legal_addr' => $ur_legal_addr,
            'ur_pnr_nr' => $ur_pnr_nr,
            'ur_bank_name' => $ur_bank_name,
            'ur_bank_code' => $ur_bank_code,
            'ur_bank_acc_code' => $ur_bank_acc_code,
            'created_at' => date('Y-m-j H:i:s'),
            'updated_at' => date('Y-m-j H:i:s'),
         //   'used_coupon'=>$coupon_id,
            'use_bonus' => $use_bonus
        ]);

        self::renameUploadsPhoto($order_id);

        app(BestEffortMailService::class)->send(
            $user_email,
            new SendUserYourOrderGiven($basket, $user, $order_id, $user->preferredLocale()),
            'checkout_order_confirmation',
            ['order_id' => $order_id, 'user_id' => $user->id]
        );
        app(SynvolveWebhookService::class)->notifyOrderSnapshotById((int) $order_id, 'site_order_created');

        if (isset($basket['coupon_id']) && $basket['coupon_id'] >0) {

            if( $basket['coupon_type']=="30_40" ){   $coupon_type='Использован купон:30x40';   }

            if( $basket['coupon_type']=="bonus" ){   $coupon_type='Использованы бонусы';    }

            if( $basket['coupon_type']=="facebook" ){   $coupon_type='Использованы купон скриншот facebook';    }

            if( $basket['coupon_type']=="40_60" ){   $coupon_type='Использован купон:40x60';    }

            if( $basket['coupon_type']=="date" ){   $coupon_type='Использован купон:2 даты';    }

            if( $basket['coupon_type']=="friend" ){

                $coupon_type='Использована скидка по приглашению';

                $user_id = DB::table('coupons')->where('id', $basket['coupon_id'])->pluck('user_id')->first();
                $coupon_text = DB::table('coupons')->where('id', $basket['coupon_id'])->pluck('value')->first();

                $user->is_active_friend_inv=1;
                $user->save();


                DB::table('users')->where('id', $user_id)
                    ->increment('bonuses', 5);



            }

            if( $basket['coupon_type']=="1free" ){   $coupon_type='Использована акция При заказе 3 картин - 1 в подарок';    }

            if( $basket['coupon_type']=="free_delivery" ){   $coupon_type='Использован купон: Бесплатная доставка';    }

            if( $basket['coupon_type']=="abandoned_basket" ){   $coupon_type='Использован купон: Брошенная корзина';    }

            if( $basket['coupon_type']=="giftcard" ){   $coupon_type='Использован купон: Подарочная карта';    }

            if( $basket['coupon_type']=="universal" ){
                $coupon_type='Использован универсальный купон';

                        /// Запишем текущую дату, чтобы купон не использовали больше одног раза в сутки
                        $currentDate = date('ymd');
                        $user->used_universal_coupon = $currentDate;
                        $user->save();
            }

            DB::table('order_action')->insert([
                'user' => $checkoutParams['email'],
               'activity' => 'При заказе '.$order_id.' '.$coupon_type,
                'created_at' => now()
            ]);

            if( $basket['coupon_type']!="friend" && $basket['coupon_type']!="universal"  && $basket['coupon_type']!="free_delivery") {
                DB::table('coupons')->where('id', $basket['coupon_id'])->delete();
            }

        }

        Session::forget('basket');
        Session::forget('is_coupon_def');
        Session::forget('sale_dated');
        Session::forget('phone_rec');

        Session::put('coupon_id', 0);
        Session::put('coupon_val', 0);
        Session::put('coupon_type', "none");
        Session::put('spend_bonus', 0);
        $bonuses=intval($user->bonuses);
        Session::put('bonus', $bonuses);

        $user->active_coupon = null;
        $user->is_coupon_dates = null;
        $user->save();

        $orders = $this->getOrdersByUserId($user->id);

        $orders = (array) $orders[0];

        return $orders['id'];
    }

    public static function renameUploadsPhoto($orderId)
    {
        $order = Orders::find($orderId);
        if (!$order) {
            return false;
        }

        $items = json_decode($order->items, true);
        if (!is_array($items)) {
            return false;
        }

        if (!\Illuminate\Support\Facades\File::exists(public_path('orders'))) {
            @\Illuminate\Support\Facades\File::makeDirectory(public_path('orders'), 0775, true);
        }

        $renamedMap = [];
        $uniqueImageCount = 0;
        $globalIndex = 1;
        $auxGlobalIndex = 1;
        $normalizeImageList = function ($value) {
            if (is_array($value)) {
                return $value;
            }
            if (!is_string($value)) {
                return [];
            }

            $trimmed = trim($value);
            if ($trimmed === '') {
                return [];
            }

            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            if (strpos($trimmed, ',') !== false) {
                return array_values(array_filter(array_map('trim', explode(',', $trimmed))));
            }

            return [$trimmed];
        };

        $isRenameablePath = function ($rawPath) {
            if (!is_string($rawPath) || trim($rawPath) === '') {
                return null;
            }

            $parsed = parse_url($rawPath);
            $path = $parsed['path'] ?? $rawPath;
            $path = ltrim($path, '/');
            if ($path === '') {
                return null;
            }

            $isPublicUpload = (strpos($path, 'uploads/') === 0) || (strpos($path, 'orders/') === 0);
            $isCollageStorage = (strpos($path, 'user_images/') === 0) || (strpos($path, 'storage/user_images/') === 0);
            if (!$isPublicUpload && !$isCollageStorage) {
                return null;
            }

            $mapKey = $path;
            if ($isCollageStorage && strpos($mapKey, 'storage/') === 0) {
                $mapKey = substr($mapKey, strlen('storage/'));
            }

            return [$path, $mapKey, $isPublicUpload, $isCollageStorage];
        };
        $formatPathLikeOriginal = function ($originalRawPath, $targetRel) {
            if (is_string($originalRawPath) && preg_match('#^https?://#i', $originalRawPath)) {
                return url($targetRel);
            }

            return $targetRel;
        };

        $qtyFromItem = function ($item) {
            $qty = 1;
            if (is_array($item) && isset($item['count']) && $item['count'] !== '' && $item['count'] !== 'undefined') {
                $qty = (int)$item['count'];
            }
            return $qty > 0 ? $qty : 1;
        };

        // 1) Считаем количество уникальных картинок, которые реально будем переименовывать в этом заказе.
        $seenMapKeys = [];
        $collect = function ($rawPath) use (&$uniqueImageCount, &$seenMapKeys, $isRenameablePath) {
            $info = $isRenameablePath($rawPath);
            if (!$info) {
                return;
            }

            [, $mapKey] = $info;
            if (isset($seenMapKeys[$mapKey])) {
                return;
            }
            $seenMapKeys[$mapKey] = true;
            $uniqueImageCount++;
        };

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (isset($item['activeImage'])) {
                $collect($item['activeImage']);
            }
            if (isset($item['savedImage'])) {
                $collect($item['savedImage']);
            }
            $origImages = $normalizeImageList($item['orig_images'] ?? []);
            if (!empty($origImages)) {
                foreach ($origImages as $img) {
                    $collect($img);
                }
            }
            $allImages = $normalizeImageList($item['allImages'] ?? []);
            if (!empty($allImages)) {
                foreach ($allImages as $img) {
                    $collect($img);
                }
            }
            $allBackgrounds = $normalizeImageList($item['allBackgrounds'] ?? []);
            if (!empty($allBackgrounds)) {
                foreach ($allBackgrounds as $img) {
                    $collect($img);
                }
            }
        }

        $renameOne = function ($rawPath, $item) use ($order, &$renamedMap, $isRenameablePath, $qtyFromItem, &$uniqueImageCount, &$globalIndex) {
            if (!is_string($rawPath) || trim($rawPath) === '') {
                return $rawPath;
            }

            $info = $isRenameablePath($rawPath);
            if (!$info) {
                return $rawPath;
            }
            [$path, $mapKey, $isPublicUpload, $isCollageStorage] = $info;

            if (isset($renamedMap[$mapKey])) {
                return $renamedMap[$mapKey];
            }

            $qty = $qtyFromItem($item);
            $base = Orders::generateImageName($order, $item, $qty);
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $extension = $extension ? $extension : 'png';

            $candidateName = $base;
            if ($uniqueImageCount > 1) {
                $idx = $globalIndex;
                $globalIndex++;
                $candidateName = $base . '-' . $idx;
            }

            $targetRel = 'orders/' . $candidateName . '.' . $extension;
            while (\Illuminate\Support\Facades\File::exists(public_path($targetRel))) {
                if ($uniqueImageCount <= 1) {
                    // Если картинка одна, но имя уже занято — включаем индекс.
                    $uniqueImageCount = 2;
                }
                $idx = $globalIndex;
                $globalIndex++;
                $candidateName = $base . '-' . $idx;
                $targetRel = 'orders/' . $candidateName . '.' . $extension;
            }

            // 1) public_path(): uploads/* или orders/*
            if ($isPublicUpload) {
                $oldFs = public_path($path);
                $newFs = public_path($targetRel);
                if (\Illuminate\Support\Facades\File::exists($oldFs)) {
                    \Illuminate\Support\Facades\File::move($oldFs, $newFs);
                    $newUrl = url($targetRel);
                    $renamedMap[$mapKey] = $newUrl;
                    return $newUrl;
                }
                return $rawPath;
            }

            // 2) storage/app/public/user_images/* (коллаж)
            $storageRel = $path;
            if (strpos($storageRel, 'storage/') === 0) {
                $storageRel = substr($storageRel, strlen('storage/'));
            }

            if (\Storage::disk('public')->exists($storageRel)) {
                $contents = \Storage::disk('public')->get($storageRel);
                \Storage::disk('uploads')->put($targetRel, $contents);
                \Storage::disk('public')->delete($storageRel);

                $newUrl = url($targetRel);
                $renamedMap[$mapKey] = $newUrl;
                return $newUrl;
            }

            return $rawPath;
        };
        $renameAux = function ($rawPath, $clientTag = '') use (
            $order,
            &$renamedMap,
            $isRenameablePath,
            $formatPathLikeOriginal,
            &$auxGlobalIndex
        ) {
            if (!is_string($rawPath) || trim($rawPath) === '') {
                return $rawPath;
            }

            $info = $isRenameablePath($rawPath);
            if (!$info) {
                return $rawPath;
            }
            [$path, $mapKey, $isPublicUpload, $isCollageStorage] = $info;

            if (isset($renamedMap[$mapKey])) {
                return $formatPathLikeOriginal($rawPath, ltrim(parse_url($renamedMap[$mapKey], PHP_URL_PATH) ?? $renamedMap[$mapKey], '/'));
            }

            $base = self::getOrderImageName($order, (string)$clientTag, false, false, false, false, false, false);
            if (!is_string($base) || trim($base) === '') {
                $base = 'N' . ($order->id ?? '') . '_' . trim((string)$clientTag);
            }
            $base = self::sanitizeFilenamePart($base);

            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $extension = $extension ? $extension : 'png';

            $candidateName = $base . '-' . $auxGlobalIndex;
            $auxGlobalIndex++;
            $targetRel = 'orders/' . $candidateName . '.' . $extension;

            while (\Illuminate\Support\Facades\File::exists(public_path($targetRel))) {
                $candidateName = $base . '-' . $auxGlobalIndex;
                $auxGlobalIndex++;
                $targetRel = 'orders/' . $candidateName . '.' . $extension;
            }

            if ($isPublicUpload) {
                $oldFs = public_path($path);
                $newFs = public_path($targetRel);
                if (\Illuminate\Support\Facades\File::exists($oldFs)) {
                    \Illuminate\Support\Facades\File::move($oldFs, $newFs);
                    $newUrl = url($targetRel);
                    $renamedMap[$mapKey] = $newUrl;
                    return $formatPathLikeOriginal($rawPath, $targetRel);
                }
                return $rawPath;
            }

            $storageRel = $path;
            if (strpos($storageRel, 'storage/') === 0) {
                $storageRel = substr($storageRel, strlen('storage/'));
            }

            if ($isCollageStorage && \Storage::disk('public')->exists($storageRel)) {
                $contents = \Storage::disk('public')->get($storageRel);
                \Storage::disk('uploads')->put($targetRel, $contents);
                \Storage::disk('public')->delete($storageRel);

                $newUrl = url($targetRel);
                $renamedMap[$mapKey] = $newUrl;
                return $formatPathLikeOriginal($rawPath, $targetRel);
            }

            return $rawPath;
        };

        foreach ($items as $key => $item) {
            if (!is_array($item)) {
                continue;
            }

            // Финальное изображение (важно для коллажа).
            if (isset($item['activeImage'])) {
                $items[$key]['activeImage'] = $renameOne($item['activeImage'], $item);
            }

            if (isset($item['savedImage'])) {
                $items[$key]['savedImage'] = $renameOne($item['savedImage'], $item);
            }

            $origImages = $normalizeImageList($item['orig_images'] ?? []);
            if (!empty($origImages)) {
                $renamedOrig = [];
                foreach ($origImages as $imgKey => $img) {
                    $renamedOrig[$imgKey] = $renameOne($img, $item);
                }
                $items[$key]['orig_images'] = $renamedOrig;
            }

            // Коллаж: исходные картинки и фоны.
            $allImages = $normalizeImageList($item['allImages'] ?? []);
            if (!empty($allImages)) {
                $renamedAllImages = [];
                foreach ($allImages as $imgKey => $img) {
                    $renamedAllImages[$imgKey] = $renameOne($img, $item);
                }
                $items[$key]['allImages'] = $renamedAllImages;
            }

            $allBackgrounds = $normalizeImageList($item['allBackgrounds'] ?? []);
            if (!empty($allBackgrounds)) {
                $renamedBackgrounds = [];
                foreach ($allBackgrounds as $imgKey => $img) {
                    $renamedBackgrounds[$imgKey] = $renameOne($img, $item);
                }
                $items[$key]['allBackgrounds'] = $renamedBackgrounds;
            }
        }

        // Переименовываем изображения художника (старые строковые поля заказа).
        $painterImages = $normalizeImageList($order->painter_images ?? null);
        if (!empty($painterImages)) {
            $renamedPainter = [];
            foreach ($painterImages as $img) {
                $renamedPainter[] = $renameAux($img, 'picture');
            }
            $order->painter_images = implode(',', array_values(array_filter($renamedPainter, function ($v) {
                return is_string($v) && trim($v) !== '';
            })));
        }

        $painterSketchImages = $normalizeImageList($order->painter_sketch_images ?? null);
        if (!empty($painterSketchImages)) {
            $renamedSketches = [];
            foreach ($painterSketchImages as $img) {
                $renamedSketches[] = $renameAux($img, 'sketch');
            }
            $order->painter_sketch_images = implode(',', array_values(array_filter($renamedSketches, function ($v) {
                return is_string($v) && trim($v) !== '';
            })));
        }

        // Переименовываем записи в таблице order_painter_images.
        $painterRows = DB::table('order_painter_images')
            ->where('order_id', $orderId)
            ->get(['id', 'image', 'small_image', 'is_img_sketch', 'is_img_painter']);
        foreach ($painterRows as $row) {
            $tag = ((int)$row->is_img_sketch === 1) ? 'sketch' : (((int)$row->is_img_painter === 1) ? 'picture' : 'painter');

            $newImage = $row->image ? $renameAux($row->image, $tag) : $row->image;
            $newSmallImage = $row->small_image ? $renameAux($row->small_image, $tag . '_small') : $row->small_image;

            if ($newImage !== $row->image || $newSmallImage !== $row->small_image) {
                DB::table('order_painter_images')
                    ->where('id', $row->id)
                    ->update([
                        'image' => $newImage,
                        'small_image' => $newSmallImage,
                        'updated_at' => now(),
                    ]);
            }
        }

        // Переименовываем отдельные клиентские фото заказа (поле orders.client_images).
        $clientImages = $normalizeImageList($order->client_images ?? null);
        if (!empty($clientImages)) {
            $renamedClient = [];
            foreach ($clientImages as $img) {
                $renamedClient[] = $renameAux($img, 'client');
            }
            $order->client_images = implode(',', array_values(array_filter($renamedClient, function ($v) {
                return is_string($v) && trim($v) !== '';
            })));
        }

        // Переименовываем клиентские фото из таблицы order_user_images.
        $userRows = DB::table('order_user_images')
            ->where('order_id', $orderId)
            ->get(['id', 'image']);
        foreach ($userRows as $row) {
            $newImage = $row->image ? $renameAux($row->image, 'client_comment') : $row->image;
            if ($newImage !== $row->image) {
                DB::table('order_user_images')
                    ->where('id', $row->id)
                    ->update([
                        'image' => $newImage,
                        'updated_at' => now(),
                    ]);
            }
        }

        $order->items = json_encode($items);
        $order->save();

        return true;
    }

    public function getOrdersByUserId($user_id)
    {
        return DB::table('orders')->where('user_id', $user_id)->orderBy('created_at', 'desc')->get()->toArray();
    }

    public function renameAndRemoveImage($img, $increment = false)
    {
        if (is_string($img) && strpos($img, "uploads") !== false)
        {

            $table = DB::select("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = '".env('DB_DATABASE')."' AND TABLE_NAME = 'orders'");
            if (!empty($table))
            {
                $auto_increment = $table[0]->AUTO_INCREMENT;
            }
            else
            {
                $auto_increment = false;
            }

            $parsed = parse_url($img);
            $img = $parsed['path'] ?? $img;
            $img = ltrim($img, '/');

            if ($increment !== false && $increment !== null) {
                $increment = "_" . $increment;
            } else {
                $increment = '';
            }

            $new_fname = Orders::getOrderImageName('', '', false, $auto_increment);
            $new_fname = "orders/".$new_fname.$increment.".".File::extension($img);

            if(Storage::disk('uploads')->exists($img))
            {
                Storage::disk('uploads')->put($new_fname,Storage::disk('uploads')->get($img));
                if(Storage::disk('uploads')->exists($new_fname))
                {
                    Storage::disk('uploads')->delete($img);
                }
                return asset($new_fname);
            }
            else
            {
                return asset($img);
            }
        }
        else
        {
            return $img;
        }
    }

    private static function normalizeCountryCode($country)
    {
        if (!is_string($country) || trim($country) === '') {
            return null;
        }
        $code = strtoupper(trim($country));
        // Нормализация частых вариантов.
        if ($code === 'LATVIA') {
            return 'LV';
        }
        if ($code === 'ESTONIA') {
            return 'EE';
        }
        if ($code === 'LITHUANIA') {
            return 'LT';
        }
        return $code;
    }

    private static function sanitizeFilenamePart($value): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        $text = preg_replace('/[\\\\\\/:"*?<>|]+/u', '-', $text);
        $text = preg_replace('/\s+/u', '_', $text);
        $text = preg_replace('/_+/u', '_', $text);
        $text = preg_replace('/-+/u', '-', $text);

        return trim($text, " _-.");
    }

    private static function safeCountryCodeFromOrder($order, $delivery = null)
    {
        $country = null;
        if (is_object($order) && isset($order->country)) {
            $country = $order->country;
        } elseif (is_array($delivery) && isset($delivery['country'])) {
            $country = $delivery['country'];
        }

        return self::normalizeCountryCode($country) ?? 'XX';
    }

    private static function normalizeDeliveryLookupValue($value)
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $value = preg_replace('/\s+/u', ' ', $value);

        return mb_strtolower($value);
    }

    private static function deliveryRowsWithPhotoCodes($table, $column)
    {
        static $cache = [];
        $cacheKey = $table . ':' . $column;

        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        try {
            $selectColumns = ['id', $column, 'photo_short_code'];
            if (Schema::hasColumn($table, 'country_code')) {
                $selectColumns[] = 'country_code';
            } elseif (Schema::hasColumn($table, 'country')) {
                $selectColumns[] = 'country';
            }

            $rows = DB::table($table)->select($selectColumns)->get();
        } catch (\Throwable $e) {
            $cache[$cacheKey] = [];
            return $cache[$cacheKey];
        }

        $translationsByForeignKey = [];
        try {
            $translations = DB::table('translations')
                ->select('foreign_key', 'value')
                ->where('table_name', $table)
                ->where('column_name', $column)
                ->get();

            foreach ($translations as $translation) {
                $foreignKey = (int)($translation->foreign_key ?? 0);
                if (!$foreignKey) {
                    continue;
                }
                if (!isset($translationsByForeignKey[$foreignKey])) {
                    $translationsByForeignKey[$foreignKey] = [];
                }
                $translationsByForeignKey[$foreignKey][] = $translation->value;
            }
        } catch (\Throwable $e) {
            // Таблица/связи переводов могут отличаться по окружениям.
        }

        $result = [];
        foreach ($rows as $row) {
            $id = (int)($row->id ?? 0);
            if (!$id) {
                continue;
            }

            $labels = [];
            if (isset($row->{$column})) {
                $labels[] = $row->{$column};
            }
            if (isset($translationsByForeignKey[$id])) {
                foreach ($translationsByForeignKey[$id] as $translatedValue) {
                    $labels[] = $translatedValue;
                }
            }

            $normalizedLabels = [];
            foreach ($labels as $label) {
                $normalizedLabel = self::normalizeDeliveryLookupValue($label);
                if ($normalizedLabel !== null) {
                    $normalizedLabels[$normalizedLabel] = true;
                }
            }

            $countryRaw = null;
            if (isset($row->country_code)) {
                $countryRaw = $row->country_code;
            } elseif (isset($row->country)) {
                $countryRaw = $row->country;
            }

            $countryCodes = self::normalizeDeliveryCountryCodes($countryRaw);

            $result[] = [
                'country_codes' => $countryCodes,
                'photo_short_code' => is_string($row->photo_short_code ?? null) ? trim((string)$row->photo_short_code) : '',
                'labels' => array_keys($normalizedLabels),
            ];
        }

        $cache[$cacheKey] = $result;

        return $cache[$cacheKey];
    }

    private static function resolveDeliveryPhotoShortCodeFromTable($table, $column, $value, $country)
    {
        $normalizedValue = self::normalizeDeliveryLookupValue($value);
        if ($normalizedValue === null) {
            return null;
        }

        $rows = self::deliveryRowsWithPhotoCodes($table, $column);
        if (!$rows) {
            return null;
        }

        $country = self::normalizeCountryCode($country) ?? 'XX';
        $defaultCode = null;

        foreach ($rows as $row) {
            if (!in_array($normalizedValue, $row['labels'], true)) {
                continue;
            }

            if ($row['photo_short_code'] === '') {
                continue;
            }

            $countryCodes = is_array($row['country_codes'] ?? null) ? $row['country_codes'] : ['ALL'];

            if (in_array($country, $countryCodes, true)) {
                return $row['photo_short_code'];
            }

            if (in_array('ALL', $countryCodes, true) && $defaultCode === null) {
                $defaultCode = $row['photo_short_code'];
            }
        }

        return $defaultCode;
    }

    private static function resolveDeliveryPhotoShortCodeById($table, $id, $country = null)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return null;
        }

        try {
            $selectColumns = ['id', 'photo_short_code'];
            if (Schema::hasColumn($table, 'country_code')) {
                $selectColumns[] = 'country_code';
            } elseif (Schema::hasColumn($table, 'country')) {
                $selectColumns[] = 'country';
            }

            $row = DB::table($table)->select($selectColumns)->where('id', $id)->first();
        } catch (\Throwable $e) {
            return null;
        }

        if (!$row) {
            return null;
        }

        $photoShortCode = is_string($row->photo_short_code ?? null) ? trim((string)$row->photo_short_code) : '';
        if ($photoShortCode === '') {
            return null;
        }

        if ($country === null) {
            return $photoShortCode;
        }

        $countryRaw = null;
        if (isset($row->country_code)) {
            $countryRaw = $row->country_code;
        } elseif (isset($row->country)) {
            $countryRaw = $row->country;
        }

        $countryCodes = self::normalizeDeliveryCountryCodes($countryRaw);
        $normalizedCountry = self::normalizeCountryCode($country) ?? 'XX';
        if (in_array($normalizedCountry, $countryCodes, true) || in_array('ALL', $countryCodes, true)) {
            return $photoShortCode;
        }

        return null;
    }

    private static function normalizeDeliveryCountryCodes($value)
    {
        if (!is_string($value) || trim($value) === '') {
            return ['ALL'];
        }

        $codes = [];
        foreach (explode(',', $value) as $chunk) {
            $code = strtoupper(trim($chunk));
            if ($code === '') {
                continue;
            }
            $codes[$code] = true;
        }

        if (!$codes) {
            return ['ALL'];
        }

        return array_keys($codes);
    }

    private static function deliveryShortCodeFromOrder($order)
    {
        if (!$order || !isset($order->delivery)) {
            return null;
        }

        $delivery = json_decode($order->delivery, true);
        if (!is_array($delivery)) {
            return null;
        }

        $country = self::safeCountryCodeFromOrder($order, $delivery);
        $method = $delivery['sposob'] ?? null;
        if (!is_string($method) || $method === '') {
            return null;
        }

        switch ($method) {
            case 'to_the_door':
                return 'D';
            case 'city_delivery':
                if (!empty($delivery['delivery_photo_short_code']) && is_string($delivery['delivery_photo_short_code'])) {
                    $photoCode = trim($delivery['delivery_photo_short_code']);
                    if ($photoCode !== '') {
                        return $photoCode;
                    }
                }
                if (!empty($delivery['delivery_town_id'])) {
                    $codeById = self::resolveDeliveryPhotoShortCodeById(
                        'a_delivery_towns',
                        $delivery['delivery_town_id'],
                        $country
                    );
                    if ($codeById !== null) {
                        return $codeById;
                    }
                }
                return self::resolveDeliveryPhotoShortCodeFromTable(
                    'a_delivery_towns',
                    'city',
                    $delivery['city'] ?? null,
                    $country
                ) ?? 'D';
            case 'venipak':
                return 'Pi';
            case 'pickup_Riga':
                if (!empty($delivery['delivery_photo_short_code']) && is_string($delivery['delivery_photo_short_code'])) {
                    $photoCode = trim($delivery['delivery_photo_short_code']);
                    if ($photoCode !== '') {
                        return $photoCode;
                    }
                }
                if (!empty($delivery['pickup_workshop_id'])) {
                    $codeById = self::resolveDeliveryPhotoShortCodeById(
                        'delivery_pickup_at_viar_workshop',
                        $delivery['pickup_workshop_id'],
                        $country
                    );
                    if ($codeById !== null) {
                        return $codeById;
                    }
                }
                return 'o-R';
            case 'pickup_Daugavplis':
            case 'pickup_Daugavpils':
                if (!empty($delivery['delivery_photo_short_code']) && is_string($delivery['delivery_photo_short_code'])) {
                    $photoCode = trim($delivery['delivery_photo_short_code']);
                    if ($photoCode !== '') {
                        return $photoCode;
                    }
                }
                if (!empty($delivery['pickup_workshop_id'])) {
                    $codeById = self::resolveDeliveryPhotoShortCodeById(
                        'delivery_pickup_at_viar_workshop',
                        $delivery['pickup_workshop_id'],
                        $country
                    );
                    if ($codeById !== null) {
                        return $codeById;
                    }
                }
                return 'o-D';
            case 'pickup_at_viar_workshop':
                if (!empty($delivery['delivery_photo_short_code']) && is_string($delivery['delivery_photo_short_code'])) {
                    $photoCode = trim($delivery['delivery_photo_short_code']);
                    if ($photoCode !== '') {
                        return $photoCode;
                    }
                }
                if (!empty($delivery['pickup_workshop_id'])) {
                    $codeById = self::resolveDeliveryPhotoShortCodeById(
                        'delivery_pickup_at_viar_workshop',
                        $delivery['pickup_workshop_id'],
                        $country
                    );
                    if ($codeById !== null) {
                        return $codeById;
                    }
                }
                $workshopAddress = $delivery['address'] ?? ($delivery['city'] ?? null);
                $workshopCode = self::resolveDeliveryPhotoShortCodeFromTable(
                    'delivery_pickup_at_viar_workshop',
                    'title',
                    $workshopAddress,
                    $country
                );
                if ($workshopCode !== null) {
                    return $workshopCode;
                }
                if ($country !== 'LV') {
                    return 'D';
                }
                return 'pi-RD';
            default:
                return 'D';
        }
    }

    private static function isExpressItem($item)
    {
        if (!is_array($item)) {
            return false;
        }

        if (array_key_exists('is_manual_express', $item)) {
            $manualExpress = $item['is_manual_express'];
            if (is_bool($manualExpress)) {
                return $manualExpress;
            }
            if (is_numeric($manualExpress)) {
                return (int)$manualExpress === 1;
            }
            if (is_string($manualExpress)) {
                return in_array(strtolower(trim($manualExpress)), ['1', 'true', 'yes', 'on'], true);
            }
            return false;
        }

        if (isset($item['terms_price']) && (float)$item['terms_price'] > 0) {
            return true;
        }

        return false;
    }

    private static function detectCanvasTypeFromText($text)
    {
        if (!is_string($text) || trim($text) === '') {
            return null;
        }

        $t = mb_strtolower($text);

        // Поддерживаем языки сайта: de, ee/et, en, lt, lv, pl, ru.
        $glossNeedles = [
            'gloss', 'glossy',                // en
            'glanz', 'glänz', 'glänzend',     // de
            'глян',                           // ru
            'blysk', 'blizg', 'połysk',       // pl + общее
            'blizgus',                        // lt
            'läikiv',                         // et
            'spīd', 'spid',                   // lv
        ];
        foreach ($glossNeedles as $needle) {
            if (strpos($t, $needle) !== false) {
                return 'G';
            }
        }

        $cottonNeedles = [
            'cotton',                         // en
            'baumwoll',                       // de
            'хлоп', 'холоп',                  // ru
            'bawełn',                         // pl
            'kokvil',                         // lv
            'medviln',                        // lt
            'puuvill',                        // et
            'bumbac', 'coton', 'algod',       // общее
        ];
        foreach ($cottonNeedles as $needle) {
            if (strpos($t, $needle) !== false) {
                return 'C';
            }
        }

        $syntheticNeedles = [
            'synthetic', 'interior',          // en
            'synthet', 'poly', 'polyester',   // de/en (частично)
            'kunststoff',                     // de
            'синтет', 'полиэстер', 'эконом', 'интерьер', // ru
            'poliest', 'poliester', 'poli',   // pl/общее
            'ekonom', 'interjer', 'interjero', 'interj', // lv/lt/общее
            'ökonoom', 'interjöör', 'sünteet', // et
            'інтер',                          // uk (встречается в данных)
        ];
        foreach ($syntheticNeedles as $needle) {
            if (strpos($t, $needle) !== false) {
                return 'S';
            }
        }

        return null;
    }

    private static function orientationCodeFromSize($size)
    {
        if (!is_string($size) || trim($size) === '') {
            return 'V0';
        }

        if (!preg_match('/(\d+(?:[.,]\d+)?)\s*x\s*(\d+(?:[.,]\d+)?)/i', $size, $m)) {
            return 'V0';
        }

        $a = (float)str_replace(',', '.', $m[1]);
        $b = (float)str_replace(',', '.', $m[2]);
        if ($a <= 0 || $b <= 0) {
            return 'V0';
        }

        if (abs($a - $b) < 0.0001) {
            return 'V3'; // квадрат
        }

        // В размерах обычно используется "{width}x{height}":
        // вертикаль: height > width, горизонталь: width > height, панорама: горизонталь + сильно вытянута.
        if ($a > $b) {
            $ratio = max($a, $b) / min($a, $b);
            return ($ratio >= 1.7) ? 'V4' : 'V2';
        }

        return 'V1';
    }

    private static function orientationCodeFromItem($item, $size = null)
    {
        if (is_array($item) && isset($item['manual_orientation_code'])) {
            $manualOrientationCode = strtoupper(trim((string)$item['manual_orientation_code']));
            if (in_array($manualOrientationCode, ['V1', 'V2', 'V3', 'V4'], true)) {
                return $manualOrientationCode;
            }
        }

        if (is_array($item)) {
            $rawFormId = $item['formId'] ?? ($item['forma_id'] ?? ($item['form_id'] ?? null));
            if ($rawFormId !== null && $rawFormId !== '' && $rawFormId !== 'undefined') {
                $formId = (int)$rawFormId;
                if ($formId >= 1 && $formId <= 4) {
                    // 1-вертикаль, 2-горизонталь, 3-квадрат, 4-панорама
                    return 'V' . $formId;
                }
            }
        }

        return self::orientationCodeFromSize(is_string($size) ? $size : '');
    }

    private static function orderTotalItemsCount($order)
    {
        $total = 0;
        if (is_object($order) && isset($order->items)) {
            $items = json_decode($order->items, true);
            if (is_array($items)) {
                foreach ($items as $key => $item) {
                    if (!is_int($key) || !is_array($item)) {
                        continue;
                    }
                    $total++;
                }
            }
        }

        return $total;
    }

    private static function canvasTypeCodeFromItem($item)
    {
        if (!is_array($item)) {
            return 'C';
        }

        $candidates = [];
        if (isset($item['canvasId'])) {
            $candidates[] = $item['canvasId'];
        }
        if (isset($item['holst_id'])) {
            $candidates[] = $item['holst_id'];
        }
        if (isset($item['canvas'])) {
            $candidates[] = $item['canvas'];
        }
        if (isset($item['show']['canvas'])) {
            $candidates[] = $item['show']['canvas'];
        }

        foreach ($candidates as $candidate) {
            if (is_numeric($candidate)) {
                $holst = \App\Models\GalleryHolst::find((int)$candidate);
                if ($holst) {
                    $type = self::detectCanvasTypeFromText($holst->name ?? '')
                        ?? self::detectCanvasTypeFromText($holst->density ?? '')
                        ?? self::detectCanvasTypeFromText($holst->hint ?? '');
                    if ($type) {
                        return $type;
                    }
                }
                continue;
            }

            $type = self::detectCanvasTypeFromText((string)$candidate);
            if ($type) {
                return $type;
            }
        }

        // Если тип холста не передался/не определился — по умолчанию C.
        return 'C';
    }

    private static function bagetCodeFromItem($item)
    {
        if (!is_array($item)) {
            return 'B0';
        }

        if (isset($item['manual_baget_code'])) {
            $manualBagetCode = strtoupper(trim((string) $item['manual_baget_code']));
            if (in_array($manualBagetCode, ['B0', 'B1', 'B2'], true)) {
                return $manualBagetCode;
            }
        }

        $ramId = $item['ram_id'] ?? null;
        if ($ramId !== null && $ramId !== '' && $ramId !== 'undefined') {
            $ramBagetCode = \App\Models\CanvasRam::productionBagetCode($ramId);
            if ($ramBagetCode !== 'B0') {
                return $ramBagetCode;
            }
        }

        if (array_key_exists('is_manual_baget', $item)) {
            $manualBaget = $item['is_manual_baget'];
            if (
                (is_bool($manualBaget) && $manualBaget) ||
                (is_numeric($manualBaget) && (int)$manualBaget === 1) ||
                (is_string($manualBaget) && in_array(strtolower(trim($manualBaget)), ['1', 'true', 'yes', 'on'], true))
            ) {
                return 'B1';
            }
        }

        if ($ramId === null || $ramId === '' || $ramId === 'undefined') {
            return 'B0';
        }

        return \App\Models\CanvasRam::productionBagetCode($ramId);
    }

    private static function decorationIdFromItem($item)
    {
        if (!is_array($item)) {
            return null;
        }
        if (isset($item['decorationId'])) {
            return $item['decorationId'];
        }
        if (isset($item['decor_id'])) {
            return $item['decor_id'];
        }
        if (isset($item['decorId'])) {
            return $item['decorId'];
        }
        return null;
    }

    private static function giftCodeFromItem($item)
    {
        if (!is_array($item)) {
            return 'G0';
        }

        if (isset($item['manual_gift_code'])) {
            $manualGiftCode = strtoupper(trim((string)$item['manual_gift_code']));
            if (in_array($manualGiftCode, ['G0', 'G1', 'G2'], true)) {
                return $manualGiftCode;
            }
        }

        $candidateIds = [];
        if (isset($item['compl_id'])) {
            $candidateIds[] = $item['compl_id'];
        }
        if (isset($item['boxIds'])) {
            $boxIds = $item['boxIds'];
            if (is_string($boxIds)) {
                $decoded = json_decode($boxIds, true);
                if (is_array($decoded)) {
                    $boxIds = $decoded;
                }
            }
            if (is_array($boxIds)) {
                foreach ($boxIds as $boxId) {
                    $candidateIds[] = $boxId;
                }
            } elseif ($boxIds !== null) {
                $candidateIds[] = $boxIds;
            }
        }

        $maxGift = 0;
        foreach ($candidateIds as $id) {
            $tag = \App\Models\GalleryBox::EXCLUSIVE_IMG_TAGS[intval($id)] ?? null;
            if (!$tag) {
                continue;
            }
            $num = (int)preg_replace('/\D+/', '', (string)$tag);
            if ($num > $maxGift) {
                $maxGift = $num;
            }
        }

        return 'G' . $maxGift;
    }

    private static function brushstrokesCodeFromOrderItem($order, $item)
    {
        if (is_array($item) && isset($item['manual_decoration_id'])) {
            $manualDecorationId = (int)$item['manual_decoration_id'];
            $decorationBrushMap = [
                1 => 'P0',
                2 => 'P1',
                3 => 'P0',
                5 => 'P0',
            ];
            if (isset($decorationBrushMap[$manualDecorationId])) {
                return $decorationBrushMap[$manualDecorationId];
            }
        }

        $catId = 0;
        if (is_object($order) && isset($order->catid)) {
            $catId = intval($order->catid);
        }

        $decorationId = self::decorationIdFromItem($item);
        $brushstrokes = GalleryDecoration::BRUSHSTROKES_IMG_TAGS[intval($decorationId)] ?? 'P0';
        if ($catId && isset(GalleryItem::BRUSHSTROKES_IMG_TAGS[$catId])) {
            $brushstrokes = GalleryItem::BRUSHSTROKES_IMG_TAGS[$catId];
        }

        return $brushstrokes ?: 'P0';
    }

    private static function lacCodeFromItem($item)
    {
        if (!is_array($item)) {
            return 'L0';
        }
        if (isset($item['manual_decoration_id'])) {
            $manualDecorationId = (int)$item['manual_decoration_id'];
            $decorationLacMap = [
                1 => 'L2',
                2 => 'L0',
                3 => 'L1',
                5 => 'L0',
            ];
            if (isset($decorationLacMap[$manualDecorationId])) {
                return $decorationLacMap[$manualDecorationId];
            }
        }
        $decorationId = self::decorationIdFromItem($item);
        return GalleryDecoration::LAC_IMG_TAGS[intval($decorationId)] ?? 'L0';
    }

    private static function orderFlagsFromOrder($order)
    {
        $flags = [
            'express' => 'E0',
            'canvas' => 'S',
            'brushstrokes' => 'P0',
            'lac' => 'L0',
            'baget' => 'B0',
            'gift' => 'G0',
        ];

        if (!$order || !isset($order->items)) {
            return $flags;
        }

        $items = json_decode($order->items, true);
        if (!is_array($items)) {
            return $flags;
        }

        $hasExpress = false;
        $canvasType = null;
        $maxBrush = 0;
        $maxLac = 0; // 0/1/2
        $maxBaget = 0; // 0 = none, 1 = frame, 2 = framed paper
        $maxGift = 0; // 0/1/2

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (self::isExpressItem($item)) {
                $hasExpress = true;
            }

            $itemCanvasType = self::canvasTypeCodeFromItem($item);
            // Приоритет: C (хлопок) важнее, чем G (глянец), затем S.
            if ($canvasType === null) {
                $canvasType = $itemCanvasType;
            } elseif ($itemCanvasType === 'C') {
                $canvasType = 'C';
            } elseif ($itemCanvasType === 'G' && $canvasType === 'S') {
                $canvasType = 'G';
            }

            $p = self::brushstrokesCodeFromOrderItem($order, $item);
            $pNum = (int)preg_replace('/\D+/', '', (string)$p);
            if ($pNum > $maxBrush) {
                $maxBrush = $pNum;
            }

            $l = self::lacCodeFromItem($item);
            $lNum = (int)preg_replace('/\D+/', '', (string)$l);
            if ($lNum > $maxLac) {
                $maxLac = $lNum;
            }

            $bagetCode = self::bagetCodeFromItem($item);
            $bagetNum = (int)preg_replace('/\D+/', '', $bagetCode);
            if ($bagetNum > $maxBaget) {
                $maxBaget = $bagetNum;
            }

            $g = self::giftCodeFromItem($item);
            $gNum = (int)preg_replace('/\D+/', '', (string)$g);
            if ($gNum > $maxGift) {
                $maxGift = $gNum;
            }
        }

        $flags['express'] = $hasExpress ? 'E1' : 'E0';
        $flags['canvas'] = $canvasType ?: 'S';
        $flags['brushstrokes'] = 'P' . $maxBrush;
        $flags['lac'] = 'L' . $maxLac;
        $flags['baget'] = 'B' . $maxBaget;
        $flags['gift'] = 'G' . $maxGift;

        return $flags;
    }

    private static function isExpressOrder($order)
    {
        if (!$order || !isset($order->items)) {
            return false;
        }

        $items = json_decode($order->items, true);
        if (!is_array($items)) {
            return false;
        }

        foreach ($items as $item) {
            if (self::isExpressItem($item)) {
                return true;
            }
        }

        return false;
    }

    public static function generateImageName($order, $item, $count)
    {
        $delimiter = self::DELIMITER_FILENAME;

        $delivery = json_decode($order->delivery, true);
        $brushstrokes = self::brushstrokesCodeFromOrderItem($order, $item);

        $size = null;
        // Приоритет: человеко-читаемый размер (show.size), затем size_name/size, затем sizeId.
        if (!empty($item['show']['size'])) {
            $size = $item['show']['size'];
        } elseif (!empty($item['size_name'])) {
            $size = $item['size_name'];
        } elseif (!empty($item['size'])) {
            $size = $item['size'];
        } elseif (!empty($item['sizeId'])) {
            $size = $item['sizeId'];
        }

        $country = self::safeCountryCodeFromOrder($order, is_array($delivery) ? $delivery : null);
        $deliveryCode = self::deliveryShortCodeFromOrder($order) ?? 'D';
        $expressCode = self::isExpressItem($item) ? 'E1' : 'E0';
        $canvasType = self::canvasTypeCodeFromItem($item);
        $lacCode = self::lacCodeFromItem($item);
        $giftCode = self::giftCodeFromItem($item);
        $bagetCode = self::bagetCodeFromItem($item);
        $orientation = self::orientationCodeFromItem($item, $size ?: '');

        $orderId = $order->id ?? null;
        $qty = 1;
        if (is_array($item) && isset($item['count']) && $item['count'] !== '' && $item['count'] !== 'undefined') {
            $qty = (int)$item['count'];
        } elseif ($count !== null && $count !== false && $count !== '') {
            $qty = (int)$count;
        }
        if ($qty <= 0) {
            $qty = 1;
        }
        $totalItems = self::orderTotalItemsCount($order);
        if ($totalItems <= 0) {
            $totalItems = 1;
        }
        $orderCode = 'N' . ($orderId ?? '') . '-' . $totalItems;
        $sizeWithQty = ($size ?: '0') . '-X' . $qty;

        $data = [
            'order' => $orderCode,
            'size' => $sizeWithQty,
            'express' => $expressCode,
            'delivery' => $deliveryCode,
            'country' => $country,
            'canvas' => $canvasType,
            'lac' => $lacCode,
            'gift' => $giftCode,
            'brushstrokes' => $brushstrokes,
            'baget' => $bagetCode,
            'orientation' => $orientation,
            // старый индикатор доставки (M0/M1) больше не используем
        ];

        $name = implode($delimiter, $data);

        $quotedDelimiter = preg_quote($delimiter, '/');
        $text = preg_replace('/' . $quotedDelimiter . '+/', $delimiter, $name);
        $text = trim($text, $delimiter);
        $text = str_replace("(","", $text);
        $text = str_replace(")","", $text);
        $text = str_replace("/","", $text);
        $text = self::sanitizeFilenamePart($text);

        return $text;
    }

    // Старая версия функции
    public static function getOrderImageName($order,
                                             $client,
                                             $i = false,
                                             $auto_increment = false,
                                             $size = false,
                                             $user = false,
                                             $name = false,
                                             $date = true,
                                             $delimiter = '_')
    {
        $whenSend = null;
        if (is_object($order) && isset($order->delivery) && $auto_increment == false) {
            $delivery = json_decode($order->delivery);
            if (isset($delivery->when_send) && is_string($delivery->when_send) && trim($delivery->when_send) !== '') {
                $whenSend = $delivery->when_send;
            }
        }

        $orderId = null;
        if (is_object($order) && isset($order->id) && $order->id) {
            $orderId = $order->id;
        } elseif ($auto_increment) {
            $orderId = $auto_increment;
        }

        $parts = [];
        $rawQty = 1;
        if ($i !== false && $i !== null && $i !== '') {
            $rawQty = (int)$i;
        }
        if ($rawQty <= 0) {
            $rawQty = 1;
        }
        $totalItems = self::orderTotalItemsCount($order);
        if ($totalItems <= 0) {
            $totalItems = $rawQty;
        }
        $orderCode = 'N' . ($orderId ?? '') . '-' . $totalItems;
        $parts[] = $orderCode;

        $resolvedSize = null;
        $resolvedItem = null;
        if (is_string($size) && trim($size) !== '') {
            $resolvedSize = trim($size);
        } elseif (is_object($order) && isset($order->items)) {
            $items = json_decode($order->items, true);
            if (is_array($items)) {
                $numericKeysCount = count(array_filter(array_keys($items), 'is_int'));
				if ($numericKeysCount === 1) {
					foreach ($items as $key => $item) {
						if (!is_int($key) || !is_array($item)) {
							continue;
						}

                        $resolvedItem = $item;
						if (!empty($item['show']['size'])) {
							$resolvedSize = $item['show']['size'];
						} elseif (!empty($item['size_name'])) {
							$resolvedSize = $item['size_name'];
						} elseif (!empty($item['sizeId'])) {
							$resolvedSize = $item['sizeId'];
						}
						break;
					}
				}
            }
        }
        $resolvedSize = $resolvedSize ?: '0';
        $itemQty = $rawQty;
        if (is_array($resolvedItem) && isset($resolvedItem['count']) && $resolvedItem['count'] !== '' && $resolvedItem['count'] !== 'undefined') {
            $itemQty = (int)$resolvedItem['count'];
        }
        if ($itemQty <= 0) {
            $itemQty = 1;
        }
        $resolvedSizeWithQty = $resolvedSize . '-X' . $itemQty;

        // Требуемая последовательность для производства:
        // {size}_{E0/E1}_{delivery}_{country}_{canvas}_{L0/L1/L2}_{G0/G1/G2}_{P0/P1/P2}_{B0/B1/B2}_{V0/V1/V2/V3/V4}
        $parts[] = $resolvedSizeWithQty;

        if (is_object($order)) {
            $deliveryObj = isset($order->delivery) ? json_decode($order->delivery, true) : null;
            $country = self::safeCountryCodeFromOrder($order, is_array($deliveryObj) ? $deliveryObj : null);
            $deliveryCode = self::deliveryShortCodeFromOrder($order) ?? 'D';
            $flags = self::orderFlagsFromOrder($order);

            $parts[] = $flags['express'];
            $parts[] = $deliveryCode;
            $parts[] = $country;
            $parts[] = $flags['canvas'];
            $parts[] = $flags['lac'];
            $parts[] = $flags['gift'];
            $parts[] = $flags['brushstrokes'];
            $parts[] = $flags['baget'];
        }

        $parts[] = self::orientationCodeFromItem($resolvedItem, $resolvedSize);

        if (is_string($client) && trim($client) !== '') {
            $parts[] = trim($client);
        }

        if (is_string($name) && trim($name) !== '') {
            $cleanName = trim($name);
            $cleanName = str_replace(' ', $delimiter, $cleanName);
            $parts[] = $cleanName;
        }

        if ($whenSend) {
            $parts[] = '(' . $whenSend . ')';
        }

        if ($date) {
            $parts[] = date("dHis");
        }

        $parts = array_values(array_filter($parts, function ($value) {
            return $value !== null && $value !== '';
        }));

        $result = implode($delimiter, $parts);
        $quotedDelimiter = preg_quote($delimiter, '/');
        $result = preg_replace('/' . $quotedDelimiter . '+/', $delimiter, $result);
        $result = self::sanitizeFilenamePart($result);

        return trim($result, $delimiter);
    }

    // Получаем email из формы бастрого заказа "4 шага"
    public static function getFieldPortraitCalc($order, $field)
    {
        if (!is_array($order['items'])) {
            return null;
        }

        foreach ($order['items'] as $product) {
            if (isset($product['content'])) {
                $items = explode('<br>', $product['content']);
                foreach ($items as $item) {
                    $item = trim($item);
                    $item = explode(': ', $item);

                    if ($item[0] == $field) {
                        $field = $item[1];
                    }
                }
            }
        }

        return $field ?? null;
    }

    public static function getNextId()
    {
        $query = DB::select("SHOW TABLE STATUS LIKE 'orders'");
        return $query[0]->Auto_increment;
    }
}
