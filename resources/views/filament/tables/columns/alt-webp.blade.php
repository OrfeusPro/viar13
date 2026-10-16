@php
    $info = app(\App\Services\AltGeneration\AltWebpInfo::class)->info((string) $getRecord()->image_path);
@endphp
<div style="width:160px;white-space:normal;overflow-wrap:anywhere">
    <strong>{{ $info['exists'] ? 'Есть' : 'Нет' }}</strong>
    @if($info['exists'])
        <p><a href="{{ $info['url'] }}" target="_blank" rel="noopener noreferrer">Открыть WebP</a></p>
    @endif
    <small>{{ $info['path'] }}</small>
</div>
