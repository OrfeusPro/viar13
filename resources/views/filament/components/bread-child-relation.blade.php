<div>
    <div style="font-size:14px;font-weight:600;margin-bottom:8px">{{ $label }}</div>
    @if($creating)
        <p style="font-size:14px;color:#71717a">Связанные записи будут доступны после создания записи.</p>
    @elseif($labels === [])
        <p style="font-size:14px;color:#71717a">Связанных записей нет.</p>
    @else
        <ul style="list-style:disc;padding-left:20px;font-size:14px;display:grid;gap:6px">
            @foreach($labels as $value)<li>{{ $value }}</li>@endforeach
        </ul>
    @endif
</div>
