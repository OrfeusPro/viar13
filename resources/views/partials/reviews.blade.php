<div class="our-work-items clearfix">
    @foreach ($reviews->chunk(2) as $rev)
        <div>
            @foreach ($rev as $item)
                <div class="our-work-item">
                    <div class="avatar">
                        <img class="" src="{{ Voyager::image($item->avatar) }}"
                            data-src="{{ Voyager::image($item->avatar) }}" alt="">
                    </div>
                    <div class="img"
                        style="background: url({{ Voyager::image($item->img) }}) no-repeat 50% 50%; background-size: cover;">
                    </div>
                    <div class="name">
                        <h5 style="width: calc(100% - 25px);display: block;word-break: break-all;">{{ $item->name }}
                        </h5>
                        <span>{{ Carbon\Carbon::parse($item->created_at)->format('d.m.Y ') }}</span>
                    </div>
                    <div class="text">
                        {!! $item->text !!}
                    </div>
                    @if ($item->a_player)
                        @php
                            if (isset(json_decode($item->a_player)[0])) {
                                $file = json_decode($item->a_player)[0]->download_link;
                            } else {
                                $file = '';
                            }

                        @endphp
                        <div class="player">
                            @if (Voyager::image($file))
                                <audio controls src="{{ Voyager::image($file) }}"></audio>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach
</div>
