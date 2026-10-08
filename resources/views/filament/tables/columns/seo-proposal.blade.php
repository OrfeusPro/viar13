@php($record = $getRecord())
<div style="min-width:240px;max-width:360px;white-space:normal;overflow-wrap:anywhere;padding:8px 0">
    @foreach (['meta_title' => ['Meta Title', config('seo_meta_generation.limits.meta_title_max', 60)], 'meta_description' => ['Meta Description', config('seo_meta_generation.limits.meta_description_max', 155)]] as $field => [$label, $max])
        @php($value = $record->{'approved_'.$field} ?? $record->{'suggested_'.$field})
        <div style="margin-bottom:10px"><strong>{{ $label }}</strong><p>{{ $value ?: '—' }}</p><small style="color:{{ mb_strlen($value ?? '') > $max ? '#dc2626' : '#64748b' }}">{{ mb_strlen($value ?? '') }} / {{ $max }}</small></div>
    @endforeach
    @if ($record->seo_keywords)<small style="color:#64748b">Ключевые слова: {{ $record->seo_keywords }}</small>@endif
</div>
