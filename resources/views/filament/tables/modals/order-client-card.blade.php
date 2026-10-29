<div style="display:grid; gap:8px; font-size:14px;">
    @if($user)
        <div><strong>ID:</strong> {{ $user->id }}</div>
        <div><strong>Имя:</strong> {{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: '—' }}</div>
        <div><strong>Email:</strong> {{ $user->email ?: '—' }}</div>
        <div><strong>Телефон:</strong> {{ $user->phone ?: '—' }}</div>
        <div><strong>Всего заказов:</strong> {{ $user->orders_count ?? $user->orders()->count() }}</div>
        @if($profileUrl ?? null)
            <a href="{{ $profileUrl }}" class="fi-link" style="color:#2563eb;text-decoration:underline;">Полная карточка клиента</a>
        @endif
    @else
        <div>Клиент не найден.</div>
    @endif
</div>
