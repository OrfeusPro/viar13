<link rel="stylesheet" href="{{ asset('css/filament-order-chats.css') }}?v={{ filemtime(public_path('css/filament-order-chats.css')) }}">
<style>
    .viar-navigation-group > .fi-sidebar-group-btn::before {
        content: '';
        display: block;
        width: 1.5rem;
        height: 1.5rem;
        flex-shrink: 0;
        background-color: var(--gray-400);
        mask: var(--viar-group-icon) center / contain no-repeat;
        -webkit-mask: var(--viar-group-icon) center / contain no-repeat;
    }
</style>
