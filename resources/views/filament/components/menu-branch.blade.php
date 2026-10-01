@foreach ($nodes as $node)
    <li class="viar-menu-branch">
        @if ($node['children'])
            <details @if ($node['active']) open @endif>
                <summary @class(['viar-menu-button', 'is-active-parent' => $node['active']])>
                    <x-filament::icon :icon="$node['icon']" />
                    <span>{{ $node['label'] }}</span>
                    <x-filament::icon icon="heroicon-o-chevron-down" class="viar-menu-chevron" />
                </summary>
                <ul class="viar-menu-children">@include('filament.components.menu-branch', ['nodes' => $node['children']])</ul>
            </details>
        @else
            <a href="{{ $node['url'] }}" @class(['viar-menu-button', 'is-active' => $node['active']]) @if ($node['active']) aria-current="page" @endif
                x-on:click="window.matchMedia('(max-width: 1024px)').matches && $store.sidebar.close()">
                <x-filament::icon :icon="$node['icon']" /><span>{{ $node['label'] }}</span>
            </a>
        @endif
    </li>
@endforeach
