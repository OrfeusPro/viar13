<?php

namespace App\Http\Controllers\Admin;

use DB;
use Str;
use File;
use Hash;
use Mail;
use Throwable;
use App\Models\User;
use App\Models\Orders;
use App\Models\OrderPainterImages;
use App\Models\GalleryBox;
use App\Models\GalleryItem;
use App\Models\GalleryHolst;
use App\Models\GalleryDecoration;
use App\Models\AOrderFrom;
use App\Models\ADeliveryTown;
use App\Models\CountryTel;
use App\Models\OrderPaymentRequest;
use App\Models\DeliveryPickupAtViarWorkshop;
use App\Mail\SendAdminOrder;
use App\Services\SynvolveWebhookService;
use App\Services\Admin\OrderItemPresentationService;
use Illuminate\Http\Request;
use App\Mail\SendUserRegister;
use App\Http\Controllers\Controller;
use App\Models\APainterImagesStatus;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\DynamicPDFController;
use App\Mail\SaleFacebook;
//use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;



class OrdersController extends Controller
{
    private $Orders;

    public function __construct()
    {
        $this->Orders = app(Orders::class);
        $this->DynamicPDF = app(DynamicPDFController::class);
    }

    public function create(Request $request, IndexController $IndexController)
    {

        $order = false;
        $user = false;

        if($request->input('from_order_id'))
        {

            $order = $this->Orders->getOrderByIdStatic($request->input('from_order_id'));
            try {
                $order = (array) $order[0];
            } catch (Throwable $th) {
                return back();
            }

            $user = User::findOrFail($order['user_id']);
        }


        $style = $IndexController->get_styles_for_quiz('ru');

        $managers = DB::table('users')
            ->where('role_id', 4)
            ->orderBy('role_id', 'desc')
            ->get();

        return view('vendor.voyager.order_create')
        ->with('c_tels', CountryTel::orderBy('sort', 'asc')->get()->translate('ru'))
        ->with('deliveryPickupAtViarWorkshop', DeliveryPickupAtViarWorkshop::where('is_show', 1)->orderBy('sort', 'asc')->get()->translate('ru'))
        ->with('deliveryTowns', ADeliveryTown::orderBy('id', 'asc')->get()->translate('ru'))
        ->with('order_from', AOrderFrom::all())
        ->with('managers', $managers)
        ->with('user', $user)
        ->with('style', $style);

    }

