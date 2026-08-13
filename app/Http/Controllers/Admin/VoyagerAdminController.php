<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\Sale30_40;
use App\Mail\SendUserSale;
use App\Models\Locale as Loc;
use App\Models\Orders;
use App\Models\OurWork;
use App\Models\PainterOrder;
use App\Models\User;
use App\Models\UserMessage;
use App\Services\SynvolveWebhookService;
use Artisan;
use Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;
use Storage;
use URL;

class VoyagerAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function header_menu_items(Request $request)
    {
        $locales = Loc::all();

        return view('admin.header_menu_items', ['locales' => $locales]);
    }

    public function user_filter()
    {
        $users = User::where('role_id', 2)->get();

        return view('vendor.voyager.user_filter')->with('users', $users);
    }

    public function set_sub_cats(Request $request)
    {
        return DB::table('gallery_categories')->where('id_type', $request->input('cat'))->get();
    }

    public function delete_order_image(Request $request)
    {
        $th = $this;
        $order_id = $request->order_id;
        $img_url = $request->img_url;
        $order = DB::table('orders')->where('id', $order_id)->get()->first();
        $order = (array) $order;
        $order['items'] = json_decode($order['items'], true);
        $is_deleted_file = 0;

        foreach ($order['items'] as $product) {
            if (!isset($product['sumPrice'])) {
                continue;
            }

            if ($product['orig_images']) {
                $data['orig_images'] = [];

                $x = -1;
                $pr = -1;

                foreach ($product['orig_images'] as $img) {
                    $pr++;

                    if ($img == $img_url) {
                        if ($th->does_url_exists($img)) {
                            $is_delete = @unlink($img);
                            $is_deleted_file = 1;
                            continue;
                        }
                    }

                    $x++;
                    $data['orig_images'][$x] = $img;
                }

                if ($is_deleted_file == 1) {
                    $orig_imgs = implode(',', $data['orig_images']);
                    dd($orig_imgs);
                    $order = Orders::find($order_id);
                    $items = json_decode($order->items, true);
                    $items['orig_images'][$pr] = json_encode($orig_imgs);
                    $order->save();
                }
            }
        }

        return back();
    }

    public function does_url_exists($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($code == 200) {
            $status = true;
        } else {
            $status = false;
        }
        curl_close($ch);

        return $status;
    }
    public function update_admin_chat_ajax(Request $request)
    {

        $message = $request->admin_msg;
        $order_id = $request->order_id;
        $userId = Auth::id();


        DB::table('order_admin_comments')->insert([
            'comment' => $message,
            'orders_id' => $order_id,
            'user_id'=> $userId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = [];
        $response['success'] = 1;
        $response['message'] = $message;

        return json_encode($response);
    }
    public function update_order_chat_ajax(Request $request)
    {
        $message = $request->painter_msg;
        $order_id = $request->order_id;
        $user_id = DB::table('painter_orders')->where('order_id', $order_id)->pluck('user_id')->first();
        $user = $user_id ? User::find($user_id) : null;
        $locale = 'ru';

        if ($user && is_string($user->preferredLocale()) && $user->preferredLocale() !== '') {
            $locale = $user->preferredLocale();
        }

        $msgData = UserMessage::first();
        $subject = 'Сообщение по заказу #' . $order_id;

        if ($msgData) {
            $translated = $msgData->get()->translate($locale, 'ru')[0] ?? null;

            if (!empty($translated['admin_user_chat_title'])) {
                $subject = str_replace('{order_id}', $order_id, $translated['admin_user_chat_title']);
            }
        }

        $userEmail = $user ? $user->email : null;

        if ($userEmail) {
            $data = [];
            $data['subject_send'] = $subject;
            $data['user_email'] = $userEmail;
            $data['content'] = $message;

            Mail::send([], [], function ($message) use ($data) {
                $message->to($data['user_email']);
                $message->subject($data['subject_send']);
                $message->setBody($data['content'], 'text/html');
            });
        }

        $insertData = [
            'comment' => $message,
            'orders_id' => $order_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'is_admin' => 1,
        ];

        if( isset($request['image_type']) && $request['image_type']) {
            $insertData[$request['image_type']] = 1;
        }

        DB::table('orders_chats')->insert($insertData);

        app(SynvolveWebhookService::class)->notifyManagerMessageForOrder((int) $order_id, (string) $message, [
            'trigger' => 'legacy_admin_orders_chat',
            'source' => 'orders_chats',
            'image_type' => $request['image_type'] ?? null,
        ]);

        $response = [];
        $response['success'] = 1;
        $response['message'] = $message;

        return json_encode($response);
    }

    public function tiny_upload(Request $request)
    {
        $file = $request->file('file');
        $path = url('/uploads/') . '/' . $file->getClientOriginalName();
        $file->move(public_path('/uploads/'), $file->getClientOriginalName());
        $fileNameToStore = $path;

        return json_encode(['location' => $fileNameToStore]);
    }

    public function upload_audio_rev(Request $request)
    {
        $file = $request->audio_upload;

        if ($file) {
            $file_name = Storage::disk('uploads')->put('uploads', $file);
            $file_url = URL::to('/') . '/' . $file_name;

            // update item with locale
            $item = OurWork::where('id', $request->item_id)->first();
            $item = $item->translate($request->lang);
            $item->a_player = $file_url;
            $item->save();

            return response()->json([
                'file' => $file_url,
                'file_name' => $file_name,
                'file_url' => $file_url,
                'item' => $item,

            ]);
        } else {
            return response()->json([
                'file' => 'no file',
            ]);
        }
    }

    public function cache_clear()
    {
        Artisan::call('cache:clear');

        return redirect()->back();
    }

    public function cache_set()
    {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        return redirect()->back();
    }

    public function user_notify()
    {
        Artisan::call('UserNotify:cron');
        return redirect()->back();
    }


    // TODO: facebook сама функция которая записывает в базу одобрение скриншота и отправляет письмо
    public function give_facebook_sale_mail($cur_user_id, $side)
    {
        /// $side Акция 1 - фейсбук, 2 - картина 30х40
        ///
        $usr_email = User::where('id', $cur_user_id)->pluck('email')->first();
        // Update user table where id = $cur_user_id set is_facebook_sale = $sale
        // User::where('id', $cur_user_id)->update(['is_facebook_sale' => 1]);
        $usr_name = User::where('id', $cur_user_id)->pluck('first_name')->first();
            // послать пользователю письмо с купоном
            $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
            $coupon_code = mb_substr(str_shuffle($rand_code), 0, 10);

            if ($side==1){
                DB::table('coupons')->insert([
                    'text' => $coupon_code,
                    'is_facebook' => 1,
                    'is_active' => 1,
                    'user_id' => $cur_user_id,
                ]);
                $data['subject']='Viar sale facebook';
                $data['coupon_code'] = $coupon_code;
                $data['first_name']=$usr_name;
                \Mail::to($usr_email)->send(new \App\Mail\SaleFacebook($data));
            }
        if ($side==2){
            DB::table('coupons')->insert([
                'text' => $coupon_code,
                'is_30_40_free' => 1,
                'is_active' => 1,
                'user_id' => $cur_user_id,
            ]);
            $data['subject']='Viar sale 30x40';
            $data['coupon_code'] = $coupon_code;
            $data['first_name']=$usr_name;
            \Mail::to($usr_email)->send(new \App\Mail\Sale30_40($data));
        }







    }

    // TODO: facebook одобрить скришнот по ссылке из письма
    public function give_user_sale(Request $request)
    {
        $side=$request->sale;  /// Акция 1 - фейсбук, 2 - картина 30х40

        if (Auth::check()) {
            $user = \Auth::user();
            if ($user->role->name == 'admin' || $user->role->name == 'manager') {
                $id = $request->id;
                // сохранить у пользователя скидку
                if ($side == 1) {
                    $user = DB::table('users')->where('id', $id)->update([
                        'is_facebook_sale' => 1,
                    ]);
                }
                if ($side == 2) {
                    $user = DB::table('users')->where('id', $id)->update([
                        'is_30_40' => 1,
                    ]);
                }


                if ($user == 1) {
                    $mail = User::where('id', $id)->pluck('email')->first();
                    $cur_user = User::where('id', $id)->first();

                    $this->give_facebook_sale_mail($id,$side);
        }
                    //Mail::to($mail)->send(new SendUserSale($cur_user));

                    echo "<script>alert('Скидка успешно выдана');document.location.href='/';</script>";
                } else {
                    echo "<script>alert('У пользователя уже есть скидка');document.location.href='/';</script>";
                }
            } else {
                return abort('404');
            }

    }

    // TODO: facebook отказать в скидке по ссылке из письма
    public function cancel_user_sale(Request $request)
    {
        $side=$request->sale;

        if (Auth::check()) {
            $user = \Auth::user();
            if ($user->role->name == 'admin' || $user->role->name == 'manager') {
                $id = $request->id;

                if ($side==1) {

                    $user = DB::table('users')->where('id', $id)->update([
                        'is_facebook_sale' => 0,
                    ]);
                    $mail = User::where('id', $id)->pluck('email')->first();
                    $cur_user = User::where('id', $id)->first();

                    //$data['coupon_code'] = $coupon_code;
                    $data['subject']='Viar facebook Deny';
                    $data['first_name']=$cur_user->first_name;

                    Mail::to($mail)->send(new \App\Mail\SaleFacebookDeny($data));
                }
                if ($side==2) {

                    $user = DB::table('users')->where('id', $id)->update([
                        'is_30_40' => 0,
                    ]);
                    $mail = User::where('id', $id)->pluck('email')->first();
                    $cur_user = User::where('id', $id)->first();

                    //$data['coupon_code'] = $coupon_code;
                    $data['subject']='Viar facebook Deny';
                    $data['first_name']=$cur_user->first_name;

                    Mail::to($mail)->send(new \App\Mail\Sale30_40_deny($data));
                }

                echo "<script>alert('Скидка не подтверждена');document.location.href='/';</script>";
            } else {
                return abort('404');
            }
        } else {
            return abort('404');
        }
    }



    public function set_sale30_40(Request $request)
    {

        if (Auth::check()) {
            $user = \Auth::user();

            if ($user->role->name == 'admin'  || $user->role->name == 'manager') {
                $id = $request->id;

                $user_to = User::where('id', $id)->get()->first();

                // послать пользователю письмо с купоном
                $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
                $coupon_code = mb_substr(str_shuffle($rand_code), 0, 10);
                DB::table('coupons')->insert([
                    'text' => $coupon_code,
                    'is_30_40_free' => 1,
                ]);


                $data['subject']='Viar sale 30 40';
                $data['first_name']=$user_to->first_name;
                $data['coupon_code'] = $coupon_code;

                Mail::to($user_to->email)->send(new Sale_30_40_new($data));

                echo "<script>alert('Купон на скидку 30x40 успешно выдан пользователю');document.location.href='/';</script>";
            } else {
                echo "<script>alert('Вы должны быть администратором');document.location.href='/';</script>";
            }
        } else {
            echo "<script>alert('Вы должны быть залогинены');document.location.href='/';</script>";
        }



    }


    public function remove_order_painter(Request $request)
    {
        $order_id = $request->order_id;
        $painter_id = $request->painter_id;

        DB::table('painter_orders')->where(
            [
                'order_id' => $order_id,
                'user_id' => $painter_id,
            ]
        )->delete();

        $response['info'] = 1;
        $response['success'] = 'Художник удален с заказа!';

        return back()->with('message', $response['success']);
    }

    public function remove_order_printing(Request $request)
    {
        $order_id = $request->order_id;
        $printing_id = $request->printing_id;

        DB::table('printing_orders')->where(
            [
                'order_id' => $order_id,
                'user_id' => $printing_id,
            ]
        )->delete();

        $response['info'] = 1;
        $response['success'] = 'Менеджер печати с заказа!';

        return back()->with('message', $response['success']);
    }

    public function update_painter_orders(Request $request)
    {
        $is_removed = 0;
        $cur_orders = $request->orders;

        if ($cur_orders == '') {
            $is_removed = 1;
        }

        $cur_orders_arr = explode(',', $cur_orders);
        if ($is_removed == 0) {
            // update or create used
            foreach ($cur_orders_arr as $order_id) {
                PainterOrder::updateOrCreate([
                    'order_id' => $order_id,
                    'user_id' => $request->user_id,
                ]);
            }

            // clear unused
            PainterOrder::whereNotIn('order_id', $cur_orders_arr)->where('user_id', $request->user_id)->delete();
        } else {
            DB::table('painter_orders')->where('user_id', $request->user_id)->remove();
        }

        return json_encode(
            [
                'succes' => 1,
                'is_remove_from_orders' => $is_removed,
            ]
        );
    }
}
