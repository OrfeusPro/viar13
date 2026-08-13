<?php

namespace App\Repositories;

use App;
use App\Http\Controllers\BasketController;
use App\Models\AbandonedCart;
use App\Models\User;
use App\Entity\BasketType;
use App\Models\GalleryBox;
use App\Models\GalleryItem;
use App\Models\GallerySize;
use App\Models\GalleryHolst;
use App\Models\CanvasRam;
use App\Models\GalleryType;
use App\Entity\GalleryFormType;
use App\Models\GalleryDecoration;
use App\Services\ImageSaverService;
use App\Entity\GalleryExecutionType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BasketRepository
{
    private $imageSaverService;

    public function __construct(ImageSaverService $imageSaverService)
    {
        $this->imageSaverService = $imageSaverService;
    }

    public function normalizeBasket($basket): array
    {
        if ($basket === null) {
            return [];
        }

        if ($basket instanceof \Illuminate\Support\Collection) {
            return $basket->toArray();
        }

        if (is_array($basket)) {
            return $basket;
        }

        if ($basket instanceof \Traversable) {
            return iterator_to_array($basket);
        }

        if (is_string($basket)) {
            $trimmed = trim($basket);
            if ($trimmed === '') {
                return [];
            }

            $decoded = json_decode($trimmed, true);
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    public function addToBasket()
    {
        Log::info('addToBasket: начало', ['basketType' => request()->basketType]);

        try {
            $data = null;
            $type = request()->basketType;

            // Заходим в switch по типу корзины
            Log::info('addToBasket: выбор типа корзины', ['basketType' => $type]);

            switch ($type) {
                case BasketType::CANVAS_TYPE:
                    Log::info('addToBasket: формируем данные для CANVAS_TYPE');
                    $data = $this->formCanvasTypeData();
                    Log::info('addToBasket: данные для CANVAS_TYPE получены', ['data' => $data]);
                    break;

                case BasketType::MODULAR_PICTURES_TYPE:
                    Log::info('addToBasket: формируем данные для MODULAR_PICTURES_TYPE');
                    $data = $this->formModularTypeData();
                    Log::info('addToBasket: данные для MODULAR_PICTURES_TYPE получены', ['data' => $data]);
                    break;

                case BasketType::COLLAGE_TYPE:
                    Log::info('addToBasket: формируем данные для COLLAGE_TYPE');
                    $data = $this->formCollageTypeData();
                    Log::info('addToBasket: данные для COLLAGE_TYPE получены', ['data' => $data]);
                    break;

                case BasketType::GIFT_CARD:
                    Log::info('addToBasket: формируем данные для GIFT_CARD');
                    $data = $this->formGiftCardTypeData();
                    Log::info('addToBasket: данные для GIFT_CARD получены', ['data' => $data]);
                    break;

                default:
                    Log::warning('addToBasket: неизвестный тип корзины', ['basketType' => $type]);
                    throw new \InvalidArgumentException("Неизвестный тип корзины: {$type}");
            }

            Log::info('addToBasket: после switch', ['data' => $data]);

            // Устанавливаем количество
            $data['count'] = 1;
            Log::info('addToBasket: count установлен в 1');

            // Корректируем цену, если есть terms_price
            if (isset($data['terms_price'])) {
                Log::info('addToBasket: корректировка цены по terms_price', [
                    'оригинальная цена' => $data['price'],
                    'terms_price'       => $data['terms_price'],
                ]);
                $data['price'] = (float)$data['price'] - (float)$data['terms_price'];
                Log::info('addToBasket: цена скорректирована', ['новая цена' => $data['price']]);
            }

            $allSessionKeys = request()->session()->all();
            $recommendationKeys = [];
            foreach ($allSessionKeys as $key => $value) {
                if (strpos($key, 'recommendation_discount_') === 0) {
                    $recommendationKeys[$key] = $value;
                }
            }

            if (isset($data['pid']) && request()->session()->has('recommendation_discount_' . $data['pid'])) {
                $recommendationDiscount = 30;

                $data['original_price'] = $data['price'];
                $data['price'] = round($data['price'] * (1 - $recommendationDiscount / 100), 2);
                $data['recommendation_discount'] = $recommendationDiscount;
                $data['is_recommendation'] = true;
                request()->session()->forget('recommendation_discount_' . $data['pid']);

            }

            if (request()->has('is_canvas_collage')) {
                $data['is_canvas_collage'] = 1;
            }

            if (isset($data['price_label']) && $data['price_label']) {
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
            } else {
                $sizeName = $data['sizeId'] ?? $data['size_name'] ?? $data['full_size'] ?? $data['size'] ?? null;
                if ($sizeName) {
                    $data['has_special_label'] = hasSpecialLabel($sizeName);
                    $data['label_type'] = getLabelType($sizeName);
                } else {
                    $data['has_special_label'] = false;
                    $data['label_type'] = null;
                }
            }

            $basket = $this->normalizeBasket(request()->session()->get('basket', []));
            request()->session()->put('basket', $basket);
            request()->session()->push('basket', $data);

            $this->saveBasketToAbandonedCartModel($data, true);
            return 1;

        } catch (\Throwable $e) {
            Log::error('addToBasket: ошибка', [
                'сообщение' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function formCanvasTypeData()
    {

        $data = request()->except('userImage', '_token', 'boxIds', 'Image3d', 'photo_ex', 'priceLabel');

        if (request()->has('priceLabel') && request()->get('priceLabel')) {
            $data['price_label'] = strtolower(request()->get('priceLabel'));
        }

        if (! request()->hasFile('userImage')) {
            abort(422, trans('gl.error_image_canvas'));
        }

        // Сохранение userImage
        try {
            $savedImagePath = \Storage::disk('uploads')->putFile('uploads', request()->file('userImage'));
            $savedImage = \URL::to('/') . '/' . $savedImagePath;
            Log::info('formCanvasTypeData: userImage сохранён', ['path' => $savedImagePath]);
        } catch (\Throwable $e) {
            Log::error('formCanvasTypeData: ошибка при сохранении userImage', ['message' => $e->getMessage()]);
            throw $e;
        }

        // Обработка Image3d (base64)
        if ($base64 = request()->input('Image3d')) {
            Log::info('formCanvasTypeData: есть Image3d, сохраняем из base64');
            $activeImage = $this->saveBase64Image($base64);
            Log::info('formCanvasTypeData: Image3d сохранён', ['activeImage' => $activeImage]);
        } else {
            $activeImage = $savedImage;
            Log::info('formCanvasTypeData: Image3d отсутствует, используем userImage', ['activeImage' => $activeImage]);
        }

        // Обработка boxIds
        if ($boxIds = request()->get('boxIds')) {
            $decoded = json_decode($boxIds, true);
            $data['boxIds'] = $decoded;
            Log::info('formCanvasTypeData: распакованы boxIds', ['boxIds' => $decoded]);
        } else {
            Log::info('formCanvasTypeData: boxIds нет в запросе');
        }

        // Обработка orig_images
        $origImages = request()->file('orig_images');
        if ($origImages && is_array($origImages)) {
            $data['orig_images'] = [];
            Log::info('formCanvasTypeData: найдено orig_images', ['count' => count($origImages)]);
            foreach ($origImages as $i => $file) {
                try {
                    $path = \Storage::disk('uploads')->putFile('uploads', $file);
                    $url = \URL::to('/') . '/' . $path;
                    $data['orig_images'][$i] = $url;
                    Log::info("formCanvasTypeData: orig_images[$i] сохранён", ['path' => $path]);
                } catch (\Throwable $e) {
                    Log::error("formCanvasTypeData: ошибка при сохранении orig_images[$i]", [
                        'index'   => $i,
                        'message' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            }
        } else {
            Log::info('formCanvasTypeData: orig_images не переданы или не являются массивом');
        }

        // Обработка photo_ex
        $photoExUrl = '';
        if (request()->hasFile('photo_ex')) {
            try {
                $photoExPath = \Storage::disk('uploads')->putFile('uploads', request()->file('photo_ex'));
                $photoExUrl = \URL::to('/') . '/' . $photoExPath;
                Log::info('formCanvasTypeData: photo_ex сохранён', ['path' => $photoExPath]);
            } catch (\Throwable $e) {
                Log::error('formCanvasTypeData: ошибка при сохранении photo_ex', ['message' => $e->getMessage()]);
                throw $e;
            }
        } else {
            Log::info('formCanvasTypeData: photo_ex не передан');
        }
        $data['photo_ex'] = $photoExUrl;

        // Дополнительные поля
        $data['improve_photo'] = request()->get('improve_photo');
        $data['activeImage'] = $activeImage;
        $data['savedImage']  = $savedImage;
        $data['price']       = request()->get('price');
        Log::info('formCanvasTypeData: добавлены дополнительные поля', [
            'improve_photo' => $data['improve_photo'],
            'activeImage'   => $data['activeImage'],
            'savedImage'    => $data['savedImage'],
            'price'         => $data['price'],
        ]);

        Log::info('formCanvasTypeData: завершение метода', ['final_data' => $data]);
        return $data;
    }


    private function formModularTypeData()
    {
        if (!request()->hasFile('activeImage')) {
            abort(422, trans('gl.error_image_modular'));
            // abort(422, 'Активное изображение обязательно для загрузки.');
        }

        $activeImage = $this->saveImageFile(request()->file('activeImage'));

        $svg = request()->get('collageSvgImage');
        if ($svg) {
            $this->imageSaverService->saveSvgImage($svg, User::IMAGE_FOLDER);
        }


        $data = request()->except('activeImage', 'collageSvgImage', '_token', 'boxIds', 'photo_ex');

        if ($boxIds = request()->get('boxIds')) {
            $data['boxIds'] = json_decode($boxIds);
        }

        $data['activeImage'] = $activeImage;

        return $data;
    }

    private function formCollageTypeData()
    {
        $allImages = [];
        $allBackgrounds = [];
        $activeImage = null;

        if (request()->allImages) {
            foreach (json_decode(request()->allImages) as $image) {
                $path = $this->imageSaverService->base64Decode($image, User::IMAGE_FOLDER);

                array_push($allImages, $path);
            }
        }

        if (request()->allBackgrounds) {
            foreach (json_decode(request()->allBackgrounds) as $image) {
                $path = $this->imageSaverService->base64Decode($image, User::IMAGE_FOLDER);

                array_push($allBackgrounds, $path);
            }
        }

        if (request()->finalImg) {
            $image = request()->finalImg;

            $activeImage = $this->imageSaverService->base64Decode($image, User::IMAGE_FOLDER);
        }

        $data = request()->except('allImages', 'allBackgrounds', 'finalImg', '_token', 'boxIds', 'photo_ex');

        if ($boxIds = request()->get('boxIds')) {
            $data['boxIds'] = json_decode($boxIds);
        }

        if (count($allImages)) {
            $data['allImages'] = $allImages;
        }

        if (count($allBackgrounds)) {
            $data['allBackgrounds'] = $allBackgrounds;
        }

        if (!is_null($activeImage)) {
            $data['activeImage'] = $activeImage;
        }

        $data['executionId'] = GalleryExecutionType::PRINT_TYPE;

        return $data;
    }

    private function saveImageFile($image)
    {
        return $this->imageSaverService->store($image, User::IMAGE_FOLDER);
    }

    private function saveBase64Image($image)
    {
        return $this->imageSaverService->base64Decode($image, User::IMAGE_FOLDER);
    }




    // TODO: Применяем, вычесляем бонусы
    public function calc_bonus($total_price,$basket)
    {

        if (Auth::check() && $basket['spend_bonus']) {

            $user = \Auth::user();
            $bonuses=intval($user->bonuses);
            $total=$total_price-$bonuses;

            if ($total>=0)
            {
                request()->session()->put('bonus',0);
            }
            if ($total<0)
            {
                $total=0;
                $basket['bonus']=$bonuses-$total_price;
                request()->session()->put('bonus',$basket['bonus']);
            }

            return $total;
        }

    }

    // TODO: Получить свойства корзины, тут мы обрабатываем все скидки для отображения на первом этапе корзины

    public function getBasketProperties($basket, $contry_mult = 1)
    {
        $basket = $this->normalizeBasket($basket);
        if (empty($basket)) {
            return [];
        }

        $totalPrice = 0;
        $totalPriceWithLabels = 0; // Сума товарів З спеціальними позначками (без знижок)
        $totalPriceWithoutLabels = 0; // Сума товарів БЕЗ спеціальних позначок (зі знижками)



        if ($basket ) {
            $match = 0;
            $was_setted = 0;
            $sum = 0;
            foreach ($basket as $element) {
                if (isset($element["count"])) {
                    $count = intval($element["count"]);
                    $sum += $count;
                }
            }
            foreach ($basket as $key => $product) {
                if (!isset($basket[$key]['basketType'])) {
                    if (isset($basket[$key]['basketType'])) {
                        $basket[$key]['show']['basketType'] = BasketType::getName($basket[$key]['basketType']);
                    }
                }

                if (!isset($product['is_def_product'])) {
                    if (!isset($basket[$key]['is_def_product']) || !isset($basket[$key]['is_modular_inter'])) {
                        if (!isset($basket[$key]['is_canvas_inter'])) {

	                            if (!isset($basket[$key]['basketType'])) {
	                                Session::forget('basket');
	                                return [];
	                            }

                            switch ($basket[$key]['basketType']) {

                                case BasketType::CANVAS_TYPE:

                                    $basket = $this->formCanvasTypeProperties($basket, $key);

                                    break;

                                case BasketType::MODULAR_PICTURES_TYPE:

                                    $basket = $this->formModularTypeProperties($basket, $key);

                                    break;

                                case BasketType::COLLAGE_TYPE:

                                    $basket = $this->formCollageProperties($basket, $key);

                                    break;

                                case BasketType::GIFT_CARD:

                                    $basket = $this->formGiftCardTypeProperties($basket, $key);

                                    break;

                            }
                        }
                    }
                }

                // все дефолтные продукты

                if (isset($basket[$key]['is_def_product']) && !isset($basket[$key]['is_port_product'])) {

                    // Якщо це рекомендований товар - НЕ перезаписуємо ціну зі знижкою
                    if (!isset($basket[$key]['is_recommendation']) || !$basket[$key]['is_recommendation']) {
                        $dbPrice = \DB::table('gallery_items')->where('id', $product['pid'])->pluck('price_from')->first();
                        $basket[$key]['price'] = $dbPrice;
                    }

                    $size_id = GalleryItem::getSizeIdByItemId($product['pid']);

                    $basket[$key]['size_name'] = \DB::table('gallery_sizes')->where('id', $size_id)->pluck('size')->first();

                    $basket[$key]['name'] = GalleryItem::getNameById($product['pid']);

                    $basket[$key]['activeImage'] = GalleryItem::getImageById($product['pid']);

                    if (!isset($basket[$key]['is_recommendation']) || !$basket[$key]['is_recommendation']) {
                        $dbPrice2 = (float)\DB::table('gallery_items')->where('id', $product['pid'])->pluck('price_from')->first() * $contry_mult;
                        $basket[$key]['price'] = $dbPrice2;
                    } else {
                        $basket[$key]['price'] = (float)$basket[$key]['price'] * $contry_mult;
                    }

                    $basket[$key]['sumPrice'] = (float)$basket[$key]['price'] * $basket[$key]['count']; //+

                    $basket[$key]['sumFormatedPrice'] = rtrim(rtrim(number_format($basket[$key]['sumPrice'], 2, ',', ' '), '0'), ',') . ' €';

                    $basket[$key]['formatedPrice'] = rtrim(rtrim(number_format($basket[$key]['price'], 2, ',', ' '), '0'), ',') . ' €';

                    $totalPrice += $basket[$key]['sumPrice']; //+

                    if (!empty($basket[$key]['has_special_label'])) {
                        $totalPriceWithLabels += $basket[$key]['sumPrice'];
                    } else {
                        $totalPriceWithoutLabels += $basket[$key]['sumPrice'];
                    }
                } // калькулятор канвас

                elseif (isset($basket[$key]['is_canvas_inter'])) {
                    $add_to_price = $basket[$key]['add_price'];

                    if ($basket[$key]['ram_id'] != null && $basket[$key]['ram_id'] !=

                        'undefined') {
                        $ram_price = \DB::table('canvas_rams')->where('id', $basket[$key]['ram_id'])->select('price')->pluck('price')->first();

                        $clear_ram_price = preg_replace("/\s+/", '', $ram_price);
                    } else {
                        $clear_ram_price = 0;
                    }

                    // In the legacy canvas flow the frontend already sends the full
                    // item price (including selected packaging/options) in add_price.
                    // Keep that value stable here instead of relying on an undefined
                    // intermediate variable, which led to random price drift.
                    $total_price = is_numeric($add_to_price) ? (float) $add_to_price : 0.0;
                    $basket[$key]['price'] = $total_price;

                    $basket[$key]['sumPrice'] = $basket[$key]['price'] * $contry_mult; //?

                    $basket[$key]['sumFormatedPrice'] = rtrim(rtrim(number_format($basket[$key]['sumPrice'], 2, ',', ' '), '0'), ',') . ' €';

                    $basket[$key]['formatedPrice'] = rtrim(rtrim(number_format($basket[$key]['price'] * $contry_mult, 2, ',', ' '), '0'), ',') . ' €';

                    $totalPrice += $basket[$key]['sumPrice'];

                    // Розділяємо товари з позначками та без
                    if (!empty($basket[$key]['has_special_label'])) {
                        $totalPriceWithLabels += $basket[$key]['sumPrice'];
                    } else {
                        $totalPriceWithoutLabels += $basket[$key]['sumPrice'];
                    }
                } // калькулятор модульные картины

                elseif (isset($basket[$key]['is_modular_inter'])) {
                    $add_to_price = $basket[$key]['add_to_price'];

                    if ($basket[$key]['ram_id'] != null && $basket[$key]['ram_id'] !=

                        'undefined') {
                        $ram_price = \DB::table('canvas_rams')->where('id', $basket[$key]['ram_id'])->select('price')->pluck('price')->first();

                        $clear_ram_price = preg_replace("/\s+/", '', $ram_price);
                    } else {
                        $clear_ram_price = 0;
                    }


                    $total_price = ((float)$add_to_price + (float)$clear_ram_price) * $contry_mult;

                    $basket[$key]['price'] = $total_price;

                    $basket[$key]['sumPrice'] = $basket[$key]['price'] * $contry_mult; //+

                    $basket[$key]['sumFormatedPrice'] = rtrim(rtrim(number_format($basket[$key]['sumPrice'], 2, ',', ' '), '0'), ',') . ' €';

                    $basket[$key]['formatedPrice'] = rtrim(rtrim(number_format($basket[$key]['price'], 2, ',', ' '), '0'), ',') . ' €';

                    $totalPrice += $basket[$key]['sumPrice'];

                    // Розділяємо товари з позначками та без
                    if (!empty($basket[$key]['has_special_label'])) {
                        $totalPriceWithLabels += $basket[$key]['sumPrice'];
                    } else {
                        $totalPriceWithoutLabels += $basket[$key]['sumPrice'];
                    }
                }

                elseif (isset($basket[$key]['is_port_product'])) {
                    $basket[$key]['sumPrice'] = (int)$basket[$key]['price'] * $contry_mult * $basket[$key]['count']; //+

                    $basket[$key]['sumFormatedPrice'] = rtrim(rtrim(number_format($basket[$key]['sumPrice'], 2, ',', ' '), '0'), ',') . ' €';

                    $basket[$key]['formatedPrice'] = rtrim(rtrim(number_format((int)$basket[$key]['price'] * $contry_mult, 2, ',', ' '), '0'), ',') . ' €';

                    $totalPrice += $basket[$key]['sumPrice']; //+

                    // Розділяємо товари з позначками та без
                    if (!empty($basket[$key]['has_special_label'])) {
                        $totalPriceWithLabels += $basket[$key]['sumPrice'];
                    } else {
                        $totalPriceWithoutLabels += $basket[$key]['sumPrice'];
                    }
                }

                else {
                        if (isset($product['price'])) {
                            $basket[$key]['price'] = $product['price'];
                        } else {
                            $basket[$key]['price'] = $this->calcPrice($basket[$key], $contry_mult);
                        }

                    $basket[$key]['sumPrice'] = $basket[$key]['price'] * $contry_mult * $basket[$key]['count'];

                    $basket[$key]['sumFormatedPrice'] = rtrim(rtrim(number_format($basket[$key]['sumPrice'], 2, ',', ' '), '0'), ',') . ' €';

                    $basket[$key]['formatedPrice'] = rtrim(rtrim(number_format($basket[$key]['price'] * $contry_mult, 2, ',', ' '), '0'), ',') . ' €';

                    $totalPrice += $basket[$key]['sumPrice'];

                    // Розділяємо товари з позначками та без
                    if (!empty($basket[$key]['has_special_label'])) {
                        $totalPriceWithLabels += $basket[$key]['sumPrice'];
                    } else {
                        $totalPriceWithoutLabels += $basket[$key]['sumPrice'];
                    }
                }
            }

        } else {
            // TODO: Сбрасываем активный купон и бонусы , если в корзине нет товаров
            if (Auth::check()) {
             basketController::clearcart();
            }
        }

        $basket['coupon_id']=Session::get('coupon_id');
        $basket['coupon_type']=Session::get('coupon_type');
        $basket['coupon_val']=Session::get('coupon_val');
        $basket['spend_bonus']=Session::get('spend_bonus');

        $basket['totalPrice']=$totalPrice;
        $basket['totalPriceWithLabels']=$totalPriceWithLabels; // Сума товарів З позначками
        $basket['totalPriceWithoutLabels']=$totalPriceWithoutLabels; // Сума товарів БЕЗ позначок

            if ( $basket['spend_bonus']) {
                $basket['sale_price'] = $this->calc_bonus($totalPrice, $basket);
            }
            else
            {
                if (Auth::user()) {
                    $user = \Auth::user();
                    $bonuses = intval($user->bonuses);
                    Session::put('bonus', $bonuses);
                }
            }
        if ($basket['coupon_id']>0) {
            // Передаємо суми окремо: застосовуємо знижку тільки до товарів БЕЗ позначок
            $basket['sale_price'] = $this->caclSales($totalPrice, $basket, $totalPriceWithoutLabels, $totalPriceWithLabels);
        }





        $basket['bonus']=request()->session()->get('bonus');
        $basket['coupon_val']=Session::get('coupon_val');



       // $basket['totalPrice'] = $this->caclSales($basket['totalPrice'])['total'];

        $basket['formatedTotalPrice'] = rtrim(rtrim(number_format($totalPrice, 2, ',', ' '), '0'), ',') . ' €';




        return $basket;
    }

    /// TODO : вычисляем цену с учетом акций
    private function caclSales($total, $basket, $totalPriceWithoutLabels = null, $totalPriceWithLabels = null)
    {
        // Якщо не передані окремі суми, використовуємо загальну (backwards compatibility)
        if ($totalPriceWithoutLabels === null) {
            $totalPriceWithoutLabels = $total;
        }
        if ($totalPriceWithLabels === null) {
            $totalPriceWithLabels = 0;
        }

        if (Auth::user()) {
            $user = \Auth::user();

            /// Даты
            if ($basket['coupon_type'] == 'date') {
                // Застосовуємо знижку тільки до товарів БЕЗ позначок
                $discountedPrice = $totalPriceWithoutLabels;

                if ($discountedPrice < 21 && $discountedPrice > 0) {
                    $discountedPrice = $discountedPrice - ($discountedPrice * (30 / 100)); // до 20 - 30%
                    \Session::put('sale_dated', 30);
                }

                if ($discountedPrice > 20 && $discountedPrice < 31) {
                    $discountedPrice = $discountedPrice - ($discountedPrice * (20 / 100)); // до 30 - 20%
                    \Session::put('sale_dated', 20);
                }

                if ($discountedPrice > 30 && $discountedPrice < 100) {
                    $discountedPrice = $discountedPrice - ($discountedPrice * (10 / 100)); // >30 - 10%
                    \Session::put('sale_dated', 10);
                }

                if ($discountedPrice > 99) {
                    $discountedPrice = $discountedPrice - ($discountedPrice * (5 / 100)); // >99 - 5 %
                    \Session::put('sale_dated', 5);
                }

                // Додаємо товари З позначками (без знижки)
                $total = $discountedPrice + $totalPriceWithLabels;
                $total = floatval($total);
                $total = number_format($total, 2, '.', '');
                return $total;
            }
            /// Пригласи друга
            if ($basket['coupon_type'] == 'friend') {
                /// $user->is_active_friend_inv==1 Записать при удачном заказе
                ///  Добавить бонусов User
                /// Купон не удаляется
                // Застосовуємо знижку тільки до товарів БЕЗ позначок
                $discountedPrice = $totalPriceWithoutLabels - 5;
                $total = $discountedPrice + $totalPriceWithLabels;
                $total = floatval($total);
                $total = number_format($total, 2, '.', '');
                return $total;

        }

            // Принт скрин Facebook
            if ($basket['coupon_type'] == 'facebook') {
                // Застосовуємо знижку тільки до товарів БЕЗ позначок
                $discountedPrice = $totalPriceWithoutLabels - ($totalPriceWithoutLabels * (2 / 100));
                $total = $discountedPrice + $totalPriceWithLabels;
                $total = floatval($total);
                $total = number_format($total, 2, '.', '');
                return $total;
            }

            /// Купон 30_40 бесплатно
            if ($basket['coupon_type'] == '30_40') {

                foreach ($basket as $basket_item)
                {
                    if ( isset($basket_item["sizeId"]) && $basket_item["sizeId"]=="30x40" && isset($basket_item["name"]) &&  $basket_item["name"]=="Canvas" ) {

                        if (isset($sale) && $sale>$basket_item["price"] ){
                            $sale = $basket_item["price"];
                        }
                        if (!isset($sale)) {
                            $sale = $basket_item["price"];
                        }
                    }

                }
                if (isset($sale)) {  $total = $total-$sale;}

                $total = floatval($total);
                $total = number_format($total, 2, '.', '');
                return $total;
            }




            /// Купон 40_60 бесплатно
            if ($basket['coupon_type'] == '40_60') {

                $have_sale=0;
                foreach ($basket as $basket_item)
                {
                    if ( isset($basket_item["sizeId"]) && ($basket_item["sizeId"]=="80x120" || $basket_item["sizeId"]=="120x80" )) {

                    $have_sale=1;
                    }
                }
                foreach ($basket as $basket_item)
                {
                if ($have_sale && isset($basket_item["sizeId"]) && ($basket_item["sizeId"] == "40x60" || $basket_item["sizeId"] == "60x40") && isset($basket_item["name"]) && $basket_item["name"] == "Canvas") {
                    if (isset($sale) && $sale > $basket_item["price"]) {
                        $sale = $basket_item["price"];
                    }
                    if (!isset($sale)) {
                        $sale = $basket_item["price"];
                    }
                }
                }
                if (isset($sale)) {  $total = $total-$sale;}

                $total = floatval($total);
                $total = number_format($total, 2, '.', '');
                return $total;

            }


            /// Купон 3 одна бесплатно
            if ($basket['coupon_type'] == '1free') {
                if ($this->total_item_counts($basket)>=4 ) {

                    foreach ($basket as $basket_item) {

                        if (isset($basket_item["price"])) {
                            if (isset($sale) && $sale > $basket_item["price"]) {
                                $sale = $basket_item["price"];
                            }
                            if (!isset($sale)) {
                                $sale = $basket_item["price"];
                            }
                        }
                    }
                    if (isset($sale)) {  $total = $total-$sale;}

                    $total = floatval($total);
                    $total = number_format($total, 2, '.', '');
                    return $total;
                }
            }

            /// Универсальный купон
            if ($basket['coupon_type'] == 'universal') {

                return  $this->convert_percent($basket, $total, $totalPriceWithoutLabels, $totalPriceWithLabels);

            }

            if ($basket['coupon_type'] == 'abandoned_basket') {
                return  $this->convert_percent($basket, $total, $totalPriceWithoutLabels, $totalPriceWithLabels);
            }

            if ($basket['coupon_type'] == 'giftcard') {
                return  $this->convert_percent($basket, $total, $totalPriceWithoutLabels, $totalPriceWithLabels);
            }



        }



        return $total;
    }


    public function total_item_counts($basket)
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

                if(isset($basket[$basketIndex]['terms_price']))
                {
                    $basket[$basketIndex]['total_item_price'] = ($basket[$basketIndex]['count'] * $basket[$basketIndex]['price']) + (float)$basket[$basketIndex]['terms_price'];
                }
                else
                {
                    $basket[$basketIndex]['total_item_price'] = $basket[$basketIndex]['count'] * $basket[$basketIndex]['price'];
                }
            }

        }

        return $total_item_counts;
    }





    private function convert_percent($basket, $total, $totalPriceWithoutLabels = null, $totalPriceWithLabels = null)
    {
        // Якщо не передані окремі суми, використовуємо загальну (backwards compatibility)
        if ($totalPriceWithoutLabels === null) {
            $totalPriceWithoutLabels = $total;
        }
        if ($totalPriceWithLabels === null) {
            $totalPriceWithLabels = 0;
        }

        if ($basket['coupon_id']>0){
            $coupon_value = $basket['coupon_val'];

            // Застосовуємо знижку тільки до товарів БЕЗ позначок
            $discountedPrice = $totalPriceWithoutLabels;

            if (strpos($coupon_value, '%') !== false) {
                $coupon_value = str_replace('%', '', $coupon_value);
                $coupon_value = str_replace(',', '.', $coupon_value);
                $discountedPrice = $discountedPrice*(100-(float)$coupon_value)/100;
            } else {
                $discountedPrice = $discountedPrice-$coupon_value;
            }
            if ($discountedPrice<1){ $discountedPrice=0; }

            // Додаємо товари З позначками (без знижки)
            $total = $discountedPrice + $totalPriceWithLabels;
        }

        $total = floatval($total);
        $total = number_format($total, 2, '.', '');
        return $total;

    }


    private function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;

        $ini = strpos($string, $start);

        if ($ini == 0) {
            return '';
        }

        $ini += strlen($start);

        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    private function formCanvasTypeProperties($basket, $key)
    {
        $basket[$key]['price'] = [];

        $basket[$key]['effectId'] = null;

        $basket[$key]['show']['form'] = GalleryFormType::getName($basket[$key]['formId']);

        $size_name = $basket[$key]['sizeId']; //30x40,60x80 etc;

        $sizes_60x30 = \DB::table('canvas_header')->pluck('sizes_60x30')[0];

        $sizes_40x30 = \DB::table('canvas_header')->pluck('sizes_40x30')[0];

        $sizes_38x38 = \DB::table('canvas_header')->pluck('sizes_38x38')[0];

        $sizes_30x40 = \DB::table('canvas_header')->pluck('sizes_30x40')[0];

        $custom_sizes1 = explode(',', $sizes_40x30);

        $custom_sizes2 = explode(',', $sizes_38x38);

        $custom_sizes3 = explode(',', $sizes_30x40);

        $custom_sizes4 = explode(',', $sizes_60x30);

        $size_single_price = 0;

        // 2

        if (is_array($custom_sizes1) && !empty($custom_sizes1) && $custom_sizes1[0] != '') {
            foreach ($custom_sizes1 as $size) {
                $price = $this->get_string_between($size, '[', ']');

                $size_clear = substr($size, 0, strpos($size, '['));

                $size_clear_vals = explode('x', $size_clear);

                $size_full_name = $size_clear_vals[0] . 'x' . $size_clear_vals[1];

                if ($size_full_name == $size_name) {
                    $size_single_price = $price;

                    break;
                }
            }
        }

        if (is_array($custom_sizes2) && !empty($custom_sizes2) && $custom_sizes2[0] != '') {
            foreach ($custom_sizes2 as $size) {
                $price = $this->get_string_between($size, '[', ']');

                $size_clear = substr($size, 0, strpos($size, '['));

                $size_clear_vals = explode('x', $size_clear);

                $size_full_name = $size_clear_vals[0] . 'x' . $size_clear_vals[1];

                if ($size_full_name == $size_name) {
                    $size_single_price = $price;

                    break;
                }
            }
        }

        if (is_array($custom_sizes3) && !empty($custom_sizes3) && $custom_sizes3[0] != '') {
            foreach ($custom_sizes3 as $size) {
                $price = $this->get_string_between($size, '[', ']');

                $size_clear = substr($size, 0, strpos($size, '['));

                $size_clear_vals = explode('x', $size_clear);

                $size_full_name = $size_clear_vals[0] . 'x' . $size_clear_vals[1];

                if ($size_full_name == $size_name) {
                    $size_single_price = $price;

                    break;
                }
            }
        }

        if (is_array($custom_sizes4) && !empty($custom_sizes4) && $custom_sizes4[0] != '') {
            foreach ($custom_sizes4 as $size) {
                $price = $this->get_string_between($size, '[', ']');

                $size_clear = substr($size, 0, strpos($size, '['));

                $size_clear_vals = explode('x', $size_clear);

                $size_full_name = $size_clear_vals[0] . 'x' . $size_clear_vals[1];

                if ($size_full_name == $size_name) {
                    $size_single_price = $price;

                    break;
                }
            }
        }

        // 2

        $basket = $this->formSizeProperty($basket, $key, $size_name, $size_single_price);

        $basket[$key]['show']['effect'] = null;

        // цена за срок изготовления

        $term_price = intval($basket[$key]['terms_price']);

        $st_price = \DB::table('string_tranlations')->pluck('standart_price')->first();

        $ex_price = \DB::table('string_tranlations')->pluck('express_price')->first();

        if ($term_price == intval($st_price) || $term_price == intval($ex_price)) {
            $basket[$key]['price']['izgTimePrice'] = intval($term_price);
        }

        $basket = $this->formExecutionProperty($basket, $key, $size_name);

        $basket = $this->formHolstProperty($basket, $key);

        $basket = $this->formDecorationProperty($basket, $key);

        $basket = $this->formBoxProperty($basket, $key);

        $basket = $this->formRamProperty($basket, $key);

        return $basket;
    }

    private function formModularTypeProperties($basket, $key)
    {
        $basket[$key]['price'] = [];

        $basket = $this->formSizeProperty($basket, $key, $basket[$key]['size'], $this->calcExecutionPrice($basket[$key]['size']));

        $basket = $this->formExecutionProperty($basket, $key, $basket[$key]['size']);

        $basket = $this->formHolstProperty($basket, $key);

        $basket = $this->formDecorationProperty($basket, $key);

        $basket = $this->formBoxProperty($basket, $key);

        return $basket;
    }

    private function formCollageProperties($basket, $key)
    {
        $basket[$key]['price'] = [];

        $size = GallerySize::find($basket[$key]['sizeId']);

        $basket = $this->formSizeProperty($basket, $key, $size->size, $size->price);

        $basket = $this->formHolstProperty($basket, $key);

        $basket = $this->formExecutionProperty($basket, $key);

        $basket = $this->formBoxProperty($basket, $key);

        return $basket;
    }

    private function formGiftCardTypeProperties($basket, $key)
    {
        $basket[$key]['price'] = [];

        return $basket;
    }

    private function formSizeProperty($basket, $key, $size, $price)
    {
        $basket[$key]['show']['size'] = $size;

        $basket[$key]['price']['sizePrice'] = $price;

        return $basket;
    }

    private function formExecutionProperty($basket, $key, $size = null)
    {
        if (!isset($basket[$key]['executionId'])) {
            $basket[$key]['executionId'] = GalleryExecutionType::PRINT_TYPE;
        }

        $basket[$key]['show']['execution'] = GalleryExecutionType::getName($basket[$key]['executionId']);

        if ($basket[$key]['executionId'] == GalleryExecutionType::OIL_TYPE) {
            $basket[$key]['price']['executionPrice'] = $this->calcExecutionPrice($size);
        }

        return $basket;
    }

    private function formHolstProperty($basket, $key)
    {
        if (!isset($basket[$key]['canvasId'])) {
            return $basket;
        }

        $holst = GalleryHolst::where('id', $basket[$key]['canvasId'])->get()->translate(\App::getLocale(), 'ru')[0];

        $basket[$key]['show']['canvas'] = $holst->name;

        $basket[$key]['price']['canvasPrice'] = $holst->price;

        return $basket;
    }

    private function formDecorationProperty($basket, $key)
    {
        if (!isset($basket[$key]['decorationId'])) {
            return $basket;
        }

        $decoration = GalleryDecoration::where('id', $basket[$key]['decorationId'])->get()->translate(\App::getLocale(), 'ru')[0];

        $basket[$key]['show']['decoration'] = $decoration->name;

        $basket[$key]['price']['decorationPrice'] = $decoration->price;

        return $basket;
    }

    private function formBoxProperty($basket, $key)
    {
        if ($basket[$key]['boxIds']) {
            $basket[$key]['price']['boxPrice'] = 0;

            $boxes = GalleryBox::whereIn('id', $basket[$key]['boxIds'])->get();

            foreach ($boxes as $index => $box) {
                $cur_box = GalleryBox::where('id', $box['id'])->get()->translate(\App::getLocale(), 'ru')[0];

                $basket[$key]['show']['box'][] = $cur_box->name;

                $basket[$key]['price']['boxPrice'] += $cur_box->price;
            }
        }

        return $basket;
    }

    private function formRamProperty($basket, $key)
    {
        if (empty($basket[$key]['ram_id'])) {
            return $basket;
        }

        $ram = CanvasRam::where('id', $basket[$key]['ram_id'])->get()->translate(\App::getLocale(), 'ru')->first();

        if (!$ram || (float) $ram->price <= 0) {
            return $basket;
        }

        $basket[$key]['show']['ram'] = $ram->name;
        $basket[$key]['price']['ramPrice'] = $ram->price;

        return $basket;
    }

    private function calcExecutionPrice($size)
    {
        $executionPrice = 0;

        $explodedSize = explode('x', $size);

        $height = $explodedSize[0];

        $length = $explodedSize[1];

        $area = $height * $length / 10000;

        if ($area <= 0.4) {
            $executionPrice = $area * 80;
        } elseif ($area > 0.4 && $area <= 1) {
            $executionPrice = $area * 60;
        } elseif ($area > 1) {
            $executionPrice = $area * 50;
        }

        return $executionPrice;
    }

    private function calcPrice($basketProduct, $contry_mult)
    {
        $totalPrice = 0;

        foreach ($basketProduct['price'] as $price) {
            $price = $price * $contry_mult;

            $totalPrice += $price;
        }

        return $totalPrice;
    }

    public function saveBasketToAbandonedCartModel($cart, bool $is_push = false)
    {
        $email = session()->get('email');
        if (auth()->check()) {
            $email = auth()->user()->email;
            // Keep session email in sync with the authenticated user to avoid stale values
            session()->put('email', $email);
        }

        if($email)
        {
            $abandonedCart = AbandonedCart::firstOrNew(['email' => $email]);
            $existingCart = $this->normalizeBasket($abandonedCart->cart_data ?? []);
            $cart = $is_push ? $cart : $this->normalizeBasket($cart);

            $newCart = $is_push
                ? array_merge($existingCart ?: [], [$cart])
                : $cart;
            $newLocale = App::getLocale();

            $cartChanged = !$abandonedCart->exists || $abandonedCart->cart_data != $newCart;
            $localeChanged = !$abandonedCart->exists || $abandonedCart->locale !== $newLocale;

            if ($cartChanged || $localeChanged) {
                $abandonedCart->cart_data = $newCart;
                // $abandonedCart->recovery_token = null;
                // $abandonedCart->token_expires_at = null;
                // $abandonedCart->is_send_email_twelve_hours = false;
                $abandonedCart->locale = $newLocale;
                $abandonedCart->save();
            }
        }
    }

    public function restoreBasketToAbandonedCartModel()
    {
        $email = request()->session()->get('email');
        if (auth()->check()) {
            $email = auth()->user()->email;
        }
        if ($email) {
            $cart = AbandonedCart::where('email', $email)
                ->first();
            if ($cart) {
                $normalized = $this->normalizeBasket($cart->cart_data);
                session()->put('basket', $normalized);

                if ($cart->cart_data !== $normalized) {
                    $cart->cart_data = $normalized;
                    $cart->save();
                }
            }
        }
    }

    public function getRecommendedItems($basket, $limit = 3, $contry_mult = 1)
    {
        $basketItemIds = [];
        $basketCategories = [];
        $basketSizes = [];
        $currentSize = null;

        $basket = $this->normalizeBasket($basket);

        foreach ($basket as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (isset($item['pid']) && $item['pid'] != 5) {
                $basketItemIds[] = $item['pid'];

                if (isset($item['sizeId'])) {
                    $basketSizes[] = $item['sizeId'];
                    $currentSize = $item['sizeId'];
                }
            }
        }

        if (empty($basketItemIds)) {
            return [];
        }

        $itemsInBasket = GalleryItem::whereIn('id', $basketItemIds)
            ->where('active', 1)
            ->get();

        foreach ($itemsInBasket as $item) {
            if ($item->id_category) {
                $basketCategories[] = $item->id_category;
            }
        }

        $specificItemId = 1076;
        $excludeIds = array_merge($basketItemIds, [$specificItemId]);

        $query = GalleryItem::where('active', 1)
            ->whereNotIn('id', $excludeIds)
            ->with('types');

        if (!empty($basketCategories)) {
            $query->whereIn('id_category', $basketCategories);
        }

        $recommendedItems = $query->inRandomOrder()
            ->limit($limit)
            ->get();

        if ($recommendedItems->count() < $limit) {
            $additionalItems = GalleryItem::where('active', 1)
                ->whereNotIn('id', $excludeIds)
                ->whereNotIn('id', $recommendedItems->pluck('id')->toArray())
                ->with('types')
                ->inRandomOrder()
                ->limit($limit - $recommendedItems->count())
                ->get();

            $recommendedItems = $recommendedItems->merge($additionalItems);
        }

        $result = [];

        $canvasRecommendation = $this->generateCanvasRecommendation($currentSize);
        if ($canvasRecommendation) {
            $result[] = $canvasRecommendation;
        }

        if (!in_array($specificItemId, $basketItemIds)) {
            $specificItem = GalleryItem::where('id', $specificItemId)
                ->where('active', 1)
                ->with('types')
                ->first();

            if ($specificItem) {
                $priceFrom = ($specificItem->price_from ?? 0) * $contry_mult;
                $discountedPrice = round($priceFrom * 0.7, 2);
                $discountAmount = round($priceFrom - $discountedPrice, 2);

                $result[] = [
                    'id' => $specificItem->id,
                    'name' => $specificItem->getTranslatedAttribute('name'),
                    'slug' => $specificItem->slug,
                    'image' => $specificItem->images,
                    'original_price' => number_format($priceFrom, 2, '.', ''),
                    'discounted_price' => number_format($discountedPrice, 2, '.', ''),
                    'discount_amount' => number_format($discountAmount, 2, '.', ''),
                    'discount_percent' => 30,
                    'short_desc' => $specificItem->short_desc ?? '',
                    'id_category' => $specificItem->id_category,
                    'id_type' => $specificItem->id_type,
                    'type_url' => $specificItem->types->url ?? 'canvas',
                ];
            }
        }

        foreach ($recommendedItems as $item) {
            $priceFrom = ($item->price_from ?? 0) * $contry_mult;

            $discountedPrice = round($priceFrom * 0.7, 2);
            $discountAmount = round($priceFrom - $discountedPrice, 2);

            $result[] = [
                'id' => $item->id,
                'name' => $item->getTranslatedAttribute('name'),
                'slug' => $item->slug,
                'image' => $item->images,
                'original_price' => number_format($priceFrom, 2, '.', ''),
                'discounted_price' => number_format($discountedPrice, 2, '.', ''),
                'discount_amount' => number_format($discountAmount, 2, '.', ''),
                'discount_percent' => 30,
                'short_desc' => $item->short_desc ?? '',
                'id_category' => $item->id_category,
                'id_type' => $item->id_type,
                'type_url' => $item->types->url ?? 'canvas',
            ];
        }

        return $result;
    }


    public function getAlternativeSizeWithDiscount($basketItem)
    {
        $currentSize = $basketItem['sizeId'] ?? $basketItem['size_name'] ?? $basketItem['size'] ?? null;

        $basketType = $basketItem['basketType'] ?? null;

        if (isset($basketItem['is_def_product']) || (isset($basketItem['pid']) && !isset($basketItem['is_canvas_collage']))) {
            $itemId = $basketItem['pid'] ?? null;

            if (!$itemId) {
                return null;
            }
            $item = GalleryItem::find($itemId);
            if (!$item) {
                return null;
            }

            return $this->findAlternativeSizeForGalleryItem($item, $currentSize);
        }

        if (($basketType == '1' || $basketType == 1) && (isset($basketItem['is_canvas_collage']) || isset($basketItem['is_canvas_inter']))) {
            return $this->findAlternativeSizeForCanvas($currentSize, $basketItem);
        }

        if ($basketType == '2' || $basketType == 2) {
            return null;
        }

        return null;
    }

    private function findAlternativeSizeForGalleryItem($item, $currentSize)
    {
        $sizePrices = [];

        if ($item->custom_size_prices) {
            $sizesArray = explode(',', $item->custom_size_prices);
            foreach ($sizesArray as $sizeWithPrice) {
                if (empty($sizeWithPrice)) continue;

                preg_match('/^(.+?)\[(\d+(?:\.\d+)?(?:-\d+(?:\.\d+)?)?)\]([a-zA-Z]*)$/', trim($sizeWithPrice), $matches);

                if ($matches) {
                    $sizeStr = $matches[1] . $matches[3];
                    $priceArr = explode('-', $matches[2]);
                    $originalPrice = (float)$priceArr[0];
                    $discountedPrice = count($priceArr) > 1 ? (float)$priceArr[1] : $originalPrice;

                    $sizePrices[] = [
                        'size' => $sizeStr,
                        'original_price' => $originalPrice,
                        'price' => $discountedPrice,
                        'has_special_label' => hasSpecialLabel($sizeStr),
                        'label_type' => getLabelType($sizeStr),
                    ];
                }
            }
        }

        if (empty($sizePrices) && $item->sizes_cals) {
            $sizesArray = explode(',', $item->sizes_cals);
            foreach ($sizesArray as $sizeWithPrice) {
                if (empty($sizeWithPrice)) continue;

                preg_match('/^(.+?)\[(\d+(?:\.\d+)?)\]$/', trim($sizeWithPrice), $matches);

                if ($matches) {
                    $sizeStr = $matches[1];
                    $price = (float)$matches[2];

                    $sizePrices[] = [
                        'size' => $sizeStr,
                        'original_price' => $price,
                        'price' => $price,
                        'has_special_label' => hasSpecialLabel($sizeStr),
                        'label_type' => getLabelType($sizeStr),
                    ];
                }
            }
        }

        if (empty($sizePrices)) {
            $gallerySizes = GallerySize::all();
            foreach ($gallerySizes as $gallerySize) {
                $sizePrices[] = [
                    'size' => $gallerySize->size,
                    'price' => $gallerySize->price,
                    'has_special_label' => hasSpecialLabel($gallerySize->size),
                    'label_type' => getLabelType($gallerySize->size),
                ];
            }
        }

        $alternativeSizes = [];
        $currentSizeClean = $currentSize ? preg_replace('/[hst]$/i', '', $currentSize) : null;

        $currentArea = 0;
        if ($currentSizeClean) {
            $currentDimensions = explode('x', strtolower($currentSizeClean));
            if (count($currentDimensions) === 2) {
                $currentArea = (float)$currentDimensions[0] * (float)$currentDimensions[1];
            }
        }

        foreach ($sizePrices as $sizeData) {
            if ($currentSize && $sizeData['size'] === $currentSize) {
                continue;
            }
            $alternativeSizeClean = preg_replace('/[htsr]$/i', '', $sizeData['size']);
            if ($alternativeSizeClean === $currentSizeClean) {
                continue;
            }

            $alternativeDimensions = explode('x', strtolower($alternativeSizeClean));
            if (count($alternativeDimensions) !== 2) {
                continue;
            }

            $alternativeArea = (float)$alternativeDimensions[0] * (float)$alternativeDimensions[1];

            if ($alternativeArea > $currentArea) {
                $hasDiscount = isset($sizeData['original_price']) && $sizeData['original_price'] > $sizeData['price'];

                $alternativeSizes[] = [
                    'size' => $sizeData['size'],
                    'size_clean' => $alternativeSizeClean,
                    'price' => $sizeData['price'],
                    'original_price' => $sizeData['original_price'] ?? $sizeData['price'],
                    'label_type' => $sizeData['label_type'] ?? null,
                    'original_size' => $currentSize,
                    'area' => $alternativeArea,
                    'has_discount' => $hasDiscount,
                ];
            }
        }

        if (!empty($alternativeSizes)) {
            usort($alternativeSizes, function($a, $b) {
                return $a['area'] <=> $b['area'];
            });
        }

        if (!empty($alternativeSizes)) {
            $alternativeOriginalPrice = $alternativeSizes[0]['original_price'] ?? $alternativeSizes[0]['price'];

            return [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'item_image' => $item->images,
                'alternative_size' => $alternativeSizes[0]['size'],
                'alternative_price' => $alternativeSizes[0]['price'],
                'alternative_original_price' => $alternativeOriginalPrice,
                'label_type' => $alternativeSizes[0]['label_type'],
                'current_size' => $currentSize,
                'current_price' => $this->getCurrentSizePrice($sizePrices, $currentSize),
                'savings' => $alternativeOriginalPrice - $alternativeSizes[0]['price'],
            ];
        }

        return null;
    }

    private function findAlternativeSizeForCanvas($currentSize, $basketItem = [])
    {
        $sizes_30x40 = \DB::table('canvas_header')->pluck('sizes_30x40')->first();

        $allCanvasSizes = [];

        foreach ([$sizes_30x40] as $sizesString) {

            if ($sizesString) {
                $sizesArray = explode(',', $sizesString);
                foreach ($sizesArray as $sizeWithPrice) {

                    if (empty($sizeWithPrice)) continue;

                    preg_match('/^(.+?)\[(\d+(?:\.\d+)?(?:-\d+(?:\.\d+)?)?)\]([a-zA-Z]*)$/', trim($sizeWithPrice), $matches);

                    if ($matches) {
                        $sizeStr = $matches[1] . $matches[3];
                        $price = explode('-',$matches[2]);

                        $allCanvasSizes[] = [
                            'size' => $sizeStr,
                            'original_price' => (float)$price[0],
                            'price' => count($price) > 1 ? (float)$price[1] : (float)$price[0],
                            'has_special_label' => hasSpecialLabel($sizeStr),
                            'label_type' => getLabelType($sizeStr),
                        ];
                    }
                }
            }
        }

        $alternativeSizes = [];
        $currentSizeClean = $currentSize ? preg_replace('/[hst]$/i', '', $currentSize) : null;

        $currentArea = 0;
        if ($currentSizeClean) {
            $currentDimensions = explode('x', strtolower($currentSizeClean));
            if (count($currentDimensions) === 2) {
                $currentArea = (float)$currentDimensions[0] * (float)$currentDimensions[1];
            }
        }

        foreach ($allCanvasSizes as $sizeData) {
            if ($currentSize && $sizeData['size'] === $currentSize) {
                continue;
            }

            $alternativeSizeClean = preg_replace('/[htsr]$/i', '', $sizeData['size']);
            if ($alternativeSizeClean === $currentSizeClean) {
                continue;
            }

            $alternativeDimensions = explode('x', strtolower($alternativeSizeClean));
            if (count($alternativeDimensions) !== 2) {
                continue;
            }

            $alternativeArea = (float)$alternativeDimensions[0] * (float)$alternativeDimensions[1];
            if ($alternativeArea > $currentArea) {
                $hasDiscount = isset($sizeData['original_price']) && $sizeData['original_price'] > $sizeData['price'];

                $alternativeSizes[] = [
                    'size' => $sizeData['size'],
                    'size_clean' => $alternativeSizeClean,
                    'original_price' => $sizeData['original_price'] ?? $sizeData['price'],
                    'price' => $sizeData['price'],
                    'label_type' => $sizeData['label_type'] ?? null,
                    'original_size' => $currentSize,
                    'area' => $alternativeArea,
                    'has_discount' => $hasDiscount,
                ];
            }
        }

        if (!empty($alternativeSizes)) {
            usort($alternativeSizes, function($a, $b) {
                return $a['area'] <=> $b['area'];
            });
        }

        if (!empty($alternativeSizes)) {
            $alternativeOriginalPrice = $this->getOriginalSizePrice($allCanvasSizes, $alternativeSizes[0]['size_clean']);

            $itemName = $basketItem['name'] ?? 'Canvas';
            $itemImage = $basketItem['activeImage'] ?? $basketItem['savedImage'] ?? null;

            return [
                'basket_key' => 1,
                'item_id' => null,
                'item_name' => $itemName,
                'item_image' => $itemImage,
                'alternative_size' => $alternativeSizes[0]['size'],
                'alternative_price' => $alternativeSizes[0]['price'],
                'alternative_original_price' => $alternativeSizes[0]['original_price'],
                'label_type' => $alternativeSizes[0]['label_type'],
                'current_size' => $currentSize,
                'current_price' => $this->getCurrentCanvasSizePrice($allCanvasSizes, $currentSize),
                'savings' => $alternativeOriginalPrice - $alternativeSizes[0]['price'],
            ];
        }

        return null;
    }

    private function getCurrentCanvasSizePrice($sizePrices, $currentSize)
    {
        if (!$currentSize) {
            return 0;
        }

        foreach ($sizePrices as $sizeData) {
            if ($sizeData['size'] === $currentSize) {
                return (float) $sizeData['price'];
            }
        }

        return 0;
    }

    private function getCurrentSizePrice($sizePrices, $currentSize)
    {
        if (!$currentSize) {
            return 0;
        }

        foreach ($sizePrices as $sizeData) {
            if ($sizeData['size'] === $currentSize) {
                return (float) $sizeData['price'];
            }
        }

        return 0;
    }

    private function getOriginalSizePrice($sizePrices, $sizeClean)
    {
        if (!$sizeClean) {
            return 0;
        }

        foreach ($sizePrices as $sizeData) {
            $currentSizeClean = preg_replace('/[hst]$/i', '', $sizeData['size']);

            if ($currentSizeClean === $sizeClean && !$sizeData['has_special_label']) {
                return (float) $sizeData['price'];
            }
        }

        return 0;
    }

    private function generateCanvasRecommendation($currentSize = null): ?array
    {
        $sizes_30x40 = \DB::table('canvas_header')->pluck('sizes_30x40')->first();

        if (!$sizes_30x40) {
            return null;
        }

        $sizesArray = explode(',', $sizes_30x40);
        $validSizes = [];

        foreach ($sizesArray as $sizeWithPrice) {
            if (empty($sizeWithPrice)) continue;

            preg_match('/^(.+?)\[(\d+(?:\.\d+)?(?:-\d+(?:\.\d+)?)?)\]([a-zA-Z]*)$/', trim($sizeWithPrice), $matches);

            if ($matches) {
                $sizeStr = $matches[1] . $matches[3];
                $priceArr = explode('-', $matches[2]);
                $originalPrice = (float)$priceArr[0];
                $discountedPrice = count($priceArr) > 1 ? (float)$priceArr[1] : $originalPrice;

                $dimensions = explode('x', strtolower($sizeStr));
                $area = 0;
                if (count($dimensions) === 2) {
                    $area = (float)$dimensions[0] * (float)$dimensions[1];
                }

                $validSizes[] = [
                    'size' => $sizeStr,
                    'original_price' => $originalPrice,
                    'discounted_price' => $discountedPrice,
                    'area' => $area,
                ];
            }
        }

        if (empty($validSizes)) {
            return null;
        }

        $currentArea = 0;
        if ($currentSize) {
            $currentDimensions = explode('x', strtolower($currentSize));
            if (count($currentDimensions) === 2) {
                $currentArea = (float)$currentDimensions[0] * (float)$currentDimensions[1];
            }
        }

        $largerSizes = [];
        foreach ($validSizes as $size) {
            if ($size['area'] > $currentArea) {
                $largerSizes[] = $size;
            }
        }

        if (empty($largerSizes)) {
            $largerSizes = $validSizes;
        }

        usort($largerSizes, function($a, $b) {
            return $a['area'] <=> $b['area'];
        });

        $selectedSize = $largerSizes[0];

        $galleryType = GalleryType::where('url', 'new/canvas')->first();
        $id_type = $galleryType ? $galleryType->id : null;

        return [
            'id' => null,
            'name' => 'Canvas',
            'slug' => 'canvas',
            'activeImage' => null,
            'size' => $selectedSize['size'],
            'original_price' => number_format($selectedSize['original_price'], 2, '.', ''),
            'discounted_price' => number_format($selectedSize['discounted_price'], 2, '.', ''),
            'discount_amount' => number_format($selectedSize['original_price'] - $selectedSize['discounted_price'], 2, '.', ''),
            'discount_percent' => 30,
            'short_desc' => '',
            'id_category' => null,
            'id_type' => $id_type,
            'is_canvas_recommendation' => true,
        ];
    }
}
