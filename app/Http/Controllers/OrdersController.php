<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Libwebtopay\WebToPay;
use App\Services\Payment\PayPal\OneTimePayPalService;
use DB;
use App;
use URL;
use File;
use Mail;
use Config;
use Cookie;
use Session;
use Storage;
use Redirect;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Coupon;
use App\Models\Orders;
use App\Models\GalleryBox;
use App\Models\GalleryItem;
use App\Models\GalleryHolst;
use App\Models\GalleryDecoration;
use App\Models\SendRev;
use App\Models\AdminChats;
use App\Models\AOrderFrom;
use App\Models\CountryTel;
use App\Models\GlobConfig;
use App\Models\OrdersChats;
use App\Models\OrderString;
use App\Models\OrderPaymentRequest;
use App\Models\UserMessage;
use App\Mail\SendUserReview;
use App\Models\PainterOrder;
use Illuminate\Http\Request;
use App\Mail\SendUserWeCheck;
use App\Models\AbandonedCart;
use App\Models\PrintingOrder;
use App\Models\OrderPainterImages;
use App\Models\APainterImagesStatus;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use App\Mail\ApproveUserCheckoutMail;
use App\Mail\SendUserYourOrderWasSend;
use App\Repositories\BasketRepository;
use App\Mail\SendPrainterToUserPicture;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\GiftcardController;
use App\Notifications\ThanksForBuyNotification;
use App\Services\SynvolveWebhookService;
use App\Support\CheckoutPaymentMethods;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Libwebtopay\PayseraController;

class OrdersController extends Controller
{
    private $Orders;
    private $DynamicPDF;
    private $basketRepository;
    private $paypalService;

    public function __construct()
    {
        $this->Orders = app(Orders::class);
        $this->DynamicPDF = app(DynamicPDFController::class);
        $this->basketRepository = resolve(BasketRepository::class);
        $this->payseraController = resolve(PayseraController::class);
        $this->paypalService = resolve(OneTimePayPalService::class);
        $this->gfc = resolve(GiftcardController::class);
    }

    private function rus2translit($string)
    {
        $converter = [

            'а' => 'a', 'б' => 'b', 'в' => 'v',

            'г' => 'g', 'д' => 'd', 'е' => 'e',

            'ё' => 'e', 'ж' => 'zh', 'з' => 'z',

            'и' => 'i', 'й' => 'y', 'к' => 'k',

            'л' => 'l', 'м' => 'm', 'н' => 'n',

            'о' => 'o', 'п' => 'p', 'р' => 'r',

            'с' => 's', 'т' => 't', 'у' => 'u',

            'ф' => 'f', 'х' => 'h', 'ц' => 'c',

            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',

            'ь' => '\'', 'ы' => 'y', 'ъ' => '\'',

            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',

            'А' => 'A', 'Б' => 'B', 'В' => 'V',

            'Г' => 'G', 'Д' => 'D', 'Е' => 'E',

            'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',

            'И' => 'I', 'Й' => 'Y', 'К' => 'K',

            'Л' => 'L', 'М' => 'M', 'Н' => 'N',

            'О' => 'O', 'П' => 'P', 'Р' => 'R',

            'С' => 'S', 'Т' => 'T', 'У' => 'U',

            'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',

            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',

            'Ь' => '\'', 'Ы' => 'Y', 'Ъ' => '\'',

            'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',

        ];

        return strtr($string, $converter);
    }

