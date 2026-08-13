<?php

namespace App\Http\Controllers;

use DB;
use App;
use Auth;
use Hash;
use Mail;
use Carbon;
use Config;
use App\Models\User;
use App\Models\Stock;
use App\Models\Orders;
use App\Models\UserMessage;
use Illuminate\Http\Request;
use App\Models\APainterImagesStatus;
use App\Mail\PainterAdminUserComment;
use App\Mail\SendPrainterToUserSketch;
use App\Mail\SendPrainterToUserPicture;
use Illuminate\Support\Facades\Redirect;
use App\Services\UpdatePainterImageService;
use App\Services\SendClientPainterImageService;
use App\Services\UpdatePainterSketchImageService;

class AccountController extends Controller
{
    private $Orders;

    public function __construct()
    {
        $this->Orders = app(Orders::class);
    }

    /**
     * Display user cabinet
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        return redirect()->route('new_account.index');

        if (Auth::user()->role->name != 'painter') {
            $orders = $this->Orders->getOrderByCurrentUser();
            $payed_orders = [];
            $orders_completed = [];
        } else {
            $orders_ids = DB::table('painter_orders')->where('user_id', Auth::id())->pluck('order_id');
            //$orders = Orders::whereIn('id', $orders_ids)->where('painter_payed', null)->with('orders_chats')->paginate(5, ['*'], 'orders');
            $orders = Orders::whereIn('id', $orders_ids)->where('painter_payed', null)->where('status', "!=" , 'completed')->with('orders_chats')->paginate(5, ['*'], 'orders');
            $orders_completed = Orders::whereIn('id', $orders_ids)->where('painter_payed', null)->where('status', 'completed')->with('orders_chats')->paginate(5, ['*'], 'orders_completed');
            $payed_orders = Orders::whereIn('id', $orders_ids)->where('painter_payed', 1)->with('orders_chats')->paginate(5, ['*'], 'payed_orders');
        }

        $sales = Stock::withTranslation(App::getLocale())->first()->select([
            'date_1_sale', 'date_2_sale', 'custom_coupon_sale', 'lk_title',
        ])->get()[0];

		foreach($orders as $order)
		{
			$order->painter_images_status = APainterImagesStatus::getPainterImagesStatus($order->painter_images_status);
			$order->painter_sketch_images_status = APainterImagesStatus::getPainterImagesStatus($order->painter_sketch_images_status);    }
        $cur_user_id=Auth::user()->id;
        $coupons = DB::table('coupons')->where('user_id', $cur_user_id)->where('is_active', 1)->get();



        return view('account', [
            'orders' => $orders,
            'payed_orders' => $payed_orders,
            'orders_completed' => $orders_completed,
            'sales' => $sales,
            'coupons' => $coupons,
        ]);

    }

    /**
     * Update painter images&admin notify
     * @param  \Illuminate\Http\Request                 $request
     * @param  \App\Services\UpdatePainterImageService  $service
     * @return false|string
     */
    public function update_painter_order_images(Request $request, UpdatePainterImageService $service)
    {
        $uploaded_images = $service->store($request);

        $user = DB::table('painter_orders')->where('order_id', $request->order_id)->pluck('user_id')->first();
        $user_mail = DB::table('users')->where('id', $user)->pluck('email')->first();

        // admin notify
        $data['to'] = env('ADMIN_MAIL');
        $data['subject'] = 'Новые картины к заказу №' . $request->order_id;
        $data['content'] = 'Художник: ' . $user_mail;

        Mail::send([], [], function ($message) use ($data) {
            $message->to($data['to']);
            $message->subject($data['subject']);
            $message->setBody($data['content'], 'text/html');
        });

        if (!is_null($uploaded_images)) {
            $cur_images = DB::table('orders')->where('id', $request->order_id)->pluck('painter_images')->first();

            if ($cur_images != '' && $cur_images != null) {
                $cur_images = rtrim($cur_images, ',');
            }

            DB::table('orders')->where('id', $request->order_id)->update([
                'painter_images' => $cur_images . ',' . $uploaded_images,
            ]);

            DB::table('orders')->where('id', $request->order_id)->update([
                'painter_images_status' => 2,
                'painter_images_status_date' => Carbon::now(),
            ]);

			$show_images = DB::table('orders')->where('id', $request->order_id)->where('is_show_painter_images', 1)->pluck('is_show_painter_images')->first();

			if ($show_images != null) {
				$user_id = DB::table('orders')->where('id', $request->order_id)->pluck('user_id')->first();
				$data = [];
				$data['to'] = DB::table('users')->where('id', $user_id)->pluck('email')->first();
				$user = User::where('id', $user_id)->get()->first();
				$msg_data = UserMessage::first()->get()->translate($user->preferredLocale(), 'ru')[0];
				$data['subject'] = $msg_data['new_photo_subject'];
				$data['content'] = $msg_data['new_photo_text'];

                $cur_lng = Config::get('app.locale');
				Mail::to($user->email)->send(new SendPrainterToUserPicture($data, $user->preferredLocale()));
                App::setLocale($cur_lng);
				/*Mail::send([], [], function ($message) use ($data) {
					$message->to($data['to']);
					$message->subject($data['subject']);
					$message->setBody($data['content'], 'text/html');
				});*/
			}

            return json_encode([
                'status' => 1,
                'order_id' => $request->order_id,
                'images' => $cur_images . ',' . $uploaded_images,
            ]);
        }

        return json_encode([
            'status' => 0,
            'order_id' => $request->order_id,
        ]);
    }

