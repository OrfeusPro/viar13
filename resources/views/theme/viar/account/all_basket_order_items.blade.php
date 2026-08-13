@isset($product['name'])
    <p class="text-base">
        {{ trans('gallery.rep_style') }}: <span class="darkSpan">{!! $product['name'] !!}</span>
    </p>
@endif

{{-- @isset($product['savedImage'])
    <p class="savedImage">
        <a href="http://viarcanvas.com/{{ $product['savedImage'] }}" target="_blank">{{ trans('gl.orig_image') }}</a>
    </p>
@endif

@isset($product['photo_ex'])
    @if($product['photo_ex'] != '')
        <p class="photo_ex">
            <a href="http://viarcanvas.com/{{ $product['photo_ex'] }}" target="_blank">Фотопример</a>
        </p>
    @endif
@endif
 --}}

@isset($product['orig_images'])
    <p class="orig_images">
        <p class="text-base">{{ trans('gl.orig_images') }}:
        @foreach($product['orig_images'] as $img)
            <span style="margin-right:5px;">
                <a href="{{ $img }}" target="_blank">#{{ $loop->index }}</a>
            </span>
        @endforeach
        </p>
    </p>
@endif

@isset($product['sizeId'])
    @if($product['sizeId'])
        <p class="text-base">
            {{ trans('cart.size') }} <span class="darkSpan">{{ $product['sizeId'] }}</span>
        </p>
    @endif
@endif

@isset($product['size_name'])
    @if($product['size_name'])
        <p class="text-base">
            {{ trans('gl.size_text') }}: <span class="darkSpan">{{ $product['size_name'] }}</span>
        </p>
    @endif
@endif


@if(Auth::user()->role->name != 'painter' && Auth::user()->role->name != "printing")
    @isset($product['improve_photo'])
        @if($product['improve_photo'] && $product['improve_photo'] != "undefined")
            <p class="text-base">
                {{ trans('canvas.photo_work_title') }}: <span class="darkSpan">{{ $product['improve_photo'] }}</span>
            </p>
        @endif
    @endif
@else
    @isset($product['improve_photo'])
        @if($product['improve_photo'] && $product['improve_photo'] != "undefined" && isset(explode(" ", $product['improve_photo'])[1]) && explode(" ", $product['improve_photo'])[1] > 0 )
            <p class="text-base">
                {{ trans('canvas.photo_work_title') }}: <span class="darkSpan" style="background: #63ffcb; padding-left: 5px; padding-right: 5px;">{{ explode(" ", $product['improve_photo'])[0] }}</span>
            </p>
        @endif
    @endif
@endif



@if(Auth::user()->role->name != 'painter' && Auth::user()->role->name != "printing")
    @php
        $hasShowBox = isset($product['show']['box']) && is_array($product['show']['box']) && count($product['show']['box']) > 0;
    @endphp
    @isset($product['count'])
        @if($product['count'] > 1)
            <p class="text-base">
                {{ trans('cart_new.general_quantity') }} <span class="darkSpan">{{ $product['count'] }}</span>
            </p>
        @endif
    @endif

    @isset($product['compl_id'])
        @if(!$hasShowBox && $product['compl_id'] != 'undefined' && $product['compl_id'])

        @php
        $pname = \App\Models\GalleryBox::getNameById($product['compl_id'])['name'];
        @endphp


        @if($pname != "Обычная упаковка"
        && $pname != "regularne pakowanie"
        && $pname != "Regulāra iepakošana"
        && $pname != "Regulāra iepakojums"
        && $pname != "reguliarus pakavimas"
        && $pname != "Įprasta pakuotė"
        && $pname != "Tavaline pakend:"
        && $pname != "Regular packing"
        && $pname != "Tavaline pakend:"
        && $pname != "normale Verpackung"
        && $pname != "Стандартная упаковка"
        && $pname != "Standardowe pakowanie"
        && $pname != "Standarta iepakojums"
        && $pname != "Standartinė pakuotė"
        && $pname != "Standardpakend"
        && $pname != "Standart packaging"
        && $pname != "Standardpakend"
        && $pname != "Standardverpackung")
            <p class="text-base">{{ trans('gl.pack') }}:
                <span class="darkSpan" style="color:red;">{{ $pname }}</span>
            </p>
        @else
            <p class="text-base">{{ trans('gl.pack') }}: <span class="darkSpan">{{ $pname }}</span>
            </p>
        @endif

            {{--
            <p class="text-base">{{ trans('gl.pack') }}:
                {{ App\Models\GalleryBox::getNameById($product['compl_id'])['name'] }}
            </p>
            --}}
        @endif
    @endisset


    @isset($product['formIndex'])
        @if($product['formIndex'])
            <p class="text-base">
                {{ trans('gl.form_num_text') }}: <span class="darkSpan">{{ $product['formIndex'] }}</span>
            </p>
        @endif
    @endisset



    @isset($product['is_gift_card'])
        <div class="text">
            <p class="text-base">{{ trans('gl.sender') }}: <span class="darkSpan">{{ data_get($product, 'sender', '') }}</span></p>
            <p class="text-base">{{ trans('gl.reseiver') }}: <span class="darkSpan">{{ data_get($product, 'reseiver', '') }}</span></p>
            <p class="text-base">{{ trans('gl.nominal') }}: <span class="darkSpan">{{ data_get($product, 'price', '') }}</span></p>
            <p class="text-base">{{ trans('gl.date') }}: <span class="darkSpan">{{ data_get($product, 'date', '') }}</span></p>
            <p class="text-base">{{ trans('gl.torjname') }}: <span class="darkSpan">{{ data_get($product, 'torjname', '') }}</span></p>
            <p class="text-base">{{ trans('gl.torjtext') }}: <span class="darkSpan">{{ data_get($product, 'torjtext', '') }}</span></p>
            <p class="text-base">{{ trans('gl.card_type') }}: <span class="darkSpan">{{ data_get($product, 'card_type', '') }}</span></p>
            <p class="text-base">{{ trans('gl.nominal') }}:
                @if((string) data_get($product, 'hide_nom', 'false') == 'false') <span class="darkSpan">{{ trans('gl.nom_show') }}</span>
                @else <span class="darkSpan">{{ trans('gl.nom_hide') }}</span> @endif
            </p>

            @if(isset($is_admin_data_orders))
                @isset($product['gift_code'])
                    <p class="text-base"><span class="darkSpan">{{ $product['gift_code'] }}</span></p>
                @endisset
            @endif

        </div>
    @endisset



    @isset($product['formId'])
        @if($product['formId'] != 'undefined' && $product['formId'])
            <p class="text-base">{{ trans('gl.form_id') }}: <span class="darkSpan">{{ $product['formId'] }}</span></p>
        @endif
    @endisset
