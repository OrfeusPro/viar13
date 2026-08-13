<?php

namespace App\Http\Controllers\Account;

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
use App\Models\CountryTel;
use App\Models\GalleryItem;
use App\Models\GalleryPage;
use App\Models\UserMessage;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Models\NewhomeService;
use App\Mail\AdminToUserComment;
use App\Models\OrderPainterImages;
use App\Http\Controllers\Controller;
use App\Models\APainterImagesStatus;
use App\Http\Controllers\Libwebtopay\WebToPay;
use App\Mail\PainterAdminUserComment;
use App\Mail\SendPrainterToUserSketch;
use App\Mail\SendPrainterToUserPicture;
use App\Services\SynvolveWebhookService;
use App\Services\Payment\ClientOrderPaymentService;
use Illuminate\Support\Facades\Request;
use App\Services\UpdatePainterImageService;
use App\Services\SendClientPainterImageService;
use App\Services\UpdatePainterSketchImageService;

class AccountController extends Controller
{
    private $Orders;
    private $clientOrderPaymentService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->Orders = app(Orders::class);
        $this->clientOrderPaymentService = app(ClientOrderPaymentService::class);
    }

    public function index()
    {
        $page["title"] = __("account_new.main.bread"); 

        //-----------
        $topmailClass= new \App\Helpers\GalleryTopMail;
        $topmail= $topmailClass->get_topmail_data();
        $images_folder = 'https://viarcanvas.com/storage/';
        $contry_mult=$topmailClass->get_gallery_price();

        $user_id = Auth::id();
        $user_code = Auth::user()->inv_sale_code;

        if ($user_code == null || $user_code == 'regenerate') {
            $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
            $user_code = mb_substr(str_shuffle($rand_code), 0, 8);
            DB::table('users')->where('id', $user_id)->update([
                'inv_sale_code' => $user_code,
            ]);
        }

        $modals = view(env('THEME_RESOURCES') . 'pages.stocks.modals')->with([
            'invite_code' => $user_code,
        ]);
        //--------------

        $content = view(config('theme.resource') . 'account.account')->with([
            'page' => $page,
            'images_folder' => $images_folder,
            'top_mail' => $topmail,
            'multiplyer' => $contry_mult
        ]);

        $this->vars = Arr::add($this->vars, 'title', __("account_new.main.meta_title"));
        $this->vars = Arr::add($this->vars, 'meta_desc', __("account_new.main.meta_desc"));
        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'modals', $modals);

        return $this->renderOutput();
    }

    public function orders(\Illuminate\Http\Request $request, $type = null, $title = null)
    {
        $sort = $request->input('sort'); 
        
        $page["title"] = __("account_new.orders.bread"); 
        $painterOrderIds = [];

        if (Auth::user()->role->name != 'painter') {
            $orders_ids = DB::table('orders')->where('user_id', Auth::id())->pluck('id');
            $orders = Orders::whereIn('id', $orders_ids)->with('order_user_comments')->orderBy("id", 'desc')->paginate(5, ['*'], 'orders');

            // $orders = $this->Orders->getOrderByCurrentUserPaginate();
            $payed_orders = [];
            $orders_completed = [];

            $count_not_read_messages = 0;
            foreach($orders as $item)
            {
                $count_not_read_messages += $item->order_user_comments->where('is_read',0)->count();
            }
            // $orders['count_not_read_messages'] = $count_not_read_messages;
        } else {
            // $orders_ids = DB::table('painter_orders')->where('user_id', Auth::id())->pluck('order_id');
            // $orders = Orders::whereIn('id', $orders_ids)->where('painter_payed', null)->where('status', "!=" , 'completed')->orderBy("id", 'desc')->with('order_painter_images')->paginate(5, ['*'], 'orders');
            // $orders_completed = Orders::whereIn('id', $orders_ids)->where('painter_payed', null)->where('status', 'completed')->orderBy("id", 'desc')->with('order_painter_images')->paginate(5, ['*'], 'orders_completed');
            // $payed_orders = Orders::whereIn('id', $orders_ids)->where('painter_payed', 1)->with('orders_chats')->orderBy("id", 'desc')->with('order_painter_images')->paginate(5, ['*'], 'payed_orders');

            $orders_ids = DB::table('painter_orders')->where('user_id', Auth::id())->pluck('order_id')->toArray();
            $painterOrderIds = $orders_ids;
        
            // 1) Открытые заказы (не completed, ещё не оплачен)
            $ordersQuery = Orders::query()
                ->whereNull('painter_payed')
                ->where('status', '!=', 'completed')
                ->orderBy('id', 'desc')
                ->with('order_painter_images');
            $orders = $this->applyChunkedWhereIn($ordersQuery, $orders_ids)->paginate(5, ['*'], 'orders');
            
            // 2) Завершённые заказы (status = completed, ещё не оплачен)
            $completedQuery = Orders::query()->whereNull('painter_payed')->where('status', 'completed')->orderBy('id', 'desc')->with('order_painter_images');
            $orders_completed = $this->applyChunkedWhereIn($completedQuery, $orders_ids)->paginate(5, ['*'], 'orders_completed');
            
            // 3) Оплаченные заказы (painter_payed = 1)
            $payedQuery = Orders::query()->where('painter_payed', 1)->orderBy('id', 'desc')->with(['orders_chats', 'order_painter_images']);
            $payed_orders = $this->applyChunkedWhereIn($payedQuery, $orders_ids)->paginate(5, ['*'], 'payed_orders');

            if($type == "orders_success")
            {
                $orders = $orders_completed;
                $page["title"] = $title;
            } else if($type == "payed_orders") {
                $orders = $payed_orders;
                $page["title"] = $title;
            } else {
                
            }

            $count_not_read_messages = 0;
            foreach($orders as $item)
            {
                $count_not_read_messages += $item->orders_chats->where('is_read',0)->count();
            }

        }

        $sales = Stock::withTranslation(App::getLocale())->first()->select([
            'date_1_sale', 'date_2_sale', 'custom_coupon_sale', 'lk_title',
        ])->get()[0];

        $total_amount_of_purchases = 0;

        $painterOrdersMetaByOrderId = collect();
        if (Auth::user()->role->name == 'painter') {
            $pageOrderIds = collect($orders->items())->pluck('id')->all();
            if ($pageOrderIds) {
                $painterOrdersMetaByOrderId = DB::table('painter_orders')
                    ->where('user_id', Auth::id())
                    ->whereIn('order_id', $pageOrderIds)
                    ->get(['order_id', 'created_at', 'complete_until'])
                    ->keyBy('order_id');
            }
        }

        $painter_deadline_orders = collect();

		foreach($orders as $order)
		{
			$order->painter_images_status = APainterImagesStatus::getPainterImagesStatus($order->painter_images_status);
			$order->painter_sketch_images_status = APainterImagesStatus::getPainterImagesStatus($order->painter_sketch_images_status);    
            $total_amount_of_purchases += $order->price;

            $scan_items = json_decode($order['items'], true) ?: [];
            $scan_delivery = json_decode($order['delivery'], true) ?: [];

            $painterMeta = $painterOrdersMetaByOrderId->get($order->id);
            $order->painter_end_time = $this->calculatePainterEndTimeForOrder($order, $painterMeta, $scan_items, $scan_delivery);

        }

        if (Auth::user()->role->name == 'painter' && empty($type) && $painterOrderIds) {
            $deadlineQuery = Orders::query()
                ->whereNull('painter_payed')
                ->where('status', '!=', 'completed')
                ->where('status', '!=', 'in_production')
                ->orderBy('id', 'desc')
                ->select(['id', 'status', 'created_at', 'painter_endtime', 'items', 'delivery']);

            $painter_deadline_orders = $this->applyChunkedWhereIn($deadlineQuery, $painterOrderIds)->get();

            $deadlineOrderIds = $painter_deadline_orders->pluck('id')->all();
            $metaByOrderId = collect();
            if ($deadlineOrderIds) {
                $metaByOrderId = DB::table('painter_orders')
                    ->where('user_id', Auth::id())
                    ->whereIn('order_id', $deadlineOrderIds)
                    ->get(['order_id', 'created_at', 'complete_until'])
                    ->keyBy('order_id');
            }

            foreach ($painter_deadline_orders as $deadlineOrder) {
                $scan_items = json_decode($deadlineOrder->items, true) ?: [];
                $scan_delivery = json_decode($deadlineOrder->delivery, true) ?: [];
                $painterMeta = $metaByOrderId->get($deadlineOrder->id);
                $deadlineOrder->painter_end_time = $this->calculatePainterEndTimeForOrder($deadlineOrder, $painterMeta, $scan_items, $scan_delivery);
            }

            $workAcceptedStatusIds = $this->getWorkAcceptedPainterImageStatusIds();
            if (!empty($workAcceptedStatusIds) && $deadlineOrderIds) {
                $lastPainterImages = DB::table('order_painter_images')
                    ->whereIn('order_id', $deadlineOrderIds)
                    ->where('is_img_painter', 1)
                    ->select('order_id', DB::raw('MAX(id) as last_id'))
                    ->groupBy('order_id')
                    ->get();

                $lastIds = $lastPainterImages->pluck('last_id')->filter()->values()->all();
                if ($lastIds) {
                    $lastStatuses = DB::table('order_painter_images')
                        ->whereIn('id', $lastIds)
                        ->get(['order_id', 'status'])
                        ->keyBy('order_id');

                    $excludeOrderIds = [];
                    foreach ($lastStatuses as $orderId => $row) {
                        if (in_array((int) $row->status, $workAcceptedStatusIds, true)) {
                            $excludeOrderIds[] = (int) $orderId;
                        }
                    }

                    if ($excludeOrderIds) {
                        $painter_deadline_orders = $painter_deadline_orders->reject(function ($o) use ($excludeOrderIds) {
                            return in_array((int) $o->id, $excludeOrderIds, true);
                        })->values();
                    }
                }
            }

            $painter_deadline_orders = $painter_deadline_orders
                ->filter(function ($o) {
                    return !empty($o->painter_end_time);
                })
                ->sortBy(function ($o) {
                    return strtotime($o->painter_end_time);
                })
                ->values();
        }

        // if($sort == "make_first")
        // {
        //     $orders = $orders->sortBy('painter_end_time');
        //     dd($orders);
        // }

        $cur_user_id=Auth::user()->id;     
        $coupons = DB::table('coupons')->where('user_id', $cur_user_id)->where('is_active', 1)->get();

        //-----------
        $topmailClass= new \App\Helpers\GalleryTopMail;
        $topmail= $topmailClass->get_topmail_data();
        $images_folder = 'https://viarcanvas.com/storage/';
        $contry_mult=$topmailClass->get_gallery_price();

        $user_id = Auth::id();
        $user_code = Auth::user()->inv_sale_code;

        if ($user_code == null || $user_code == 'regenerate') {
            $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
            $user_code = mb_substr(str_shuffle($rand_code), 0, 8);
            DB::table('users')->where('id', $user_id)->update([
                'inv_sale_code' => $user_code,
            ]);
        }

        $modals = view(env('THEME_RESOURCES') . 'pages.stocks.modals')   ->with([
            'invite_code' => $user_code,
        ]);
        //--------------

       
		$APainterImagesStatus = APainterImagesStatus::all();

        $content = view(config('theme.resource') . 'account.orders')->with([
            'page' => $page,
            'orders' => $orders,
            'payed_orders' => $payed_orders,
            'orders_completed' => $orders_completed,
            'sales' => $sales,
            'coupons' => $coupons,
            'images_folder' => $images_folder,
            'top_mail' => $topmail,
            'multiplyer' => $contry_mult,
            'total_amount_of_purchases' => $total_amount_of_purchases,
            'APainterImagesStatus' => $APainterImagesStatus,
            'count_not_read_messages' => $count_not_read_messages,
            'painter_deadline_orders' => $painter_deadline_orders,
        ]);


        // $modals = '';


        if($type == "orders_success")
        {
            $orders = $orders_completed;
        } 
        else if($type == "payed_orders") {
            $orders = $payed_orders;
        }
        else
        {
            $this->vars = Arr::add($this->vars, 'title', __("account_new.orders.meta_title"));
            $this->vars = Arr::add($this->vars, 'meta_desc', __("account_new.orders.meta_desc"));
        }

        $this->vars = Arr::add($this->vars, 'modals', $modals);
        $this->vars = Arr::add($this->vars, 'content', $content);
        return $this->renderOutput();
    }

    public function order_payment($orderId)
    {
        $order = $this->resolveClientOrderForPayment($orderId);

        if ($order->payment_status === 'payed') {
            return redirect()->route('new_account.orders');
        }

        $page['title'] = __('account_new.pay_order_title');
        $paymentMethods = WebToPay::PAYSERA_METHODS_MAP;
        $paymentMethods['paypalOnetimePayment'] = [
            'title' => 'PayPal',
            'title_translate' => false,
            'img' => 'img/icons/PayPal.svg',
        ];

        $content = view(config('theme.resource') . 'account.order_payment')->with([
            'page' => $page,
            'order' => $order,
            'orderData' => $this->buildClientPaymentOrderData($order),
            'paymentMethods' => $paymentMethods,
        ]);

        $this->vars = Arr::add($this->vars, 'title', __('account_new.pay_order_meta_title'));
        $this->vars = Arr::add($this->vars, 'meta_desc', __('account_new.pay_order_meta_desc'));
        $this->vars = Arr::add($this->vars, 'content', $content);

        return $this->renderOutput();
    }

    public function start_order_payment(\Illuminate\Http\Request $request, $orderId)
    {
        $order = $this->resolveClientOrderForPayment($orderId);

        if ($order->payment_status === 'payed') {
            return redirect()->route('new_account.orders');
        }

        $request->validate([
            'payment' => [
                'required',
                'string',
                Rule::in(array_merge(array_keys(WebToPay::PAYSERA_METHODS_MAP), ['paypalOnetimePayment'])),
            ],
        ]);

        return $this->clientOrderPaymentService->start($order, $request->input('payment'));
    }

    // Вспомогательная функция для whereIn с чанками
    function applyChunkedWhereIn($query, array $ids, string $column = 'id', int $chunkSize = 999)
    {
        return $query->where(function ($q) use ($ids, $column, $chunkSize) {
            foreach (array_chunk($ids, $chunkSize) as $chunk) {
                $q->orWhereIn($column, $chunk);
            }
        });
    }

    private function parseCabinetDateTime($value, bool $endOfDayIfNoTime = false)
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (!is_string($value)) {
            return null;
        }

        $raw = trim($value);
        if ($raw === '' || $raw === 'null') {
            return null;
        }

        $dt = null;
        try {
            $dt = Carbon::parse($raw);
        } catch (\Throwable $e) {
            foreach (['d.m.Y H:i:s', 'd.m.Y H:i', 'd.m.Y', 'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d'] as $format) {
                try {
                    $dt = Carbon::createFromFormat($format, $raw);
                    break;
                } catch (\Throwable $e2) {
                    // ignore
                }
            }
        }

        if (!$dt) {
            return null;
        }

        if ($endOfDayIfNoTime && !preg_match('/\\d{1,2}:\\d{2}/', $raw)) {
            $dt = $dt->endOfDay();
        }

        return $dt;
    }

    private function calculatePainterEndTimeForOrder($order, $painterMeta, array $scan_items, array $scan_delivery)
    {
        $expressTerms = ["Express", "Ekspress", "Ekspresowy", "Экспресс", "Kiirsaadetis", "Ekspres"];
        $isExpress = false;

        foreach ($scan_items as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (array_key_exists('is_manual_express', $item)) {
                $manualExpress = $item['is_manual_express'];
                $isManualExpress = (
                    (is_bool($manualExpress) && $manualExpress) ||
                    (is_numeric($manualExpress) && (int)$manualExpress === 1) ||
                    (is_string($manualExpress) && in_array(strtolower(trim($manualExpress)), ['1', 'true', 'yes', 'on'], true))
                );
                if ($isManualExpress) {
                    $isExpress = true;
                    break;
                }
            }
            foreach ($expressTerms as $term) {
                if (isset($item['terms']) && $item['terms'] && stripos($item['terms'], $term) !== false) {
                    $isExpress = true;
                    break 2;
                }
            }
        }

        if (!$isExpress && array_key_exists('is_manual_express', $scan_delivery)) {
            $manualExpress = $scan_delivery['is_manual_express'];
            $isExpress = (
                (is_bool($manualExpress) && $manualExpress) ||
                (is_numeric($manualExpress) && (int)$manualExpress === 1) ||
                (is_string($manualExpress) && in_array(strtolower(trim($manualExpress)), ['1', 'true', 'yes', 'on'], true))
            );
        }

        $isUrgent = $isExpress || (isset($scan_items['total_terms_price']) && (float) $scan_items['total_terms_price'] > 0);
        $order->painter_is_urgent = $isUrgent;
        $order->painter_is_express = $isExpress;

        $desiredDelivery = $this->parseCabinetDateTime($scan_delivery['when_send'] ?? null, true);

        $explicitDeadlineRaw = null;
        // admin "Время на заказ" (completed_at) has priority
        if (!empty($order->painter_endtime)) {
            $explicitDeadlineRaw = $order->painter_endtime;
        } elseif ($painterMeta && !empty($painterMeta->complete_until)) {
            $explicitDeadlineRaw = $painterMeta->complete_until;
        }
        $explicitDeadline = $this->parseCabinetDateTime($explicitDeadlineRaw, true);

        if ($isUrgent) {
            $candidateDeadline = Carbon::now()->setTime(16, 0, 0);
            if ($explicitDeadline && $explicitDeadline->lessThan($candidateDeadline)) {
                $candidateDeadline = $explicitDeadline;
            }
        } else {
            if ($explicitDeadline) {
                $candidateDeadline = $explicitDeadline;
            } else {
                $startFrom = null;
                if ($painterMeta && !empty($painterMeta->created_at)) {
                    $startFrom = $this->parseCabinetDateTime($painterMeta->created_at);
                }

                if (!$startFrom) {
                    $startFrom = $this->parseCabinetDateTime($order->created_at);
                }

                $candidateDeadline = $startFrom ? $startFrom->copy()->addDays(3)->endOfDay() : Carbon::now()->addDays(3)->endOfDay();
            }
        }

        if ($desiredDelivery && $candidateDeadline->greaterThan($desiredDelivery)) {
            $candidateDeadline = $desiredDelivery;
        }

        $order->painter_deadline_time = $candidateDeadline->format('H:i');
        return $candidateDeadline->format('Y-m-d H:i:s');
    }

    private function getWorkAcceptedPainterImageStatusIds()
    {
        $ids = collect();

        try {
            $fromStatuses = DB::table('a_painter_images_status')
                ->where('title', 'like', '%Work accepted%')
                ->pluck('id');
            $ids = $ids->merge($fromStatuses);
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            $fromTranslations = DB::table('translations')
                ->where('table_name', 'a_painter_images_status')
                ->where('column_name', 'title')
                ->where('value', 'like', '%Work accepted%')
                ->pluck('foreign_key');
            $ids = $ids->merge($fromTranslations);
        } catch (\Throwable $e) {
            // ignore
        }

        return $ids
            ->filter()
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();
    }

    public function mystocks()
    {
        $topmailClass= new \App\Helpers\GalleryTopMail;
        $topmail= $topmailClass->get_topmail_data();
        $images_folder = 'https://viarcanvas.com/storage/';
        $contry_mult=$topmailClass->get_gallery_price();

        if (Auth::check()) {
            $user_id = Auth::id();
            $user_code = Auth::user()->inv_sale_code;

            if ($user_code == null || $user_code == 'regenerate') {
                $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
                $user_code = mb_substr(str_shuffle($rand_code), 0, 8);
                DB::table('users')->where('id', $user_id)->update([
                    'inv_sale_code' => $user_code,
                ]);
            }

            $modals = view(env('THEME_RESOURCES') . 'pages.stocks.modals')   ->with([
                'invite_code' => $user_code,
            ]);
            $this->vars = Arr::add($this->vars, 'modals', $modals);

        }
        else
        { 
            $user_id = null; 
        }

        // sales
        $module_sale = GalleryItem::where('id_type', 2)->where('custom_size_prices_sale', '!=', 'NULL')->where('is_big_sale', 0)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $foto_sale = GalleryItem::where('id_type', 3)->where('custom_size_prices_sale', '!=', 'NULL')->where('is_big_sale', 0)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $repr_sale = GalleryItem::where('id_type', 4)->where('custom_size_prices_sale', '!=','NULL')->where('is_big_sale', 0)->whereDate('sale_end','>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');

        // big salse
        $module_big_sale = GalleryItem::where('id_type', 2)->where('custom_size_prices_sale','!=', null)->where('is_big_sale', 1)->whereDate('sale_end', '>=',Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $foto_big_sale = GalleryItem::where('id_type', 3)->where('custom_size_prices_sale','!=', null)->where('is_big_sale', 1)->where('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $repr_big_sale = GalleryItem::where('id_type', 4)->where('custom_size_prices_sale','!=', null)->where('is_big_sale', 1)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');

        $page = Stock::first()->get()->translate(App::getLocale(), 'ru')[0];
        $head = $page;

        $gallery = GalleryPage::first()->get()->translate(App::getLocale(), 'ru')[0];

        $page["title"] = __("account_new.stocks.bread"); 
        $content = view(config('theme.resource') . 'account.stocks')
        ->with([
            'page' => $page,
            'head' => $head,
            'mod_sale' => $module_sale,
            'foto_sale' => $foto_sale,
            'repr_sale' => $repr_sale,
            'module_big_sale' => $module_big_sale,
            'foto_big_sale' => $foto_big_sale,
            'repr_big_sale' => $repr_big_sale,
            'user_id' => $user_id,
            'gallery' => $gallery,
            'images_folder' => $images_folder,
            'top_mail' => $topmail,
            'multiplyer' => $contry_mult
        ]);

        $modals = '';

        $this->vars = Arr::add($this->vars, 'title', __("account_new.stocks.meta_title"));
        $this->vars = Arr::add($this->vars, 'meta_desc', __("account_new.stocks.meta_desc"));
        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'modals', $modals);

        return $this->renderOutput();
    }

    public function settings()
    {
        //-----------
        $topmailClass= new \App\Helpers\GalleryTopMail;
        $topmail= $topmailClass->get_topmail_data();
        $images_folder = 'https://viarcanvas.com/storage/';
        $contry_mult=$topmailClass->get_gallery_price();

        $user_id = Auth::id();
        $user_code = Auth::user()->inv_sale_code;

        if ($user_code == null || $user_code == 'regenerate') {
            $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
            $user_code = mb_substr(str_shuffle($rand_code), 0, 8);
            DB::table('users')->where('id', $user_id)->update([
                'inv_sale_code' => $user_code,
            ]);
        }

        $modals = view(env('THEME_RESOURCES') . 'pages.stocks.modals')   ->with([
            'invite_code' => $user_code,
        ]);
        //--------------

        $c_tels = CountryTel::orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');

        $page["title"] = __("account_new.settings.bread"); 
        $content = view(config('theme.resource') . 'account.settings')->with([
            'page' => $page,
            'c_tels' => $c_tels,
            'images_folder' => $images_folder,
            'top_mail' => $topmail,
            'multiplyer' => $contry_mult
        ]);




        $this->vars = Arr::add($this->vars, 'title', __("account_new.settings.meta_title"));
        $this->vars = Arr::add($this->vars, 'meta_desc', __("account_new.settings.meta_desc"));
        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'modals', $modals);

        return $this->renderOutput();
    }

    public function changeOrderPainterImageStatus(\Illuminate\Http\Request $request)
    {
        $order_painter_image = false;
        $order_user_comments = null;
        if(isset($request->check_comment) && $request->check_comment)
        {
            $order_user_comments = DB::table('order_user_comments')->where('user_id', Auth::id())->where('order_id', $request->order_id)->where('order_painter_image_id', $request->order_painter_image_id)->where('created_at', '>=', Carbon::now()->subMinutes(30))->first();
            $check_comment = $order_user_comments ? true : false;
        } else {
            $check_comment = null;
        }

        // dd($request->check_comment, $order_user_comments, $check_comment, $request->all());

        if ($check_comment == true || $check_comment === null)
        {
            // dd($request->check_comment, $order_user_comments, $check_comment, $request->all());

            $order_painter_image = OrderPainterImages::where("id", $request->order_painter_image_id)
            ->where("order_id", $request->order_id)
            ->update(['status' => $request->status_name, 'updated_at' => now()]);

            // $order_painter_image= false; // удалить нужно после проверке на фронте
        }

        if($order_painter_image){
            $response['success'] = 'Статус обновлен!';
            $response['info'] = true;
            $response['check_comment'] = $check_comment;
        } else {
            $response['error'] = 'Error status update!';
            $response['info'] = false;
            $response['check_comment'] = $check_comment;
        }

        return json_encode($response);
    }

    public function unpaid()
    {
        $page["title"] = __("account_new.unpaid.bread"); 
        $content = view(config('theme.resource') . 'account.unpaid')->with([
            'page' => $page,
        ]);

        $modals = '';

        $this->vars = Arr::add($this->vars, 'title', __("account_new.unpaid.meta_title"));
        $this->vars = Arr::add($this->vars, 'meta_desc', __("account_new.unpaid.meta_desc"));
        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'modals', $modals);

        return $this->renderOutput();
    }

    public function paid(\Illuminate\Http\Request $request)
    {
        // $page["title"] = __("account_new.paid.bread"); 
        // $content = view(config('theme.resource') . 'account.paid')->with([
        //     'page' => $page,
        // ]);

        // $modals = '';

        $this->vars = Arr::add($this->vars, 'title', __("account_new.paid.meta_title"));
        $this->vars = Arr::add($this->vars, 'meta_desc', __("account_new.paid.meta_desc"));
        // $this->vars = Arr::add($this->vars, 'content', $content);
        // $this->vars = Arr::add($this->vars, 'modals', $modals);

        return $this->orders($request,"payed_orders", __("account_new.paid.bread"));

        // return $this->renderOutput();
    }

    public function orders_success(\Illuminate\Http\Request $request)
    {
        
        // $page["title"] = __("account_new.orders_success.bread"); 
        // $content = view(config('theme.resource') . 'account.orders_success')->with([
        //     'page' => $page,
        // ]);

        // $modals = '';

        $this->vars = Arr::add($this->vars, 'title', __("account_new.orders_success.meta_title"));
        $this->vars = Arr::add($this->vars, 'meta_desc', __("account_new.orders_success.meta_desc"));
        // $this->vars = Arr::add($this->vars, 'content', $content);
        // $this->vars = Arr::add($this->vars, 'modals', $modals);

        return $this->orders($request,"orders_success", __("account_new.orders_success.bread"));
        // return $this->renderOutput();
    }





    /**
     * Display user cabinet
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    { 
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
			$order->painter_sketch_images_status = APainterImagesStatus::getPainterImagesStatus($order->painter_sketch_images_status);    
        }

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
    public function set_all_painter_images()
    {
        $orders = Orders::all();

        foreach($orders as $order)
        {
            //наброски
            if($order->painter_sketch_images)
            {
                $images = explode(',', $order->painter_sketch_images);
                foreach($images as $image)
                {
                    if($image)
                    {
                        $image = str_replace("/ ","/",$image);
                        $image = str_replace(" http","http",$image);
                        $image = str_replace("https://viarcanvas.com/","",$image);
                        $image = str_replace("http://viarcanvas.com/","",$image);
                        $image = str_replace("http://viarcanvas.loc/","",$image);
                        $image = str_replace("https://viarcanvas.loc/","",$image);
                        $image = str_replace("https://www.viarcanvas.com/","",$image);
                        $image = str_replace("http://www.viarcanvas.com/","",$image);

                        
                        $is_image = OrderPainterImages::where('order_id', $order->id)->where('image', $image)->first();
                        if(isset($is_image->id))
                        {
                            //$is_image->updated_at = now();

                            if($order->painter_sketch_images_status)
                            {
                                $is_image->status = $order->painter_sketch_images_status;
                            }

                            if($order->status == "completed")
                            {
                                $is_image->status = 5;
                            }

                            $is_image->save();

                        } else {
                            OrderPainterImages::insert([
                                'order_id' => $order->id,
                                'image' => $image,
                                'is_img_sketch' => 1,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }

            }

            //картины
            if($order->painter_images_status)
            {
                $images = explode(',', $order->painter_images);
                foreach($images as $image)
                {
                    if($image)
                    {
                        $image = str_replace("/ ","/",$image);
                        $image = str_replace(" http","http",$image);
                        $image = str_replace("https://viarcanvas.com/","",$image);
                        $image = str_replace("http://viarcanvas.com/","",$image);
                        $image = str_replace("http://viarcanvas.loc/","",$image);
                        $image = str_replace("https://viarcanvas.loc/","",$image);
                        $image = str_replace("https://www.viarcanvas.com/","",$image);
                        $image = str_replace("http://www.viarcanvas.com/","",$image);


                        $is_image = OrderPainterImages::where('order_id', $order->id)->where('image', $image)->first();
                        if(isset($is_image->id))
                        {
                            $is_image->updated_at = now();

                            if($order->painter_images_status)
                            {
                                $is_image->status = $order->painter_images_status;
                            }

                            if($order->status == "completed")
                            {
                                $is_image->status = 5;
                            }

                            $is_image->save();

                        } else {
                            OrderPainterImages::insert([
                                'order_id' => $order->id,
                                'image' => $image,
                                'is_img_painter' => 1,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }

                
            }




        }

        dd("ок");
        return true;
    }



    public function update_painter_order_images(\Illuminate\Http\Request $request, UpdatePainterImageService $service)
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
    public function update_painter_sketch_order_images(\Illuminate\Http\Request $request, UpdatePainterSketchImageService $service)
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
    public function update_painter_comment(\Illuminate\Http\Request $request)
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
    public function send_client_painter_comments(\Illuminate\Http\Request $request, SendClientPainterImageService $service)
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

        if(isset($request['msg']) && $request['msg']) {
            $request['client_comment'] = $request['msg'];
        }

        if($request->client_comment != null && $request->client_comment != "") {

            $data = [
                'comment' => $request->client_comment,
                'order_id' => $request->order_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_id' => Auth::id(),
                'is_read' => 1,
            ];

            if(isset($request->is_img_painter) && $request->is_img_painter) {
                $data['is_img_painter'] = 1;
                $data['order_painter_image_id'] = $request->order_painter_image_id;
            }

            if(isset($request->is_img_sketch) && $request->is_img_sketch) {
                $data['is_img_sketch'] = 1;
                $data['order_painter_image_id'] = $request->order_painter_image_id;
            }

            $updated_comments = DB::table('order_user_comments')->insert($data);
        }

        if(!$request->client_comment) {
            $request['client_comment'] = "";
            $updated_comments = true;
        }

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
     * @param  \Illuminate\Http\Request                     $request
     * @param  \App\Services\SendClientPainterImageService  $service
     * @return false|string
     */
    public function send_admin_to_client_painter_comments(\Illuminate\Http\Request $request, SendClientPainterImageService $service)
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

        if(isset($request['msg']) && $request['msg']) {
            $request['client_comment'] = $request['msg'];
        }

        if($request->client_comment != null && $request->client_comment != "") {

            $data = [
                'comment' => $request->client_comment,
                'order_id' => $request->order_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_id' => Auth::id(),
                'is_admin' => 1,
                'is_read' => 0,
                'admin_is_read' => 1,
            ];

            if(isset($request->is_img_painter) && $request->is_img_painter) {
                $data['is_img_painter'] = 1;
                $data['order_painter_image_id'] = $request->order_painter_image_id;
            }

            if(isset($request->is_img_sketch) && $request->is_img_sketch) {
                $data['is_img_sketch'] = 1;
                $data['order_painter_image_id'] = $request->order_painter_image_id;
            }

            $updated_comments = DB::table('order_user_comments')->insert($data);
        }

        if(!$request->client_comment) {
            $request['client_comment'] = "";
            $updated_comments = true;
        }

        $user_id = DB::table('orders')->where('id', $request->order_id)->pluck('user_id')->first();
        $user = User::where('id', $user_id)->get()->first();
        $msg_data = UserMessage::first()->get()->translate($user->preferredLocale(), 'ru')[0];

        $data['subject'] = $msg_data['user_painter_mail_subject'] . ' ' . $request->order_id;
        $data['text'] = $request->client_comment;
        $data['order_id'] = $request->order_id;

        $data['admin_email'] = env('ADMIN_MAIL');
        $data['comment_images'] = $comment_images;


        $data['user_email'] = DB::table('users')->where('id', $user_id)->pluck('email')->first();
        Mail::to($data['user_email'])->send(new AdminToUserComment($data, $user->preferredLocale()));

        app(SynvolveWebhookService::class)->notifyManagerMessageForOrder((int) $request->order_id, (string) $request->client_comment, [
            'trigger' => 'legacy_admin_order_chat',
            'source' => 'order_user_comments',
            'has_attachments' => !empty($comment_images),
        ]);


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
    public function update_order_chat(\Illuminate\Http\Request $request)
    {
        if(isset($request->msg) && $request->msg) {
            $request['painter_msg'] = $request->msg;
        }

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


        $data = [
            'comment' => $request->painter_msg,
            'orders_id' => $request->order_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'is_admin' => 0,
            'is_read' => 1,
        ];

        if(isset($request->is_img_painter) && $request->is_img_painter)
        {
            $data['is_img_painter'] = 1;
            $data['order_painter_image_id'] = $request->order_painter_image_id;
        }
        
        if(isset($request->is_img_sketch) && $request->is_img_sketch) {
            $data['is_img_sketch'] = 1;
            $data['order_painter_image_id'] = $request->order_painter_image_id;
        }

        $updated = DB::table('orders_chats')->insert($data);

        return json_encode(['status' => $updated, 'comment' => $request->painter_msg]);
    }

    public function message_read(\Illuminate\Http\Request $request)
    {
        $data = ['is_read' => 1];
        $chatId = $request->input('chatId'); 

        if(Auth::user()->role->name == 'painter') {
            $updated = DB::table('orders_chats')->where('id', $chatId)->update($data);
        } else {
            $updated = DB::table('order_user_comments')->where('id', $chatId)->update($data);
        }

        return json_encode(['status' => $updated]);
    }

    public function admin_to_client_message_read(\Illuminate\Http\Request $request)
    {
        $data = ['admin_is_read' => 1];
        $chatId = $request->input('chatId'); 
        $updated = DB::table('order_user_comments')->where('id', $chatId)->update($data);

        return json_encode(['status' => $updated]);
    }


    public function admin_message_read(\Illuminate\Http\Request $request)
    {
        $data = ['admin_is_read' => 1];
        $chatId = $request->input('chatId'); 
        $updated = DB::table('orders_chats')->where('id', $chatId)->update($data);

        return json_encode(['status' => $updated]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User          $user
     * @return false|string
     */
    public function ajax_change_information(\Illuminate\Http\Request $request, User $user)
    {
        $pass_result = false;
        $errors = [];
        // Проверка на смену пароля
        if (
            !empty($request->input('old_password')) || 
            !empty($request->input('password')) || 
            !empty($request->input('password_confirmation')
            )
        ) {

            $oldPassword = $request->input('old_password');
            $newPassword = $request->input('password');
            $passwordConfirmation = $request->input('password_confirmation');

            // Проверка старого пароля
            if (empty($oldPassword) && !$oldPassword) {
                $errors['old_password'] = [__("auth2.error_old_password")];
            }

            if (!Hash::check($oldPassword, Auth::user()->password)) {
                $errors['old_password'] = [__("auth2.error_incorrect_old_password")];
            }

            // Проверка нового пароля и его подтверждения
            if (empty($newPassword) && !$newPassword) {
                $errors['password'] = [__("auth2.error_password")];
            }

            if ($newPassword !== $passwordConfirmation) {
                $errors['password_confirmation'] = [__("auth2.error_password_confirmation")];
            }

            if($errors)
            {
                return json_encode(['status' => 'error', 'errors' => $errors]);
            }

    
            $pass_result = $user->change_password([
                'id' => Auth::id(),
                'password' => Hash::make($newPassword),
            ]);
        }
    
        $infoToUpdate = [];
    
        if (!empty($request->input('first_name'))) {
            $infoToUpdate['first_name'] = $request->input('first_name');
        }
    
        if (!empty($request->input('last_name'))) {
            $infoToUpdate['last_name'] = $request->input('last_name');
        }
    
        if (!empty($request->input('phone'))) {
            $infoToUpdate['phone'] = $request->input('phone');
        }
    
        if (!empty($request->input('news'))) {
            $infoToUpdate['news'] = $request->input('news');
        }
    
        if (!empty($request->input('ad'))) {
            $infoToUpdate['ad'] = $request->input('ad');
        }
    
        if (!empty($request->input('client_data'))) {
            $infoToUpdate['client_data'] = $request->input('client_data');
        }
    
        if (!empty($request->input('address'))) {
            $infoToUpdate['address'] = $request->input('address');
        }
    
        if (!empty($request->input('country'))) {
            $infoToUpdate['country'] = $request->input('country');
        }
    
        $info_result = User::where('id', Auth::id())->update($infoToUpdate);
    
        if ($info_result || $pass_result) {
            return json_encode(['status' => 'ok', 'message' => '', 'errors' => $errors]);
        }
        
        return json_encode(['status' => 'error', 'message' => 'Failed to update information.','errors' => $errors]);
    }

    private function resolveClientOrderForPayment($orderId): Orders
    {
        abort_if(Auth::user()->role->name === 'painter', 404);

        return Orders::query()
            ->where('id', (int) $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    private function buildClientPaymentOrderData(Orders $order): array
    {
        $delivery = json_decode($order->delivery, true) ?: [];
        $items = json_decode($order->items, true) ?: [];

        $toFloat = static function ($value): float {
            if (is_float($value) || is_int($value)) {
                return (float) $value;
            }

            $value = preg_replace('/[^0-9,\\.\\-]/', '', (string) $value);
            $value = str_replace(',', '.', (string) $value);

            return (float) $value;
        };

        $originalSubtotal = $toFloat($order->price ?? 0);
        $saleSubtotalRaw = $order->sale_price ?? null;
        $saleSubtotal = ($saleSubtotalRaw !== null && $saleSubtotalRaw !== '') ? $toFloat($saleSubtotalRaw) : null;

        $discountedSubtotal = $originalSubtotal;
        if ($saleSubtotal !== null && $saleSubtotal > 0 && $saleSubtotal < $originalSubtotal) {
            $discountedSubtotal = $saleSubtotal;
        }

        $saleEur = $toFloat($order->sale_eur ?? 0);
        if ($saleEur > 0) {
            $discountedSubtotal -= $saleEur;
        }

        $salePercent = $toFloat($order->sale_percent ?? 0);
        if ($salePercent > 0) {
            $discountedSubtotal -= $discountedSubtotal * ($salePercent / 100);
        }

        if ($discountedSubtotal < 0) {
            $discountedSubtotal = 0;
        }

        $termsPrice = isset($items['total_terms_price']) ? $toFloat($items['total_terms_price']) : 0.0;
        if ($termsPrice <= 0) {
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $termsPrice += $toFloat($item['terms_price'] ?? 0);
            }
        }

        $deliveryPrice = isset($delivery['deliv_price']) ? $toFloat($delivery['deliv_price']) : 0.0;
        $total = round($discountedSubtotal + $termsPrice + $deliveryPrice, 2);

        $paymentStatuses = [
            'not_payed' => __('account_new.payment_status_unpaid'),
            'prepayment' => __('account_new.payment_status_prepayment'),
            'payed' => __('account_new.payment_status_paid'),
        ];

        return [
            'delivery' => $delivery,
            'items' => $items,
            'discounted_subtotal' => round($discountedSubtotal, 2),
            'original_subtotal' => round($originalSubtotal, 2),
            'terms_price' => round($termsPrice, 2),
            'delivery_price' => round($deliveryPrice, 2),
            'total' => $total,
            'payment_status_label' => $paymentStatuses[$order->payment_status] ?? __('account_new.payment_status_unpaid'),
        ];
    }
}
