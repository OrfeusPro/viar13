<div style="display:flex;flex-wrap:wrap;gap:12px">
    @forelse ($images as $image)
        @php($src = order_image_url($image))
        <a href="{{ $src }}" target="_blank" rel="noopener noreferrer">
            <img src="{{ $src }}" alt="Фото клиента" loading="lazy" style="width:96px;height:96px;object-fit:contain;border-radius:8px">
        </a>
    @empty
        <span>Нет фотографий</span>
    @endforelse
</div>