@endif

@isset($product['obraz'])
    <p class="text-base" @if(Auth::user()->role->name != 'painter' && Auth::user()->role->name != "printing") @else style="background: #63ffcb; padding-left: 5px; padding-right: 5px;" @endif>@lang("account_new.orders.fashion"): 
        <span class="darkSpan">
            @isset($product['obraz_img'])<a href="{{ $product['obraz_img'] }}" target="_blank">@endif
                {{$product['obraz']}}
            @isset($product['obraz_img'])</a>@endif
        </span>
    </p>
@endisset

@if(isset($product['obraz_img']) && isset($product['obraz_title']) && $product['obraz_img'] && $product['obraz_title'])
<p class="text-base" @if(Auth::user()->role->name != 'painter' && Auth::user()->role->name != "printing") @else style="background: #63ffcb; padding-left: 5px; padding-right: 5px;" @endif>

    @if(isset($product['pid']) && $product['pid'] == "1055")
        <span>{{ trans('cart_new.fon') }}:</span>
    @else
        <span>{{ trans('gallery.rep_style') }}:</span>
    @endif
  
   <span class="darkSpan"><a href="{{ $product['obraz_img'] }}" target="_blank">{{ $product['obraz_title'] }}</a></span>
</p>
@endif

@isset($product['execution'])
    @if($product['execution'] != 'undefined' && $product['execution'])
        <p class="text-base">{{ trans('cart.execution_type') }}: <span class="darkSpan">{{ $product['execution'] }}</span></p>
    @endif
@endisset

@isset($product['is_port_product'])


    @isset($product['forma_id'])
        @if($product['forma_id'] != 'undefined' && $product['forma_id'])
            <p class="text-base">{{ trans('gl.bask_form') }} <span class="darkSpan">{{ $product['forma_id'] }}</span></p>
        @endif
    @endisset

    @isset($product['users_count'])
        @if($product['users_count'] != 'undefined' && $product['users_count'])
            <p class="text-base">{{ trans('gl.bask_persons') }} <span class="darkSpan">{{ $product['users_count'] }}</span></p>
        @endif
    @endisset

    @if(Auth::user()->role->name != 'painter' && Auth::user()->role->name != "printing")

        @isset($product['type'])
            @if($product['type'] != 'undefined' && $product['type'])
                <p class="text-base">{{ trans('gl.bask_isp') }} <span class="darkSpan">{{ $product['type'] }}</span></p>
            @endif
        @endisset

        @isset($product['holst_id'])
            @if($product['holst_id'] != 'undefined' && $product['holst_id'])
                <p class="text-base">{{ trans('gl.bask_holst') }}
                    <span class="darkSpan">{{ App\Models\GalleryHolst::getHolstNameById($product['holst_id']) }}</span>
                </p>
            @endif
        @endisset

        @isset($product['hud_of'])
            @if($product['hud_of'] != 'undefined' && $product['hud_of'])
                <p class="text-base">{{ trans('gl.bask_of') }} <span class="darkSpan">{{ $product['hud_of'] }}</span></p>
            @endif
        @endisset

    @endif

