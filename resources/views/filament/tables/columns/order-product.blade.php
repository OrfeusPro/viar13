@php
    $record = $getRecord();
    $items = is_array($record->items) ? $record->items : (json_decode((string) $record->items, true) ?: []);
    $delivery = is_array($record->delivery) ? $record->delivery : (json_decode((string) $record->delivery, true) ?: []);
    $products = collect($items)->filter(fn ($item, $key) => is_int($key) && is_array($item) && array_key_exists('sumPrice', $item));
    $toFloat = static function ($value): float {
        $value = preg_replace('/[^0-9,.\-]/', '', (string) $value);
        return (float) str_replace(',', '.', (string) $value);
    };
    $formatPrice = static function (float $value): string {
        $formatted = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
        return ($formatted === '' ? '0' : $formatted).' €';
    };
    $originalSubtotal = $toFloat($record->price);
    $saleSubtotal = filled($record->sale_price) ? $toFloat($record->sale_price) : null;
    $baseSubtotal = ($saleSubtotal !== null && $saleSubtotal > 0 && $saleSubtotal < $originalSubtotal)
        ? $saleSubtotal
        : $originalSubtotal;
    $discountedSubtotal = max(0, $baseSubtotal - $toFloat($record->sale_eur));
    if ($toFloat($record->sale_percent) > 0) {
        $discountedSubtotal -= $discountedSubtotal * ($toFloat($record->sale_percent) / 100);
    }
    $discountedSubtotal = round(max(0, $discountedSubtotal), 2);
    $termsPrice = $toFloat($items['total_terms_price'] ?? 0);
    $deliveryPrice = $toFloat($delivery['deliv_price'] ?? 0);
    $total = round($discountedSubtotal + $termsPrice + $deliveryPrice, 2);
    $couponLabels = [
        '30_40' => 'Использован купон: 30x40',
        'bonus' => 'Использованы бонусы',
        'facebook' => 'Использован купон: скриншот Facebook',
        '40_60' => 'Использован купон: 40x60',
        'date' => 'Использован купон: 2 даты',
        'friend' => 'Использована скидка по приглашению',
        '1free' => 'Акция: при заказе 3 картин — 1 в подарок',
        'universal' => 'Использован универсальный купон',
        'abandoned_basket' => 'Использован купон: брошенная корзина',
        'giftcard' => 'Использован купон: подарочная карта',
        'free_delivery' => 'Бесплатная доставка',
    ];
    $legacyFlags = [
        'is_coupon_30_40' => 'Использован купон: 30x40',
        'is_coupon_40_60' => 'Использован купон: 40x60',
        'is_coupon_dates' => 'Использован купон: 2 даты',
        'has_invited_sale' => 'Использована скидка по приглашению',
        'is_1free' => 'Акция: при заказе 3 картин — 1 в подарок',
        'is_universal' => 'Использован универсальный купон',
        'is_abandoned_basket' => 'Использован купон: брошенная корзина',
        'is_giftcard' => 'Использован купон: подарочная карта',
        'free_delivery' => 'Бесплатная доставка',
    ];
    $role = auth('filament')->user()?->role?->name ?? 'admin';
@endphp

