@php($record = $getRecord())
<div class="viar-seo-current">
    @foreach (['current_meta_title' => ['Meta Title', config('seo_meta_generation.limits.meta_title_max', 60)], 'current_meta_description' => ['Meta Description', config('seo_meta_generation.limits.meta_description_max', 155)]] as $field => [$label, $max])
        <div class="viar-seo-current-field"><span>{{ $label }}</span><p>{{ $record->$field ?: '—' }}</p><small style="color:{{ mb_strlen($record->$field ?? '') > $max ? '#dc2626' : '#64748b' }}">{{ mb_strlen($record->$field ?? '') }} / {{ $max }}</small></div>
    @endforeach
</div>