@endisset


@if(Auth::user()->role->name != 'painter' && Auth::user()->role->name != "printing")

    @isset($product['ram_id'])
        @if($product['ram_id'] != 'undefined' && $product['ram_id'] && \App\Models\CanvasRam::isFramedOption($product['ram_id']))
            <p class="text-base">{{ trans('gl.bask_ram') }}:
                <span class="darkSpan">{{ App\Models\CanvasRam::getRamNameById($product['ram_id']) }}</span>
            </p>
        @endif
    @endisset

@endif

@isset($product['wall_size_mod'])
    @if ($product['wall_size_mod'])
        <p class="text-base">{{ trans('gl.wall_size') }} <span class="darkSpan">{{ $product['wall_size_mod'] }}</span></p>
    @endif
@endisset

@php
    $is_mod = 0;
@endphp



@isset($product['terms'])
    @if($is_mod == 0 && $product['terms'])

        @if(strripos($product['terms'], "Express") === false
        && strripos($product['terms'], "Ekspress") === false
        && strripos($product['terms'], "Ekspresowy") === false
        && strripos($product['terms'], "Экспресс") === false
        && strripos($product['terms'], "Kiirsaadetis") === false
        && strripos($product['terms'], "Ekspres") === false)

            @if(Auth::user()->role->name != 'painter' && Auth::user()->role->name != "printing")
                <p class="text-base">{{ trans('gl.bask_izg') }}: <span class="darkSpan">{{ $product['terms'] }}</span></p>
            @else

            @endif

        @else
                @if(Auth::user()->role->name != 'painter' && Auth::user()->role->name != "printing")
                    <p class="text-base">{{ trans('gl.bask_izg') }}: 
                        <span class="darkSpan" style="color:red;">{{ $product['terms'] }}</span>
                    </p>
                @else
                    <p class="text-base">
                        @php
                            $painterDeadlineTs = strtotime($order->painter_end_time);
                        @endphp
                        <span class="darkSpan" style="background: #63ffcb; padding-left: 5px; padding-right: 5px;">
                            Выполнить до {{ date('H:i', $painterDeadlineTs) }} - {{ date('d.m.Y', $painterDeadlineTs) }}
                        </span>
                    </p>
                    {{-- Express --}}
                @endif
        @endif

    @endif
@endisset


@isset($product['is_canvas_inter'])
    @isset($product['wall_size'])
        @if($product['wall_size'] != 'x' && $product['wall_size'])
            <p class="text-base">{{ trans('gl.wall_size') }} <span class="darkSpan">{{ $product['wall_size'] }}</span></p>
        @endif
    @endisset

    @isset($product['pic_size'])
        @if($product['pic_size'] != 'x' && $product['pic_size'])
            <p class="text-base">{{ trans('gl.pic_size') }} <span class="darkSpan">{{ $product['pic_size'] }}</span></p>
        @endif
    @endisset

    @isset($product['ram_id'])
        @if($product['ram_id'] != 'undefined' && $product['ram_id'])
            <p class="text-base">{{ trans('gl.rama') }} <span class="darkSpan">{{ App\Models\GalleryItem::getRamNameById( $product['ram_id']) }}</span></p>
        @endif
    @endisset
@endisset

@isset($product['is_modular_inter'])
    @if($product['wall_size'] != 'x' && $product['wall_size'])
        <p class="text-base">{{ trans('gl.wall_size') }} <span class="darkSpan">{{ $product['wall_size'] }}</span></p>
    @endif

    @isset($product['pic_size'])
        @if($product['pic_size'] != 'x' && $product['pic_size'])
            <p class="text-base">{{ trans('gl.pic_size') }} <span class="darkSpan">{{ $product['pic_size'] }}</span></p>
        @endif
    @endisset

    @isset($product['ram_id'])
        @if($product['ram_id'] != 'undefined' && $product['ram_id'])
            <p class="text-base">{{ trans('gl.rama') }} <span class="darkSpan">{{ App\Models\GalleryItem::getRamNameById( $product['ram_id']) }}</span></p>
        @endif
    @endisset
@endisset

@php
    $showed_box = 0;
@endphp