<style>
    .adm-fil-order-product,
    .adm-fil-order-product * {
        box-sizing: border-box;
    }

    .adm-fil-order-product li,
    .adm-fil-order-product p,
    .adm-fil-order-product span,
    .adm-fil-order-product strong {
        max-width: 100%;
        white-space: normal !important;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
</style>
<div class="adm-fil-order-product" style="width: 235px; min-width: 220px; max-width: 235px; padding: 5px 7px; background: #fff; text-align: center; white-space: normal; font-size: 13px; line-height: 1.35; color: #313942; overflow: hidden; overflow-wrap: anywhere;">
    @if($role !== 'printing')
        <div>Стоимость:
            @if(abs($originalSubtotal - $discountedSubtotal) > 0.009)
                <span style="text-decoration: line-through;">{{ $record->price }}</span>
                <strong>{{ $formatPrice($discountedSubtotal) }}</strong>
            @else
                <strong>{{ $record->price }}</strong>
            @endif
        </div>
        @if($toFloat($record->sale_eur) > 0)<div>Скидка в евро: <strong>{{ $formatPrice($toFloat($record->sale_eur)) }}</strong></div>@endif
        @if($toFloat($record->sale_percent) > 0)<div>Скидка в процентах: <strong>{{ $toFloat($record->sale_percent) }} %</strong></div>@endif
        @if($termsPrice > 0)<div>Экспресс: <strong>{{ $formatPrice($termsPrice) }}</strong></div>@endif
        @if(array_key_exists('deliv_price', $delivery))<div>Доставка: <strong>{{ $formatPrice($deliveryPrice) }}</strong></div>@endif
        @if(array_key_exists('deliv_price', $delivery))<div>Итого: <strong>{{ $formatPrice($total) }}</strong></div>@endif

        @foreach($legacyFlags as $flag => $label)
            @if(! empty($delivery[$flag]))<div style="color: #f59e0b; font-weight: 700;">{{ $label }}</div>@endif
        @endforeach
        @if(filled($delivery['bonus'] ?? null))<div style="color: #f59e0b; font-weight: 700;">Использовано {{ $delivery['bonus'] }} бонусов</div>@endif
        @if(filled($delivery['coupon_type'] ?? null) && isset($couponLabels[$delivery['coupon_type']]))
            <div style="color: #f59e0b; font-weight: 700;">{{ $couponLabels[$delivery['coupon_type']] }}</div>
        @endif

        <div style="margin: 8px 0; border-top: 1px solid #d8dee7;"></div>
        @if($record->user)
            <button type="button" x-on:click.stop="$wire.mountTableAction('requestReview', '{{ $record->getKey() }}')" style="padding: 0; border: 0; background: transparent; color: #2563eb; text-decoration: underline; cursor: pointer;">Запрос отзыва</button>
        @endif
    @endif

    @if(filled(data_get($items, '1.content')))
        <div style="margin-top: 7px; white-space: pre-wrap;">{{ data_get($items, '1.content') }}</div>
    @endif

    @forelse($products as $product)
        @php
            $activeSrc = filled($product['activeImage'] ?? null) ? order_image_url($product['activeImage']) : null;
            $savedSrc = filled($product['savedImage'] ?? null) ? order_image_url($product['savedImage']) : null;
            $sameImage = $activeSrc && $savedSrc && basename((string) parse_url($activeSrc, PHP_URL_PATH)) === basename((string) parse_url($savedSrc, PHP_URL_PATH));
            $isCanvasLike = isset($product['is_construct'])
                || isset($product['is_canvas_inter'])
                || isset($product['is_modular_inter'])
                || isset($product['is_oil_portrait'])
                || ! empty($product['is_gall_with_img'])
                || ! empty($product['is_canvas_collage'])
                || (string) ($product['basketType'] ?? '') === '1';
        @endphp
        <div style="margin-top: 9px; padding-top: 8px; border-top: 1px solid #d8dee7;">
            @isset($product['name'])<div>Товар: {{ strip_tags((string) $product['name']) }}</div>@endisset
            @if(array_key_exists('improve_photo', $product))<div>Улучшение фото: {{ $product['improve_photo'] === 'undefined' ? 'Базовое 0€' : $product['improve_photo'] }}</div>@endif

            @if(filled($product['offsetImage'] ?? null))
                <div><a target="_blank" rel="noopener" href="{{ order_image_url($product['offsetImage']) }}" style="color: #2563eb; text-decoration: underline;">Картинка с отступами</a></div>
            @endif
            @if($activeSrc)
                <a target="_blank" rel="noopener" href="{{ $activeSrc }}"><img src="{{ $activeSrc }}" alt="Изображение товара" style="display: block; max-width: 150px; max-height: 170px; margin: 6px auto; object-fit: contain;"></a>
            @endif
            @if($savedSrc && ! $sameImage && ! $isCanvasLike)
                <a target="_blank" rel="noopener" href="{{ $savedSrc }}"><img src="{{ $savedSrc }}" alt="Исходное изображение" style="display: block; max-width: 150px; max-height: 170px; margin: 6px auto; object-fit: contain;"></a>
            @endif
            @if(! empty($product['fon_images']) && is_array($product['fon_images']))
                <div>Фон:
                    @foreach($product['fon_images'] as $background)
                        <a target="_blank" rel="noopener" href="{{ $background }}" style="color: #2563eb; text-decoration: underline;">Фон-{{ $loop->index }}</a>
                    @endforeach
                </div>
            @endif
            @if(isset($product['is_gift_card']))
                <img src="{{ asset('img/benefits-img1.png') }}" alt="Подарочная карта" style="display: block; max-width: 72px; margin: 5px auto;">
            @endif

            <ul style="margin: 5px 0 0; padding-left: 16px; list-style: none;">
                @include('partials.all_basket_order_items', ['is_admin_data_orders' => 'true', 'role' => $role])
            </ul>
            @if($role !== 'printing' && filled($product['sumFormatedPrice'] ?? null))<div>Цена: {{ $product['sumFormatedPrice'] }}</div>@endif
            @if(! empty($product['has_gift']))<strong>Подарок</strong>@endif
        </div>
    @empty
        <div style="margin-top: 8px; color: #6b7280;">Позиции отсутствуют</div>
    @endforelse

    @if(filled($delivery['comment'] ?? null) && $delivery['comment'] !== 'null')
        <div style="margin-top: 8px;"><strong>Комментарий:</strong> {{ $delivery['comment'] }}</div>
    @endif
</div>
