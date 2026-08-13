<section class="portraits">
    <div class="portraits-content">
        <div class="title">
            <h2>{{ trans('gl.sh_title') }} </h2>
        </div>
        <p>{{ trans('gl.sh_sub') }}</p>
        <div class="portraits-slider">
            @foreach($gr_top_items->chunk(2) as $ch_item)
            <div>
                @foreach($ch_item as $item)
                <div class="portraits-item">
                    <div class="img">
                        <i></i>
                        <img alt="{{ $item['name'] }}" title="{{ $item['name'] }}" class="p__img"
                            src="{{ Voyager::image(  $item['image'] ) }}">
                    </div>
                    <h4>{{ $item['name'] }}</h4>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
        <a href="{{ $data['sharj_link1'] }}"><span>{{ trans('gl.all_obr') }}</span></a>
    </div>
</section>

<section class="group">
    <div class="group-content">
        <div class="title">
            <h2>{{ trans('gl.group_obr') }}</h2>
        </div>
        <p>{{ trans('gl.group_obr_subtext') }}</p>
        <div class="group-slider">
            @foreach($gr_bot_items->chunk(2) as $item)
            <div>
                @foreach($item as $sub_item)
                <div class="group-item">
                    <div class="img">
                        <img alt="{{ $sub_item['name'] }}" title="{{ $sub_item['name'] }}"
                            src="{{ Voyager::image( $sub_item['image'] ) }}">
                    </div>
                    <h4>{{ $sub_item['name'] }}</h4>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
        <a href="{{ $data['sharj_link2'] }}"><span>{{ trans('gl.all_obr') }}</span></a>
    </div>
</section>
