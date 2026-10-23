<span class="viar-order-heading">
    <span>{{ $title }}</span>
    @if ($totals !== null)
        <span class="viar-order-heading-totals">
            <span class="viar-order-heading-stat" aria-label="Заказов: {{ $totals['count'] }}" title="Количество заказов">
                <x-filament::icon icon="heroicon-o-shopping-bag" />
                <span>{{ $totals['count'] }}</span>
            </span>
            <span class="viar-order-heading-stat" aria-label="Сумма: {{ $totals['sum'] }} €" title="Сумма заказов">
                <x-filament::icon icon="heroicon-o-banknotes" />
                <span>{{ $totals['sum'] }} €</span>
            </span>
        </span>
    @endif
</span>
