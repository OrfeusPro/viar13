<div class="category-pop">
    <div class="section-frame">
        <div class="category-pop__inner">
            <h2 class="category-pop__title page-title">
                @if  (Route::currentRouteName() == 'hb.gallery.module' && $type == 'photo')
                    @lang("gallery.photo_category_popular")
                    @elseif  (Route::currentRouteName() == 'hb.gallery.module' && $type == 'module')
                        @lang("gallery.module_category_popular")
                @elseif  (Route::currentRouteName() == 'hb.gallery.module' && $type == 'reproduction')
                    @lang("gallery.category_popular")
                @else
                    @lang("gallery.category_popular")
                @endif

            </h2>
            <div class="category-pop--block">
                <div class="cp-tabs">
                    <div class="cp-tabs-inner">
                        <div class="cp-tab active">
                            {!! $gallery['modc_title'] !!}
                        </div>
                        <div class="cp-tab">
                            {!! $gallery['repr_title'] !!}
                        </div>
                        <div class="cp-tab">
                            {!! $gallery['fotoc_title'] !!}
                        </div>
                    </div>
                </div>
                <div class="cp-blocks">
                    <div class="cp-block">
                        <div class="cp-block-inner">
                            @foreach ($categories_module as $item)

                                @if($loop->iteration > 3) @break @endif

                                <div class="cp-item">
                                    <div class="cp-item-inner">
                                        <div class="cp-item-inner-b">
                                            <a href="{{ route('hb.gallery.category', ["type"=> $item->getType(), "category"=> $item->url]) }}" class="mm-btn">@lang("gallery.see")</a>
                                        </div>

                                        @if($item->image)
                                        <picture>
                                            <source srcset="{{ asset('/storage') }}/{{ $item->image }}" type="image/jpeg">
                                            <img width="433" height="583" src="{{ asset('/storage') }}/{{ $item->image }}" alt="{{ $item->name }}" loading="lazy">
                                        </picture>
                                        @endif
                                    </div>
                                    <p>{{ $item->name }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="cp-block hidden-block">
                        <div class="cp-block-inner">

                            @foreach ($categories_reproduction as $item)
                                @if($loop->iteration > 3) @break @endif

                                <div class="cp-item">
                                    <div class="cp-item-inner">
                                        <div class="cp-item-inner-b">
                                            <a href="{{ route('hb.gallery.category', ["type"=> $item->getType(), "category"=> $item->url]) }}" class="mm-btn">@lang("gallery.see")</a>
                                        </div>
                                        @if($item->image)
                                        <picture>
                                            <source srcset="{{ asset('/storage') }}/{{ $item->image }}" type="image/jpeg">
                                            <img width="433" height="583" src="{{ asset('/storage') }}/{{ $item->image }}" alt="{{ $item->name }}" loading="lazy">
                                        </picture>
                                        @endif

                                    </div>
                                    <p>{{ $item->name }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="cp-block hidden-block">
                        <div class="cp-block-inner">
                            @foreach ($categories_photo as $item)

                                @if($loop->iteration > 3) @break @endif

                                <div class="cp-item">
                                    <div class="cp-item-inner">
                                        <div class="cp-item-inner-b">
                                            <a href="{{ route('hb.gallery.category', ["type"=> $item->getType(), "category"=> $item->url]) }}" class="mm-btn">@lang("gallery.see")</a>
                                        </div>

                                        @if($item->image)
                                        <picture>
                                            <source srcset="{{ asset('/storage') }}/{{ $item->image }}" type="image/jpeg">
                                            <img width="433" height="583" src="{{ asset('/storage') }}/{{ $item->image }}" alt="{{ $item->name }}" loading="lazy">
                                        </picture>
                                        @endif
                                    </div>
                                    <p>{{ $item->name }}</p>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
