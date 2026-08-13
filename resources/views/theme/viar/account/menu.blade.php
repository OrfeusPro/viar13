@php
function isActiveRoute($routeName) {
    return Route::currentRouteName() == $routeName ? 'active' : '';
}
@endphp

<div class="cabinet-nav">
    <ul>
        <li class="{{ isActiveRoute('new_account.index') }}"><a href="{{ route('new_account.index') }}">@lang("account_new.menu.my_account")</a></li>
        <li class="{{ isActiveRoute('new_account.orders') }}"><a href="{{ route('new_account.orders') }}">@lang("account_new.menu.my_orders")</a></li>

        @if (Auth::user()->role->name == 'painter')
        {{-- <li class="{{ isActiveRoute('new_account.unpaid') }}"><a href="{{ route('new_account.unpaid') }}">@lang("account_new.menu.unpaid")</a></li> --}}
        <li class="{{ isActiveRoute('new_account.paid') }}"><a href="{{ route('new_account.paid') }}">@lang("account_new.menu.paid")</a></li>
        <li class="{{ isActiveRoute('new_account.orders_success') }}"><a href="{{ route('new_account.orders_success') }}">@lang("account_new.menu.orders_success")</a></li>
        @else
        <li class="{{ isActiveRoute('new_account.mystocks') }}"><a href="{{ route('new_account.mystocks') }}">@lang("account_new.menu.my_bonuses")</a></li>
        @endif
        <li class="{{ isActiveRoute('new_account.settings') }}"><a href="{{ route('new_account.settings') }}">@lang("account_new.menu.my_settings")</a></li>
    </ul>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>