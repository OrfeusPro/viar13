<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
// напоминание о акции 2 даты

class UserNotifyCron extends Command
{
    /**

     * The name and signature of the console command.

     *

     * @var string

     */
    protected $signature = 'UserNotify:cron';

    /**

     * The console command description.

     *

     * @var string

     */
    protected $description = 'Notify users cron task';

    /**

     * Create a new command instance.

     *

     * @return void

     */
    public function __construct()
    {
        parent::__construct();
    }

    /**

     * Execute the console command.

     *

     * @return mixed

     */

    public function handle()
    {
        $coupons = \DB::table('coupons')
            ->join('users', 'coupons.user_id', '=', 'users.id')
            ->where('coupons.is_dates_sale', 1)
            ->where('coupons.is_active', 1)
            ->select('coupons.sale_date', 'users.email','coupons.text','users.first_name','coupons.sale_date','users.torj1','users.torj2','users.date1','users.date2')
            ->get();

        $today = Carbon::today();

        foreach ($coupons as $coupon)
        {
            $log= '';
            $couponDate = Carbon::parse($coupon->sale_date);

            if ($couponDate->diffInDays($today) == 5) {
                $data['coupon_code'] = $coupon->text;
                $data['2dates_subject']='Viar sale 2 dates';
                $data['first_name']=$coupon->first_name;
                $data['sale_date']=$coupon->sale_date;

                if ($coupon->sale_date==$coupon->date1){ $data['text']= $coupon->torj1; }
                if ($coupon->sale_date==$coupon->date2){ $data['text']= $coupon->torj2; }


                $sended= \Mail::to($coupon->email)->send(new \App\Mail\Sale2dates($data));

                if ($sended) {
                    $log .= 'Пользователь: ' . $coupon->email . ' Получил макет с предолжением на 2 даты<br> |';
                } else {
                    $log .= 'Пользователь: ' . $coupon->email . ' Ошибка отправки email-а<br> |';
                }
            //    Send to activity log
                \DB::table('order_action')->insert(
                    [
                        'user'     => $coupon->email,
                        'activity' => $log,
                    ]
                );
            }
        }

            }



}

