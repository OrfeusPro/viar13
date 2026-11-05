<dl style="display:grid;grid-template-columns:minmax(0,1fr) auto;gap:8px 24px">
    @foreach ($sales as $label => $value)
        <dt>{{ $label }}</dt>
        <dd>{{ $value }}</dd>
    @endforeach
</dl>
