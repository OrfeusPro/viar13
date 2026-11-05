<template id="calculator-navigation">
    <nav class="calculator-steps" aria-label="{{ trans('calculator.title') }}">
        @foreach(['photo','size','frame','extras'] as $step)
        <button type="button" class="jcf-ignore" data-calculator-step="{{ $loop->index }}"><span>{{ $loop->iteration }}</span>{{ trans('calculator.'.$step) }}</button>
        @endforeach
    </nav>
</template>
<template id="calculator-navigation-bottom">
    <div class="calculator-navigation-bottom">
        <button type="button" class="jcf-ignore" data-calculator-back>← {{ trans('calculator.back') }}</button>
        <button type="button" class="jcf-ignore" data-calculator-next>{{ trans('calculator.next') }} →</button>
    </div>
</template>
<div hidden id="calculator-labels" data-fullscreen-preview="{{ trans('calculator.fullscreen_preview') }}" data-close-preview="{{ trans('calculator.close_preview') }}" data-order-error="{{ trans('basket.error_add') }}" data-prep-preview="{{ trans('calculator.prep_preview') }}" data-prep-original="{{ trans('calculator.prep_original') }}" data-canvas-short="{{ trans('calculator.canvas_short') }}" data-room-short="{{ trans('calculator.room_short') }}" data-frame-short="{{ trans('calculator.frame_short') }}" data-photo-comment="{{ trans('calculator.photo_comment') }}" data-order-comment="{{ trans('calculator.order_comment') }}"
@foreach(['size','pin_preview','unpin_preview','save_comment','comment_saved','base_price','improve_photo','photo_title','quality_title','frame_title','extras_title','upload','upload_hint','uploading','replace_photo','cart'] as $key) data-{{ str_replace('_','-',$key) }}="{{ trans('calculator.'.$key) }}" @endforeach data-frame-optional="{{ trans('calculator.frame_optional') }}" data-photo-size="{{ trans('calculator.photo_size') }}" data-canvas="{{ trans('calculator.canvas') }}" data-replace="{{ trans('calculator.replace') }}" data-all-sizes="{{ trans('calculator.all_sizes') }}" data-less-sizes="{{ trans('calculator.less_sizes') }}" data-hide-preview="{{ trans('calculator.hide_preview') }}" data-show-preview="{{ trans('calculator.show_preview') }}" data-details="{{ trans('calculator.details') }}" data-total="{{ trans('calculator.total') }}" data-frame="{{ trans('calculator.frame') }}" data-fallback="{{ trans('calculator.fallback') }}" data-title="{{ trans('calculator.title') }}" data-subtitle="{{ trans('calculator.subtitle') }}" data-hint="{{ trans('calculator.size_hint') }}"
@foreach(['portrait','square','landscape','panorama'] as $shape) data-shape-{{ $loop->iteration }}="{{ trans('calculator.'.$shape) }}" @endforeach></div>
<link rel="stylesheet" href="{{ asset(config('theme.current').'css/calculator-layout.css') }}?v={{ hash_file('sha256', public_path(config('theme.current').'css/calculator-layout.css')) }}">
<script defer src="{{ asset(config('theme.current').'js/canvas-room.js') }}?v={{ hash_file('sha256', public_path(config('theme.current').'js/canvas-room.js')) }}"></script>
<script defer src="{{ asset(config('theme.current').'js/calculator-layout.js') }}?v={{ hash_file('sha256', public_path(config('theme.current').'js/calculator-layout.js')) }}"></script>

<script id="calculator-preparation-text" type="application/json">@json(trans('calculator.preparation'))</script>

<script id="calculator-room-text" type="application/json">@json(trans('calculator.rooms'))</script>

<script id="calculator-finish-text" type="application/json">@json(trans('calculator.finish_details'))</script>

<script id="calculator-material-text" type="application/json">@json(trans('calculator.material_details'))</script>

<script id="calculator-frame-structure-text" type="application/json">@json(trans('calculator.frame_structure'))</script>

<script id="calculator-ui-text" type="application/json">@json(trans('calculator.ui'))</script>

<script id="calculator-frame-rates" type="application/json">@json((\Illuminate\Support\Facades\Schema::hasColumn('canvas_rams', 'price_per_meter') ? \App\Models\CanvasRam::pluck('price_per_meter', 'id') : []))</script>

<script id="calculator-catalogue-text" type="application/json">@json(trans('canvas_catalogue'))</script>
