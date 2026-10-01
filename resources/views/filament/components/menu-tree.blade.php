@php($tree = \App\Filament\Bread\BreadMenuTree::make($navigation))
<ul class="viar-menu-tree" x-bind:class="{ 'is-compact': ! $store.sidebar.isOpen }"
    x-effect="if ($store.sidebar.isOpen) $nextTick(() => $el.querySelector('[aria-current=page]')?.scrollIntoView({ block: 'nearest' }))">
    @foreach ($tree as $node)
        <li class="viar-menu-root" data-menu-id="{{ $node['id'] }}" x-data="{ expanded: @js($node['active']), flyout: false, top: 0, left: 0 }"
            x-on:keydown.escape.stop="flyout = false; $refs.trigger?.focus()" x-on:click.outside="if (! $refs.flyout?.contains($event.target)) flyout = false"
            x-effect="if ($store.sidebar.isOpen) flyout = false">
            @if ($node['children'])
                <button type="button" x-ref="trigger" @class(['viar-menu-button', 'is-active-parent' => $node['active']])
                    title="{{ $node['label'] }}" aria-label="{{ $node['label'] }}" aria-controls="{{ $node['id'] }}-children"
                    x-bind:aria-expanded="$store.sidebar.isOpen ? expanded : flyout"
                    x-on:click="if ($store.sidebar.isOpen) { expanded = !expanded } else { const r = $el.getBoundingClientRect(); top = Math.min(r.top, window.innerHeight - 380); left = r.right + 12; flyout = !flyout }">
                    <x-filament::icon :icon="$node['icon']" /><span x-show="$store.sidebar.isOpen">{{ $node['label'] }}</span>
                    <x-filament::icon icon="heroicon-o-chevron-down" class="viar-menu-chevron" x-show="$store.sidebar.isOpen" x-bind:class="{ 'is-expanded': expanded }" />
                </button>
                <ul id="{{ $node['id'] }}-children" class="viar-menu-children" x-show="$store.sidebar.isOpen && expanded" x-cloak>
                    @include('filament.components.menu-branch', ['nodes' => $node['children']])
                </ul>
                <template x-teleport="body">
                    <section class="viar-menu-flyout" x-ref="flyout" x-show="flyout && ! $store.sidebar.isOpen" x-trap="flyout && ! $store.sidebar.isOpen" x-cloak
                        x-bind:style="{ top: Math.max(76, top) + 'px', left: left + 'px', maxHeight: 'calc(100vh - ' + Math.max(76, top) + 'px - 16px)' }" aria-label="{{ $node['label'] }}"
                        x-on:keydown.escape.stop="flyout = false; $refs.trigger.focus()">
                        <div class="viar-menu-flyout-heading"><x-filament::icon :icon="$node['icon']" /><strong>{{ $node['label'] }}</strong></div>
                        <ul>@include('filament.components.menu-branch', ['nodes' => $node['children']])</ul>
                    </section>
                </template>
            @else
                <a href="{{ $node['url'] }}" title="{{ $node['label'] }}" aria-label="{{ $node['label'] }}"
                    @class(['viar-menu-button', 'is-active' => $node['active']]) @if ($node['active']) aria-current="page" @endif
                    x-on:click="window.matchMedia('(max-width: 1024px)').matches && $store.sidebar.close()">
                    <x-filament::icon :icon="$node['icon']" /><span x-show="$store.sidebar.isOpen">{{ $node['label'] }}</span>
                </a>
            @endif
        </li>
    @endforeach
</ul>
