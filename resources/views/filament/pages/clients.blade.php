<x-filament-panels::page class="viar-management-page">
    @include('filament.components.management-styles')
    <style>
        .viar-clients-filters { display: flex; flex-wrap: wrap; gap: 16px; align-items: end; width: 100%; }
        .viar-clients-filters > label { display: grid; gap: 6px; }
        .viar-clients-search { flex: 1 1 320px; max-width: 480px; min-width: 0; }
        @media (max-width: 640px) { .viar-clients-filters > label { width: 100%; max-width: none; flex-basis: auto; } }
    </style>
    @php($clients = $this->clients())
    <x-filament::section>
        <div class="viar-management-toolbar">
            <div class="viar-clients-filters">
                <label class="viar-clients-search">Поиск<x-filament::input.wrapper><x-filament::input wire:model.live.debounce.400ms="search" placeholder="Имя, email, телефон, адрес или ID" /></x-filament::input.wrapper></label>
                <label>Подписка<x-filament::input.wrapper><x-filament::input.select wire:model.live="subscription"><option value="">Все</option><option value="YES">Подписаны</option><option value="NO">Не подписаны</option></x-filament::input.select></x-filament::input.wrapper></label>
                <label>Страна<x-filament::input.wrapper><x-filament::input.select wire:model.live="country"><option value="">Все</option>@foreach($this->countries() as $countryOption)<option value="{{ $countryOption }}">{{ $countryOption }}</option>@endforeach</x-filament::input.select></x-filament::input.wrapper></label>
                <label>На странице<x-filament::input.wrapper><x-filament::input.select wire:model.live="perPage">@foreach([10,25,50,100] as $size)<option value="{{ $size }}">{{ $size }}</option>@endforeach</x-filament::input.select></x-filament::input.wrapper></label>
            </div>
        </div>
        <div class="viar-management-scroll" tabindex="0" role="region" aria-label="Список клиентов">
            <table class="viar-management-table">
                <thead><tr>
                    @foreach(['id'=>'ID','email'=>'Email','first_name'=>'Имя','last_name'=>'Фамилия'] as $field=>$label)
                        <th scope="col" aria-sort="{{ $sort === $field ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}"><button type="button" wire:click="sortBy('{{ $field }}')">{{ $label }} {{ $sort === $field ? ($direction === 'asc' ? '↑' : '↓') : '↕' }}</button></th>
                    @endforeach
                    <th scope="col">Телефон</th><th scope="col">Адрес</th><th scope="col">Почтовый индекс</th>
                    <th scope="col" aria-sort="{{ $sort === 'country' ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}"><button type="button" wire:click="sortBy('country')">Страна {{ $sort === 'country' ? ($direction === 'asc' ? '↑' : '↓') : '↕' }}</button></th>
                    <th scope="col">Подписка</th><th scope="col">Заказы / действия</th>
                </tr></thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr wire:key="client-{{ $client->id }}">
                            <td>@if(auth('filament')->user()->hasPermission('edit_users'))<a href="/filament/bread/users/{{ $client->id }}/edit">{{ $client->id }}</a>@else{{ $client->id }}@endif</td>
                            <td style="overflow-wrap:anywhere">{{ $client->email }}</td><td>{{ $client->first_name }}</td><td>{{ $client->last_name }}</td>
                            <td>{{ $client->phone ?: '—' }}</td><td style="overflow-wrap:anywhere">{{ $client->address ?: '—' }}</td><td>{{ $client->postal_index ?: '—' }}</td><td>{{ $client->country ?: '—' }}</td>
                            <td><span @class(['viar-management-badge','is-enabled'=>$client->news === 'YES'])>{{ $client->news === 'YES' ? 'Да' : ($client->news === 'NO' ? 'Нет' : '—') }}</span></td>
                            <td>
                                <div style="display:grid;gap:8px">
                                    @if($client->orders_count && auth('filament')->user()->hasPermission('browse_orders'))<a href="{{ $this->ordersUrl($client->id) }}">Список ({{ $client->orders_count }})</a>@elseif($client->orders_count){{ $client->orders_count }}@else<span class="viar-management-muted">Нет заказов</span>@endif
                                    @if(auth('filament')->user()->hasPermission('edit_users'))
                                        <x-filament::icon-button tag="a" href="/filament/bread/users/{{ $client->id }}/edit" icon="heroicon-o-pencil-square" label="Редактировать клиента #{{ $client->id }}" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty<tr><td colspan="10">Клиенты не найдены</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:24px"><x-filament::pagination :paginator="$clients" /></div>
    </x-filament::section>
</x-filament-panels::page>
