@php
    $label = match ($stream) {
        'sa' => 'Ответить клиенту в WhatsApp',
        'painter' => 'Общий ответ художнику',
        default => match ($threadType) {
            'sketch' => 'Ответ по наброску #'.$imageId,
            'painter' => 'Ответ по картине #'.$imageId,
            default => 'Ответ клиенту в общий чат',
        },
    };
    $fieldId = 'chat-text-'.$this->getId();
@endphp
<div style="margin-top: 14px; padding: 14px; border: 1px solid #dbe2ea; border-radius: 7px;" data-chat-composer="{{ $stream }}">
    @if($stream === 'sa')
        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;" aria-label="Команды бота">
            <x-filament::button type="button" size="sm" color="success" wire:click="bot('resume_bot')" wire:confirm="Включить бота в этом диалоге?" wire:loading.attr="disabled" :disabled="$requiresReview">Resume — включить</x-filament::button>
            <x-filament::button type="button" size="sm" color="warning" wire:click="bot('pause_bot')" wire:confirm="Приостановить бота в этом диалоге?" wire:loading.attr="disabled" :disabled="$requiresReview">Pause — пауза</x-filament::button>
            <x-filament::button type="button" size="sm" color="danger" wire:click="bot('handoff_to_manager')" wire:confirm="Передать этот диалог менеджеру?" wire:loading.attr="disabled" :disabled="$requiresReview">Handoff — менеджер</x-filament::button>
        </div>
    @endif
    <label for="{{ $fieldId }}" style="display: block; font-weight: 600; margin-bottom: 7px;">{{ $label }}</label>
    <textarea id="{{ $fieldId }}" wire:model="text" rows="3" maxlength="10000" aria-describedby="{{ $fieldId }}-help {{ $fieldId }}-errors" aria-invalid="{{ $errors->has('text') ? 'true' : 'false' }}" style="width: 100%; min-height: 90px; padding: 10px; border: 1px solid #94a3b8; border-radius: 6px; color: inherit; background: transparent;" placeholder="Введите сообщение..."></textarea>
    <div id="{{ $fieldId }}-errors" role="alert" style="color: #b91c1c; font-size: 13px;">
        @error('text') <p>{{ $message }}</p> @enderror
        @error('command') <p>{{ $message }}</p> @enderror
    </div>
    <div id="{{ $fieldId }}-help" style="font-size: 12px; color: #64748b; margin: 7px 0;">
        @if($stream === 'sa')
            {{ config('admin_migration.sa_commands_enabled', false) ? 'Приём команды интеграцией ещё не означает доставку WhatsApp.' : 'UAT: внешние отправки отключены; клиентский кабинет и режим бота не меняются.' }}
            Тестовые ADM-FIL-UAT диалоги никогда не отправляются наружу.
        @else
            {{ $stream === 'client' ? 'Сообщение появится в кабинете клиента.' : 'Сообщение сохраняется в общем чате заказа; доступ художника зависит от назначения.' }}
            {{ config('admin_migration.'.($stream === 'client' ? 'client' : 'painter').'_chat_notifications_enabled', false) ? 'Email и CRM webhook включены.' : 'Email и CRM webhook отключены на период UAT.' }}
        @endif
    </div>
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px;">
        @if($stream === 'sa')
            <label style="font-size: 13px; display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" wire:model="handoff">
                Handoff после отправки (без отметки режим сохраняется)
            </label>
        @endif
        <x-filament::button type="button" size="sm" wire:click="send" wire:loading.attr="disabled" :disabled="$requiresReview">{{ $stream === 'sa' ? 'Отправить в WhatsApp' : 'Отправить сообщение' }}</x-filament::button>
        <span wire:loading role="status" style="font-size: 12px;">Обрабатывается…</span>
    </div>
</div>
