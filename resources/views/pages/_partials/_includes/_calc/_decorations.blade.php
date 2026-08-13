@forelse($galleryDecorations as $decoration)
    <div class="decoration-item js_decor_item @if ($decoration->price == 0) js_decor_null @endif"
        @if ($decoration->price == 0) style="display:none;" @endif
        >
        <input name="decoration" type="radio"
            data-name="{{ $decoration->getTranslatedAttribute('name', app()->getLocale()) }}"
            data-id="{{ $decoration->id }}" data-coef_sm="{{ $decoration->coef_sm }}"
            data-coef_md="{{ $decoration->coef_md }}" data-coef_lg="{{ $decoration->coef_lg }}"
            value="{{ $decoration->price }}">
        <label>{{ $decoration->getTranslatedAttribute('name', app()->getLocale()) }}
            <i class="icon-icon1 hint__icon">
                <span class="hint__text">{{ $decoration->getTranslatedAttribute('hint', app()->getLocale()) }}</span>
            </i></label>
    </div>
@empty
@endforelse