    /// Фунция для страницы /admin/orders
    public function index(Request $request, IndexController $IndexController)
    {
        $auth_user = \Auth::user();
        $cur_user_id = Auth::id();

        if ($auth_user->role->name == 'printing') {
            if(!$request->printing && !$request->sent_today && !$request->express && !$request->order_filter && !$request->user_id)
            {
                //dd(\Route::currentRouteName());
                // return redirect()->route("voyager.orders.index", ["printing"=>1]); //редирект ждя прав "Менеджер печати"
            }

            // dd($request->all());
        }



        $style = $IndexController->get_styles_for_quiz('ru');
		$summ=0;
		$params = $request->query();
        $orderIdSort = in_array($request->get('order_id_sort'), ['desc', 'asc'], true)
            ? $request->get('order_id_sort')
            : null;
		$calc_summ=false;

		$managers = DB::table('users')
                        ->where('role_id', 4)
                      //  ->orWhere('role_id', 1)
						->orderBy('role_id', 'desc')
						->get();
		$m=array();
		foreach ($managers as $manager) {
				$m[] = $manager->id;
		}



        if (!empty($request['order_filter'])) {

            $orders = Orders::where(function ($q) use ($request, $m) {
                // order id
                if ($request->has('order_id') && ($request->get('order_id') != null) && ($request->get('order_id') != 0)) {
                    $q->where('orders.id', $request->get('order_id'));
                }

                if ($request->has('filter_payment_request_number') && trim((string) $request->get('filter_payment_request_number')) !== '') {
                    $paymentRequestNumber = strtoupper(trim((string) $request->get('filter_payment_request_number')));
                    $paymentRequestOrderIds = OrderPaymentRequest::query()
                        ->where('public_number', 'like', '%' . $paymentRequestNumber . '%')
                        ->pluck('order_id')
                        ->unique()
                        ->values()
                        ->all();

                    $q->whereIn('orders.id', $paymentRequestOrderIds ?: [0]);
                }

                if (($request->has('filter_customer') && ($request->get('filter_customer') != null) && ($request->get('filter_customer') != null)) || $request->get('user_id')>0) {
                    $user_filter_data = $request->get('filter_customer');
                    $has_vr_filter = 0;

                    if (strpos($user_filter_data, 'BAW') !== false) {
                        $has_vr_filter = 1;
                        $user_filter_data_mod = str_replace('BAW', '', $user_filter_data);
                        $finded_order_id = DB::table('vr_numbers')->where('vrv_2', $user_filter_data_mod)
                            ->pluck('order_id')->first();

                        $q->where('orders.id', $finded_order_id);
                    }

                    if (strpos($user_filter_data, 'VRR445') !== false) {
                        $has_vr_filter = 1;
                        $user_filter_data_mod = str_replace('VRR445', '', $user_filter_data);
                        $finded_order_id = DB::table('vr_numbers')->where('vrv_3', $user_filter_data_mod)
                            ->pluck('order_id')->first();

                        $q->where('orders.id', $finded_order_id);
                    }

                    if (strpos($user_filter_data, 'VR00') !== false) {
                        $has_vr_filter = 1;
                        $user_filter_data_mod = str_replace('VR00', '', $user_filter_data);
                        $finded_order_id = DB::table('vr_numbers')->where('vrv_1', $user_filter_data_mod)
                            ->pluck('order_id')->first();

                        $q->where('orders.id', $finded_order_id);
                    }

                    if (strpos($user_filter_data, 'DS020') !== false) {
                        $has_vr_filter = 1;
                        $user_filter_data_mod = str_replace('DS020', '', $user_filter_data);
                        $finded_order_id = DB::table('vr_numbers')->where('vrv_4', $user_filter_data_mod)
                            ->pluck('order_id')->first();

                        $q->where('orders.id', $finded_order_id);
                    }

                    if ($has_vr_filter == 0) {
                        if ($request->get('user_id')>0){
                           $user_id=$request->get('user_id');
                            $user_data = DB::table('users')
                                ->where('id', $user_id)
                                ->pluck('id')->toArray();
                            $contact_filters=$user_data;
                        } else {
                            $user_data1 = DB::table('users')
                                ->where('email', $user_filter_data)
                                ->orWhere('id', $user_filter_data)
                                ->orWhere('first_name', 'like', '%' . $user_filter_data . '%')
                                ->orWhere('first_name', 'like', '%' . $this->rus2translit($user_filter_data) . '%')
                                ->orWhere('last_name', 'like', '%' . $user_filter_data . '%')
                                ->orWhere('last_name', 'like', '%' . $this->rus2translit($user_filter_data) . '%')
                                ->orWhere('phone', 'like', '%' . $user_filter_data . '%')
                                ->orWhere('address', 'like', '%' . $user_filter_data . '%')
                                ->orWhere('postal_index', $user_filter_data)
                                ->orWhere('country', $user_filter_data)
                                ->pluck('id')->toArray();


                        $user_data2 = DB::table('orders')
                            ->whereJsonContains('delivery->email', $user_filter_data)
                            ->orWhere('price', $user_filter_data)
                            ->orWhere('sale_price', $user_filter_data)
                            ->orWhereJsonContains('delivery->first_name', $user_filter_data)
                            ->orWhereJsonContains('delivery->first_name', $this->rus2translit($user_filter_data))
                            ->orWhereJsonContains('delivery->last_name', $user_filter_data)
                            ->orWhereJsonContains('delivery->last_name', $this->rus2translit($user_filter_data))
                            ->orWhereJsonContains('delivery->phone', $user_filter_data)
                            ->orWhereJsonContains('delivery->address', $user_filter_data)
                            ->orWhereJsonContains('delivery->postal_index', $user_filter_data)
                            ->orWhereJsonContains('delivery->comment', $user_filter_data)
                            ->pluck('user_id')->toArray();

                        $contact_filters = array_merge($user_data1, $user_data2);
                        }
                        $q->whereIn('orders.user_id', array_values($contact_filters));
                    }
                }

                // user phone
                if ($request->has('filter_phone') && ($request->get('filter_phone') != null)) {
                    $phone = $request->get('filter_phone');
                    $phone_trim = preg_replace('/\s+/', '', $phone);

                    $tel_format_2_chars = substr($phone_trim, 2);
                    $tel_format_3_chars = substr($phone_trim, 3);
                    $tel_format_4_chars = substr($phone_trim, 4);

                    $user_data = DB::table('users')
                        ->where('phone', $phone)
                        ->orWhere('phone', $phone_trim)
                        ->orWhere('phone','like','%'. $phone.'%')
                        ->orWhere('phone','like','%'.$phone_trim.'%')
                        ->pluck('id')->toArray();

                    $user_data2 = DB::table('orders')
                        ->whereJsonContains('delivery->phone', $phone)
                        ->orWhereJsonContains('delivery->phone', $phone_trim)
                        ->orWhereJsonContains('delivery->phone', $tel_format_2_chars)
                        ->orWhereJsonContains('delivery->phone', $tel_format_3_chars)
                        ->orWhereJsonContains('delivery->phone', $tel_format_4_chars)
                        ->pluck('user_id')->toArray();

                    $contact_filters = array_merge($user_data, $user_data2);

                    $q->whereIn('orders.user_id', array_values($contact_filters));
                }


                // user phone
                if ($request->has('sizeId') && ($request->get('sizeId') != null)) {
                    $sizeId = $request->get('sizeId');

                    $sizeId = DB::table('orders')
                    ->whereJsonContains('items->0->sizeId', $sizeId)
                    ->orWhereJsonContains('items->1->sizeId', $sizeId)
                    ->orWhereJsonContains('items->2->sizeId', $sizeId)
                    ->orWhereJsonContains('items->3->sizeId', $sizeId)
                    ->orWhereJsonContains('items->4->sizeId', $sizeId)
                    ->orWhereJsonContains('items->5->sizeId', $sizeId)
                    ->orWhereJsonContains('items->6->sizeId', $sizeId)
                    ->orWhereJsonContains('items->7->sizeId', $sizeId)
                    ->orWhereJsonContains('items->8->sizeId', $sizeId)
                    ->orWhereJsonContains('items->9->sizeId', $sizeId)
                    ->pluck('id')->toArray();

                    $q->whereIn('orders.id', array_values($sizeId));
                }

                // order status type
                if ($request->has('filter_order_status_id') && ($request->get('filter_order_status_id') != null)) {
                    $q->where('payment_status', $request->get('filter_order_status_id'));
                }

                // filter_a_order_from  type
                if ($request->has('filter_a_order_from') && ($request->get('filter_a_order_from') != null)) {
                    $q->where('a_order_from', $request->get('filter_a_order_from'));
                }

                // filter_catid  type
                if ($request->has('filter_catid') && ($request->get('filter_catid') != null)) {
                    $q->where('catid', $request->get('filter_catid'));
                }

                // filter_country type
                if ($request->has('filter_country') && ($request->get('filter_country') != null)) {
                    $q->where('country', $request->get('filter_country'));
                }

                // filter_price type
                if ($request->has('filter_price') && ($request->get('filter_price') != null)) {
                    $q->where('price', $request->get('filter_price'));
                }

               // filter_painter_choose type
                if ($request->has('filter_painter_choose') && ($request->get('filter_painter_choose') != null)) {
					$q->where('p.user_id', $request->get('filter_painter_choose'));
                }

               // filter_managers type
                if ($request->has('filter_managers') && ($request->get('filter_managers') != null)) {
					if($request->get('filter_managers')=='all' && $m)
					{
						$q->whereNotIn('orders.manager_id', $m);
					}
					else
					{
						$q->where('orders.manager_id', $request->get('filter_managers'));
					}
                }

                // filter_start_date type
                if ($request->has('filter_start_date') && ($request->get('filter_start_date') != null)) {
                    $q->where('orders.created_at','>=',  date('Y-m-d', strtotime($request->get('filter_start_date'))));
                }

                // filter_end_date type
                if ($request->has('filter_end_date') && ($request->get('filter_end_date') != null)) {
                    $q->where('orders.created_at','<=',  date('Y-m-d', strtotime($request->get('filter_end_date'))));
                }

                // userdata
            });



			// filter_painter_choose type
			if ($request->has('filter_painter_choose') && ($request->get('filter_painter_choose') != null)) {
				$orders = $orders->select('orders.*');
				$orders = $orders->join('painter_orders as p', 'p.order_id', '=', 'orders.id');
			}

			$orders = $orders->leftJoin('vr_numbers', 'orders.id', '=', 'vr_numbers.order_id');
			$orders = $orders->select('orders.*', 'vr_numbers.vrv_1', 'vr_numbers.vrv_2', 'vr_numbers.vrv_3', 'vr_numbers.vrv_4');

			$orders = $orderIdSort
                ? $orders->orderBy('orders.id', $orderIdSort)
                : $orders->orderBy('orders.created_at', 'desc');



			$calc_summ = $orders->get();
			$orders = $orders->paginate(10);

			$orders->appends($params);
			/*
			foreach ($calc_summ as $calc_item) {
				$summ = $summ + (int)$calc_item->price;
			}
			*/

            $no_pagin = 0;
        } else {
            if (!empty($request['completed'])) {
                $orders = DB::table('orders')->where(
                    'status',
                    '=',
                    'completed'
                 )->leftJoin('vr_numbers', 'orders.id', '=', 'vr_numbers.order_id')
                ->select('orders.*', 'vr_numbers.vrv_1', 'vr_numbers.vrv_2', 'vr_numbers.vrv_3', 'vr_numbers.vrv_4');
                $orders = $orderIdSort
                    ? $orders->orderBy('orders.id', $orderIdSort)
                    : $orders->orderBy('orders.created_at', 'desc');

				$calc_summ = $orders->get();
				$orders = $orders->paginate(13);
				$orders->appends($params);
            }
            else if (!empty($request['not_payed'])) {
                $orders = DB::table('orders')->orderBy('orders.created_at', 'desc')
                    ->where('orders.status', '!=', 'completed')
                    ->where('orders.payment_status', 'not_payed')
                    ->leftJoin('vr_numbers', 'orders.id', '=', 'vr_numbers.order_id')
                    ->select('orders.*', 'vr_numbers.vrv_1', 'vr_numbers.vrv_2', 'vr_numbers.vrv_3', 'vr_numbers.vrv_4')
                    ->get();

                $calc_summ = $orders;
                $items = $this->sortAdminRotationOrders($orders, null, $orderIdSort);
                $currentPage = LengthAwarePaginator::resolveCurrentPage();
                $perPage = 13;
                $currentItems = array_slice($items, $perPage * ($currentPage - 1), $perPage);
                $total = count($items);

                $orders = new \Illuminate\Pagination\LengthAwarePaginator($currentItems, $total, $perPage, $currentPage);
                $orders = $orders->withPath(url()->current());
                $orders->appends($params);
            }
            else if (!empty($request['in_production'])) {
                $orders = DB::table('orders')->where(
                    'status',
                    '=',
                    'in_production'
                 )->leftJoin('vr_numbers', 'orders.id', '=', 'vr_numbers.order_id')
                ->select('orders.*', 'vr_numbers.vrv_1', 'vr_numbers.vrv_2', 'vr_numbers.vrv_3', 'vr_numbers.vrv_4');
                $orders = $orderIdSort
                    ? $orders->orderBy('orders.id', $orderIdSort)
                    : $orders->orderBy('orders.created_at', 'desc');

				$calc_summ = $orders->get();
				$orders = $orders->paginate(13);
				$orders->appends($params);
            }
            else if ($request->get('status') === 'sended') {
                $orders = DB::table('orders')
                    ->where('orders.status', 'sended')
                    ->leftJoin('vr_numbers', 'orders.id', '=', 'vr_numbers.order_id')
                    ->select('orders.*', 'vr_numbers.vrv_1', 'vr_numbers.vrv_2', 'vr_numbers.vrv_3', 'vr_numbers.vrv_4');
                $orders = $orders->orderBy('orders.id', $orderIdSort ?: 'desc');

                $calc_summ = $orders->get();
                $orders = $orders->paginate(13);
                $orders->appends($params);
            }
            else if (!empty($request['new_orders'])) {
                $orders = DB::table('orders')
                    ->whereBetween('orders.created_at', [
                        Carbon::yesterday()->startOfDay(),
                        Carbon::today()->endOfDay(),
                    ])
                    ->leftJoin('vr_numbers', 'orders.id', '=', 'vr_numbers.order_id')
                    ->select('orders.*', 'vr_numbers.vrv_1', 'vr_numbers.vrv_2', 'vr_numbers.vrv_3', 'vr_numbers.vrv_4');
                $orders = $orders->orderBy('orders.id', $orderIdSort ?: 'desc');

                $calc_summ = $orders->get();
                $orders = $orders->paginate(13);
                $orders->appends($params);
            }
            else if (!empty($request['express']) || !empty($request['printing']) || !empty($request['sent_today'])) {

                if (!empty($request['express'])) {
                    $orders = DB::table('orders')->orderBy('created_at', 'desc')
                    ->where('status', '!=', 'completed')
                    ->leftJoin('vr_numbers', 'orders.id', '=', 'vr_numbers.order_id')
                    ->select('orders.*', 'vr_numbers.vrv_1', 'vr_numbers.vrv_2', 'vr_numbers.vrv_3', 'vr_numbers.vrv_4')
                    ->orderBy('created_at', 'desc')
                    ->get();
                }

                if (!empty($request['printing']))
                {
                    $orders = DB::table('orders')->orderBy('created_at', 'desc')
                    ->where('status', '!=', 'completed')
                    ->join('printing_orders', 'orders.id', '=', 'printing_orders.order_id')
                    ->leftJoin('vr_numbers', 'orders.id', '=', 'vr_numbers.order_id')
                    ->select('orders.*', 'vr_numbers.vrv_1', 'vr_numbers.vrv_2', 'vr_numbers.vrv_3', 'vr_numbers.vrv_4')
                    ->orderBy('created_at', 'desc')
                    ->get();
                }

                if (!empty($request['sent_today']))
                {
					//->join('printing_orders', 'orders.id', '=', 'printing_orders.order_id')
                    $orders = DB::table('orders')
                    ->where('orders.status', 'sended')
                    ->leftJoin('vr_numbers', 'orders.id', '=', 'vr_numbers.order_id')
                    ->select('orders.*', 'vr_numbers.vrv_1', 'vr_numbers.vrv_2', 'vr_numbers.vrv_3', 'vr_numbers.vrv_4')
                    ->whereDate('orders.send_date', '=', Carbon::today())
                    ->orderBy('orders.send_date', 'desc')
                    ->get();
                }

                if (!empty($request['sent_today']) && !$orderIdSort) {
                    $items = $orders->all();
                } elseif (!empty($request['express'])) {
                    $items = $this->sortAdminExpressOrders($orders, $orderIdSort);
                } else {
                    $items = $this->sortAdminRotationOrders($orders, null, $orderIdSort);
                }

                // Paginate and return the result
                $currentPage = LengthAwarePaginator::resolveCurrentPage();
                $perPage = 13;
                $currentItems = array_slice($items, $perPage * ($currentPage - 1), $perPage);
                $total = count($items);

                $orders = new \Illuminate\Pagination\LengthAwarePaginator($currentItems, $total, $perPage, $currentPage);
                $orders = $orders->withPath(url()->current());
                $orders->appends($params);

            } else {
                $orders = DB::table('orders')->orderBy('orders.created_at', 'desc')
                    ->leftJoin('vr_numbers', 'orders.id', '=', 'vr_numbers.order_id')
                    ->select('orders.*', 'vr_numbers.vrv_1', 'vr_numbers.vrv_2', 'vr_numbers.vrv_3', 'vr_numbers.vrv_4')
                    ->where(
                    'orders.status',
                    '!=',
                    'completed'
                )->get();
				$calc_summ = $orders;
                $items = $this->sortAdminRotationOrders($orders, null, $orderIdSort);
                $currentPage = LengthAwarePaginator::resolveCurrentPage();
                $perPage = 13;
                $currentItems = array_slice($items, $perPage * ($currentPage - 1), $perPage);
                $total = count($items);

                $orders = new \Illuminate\Pagination\LengthAwarePaginator($currentItems, $total, $perPage, $currentPage);
                $orders = $orders->withPath(url()->current());
				$orders->appends($params);

            }

            $no_pagin = 0;
        }

        $order_columns = [
            'firts' => '',
            'second' => '',
            'third' => '',
            'four' => '',
            'five' => 'Комментарий',
            'six' => 'Комментарий админа',
        ];

		if($calc_summ)
		{
			foreach ($calc_summ as $calc_item) {
				$summ = $summ + (float)$calc_item->price;
			}
		}

//        if (empty($request['user_id']))
//        {
//            //->join('printing_orders', 'orders.id', '=', 'printing_orders.order_id')
//            $orders = DB::table('orders')->orderBy('created_at', 'desc')
////                ->where('users.id', '6009')
//                ->leftJoin('vr_numbers', 'orders.id', '=', 'vr_numbers.order_id')
////                ->leftJoin('users', 'orders.user_id', '=', 'users.id' )
//                ->select('orders.*', 'vr_numbers.vrv_1', 'vr_numbers.vrv_2', 'vr_numbers.vrv_3', 'vr_numbers.vrv_4')
//                ->whereDate('orders.updated_at', '=', now())
//                ->orderBy('updated_at', 'desc')
//                ->get();
//        }


        $normalizePhone = static function ($phone): string {
            return preg_replace('/\D+/', '', (string) $phone);
        };

        $extractDelivery = static function ($order): array {
            $delivery = is_array($order) ? ($order['delivery'] ?? []) : ($order->delivery ?? []);
            if (is_string($delivery)) {
                $decoded = json_decode($delivery, true);
                return is_array($decoded) ? $decoded : [];
            }
            return is_array($delivery) ? $delivery : [];
        };

        $activeOrdersSnapshot = Orders::query()
            ->where('status', '!=', 'completed')
            ->get(['id', 'user_id', 'delivery'])
            ->map(function ($activeOrder) use ($extractDelivery, $normalizePhone) {
                $delivery = $extractDelivery($activeOrder);
                $email = strtolower(trim((string) ($delivery['email'] ?? '')));
                $phone = $normalizePhone($delivery['phone'] ?? '');
                $payerPhone = $normalizePhone($delivery['payer_phone'] ?? '');

                return [
                    'id' => (int) $activeOrder->id,
                    'user_id' => (int) $activeOrder->user_id,
                    'email' => $email,
                    'phone' => $phone,
                    'payer_phone' => $payerPhone,
                ];
            });

        $paymentRequestsByOrder = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('order_payment_requests')) {
            $paymentRequestsByOrder = OrderPaymentRequest::query()
                ->whereIn('order_id', $orders->pluck('id')->all())
                ->orderByDesc('id')
                ->get()
                ->groupBy('order_id');
        }

