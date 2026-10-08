<x-filament-panels::page>
    @if (!$this->ready())
        <x-filament::section>Для подготовки рассылки нужно применить миграцию служебной таблицы admin_email_campaigns.</x-filament::section>
    @else
        <x-filament::section heading="Получатели и письмо">
            @if (!config('admin_migration.bulk_email_enabled'))
                <p style="margin-bottom:24px;color:#64748b">Тестовый режим: подтверждение сохранит результат без отправки писем.</p>
            @endif
            <form wire:submit="prepare">
                {{ $this->form }}
                <div style="margin-top:24px"><x-filament::button type="submit" icon="heroicon-o-eye" wire:loading.attr="disabled">Подготовить и показать превью</x-filament::button></div>
            </form>
        </x-filament::section>
        @if ($prepared)
            <x-filament::section heading="Подтверждение рассылки">
                <p>Получателей: <strong>{{ $prepared['count'] }}</strong>. Пример письма для {{ $prepared['sample'] }}.</p>
                <p style="margin:12px 0;color:#64748b">Внешние изображения в превью не загружаются.</p>
                <iframe title="Предпросмотр письма" sandbox="" referrerpolicy="no-referrer"
                    srcdoc="{{ '<meta http-equiv="Content-Security-Policy" content="default-src &#39;none&#39;; style-src &#39;unsafe-inline&#39;; img-src data:">'.$prepared['html'] }}"
                    style="width:100%;height:560px;border:1px solid #e2e8f0;border-radius:12px;background:white"></iframe>
                <div style="margin-top:24px">
                    <x-filament::button wire:click="confirm" wire:confirm="Подтвердить рассылку для {{ $prepared['count'] }} получателей?" wire:loading.attr="disabled" :disabled="$result !== null" icon="heroicon-o-paper-airplane">
                        {{ config('admin_migration.bulk_email_enabled') ? 'Подтвердить отправку' : 'Проверить без отправки' }}
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif
        @if ($result)
            <x-filament::section heading="Результат">
                @if ($result['status'] === 'suppressed')<p>Тестовый режим: отправка отключена.</p>
                @elseif ($result['status'] === 'queued')<p>Уведомления переданы в очередь. Доставка писем ещё не подтверждена.</p>
                @else<p>Обработка не завершена либо результат части операций неизвестен. Повторное подтверждение не отправит письма снова.</p>@endif
                <p style="margin-top:12px">В очереди: {{ $result['queued'] ?? 0 }} · Пропущено: {{ $result['skipped'] ?? 0 }} · Без отправки: {{ $result['suppressed'] ?? 0 }} · Неизвестный результат: {{ $result['uncertain'] ?? 0 }} · Не обработано: {{ $result['not_attempted'] ?? 0 }}</p>
            </x-filament::section>
        @endif
    @endif
</x-filament-panels::page>
