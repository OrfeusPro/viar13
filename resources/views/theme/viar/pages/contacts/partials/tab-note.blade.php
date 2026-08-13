@php
    $noteText = $noteText ?? '';
    $noteTextPlain = trim(str_replace("\xc2\xa0", ' ', html_entity_decode(strip_tags($noteText))));
@endphp

@if($noteTextPlain !== '')
    <div class="msc-tab-note">
        <div class="msc-tab-note__text">
            {!! $noteText !!}
        </div>
    </div>
@endif
