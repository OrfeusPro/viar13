
@if (Route::currentRouteName() == 'caricature')
    <h2 class="page-title"><span class="orange">@lang("sharj.translate2")</h2>
@elseif (Route::currentRouteName() == 'home')
    <h2 class="page-title"><span class="orange">@lang("sharj.translate2")</h2>
@else
    <h2 class="page-title"><span class="orange">@lang("sharj.translate2")</h2>
@endif
<div class="text">
    <span class="orange">@lang("sharj.translate3")</div>

<div class="portrait-list__inner sharj-list__inner">

    @foreach($categories as $category)
        @if(!$category->image==null)
            <?php
            $absoluteImagePath= public_path('/storage/'.$category->image);

            if (file_exists($absoluteImagePath)) {
                $imageDimensions = getimagesize($absoluteImagePath);
                if ($imageDimensions !== false) {
                // The image dimensions are available.
                $width = $imageDimensions[0];
                $height = $imageDimensions[1];
                if ($width>$height) { $class='span-2';} else { $class='';}
                }
            }
            ?>

            <div class="portrait-list__item {{ $class ?? "" }}">
                <p>{!! $category->name !!}</p>
                <div class="portrait-list__img  ">
                    @php
                        $sharjCategoryImageSources = image_picture_sources('/storage/'.$category->image, true);
                    @endphp
                    <picture>
                        @if(!empty($sharjCategoryImageSources['src_webp']))
                            <source srcset="{{ $sharjCategoryImageSources['src_webp'] }}" type="image/webp">
                        @endif
                        @if(!empty($sharjCategoryImageSources['src']) && !empty($sharjCategoryImageSources['type']))
                            <source srcset="{{ $sharjCategoryImageSources['src'] }}" type="{{ $sharjCategoryImageSources['type'] }}">
                        @endif
                        <img width="315" height="451" src="{{ $sharjCategoryImageSources['src'] }}" alt="">
                    </picture>
                    <a href="{{ storefront_url('/new/caricature/'.$category->slug) }}" class="default-btn">@lang('simpson.yellow_btn')</a>
                </div>
            </div>


        @endif
    @endforeach

</div>










