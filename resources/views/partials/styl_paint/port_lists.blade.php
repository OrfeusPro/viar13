<section class="portraits">
    <div class="portraits-content">
        <div class="title">
            <h2>{{ $port_info['port_obr_title'] }}</h2>
        </div>
        <p>{{ $port_info['port_obr_text'] }}</p>
        <div class="portraits-slider">
            @foreach($styl_page_port_obr_items->chunk(2) as $top_item)
            <div>
                @foreach($top_item as $item)
                <div class="portraits-item">
                    <div class="img">
                        <i></i>
                        <img alt="{{ $item->name }}" title="{{ $item->name }}" class="p__img"
                            src="{{ Voyager::image($item->image) }}">
                    </div>
                    <h4>{{ $item->name }}</h4>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
        <a href="{{ $port_info['all_obr_link'] }}"><span>{{ $port_info['all_obr_link_text'] }}</span></a>
    </div>
</section>

<section class="group">
    <div class="group-content">
        <div class="title">
            <h2>{{ $port_info['group_obr_title'] }}</h2>
        </div>
        <p>{{ $port_info['group_obr_text'] }}</p>


        <div class="group-slider">
            @foreach($styl_page_group_obr_items->chunk(2) as $top_item)
            <div>
                @foreach($top_item as $item)
                <div class="group-item">
                    <div class="img">
                        <img @altAttrs($item, 'image', data_get($item, 'image')) src="{{ Voyager::image($item->image) }}">
                    </div>
                    <h4>{{ $item->name }}</h4>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
        <a href="{{ $port_info['all_obr_link'] }}"><span>{{ $port_info['all_obr_link_text'] }}</span></a>
    </div>
</section>
