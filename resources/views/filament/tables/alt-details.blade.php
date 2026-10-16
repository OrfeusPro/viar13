<div style="display:grid;gap:24px">
    <div style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-start">
        @if($record->preview_url)
            <img src="{{ $record->preview_url }}" alt="" style="max-width:160px;max-height:120px;object-fit:contain">
        @endif
        <div style="min-width:0;flex:1;overflow-wrap:anywhere">
            <p>{{ $record->image_path }}</p>
            @php
                $webp = app(\App\Services\AltGeneration\AltWebpInfo::class)->info((string) $record->image_path);
            @endphp
            <p><strong>WebP:</strong> {{ $webp['exists'] ? 'Есть' : 'Нет' }}
                @if($webp['exists'] && $webp['url'])
                    <a href="{{ $webp['url'] }}" target="_blank" rel="noopener noreferrer">Открыть WebP</a>
                @endif
            </p>
            @if($webp['path'])
                <p>{{ $webp['exists'] ? '' : 'Ожидался: ' }}{{ $webp['path'] }}</p>
            @endif
            <p><strong>Источник:</strong> {{ class_basename($record->imageable_type ?: 'Неизвестно') }} #{{ $record->imageable_id }} ({{ $record->field }}) [{{ $record->locale }}]</p>
            @php
                $promptContext = (array) $record->prompt_context;
                $context = isset($promptContext['context']) && is_array($promptContext['context']) ? $promptContext['context'] : $promptContext;
                $blockLabel = $context['section_block'] ?? $context['purpose'] ?? $record->field ?? 'Неизвестный блок';
                $previewAlt = $record->approved_alt ?: ($record->suggested_alt ?: '');
                $previewTitle = $record->approved_title ?: ($record->suggested_title ?: '');
                $previewTag = '<img alt="'.htmlspecialchars($previewAlt, ENT_QUOTES, 'UTF-8').'" title="'.htmlspecialchars($previewTitle, ENT_QUOTES, 'UTF-8').'">';
            @endphp
            <p><strong>Блок/позиция:</strong> {{ $blockLabel }}</p>
            <p><strong>Где используется:</strong>
                @if(preg_match('~^https?://~i', (string) $record->page_url))
                    <a href="{{ $record->page_url }}" target="_blank" rel="noopener noreferrer">{{ $record->page_url }}</a>
                @else
                    {{ $record->page_url ?: 'URL страницы не найден' }}
                @endif
            </p>
        </div>
    </div>
    <section style="overflow-wrap:anywhere">
        <h3 class="font-semibold">Превью после применения</h3>
        <code>{{ $previewTag }}</code>
    </section>
    <div style="display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(180px,1fr))">
        @foreach(['current' => 'Оригинал', 'suggested' => 'Предложенное', 'approved' => 'Одобренное'] as $prefix => $label)
            <section style="border:1px solid #d1d5db;border-radius:8px;padding:16px;overflow-wrap:anywhere">
                <h3 class="font-semibold">{{ $label }}</h3>
                <p style="margin-top:12px"><strong>ALT</strong></p>
                <p>{{ $record->{$prefix.'_alt'} ?? 'None' }}</p>
                <p style="margin-top:12px"><strong>TITLE</strong></p>
                <p>{{ $record->{$prefix.'_title'} ?? 'None' }}</p>
            </section>
        @endforeach
    </div>
    @if($record->error)
        <p><strong>Ошибка:</strong> {{ $record->error }}</p>
    @endif
</div>
