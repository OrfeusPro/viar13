<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr)); gap: 20px;">
    @foreach($groups as $title => $images)
        <section>
            <h3 style="font-weight: 600; margin-bottom: 10px;">{{ $title }}</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                @forelse($images as $image)
                    <figure style="width: 150px; margin: 0; overflow-wrap: anywhere;">
                        <a href="{{ $image['url'] }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ $image['preview'] }}" alt="{{ $title }}" loading="lazy" referrerpolicy="no-referrer" style="width: 150px; height: 150px; object-fit: contain; border-radius: 6px;" x-on:error.once="$el.src = @js(order_image_placeholder())">
                        </a>
                        @if($image['status'])<figcaption>{{ $image['status'] }}</figcaption>@endif
                        @if($image['date'])<small>{{ $image['date'] }}</small>@endif
                        @if(isset($removeAction, $image['removal'])){{ $removeAction($image['removal']) }}@endif
                    </figure>
                @empty
                    <p>Нет изображений</p>
                @endforelse
            </div>
        </section>
    @endforeach
</div>
