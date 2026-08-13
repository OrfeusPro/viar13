@isset($product['whom'])
    @if($product['whom'])
        <li>
            <img src="/theme/viar/img/icons/gift.svg">
        </li>
    @endif
@endisset

@isset($product['name'])
    <li class="js_item__name__orig">{!! $product['name'] !!}</li>
@endif
<br>

@isset($product['obraz_title'])
@if(isset($product['pid']) && $product['pid'] == "1055")
    <span>Фон:</span>
@else
    <span>Образ:</span>
@endif
 #<a href="{{ $product['obraz_img'] }}" target="_blank">{{ $product['obraz_title'] }}</a>
<br><br>
@endisset

@isset($product['obraz'])
<a @if(isset($product['obraz_img']) && $product['obraz_img']) href="{{ $product['obraz_img'] }}" @else href="#" @endif target="_blank">{{ $product['obraz'] }}</a>
<br><br>
@endisset

@isset($product['savedImage'])
    <li class="savedImage">
        <a href="{{ $product['savedImage'] }}" target="_blank">{{ trans('gl.orig_image') }}</a>
    </li>
@endif

@isset($product['photo_ex'])
    @if($product['photo_ex'] != '')
        <li class="photo_ex">
            <a href="{{ $product['photo_ex'] }}" target="_blank">Фотопример</a>
        </li>
    @endif
@endif

@isset($product['orig_images'])
    <li class="orig_images">
        <span>{{ trans('gl.orig_images') }}</span>
        @foreach($product['orig_images'] as $img)
            <span style="margin-right:5px;">
                <a href="{{ $img }}" target="_blank">#{{ $loop->index }}</a>
            </span>
        @endforeach
    </li>
@endif

@isset($product['sizeId'])
    @if($product['sizeId'])
        <li>
            {{ trans('cart.size') }} <b>{{ $product['sizeId'] }}</b>
        </li>
    @endif
@endif

@isset($product['whom'])
    @if($product['whom'])
        <li>
            {{ trans('gl.card_type') }}: <b>{{ $product['whom'] }}</b>
        </li>
    @endif
@endif

@isset($product['card_type'])
    @if($product['card_type'] == "offline")
        <li>
            <span style="color:red; font-weight: 600;">{{ $product['card_type'] }}</span>
        </li>
    @endif
@endif


@if(isset($product['pid']) && $product['pid'] == 5)
    @isset($product['price'])
        @if($product['price'])
            <li>
                {{ trans('gl.nominal') }}: <b>{{ $product['price'] }}€</b>
            </li>
        @endif
    @endisset
@endif

@isset($product['size_name'])
    @if($product['size_name'])
        <li>
            {{ trans('gl.size_text') }}: <b>{{ $product['size_name'] }}</b>
        </li>
    @endif
@endif

@isset($product['count'])
    @if($product['count'] > 1)
        <li>
            {{ trans('cart_new.general_quantity') }} <span style="color:red; font-weight: 600;">{{ $product['count'] }}</span>
        </li>
    @endif
@endif


@php
    $hasShowBox = isset($product['show']['box']) && is_array($product['show']['box']) && count($product['show']['box']) > 0;
@endphp
@isset($product['compl_id'])
    @if(!$hasShowBox && $product['compl_id'] != 'undefined' && $product['compl_id'])

    @php
       $pname = \App\Models\GalleryBox::getNameById($product['compl_id'])['name'];
    @endphp


    @if($pname != "Обычная упаковка"
    && $pname != "regularne pakowanie"
    && $pname != "Regulāra iepakošana"
    && $pname != "Regulāra iepakojums"
    && $pname != "Įprasta pakuotė"
    && $pname != "reguliarus pakavimas"
    && $pname != "Tavaline pakend:"
    && $pname != "Regular packing"
    && $pname != "Tavaline pakend"
    && $pname != "normale Verpackung"
    && $pname != "Стандартная упаковка"
    && $pname != "Standardowe pakowanie"
    && $pname != "Standarta iepakojums"
    && $pname != "Standartinė pakuotė"
    && $pname != "Standardpakend"
    && $pname != "Standart packaging"
    && $pname != "Standardpakend"
    && $pname != "Standardverpackung")
        <li>{{ trans('gl.pack') }}:<br>
            <span style="color:red;"><b>{{ $pname }}</b></span>
        </li>
    @else
        <li>{{ trans('gl.pack') }}:<br>
            <b>{{ $pname }}</b>
        </li>
    @endif

        {{--
        <li>{{ trans('gl.pack') }}:
            {{ App\Models\GalleryBox::getNameById($product['compl_id'])['name'] }}
        </li>
        --}}
    @endif
