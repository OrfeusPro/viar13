<div style="display:grid;gap:12px">
    <div><strong>Подписка на новости:</strong> {{ $client->news === 'YES' ? 'Да' : ($client->news === 'NO' ? 'Нет' : 'Не указана') }}</div>
    <div><strong>Пригласил друга?</strong> {{ $client->inv_sale_code === 'alredy_used' ? 'Да' : 'Нет' }}</div>
    @if (filter_var($client->screenshot, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($client->screenshot, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true))
        <div><a href="{{ $client->screenshot }}" target="_blank" rel="noopener noreferrer" style="color:#2563eb;text-decoration:underline">Открыть PrintScreen</a></div>
    @endif
</div>
