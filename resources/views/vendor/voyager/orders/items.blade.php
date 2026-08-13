@if($role != "printing")
    <p>Стоимость:
        @php
            $toFloat = static function ($value): float {
                $value = preg_replace('/[^0-9,\\.\\-]/', '', (string) $value);
                $value = str_replace(',', '.', (string) $value);
                return (float) $value;
            };

            $fmt = static function (float $value): string {
                $s = number_format($value, 2, '.', '');
                $s = rtrim(rtrim($s, '0'), '.');
                return $s === '' ? '0' : $s;
            };

            $originalSubtotal = $toFloat($order['price'] ?? 0);
            $salePriceRaw = $order['sale_price'] ?? null;
            $saleSubtotal = ($salePriceRaw !== null && $salePriceRaw !== '') ? $toFloat($salePriceRaw) : null;

            $baseSubtotal = $originalSubtotal;
            if ($saleSubtotal !== null && $saleSubtotal > 0 && $saleSubtotal < $originalSubtotal) {
                $baseSubtotal = $saleSubtotal;
            }

            $discountedSubtotal = $baseSubtotal;

            $saleEur = $toFloat($order['sale_eur'] ?? 0);
            if ($saleEur > 0) {
                $discountedSubtotal -= $saleEur;
            }

            $salePercent = $toFloat($order['sale_percent'] ?? 0);
            if ($salePercent > 0) {
                $discountedSubtotal -= $discountedSubtotal * ($salePercent / 100);
            }

            if ($discountedSubtotal < 0) {
                $discountedSubtotal = 0;
            }
            $discountedSubtotal = round($discountedSubtotal, 2);

            $deliveryPrice = isset($order['delivery']['deliv_price']) ? $toFloat($order['delivery']['deliv_price']) : 0.0;
            $termsPrice = isset($order['items']['total_terms_price']) ? $toFloat($order['items']['total_terms_price']) : 0.0;

            $total_all_summ = round($discountedSubtotal + $deliveryPrice + $termsPrice, 2);
        @endphp

        @if (abs($originalSubtotal - $discountedSubtotal) > 0.009)
            <span style="text-decoration: line-through;">{{ $order['price'] }}</span>
            <b>{{ $fmt($discountedSubtotal) }} €</b><br>
        @else
            <b>{{ $order['price'] }}</b>
        @endif
        <br>
        @if ($order['sale_eur'] != '' && $order['sale_eur'] != null && $order['sale_eur'] != 0)
            Скидка в евро: <b>{{ $order['sale_eur'] }} €</b>
        @endif
        <br>
        @if ($order['sale_percent'] != '' && $order['sale_percent'] != null && $order['sale_percent'] != 0)
            Скидка в процентах: <b>{{ $order['sale_percent'] }} %</b>
        @endif
        <br>
        @if (isset($order["items"]['total_terms_price']) && $order["items"]['total_terms_price'] > 0)
            Экспресс:
            <b>{{ $order["items"]['total_terms_price'] }}
                €</b>
        <br>
        @endisset
        @if (isset($order['delivery']['deliv_price']))
            Доставка:
            <b>{{ $order['delivery']['deliv_price'] }}
                €</b>
        @endisset


        @if (isset($order['delivery']['deliv_price']))
            <br>
            Итого: <b>{{ $fmt($total_all_summ) }} €</b>
        @endisset
    </p>

    <hr>
    <?php /// TODO: Вывод в админке статуса какой купон использован ?>


    @if (isset($order['delivery']['is_coupon_30_40']))
        <p style="color:orange;font-weight: bold;">Использован купон:30x40</p>
    @endif

    @if (isset($order['delivery']['bonus']))
        <p style="color:orange;font-weight: bold;">Использовано <?=$order['delivery']['bonus']?> бонусов</p>
    @endif

    @if (isset($order['delivery']['is_coupon_40_60']))
        <p style="color:orange;font-weight: bold;">Использован купон:40x60</p>
    @endif

    @if (isset($order['delivery']['is_coupon_dates']))
        <p style="color:orange;font-weight: bold;">Использован купон:2 даты</p>
    @endif

    @if (isset($order['delivery']['has_invited_sale']))
        <p style="color:orange;font-weight: bold;">Использована скидка по приглашению</p>
    @endif

    @if (isset($order['delivery']['is_1free']))
        <p style="color:orange;font-weight: bold;">Использована акция При заказе 3 картин - 1 в подарок </p>
    @endif

    @if (isset($order['delivery']['is_universal']))
        <p style="color:orange;font-weight: bold;">Использован универсальный купон </p>
    @endif

    @if (isset($order['delivery']['is_abandoned_basket']))
        <p style="color:orange;font-weight: bold;">Использован купон: Брошенная корзина</p>
    @endif

    @if (isset($order['delivery']['is_giftcard']))
        <p style="color:orange;font-weight: bold;">Использован купон: Подарочная карта</p>
    @endif

    @if (isset($order['delivery']['free_delivery']))
        <p style="color:orange;font-weight: bold;">Бесплатная доставка</p>
    @endif


    @if (isset($order['delivery']['coupon_type']))

    @if ($order['delivery']['coupon_type']=="30_40")
        <p style="color:orange;font-weight: bold;">Использован купон:30x40</p>
    @endif

    @if ($order['delivery']['coupon_type']=="bonus")
        <p style="color:orange;font-weight: bold;">Использованы бонусы</p>
    @endif

    @if ($order['delivery']['coupon_type']=="facebook")
        <p style="color:orange;font-weight: bold;">Использованы купон скриншот facebook</p>
    @endif

    @if ($order['delivery']['coupon_type']=="40_60")
        <p style="color:orange;font-weight: bold;">Использован купон:40x60</p>
    @endif

    @if ($order['delivery']['coupon_type']=="date")
        <p style="color:orange;font-weight: bold;">Использован купон:2 даты</p>
    @endif

    @if ($order['delivery']['coupon_type']=="friend")
        <p style="color:orange;font-weight: bold;">Использована скидка по приглашению</p>
    @endif

    @if ($order['delivery']['coupon_type']=="1free")
        <p style="color:orange;font-weight: bold;">Использована акция При заказе 3 картин - 1 в подарок </p>
    @endif

    @if ($order['delivery']['coupon_type']=="universal")
        <p style="color:orange;font-weight: bold;">Использован универсальный купон </p>
    @endif

    @if ($order['delivery']['coupon_type']=="abandoned_basket")
        <p style="color:orange;font-weight: bold;">Использован купон: Брошенная корзина</p>
    @endif

    @if ($order['delivery']['coupon_type']=="giftcard")
        <p style="color:orange;font-weight: bold;">Использован купон: Подарочная карта</p>
    @endif


    @if ($order['delivery']['coupon_type']=="free_delivery")
        <p style="color:orange;font-weight: bold;">Бесплатная доставка</p>
    @endif




    @endif