@isset($product['show'])
    @isset($product['show']['basketType'])
        @if ($product['show']['basketType'])
            <p class="text-base">@lang('basket.picture_type'):
                <span class="darkSpan">{{ $product['show']['basketType'] }}</span>
            </p>
        @endif
    @endisset
    @isset($product['show']['effect'])
        @if ($product['show']['effect'])
            <p class="text-base"><span class="darkSpan">{{ $product['show']['effect'] }}</span></p>
        @endif
    @endisset
    @isset($product['show']['decoration'])
        @if ($product['show']['decoration'])
            <p class="text-base">{{ trans('gl.hud_of_text') }}: <span class="darkSpan">{{ $product['show']['decoration'] }}</span></p>
        @endif
    @endisset

    @php
        if(!isset($product['sizeId'])):
    @endphp
    @isset( $product['show']['size'])
        @if ($product['show']['size'])
            <p class="text-base">{{ trans('cart.size') }}: <span class="darkSpan">{{ $product['show']['size'] }}</span></p>
        @endif
    @endisset
    @php
        endif;
    @endphp

    @isset( $product['show']['execution'])
        @if ($product['show']['execution'])
            <p class="text-base">{{ trans('cart.execution_type') }}: <span class="darkSpan">{{ $product['show']['execution'] }}</span></p>
        @endif
    @endisset
    @isset( $product['show']['canvas'])
        @if ($product['show']['canvas'])
            <p class="text-base">{{ trans('cart.canvas') }}: <span class="darkSpan">{{ $product['show']['canvas'] }}</span></p>
        @endif
    @endisset

    @if(Auth::user()->role->name != 'painter' && Auth::user()->role->name != "printing")
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
            && implode(', ', $product['show']['box']) != "Tavaline pakend:"
            && implode(', ', $product['show']['box']) != "normale Verpackung"
            && implode(', ', $product['show']['box']) != "Стандартная упаковка"
            && implode(', ', $product['show']['box']) != "Standardowe pakowanie"
            && implode(', ', $product['show']['box']) != "Standarta iepakojums"
            && implode(', ', $product['show']['box']) != "Standartinė pakuotė"
            && implode(', ', $product['show']['box']) != "Standardpakend"
            && implode(', ', $product['show']['box']) != "Standart packaging"
            && implode(', ', $product['show']['box']) != "Standardpakend"
            && implode(', ', $product['show']['box']) != "Standardverpackung")
                <p class="text-base">{{ trans('cart.packaging') }}: 
                    <span class="darkSpan" style="color:red;">{{ implode(', ', $product['show']['box']) }}</span>
                </p>
            @else
                <p class="text-base">{{ trans('cart.packaging') }}: 
                    <span class="darkSpan">{{ implode(', ', $product['show']['box']) }}</span>
                </p>
            @endif
        @endisset
    @endif

@endisset

@isset($product['canvas'])
    @if ($product['canvas'])
        <p class="text-base">{{ trans('cart.canvas') }}: <span class="darkSpan">{{ $product['canvas'] }}</span></p>
    @endif
@endif


@if(Auth::user()->role->name != 'painter' && Auth::user()->role->name != "printing")

    @isset($product['pack'])
        @if($product['pack'] != 'undefined' && $product['pack'])
            @if($showed_box != 1 && empty($product['compl_id']))
                @if($product['pack'] != "Обычная упаковка"
                && $product['pack'] != "regularne pakowanie"
                && $product['pack'] != "Regulāra iepakošana"
                && $product['pack'] != "Regulāra iepakojums"
                && $product['pack'] != "reguliarus pakavimas"
                && $product['pack'] != "Įprasta pakuotė"
                && $product['pack'] != "Tavaline pakend:"
                && $product['pack'] != "Regular packing"
                && $product['pack'] != "Tavaline pakend:"
                && $product['pack'] != "normale Verpackung"
                && $product['pack'] != "Стандартная упаковка"
                && $product['pack'] != "Standardowe pakowanie"
                && $product['pack'] != "Standarta iepakojums"
                && $product['pack'] != "Standartinė pakuotė"
                && $product['pack'] != "Standardpakend"
                && $product['pack'] != "Standart packaging"
                && $product['pack'] != "Standardpakend"
                && $product['pack'] != "Standardverpackung")
                    <p class="text-base">{{ trans('cart.packaging') }}: 
                        <span class="darkSpan" style="color:red;">{{ $product['pack'] }}</span>
                    </p>
                @else
                    <p class="text-base">{{ trans('cart.packaging') }}: 
                        <span class="darkSpan">{{ $product['pack'] }}</span>
                    </p>
                @endif
            @endisset
        @endif
    @endisset

@endif
{{-- 
@if(isset($product['userComment']))
    @if ($product['userComment'])
    <br>
        <p class="text-base">
            <span class="darkSpan">{{ trans('gl.comments') }}</span> {{ $product['userComment'] }}
        </p>
    @endif
@endif --}}
