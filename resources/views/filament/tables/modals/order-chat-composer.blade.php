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
<div class="adm-chat-composer adm-chat-composer--{{ $stream }}" data-chat-composer="{{ $stream }}">
    @if($stream === 'sa')
        <div class="adm-chat-bot-actions" aria-label="Команды бота">
            <x-filament::button class="adm-bot-resume" type="button" size="sm" color="success" wire:click="bot('resume_bot')" wire:confirm="Включить бота в этом диалоге?" wire:loading.attr="disabled" :disabled="$requiresReview">Resume — включить</x-filament::button>
            <x-filament::button class="adm-bot-pause" type="button" size="sm" color="warning" wire:click="bot('pause_bot')" wire:confirm="Приостановить бота в этом диалоге?" wire:loading.attr="disabled" :disabled="$requiresReview">Pause — пауза</x-filament::button>
            <x-filament::button class="adm-bot-handoff" type="button" size="sm" color="danger" wire:click="bot('handoff_to_manager')" wire:confirm="Передать этот диалог менеджеру?" wire:loading.attr="disabled" :disabled="$requiresReview">Handoff — менеджер</x-filament::button>
        </div>
    @endif
    <div class="adm-chat-response">
    <div class="adm-chat-response-row">
    <label for="{{ $fieldId }}" class="adm-chat-response-label">{{ $stream === 'client' ? 'Вы:' : $label }}</label>
    <textarea id="{{ $fieldId }}" aria-label="{{ $label }}" wire:model="text" rows="3" maxlength="10000" aria-describedby="{{ $fieldId }}-help {{ $fieldId }}-errors" aria-invalid="{{ $errors->has('text') ? 'true' : 'false' }}" placeholder="{{ $stream === 'painter' ? 'Комментарий' : 'Введите сообщение...' }}"></textarea>
    @if($stream === 'painter')
        <label class="adm-chat-thread">Тип переписки <select aria-label="Тип переписки художника"><option value="general">Общий</option></select></label>
    @endif
    <x-filament::button class="adm-chat-send" type="button" size="sm" wire:click="send" wire:loading.attr="disabled" :disabled="$requiresReview">{{ $stream === 'sa' ? 'Отправить в WhatsApp' : ($stream === 'client' ? 'Добавить комментарий' : 'Отправить сообщение') }}</x-filament::button>
    @if($stream === 'sa')
        <label class="adm-chat-handoff">
            <input type="checkbox" wire:model="handoff">
            Handoff после отправки (без отметки режим сохраняется)
        </label>
    @endif
    </div>
    <div id="{{ $fieldId }}-errors" role="alert" class="adm-chat-errors">
        @error('text') <p>{{ $message }}</p> @enderror
        @error('command') <p>{{ $message }}</p> @enderror
    </div>
    <div id="{{ $fieldId }}-help" class="adm-chat-help">
        @if($stream === 'sa')
            {{ config('admin_migration.sa_commands_enabled', false) ? 'Приём команды интеграцией ещё не означает доставку WhatsApp.' : 'UAT: внешние отправки отключены; клиентский кабинет и режим бота не меняются.' }}
            Тестовые ADM-FIL-UAT диалоги никогда не отправляются наружу.
        @else
            {{ $stream === 'client' ? 'Сообщение появится в кабинете клиента.' : 'Сообщение сохраняется в общем чате заказа; доступ художника зависит от назначения.' }}
            {{ config('admin_migration.'.($stream === 'client' ? 'client' : 'painter').'_chat_notifications_enabled', false) ? 'Email и CRM webhook включены.' : 'Email и CRM webhook отключены на период UAT.' }}
        @endif
    </div>
        <span wire:loading role="status" style="font-size: 12px;">Обрабатывается…</span>
    </div>
</div>
