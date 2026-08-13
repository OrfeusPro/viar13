<div class="tabs-item tabs-work">
    <div class="height-content">
        <div class="tabs-height tabs-work-content">
            <div class="title">
                <h2>{{ $canvas_work_serv['all_our_work_title'] }}</h2>
            </div>
            <div class="tabs-work-slider">
                @if ($our_works)
                    @php
                        $works = json_decode($our_works, true);
                    @endphp
                    @foreach (array_chunk($works, 2) as $item)
                        <div class="tabs-work-item chunk">
                            @php
                                $i = 0;
                            @endphp
                            @foreach ($item as $img)
                                @php
                                    $i++;
                                @endphp
                                <div class="tab-chunk tab-chunk-{{ $i }}">
                                    @php
                                        $img = str_replace('\\', '/', $img);
                                    @endphp
                                    <img class="lazy" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AAoMBgDTD2qgAAAAASUVORK5CYII="
                                    data-src="/storage/{{ $img }}" alt="">
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    <a data-collapse="tabs-work-content" class="collapse" href="javascript:void(0)"
            data-show="{{ trans('gl.show_btn') }}"
            data-hide="{{ trans('gl.hide_btn') }}"><span>{{ trans('gl.show_btn') }}</span></a>
</div>
