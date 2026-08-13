@php
    use App\Models\Locale;

    $locales = new Locale();
    $prefixs = [];

    foreach ($locales->getLocales() AS $locale) {
        $prefixs[] = $locale->prefix;
    }

@endphp

@if (isset($isModelTranslatable) && $isModelTranslatable)
    <div class="language-selector">
        <div class="btn-group btn-group-sm" role="group" data-toggle="buttons">
            @foreach($prefixs as $lang)
                <label class="btn btn-primary{{ ($lang === config('voyager.multilingual.default')) ? " active" : "" }}">
                    <input type="radio" name="i18n_selector" id="{{$lang}}"
                           autocomplete="off"{{ ($lang === config('voyager.multilingual.default')) ? ' checked="checked"' : '' }}> {{ strtoupper($lang) }}
                </label>
            @endforeach
        </div>
    </div>
@endif

