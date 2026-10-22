<div style="display:flex;flex-wrap:wrap;gap:12px">
    @forelse ($images as $image)
        @php
            $src = order_image_url($image);
            $extension = strtolower(pathinfo(parse_url($image, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            $document = in_array($extension, ['pdf', 'psd', 'fig'], true);
        @endphp
        <a href="{{ $src }}" target="_blank" rel="noopener noreferrer" @if($document) aria-label="Открыть {{ strtoupper($extension) }}" @endif>
            @if(in_array($extension, ['pdf', 'psd'], true))
                <img src="{{ asset('img/'.$extension.'.svg') }}" alt="{{ strtoupper($extension) }}" loading="lazy" style="width:96px;height:96px;object-fit:contain;border-radius:8px">
            @elseif($extension === 'fig')
                <span style="display:flex;align-items:center;justify-content:center;width:96px;height:96px;border:1px solid #e5e7eb;border-radius:8px;background:#f8fafc;font-weight:600">FIG</span>
            @else
                <img src="{{ $src }}" alt="Фото клиента" loading="lazy" style="width:96px;height:96px;object-fit:contain;border-radius:8px">
            @endif
        </a>
    @empty
        <span>Нет фотографий</span>
    @endforelse
</div>