@endisset

@isset($product['formIndex'])
    @if($product['formIndex'])
        <li>
            {{ trans('gl.form_num_text') }}: <b>{{ $product['formIndex'] }}</b>
        </li>
    @endif
@endisset
@isset($product['is_gift_card'])
    <div class="text">
        <li>{{ trans('gl.sender') }}: <b>{{ data_get($product, 'sender', '') }}</b></li>
        <li>{{ trans('gl.reseiver') }}: <b>{{ data_get($product, 'reseiver', '') }}</b></li>
        <li>{{ trans('gl.nominal') }}: <b>{{ data_get($product, 'price', '') }}</b></li>
        <li>{{ trans('gl.date') }}: <b>{{ data_get($product, 'date', '') }}</b></li>
        <li>{{ trans('gl.torjname') }}: <b>{{ data_get($product, 'torjname', '') }}</b></li>
        <li>{{ trans('gl.torjtext') }}: <b>{{ data_get($product, 'torjtext', '') }}</b></li>
        <li>{{ trans('gl.card_type') }}: <b>{{ data_get($product, 'card_type', '') }}</b></li>
        <li>{{ trans('gl.nominal') }}:
            @if((string) data_get($product, 'hide_nom', 'false') == 'false') <b>{{ trans('gl.nom_show') }}</b>
            @else <b>{{ trans('gl.nom_hide') }}</b> @endif
        </li>

        @if(isset($is_admin_data_orders))
            @isset($product['gift_code'])
                <li><b>{{ $product['gift_code'] }}</b></li>
            @endisset
        @endif

    </div>
@endisset

@isset($product['formId'])
    @if($product['formId'] != 'undefined' && $product['formId'])
        <li>{{ trans('gl.form_id') }}: <b>{{ $product['formId'] }}</b></li>
    @endif
@endisset

@isset($product['execution'])
    @if($product['execution'] != 'undefined' && $product['execution'])
        <li>{{ trans('cart.execution_type') }}: <b>{{ $product['execution'] }}</b></li>
    @endif
@endisset

@isset($product['is_port_product'])

    @isset($product['forma_id'])
        @if($product['forma_id'] != 'undefined' && $product['forma_id'])
            <li>{{ trans('gl.bask_form') }} <b>{{ $product['forma_id'] }}</b></li>
        @endif
    @endisset

    @isset($product['users_count'])
        @if($product['users_count'] != 'undefined' && $product['users_count'])
            <li>{{ trans('gl.bask_persons') }} <b>{{ $product['users_count'] }}</b></li>
        @endif
    @endisset

    @isset($product['type'])
        @if($product['type'] != 'undefined' && $product['type'])
            <li>{{ trans('gl.bask_isp') }} <b>{{ $product['type'] }}</b></li>
        @endif
    @endisset

    @isset($product['holst_id'])
        @if($product['holst_id'] != 'undefined' && $product['holst_id'])
            <li>{{ trans('gl.bask_holst') }}
                <b>{{ App\Models\GalleryHolst::getHolstNameById($product['holst_id']) }}</b>
            </li>
        @endif
    @endisset



    @isset($product['hud_of'])
        @if($product['hud_of'] != 'undefined' && $product['hud_of'])
            <li>{{ trans('gl.bask_of') }} <b>{{ $product['hud_of'] }}</b></li>
        @endif
    @endisset

@endisset

@isset($product['ram_id'])
    @if($product['ram_id'] != 'undefined' && $product['ram_id'] && \App\Models\CanvasRam::isFramedOption($product['ram_id']))
        <li>{{ trans('gl.bask_ram') }}:
            <b>{{ App\Models\CanvasRam::getRamNameById($product['ram_id']) }}</b>
        </li>
    @endif
@endisset