        foreach ($orders as $order) {
			if(!$calc_summ)
			{
				$summ = $summ + (float)$order->price;
			}
            $orderUser = User::find($order->user_id);
            $order->user = $orderUser;
            $orderDelivery = $extractDelivery($order);
            $orderEmail = strtolower(trim((string) ($orderDelivery['email'] ?? ($orderUser ? $orderUser->email : ''))));
            $orderPhone = $normalizePhone($orderDelivery['phone'] ?? ($orderUser ? $orderUser->phone : ''));
            $orderPayerPhone = $normalizePhone($orderDelivery['payer_phone'] ?? '');

            $order->user_other_orders = $activeOrdersSnapshot
                ->filter(function ($activeOrder) use ($order, $orderEmail, $orderPhone, $orderPayerPhone) {
                    if ((int) $activeOrder['user_id'] === (int) $order->user_id) {
                        return true;
                    }

                    if ($orderEmail !== '' && $activeOrder['email'] !== '' && $activeOrder['email'] === $orderEmail) {
                        return true;
                    }

                    if ($orderPhone !== '' && ($activeOrder['phone'] === $orderPhone || $activeOrder['payer_phone'] === $orderPhone)) {
                        return true;
                    }

                    if ($orderPayerPhone !== '' && ($activeOrder['phone'] === $orderPayerPhone || $activeOrder['payer_phone'] === $orderPayerPhone)) {
                        return true;
                    }

                    return false;
                })
                ->pluck('id')
                ->values();
            $order->payment_requests = $paymentRequestsByOrder->get((int) $order->id, collect())->take(3)->values();
            $order->orders_chats = OrdersChats::where('orders_id', $order->id)->get();
            $order->order_painter_images = OrderPainterImages::where('order_id', $order->id)->get();
            
            // SA Integration Data
            if (\Illuminate\Support\Facades\Schema::hasTable('sa_messages')) {
                $order->sa_messages = \App\Models\SaMessage::where('orders_id', $order->id)->orderBy('sent_at', 'asc')->get();
            } else {
                $order->sa_messages = collect();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('sa_escalations')) {
                $order->sa_escalations = \App\Models\SaEscalation::where('orders_id', $order->id)->get();
            } else {
                $order->sa_escalations = collect();
            }

            if ($auth_user->role->name == 'admin' || $auth_user->role->name == 'Admin 2' ) {
                $order->admin_chats = AdminChats::where('orders_id', $order->id)
                    ->join('users', 'order_admin_comments.user_id', '=', 'users.id')
                    ->select('order_admin_comments.*', 'users.email','users.first_name')
                    ->get();


            } else {$order->admin_chats=[];}
        }

        $ordersCollection = $orders['items'];

//        $filteredOrders = array_filter($ordersCollection, function ($order) {
//            return $order->user_id == 6009;
//        });

//        var_dump($orders['items']);
//        die();



	//dd($orders);

        $statuses = User::getRoles();
        $painters = User::where('role_id', 3)->get();
        $printing = User::where('role_id', 6)->get();
        $APainterImagesStatus = APainterImagesStatus::all();

        $userTypes = DB::table('user_types')->get();
        $my_ord_strings = OrderString::where('id', 1)->get()->translate('ru')[0];
        $unpaidOrdersCount = DB::table('orders')
            ->where('status', '!=', 'completed')
            ->where('payment_status', 'not_payed')
            ->count();

        // Отримуємо активний запис VenipakData для автозаповнення форми відправника
        $activeVenipakData = \App\Models\VenipakData::where('is_active', true)->first();

