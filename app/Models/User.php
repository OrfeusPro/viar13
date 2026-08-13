<?php

namespace App\Models;

use App\Entity\UserType;
use App\Notifications\AdminMailNotification;
use App\Notifications\BrandedResetPassword;
use App\Models\Orders;
use DB;
use Eloquent;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Throwable;

class User extends \TCG\Voyager\Models\User implements HasLocalePreference
{
    use Notifiable;

    const IMAGE_FOLDER = 'public/user_images/';

    protected $fillable = [
        'invited', 'email', 'password', 'first_name',
        'last_name', 'ad', 'client_data', 'news', 'phone',
        'type_id', 'settings', 'screenshot', 'invited', 'bonuses', 'inv_sale_code', 'is_facebook_sale', 'facebook_id',
        'registration_page',
        'referrer_url',
        'utm_parameters',
        'user_agent',
        'last_ip',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'utm_parameters'    => 'array',
        'last_ip'           => 'string',
        'user_agent'        => 'string',
    ];

    public static function get_painter_orders($user_id)
    {
        $orders_ids = \App\Models\Orders::where('status', '!=', 'completed')->orderBy(
            'created_at',
            'desc'
        )->pluck('id');

        return $orders_ids;
    }

    public static function get_user_acc_comments($order_id, $img_type = null)
    {
        $comments = DB::table('order_user_comments')->where('order_id', $order_id)->orderBy(
            'created_at',
            'asc'
        )->get();

        if($img_type)
        {
            $comments = DB::table('order_user_comments')->where('order_id', $order_id)->where($img_type, 1)->orderBy(
                'created_at',
                'asc'
            )->get();
        }

        return $comments;
    }

    public static function get_user_comments_total($order_id)
    {
        $comments = DB::table('order_user_comments')->where('order_id', $order_id)->where("is_img_sketch", 0)->where("is_img_painter", 0)->orderBy(
            'created_at',
            'asc'
        )->get();

        return $comments;
    }

    public static function get_painter_all_comments($order_id)
    {
        $comments = DB::table('order_painter_comments')->where('order_id', $order_id)->orderBy(
            'created_at',
            'desc'
        )->get();

        return $comments;
    }

    public static function get_painter_selected_orders($user_id)
    {
        $orders_ids = DB::table('painter_orders')->where('user_id', $user_id)->orderBy(
            'created_at',
            'desc'
        )->pluck('order_id')->toArray();

        return $orders_ids;
    }

    public function setPhoneAttribute($value)
    {
        $value = str_replace(" ","", $value);
        $value = str_replace(")","", $value);
        $value = str_replace("(","", $value);
        $value = str_replace("-","", $value);
        $this->attributes['phone'] = $value;
    }

    public static function getCouponNominal($id)
    {
        return DB::table('coupons')->where('id', $id)->pluck('value')->first();
    }

    public static function getCouponValById($id)
    {
        return \App\Models\Coupon::where('id', $id)->pluck('text')->first();
    }

    public static function getPainterByOrderId($order_id)
    {
        $painter_id = DB::table('painter_orders')->where('order_id', $order_id)->pluck('user_id')->first();

        if ($painter_id) {
            $user = DB::table('users')->where('id', $painter_id)->first();

            if ($user) {
                return $user;
            }

            return '';
        }

        return '';
    }

    public static function getPrintingByOrderId($order_id)
    {
        $printing_id = DB::table('printing_orders')->where('order_id', $order_id)->pluck('user_id')->first();

        if ($printing_id) {
            $user = DB::table('users')->where('id', $printing_id)->first();

            if ($user) {
                return $user;
            }

            return '';
        }

        return '';
    }

    public static function getRoles()
    {
        return UserType::all();
    }

    public static function getUserStatusById($id)
    {
        try {
            $status = User::where('id', $id)->pluck('client_status')[0];
        } catch (Throwable $th) {
            $status = null;
        }

        return $status;
    }

    public static function getUserLastOrderId($user_id)
    {
        $order_id = \App\Models\Orders::where('user_id', $user_id)->pluck('id')->first();

        return $order_id;
    }

    public static function getUserLastCompletedOrderId($user_id)
    {
        
        $order_id = \App\Models\Orders::where('user_id', $user_id)->where('status', 'completed')->pluck('id')->first();

        return $order_id;
    }

    public function preferredLocale()
    {
        return $this->locale;
    }

    public function sendPasswordResetNotification($token)
    {
        $locale = app()->getLocale();
        $supportedLocales = array_keys(config('laravellocalization.supportedLocales', []));

        if (!in_array($locale, $supportedLocales, true)) {
            $locale = app('laravellocalization')->getDefaultLocale();
        }

        $this->notify((new BrandedResetPassword($token))->locale($locale));
    }

    public function DPFLocale()
    {
        return $this->pdf_locale;
    }

    public function type()
    {
        return $this->belongsTo(UserType::class, 'type_id');
    }

    public function change_password(array $data)
    {
        $result = User::where('id', $data['id'])->update(['password' => $data['password']]);

        return $result;
    }

    public function change_contact_info(array $data)
    {
        $result = User::where('id', $data['id'])
            ->update([

                'first_name' => $data['first_name'],

                'last_name' => $data['last_name'],

                'phone' => $data['phone'],

                'news' => $data['news'],

                'ad' => $data['ad'],

                'client_data' => $data['client_data'],

            ]);

        return $result;
    }

    public function sendAdminMail($data)
    {
        $this->notify(new AdminMailNotification(
            $data->subject,
            $data->greetings,
            $data->line,
            $data->salutation
        ));
    }

    public function getOrdersCount($user_id) {
        $count = Orders::where('user_id', $user_id)->count();

        return $count;
    }

    protected static function boot()
    {
        parent::boot();

        // при создании
        static::creating(function (User $user) {
            $req = request();

            $user->registration_page = $user->registration_page
                ?? session('registration_page')
                ?? $req->fullUrl();

            $user->referrer_url = $user->referrer_url
                ?? session('referrer_url')
                ?? $req->headers->get('referer');

            $user->utm_parameters = $user->utm_parameters
                ?? session('utm_parameters', [])
                ?? $req->only([
                    'utm_source','utm_medium','utm_campaign','utm_term','utm_content'
                ]);

            $user->user_agent = $user->user_agent
                ?? $req->header('User-Agent');
        });

        // при любом save() (create и update) обновляем IP
        static::saving(function (User $user) {
            $user->last_ip = request()->ip();
        });
    }
}
