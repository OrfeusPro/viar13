@forelse($galleryHolsts as $holst)
    <div class="canvas-item js__canv_radio _holsts_tpl" data-id="{{ $holst->id }}"
        data-name="{{ $holst->getTranslatedAttribute('name', app()->getLocale()) }}"
        data-ratio="{{ $holst->getTranslatedAttribute('density', app()->getLocale()) }}">
        <input name="canvas_type" type="radio" data-id="{{ $holst->id }}"
            data-text="{{ $holst->getTranslatedAttribute('name', app()->getLocale()) }}" @if ($loop->index == 1) checked @endif value="{{ $holst->price }}">
        <label>{{ $holst->getTranslatedAttribute('name', app()->getLocale()) }}
            <span>{{ $holst->getTranslatedAttribute('density', app()->getLocale()) }}</span>
            <i class="icon-icon1 hint__icon">
                <span class="hint__text">{{ $holst->getTranslatedAttribute('hint', app()->getLocale()) }}</span>
            </i>
        </label>
    </div>
@empty
@endforelse