        return view('voyager::orders', [
            'orders' => $orders,
            'role' => $auth_user->role->name,
            'style' => $style,
            'statuses' => $statuses,
            'painters' => $painters,
            'printing' => $printing,
            'APainterImagesStatus' => $APainterImagesStatus,
            'managers' => $managers,
            'no_pagin' => $no_pagin,
            'order_columns' => $order_columns,
            'date_form_now' => Carbon::now(),
            'order_from' => AOrderFrom::all(),
            'c_tels' => CountryTel::orderBy('sort', 'asc')->get()->translate('ru'),
            'summ' => $summ,
            'filter_start_date' => $request->get('filter_start_date'),
            'filter_end_date' => $request->get('filter_end_date'),
            'user_status' => $userTypes,
            'my_ord_strings' => $my_ord_strings,
            'unpaidOrdersCount' => $unpaidOrdersCount,
            'activeVenipakData' => $activeVenipakData,
            'orderIdSort' => $orderIdSort,
        ]);
    }

    protected function sortAdminRotationOrders($orders, ?\DateTimeImmutable $now = null, ?string $orderIdSort = null): array
    {
        if (in_array($orderIdSort, ['desc', 'asc'], true)) {
            return $this->sortAdminRotationOrdersById($orders, $orderIdSort);
        }

        $now = $now ?: new \DateTimeImmutable('now');
        $newTopOrders = [];
        $ordersWithExpressDate = [];
        $ordersWithExpressNoDate = [];
        $ordersWithPriorityDeadline = [];
        $ordersWithoutDeadline = [];

        foreach ($orders as $item) {
            $item->deliv_hb = $this->decodeAdminRotationJson($item->delivery ?? null);
            $item->items_info = $this->decodeAdminRotationJson($item->items ?? null);

            $hasExpress = $this->isAdminRotationExpressOrder($item->deliv_hb, $item->items_info);
            $whenSend = $this->parseAdminRotationDate($item->deliv_hb['when_send'] ?? null);
            $item->when_send = $whenSend ? $whenSend->format('Y-m-d') : null;

            if ($this->isAdminRotationNewTopOrder($item, $now)) {
                $item->admin_rotation_new_top_at = strtotime($item->created_at ?? 'now');
                $newTopOrders[] = $item;
                continue;
            }

            if ($hasExpress) {
                $item->admin_rotation_priority_at = $whenSend ? $whenSend->getTimestamp() : null;
                if ($whenSend) {
                    $ordersWithExpressDate[] = $item;
                } else {
                    $ordersWithExpressNoDate[] = $item;
                }
                continue;
            }

            $priorityDate = $whenSend ?: $this->resolveAdminRotationProductionDeadline($item, $item->items_info);
            if ($priorityDate) {
                $item->admin_rotation_priority_at = $priorityDate->getTimestamp();
                $ordersWithPriorityDeadline[] = $item;
            } else {
                $item->admin_rotation_priority_at = null;
                $ordersWithoutDeadline[] = $item;
            }
        }

        $sortByPriority = static function ($a, $b) {
            $left = $a->admin_rotation_priority_at ?? PHP_INT_MAX;
            $right = $b->admin_rotation_priority_at ?? PHP_INT_MAX;

            if ($left === $right) {
                return strtotime($b->created_at ?? 'now') <=> strtotime($a->created_at ?? 'now');
            }

            return $left <=> $right;
        };

        usort($newTopOrders, static function ($a, $b) {
            return ($b->admin_rotation_new_top_at ?? 0) <=> ($a->admin_rotation_new_top_at ?? 0);
        });
        usort($ordersWithExpressDate, $sortByPriority);
        usort($ordersWithPriorityDeadline, $sortByPriority);

        return array_merge(
            $newTopOrders,
            $ordersWithExpressDate,
            $ordersWithExpressNoDate,
            $ordersWithPriorityDeadline,
            $ordersWithoutDeadline
        );
    }

    protected function sortAdminExpressOrders($orders, ?string $orderIdSort = null): array
    {
        $expressOrders = [];
        $priorityOrders = [];
        $datedOrders = [];
        $regularOrders = [];
        $now = new \DateTimeImmutable('today');
        $productionDueThreshold = $now->modify('+1 day');

        foreach ($orders as $item) {
            $item->deliv_hb = $this->decodeAdminRotationJson($item->delivery ?? null);
            $item->items_info = $this->decodeAdminRotationJson($item->items ?? null);

            $hasExpress = $this->isAdminRotationExpressOrder($item->deliv_hb, $item->items_info);
            $whenSend = $this->parseAdminRotationDate($item->deliv_hb['when_send'] ?? null);
            $productionDeadline = $this->resolveAdminRotationProductionDeadline($item, $item->items_info);
            $priorityDate = $this->resolveAdminExpressPriorityDate($whenSend, $productionDeadline);
            $item->when_send = $whenSend ? $whenSend->format('Y-m-d') : null;
            $item->admin_express_priority_at = $priorityDate ? $priorityDate->getTimestamp() : PHP_INT_MAX;
            $item->admin_express_is_express = $hasExpress ? 1 : 0;

            if ($hasExpress) {
                $expressOrders[] = $item;
            } elseif ($productionDeadline && $productionDeadline <= $productionDueThreshold) {
                $priorityOrders[] = $item;
            } elseif ($whenSend) {
                $datedOrders[] = $item;
            } else {
                $regularOrders[] = $item;
            }
        }

        $items = array_merge($expressOrders, $priorityOrders, $datedOrders, $regularOrders);

        if (in_array($orderIdSort, ['desc', 'asc'], true)) {
            return $this->sortAdminRotationOrdersById($items, $orderIdSort);
        }

        $sortByPriority = static function ($a, $b) {
            $leftDate = $a->admin_express_priority_at ?? PHP_INT_MAX;
            $rightDate = $b->admin_express_priority_at ?? PHP_INT_MAX;

            if ($leftDate === $rightDate) {
                return ($b->admin_express_is_express ?? 0) <=> ($a->admin_express_is_express ?? 0);
            }

            return $leftDate <=> $rightDate;
        };

        usort($expressOrders, $sortByPriority);
        usort($priorityOrders, $sortByPriority);
        usort($datedOrders, $sortByPriority);
        usort($regularOrders, static function ($a, $b) {
            return strtotime($b->created_at ?? 'now') <=> strtotime($a->created_at ?? 'now');
        });

        return array_merge($expressOrders, $priorityOrders, $datedOrders, $regularOrders);
    }

    protected function resolveAdminExpressPriorityDate(
        ?\DateTimeImmutable $whenSend,
        ?\DateTimeImmutable $productionDeadline
    ): ?\DateTimeImmutable {
        if ($whenSend && $productionDeadline) {
            return $whenSend < $productionDeadline ? $whenSend : $productionDeadline;
        }

        return $whenSend ?: $productionDeadline;
    }

    protected function sortAdminRotationOrdersById($orders, string $direction): array
    {
        $items = [];
        foreach ($orders as $order) {
            $items[] = $order;
        }

        usort($items, static function ($a, $b) use ($direction) {
            $left = (int) ($a->id ?? 0);
            $right = (int) ($b->id ?? 0);

            return $direction === 'asc' ? ($left <=> $right) : ($right <=> $left);
        });

        return $items;
    }

    protected function decodeAdminRotationJson($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    protected function isAdminRotationExpressOrder(array $delivery, array $items): bool
    {
        if ($this->isAdminRotationTruthy($delivery['is_manual_express'] ?? null)) {
            return true;
        }

        if (isset($items['total_terms_price']) && (float) $items['total_terms_price'] > 0) {
            return true;
        }

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            if ($this->isAdminRotationTruthy($item['is_manual_express'] ?? null)) {
                return true;
            }

            if (isset($item['terms_price']) && (float) $item['terms_price'] > 0) {
                return true;
            }
        }

        return false;
    }

    protected function isAdminRotationTruthy($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }

    protected function isAdminRotationNewTopOrder($order, \DateTimeImmutable $now): bool
    {
        $createdAt = $this->parseAdminRotationDateTime($order->created_at ?? null);
        if (!$createdAt) {
            return false;
        }

        return $createdAt->add($this->resolveAdminRotationNewTopInterval($createdAt)) > $now;
    }

    protected function resolveAdminRotationNewTopInterval(\DateTimeImmutable $createdAt): \DateInterval
    {
        $hour = (int) $createdAt->format('G');
        if ($hour >= 9 && $hour < 20) {
            return new \DateInterval('PT2H');
        }

        $nextWorkStart = $createdAt->setTime(9, 0, 0);
        if ($hour >= 20) {
            $nextWorkStart = $nextWorkStart->modify('+1 day');
        }

        $pinnedUntil = $nextWorkStart->modify('+2 hours');
        $seconds = max(0, $pinnedUntil->getTimestamp() - $createdAt->getTimestamp());

        return new \DateInterval('PT' . $seconds . 'S');
    }

    protected function parseAdminRotationDateTime($value): ?\DateTimeImmutable
    {
        if ($value instanceof \DateTimeInterface) {
            return (new \DateTimeImmutable('@' . $value->getTimestamp()))
                ->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        }

        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function parseAdminRotationDate($value): ?\DateTimeImmutable
    {
        if ($value instanceof \DateTimeInterface) {
            return (new \DateTimeImmutable('@' . $value->getTimestamp()))
                ->setTimezone(new \DateTimeZone(date_default_timezone_get()))
                ->setTime(0, 0, 0);
        }

        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return (new \DateTimeImmutable($value))->setTime(0, 0, 0);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function resolveAdminRotationProductionDeadline($order, array $items): ?\DateTimeImmutable
    {
        $explicitDeadline = $this->parseAdminRotationDate($order->painter_endtime ?? null);
        if ($explicitDeadline) {
            return $explicitDeadline;
        }

        $createdAt = $this->parseAdminRotationDate($order->created_at ?? null);
        if (!$createdAt) {
            return null;
        }

        return $createdAt->add(new \DateInterval('P' . $this->resolveAdminRotationProductionDays($items) . 'D'));
    }

    protected function resolveAdminRotationProductionDays(array $items): int
    {
        $days = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $termsDays = $this->extractAdminRotationProductionDays($item['terms'] ?? null);
            if ($termsDays !== null) {
                $days[] = $termsDays;
            }
        }

        return $days ? max($days) : 3;
    }

    protected function extractAdminRotationProductionDays($terms): ?int
    {
        if (!is_string($terms) || trim($terms) === '') {
            return null;
        }

        if (preg_match('/(\d+)\s*[-–]\s*(\d+)/u', $terms, $rangeMatches)) {
            return max(1, (int) $rangeMatches[2]);
        }

        if (preg_match('/(\d+)/u', $terms, $matches)) {
            return max(1, (int) $matches[1]);
        }

        return null;
    }

    public function show($order_id)
    {
        $order = $this->Orders->getOrderById($order_id);
        $order = (array) $order[0];
        $order['delivery'] = json_decode($order['delivery'], 1);
        $order['items'] = json_decode($order['items'], 1);
        $order['price'] = rtrim(rtrim(number_format($order['price'], 2, ',', ' '), '0'), ',') . ' €';
        $user = User::findOrFail($order['user_id']);
        $my_ord_strings = OrderString::where('id', 1)->get()->translate('ru')[0];


        return view('voyager::order', [
            'order' => $order,
            'my_ord_strings' => $my_ord_strings,
            'user' => $user,

        ]);
    }

    public function update(Request $request)
    {
        $params = $request->all();
        $id = $params['id'];
        $params['delivery'] = [];
        $params['delivery']['address'] = $params['address'];
        $params['delivery']['sposob'] = $params['sposob'];
        $params['delivery']['postal_index'] = $params['postal_index'];
        unset($params['_method']);
        unset($params['id']);
        unset($params['_token']);
        unset($params['sposob']);
        unset($params['address']);
        unset($params['postal_index']);


        $params['updated_at'] = date('Y-m-d H:i:s');
        $this->Orders->updateOrder($id, $params);
        $order = $this->Orders->getOrderById($id);
        $order = (array) $order[0];
        $order['delivery'] = json_decode($order['delivery'], 1);
        $order['items'] = json_decode($order['items'], 1);
        $order['price'] = rtrim(rtrim(number_format($order['price'], 2, ',', ' '), '0'), ',') . ' €';
        $user = User::findOrFail($order['user_id']);

        return view('voyager::order', [
            'order' => $order,
            'user' => $user,
        ]);
    }

    public function deleteOrder(Request $request)
    {
        $this->validate($request, [
            'id' => 'integer',
        ]);

        $this->Orders->deleteOrder($request['id']);
        DB::table('vr_numbers')->where('order_id', $request['id'])->delete();
        $response['success'] = 'элемент успешно удален';

        return json_encode($response);
    }

    public function approveOrder(Request $request)
    {
        $this->validate($request, [
            'id' => 'integer',
        ]);

        $response['success'] = 'элемент успешно подтвержден';
        DB::table('vr_numbers')->where('order_id', $request['id'])->delete();
        $this->Orders->changeOrderStatus($request['id'], 'completed');

        return json_encode($response);
    }

    public function update_order_painter(Request $request)
    {
        $order_id = $request->order_id;
        $painter_id = $request->painter_id;
        if ($order_id != '#') {
            PainterOrder::updateOrCreate([
                'order_id' => $order_id,
                'user_id' => $painter_id,
            ]);

            $data = [];
            $data['user_email'] = DB::table('users')->where('id', $painter_id)->pluck('email')->first();
            $user = User::where('id', $painter_id)->get()->first();
            $str = UserMessage::first()->get()->translate($user->preferredLocale(), 'ru')[0];
            $data['subject_send'] = $str['new_painter_order'];
            $data['content'] = $str['new_painter_order_text'] . ' ' . $order_id;

            Mail::send([], [], function ($message) use ($data) {
                $message->to($data['user_email']);
                $message->subject($data['subject_send']);
                $message->setBody($data['content'], 'text/html');
            });
        } else {
            DB::table('painter_orders')->where('order_id', $order_id)->delete();
        }

        $response['info'] = 1;
        $response['success'] = 'Художник обновлен!';

        return json_encode($response);
    }

    public function update_printing_order(Request $request)
    {
        $order_id = $request->order_id;
        $printing_id = $request->printing_id;
        if ($order_id != '#') {
            PrintingOrder::updateOrCreate([
                'order_id' => $order_id,
                'user_id' => $printing_id,
            ]);

            // $data = [];
            // $data['user_email'] = DB::table('users')->where('id', $printing_id)->pluck('email')->first();
            // $user = User::where('id', $printing_id)->get()->first();
            // $str = UserMessage::first()->get()->translate($user->preferredLocale(), 'ru')[0];
            // $data['subject_send'] = $str['new_printing_order'];
            // $data['content'] = $str['new_printing_order_text'] . ' ' . $order_id;

            // Mail::send([], [], function ($message) use ($data) {
            //     $message->to($data['user_email']);
            //     $message->subject($data['subject_send']);
            //     $message->setBody($data['content'], 'text/html');
            // });
        } else {
            DB::table('printing_orders')->where('order_id', $order_id)->delete();
        }

        $response['info'] = 1;
        $response['success'] = 'Менеджер печати обновлен!';

        return json_encode($response);
    }

    public function changeOrderVrv(Request $request)
    {
        $this->validate($request, [
            'num' => 'integer',
            'order_id' => 'integer',
        ]);

        $response['info'] = 0;
        //ViarStudia
        if ($request->num == 1) {
            $prefix = 'VR00';
            $last_vrv_id = DB::table('vr_numbers')->latest()->where('vrv_1', '!=', null)->pluck('vrv_1')->first();
            $vr_inc = intval($last_vrv_id) + 1;
            DB::table('vr_numbers')->insertGetId([
                'order_id' => $request->order_id,
                'vrv_1' => intval($last_vrv_id) + 1,
                'vrv_2' => null,
                'created_at' => date('Y-m-j H:i:s'),
                'updated_at' => date('Y-m-j H:i:s'),
            ]);
        }

        //Viar
        else if  ($request->num == 2){
            $prefix = 'BAW';
            $last_vrv_id = DB::table('vr_numbers')->latest()->where('vrv_2', '!=', null)->pluck('vrv_2')->first();
            $vr_inc = intval($last_vrv_id) + 1;
            DB::table('vr_numbers')->insertGetId([
                'order_id' => $request->order_id,
                'vrv_1' => null,
                'vrv_2' => intval($last_vrv_id) + 1,
                'created_at' => date('Y-m-j H:i:s'),
                'updated_at' => date('Y-m-j H:i:s'),
            ]);
        }

        else if  ($request->num == 3){
            $prefix = 'VRR445';
            $last_vrv_id = DB::table('vr_numbers')->latest()->where('vrv_3', '!=', null)->pluck('vrv_3')->first();
            $vr_inc = intval($last_vrv_id) + 1;
            DB::table('vr_numbers')->insertGetId([
                'order_id' => $request->order_id,
                'vrv_1' => null,
                'vrv_2' => null,
                'vrv_3' => intval($last_vrv_id) + 1,
                'created_at' => date('Y-m-j H:i:s'),
                'updated_at' => date('Y-m-j H:i:s'),
            ]);
        }

        else if  ($request->num == 4){
            $prefix = 'DS020';
            $last_vrv_id = DB::table('vr_numbers')->latest()->where('vrv_4', '!=', null)->pluck('vrv_4')->first();
            $vr_inc = intval($last_vrv_id) + 1;
            DB::table('vr_numbers')->insertGetId([
                'order_id' => $request->order_id,
                'vrv_1' => null,
                'vrv_2' => null,
                'vrv_3' => null,
                'vrv_4' => intval($last_vrv_id) + 1,
                'created_at' => date('Y-m-j H:i:s'),
                'updated_at' => date('Y-m-j H:i:s'),
            ]);
        }
        $response['vr_id'] = $vr_inc;

        // update pdf
        $order = $this->Orders->getOrderById($request->order_id);
        $order = (array) $order[0];

        $order['delivery'] = json_decode($order['delivery'], true);
        $order['items'] = json_decode($order['items'], true);
        $order['price'] = rtrim(rtrim(number_format($order['price'], 2, ',', ' '), '0'), ',') . ' €';

        $this->DynamicPDF->getPDFFromOrder($order, $prefix . $response['vr_id'], null);

        $response['success'] = 'Код обновлен!';
        $response['info'] = 1;
        return json_encode($response);
    }

    public function rem_vr_num(Request $request)
    {
        $order_id = $request->order_id;
        DB::table('vr_numbers')->where('order_id', $order_id)->delete();
        DB::table('orders')->where('id', $order_id)->update([
            'has_pdf' => null,
        ]);

        Storage::disk('public')->delete('pdf/' . $order_id . '.pdf');
        $response['info'] = 1;
        $response['success'] = 'Успешно удалено!';

        return json_encode($response);
    }

    public function update_painter_payed(Request $request)
    {
        $order_id = $request->order_id;
        $is_payed = $request->is_payed;

        if ($is_payed == 'null') {
            $is_payed = null;
        }

        $updated = DB::table('orders')->where('id', $order_id)->update([
            'painter_payed' => $is_payed,
        ]);

        $response['info'] = $updated;
        $response['success'] = 'Успешно обновлено!';

        return json_encode($response);
    }

    public function add_painter_images(Request $request)
    {
        $order_id = $request->order_id;
        $orig_images = $request->hasFile('user_images');
        $order = Orders::where('id', $order_id)->get()->first();
		$new_fname = Orders::getOrderImageName($order, 'painter');

        if ($orig_images && !empty($orig_images)) {
            $data['painter_images'] = ',';
            $files = $request->file('user_images');
			$i=0;
             foreach ($files as $file) {
				$i++;
				$file_extension = mb_strtolower(File::extension($file->getClientOriginalName()));
				$file_name = Storage::disk('uploads')->putFileAs('orders', $file, $new_fname."_".$i.".".$file_extension);
                //$file_name = Storage::disk('uploads')->put('uploads', $file);
                $data['painter_images'] .= URL::to('/') . '/' . $file_name . ',';
            }
        }

        $painter_all_images = $order->painter_images . $data['painter_images'];
        $painter_all_images = trim($painter_all_images, ',');
        $painter_all_images = preg_replace('/,+/', ',', $painter_all_images);
        $order->painter_images = $painter_all_images;
        $order->save();

        return redirect()->back();
    }

    public function add_client_images(Request $request)
    {
        $order_id = $request->order_id;
        $orig_images = $request->hasFile('user_images');
		$imgs = array();
        $order = Orders::where('id', $order_id)->get()->first();

		$new_fname = Orders::getOrderImageName($order, '');

        if ($orig_images && !empty($orig_images)) {
            $data['add_client_images'] = ',';
            $files = $request->file('user_images');
			$i=0;
            foreach ($files as $file) {
				$i++;
				$file_extension = mb_strtolower(File::extension($file->getClientOriginalName()));
                $file_name = Storage::disk('uploads')->putFileAs('orders', $file, $new_fname."_".$i.".".$file_extension);
				$imgs[] = URL::to('/') . '/' .$file_name;
                $data['add_client_images'] .= URL::to('/') . '/' . $file_name . ',';
            }
        }

		$items = json_decode($order['items'], true);

        $itemIndexRaw = $request->input('item_index', null);
        $itemIndex = is_numeric($itemIndexRaw) ? (int)$itemIndexRaw : null;

        // Фоллбек: если индекс не передан/невалидный - берем первую товарную позицию.
        if ($itemIndex === null || !isset($items[$itemIndex]) || !is_array($items[$itemIndex]) || isset($items[$itemIndex]['totalPrice'])) {
            $itemIndex = null;
            if (is_array($items)) {
                foreach ($items as $k => $it) {
                    if (is_int($k) && is_array($it) && !isset($it['totalPrice'])) {
                        $itemIndex = $k;
                        break;
                    }
                }
            }
        }

        if ($itemIndex !== null && is_array($items) && isset($items[$itemIndex]) && is_array($items[$itemIndex])) {
            if (isset($items[$itemIndex]['orig_images'])) {
                $orig_images = $items[$itemIndex]['orig_images'];
                $orig_images = array_merge((array)$orig_images, $imgs);
            } else {
                $orig_images = $imgs;
            }

            $items[$itemIndex]['orig_images'] = $orig_images;
            // items хранится как JSON-строка, иначе есть риск "сломать" заказ при сохранении.
            $order->items = json_encode($items);
            $order->save();
        }

        Orders::renameUploadsPhoto($order_id);

        return redirect()->back();
    }

    public function remove_painter_image(Request $request)
    {
        $order_id = $request->id;
        $img_id = $request->img_id;
        $order = Orders::where('id', $order_id)->get()->first();

        if ($request->filled('row_id')) {
            $row = OrderPainterImages::query()
                ->where('id', (int) $request->row_id)
                ->where('order_id', $order_id)
                ->where('is_img_painter', 1)
                ->first();

            if ($row) {
                $legacyImages = array_values(array_filter(array_map('trim', explode(',', (string) $order->painter_images))));
                $legacyImages = array_values(array_filter($legacyImages, function ($img) use ($row) {
                    return $img !== $row->image && basename(parse_url($img, PHP_URL_PATH) ?: $img) !== basename($row->image);
                }));

                $order->painter_images = implode(',', $legacyImages);
                $order->save();
                $row->delete();
            }

            return redirect()->back();
        }

        $images = $order['painter_images'];
        $painter_images = explode(',', $images);
        $i = -1;
        $pi = '';

        foreach ($painter_images as $img) {
            $i++;
            if ($i != $img_id) {
                $pi .= $img . ',';
            }
        }

        $order->painter_images = rtrim($pi, ',');
        $order->save();

        return redirect()->back();
    }

    public function remove_painter_sketch_image(Request $request)
    {
        $order_id = $request->id;
        $img_id = $request->img_id;
        $order = Orders::where('id', $order_id)->get()->first();

        if ($request->filled('row_id')) {
            $row = OrderPainterImages::query()
                ->where('id', (int) $request->row_id)
                ->where('order_id', $order_id)
                ->where('is_img_sketch', 1)
                ->first();

            if ($row) {
                $legacyImages = array_values(array_filter(array_map('trim', explode(',', (string) $order->painter_sketch_images))));
                $legacyImages = array_values(array_filter($legacyImages, function ($img) use ($row) {
                    return $img !== $row->image && basename(parse_url($img, PHP_URL_PATH) ?: $img) !== basename($row->image);
                }));

                $order->painter_sketch_images = implode(',', $legacyImages);
                $order->save();
                $row->delete();
            }

            return redirect()->back();
        }

        $images = $order['painter_sketch_images'];
        $painter_sketch_images = explode(',', $images);
        $i = -1;
        $pi = '';

        foreach ($painter_sketch_images as $img) {
            $i++;
            if ($i != $img_id) {
                $pi .= $img . ',';
            }
        }

        $order->painter_sketch_images = rtrim($pi, ',');
        $order->save();

        return redirect()->back();
    }

    public function remove_user_image(Request $request)
    {
        $order_id = $request->id;
        $img_id = $request->img_id;
        $order = Orders::where('id', $order_id)->get()->first();
        $images = $order['client_images'];
        $painter_images = explode(',', $images);
        $i = -1;
        $pi = '';

        foreach ($painter_images as $img) {
            $i++;
            if ($i != $img_id) {
                $pi .= $img . ',';
            }
        }

        $order->client_images = rtrim($pi, ',');
        $order->save();

        return redirect()->back();
    }

    public function show_painter_images(Request $request)
    {
        $order_id = $request->order_id;
        $show_images = $request->show_images;

        if ($show_images == 'null' || $show_images == 0 || $show_images == '0') {
            $show_images = null;
        }

        $updated = DB::table('orders')->where('id', $order_id)->update([
            'is_show_painter_images' => $show_images,
        ]);

        if ($show_images != null) {
            $user_id = DB::table('orders')->where('id', $order_id)->pluck('user_id')->first();
            $data = [];
            $data['to'] = DB::table('users')->where('id', $user_id)->pluck('email')->first();
            $user = User::where('id', $user_id)->get()->first();
            $msg_data = UserMessage::first()->get()->translate($user->preferredLocale(), 'ru')[0];
            $data['subject'] = $msg_data['new_photo_subject'];
            $data['content'] = $msg_data['new_photo_text'];

            $cur_lng = Config::get('app.locale');
			Mail::to($user->email)->send(new SendPrainterToUserPicture($data, $user->preferredLocale()));
            App::setLocale($cur_lng);
			/*
            Mail::send([], [], function ($message) use ($data) {
                $message->to($data['to']);
                $message->subject($data['subject']);
                $message->setBody($data['content'], 'text/html');
            });
			*/
        }

        $response['info'] = $updated;
        $response['success'] = 'Успешно обновлено!';

        return json_encode($response);
    }

    public function update_painter_time(Request $request)
    {
        $order_id = $request->order_id;
        $time = $request->setted_time;

        $updated = DB::table('orders')->where('id', $order_id)->update([
            'painter_endtime' => $time,
        ]);

        $response['info'] = $updated;
        $response['success'] = 'Успешно обновлено!';

        return json_encode($response);
    }


    public function update_when_send_time(Request $request)
    {
        $order_id = $request->order_id;
        $time = $request->setted_time;

        $order = $this->Orders->getOrderById($order_id);
        $order = (array) $order[0];
        $order['delivery'] = json_decode($order['delivery'], true);

        if(isset($order['delivery']['when_send']))
		{
			$order['delivery']['when_send'] = $time;
		}
		else
		{
			$order['delivery']['when_send'] = $time;
		}

		$delivery = $order['delivery'];
		$delivery = json_encode($delivery, true);
		//dd($delivery);
        $updated = DB::table('orders')->where('id', $order_id)->update([
            'delivery' => $delivery,
        ]);

		/*
        $updated = DB::table('orders')->where('id', $order_id)->update([
            'painter_endtime' => $time,
        ]);
		*/
        $response['info'] = $updated;
        $response['success'] = 'Успешно обновлено!';

        return json_encode($response);
    }

    public function update_prepayment_price(Request $request)
    {
        $order_id = $request->order_id;
        $prepayment_price = $request->prepayment_price;

        $updated = DB::table('orders')->where('id', $order_id)->update([
            'prepayment_price' => $prepayment_price,
        ]);

        $response['info'] = $updated;
        $response['success'] = 'Успешно предоплата обновлена!';

        return json_encode($response);
    }


    public function update_vr_num(Request $request)
    {
        $new_vr = $request->num;
        $order_id = $request->order_id;

        // по идее надо обновление номера для DS020, надо тестить
        if (strpos($new_vr, 'BAW') !== false) { // viar
            $new_val = str_replace('BAW', '', $new_vr);
            DB::table('vr_numbers')->where('order_id', $order_id)->update([
                'vrv_2' => $new_val,
            ]);
            $new_vr_to = $new_vr;
            // $new_vr_to = 'BAW' . $new_vr;
        } else { // viarstudia
            $new_val = str_replace('VR', '', $new_vr);
            DB::table('vr_numbers')->where('order_id', $order_id)->update([
                'vrv_1' => $new_val,
            ]);
            $new_vr_to = $new_vr;
            // $new_vr_to = 'VR' . $new_vr;
        }

        // update pdf
        $order = $this->Orders->getOrderById($order_id);
        $order = (array) $order[0];
        $order['delivery'] = json_decode($order['delivery'], true);
        $order['items'] = json_decode($order['items'], true);
        $order['price'] = rtrim(rtrim(number_format($order['price'], 2, ',', ' '), '0'), ',') . ' €';
        $this->DynamicPDF->getPDFFromOrder($order, $new_vr_to, null);
        $response['info'] = 1;
        $response['success'] = 'Код обновлен!';

        return json_encode($response);
    }

    public function update_order_firm(Request $request)
    {
        $order = $this->Orders->getOrderById($request->order_id);
        $order = (array) $order[0];
        $order['delivery'] = json_decode($order['delivery'], true);
        $order['items'] = json_decode($order['items'], true);
        $order['price'] = rtrim(rtrim(number_format($order['price'], 2, ',', ' '), '0'), ',') . ' €';
        $form_data = [];
        $form_data['name'] = $request->name; // name +
        $form_data['reg_num'] = $request->reg_num; // reg num +
        $form_data['addr'] = $request->addr; // addr +
        $form_data['bank'] = $request->bank; // bank name
        $form_data['bank_code'] = $request->bank_code; // bank name
        $form_data['vat_num'] = $request->vat_num; // bank name
        $form_data['office_addr'] = $request->office_addr; // bank name
        $form_data['acc_num'] = $request->acc_num;
        $this->DynamicPDF->getPDFFromOrder($order, $request->order_vr_id, $form_data);

        return back();
    }

     public function calculate_bonus($total)
     {
        if ($total<30){$bonus=1;}
        elseif ($total<60){$bonus=2;}
         elseif ($total<90){$bonus=3;}
          elseif ($total<140){$bonus=4;}
           elseif ($total<180){$bonus=5;}
            elseif ($total<250){$bonus=6;}
             elseif ($total<300){$bonus=7;}
              elseif ($total>=300){$bonus=10;}
              else $bonus=0;
         return $bonus;

     }




    public function changeOrderPayment(Request $request)
    {

        $this->validate($request, [
            'status_name' => 'string',
            'order_id' => 'integer',
        ]);

        // Проверяем оплачен ли заказ, чтобы начислить бонусы.
        if ($request->status_name=='payed'){
            $userRepository = app(UserRepository::class);

            $order = DB::table('orders')->where('id', $request->order_id)->first();
            $user_id=$order->user_id;
            $users=DB::table('users')->where('id', $user_id)->first();
            $email=$users->email;

            Mail::to($email)->send(new \App\Mail\Payment_successful($request->order_id, strtolower($users->country)));

            ///  find used_coupon from orders table
            $used_coupon = DB::table('orders')->where('id', $request->order_id)->first();

            //find user where inv_sale_code=$used_coupons
            $user = DB::table('users')->where('inv_sale_code', $used_coupon->used_coupon)->first();

            if ($user) {

                /// Send mail to user who invited, bonus 5  euro///
                $data['to']= $user->email;
                Mail::send([], [], function ($message) use ($data) {
                    $message->to($data['to']);
                    $message->subject('Вам пришли бонусы на сайте viarcanvas.com');
                    $message->setBody('Вам пришел бонус на 5 евро', 'text/html');
                });

                /// Update user bonuses with addBonusToUser function from OrderModel///
                $this->Orders->addBonusToUser($user->id, 5);
            }



            if($order->use_bonus==0){
                $user_id=$order->user_id;
                $users = DB::table('users')->where('id', $user_id)->first();
                $bonus=$this->calculate_bonus($order->price);
                if($bonus>0){
                    $current_bonus=$users->bonuses+$bonus;
                    DB::table('users')->where('id', $order->user_id)->update(['bonuses' => $current_bonus]);
                }
                $bonus_status=2;
            } else { $bonus_status=1;}



            //gift_card generate and send to email
            $for_create_coupons = [];

            $items = json_decode($order->items, true);
            foreach ($items as $key => $item) {
                if (is_array($item) && isset($item['pid']) && $item['pid'] == 5) {
                    $count = isset($item['count']) ? (int)$item['count'] : 1;

                    for ($i = 0; $i < $count; $i++) {
                        if($item['card_type'] == 'offline') {
                            $item['is_offline'] = true;
                        }  else {
                            $item['is_offline'] = false;
                        }

                        $for_create_coupons[] = [
                            'price'     => $item['price'],
                            'count'     => 1,
                            'card_type' => $item['card_type'],
                            'is_offline' => $item['is_offline']
                        ];
                    }
                }
            }

            // dd($for_create_coupons);
            if(count($for_create_coupons)){
                $data = json_decode($users->settings, true);

                foreach ($for_create_coupons as $for_create_coupon) {
                    $coupone_code = $userRepository->generateCouponUserGiftCard($users->email, (int)$for_create_coupon['price'], $request->order_id, $for_create_coupon['is_offline']);

                    $this->gfc = resolve(GiftcardController::class);
                    $coupon = Coupon::where('text',$coupone_code)->first();
                    $coupon_url = $this->gfc->generateGiftCardPDF($user_id, $request->order_id, $coupon->id, (int)$for_create_coupon['price'], $data['locale'],$coupone_code);
                    $coupon->pdf = $coupon_url;
                    $coupon->save();
                }

                $data['subject']=__('pages.gift_card');
                $data['first_name']=$users->first_name;
                $data['user']=$users;
                $data['order_id']=$request->order_id;

                Mail::to($email)->send(new \App\Mail\GiftCard($data, $data['locale']));
            }




        } elseif ($request->status_name == 'prepayment') {
            $order = DB::table('orders')->where('id', $request->order_id)->first();
            $user_id=$order->user_id;
            $users=DB::table('users')->where('id', $user_id)->first();
            $email=$users->email;
            Mail::to($email)->send(new \App\Mail\Payment_successful($request->order_id, strtolower($users->country)));
        } else { $bonus_status=1;}


        if(isset($bonus_status)){
            $order = Orders::where('id', $request->order_id)->update([
                'payment_status' => $request->status_name,
                'use_bonus' => $bonus_status,
            ]);
        } else {
            $order = Orders::where('id', $request->order_id)->update([
                'payment_status' => $request->status_name
            ]);
        }



        $response['success'] = 'Статус обновлен!';
        $response['info'] = $order;

        app(SynvolveWebhookService::class)->notifyOrderSnapshotById((int) $request->order_id, 'payment_status_changed_manual', [
            'payment_status' => (string) $request->status_name,
        ]);



        return json_encode($response);
    }

    public function changeOrder(Request $request)
    {

        $this->validate($request, [
            'order_id' => 'integer',
            'status_name' => 'string',
        ]);

        $order = Orders::where('id', $request->order_id)->update([
            'status' => $request->status_name,
            'status_date' => now(),
        ]);

        $cur_order = Orders::where('id', $request->order_id)->first();
        $del_info = json_decode($cur_order['delivery'], true);
        $user = User::where('id', $cur_order->user_id)->first();
        $data = UserMessage::first()->get()->translate($user->preferredLocale(), 'ru')[0];

		/*
        if ($request->status_name == 'pegging' || $request->status_name == 'pending') {
            Mail::to($del_info['email'])->send(
                new SendUserWeCheck($cur_order['items'], $data, $request->order_id, $user->preferredLocale())
            );
        }*/

        if ($request->status_name == 'watching') {
            $order->watching_date = now();
            $order->save();
        } else if ($request->status_name == 'pegging') {
            $order->pegging_date = now();
            $order->save();
        } else if ($request->status_name == 'in_production') {
            $order->in_production_date = now();
            $order->save();
        } else if ($request->status_name == 'completed') {
            $order->completed_date = now();
            $order->save();
        }

        if ($request->status_name == 'sended') {
            $cur_order->send_date = now();
            $cur_order->save();
            Mail::to($del_info['email'])->send(new SendUserYourOrderWasSend($cur_order, $data, $user->preferredLocale()));
        }

        //if ($request->status_name == 'completed' && $cur_order->payment_status == "payed") {
            // Mail::to($del_info['email'])->send(new SendUserYourOrderWasSend($cur_order, $data, $user->preferredLocale()));
        //}


        if ($request->status_name == 'send_lubanas' ) {
                $data['address']='';
                $email=$del_info['email'];
                $data['order_id']=$request->order_id;
                $cur_order->send_lubanas_date = now();
                $cur_order->save();
                Mail::to($email)->send(new \App\Mail\pickup_at_workshop($data, $user->preferredLocale()));
        }

        $response['success'] = 'Статус обновлен!';
        $response['info'] = $order;

        return json_encode($response);
    }



    public function change_client_status(Request $request)
    {
        $this->validate($request, [
            'status_id' => 'integer',
            'user_id' => 'integer',
        ]);

        $order = User::where('id', $request->user_id)->update([
            'client_status' => $request->status_id,
        ]);

        $response['success'] = 'Статус обновлен!';
        $response['info'] = $order;

        return json_encode($response);
    }

    public function changePainterSketchImagesStatus(Request $request)
    {
        $this->validate($request, [
            'order_id' => 'integer',
			'status_name' => 'string',
        ]);
//dd($request);
		$order = DB::table('orders')->where('id', $request->order_id)->update([
			'painter_sketch_images_status' => $request->status_name,
			'painter_sketch_images_status_date' => Carbon::now(),
		]);

        $response['success'] = 'Статус обновлен!';
        $response['info'] = $order;

        return json_encode($response);
    }

    public function changePainterImagesStatus(Request $request)
    {
        $this->validate($request, [
            'order_id' => 'integer',
			'status_name' => 'string',
        ]);
//dd($request);
		$order = DB::table('orders')->where('id', $request->order_id)->update([
			'painter_images_status' => $request->status_name,
			'painter_images_status_date' => Carbon::now(),
		]);

        $response['success'] = 'Статус обновлен!';
        $response['info'] = $order;

        return json_encode($response);
    }

    public function cart_total_item_counts($basket)
    {
        $total_item_counts = 0;
		if (!empty($basket))
        {
			foreach ($basket as $basketIndex => $product)
            {
				if (!isset($product['sumPrice']))
                {
					continue;
                }
				$total_item_counts = $total_item_counts + $product['count'];
            }
        }

        return $total_item_counts;
    }

    public function handlePayseraMethods($request, $makeOrder, $terms_price, $basket)
    {
        if (array_key_exists($request['payment'], WebToPay::PAYSERA_METHODS_MAP) && $makeOrder) {
            $request["order_id"] = $makeOrder;

            $baseTotal = (float) ($basket['sale_price'] ?? $basket['totalPrice'] ?? 0);
            $termsTotal = (float) ($basket['total_term_price'] ?? $basket['term_price'] ?? ($terms_price ?? 0));
            $deliveryTotal = (float) ($request['deliv_price'] ?? ($basket['delivery_price'] ?? 0));

            $request["deliv_price"] = number_format($deliveryTotal, 2, '.', '');
            $request["terms_price"] = number_format($termsTotal, 2, '.', '');
            $request["totalPrice"]  = number_format($baseTotal, 2, '.', '');

            $payseraTotal = $baseTotal + $termsTotal + $deliveryTotal;

            $request["payseraTotalPrice"] = number_format($payseraTotal, 2, '', '');

            return $this->payseraController->index($request);
        }

        return null;
    }

    public function save_order_and_pay(Request $request)
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
        $cart_pay_type = request()->session()->get('cart_pay_type');

        if (! is_array($cart_delivery) || empty($cart_delivery['country'])) {
            return redirect()->route('cart.step3');
        }

        $paymentType = is_array($cart_pay_type) ? ($cart_pay_type['type'] ?? null) : null;
        if (! is_string($paymentType)
            || ! CheckoutPaymentMethods::isAllowedForDelivery($paymentType, $cart_delivery)) {
            return redirect()->route('cart.step4')
                ->with('error', __('The selected payment method is unavailable.'));
        }

        $ur_name = request()->session()->get('ur_name');
        if($ur_name) $ur_name = 'on';
        $ur_name_l = request()->session()->get('ur_name_l');
        $ur_reg_num = request()->session()->get('ur_reg_num');
        $ur_legal_addr = request()->session()->get('ur_legal_addr');
        $ur_pnr_nr = request()->session()->get('ur_pnr_nr');
        $ur_bank_name = request()->session()->get('ur_bank_name');
        $ur_bank_code = request()->session()->get('ur_bank_code');
        $ur_bank_acc_code = request()->session()->get('ur_bank_acc_code');


        if($cart_delivery['delivery_type'] == 'to_the_door' && $cart_pay_type['type'] == 'on_delivery')
        {
            if($cart_delivery['country']=='LV' || $cart_delivery['country']=='LT' || $cart_delivery['country']=='EE')
            {
                $ct = CountryTel::orderBy('sort', 'asc')->where('country_code',$cart_delivery['country'])->first()->translate(App::getLocale(), 'ru');
                if(isset($ct['high_price']) && $ct['high_price'])
                {
                    $cart_delivery['price'] = (float)$cart_delivery['price'] + (float)$ct['high_price'];
                }
            }
        }

        // $request["_token"] = "6IXc6Q7jcMRdrQuvpoQgbzw1IopOUUs9XDs9fUYF";
        $request["email"] = $user['email'];
        $request["name"] = $user['first_name'];
        $request["last_name"] = $user['last_name'];
        $request["phone"] = $user['phone'];
        $request["name_rec"] = null;
        $request["last_name_rec"] = null;
        $phoneRec = session()->get('phone_rec');
        if (is_string($phoneRec)) {
            $phoneRec = trim($phoneRec);
        }
        $request["phone_rec"] = $phoneRec ?: null;
        $request["address_rec"] = null;
        $request["postal_index_rec"] = null;
        $request["country"] = $cart_delivery['country'];
        $request["delivery"] = $cart_delivery['delivery_type'];
        $request["city"] = $cart_delivery['city'];
        $request["address"] = $cart_delivery['address'];
        $request["postal_index"] = $cart_delivery['index'];
        $request["pickup_workshop_id"] = $cart_delivery['pickup_workshop_id'] ?? null;
        $request["delivery_town_id"] = $cart_delivery['delivery_town_id'] ?? null;
        $request["payment"] = $cart_pay_type['type'];
        $request["comment"] = $cart_delivery['cartComment'];
        $request["when_send"] = $cart_delivery['date'];
        $request['ur_name'] = $ur_name ?? null;
        $request["ur_name_l"] = $ur_name_l ?? null;
        $request["ur_reg_num"] = $ur_reg_num ?? null;
        $request["ur_legal_addr"] = $ur_legal_addr ?? null;
        $request["ur_pnr_nr"] = $ur_pnr_nr ?? null;
        $request["ur_bank_name"] = $ur_bank_name ?? null;
        $request["ur_bank_code"] = $ur_bank_code ?? null;
        $request["ur_bank_acc_code"] = $ur_bank_acc_code ?? null;
        $request["deliv_price"] = (float)$cart_delivery['price'];
        $request["agreement"] = "on";
        //новый заказ;
        $request["new_order"] = "1";


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

        $makeOrder = $this->makeOrder($request);
        // $makeOrder = 136;

        // if ($request['payment'] === 'paypalOnetimePayment' && $makeOrder) {
        //     $request["order_id"]    = $makeOrder;
        //     $request["deliv_price"] = number_format($request["deliv_price"], 2, '.', '');
        //     $request["terms_price"] = number_format($terms_price, 2, '.', '');
        //     $request["totalPrice"]  = number_format($basket['totalPrice'], 2, '.', '');

        //     if (isset($basket['sale_price']) && (float)$basket['sale_price']) {
        //         $request["paypalTotalPrice"] = (float)$request["deliv_price"] + (float)$request["terms_price"] + (float)$basket["sale_price"];
        //     } else {
        //         $request["paypalTotalPrice"] = (float)$request["deliv_price"] + (float)$request["terms_price"] + (float)$request["totalPrice"];
        //     }

        //     (new OneTimePayPalService)->getRequisites($request->all());
        // }

        if ($request['payment'] === 'paypalOnetimePayment' && $makeOrder) {
            $request["order_id"] = $makeOrder;

            $baseTotal = (float) ($basket['sale_price'] ?? $basket['totalPrice'] ?? 0);

            $termsTotal = (float) ($basket['total_term_price'] ?? $basket['term_price'] ?? ($terms_price ?? 0));

            $deliveryTotal = (float) ($cart_delivery['price'] ?? $request['deliv_price'] ?? 0);

            $request["deliv_price"] = number_format($deliveryTotal, 2, '.', '');
            $request["terms_price"] = number_format($termsTotal, 2, '.', '');
            $request["totalPrice"]  = number_format($baseTotal, 2, '.', '');

            $paypalTotal = $baseTotal + $termsTotal + $deliveryTotal;
            $request["paypalTotalPrice"] = number_format($paypalTotal, 2, '.', '');

            $gatewayResponse = $this->paypalService->getRequisites($request->all());
            if ($gatewayResponse) {
                return $gatewayResponse;
            }
        }

        $gatewayResponse = $this->handlePayseraMethods($request, $makeOrder, $terms_price, $basket);
        if ($gatewayResponse) {
            return $gatewayResponse;
        }

        $currentDate = date('ymd');

        Session::forget('ur_name');
        Session::forget('ur_name_l');
        Session::forget('ur_reg_num');
        Session::forget('ur_legal_addr');
        Session::forget('ur_pnr_nr');
        Session::forget('ur_bank_name');
        Session::forget('ur_bank_code');
        Session::forget('ur_bank_acc_code');

        $emailToClean = null;
        if (Auth::check()) {
            $emailToClean = Auth::user()->email;
        } elseif (session()->has('email')) {
            $emailToClean = session('email');
        }
        if ($emailToClean) {
            AbandonedCart::where('email', $emailToClean)->delete();
        }

        return Redirect::route('thanks', ['order_id' => $makeOrder]);
    }

    public function get_basket()
    {
        $basket = request()->session()->get('basket');
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

        return $basket;
    }

    public function makeOrder(Request $request)
    {

        $response = [];

        $requiredParams = [
            'agreement',
            'country',
            'delivery',
            'email',
            'payment',
            'phone',
        ];



        if (!auth()->check()) {
            if (User::where('email', '=', $request['email'])->exists()) {
                return redirect()->back()->with('error', trans('gl.user_ex'));
            }
        }


        foreach ($requiredParams as $reqParam) {
            if (!isset($request[$reqParam]) || $request[$reqParam] == null) {
                $response['Error'] = 'Не все поля заполнены';

                return redirect()->back()->with('def_error', $response['Error']);
            }
        }

        // add from country


        $basket_country = request()->session()->get('basket_country');
        $basket = request()->session()->get('basket');

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


        if (empty($basket)) {
            $response['Error'] = 'Время сессии истекло';
            return redirect()->back()->with('def_error', $response['Error']);
        }


        // Сохранение заказа
        $order_id = $this->Orders->saveOrder($basket, $request->all());



        if (!$order_id) {
            $response['Error'] = 'Ошибка сохранения';
            return redirect()->back()->with('def_error', $response['Error']);
        }
        AbandonedCart::where('email', $request['email'])->delete();
        $this->sendOrderInPdfToAdminEmail($order_id);


        // скидки
        if (Auth::check()) {
            $cur_user_id = Auth::id();
            // дать скидку на след. заказ
            if (intval($basket['totalPrice']) > 299) {
                $bonuses = DB::table('stocks')->where('id', 1)->pluck('custom_coupon_sale')->first();
                $cur_user_get = User::find($cur_user_id);
                $cur_user_get->has_sale_20eur = 1;
                if ($cur_user_get->bonuses == null) {
                    $cur_user_get->bonuses = $bonuses;
                } else {
                    $cur_user_get->increment('bonuses', intval($bonuses));
                }
                $cur_user_get->save();
            } else {
                DB::table('users')->where('id', $cur_user_id)->update([
                    'has_sale_20eur' => null,
                ]);
            }




            $cur_user_get = User::find($cur_user_id);
            $active_coupon =$cur_user_get->active_coupon;
            $coup_id = DB::table('coupons')->where('text', $active_coupon)->pluck('id')->first();




            // убрать если есть скидку за скриншот

            DB::table('users')->where('id', $cur_user_id)->update([
                'is_facebook_sale' => 0,
                'is_coupon_dates' => null,
            ]);


            // если есть остаток бонусов
            if (request()->session()->has('saved_bonuses')) {
                $saved_bonuses = request()->session()->get('saved_bonuses');
                $cur_user_get = User::find($cur_user_id);
                if ($cur_user_get->bonuses == null) {
                    $cur_user_get->bonuses = $saved_bonuses;
                } else {
                    $cur_user_get->increment('bonuses', intval($saved_bonuses));
                }
                $cur_user_get->save();
            }
        }



        if(isset($request['new_order']) && $request['new_order'])
		{
			return $order_id;
		}
		else
		{
            Cookie::queue(Cookie::forget('basket'));
            Session::forget('basket');
            Session::forget('saved_bonuses');
            Session::forget('is_coupon_30_40');
            Session::forget('email');
            Session::forget('ur_name');
            Session::forget('ur_name_l');
            Session::forget('ur_reg_num');
            Session::forget('ur_legal_addr');
            Session::forget('ur_pnr_nr');
            Session::forget('ur_bank_name');
            Session::forget('ur_bank_code');
            Session::forget('ur_bank_acc_code');

            return Redirect::route('thanks', ['order_id' => $order_id]);
		}

    }

    public function sendOrderInPdfToAdminEmail($order_id)
    {
        // order data
        $order = $this->Orders->getOrderById($order_id);
        $order = (array) $order[0];
        $order['delivery'] = json_decode($order['delivery'], true);
        $order['items'] = json_decode($order['items'], true);
        $order['price'] = rtrim(rtrim(number_format($order['price'], 2, ',', ' '), '0'), ',') . ' €';

        // user data
        $user = User::findOrFail($order['user_id']);
        // admin data
        $user_link = URL::to('/') . '/user/' . $user->id;
        $user_link_admin = URL::to('/') . '/admin/users/' . $user->id;
        $data['pdf'] = $this->DynamicPDF->getPDFFromOrder($order);
        $admin_data = GlobConfig::first()->get()->translate(App::getLocale(), 'ru')[0];
        $admin_data_mail = $admin_data['admin_email'];
        $data['to'] = $admin_data_mail;
        $data['order_id'] = $order_id;
        $data['subject'] = 'VIARCANVAS – новый заказ';
        $data['content'] = "Профиль: <a href='{$user_link_admin}'>{$user_link_admin}</a><br><br>";

        Mail::send([], [], function ($message) use ($data) {
            $message->to($data['to']);
            $message->subject($data['subject']);
            $message->setBody($data['content'] . 'Заказ: ' . $data['order_id'], 'text/html');
        });

        return true;
    }

    public function send_review()
    {
        $data = SendRev::first()->get()->translate(App::getLocale())[0];

        return view('send_rev')->with('data', $data);
    }

    public function sendOrderInPdfToUserEmail($order_id, $has_pdf)
    {
        $order = $this->Orders->getOrderById($order_id);
        $order = (array) $order[0];
        $order['delivery'] = json_decode($order['delivery'], true);
        $order['items'] = json_decode($order['items'], true);
        $order['price'] = rtrim(rtrim(number_format($order['price'], 2, ',', ' '), '0'), ',') . ' €';
        $pdf = $this->DynamicPDF->getPDFFromOrder($order);
        $cur_order = Orders::where('id', $order_id)->first();
        $cur_order->pdf_link = $pdf;
        $cur_order->save();
        $user = User::findOrFail($order['user_id']);
        $user->notify(new ThanksForBuyNotification($pdf, $has_pdf));

        return true;
    }

    public function leave_rev(Request $request)
    {
        $user = User::where('id', $request->user_id)->get()->first();
        $send_rev_url = route('send_rev');
        $rev_lang_url = str_replace('/user/', '/' . $user->preferredLocale() . '/user/', $send_rev_url);
        $data = UserMessage::first()->get()->translate($user->preferredLocale(), 'ru')[0];
        $data['rev_lang_url'] = str_replace('/user/', '/' . $user->preferredLocale() . '/user/', $send_rev_url);
        Mail::to($user->email)->send(new SendUserReview($data,strtolower($user->country)));
        echo '<script type="text/javascript">';
        echo 'alert("Запрос на отзыв успешно отправлен");';
        echo '</script>';

        return back();
    }

    public function leave_rev_no_orders(Request $request)
    {
        $user = User::where('id', $request->user_id)->get()->first();
        $send_rev_url = route('send_rev');
        str_replace('/user/', '/' . $user->preferredLocale() . '/user/', $send_rev_url);
        $data = UserMessage::first()->get()->translate($user->preferredLocale(), 'ru')[0];
        $data['rev_lang_url'] = str_replace('/user/', '/' . $user->preferredLocale() . '/user/', $send_rev_url);
        Mail::to($user->email)->send(new SendUserReview($data,strtolower($user->country)));
        echo '<script type="text/javascript">';
        echo 'alert("Запрос на отзыв успешно отправлен");';
        echo '</script>';

        return back();
    }

    public function approve_user_checkout(Request $request)
    {
        if (Auth::check()) {
            $auth_user = \Auth::user();
            $cur_user_id = Auth::id();

            if ($auth_user->role->name == 'admin' || $auth_user->role->name == 'manager') {
                $order_id = $request->order_id;
                $order = $this->Orders->getOrderById($order_id);
                $order = (array) $order[0];

                if ($order['approved_date'] != 'null' && $order['approved_date'] != '') {
                    $cur_date = $order['approved_date'];
                } else {
                    $cur_date = date('d.m.Y');
                }

                // update
                DB::table('orders')->where('id', $order_id)->update([
                    'has_pdf' => 1,
                    'approved_date' => $cur_date,
                ]);

                $order['delivery'] = json_decode($order['delivery'], true);
                $order['items'] = json_decode($order['items'], true);
                $order['price'] = rtrim(rtrim(number_format($order['price'], 2, ',', ' '), '0'), ',') . ' €';
                $order_vr_id = Orders::getVRById($order_id);
                $data['pdf'] = $this->DynamicPDF->getPDFFromOrder($order, $order_vr_id, null);

                // ednupdate
                $user_id = $request->user_id;
                $user = User::where('id', $user_id)->first();
				$udata = UserMessage::first()->get()->translate($user->preferredLocale(), 'ru')[0];

				$data['pdf'] = str_replace('https://','',$data['pdf']);
				$data['pdf'] = str_replace('www.','',$data['pdf']);
				$data['pdf'] = str_replace('http://','',$data['pdf']);
				$data['pdf'] = str_replace('viarcanvas.com','',$data['pdf']);
				$data['pdf'] = str_replace('viarcanvas.loc','',$data['pdf']);
				$data['pdf'] = public_path().$data['pdf'];
//dd(public_path() ,str_replace('https://viarcanvas.com/','',$data['pdf']));
                Mail::to($user->email)->send(new ApproveUserCheckoutMail(
                    $data['pdf'],
                    $order['updated_at'],

					$order['items'],
					$udata,
					$request->order_id,
					$user->preferredLocale()
                ));

                $cur_user_inv_code = $user->invited;

                if ($cur_user_inv_code != null) {
                    $users = User::all()->except($user_id);

                    foreach ($users as $user) {
                        if ($user->inv_sale_code == $cur_user_inv_code) {
                            // скидка
                            $invited_user = User::where('id', $user->id)->firstOrFail();
                            $bon_count = DB::table('stocks')->where('id', 1)->pluck('friend_sale')->first();
                            $invited_user->bonuses = $invited_user->bonuses + $bon_count;
                            $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
                            $user_code = mb_substr(str_shuffle($rand_code), 0, 8);
                            $invited_user->inv_sale_code = $user_code;
                            $invited_user->is_active_friend_inv = 1;
                            $invited_user->save();
                            $cur_user = User::where('id', $cur_user_id)->firstOrFail();
                            $cur_user->invited = null;
                            $cur_user->save();
                        }
                    }
                }

                $cur_order = Orders::where('id', $order_id)->first();

				if($cur_order->status == "watching")
				{
					$cur_order->status = 'pegging';
				}

                $cur_order->pdf_approved = 1;
                $cur_order->save();



                echo '<script type="text/javascript">';
                echo 'alert("Счет успешно отправлен");';
                echo 'window.location.href = "/admin/orders";';
                echo '</script>';
            } else {
                echo '<script type="text/javascript">';
                echo 'alert("Только для администраторов");';
                echo 'window.location.href = "/";';
                echo '</script>';
            }
        } else {
            echo '<script type="text/javascript">';
            echo 'alert("Доступ запрещен");';
            echo 'window.location.href = "/";';
            echo '</script>';
        }
    }

    public function add_order_item(Request $request)
    {
        $order_id = $request->order_id;
        $order = Orders::find($order_id);
        $items = json_decode($order->items, true);
        $cur_price = round($request->price, 2);

		if(isset($request->size) && $request->size)
		{
			$size = $request->size;
		}
		else
		{
			$size = false;
		}

		if(isset($request->size) && $request->size)
		{
			$name = $request->name;
		}
		else
		{
			$name = false;
		}

		$orig_images = $request->hasFile('basket_images');
		$new_fname = Orders::getOrderImageName($order, '', false, false, $size, false ,$name);
		$data['orig_images'] = [];
        if ($orig_images && !empty($orig_images)) {
            $files = $request->file('basket_images');
			$i=0;
             foreach ($files as $file) {
				$i++;
				$file_extension = mb_strtolower(File::extension($file->getClientOriginalName()));
				$file_name = Storage::disk('uploads')->putFileAs('orders', $file, $new_fname."_".$i.".".$file_extension);
                //$file_name = Storage::disk('uploads')->put('uploads', $file);
                $data['orig_images'][] = URL::to('/') . '/' . $file_name . '';
            }
        }

        $giftCode = strtoupper(trim((string)$request->input('manual_gift_code', 'G0')));
        if (!in_array($giftCode, ['G0', 'G1', 'G2'], true)) {
            $giftCode = 'G0';
        }
        $manualDecorationId = (int)$request->input('manual_decoration_id', 5);
        $decorationMap = [
            1 => ['lac' => 'L2', 'brush' => 'P0'],
            2 => ['lac' => 'L0', 'brush' => 'P1'],
            3 => ['lac' => 'L1', 'brush' => 'P0'],
            5 => ['lac' => 'L0', 'brush' => 'P0'],
        ];
        if (!isset($decorationMap[$manualDecorationId])) {
            $manualDecorationId = 5;
        }
        $lacCode = $decorationMap[$manualDecorationId]['lac'];
        $brushstrokesCode = $decorationMap[$manualDecorationId]['brush'];
        $orientationCode = strtoupper(trim((string)$request->input('manual_orientation_code', 'V0')));
        if (!in_array($orientationCode, ['V0', 'V1', 'V2', 'V3', 'V4'], true)) {
            $orientationCode = 'V0';
        }
        $manualCanvasId = (int)$request->input('manual_canvas_id', 2);
        if ($manualCanvasId < 1 || $manualCanvasId > 5) {
            $manualCanvasId = 2;
        }

        $manualExpressItem = $request->boolean('manual_express') ? 1 : 0;

        $newItem = [
            'name' => $request->name,
            'userComment' => $request->basket_comment,
            'orig_images' => $data['orig_images'],
            'price' => $cur_price,
            'sumPrice' => $cur_price,
            'sumFormatedPrice' => $cur_price . ' €',
            'formatedPrice' => $cur_price . ' €',
            'formatedTotalPrice' => $cur_price . ' €',
            'size' => $request->size,
            'terms' => $request->basket_terms,
            'manual_gift_code' => $giftCode,
            'manual_decoration_id' => $manualDecorationId,
            'manual_lac_code' => $lacCode,
            'manual_brushstrokes_code' => $brushstrokesCode,
            'manual_orientation_code' => $orientationCode,
            'manual_canvas_id' => $manualCanvasId,
            'canvasId' => $manualCanvasId,
            'is_manual_baget' => $request->boolean('manual_baget') ? 1 : 0,
            'is_manual_express' => $manualExpressItem
        ];
        $newItem = $this->applyManualPresentationForOrderItem($newItem, $order);
        $items[] = $newItem;

        if (!isset($items['formatedTotalPrice'])) {
            $items['formatedTotalPrice'] = 0;
        }

        if (!isset($items['saved_price'])) {
            $items['saved_price'] = 0;
        }

        if (!isset($items['totalPrice'])) {
            $items['totalPrice'] = 0;
        }

        $items['formatedTotalPrice'] = round((float)$items['formatedTotalPrice'] + $cur_price, 2) . ' €';
        $items['saved_price'] = round((float)$items['saved_price'] + $cur_price, 2);
        $items['totalPrice'] = round((float)$items['totalPrice'] + $cur_price, 2);

        ksort($items);
        if ($manualExpressItem === 1) {
            $delivery = json_decode($order->delivery, true);
            if (!is_array($delivery)) {
                $delivery = [];
            }
            $delivery['is_manual_express'] = 1;
            $order->delivery = json_encode($delivery);
        }
        $order->items = json_encode($items);
        $order->save();

        $this->update_order_price($order_id);

        $order = $this->Orders->getOrderById($order_id);
        $order = (array) $order[0];
        $order['delivery'] = json_decode($order['delivery'], true);
        $order['items'] = json_decode($order['items'], true);
        $order['price'] = rtrim(rtrim(number_format((float)$order['price'], 2, ',', ' '), '0'), ',') . ' €';
        $order_vr_id = Orders::getVRById($order_id);

        $this->DynamicPDF->getPDFFromOrder($order, $order_vr_id, null);

        Orders::renameUploadsPhoto($order_id);

        return back();
    }

    protected function resolveClientLocaleForOrderItem(?Orders $order = null): string
    {
        if ($order instanceof Orders) {
            $orderDelivery = is_string($order->delivery) ? json_decode($order->delivery, true) : $order->delivery;
            if (is_array($orderDelivery)) {
                $country = strtoupper(trim((string)($orderDelivery['country'] ?? '')));
                $map = [
                    'LV' => 'lv',
                    'LT' => 'lt',
                    'EE' => 'ee',
                    'ET' => 'ee',
                    'PL' => 'pl',
                    'DE' => 'de',
                    'EN' => 'en',
                    'GB' => 'en',
                    'RU' => 'ru',
                ];
                if (isset($map[$country])) {
                    return $map[$country];
                }
            }
        }

        if ($order instanceof Orders && !empty($order->user_id)) {
            $user = User::find((int)$order->user_id);
            if ($user && is_string($user->preferredLocale()) && $user->preferredLocale() !== '') {
                return $user->preferredLocale();
            }
        }

        return 'ru';
    }

    protected function resolveGiftCodeFromOrderItem(array $item): string
    {
        $giftCode = strtoupper(trim((string)($item['manual_gift_code'] ?? '')));
        if (in_array($giftCode, ['G0', 'G1', 'G2'], true)) {
            return $giftCode;
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
                    $id = (int)$boxId;
                    if ($id === 1) {
                        return 'G2';
                    }
                    if ($id === 2) {
                        return 'G1';
                    }
                }
            } elseif ($boxIds !== null) {
                $id = (int)$boxIds;
                if ($id === 1) {
                    return 'G2';
                }
                if ($id === 2) {
                    return 'G1';
                }
            }
        }

        $complId = isset($item['compl_id']) ? (int)$item['compl_id'] : 3;
        if ($complId === 1) {
            return 'G2';
        }
        if ($complId === 2) {
            return 'G1';
        }

        return 'G0';
    }

    protected function resolveDecorationIdFromOrderItem(array $item): int
    {
        $manualDecorationId = isset($item['manual_decoration_id']) ? (int)$item['manual_decoration_id'] : 0;
        if (in_array($manualDecorationId, [1, 2, 3, 5], true)) {
            return $manualDecorationId;
        }

        foreach (['decorationId', 'decor_id', 'decorId'] as $key) {
            if (!isset($item[$key])) {
                continue;
            }
            $legacyId = (int)$item[$key];
            if (in_array($legacyId, [1, 2, 3, 5], true)) {
                return $legacyId;
            }
        }

        return 5;
    }

    protected function resolveCanvasIdFromOrderItem(array $item): int
    {
        $manualCanvasId = isset($item['manual_canvas_id']) ? (int)$item['manual_canvas_id'] : 0;
        if ($manualCanvasId >= 1 && $manualCanvasId <= 5) {
            return $manualCanvasId;
        }

        $canvasId = isset($item['canvasId']) ? (int)$item['canvasId'] : 0;
        if ($canvasId >= 1 && $canvasId <= 5) {
            return $canvasId;
        }

        $holstId = isset($item['holst_id']) ? (int)$item['holst_id'] : 0;
        if ($holstId >= 1 && $holstId <= 5) {
            return $holstId;
        }

        return 2;
    }

    protected function isExpressTermsForOrderItem(string $text): bool
    {
        $needles = ['express', 'ekspress', 'ekspresowy', 'экспресс', 'kiirsaadetis', 'ekspres'];
        $lower = mb_strtolower($text);
        foreach ($needles as $needle) {
            if (mb_stripos($lower, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    protected function isStandardTermsForOrderItem(string $text, string $locale): bool
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return false;
        }

        $standardText = trim((string)GalleryItem::getTermsByPriceLocaled(0, $locale));
        if ($standardText !== '' && mb_stripos(mb_strtolower($trimmed), mb_strtolower($standardText)) !== false) {
            return true;
        }

        $needles = ['standart', 'standard', 'стандарт'];
        $lower = mb_strtolower($trimmed);
        foreach ($needles as $needle) {
            if (mb_stripos($lower, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function applyManualPresentationForOrderItem(array $item, ?Orders $order = null): array
    {
        $locale = $this->resolveClientLocaleForOrderItem($order);

        $canvasId = $this->resolveCanvasIdFromOrderItem($item);
        $item['manual_canvas_id'] = $canvasId;
        $item['canvasId'] = $canvasId;
        $canvas = GalleryHolst::find($canvasId);

        $giftCode = $this->resolveGiftCodeFromOrderItem($item);
        $giftToBoxId = ['G0' => 3, 'G1' => 2, 'G2' => 1];
        $boxId = $giftToBoxId[$giftCode] ?? 3;

        if (!is_array($item['show'] ?? null)) {
            $item['show'] = [];
        }
        if ($canvas && !empty($canvas->translate($locale, 'ru')->name)) {
            $item['show']['canvas'] = $canvas->translate($locale, 'ru')->name;
        }

        $box = GalleryBox::find($boxId);
        if ($box) {
            $translatedBox = $box->translate($locale, 'ru');
            if (!empty($translatedBox->name)) {
                $item['show']['box'] = [$translatedBox->name];
            }
        }
        $item['compl_id'] = $boxId;

        $decorationId = $this->resolveDecorationIdFromOrderItem($item);
        $decoration = GalleryDecoration::find($decorationId);
        if ($decoration) {
            $translatedDecoration = $decoration->translate($locale, 'ru');
            if (!empty($translatedDecoration->name)) {
                $item['show']['decoration'] = $translatedDecoration->name;
            }
        }

        if (!empty($item['is_manual_express'])) {
            $terms = trim((string)($item['terms'] ?? ''));
            if ($terms === '' || is_numeric(str_replace(',', '.', $terms))) {
                $expressText = GalleryItem::getTermsByPriceLocaled(1, $locale);
                $priceText = '';
                $termsValue = str_replace(',', '.', $terms);
                if ($terms !== '' && is_numeric($termsValue) && (float)$termsValue > 0) {
                    $priceText = ' ' . rtrim(rtrim(number_format((float)$termsValue, 2, '.', ''), '0'), '.') . ' €';
                } elseif (isset($item['terms_price']) && is_numeric($item['terms_price']) && (float)$item['terms_price'] > 0) {
                    $priceText = ' ' . rtrim(rtrim(number_format((float)$item['terms_price'], 2, '.', ''), '0'), '.') . ' €';
                }
                $item['terms'] = trim($expressText . $priceText);
            } elseif (!$this->isExpressTermsForOrderItem($terms)) {
                if ($this->isStandardTermsForOrderItem($terms, $locale)) {
                    $item['terms'] = GalleryItem::getTermsByPriceLocaled(1, $locale);
                } else {
                    $item['terms'] = GalleryItem::getTermsByPriceLocaled(1, $locale) . ' | ' . $terms;
                }
            }
        } elseif (array_key_exists('is_manual_express', $item)) {
            if (isset($item['terms_price'])) {
                $item['terms_price'] = 0;
            }
            $terms = trim((string)($item['terms'] ?? ''));
            if ($terms === '' || $this->isExpressTermsForOrderItem($terms)) {
                $item['terms'] = GalleryItem::getTermsByPriceLocaled(0, $locale);
            }
        }

        return $item;
    }

    public function update_order_price($order_id)
    {
        $order = $this->Orders->getOrderById($order_id);
        $order = (array) $order[0];
        $order['items'] = json_decode($order['items'], true);
        $summ = 0;

        foreach ($order['items'] as $item) {
            if (isset($item['name']) && isset($item['price'])) {
                $summ = $summ + floatval(number_format((float)$item['price'], 2));
            }
        }

        $summ = round((float)$summ, 2);

        return DB::table('orders')->where('id', $order_id)->update([
            'price' => $summ,
            'sale_price' => $summ,
        ]);
    }


    public function generate_checkout(Request $request)
    {
        if (Auth::check()) {
            $auth_user = \Auth::user();
            if ($auth_user->role->name == 'admin' || $auth_user->role->name == 'manager' ) {
                $order_id = $request->order_id;
                DB::table('orders')->where('id', $order_id)->update([
                    'has_pdf' => 1,
                ]);

                $order = $this->Orders->getOrderById($order_id);
                $order = (array) $order[0];
                $order['delivery'] = json_decode($order['delivery'], true);
                $order['items'] = json_decode($order['items'], true);
                $order['price'] = rtrim(rtrim(number_format($order['price'], 2, ',', ' '), '0'), ',') . ' €';
                $order_vr_id = Orders::getVRById($order_id);

                $this->DynamicPDF->getPDFFromOrder($order, $order_vr_id, null);

                return back();
            }
        }
    }
}
