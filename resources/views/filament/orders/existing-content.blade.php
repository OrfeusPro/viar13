<div style="display:grid;gap:16px">
    @foreach ($contents as $content)
        <div style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $content }}</div>
    @endforeach
</div>
