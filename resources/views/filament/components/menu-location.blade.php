@php
    $path = \App\Filament\Bread\BreadMenuTree::activePath(\App\Filament\Bread\BreadMenuTree::make(filament()->getNavigation()));
@endphp
@if ($path)
    <nav aria-label="Путь текущего раздела" class="viar-menu-location">
        <a href="{{ url('/filament') }}">Инфопанель</a>
        @foreach ($path as $node)
            <span aria-hidden="true">›</span>
            @if ($loop->last)
                <span aria-current="page">{{ $node['label'] }}</span>
            @elseif ($node['url'])
                <a href="{{ $node['url'] }}">{{ $node['label'] }}</a>
            @else
                <span>{{ $node['label'] }}</span>
            @endif
        @endforeach
    </nav>
@endif
