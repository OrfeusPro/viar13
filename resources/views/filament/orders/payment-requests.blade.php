<div style="display: grid; gap: 12px;">
    <p>Отдельные доплаты по уникальной ссылке для клиента.</p>
    @forelse($requests as $request)
        <article style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; overflow-wrap: anywhere;">
            <strong>{{ $request->public_number }}</strong> · {{ number_format((float) $request->amount, 2) }} {{ $request->currency }}
            <p>{{ $request->isPaid() ? 'Оплачено' : 'Ожидает оплаты' }}</p>
            <p>Метод: {{ [
                'online_paysera' => 'Онлайн банкинг', 'creditcart' => 'Картой онлайн', 'google_pay' => 'Google Pay',
                'apple_pay' => 'Apple Pay', 'paypalOnetimePayment' => 'PayPal', 'transfer' => 'Оплата перечислением',
            ][$request->selected_payment_method] ?? ($request->selected_payment_method ?: 'не выбран') }}</p>
            @if(filled($request->purpose))<p>Назначение: {{ $request->purpose }}</p>@endif
            <p>Создано: {{ $request->created_at?->format('d.m.Y H:i') ?: '—' }} · Оплачено: {{ $request->paid_at?->format('d.m.Y H:i') ?: '—' }}</p>
            @if(filled($request->customer_email))<p>Email: {{ $request->customer_email }}</p>@endif
            <div x-data="adminPaymentRequestCopy()" style="margin-top: 8px;">
                <input type="text" readonly value="{{ $request->publicUrl() }}" aria-label="Ссылка на оплату {{ $request->public_number }}" x-on:click.stop="$el.select()" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                <button type="button" x-on:click.stop="copy(@js($request->publicUrl()))" x-bind:disabled="copying" style="margin-top: 8px; color: #2563eb;">Копировать ссылку</button>
                <a href="{{ $request->publicUrl() }}" target="_blank" rel="noopener" style="margin-left: 12px; color: #2563eb;">Открыть</a>
                <span x-cloak x-show="copied" role="status">Ссылка скопирована</span>
                <span x-cloak x-show="manual" role="status">Скопируйте ссылку вручную из поля выше</span>
            </div>
        </article>
    @empty
        <p>Заявок на оплату нет.</p>
    @endforelse
</div>
