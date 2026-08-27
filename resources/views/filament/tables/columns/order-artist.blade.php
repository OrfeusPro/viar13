@php($record = $getRecord())

<div class="min-w-40 space-y-1.5 text-xs">
    <div class="font-semibold text-gray-950 dark:text-white">
        {{ $record->painterAssignment?->user?->email ?: 'Не назначен' }}
    </div>
    <div class="text-gray-500">
        Печатник: {{ $record->printingAssignment?->user?->email ?: 'не назначен' }}
    </div>
    @if(filled($record->painter_endtime))
        <div>Дедлайн: {{ date('d.m.Y', strtotime((string) $record->painter_endtime)) }}</div>
    @endif
    @if($record->painter_payed)
        <span class="inline-flex rounded bg-success-600 px-2 py-1 font-semibold text-white">Работа оплачена</span>
    @endif
</div>
