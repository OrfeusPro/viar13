@php($record = $getRecord())
<div style="width:180px;white-space:normal;overflow-wrap:anywhere">
    <strong>ALT</strong><p>{{ $record->current_alt ?: '—' }}</p>
    <strong>TITLE</strong><p>{{ $record->current_title ?: '—' }}</p>
</div>