@php
    $ramId = $product['ram_id'] ?? null;
    $hasFrameRam = $ramId !== null && $ramId !== '' && $ramId !== 'undefined' && \App\Models\CanvasRam::isFramedOption($ramId);
    $legacyFrameFlag = empty($ramId) && !empty($product['baget']) && (string) $product['baget'] === 'B1';
    $manualBagetChecked = !empty($product['is_manual_baget']) || $hasFrameRam || $legacyFrameFlag;
@endphp
@if($manualBagetChecked && !$hasFrameRam)
    <li><b>{{ trans('gl.bask_ram') }}</b></li>
@endif


@isset($product['wall_size_mod'])
    @if ($product['wall_size_mod'])
        <li>{{ trans('gl.wall_size') }} <b>{{ $product['wall_size_mod'] }}</b></li>
    @endif
@endisset

@php
    $is_mod = 0;
@endphp


@isset($product['terms'])
    @if($is_mod == 0)

        @if(strripos($product['terms'], "Express") === false
        && strripos($product['terms'], "Ekspress") === false
        && strripos($product['terms'], "Ekspresowy") === false
        && strripos($product['terms'], "Экспресс") === false
        && strripos($product['terms'], "Kiirsaadetis") === false
        && strripos($product['terms'], "Ekspres") === false)
           @if($product['terms'])
            <li class="bask__terms__text">{{ trans('gl.bask_izg') }}: <b>{{ $product['terms'] }}</b></li>
           @endif
        @else
            <li class="bask__terms__text">{{ trans('gl.bask_izg') }}:<br>

                @if(Auth::user()->role->name != 'painter' && Auth::user()->role->name != "printing")
                    <span style="color:red;"><b>{{ $product['terms'] }}</b></span>
                @else
                    <span style="color:red;"><b>Express</b></span>
                @endif
            </li>
        @endif

        {{-- @if ($product['terms'])
            <li class="bask__terms__text">{{ trans('gl.bask_izg') }}: {{ $product['terms'] }}</li>
        @endif --}}
    @endif
@endisset

@isset($product['is_canvas_inter'])
    @isset($product['wall_size'])
        @if($product['wall_size'] != 'x' && $product['wall_size'])
            <li>{{ trans('gl.wall_size') }} <b>{{ $product['wall_size'] }}</b></li>
        @endif
    @endisset

    @isset($product['pic_size'])
        @if($product['pic_size'] != 'x' && $product['pic_size'])
            <li>{{ trans('gl.pic_size') }} <b>{{ $product['pic_size'] }}</b></li>
        @endif
    @endisset

    @isset($product['ram_id'])
        @if($product['ram_id'] != 'undefined' && $product['ram_id'])
            <li>{{ trans('gl.rama') }} <b>{{ App\Models\GalleryItem::getRamNameById( $product['ram_id']) }}</b></li>
        @endif
    @endisset
@endisset

@isset($product['is_modular_inter'])
    @if($product['wall_size'] != 'x' && $product['wall_size'])
        <li>{{ trans('gl.wall_size') }} <b>{{ $product['wall_size'] }}</b></li>
    @endif

    @isset($product['pic_size'])
        @if($product['pic_size'] != 'x' && $product['pic_size'])
            <li>{{ trans('gl.pic_size') }} <b>{{ $product['pic_size'] }}</b></li>
        @endif
    @endisset

    @isset($product['ram_id'])
        @if($product['ram_id'] != 'undefined' && $product['ram_id'])
            <li>{{ trans('gl.rama') }} <b>{{ App\Models\GalleryItem::getRamNameById( $product['ram_id']) }}</b></li>
        @endif
    @endisset
@endisset

@php
    $showed_box = 0;
@endphp