@endif

@php $user_link = \URL::to('/') . '/admin/user/' . $order['user_id']; @endphp

<ul class="list__adm__items">

    @if($role != "printing")
        <li>
            <a href='{{ $user_link }}/leave_rev/{{ $order['id'] }}'>Запрос отзыва</a>
        </li>
        <br>
    @endif

    @if(isset($order['items'][1]['content']) && $order['items'][1]['content'])
        {!! $order['items'][1]['content'] !!}
    @endif

    @if ($order['items'])
        @foreach ($order['items'] as $product)
            @if (!isset($product['sumPrice']))
                @continue
            @endif
            @isset($product['name'])
                <li> Товар: {{ $product['name'] }} </li>
            @endif
                <?php
                if(isset($product['improve_photo'])){
                if ($product['improve_photo']=='undefined') {
                $product['improve_photo']='Базовое 0€';
                } ?>
                <p>Улучшение фото: <?php echo e($product['improve_photo']); ?></p>
                <?php } ?>
            @php
                $was_act_img = 0;
                $activeSrc = isset($product['activeImage']) ? order_image_url($product['activeImage']) : null;
                $activePath = isset($product['activeImage']) ? parse_url($product['activeImage'], PHP_URL_PATH) : null;
                $activeName = $activePath ? basename($activePath) : null;

                $savedSrc = isset($product['savedImage']) ? order_image_url($product['savedImage']) : null;
                $savedPath = isset($product['savedImage']) ? parse_url($product['savedImage'], PHP_URL_PATH) : null;
                $savedName = $savedPath ? basename($savedPath) : null;

                $isCanvasLike = isset($product['is_construct'])
                    || isset($product['is_canvas_inter'])
                    || isset($product['is_modular_inter'])
                    || isset($product['is_oil_portrait'])
                    || (isset($product['is_gall_with_img']) && $product['is_gall_with_img'] == 1)
                    || (isset($product['is_canvas_collage']) && $product['is_canvas_collage'])
                    || (isset($product['basketType']) && (string) $product['basketType'] === '1');

                $showSecondary = $savedName && (!$activeName || $savedName !== $activeName) && !$isCanvasLike;
            @endphp
            @isset($product['is_oil_portrait'])
                @php
                    $oil_img = json_decode(\App\Models\OilHeader::getOilPromoImage());
                @endphp
                <img class="def_img_some" style="max-width:150px;" src="{{ Voyager::image($oil_img[0]) }}" alt="">
            @endisset
            @if (isset($product['offsetImage']))
                <br>
                @php
                    $rep = str_replace('uploads', 'public/storage', $product['offsetImage']);
                @endphp
                <a target="_blank" href="{{ asset($rep) }}">Картинка с отступами</a>
                <br>
                <br>
            @endif
            @if (isset($product['activeImage']))
                @if (isset($product['is_construct']) || isset($product['is_canvas_inter']) || isset($product['is_modular_inter']) || isset($product['is_oil_portrait']) || (isset($product['is_gall_with_img']) && $product['is_gall_with_img'] == 1))
                    @php
                        $svg1 = str_replace('.jpeg', '.svg', $product['activeImage']);
                        $file = str_replace('.png', '.svg', $svg1);
                        $file = str_replace('.jpg', '.svg', $file);
                        $file = str_replace('.heic', '.svg', $file);
                        $file = str_replace('.heif', '.svg', $file);
                        $file = str_replace('/storage/', '/uploads/', $file);
                        $file = strstr($file, 'uploads/');
                        $cur_file = $file ?: null;
                        $file = $cur_file ? ($_SERVER['DOCUMENT_ROOT'] . '/public/' . ltrim($cur_file, '/')) : null;
                        $file = $file ? str_replace('/public/public', '/public', $file) : null;
                    @endphp
                    <br>
                    @if (!$file || !file_exists($file))
                        @if ($was_act_img == 0 && $activeSrc)
                            <a href="{{ $activeSrc }}">
                                <img class="def_img_some def_img_no_file" style="max-width:150px;"
                                     src="{{ $activeSrc }}" alt="">
                            </a>
                            @php
                                $was_act_img = 1;
                            @endphp
                        @endif
                    @else
                        @php
                            $was_act_img = 1;
                        @endphp
                        <br>
                        <a style="position:relative;display:block;" href="javascript:void(0);">
                            <img class="def_img_some" style="max-width:150px;opacity:0;"
                                 src="{{ $activeSrc ?? order_image_url($product['activeImage']) }}" alt="">
                            @php
                                try {
                                    $svg_file = file_get_contents($file);
                                } catch (\Throwable $th) {
                                    $svg_file = '';
                                }
                                if (!isset($product['file_hash'])) {
                                    $file = str_replace('id="image"', 'id="image' . $loop->index . '"', $svg_file);
                                } else {
                                    $file = $svg_file;
                                }
                                $file = str_replace('style="','style="max-width:180px;max-height:180px;',$file);
                                echo "<div style='width:100%; height:100%;position:absolute;left:0;top:0;right:0;bottom:0;' >" . $file . '</div>';
                            @endphp
                        </a>
                        <br>
                    @endif
                @else
                    @if ($activeSrc)
                        <a href="{{ $activeSrc }}">
                            <img class="def_img" style="max-width:150px;" src="{{ $activeSrc }}"
                                 alt="">
                        </a>
                        @php
                            $was_act_img = 1;
                        @endphp
                    @endif
                @endif
                @if ($showSecondary && $savedSrc)
                    <a href="{{ $savedSrc }}" style="display:inline-block; margin-left:6px;">
                        <img class="def_img" style="max-width:150px;" src="{{ $savedSrc }}" alt="">
                    </a>
                @endif
                @isset($product['fon_images'])
                    Фон:
                    @foreach ($product['fon_images'] as $fn)
                        <a target="_blank" href="{{ $fn }}">Фон-{{ $loop->index }}</a>
                    @endforeach
                @endisset

            @endif
            @isset($product['is_gift_card'])
                <img style="max-width:72px;" class="def_img card__img" src="{{ asset('img/benefits-img1.png') }}"
                     alt="">
            @endif

            @include('partials.all_basket_order_items',['is_admin_data_orders' => 'true', 'role' => $role])
            @if(isset($role) && $role != "printing")

                @isset($product['sumFormatedPrice'])
                    <p>Цена: {{ $product['sumFormatedPrice'] }}</p>
                @endisset
                @if (!$loop->last)
                    <hr>@endif
            @endif
            @if(isset($product['has_gift']) && $product['has_gift']==1)
                <strong>Подарок</strong>
            @endif
@endforeach

@if(isset($order['delivery']['comment']) && $order['delivery']['comment'] !="null" && $order['delivery']['comment'])<p><b>Комментарий:</b> {{ $order['delivery']['comment'] ?? "" }} </p>@endif

@endif
