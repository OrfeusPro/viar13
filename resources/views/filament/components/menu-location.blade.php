@php
    $path = \App\Filament\Bread\BreadMenuTree::activePath(\App\Filament\Bread\BreadMenuTree::make(filament()->getNavigation()));
@endphp
@if ($path)
    <nav aria-label="Путь текущего раздела" class="viar-menu-location">
        <a href="{{ url('/filament') }}" class="viar-menu-location-home"><x-filament::icon icon="heroicon-o-home" />Инфопанель</a>
        @foreach ($path as $node)
            @continue($node['url'] === url('/filament'))
            <x-filament::icon icon="heroicon-o-chevron-right" class="viar-menu-location-separator" aria-hidden="true" />
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