    public function changePdfLocale(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|integer|exists:users,id',
            'pdf_locale' => 'required|string|max:10',
        ]);

        $updated = User::where('id', $request->user_id)
            ->update(['pdf_locale' => $request->pdf_locale]);

        if ($updated) {
            $response = [
                'info'    => 1,
                'success' => 'Язык счета успешно обновлён!',
            ];
        } else {
            $response = [
                'info'    => 0,
                'success' => 'Не удалось обновить язык счета.',
            ];
        }

        return response()->json($response);
    }

    public function edit_admin_order(Request $request, IndexController $IndexController)
    {
        $style = $IndexController->get_styles_for_quiz('ru');
        $order = $this->Orders->getOrderByIdStatic($request->id);

        try {
            $order = (array) $order[0];
        } catch (Throwable $th) {
            return back();
        }

        $orderModel = Orders::find((int)$request->id);
        if ($orderModel && is_string($orderModel->items)) {
            $decodedItems = json_decode($orderModel->items, true);
            if (is_array($decodedItems)) {
                $locale = $this->resolveClientLocale($orderModel, null);
                $updatedItems = $decodedItems;
                $needsSave = false;
                foreach ($decodedItems as $itemKey => $itemData) {
                    if (!is_int($itemKey) || !is_array($itemData)) {
                        continue;
                    }
                    $normalized = $this->syncManualCodesFromLegacy($itemData);
                    $prepared = $this->applyManualPresentation($normalized, $locale);
                    if ($prepared !== $itemData) {
                        $updatedItems[$itemKey] = $prepared;
                        $needsSave = true;
                    }
                }

                if ($needsSave) {
                    $orderModel->items = json_encode($updatedItems);
                    $orderModel->save();
                    $order['items'] = json_encode($updatedItems);
                }
            }
        }

        $order['order_painter_images'] = OrderPainterImages::query()
            ->where('order_id', (int) $request->id)
            ->orderBy('id', 'asc')
            ->get();

        if ($order['is_admin_order'] !== 2) {
            $user = User::findOrFail($order['user_id']);
            $c_tels = CountryTel::orderBy('sort', 'asc')->get()->translate($user->preferredLocale(), 'en');
        } else {
            $c_tels = CountryTel::orderBy('sort', 'asc')->get()->translate('en');
        }


		$managers = DB::table('users')
				->where('role_id', 4)
				->orderBy('role_id', 'desc')
				->get();

		$APainterImagesStatus = APainterImagesStatus::all();
        $deliveryPickupAtViarWorkshop = DeliveryPickupAtViarWorkshop::where('is_show', 1)
            ->orderBy('sort', 'asc')
            ->get()
            ->translate('ru');
        $deliveryTowns = ADeliveryTown::orderBy('id', 'asc')
            ->get()
            ->translate('ru');
        $paymentRequests = OrderPaymentRequest::query()
            ->where('order_id', (int) $request->id)
            ->orderByDesc('id')
            ->get();

        return view('voyager::order', [
            'style' => $style,
            'order' => $order,
            'c_tels' => $c_tels,
            'APainterImagesStatus' => $APainterImagesStatus,
            'managers' => $managers,
            'order_from' => AOrderFrom::all(),
            'deliveryPickupAtViarWorkshop' => $deliveryPickupAtViarWorkshop,
            'deliveryTowns' => $deliveryTowns,
            'paymentRequests' => $paymentRequests,
        ]);
    }

    protected function resolveClientLocale(?Orders $order = null, ?User $user = null): string
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

        if ($user instanceof User && is_string($user->preferredLocale()) && $user->preferredLocale() !== '') {
            return $user->preferredLocale();
        }

        if ($order instanceof Orders && !empty($order->user_id)) {
            $orderUser = User::find((int)$order->user_id);
            if ($orderUser && is_string($orderUser->preferredLocale()) && $orderUser->preferredLocale() !== '') {
                return $orderUser->preferredLocale();
            }
        }

        return 'ru';
    }

    protected function isExpressText(string $text): bool
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

    protected function isStandardTermsText(string $text, string $locale): bool
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

    protected function resolveGiftCodeFromItem(array $item): string
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

        $complId = isset($item['compl_id']) ? (int)$item['compl_id'] : 0;
        if ($complId === 1) {
            return 'G2';
        }
        if ($complId === 2) {
            return 'G1';
        }
        return 'G0';
    }

    protected function resolveDecorationIdFromItem(array $item): int
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

    protected function resolveCanvasIdFromItem(array $item): int
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

    protected function syncManualCodesFromLegacy(array $item): array
    {
        $legacyDetected = isset($item['boxIds']) || isset($item['decorationId']) || isset($item['formId']);
        if (!$legacyDetected) {
            return $item;
        }

        $manualGiftCode = strtoupper(trim((string)($item['manual_gift_code'] ?? '')));
        if (!in_array($manualGiftCode, ['G0', 'G1', 'G2'], true)) {
            $giftCode = 'G0';
            if (isset($item['boxIds'])) {
                $boxIds = $item['boxIds'];
                if (is_string($boxIds)) {
                    $decoded = json_decode($boxIds, true);
                    if (is_array($decoded)) {
                        $boxIds = $decoded;
                    }
                }
                $boxIdsList = is_array($boxIds) ? $boxIds : [$boxIds];
                foreach ($boxIdsList as $boxId) {
                    $id = (int)$boxId;
                    if ($id === 1) {
                        $giftCode = 'G2';
                        break;
                    }
                    if ($id === 2) {
                        $giftCode = 'G1';
                    }
                }
            } elseif (isset($item['compl_id'])) {
                $id = (int)$item['compl_id'];
                if ($id === 1) {
                    $giftCode = 'G2';
                } elseif ($id === 2) {
                    $giftCode = 'G1';
                }
            }
            $item['manual_gift_code'] = $giftCode;
        }

        $manualDecoration = isset($item['manual_decoration_id']) ? (int)$item['manual_decoration_id'] : 0;
        if (!in_array($manualDecoration, [1, 2, 3, 5], true)) {
            $legacyDecoration = (int)($item['decorationId'] ?? ($item['decor_id'] ?? ($item['decorId'] ?? 0)));
            if (!in_array($legacyDecoration, [1, 2, 3, 5], true)) {
                $legacyDecoration = 5;
            }
            $item['manual_decoration_id'] = $legacyDecoration;
            $manualDecoration = $legacyDecoration;
        }

        $decorationMap = [
            1 => ['lac' => 'L2', 'brush' => 'P0'],
            2 => ['lac' => 'L0', 'brush' => 'P1'],
            3 => ['lac' => 'L1', 'brush' => 'P0'],
            5 => ['lac' => 'L0', 'brush' => 'P0'],
        ];
        if (!isset($item['manual_lac_code']) || !in_array(strtoupper((string)$item['manual_lac_code']), ['L0', 'L1', 'L2'], true)) {
            $item['manual_lac_code'] = $decorationMap[$manualDecoration]['lac'];
        }
        if (!isset($item['manual_brushstrokes_code']) || !in_array(strtoupper((string)$item['manual_brushstrokes_code']), ['P0', 'P1'], true)) {
            $item['manual_brushstrokes_code'] = $decorationMap[$manualDecoration]['brush'];
        }

        $manualOrientation = strtoupper(trim((string)($item['manual_orientation_code'] ?? '')));
        if (!in_array($manualOrientation, ['V0', 'V1', 'V2', 'V3', 'V4'], true)) {
            $formId = (int)($item['formId'] ?? ($item['forma_id'] ?? ($item['form_id'] ?? 0)));
            if ($formId >= 1 && $formId <= 4) {
                $item['manual_orientation_code'] = 'V' . $formId;
            } else {
                $item['manual_orientation_code'] = 'V0';
            }
        }

        return $item;
    }

    protected function applyManualPresentation(array $item, string $locale): array
    {
        return app(OrderItemPresentationService::class)->apply($item, $locale);
    }

    protected function create_admin_order_action($user, $request, $delivery, $random_pass = '')
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


        $basket = [];

        $basket_name=$request->input('styles');
        if ($request->input('styles')==null) {$basket_name=$request->input('new_name');}
        $readItemCheckbox = static function (Request $request, string $key, int $index): int {
            $values = $request->input($key, []);
            if (!is_array($values)) {
                return 0;
            }

            $value = $values[$index] ?? null;
            if ($value === null) {
                return 0;
            }

            if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                return 1;
            }

            return (is_numeric($value) && (int)$value === 1) ? 1 : 0;
        };
        $readItemValue = static function (Request $request, string $key, int $index, string $default = ''): string {
            $values = $request->input($key, []);
            if (!is_array($values)) {
                return $default;
            }

            $value = $values[$index] ?? null;
            if ($value === null) {
                return $default;
            }

            return trim((string)$value);
        };


        $orderLocale = $this->resolveClientLocale(null, $user);
        $hasManualExpressInBasket = false;
        for ($i = 0; $i < count($request->input('basket_item')); $i++) {
            $basket[$i]['name'] = $basket_name ?? $request->input('basket_name')[$i];
            $request->input('basket_price')[$i];
            $basket[$i]['basketType'] = '1';
            $basket[$i]['is_def_product'] = '1';
            $data['orig_images'] = [];

			$new_fname = Orders::getOrderImageName('', '', false, $auto_increment, $request->input('basket_size')[$i], $user, $basket_name ?? $request->input('basket_name')[$i]);

            $orig_images = $request->hasFile('basket_images_' . $i);
            if ($orig_images && !empty($orig_images)) {
                $data['orig_images'] = [];
                $j = -1;
                $files = $request->file('basket_images_' . $i);
                foreach ($files as $file) {
                    $j++;
					$file_extension = mb_strtolower(File::extension($file->getClientOriginalName()));
					$file_name = \Storage::disk('uploads')->putFileAs('orders', $file, $new_fname."_".$j.".".$file_extension);
                    //$file_name = \Storage::disk('uploads')->put('uploads', $file);
                    $data['orig_images'][$j] = \URL::to('/') . '/' . $file_name;
                }
            }

            $basket[$i]['activeImage'] = '';
            $basket[$i]['count'] = 1;
            $basket[$i]['pack'] = '';
            if(isset($request['pack']) && $request['pack'])
            {
                $basket[$i]['pack'] = $request->input('pack')[$i];
            }
            if(isset($request['quiz_orig_images']) && $request['quiz_orig_images'])
            {
                $data['orig_images'] = $request->input('quiz_orig_images');
            }
            if(isset($request->input('users_count')[$i]) && $request->input('users_count')[$i])
            {
                $basket[$i]['users_count'] = $request->input('users_count')[$i];
                $basket[$i]['is_port_product'] = 1;
            }
            $basket[$i]['orig_images'] = $data['orig_images'];
            $basket[$i]['size_name'] = $request->input('basket_size')[$i];
            $basket[$i]['userComment'] = $request->input('basket_comment')[$i];
            $basket[$i]['terms'] = $request->input('basket_terms')[$i];
            $basket[$i]['price'] = $request->input('basket_price')[$i];
            $basket[$i]['sumPrice'] = $request->input('basket_price')[$i];
            $basket[$i]['sumFormatedPrice'] = $request->input('basket_price')[$i] . ' €';
            $basket[$i]['formatedPrice'] = $request->input('basket_price')[$i] . ' €';
            $giftCode = strtoupper($readItemValue($request, 'basket_manual_gift_code', $i, 'G0'));
            if (!in_array($giftCode, ['G0', 'G1', 'G2'], true)) {
                $giftCode = 'G0';
            }
            $manualDecorationId = (int)$readItemValue($request, 'basket_manual_decoration_id', $i, '5');
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
            $orientationCode = strtoupper($readItemValue($request, 'basket_manual_orientation_code', $i, 'V0'));
            if (!in_array($orientationCode, ['V0', 'V1', 'V2', 'V3', 'V4'], true)) {
                $orientationCode = 'V0';
            }
            $manualCanvasId = (int)$readItemValue($request, 'basket_manual_canvas_id', $i, '2');
            if ($manualCanvasId < 1 || $manualCanvasId > 5) {
                $manualCanvasId = 2;
            }

            $basket[$i]['manual_gift_code'] = $giftCode;
            $basket[$i]['manual_decoration_id'] = $manualDecorationId;
            $basket[$i]['manual_lac_code'] = $lacCode;
            $basket[$i]['manual_brushstrokes_code'] = $brushstrokesCode;
            $basket[$i]['manual_orientation_code'] = $orientationCode;
            $basket[$i]['manual_canvas_id'] = $manualCanvasId;
            $basket[$i]['canvasId'] = $manualCanvasId;
            $manualBagetCode = strtoupper($readItemValue($request, 'basket_manual_baget_code', $i, ''));
            if (!in_array($manualBagetCode, ['B0', 'B1', 'B2'], true)) {
                $manualBagetCode = $readItemCheckbox($request, 'basket_manual_baget', $i) ? 'B1' : 'B0';
            }
            $basket[$i]['manual_baget_code'] = $manualBagetCode;
            $basket[$i]['is_manual_baget'] = $manualBagetCode === 'B1' ? 1 : 0;
            $basket[$i]['is_manual_express'] = $readItemCheckbox($request, 'basket_manual_express', $i);
            $basket[$i] = $this->applyManualPresentation($basket[$i], $orderLocale);
            if ($basket[$i]['is_manual_express'] === 1) {
                $hasManualExpressInBasket = true;
            }
            if ($request->input('has_gift')!==NULL) {$basket[$i]['has_gift'] = $request->input('has_gift')[$i]; }
        }

        $deliveryManualExpress = false;
        if (isset($delivery['is_manual_express'])) {
            $value = $delivery['is_manual_express'];
            $deliveryManualExpress = (
                (is_bool($value) && $value) ||
                (is_numeric($value) && (int)$value === 1) ||
                (is_string($value) && in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true))
            );
        }
        $delivery['is_manual_express'] = ($deliveryManualExpress || $hasManualExpressInBasket) ? 1 : 0;

        // In edit, totals come from items. In create, totals are synced in JS,
        // but we also compute them server-side as a safety net.
        $summFromItems = 0.0;
        $basketPrices = $request->input('basket_price', []);
        if (is_array($basketPrices)) {
            foreach ($basketPrices as $priceItem) {
                $summFromItems += (float)str_replace(',', '.', (string)$priceItem);
            }
        }
        $summFromItems = round($summFromItems, 2);
        $request['price'] = $summFromItems;
        $request['sale_price'] = $summFromItems;

        if (isset($request['bonus']) && (float)$request['bonus'] > 0) {
            $bonus = (float)$request['bonus'];
            $request['price'] = max(0, (float)$request['price'] - $bonus);
            $request['sale_price'] = max(0, (float)$request['sale_price'] - $bonus);
        }




        $basket['totalPrice'] = $request['price'];
        $basket['saved_price'] = $request['price'];
        $basket['formatedTotalPrice'] = $request['price'] . ' €';



        if(isset(\Auth::user()->id))
        {
            $user_id = \Auth::user()->id;
        }
        else
        {
            $user_id = 1;
        }

        $request['price'] = str_replace(' €','', $request['price']);
        if(!isset($request['new_catid']))
        {
            $request['new_catid'] = 0;
        }
        if ($request['comment']=="") {$comment=$request['comments'];} else {$comment=$request['comment'];}

        //dd($request);
        $selectedManagerId = 0;
        if (isset($request['manager']) && $request['manager'] && $request['manager'] !== 'all') {
            $selectedManagerId = (int)$request['manager'];
        }

        $paymentStatus = $request['payment_status'] ?? 'not_payed';
        if (!in_array($paymentStatus, ['not_payed', 'prepayment', 'payed'], true)) {
            $paymentStatus = 'not_payed';
        }

        $order_id = DB::table('orders')->insertGetId([
            'user_id' => $user->id,
            'manager_id' => $selectedManagerId,
            'is_admin_order' => 1,
            'price' => $request['price'],
            'sale_price' => $request['sale_price'],
            'sale_eur' => $request['sale_eur'] ?? 0,
            'sale_percent' => $request['sale_percent'] ?? 0,
            'items' => json_encode($basket),
            'country' => $request['country'],
            'a_order_from' => $request['a_order_from'],
            'delivery' => json_encode($delivery),
            'payment' => $request['payment'],
            'payment_status' => $paymentStatus,
            'comment' => $comment,
            'admin_comment' => $request['admin_comment'] ?? null,
            'catid' => $request['new_catid'],
            'status' => 'watching',
            'order_image' => null,
            'photo' => null,
            'ur_name'       => (isset($request['ur_name']) && $request['ur_name'] == 'on') ? 'on' : '',
            'ur_name_l'     => $request['ur_name_l'] ?? '',
            'ur_reg_num'    => $request['ur_reg_num'] ?? '',
            'ur_legal_addr' => $request['ur_legal_addr'] ?? '',
            'ur_pnr_nr'     => $request['ur_pnr_nr'] ?? '',
            'ur_bank_name'  => $request['ur_bank_name'] ?? '',
            'ur_bank_code'  => $request['ur_bank_code'] ?? '',
            'ur_bank_acc_code' => $request['ur_bank_acc_code'] ?? '',
            'created_at' => date('Y-m-j H:i:s'),
            'updated_at' => date('Y-m-j H:i:s'),
        ]);

        Orders::renameUploadsPhoto($order_id);

        // send user email

        Mail::to($user->email)->send(new SendAdminOrder($user->preferredLocale(), $order_id, $random_pass));
        app(SynvolveWebhookService::class)->notifyOrderSnapshotById((int) $order_id, 'admin_order_created');

        return $order_id;
    }


    public function create_admin_order(Request $request)
    {
        if(isset($request['phone']))
        {
            $request['phone'] = str_replace(" ","", $request['phone']);
            $request['phone'] = str_replace(")","", $request['phone']);
            $request['phone'] = str_replace("(","", $request['phone']);
            $request['phone'] = str_replace("-","", $request['phone']);
        }

        if(isset($request['phone_rec']))
        {
            $request['phone_rec'] = str_replace(" ","", $request['phone_rec']);
            $request['phone_rec'] = str_replace(")","", $request['phone_rec']);
            $request['phone_rec'] = str_replace("(","", $request['phone_rec']);
            $request['phone_rec'] = str_replace("-","", $request['phone_rec']);
        }

        $phone = $request['phone'] ?? null;
        $phoneRec = $request['phone_rec'] ?? null;
        if (is_string($phone)) {
            $phone = trim($phone);
        }
        if (is_string($phoneRec)) {
            $phoneRec = trim($phoneRec);
        }
        if ($phoneRec === '') {
            $phoneRec = null;
        }

        // delivery info
        $delivery = [];
        $delivery['deliv_price'] = $request['deliv_price'];
        $delivery['when_send'] = $request['when_send'];
        $delivery['email'] = $request['email'];
        $delivery['first_name'] = $request['first_name'];
        $delivery['last_name'] = $request['last_name'];
        $delivery['phone'] = $phoneRec ?: $phone;
        $delivery['payer_phone'] = $phone;
        $delivery['address'] = $request['address'];
        $delivery['postal_index'] = $request['postal_index'];
        $delivery['country'] = $request['country'];
        $delivery['a_order_from'] = $request['a_order_from'];
        $delivery['city'] = $request['city'];
        $delivery['sposob'] = $request['sposob'];
        $delivery['payment'] = $request['payment'] ?? null;
        $delivery['is_manual_express'] = $request->boolean('is_manual_express') ? 1 : 0;
        $comment = $request['comment'] ?? null;
        if (is_string($comment)) {
            $comment = trim($comment);
        }
        $delivery['comment'] = ($comment === '' || $comment === 'null') ? null : $comment;

        // Delivery location id + photo short code (for correct naming and future edits).
        $delivery['pickup_workshop_id'] = null;
        $delivery['delivery_town_id'] = null;
        $delivery['delivery_photo_short_code'] = null;

        $pickupWorkshopId = (int)$request->input('pickup_workshop_id', 0);
        $deliveryTownId = (int)$request->input('delivery_town_id', 0);

        if ($delivery['sposob'] === 'pickup_Riga' && $pickupWorkshopId <= 0) {
            $pickupWorkshopId = 1;
        }
        if (($delivery['sposob'] === 'pickup_Daugavplis' || $delivery['sposob'] === 'pickup_Daugavpils') && $pickupWorkshopId <= 0) {
            $pickupWorkshopId = 2;
        }

        if (in_array($delivery['sposob'], ['pickup_at_viar_workshop', 'pickup_Riga', 'pickup_Daugavplis', 'pickup_Daugavpils'], true)) {
            if ($pickupWorkshopId > 0) {
                $pickupRow = DB::table('delivery_pickup_at_viar_workshop')
                    ->select('id', 'title', 'photo_short_code')
                    ->where('id', $pickupWorkshopId)
                    ->first();
                if ($pickupRow) {
                    $delivery['pickup_workshop_id'] = (int)$pickupRow->id;
                    $delivery['address'] = $pickupRow->title;
                    if ($delivery['sposob'] === 'pickup_at_viar_workshop') {
                        $delivery['city'] = null;
                    } else {
                        $delivery['city'] = $pickupRow->title;
                    }
                    $photoCode = trim((string)($pickupRow->photo_short_code ?? ''));
                    $delivery['delivery_photo_short_code'] = $photoCode !== '' ? $photoCode : null;
                }
            }
        } elseif ($delivery['sposob'] === 'city_delivery') {
            if ($deliveryTownId > 0) {
                $townRow = DB::table('a_delivery_towns')
                    ->select('id', 'city', 'photo_short_code')
                    ->where('id', $deliveryTownId)
                    ->first();
                if ($townRow) {
                    $delivery['delivery_town_id'] = (int)$townRow->id;
                    $delivery['city'] = $townRow->city;
                    $photoCode = trim((string)($townRow->photo_short_code ?? ''));
                    $delivery['delivery_photo_short_code'] = $photoCode !== '' ? $photoCode : null;
                }
            }
        }



        // create user
        $check_user = User::where('email', $request['email'])->first();



        $random_pass = '';
        if (!$check_user) {
            $random_pass = Str::random(8);
            $user = new User();
            $user->first_name = $delivery['first_name'] ?? '';
            $user->last_name = $delivery['last_name'] ?? '';
            $user->email = $delivery['email'];
            $user->phone = $delivery['phone'] ?? '';
            $user->address = $delivery['address'] ?? '';
            $user->postal_index = $delivery['postal_index'] ?? '';
            $user->country = $delivery['country'] ?? '';
            $user->client_data = 'NO';
            $user->news = 'YES';
            $user->role_id = 2;
            $user->avatar = 'users/default.png';
            $user->active_coupon = null;
            $user->password = Hash::make($random_pass);


            if(isset($delivery['country'])) {
                $cur_loc = strtolower($delivery['country']);
            }
            else
            {
                $cur_loc = strtolower(app()->getLocale());
            }

            $settings = $user->settings;
            $settings['locale'] = $cur_loc;
            $user->settings = $settings;
            $user->save();

            // send user notify
            Mail::to($user->email)->send(new SendUserRegister($user, $random_pass, $cur_loc));
            $order_id = $this->create_admin_order_action($user, $request, $delivery, $random_pass);
        } else {
                 if ((int)$request['bonus']>0)
                 {
                     if ($check_user->bonuses>=$request['bonus'])
                     {
                         $check_user->bonuses=$check_user->bonuses-(int)$request['bonus'];
                         $check_user->save();
                         $delivery['bonus']=$request['bonus'];
                     }
                 }
            $order_id = $this->create_admin_order_action($check_user, $request, $delivery, $random_pass);
        }

        if(isset($request['quiz_order']) && $request['quiz_order'])
        {
            return ['order_id' => $order_id];
        }
        else
        {
            return back()->with('order_id', $order_id)->with('success', 1)->with('message', 'Заказ успешно добавлен!');
        }
    }

    public function update_order_item_price(Request $request)
    {
        $auth_user = \Auth::user();

        if ($auth_user->role->name == 'admin' || $auth_user->role->name == 'manager') {
            $order_id = $request->order_id;
            $item_index = $request->index;
            $item_name = $request->name;
            $item_price = round($request->price, 2);
            $item_sizeId = $request->sizeId;
            $item_size_name = $request->size_name;
            $item_comment = $request->input('userComment', null);
            if (is_string($item_comment)) {
                $item_comment = trim($item_comment);
            }
            $order = Orders::find($order_id);
            $items = json_decode($order->items, true);
            $items[$item_index]['name'] = $item_name;
            $items[$item_index]['price'] = $item_price;
            $items[$item_index]['sumPrice'] = $item_price;
            $items[$item_index]['sumFormatedPrice'] = $item_price . ' €';
            $items[$item_index]['formatedPrice'] = $item_price . ' €';
            $items[$item_index]['formatedTotalPrice'] = $item_price . ' €';
            if (!empty($item_sizeId)) {
                $items[$item_index]['sizeId'] = $item_sizeId;
                $items[$item_index]['show']['size'] = $item_sizeId;
            }
            if (!empty($item_size_name)) {
                $items[$item_index]['size_name'] = $item_size_name;
            }
            if ($item_comment !== null) {
                $items[$item_index]['userComment'] = $item_comment === '' ? null : $item_comment;
            }

            $giftCode = strtoupper(trim((string)$request->input('manual_gift_code', 'G0')));
            if (!in_array($giftCode, ['G0', 'G1', 'G2'], true)) {
                $giftCode = 'G0';
            }
            $items[$item_index]['manual_gift_code'] = $giftCode;

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
            $items[$item_index]['manual_decoration_id'] = $manualDecorationId;
            $items[$item_index]['manual_lac_code'] = $decorationMap[$manualDecorationId]['lac'];
            $items[$item_index]['manual_brushstrokes_code'] = $decorationMap[$manualDecorationId]['brush'];

            $orientationCode = strtoupper(trim((string)$request->input('manual_orientation_code', 'V0')));
            if (!in_array($orientationCode, ['V0', 'V1', 'V2', 'V3', 'V4'], true)) {
                $orientationCode = 'V0';
            }
            $items[$item_index]['manual_orientation_code'] = $orientationCode;
            $manualCanvasId = (int)$request->input('manual_canvas_id', 2);
            if ($manualCanvasId < 1 || $manualCanvasId > 5) {
                $manualCanvasId = 2;
            }
            $items[$item_index]['manual_canvas_id'] = $manualCanvasId;
            $items[$item_index]['canvasId'] = $manualCanvasId;

            $manualBagetCode = strtoupper(trim((string)$request->input('manual_baget_code', '')));
            if (!in_array($manualBagetCode, ['B0', 'B1', 'B2'], true)) {
                $manualBagetCode = $request->boolean('manual_baget') ? 'B1' : 'B0';
            }
            $items[$item_index]['manual_baget_code'] = $manualBagetCode;
            $items[$item_index]['is_manual_baget'] = $manualBagetCode === 'B1' ? 1 : 0;
            $items[$item_index]['is_manual_express'] = $request->boolean('manual_express') ? 1 : 0;
            $itemLocale = $this->resolveClientLocale($order, null);
            $items[$item_index] = $this->applyManualPresentation($items[$item_index], $itemLocale);
            $termsTotal = 0.0;
            foreach ($items as $k => $it) {
                if (!is_int($k) || !is_array($it)) {
                    continue;
                }
                $termsTotal += (float)($it['terms_price'] ?? 0);
            }
            $items['total_terms_price'] = round($termsTotal, 2);

            $hasManualExpress = false;
            foreach ($items as $k => $it) {
                if (!is_int($k) || !is_array($it)) {
                    continue;
                }
                $value = $it['is_manual_express'] ?? null;
                $isManualExpress = (
                    (is_bool($value) && $value) ||
                    (is_numeric($value) && (int)$value === 1) ||
                    (is_string($value) && in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true))
                );
                if ($isManualExpress) {
                    $hasManualExpress = true;
                    break;
                }
            }

            $delivery = json_decode($order->delivery, true);
            if (!is_array($delivery)) {
                $delivery = [];
            }
            $delivery['is_manual_express'] = $hasManualExpress ? 1 : 0;
            $order->delivery = json_encode($delivery);

            $order->items = json_encode($items);
            $order->save();

            Orders::renameUploadsPhoto($order_id);
            $this->update_order_price($order_id);
            $order = $this->Orders->getOrderById($order_id);
            $order = (array) $order[0];
            $order['delivery'] = json_decode($order['delivery'], true);
            $order['items'] = json_decode($order['items'], true);
            $order['price'] = rtrim(rtrim(number_format((float)$order['price'], 2, ',', ' '), '0'), ',') . ' €';
            $order_vr_id = Orders::getVRById($order_id);

            $this->DynamicPDF->getPDFFromOrder($order, $order_vr_id, null);
            return back();
        }
    }

    public function update_admin_order(Request $request)
    {
        if(isset($request['phone']))
        {
            $request['phone'] = str_replace(" ","", $request['phone']);
            $request['phone'] = str_replace(")","", $request['phone']);
            $request['phone'] = str_replace("(","", $request['phone']);
            $request['phone'] = str_replace("-","", $request['phone']);
        }

        if(isset($request['phone_rec']))
        {
            $request['phone_rec'] = str_replace(" ","", $request['phone_rec']);
            $request['phone_rec'] = str_replace(")","", $request['phone_rec']);
            $request['phone_rec'] = str_replace("(","", $request['phone_rec']);
            $request['phone_rec'] = str_replace("-","", $request['phone_rec']);
        }

        $params['delivery'] = [];
        $existingOrder = Orders::find($request->order_id);
        $existingDelivery = [];
        if ($existingOrder && is_string($existingOrder->delivery)) {
            $decodedDelivery = json_decode($existingOrder->delivery, true);
            if (is_array($decodedDelivery)) {
                $existingDelivery = $decodedDelivery;
            }
        }

        $normalizeDeliveryValue = function ($value) {
            if (!is_string($value)) {
                return '';
            }
            return trim($value);
        };
        $phone = $request['phone'] ?? null;
        $phoneRec = $request['phone_rec'] ?? null;
        if (is_string($phone)) {
            $phone = trim($phone);
        }
        if (is_string($phoneRec)) {
            $phoneRec = trim($phoneRec);
        }
        if ($phoneRec === '') {
            $phoneRec = null;
        }

        $params['delivery']['first_name'] = $request['first_name'];
        $params['delivery']['last_name'] = $request['last_name'];
        $params['delivery']['email'] = $request['email'];
        $params['delivery']['city'] = $request['city'];
        $params['delivery']['phone'] = $phoneRec ?: $phone;
        $params['delivery']['payer_phone'] = $phone;
        $params['delivery']['when_send'] = $request['when_send'];
        $params['delivery']['address'] = $request['address'];
        $params['delivery']['postal_index'] = $request['postal_index'];
        $params['delivery']['country'] = $request['country'];
        $params['delivery']['sposob'] = $request['sposob'];
        $params['delivery']['deliv_price'] = $request['deliv_price'];
        $itemsHasManualExpress = false;
        if ($existingOrder && is_string($existingOrder->items)) {
            $decodedItems = json_decode($existingOrder->items, true);
            if (is_array($decodedItems)) {
                foreach ($decodedItems as $itemKey => $itemData) {
                    if (!is_int($itemKey) || !is_array($itemData)) {
                        continue;
                    }
                    $value = $itemData['is_manual_express'] ?? null;
                    $isManualExpress = (
                        (is_bool($value) && $value) ||
                        (is_numeric($value) && (int)$value === 1) ||
                        (is_string($value) && in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true))
                    );
                    if ($isManualExpress) {
                        $itemsHasManualExpress = true;
                        break;
                    }
                }
            }
        }
        $existingManualExpress = false;
        if (array_key_exists('is_manual_express', $existingDelivery)) {
            $value = $existingDelivery['is_manual_express'];
            $existingManualExpress = (
                (is_bool($value) && $value) ||
                (is_numeric($value) && (int)$value === 1) ||
                (is_string($value) && in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true))
            );
        }
        if ($request->has('is_manual_express')) {
            $params['delivery']['is_manual_express'] = $request->boolean('is_manual_express') ? 1 : 0;
        } else {
            $params['delivery']['is_manual_express'] = ($existingManualExpress || $itemsHasManualExpress) ? 1 : 0;
        }
        $params['delivery']['pickup_workshop_id'] = null;
        $params['delivery']['delivery_town_id'] = null;
        $params['delivery']['delivery_photo_short_code'] = null;

        $pickupWorkshopId = (int)$request->input('pickup_workshop_id', 0);
        $deliveryTownId = (int)$request->input('delivery_town_id', 0);

        if ($params['delivery']['sposob'] === 'pickup_Riga' && $pickupWorkshopId <= 0) {
            $pickupWorkshopId = 1;
        }
        if (($params['delivery']['sposob'] === 'pickup_Daugavplis' || $params['delivery']['sposob'] === 'pickup_Daugavpils') && $pickupWorkshopId <= 0) {
            $pickupWorkshopId = 2;
        }

        if (
            $params['delivery']['sposob'] === 'pickup_at_viar_workshop' ||
            $params['delivery']['sposob'] === 'pickup_Riga' ||
            $params['delivery']['sposob'] === 'pickup_Daugavplis' ||
            $params['delivery']['sposob'] === 'pickup_Daugavpils'
        ) {
            if ($pickupWorkshopId > 0) {
                $pickupRow = DB::table('delivery_pickup_at_viar_workshop')
                    ->select('id', 'title', 'photo_short_code')
                    ->where('id', $pickupWorkshopId)
                    ->first();
                if ($pickupRow) {
                    $params['delivery']['pickup_workshop_id'] = (int)$pickupRow->id;
                    $params['delivery']['address'] = $pickupRow->title;
                    if ($params['delivery']['sposob'] === 'pickup_at_viar_workshop') {
                        $params['delivery']['city'] = null;
                    } else {
                        $params['delivery']['city'] = $pickupRow->title;
                    }
                    $photoCode = trim((string)($pickupRow->photo_short_code ?? ''));
                    $params['delivery']['delivery_photo_short_code'] = $photoCode !== '' ? $photoCode : null;
                }
            } else {
                // Для старых заказов без id сохраняем прошлый id/код, если способ не менялся.
                if (in_array(($existingDelivery['sposob'] ?? null), ['pickup_at_viar_workshop', 'pickup_Riga', 'pickup_Daugavplis', 'pickup_Daugavpils'], true)) {
                    $params['delivery']['pickup_workshop_id'] = $existingDelivery['pickup_workshop_id'] ?? null;
                    $params['delivery']['delivery_photo_short_code'] = $existingDelivery['delivery_photo_short_code'] ?? null;
                }
            }
        } elseif ($params['delivery']['sposob'] === 'city_delivery') {
            if ($deliveryTownId > 0) {
                $townRow = DB::table('a_delivery_towns')
                    ->select('id', 'city', 'photo_short_code')
                    ->where('id', $deliveryTownId)
                    ->first();
                if ($townRow) {
                    $params['delivery']['delivery_town_id'] = (int)$townRow->id;
                    $params['delivery']['city'] = $townRow->city;
                    $photoCode = trim((string)($townRow->photo_short_code ?? ''));
                    $params['delivery']['delivery_photo_short_code'] = $photoCode !== '' ? $photoCode : null;
                }
            } else {
                if (($existingDelivery['sposob'] ?? null) === 'city_delivery') {
                    $params['delivery']['delivery_town_id'] = $existingDelivery['delivery_town_id'] ?? null;
                    $params['delivery']['delivery_photo_short_code'] = $existingDelivery['delivery_photo_short_code'] ?? null;
                }
            }
        }

        $oldSposob = $normalizeDeliveryValue($existingDelivery['sposob'] ?? '');
        $oldCountry = strtoupper($normalizeDeliveryValue($existingDelivery['country'] ?? ''));
        $oldCity = $normalizeDeliveryValue($existingDelivery['city'] ?? '');
        $oldAddress = $normalizeDeliveryValue($existingDelivery['address'] ?? '');

        $newSposob = $normalizeDeliveryValue($params['delivery']['sposob'] ?? '');
        $newCountry = strtoupper($normalizeDeliveryValue($params['delivery']['country'] ?? ''));
        $newCity = $normalizeDeliveryValue($params['delivery']['city'] ?? '');
        $newAddress = $normalizeDeliveryValue($params['delivery']['address'] ?? '');

        $deliveryIdentityChanged = (
            $oldSposob !== $newSposob ||
            $oldCountry !== $newCountry ||
            $oldCity !== $newCity ||
            $oldAddress !== $newAddress
        );

        if ($deliveryIdentityChanged && !$params['delivery']['delivery_photo_short_code']) {
            $params['delivery']['delivery_photo_short_code'] = null;
        }

        if ($request['comment']) {
            $params['delivery']['comment'] = $request['comment'];
            $params['comment'] = $request['comment'];
        }

        $params['catid'] = $request['new_catid'];
        $params['a_order_from'] = $request['a_order_from'];
        $params['price'] = $request['price'];
        $params['sale_price'] = $request['sale_price'];
        $params['sale_eur'] = $request['sale_eur'];
        $params['sale_percent'] = $request['sale_percent'];
        $params['payment'] = $request['payment'];
        $params['payment_status'] = $request['payment_status'];
        $params['admin_comment'] = $request['admin_comment'];

		if($request['manager'] && $request['manager']!='all')
		{
			$params['manager_id'] = $request['manager'];
		}
		else{
			$params['manager_id'] = 0;
		}

        if ($request['approved_date'] != '' && $request['approved_date'] != null) {
            $params['approved_date'] = $request['approved_date'];
        }

        $params['ur_name'] = $request->has('ur_name') && $request['ur_name'] == 'on' ? 'on' : '';
        $params['ur_name_l'] = $request['ur_name_l'] ?? '';
        $params['ur_reg_num'] = $request['ur_reg_num'] ?? '';
        $params['ur_legal_addr'] = $request['ur_legal_addr'] ?? '';
        $params['ur_pnr_nr'] = $request['ur_pnr_nr'] ?? '';
        $params['ur_bank_name'] = $request['ur_bank_name'] ?? '';
        $params['ur_bank_code'] = $request['ur_bank_code'] ?? '';
        $params['ur_bank_acc_code'] = $request['ur_bank_acc_code'] ?? '';

        $updated = $this->Orders->updateOrder($request->order_id, $params);
        // edit pdf
        if ($updated) {
            Orders::renameUploadsPhoto($request->order_id);
            $order = $this->Orders->getOrderById($request->order_id);
            $order = (array) $order[0];
            $order['delivery'] = json_decode($order['delivery'], true);
            $order['items'] = json_decode($order['items'], true);
            $order['price'] = rtrim(rtrim(number_format($order['price'], 2, ',', ' '), '0'), ',') . ' €';
            //$this->DynamicPDF->getPDFFromOrder($order);
			$order_vr_id = Orders::getVRById($request->order_id);
			$this->DynamicPDF->getPDFFromOrder($order, $order_vr_id, null);
        }

        $prev_url = $request['prev_url'];
        if ($prev_url) {
            return redirect($prev_url . '#order__' . $request->order_id);
        } else {
            return back();
        }
    }

    protected function update_order_price($order_id)
    {
        $order = $this->Orders->getOrderById($order_id);
        $order = (array) $order[0];
        $order['delivery'] = json_decode($order['delivery'], true);
        $order['items'] = json_decode($order['items'], true);
        $order['price'] = (float)$order['price'];
        $nf = number_format($order['price'], 2, ',', ' ');
        $order['price'] = rtrim(rtrim($nf, '0'), ',') . ' €';
        $summ = 0;

        foreach ($order['items'] as $item) {
            if (isset($item['name']) && isset($item['price'])) {
                $summ = $summ + floatval(number_format($item['price'], 2));
            }
        }

        //$summ = number_format($summ, 2);
        $summ = round((float)$summ, 2);

        return DB::table('orders')->where('id', $order_id)->update([
            'price' => $summ,
            'sale_price' => $summ,
        ]);
    }

    public function user_filter()
    {
        $users = User::where('role_id', 2)->get();
        return view('vendor.voyager.user_filter')->with('users', $users);
    }

    public function facebookCoupon()
    {
        //$users = get all users
        $users = User::whereNotNull('screenshot')
            ->orderBy('updated_at')
            ->get();
       //$users = User::where('role_id', 2)->get();
        return view('vendor.voyager.facebook')->with('users', $users);

    }

    public function Coupon30_40()
    {
        //$users = get all users
        $users = User::whereNotNull('screenshot2')
            ->where('screenshot2', '!=', '')
            ->orderBy('updated_at')
            ->get();
        //$users = User::where('role_id', 2)->get();
        return view('vendor.voyager.coupon30_40')->with('users', $users);

    }



    public function ajaxfacebook(Request $request)
    {
        $locale = $request->input('locale');
        $locale = 'uk';
            $sale= $request->sale;
            $cur_user_id = $request->user_id;
            $usr_email = User::where('id', $cur_user_id)->pluck('email')->first();

            // Update user table where id = $cur_user_id set is_facebook_sale = $sale
             User::where('id', $cur_user_id)->update(['is_facebook_sale' => $sale]);

            $usr_name = User::where('id', $cur_user_id)->pluck('first_name')->first();
        if  ($sale==1 ) {
            // послать пользователю письмо с купоном
            $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
            $coupon_code = mb_substr(str_shuffle($rand_code), 0, 10);
            DB::table('coupons')->insert([
                'text' => $coupon_code,
                'is_facebook' => 1,
                'is_active' => 1,
                'user_id' => $cur_user_id,
            ]);

            $data['coupon_code'] = $coupon_code;
            $data['first_name']=$usr_name;
            $data['subject']='Viar sale facebook';



            \Mail::to($usr_email)->send(new \App\Mail\SaleFacebook($data));

        }
        if  ($sale==0 ) {
            $data['first_name']=$usr_name;
            $data['subject']='Viar sale facebook';







            \Mail::to($usr_email)->send(new \App\Mail\SaleFacebookDeny($data));




        }




    }


    public function ajax30_40(Request $request)
    {

        $locale = $request->input('locale');
        $locale = 'uk';
        $sale= $request->sale;
        $cur_user_id = $request->user_id;
        $usr_email = User::where('id', $cur_user_id)->pluck('email')->first();

        // Update user table where id = $cur_user_id set is_facebook_sale = $sale
        User::where('id', $cur_user_id)->update(['is_30_40' => $sale]);
        $usr_name = User::where('id', $cur_user_id)->pluck('first_name')->first();
        if  ($sale==1 ) {
            // послать пользователю письмо с купоном
            $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
            $coupon_code = mb_substr(str_shuffle($rand_code), 0, 10);
            DB::table('coupons')->insert([
                'text' => $coupon_code,
                'is_30_40_free' => 1,
                'is_active' => 1,
                'user_id' => $cur_user_id,
            ]);

            $data['coupon_code'] = $coupon_code;
            $data['first_name']=$usr_name;
            $data['subject']='Viar 30_40 sale';

            \Mail::to($usr_email)->send(new \App\Mail\Sale_30_40_new($data));

//            Mail::to($usr_email)->send(new SaleFacebook($coupon_code,$locale));
        }

    }

    public function coupon_editor()
    {
        $coupons = DB::table('coupons')->paginate(10);
        $coupons->withPath(url()->current());
        $data = [
            'coupons' => $coupons,
        ];

        return view('vendor.voyager.coupon')->with('data', $data);
    }
}