@isset($product['show'])
    @isset($product['show']['basketType'])
        @if ($product['show']['basketType'])
            <li>@lang('basket.picture_type'):
                <b>{{ $product['show']['basketType'] }}</b>
            </li>
        @endif
    @endisset
    @isset($product['show']['effect'])
        @if ($product['show']['effect'])
            <li><b>{{ $product['show']['effect'] }}</b></li>
        @endif
    @endisset
    @isset($product['show']['decoration'])
        @if ($product['show']['decoration'])
            <li>{{ trans('gl.hud_of_text') }}: <b>{{ $product['show']['decoration'] }}</b></li>
        @endif
    @endisset

    @php
        if(!isset($product['sizeId'])):
    @endphp
    @isset( $product['show']['size'])
        @if ($product['show']['size'])
            <li>{{ trans('cart.size') }}: <b>{{ $product['show']['size'] }}</b></li>
        @endif
    @endisset
    @php
        endif;
    @endphp

    @isset( $product['show']['execution'])
        @if ($product['show']['execution'])
            <li>{{ trans('cart.execution_type') }}: <b>{{ $product['show']['execution'] }}</b></li>
        @endif
    @endisset
    @isset( $product['show']['canvas'])
        @if ($product['show']['canvas'])
            <li>{{ trans('cart.canvas') }}: <span style="color:red;"><b>{{ $product['show']['canvas'] }}</b></span></li>
        @endif
    @endisset
    @isset($product['show']['box'])
        @php
            $showed_box = 1;
        @endphp

        @if(implode(', ', $product['show']['box']) != "Обычная упаковка"
        && implode(', ', $product['show']['box']) != "regularne pakowanie"
        && implode(', ', $product['show']['box']) != "Regulāra iepakošana"
        && implode(', ', $product['show']['box']) != "Regulāra iepakojums"
        && implode(', ', $product['show']['box']) != "Įprasta pakuotė"
        && implode(', ', $product['show']['box']) != "reguliarus pakavimas"
        && implode(', ', $product['show']['box']) != "Tavaline pakend:"
        && implode(', ', $product['show']['box']) != "Regular packing"
        && implode(', ', $product['show']['box']) != "Tavaline pakend"
        && implode(', ', $product['show']['box']) != "normale Verpackung"
        && implode(', ', $product['show']['box']) != "Стандартная упаковка"
        && implode(', ', $product['show']['box']) != "Standardowe pakowanie"
        && implode(', ', $product['show']['box']) != "Standarta iepakojums"
        && implode(', ', $product['show']['box']) != "Standartinė pakuotė"
        && implode(', ', $product['show']['box']) != "Standardpakend"
        && implode(', ', $product['show']['box']) != "Standart packaging"
        && implode(', ', $product['show']['box']) != "Standardpakend"
        && implode(', ', $product['show']['box']) != "Standardverpackung")
            <li>{{ trans('cart.packaging') }}:<br>
                <span style="color:red;"><b>{{ implode(', ', $product['show']['box']) }}</b></span>
            </li>
        @else
            <li>{{ trans('cart.packaging') }}:<br>
                <b>{{ implode(', ', $product['show']['box']) }}</b>
            </li>
        @endif


    @endisset
@endisset

@isset($product['canvas'])
    @if ($product['canvas'])
        <li>{{ trans('cart.canvas') }}: <span style="color:red;"><b>{{ $product['canvas'] }}</b></span></li>
    @endif
@endif

@isset($product['pack'])
    @if($product['pack'] != 'undefined' && $product['pack'])
        @if($showed_box != 1 && empty($product['compl_id']))
            @if($product['pack'] != "Обычная упаковка"
            && $product['pack'] != "regularne pakowanie"
            && $product['pack'] != "Regulāra iepakošana"
            && $product['pack'] != "Regulāra iepakojums"
            && $product['pack'] != "Įprasta pakuotė"
            && $product['pack'] != "reguliarus pakavimas"
            && $product['pack'] != "Tavaline pakend:"
            && $product['pack'] != "Regular packing"
            && $product['pack'] != "Tavaline pakend"
            && $product['pack'] != "normale Verpackung"
            && $product['pack'] != "Стандартная упаковка"
            && $product['pack'] != "Standardowe pakowanie"
            && $product['pack'] != "Standarta iepakojums"
            && $product['pack'] != "Standartinė pakuotė"
            && $product['pack'] != "Standardpakend"
            && $product['pack'] != "Standart packaging"
            && $product['pack'] != "Standardpakend"
            && $product['pack'] != "Standardverpackung")
                <li>{{ trans('cart.packaging') }}:<br>
                    <span style="color:red;"><b>{{ $product['pack'] }}</b></span>
                </li>
            @else
                <li>{{ trans('cart.packaging') }}:<br>
                    <b>{{ $product['pack'] }}</b>
                </li>
            @endif
        @endisset
    @endif
@endisset

@if(isset($product['userComment']))
    @if ($product['userComment'])
    <br>
        <li>
            <b>{{ trans('gl.comments') }}</b> {!! $product['userComment'] !!}
        </li>
    @endif
@endif
