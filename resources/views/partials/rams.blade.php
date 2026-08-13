<div class="ramu-slider js_rams">
    @foreach($def_inter_rams as $ram)
    <label>
        <div
        data-price="{{ $ram->price }}"
        data-id="{{ $ram->id }}"
        @if($ram->price == 0) ramu_zero_item
        data-interior="empty"
        class="ramu-item active"
        @else
        class="ramu-item"
        data-src="{{ Voyager::image($ram->img) }}" data-width="{{ $ram->css_params }}"
        @endif>
            <input
            style="opacity:0;
            position:absolute;pointer-events:none;"
            type="radio"
            name="ram_id" value="{{ $ram->id }}">
            <span class="code">{{ $ram->name }}</span>
            <div class="img">
                <i class="icon-down-arrow"></i>
                @if($ram->price != 0)
                <img @altAttrs($ram, 'img_bg', data_get($ram, 'img_bg')) src="{{ Voyager::image($ram->img_bg) }}">
                @endif
            </div>
            <span class="price">
                @if($ram->price != 0)
                + {{ $ram->price }} &euro;
                @else
                --
                @endif
            </span>
        </div>
    </label>
    @endforeach
</div>

