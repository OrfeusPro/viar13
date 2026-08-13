<div itemtype="https://schema.org/Product" itemscope>
    <meta itemprop="name" content="{{ $data['meta_title'] }}" />
    @php
        $s_items = $item->getMedia('our_works_new');
    @endphp
    @if ($s_items)
        @foreach ($s_items as $image)
            <link itemprop="image" href="{{ $image->getUrl() }}">
        @endforeach
    @endif
<div class="filter-picture tabs-item tabs-item1 active">
    <div class="filter-accordion">
        @include('partials.canvas_new.form.step1')
        @include('partials.canvas_new.form.step2')
        @include('partials.canvas_new.form.step3')
        @include('partials.canvas_new.form.step4')

{{--        @include('partials.canvas_new.form.step5')--}}

        @include('partials.canvas_new.form.step4_show')

        @include(env('THEME_RESOURCES') . 'pages.canvas.form.canvas')

        @include('partials.canvas_new.form.step5_show')
        @include(  'partials.schema_reviews')
    </div>
</div>
</div>
