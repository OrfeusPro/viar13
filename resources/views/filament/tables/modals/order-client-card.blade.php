<div style="display:grid; gap:8px; font-size:14px;">
    @if($user)
        <div><strong>ID:</strong> {{ $user->id }}</div>
        <div><strong>Имя:</strong> {{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: '—' }}</div>
        <div><strong>Email:</strong> {{ $user->email ?: '—' }}</div>
        <div><strong>Телефон:</strong> {{ $user->phone ?: '—' }}</div>
        <div><strong>Всего заказов:</strong> {{ $user->orders_count ?? $user->orders()->count() }}</div>
    @else
        <div>Клиент не найден.</div>
    @endif
</div>
