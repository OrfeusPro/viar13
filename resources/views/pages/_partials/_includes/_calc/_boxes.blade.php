@forelse($galleryBoxes as $box)
    <div class="comments-item">
        <input name="boxes[]" type="radio" checked data-id="{{ $box->id }}"
            data-name="{{ $box->getTranslatedAttribute('name', app()->getLocale()) }}" value="{{ $box->price }}">
        <label>{{ $box->getTranslatedAttribute('name', app()->getLocale()) }}
            <i class="icon-icon1 hint__icon"><span
                    class="hint__text">{{ $box->getTranslatedAttribute('hint', app()->getLocale()) }}</span></i></label>
    </div>
@empty
@endforelse
