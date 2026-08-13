@forelse($galleryBoxes as $box)
    <div class="picking-item">
        <input name="boxes[]" type="checkbox" @if ($box->id == 3) checked @endif data-id="{{ $box->id }}" value="{{ $box->price }}">
        <label>{{ $box->getTranslatedAttribute('name', app()->getLocale()) }}
            <i class="icon-icon1"></i></label>
    </div>
@empty
@endforelse