    /**
     * Update painter images&admin notify
     * @param  \Illuminate\Http\Request                 $request
     * @param  \App\Services\UpdatePainterSketchImageService  $service
     * @return false|string
     */
    public function update_painter_sketch_order_images(Request $request, UpdatePainterSketchImageService $service)
    {
        $uploaded_images = $service->store($request);

        $user = DB::table('painter_orders')->where('order_id', $request->order_id)->pluck('user_id')->first();
        $user_mail = DB::table('users')->where('id', $user)->pluck('email')->first();

        // admin notify
        $data['to'] = env('ADMIN_MAIL');
        $data['subject'] = 'Новый набросок к заказу №' . $request->order_id;
        $data['content'] = 'Художник: ' . $user_mail;

        Mail::send([], [], function ($message) use ($data) {
            $message->to($data['to']);
            $message->subject($data['subject']);
            $message->setBody($data['content'], 'text/html');
        });

        if (!is_null($uploaded_images)) {
            $cur_images = DB::table('orders')->where('id', $request->order_id)->pluck('painter_sketch_images')->first();

            if ($cur_images != '' && $cur_images != null) {
                $cur_images = rtrim($cur_images, ',');
            }

            DB::table('orders')->where('id', $request->order_id)->update([
                'painter_sketch_images' => $cur_images . ',' . $uploaded_images,
            ]);

            DB::table('orders')->where('id', $request->order_id)->update([
                'painter_sketch_images_status' => 2,
                'painter_sketch_images_status_date' => Carbon::now(),
            ]);

			$show_images = DB::table('orders')->where('id', $request->order_id)->where('is_show_painter_images', 1)->pluck('is_show_painter_images')->first();

			if ($show_images != null) {
				$user_id = DB::table('orders')->where('id', $request->order_id)->pluck('user_id')->first();
				$data = [];
				$data['to'] = DB::table('users')->where('id', $user_id)->pluck('email')->first();
				$user = User::where('id', $user_id)->get()->first();
				$msg_data = UserMessage::first()->get()->translate($user->preferredLocale(), 'ru')[0];
				$data['subject'] = $msg_data['new_photo_subject'];
				$data['content'] = $msg_data['new_photo_text'];

                $cur_lng = Config::get('app.locale');
				Mail::to($user->email)->send(new SendPrainterToUserSketch($data, $user->preferredLocale()));
                App::setLocale($cur_lng);
				/*
				Mail::send([], [], function ($message) use ($data) {
					$message->to($data['to']);
					$message->subject($data['subject']);
					$message->setBody($data['content'], 'text/html');
				});*/
			}

            return json_encode([
                'status' => 1,
                'order_id' => $request->order_id,
                'images' => $cur_images . ',' . $uploaded_images,
            ]);
        }

        return json_encode([
            'status' => 0,
            'order_id' => $request->order_id,
        ]);
    }

