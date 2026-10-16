<div style="padding:16px;display:flex;flex-wrap:wrap;gap:20px">
    <strong>WebP</strong>
    <span>Изображений: {{ $stats['total'] }}</span>
    <span>Есть WebP: {{ $stats['with_webp'] }}</span>
    <span>Нет WebP: {{ $stats['without_webp'] }}</span>
    <span>Покрытие: {{ $stats['coverage_percent'] }}%</span>
</div>
<div style="padding:0 16px 16px;display:flex;flex-wrap:wrap;gap:16px">
    <strong>Статусы</strong>
    <button type="button" wire:click="selectStatus" style="border:1px solid #d1d5db;border-radius:6px;padding:4px 8px">Всего: {{ array_sum($statusCounts) }}</button>
    @foreach($statusCounts as $status => $count)
        <button type="button" wire:click="selectStatus('{{ $status }}')" style="border:1px solid #d1d5db;border-radius:6px;padding:4px 8px">{{ $status }}: {{ $count }}</button>
    @endforeach
</div>
