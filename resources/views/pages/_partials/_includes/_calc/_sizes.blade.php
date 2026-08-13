<ul>
    @forelse($gallerySizes as $size)
        <li><a class="calcSize js_size @if ($loop->first) active @endif"
                data-price="{{ $size->price }}"
                data-size="{{ $size->size }}"
                data-id="{{ $size->id }}"
                href="javascript:void(0)"><strong>{{ $size->getHeight() }}
                    <span>{{ $int_globs['cm'] }}</span></strong> х
                <strong>{{ $size->getLength() }}<span>{{ $int_globs['cm'] }}</span></strong> -
                <strong>{{ $size->price }} &euro;</strong></a></li>
    @empty
    @endforelse
</ul>