    /**
     * Painter update order comment
     * @param  \Illuminate\Http\Request  $request
     * @return false|string
     */
    public function update_painter_comment(Request $request)
    {
        $updated = DB::table('order_painter_comments')->insert([
            'comment' => $request->client_comment,
            'order_id' => $request->order_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $user = DB::table('painter_orders')->where('order_id', $request->order_id)->pluck('user_id')->first();
        $user_mail = DB::table('users')->where('id', $user)->pluck('email')->first();

        $data['to'] = env('ADMIN_MAIL');
        $data['subject'] = 'Комментарий к заказу №' . $request->order_id;
        $data['content'] = 'Художник: ' . $user_mail;

        Mail::send([], [], function ($message) use ($data) {
            $message->to($data['to']);
            $message->subject($data['subject']);
            $message->setBody($data['content'], 'text/html');
        });

        return json_encode([
            'status' => $updated,
            'comment' => $request->client_comment,
            'order_id' => $request->order_id,
        ]);
    }

    /**
     * @param  \Illuminate\Http\Request                     $request
     * @param  \App\Services\SendClientPainterImageService  $service
     * @return false|string
     */
    public function send_client_painter_comments(Request $request, SendClientPainterImageService $service)
    {
        $comment_images = null;
        $isImageThreadComment = (bool) (
            $request->order_painter_image_id
            || $request->is_img_painter
            || $request->is_img_sketch
        );

        if ($request->hasFile('client_images')) {
            $up_images = $service->store($request, !$isImageThreadComment);
            $client_images_urls = $isImageThreadComment
                ? DB::table('orders')->where('id', $request->order_id)->pluck('client_images')->first()
                : $up_images['client_images_urls'];
            $comment_images = $up_images['comment_images'];
        } else {
            $client_images_urls = DB::table('orders')->where('id', $request->order_id)->pluck('client_images')->first();
        }

        $client_images_urls = rtrim($client_images_urls, ',');

        if (!$isImageThreadComment) {
            DB::table('orders')->where('id', $request->order_id)->update([
                'client_images' => $client_images_urls,
            ]);
        }

        $updated_comments = DB::table('order_user_comments')->insert([
            'comment' => $request->client_comment,
            'order_id' => $request->order_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'user_id' => Auth::id(),
        ]);

        $user_id = DB::table('orders')->where('id', $request->order_id)->pluck('user_id')->first();
        $user = User::where('id', $user_id)->get()->first();
        $msg_data = UserMessage::first()->get()->translate($user->preferredLocale(), 'ru')[0];

        $data['subject'] = $msg_data['user_painter_mail_subject'] . ' ' . $request->order_id;
        $data['text'] = $request->client_comment;

        $data['admin_email'] = env('ADMIN_MAIL');
        $data['comment_images'] = $comment_images;

        $painter_id = DB::table('painter_orders')->where('order_id', $request->order_id)->pluck('user_id')->first();

        // notify
        if ($painter_id) {
            $data['painter_email'] = DB::table('users')->where('id', $painter_id)->pluck('email')->first();
            Mail::to($data['admin_email'])->send(new AdminToUserComment($data, $user->preferredLocale()));
            Mail::to($data['painter_email'])->send(new AdminToUserComment($data, $user->preferredLocale()));
        } else {
            Mail::to($data['admin_email'])->send(new AdminToUserComment($data, $user->preferredLocale()));
        }

        return json_encode([
            'status' => $updated_comments,
            'order_id' => $request->order_id,
            'comment' => $request->client_comment,
            'images' => $client_images_urls,
        ]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return false|string
     */
    public function update_order_chat(Request $request)
    {
        $data = [];
        $data['user_email'] = env('ADMIN_MAIL');
        $msg_data = UserMessage::first()->get()->translate('ru')[0];
        $subject = $msg_data['admin_user_chat_title'];
        $data['subject_send'] = str_replace('{order_id}', $request->order_id, $subject);
        $data['content'] = $request->painter_msg;

        Mail::send([], [], function ($message) use ($data) {
            $message->to($data['user_email']);
            $message->subject($data['subject_send']);
            $message->setBody($data['content'], 'text/html');
        });

        $updated = DB::table('orders_chats')->insert([
            'comment' => $request->painter_msg,
            'orders_id' => $request->order_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'is_admin' => 0,
        ]);

        return json_encode(['status' => $updated]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User          $user
     * @return false|string
     */
    public function ajax_change_information(Request $request, User $user)
    {
        $pass_result = false;

        if (!empty($request->input('old_password')) || !empty($request->input('password'))) {
            $pass_result = $user->change_password([
                'id' => Auth::id(),
                'password' => Hash::make($request->input('password')),
            ]);
        }

        if(isset($request['phone']))
        {
            $request['phone'] = str_replace(" ","", $request['phone']);
            $request['phone'] = str_replace(")","", $request['phone']);
            $request['phone'] = str_replace("(","", $request['phone']);
            $request['phone'] = str_replace("-","", $request['phone']);
        }

        $info_result = $user->change_contact_info([
            'id' => Auth::id(),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'phone' => $request->input('phone'),
            'news' => $request->input('news'),
            'ad' => $request->input('ad'),
            'client_data' => $request->input('client_data'),
        ]);

        if ($info_result || $pass_result) {
            return json_encode(['status' => 'ok', 'message' => '']);
        }
    }

    public function ajax_check_user(Request $request)
    {
        // Get the email value sent in the POST request
        $email = $request->input('email');
 if ($request->input('form')==1) {
     $users = DB::table('users')
          ->where('email', 'like', '%' . $email . '%')
          ->get();
 }
 if ($request->input('form')==2) {
            $users = DB::table('users')
                ->where('email', $email)
                ->get();
        }
        return response()->json($users);
    }

}
