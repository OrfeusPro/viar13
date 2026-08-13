<h2 class="page-title">
@if ($h2_titles->other_work_h2!='')
    {!! $h2_titles->other_work_h2  !!}
@else
        @lang('sharj.translate28')
@endif
</h2>

<div class="text">
    @lang('sharj.translate29')
</div>
<div class="sharj-example">
    <div class="sharj-example__grid">
        <?php $appUrl = config('app.url'); ?>

        <?php
        $j = 0;
        $max = 8;
        $i=100;
        foreach ($ba_items2 as $ba_item)
        {
            $class = "";
            $j++; $i++;
            $absoluteImagePath= public_path($ba_item->getUrl());
            if (file_exists($absoluteImagePath)) {
                $imageDimensions = getimagesize($absoluteImagePath);
                if ($imageDimensions !== false) {
                    // The image dimensions are available.
                    $width = $imageDimensions[0];
                    $height = $imageDimensions[1];
                    if ($width>$height)
                    {
                        $max--;
                        $class='span-2';
                    }
                    else {
                        $class='';
                    }

                }
            }

            if ($j>$max) { $class.=' top-item_hide ';}

            ?>
        <div class="sharj-example__item {{ $class ?? '' }}">
            <div class="img js-simps-calc" 
                attr_image="{{ $ba_item->getUrl() }}" 
                attr_type="obraz"
                 @isset($ba_item["custom_properties"][$locale])
                    attr_name="{{ $ba_item["custom_properties"][$locale] }}"
                 @else
                    attr_name="@lang('sharj.cat_po_shablonu') #{{ $i }}"
                 @endisset
                 
                 >
                @php
                    $sharjExampleImageSources = image_picture_sources($ba_item->getUrl(), true);
                @endphp
                <picture>
                    @if(!empty($sharjExampleImageSources['src_webp']))
                        <source srcset="{{ $sharjExampleImageSources['src_webp'] }}" type="image/webp">
                    @endif
                    @if(!empty($sharjExampleImageSources['src']) && !empty($sharjExampleImageSources['type']))
                        <source srcset="{{ $sharjExampleImageSources['src'] }}" type="{{ $sharjExampleImageSources['type'] }}">
                    @endif
                    <img width="315" height="451" src="{{ $sharjExampleImageSources['src'] }}" alt="">
                </picture>
                <div class="default-btn">
                    @lang('sharj.translate30')
                </div>
            </div>
        </div>
        <?php
        }
        ?>
    </div>

    @if ($j>$max)
    <a href="#" class="top-all js_more_wks" data-more="{{ trans('homepage_new.top_sales_more_btn_title') }}" data-less="@lang('homepage_new.hide')">
        <picture>
            <source srcset="https://viarcanvas.com/images/icon/load-more.webp" type="image/webp">
            <source srcset="https://viarcanvas.com/images/icon/load-more.png">
            <img src="https://viarcanvas.com/images/icon/load-more.png" alt="img" loading="lazy">
        </picture>
        <span>{{ trans('homepage_new.top_sales_more_btn_title') }}</span>
    </a>
    @endif
</div>
