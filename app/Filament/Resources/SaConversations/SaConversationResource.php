<?php

namespace App\Filament\Resources\SaConversations;

use App\Filament\Resources\Orders\OrdersResource;
use App\Filament\Resources\SaConversations\Pages\ListSaConversations;
use App\Filament\Resources\SaConversations\Pages\ViewSaConversation;
use App\Models\SaConversation;
use App\Models\SaMessage;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class SaConversationResource extends Resource
{
    protected static ?string $model = SaConversation::class;
    protected static ?string $slug = 'sa-conversations';
    protected static ?string $navigationLabel = 'SA-диалоги';
    protected static ?string $pluralModelLabel = 'SA-диалоги';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function canViewAny(): bool
    {
        $user = auth('filament')->user();
        return $user && $user->hasPermission('browse_admin') && $user->hasPermission('browse_orders') && $user->hasPermission('read_orders')
            && Schema::hasTable('sa_conversations') && Schema::hasTable('sa_messages');
    }
    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool { return static::canViewAny(); }
    public static function canCreate(): bool { return false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool { return false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return false; }

    public static function getEloquentQuery(): Builder
    {
        \App\Services\Admin\SaInboxService::authorize(auth('filament')->user());
        return parent::getEloquentQuery()->select('sa_conversations.*')->addSelect([
            'last_text' => SaMessage::select('text')->whereColumn('conversation_id', 'sa_conversations.conversation_id')
                ->orderByRaw('COALESCE(sent_at, created_at) desc')->orderByDesc('id')->limit(1),
            'last_direction' => SaMessage::select('direction')->whereColumn('conversation_id', 'sa_conversations.conversation_id')
                ->orderByRaw('COALESCE(sent_at, created_at) desc')->orderByDesc('id')->limit(1),
            'messages_count' => SaMessage::selectRaw('count(*)')->whereColumn('conversation_id', 'sa_conversations.conversation_id'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->poll('10s')->defaultSort(fn (Builder $query) => $query
            ->orderByRaw('COALESCE(last_message_at, updated_at, created_at) desc')->orderByDesc('id'))
            ->recordUrl(fn ($record) => static::getUrl('view', ['record' => $record]))->columns([
                IconColumn::make('unread_for_manager')->label('Непрочитано')->boolean()
                    ->trueIcon('heroicon-o-envelope')->trueColor('warning')->falseIcon('heroicon-o-check')->falseColor('gray'),
                TextColumn::make('client_name')->label('Клиент')->placeholder('Без имени')->searchable()->wrap(),
                TextColumn::make('client_phone')->label('Телефон')->searchable(),
                TextColumn::make('conversation_id')->label('Диалог')->searchable()->wrap()->limit(45),
                TextColumn::make('channel')->label('Канал')->badge(),
                TextColumn::make('bot_mode')->label('Режим бота')->badge()->formatStateUsing(fn ($state) => match($state) {
                    'active' => 'Активен', 'paused' => 'Пауза', 'handoff_to_manager' => 'Менеджер', default => $state,
                }),
                TextColumn::make('orders_id')->label('Заказ')->searchable()->placeholder('Без заказа')
                    ->url(fn ($record) => $record->orders_id ? OrdersResource::getUrl('edit', ['record' => $record->orders_id]) : null),
                TextColumn::make('last_text')->label('Последнее сообщение')->wrap()->limit(100)
                    ->description(fn ($record) => $record->last_direction === 'inbound' ? 'Входящее' : ($record->last_direction ? 'Исходящее' : null)),
                TextColumn::make('messages_count')->label('Сообщений'),
                TextColumn::make('last_message_at')->label('Последняя активность')
                    ->getStateUsing(fn ($record) => $record->last_message_at ?? $record->updated_at ?? $record->created_at)
                    ->dateTime('d.m.Y H:i')->sortable(),
            ])->filters([
                SelectFilter::make('scope')->label('Показать')->options(['unread' => 'Непрочитанные', 'unlinked' => 'Без заказа',
                    'awaiting_reply' => 'Последнее сообщение входящее', 'recent' => 'За последние сутки'])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'unread' => $query->where('unread_for_manager', true),
                        'unlinked' => $query->whereNull('orders_id'),
                        'awaiting_reply' => $query->whereRaw("(select direction from sa_messages where sa_messages.conversation_id = sa_conversations.conversation_id order by COALESCE(sent_at, created_at) desc, id desc limit 1) = ?", ['inbound']),
                        'recent' => $query->whereRaw('COALESCE(last_message_at, updated_at, created_at) >= ?', [now()->subDay()]),
                        default => $query,
                    }),
                SelectFilter::make('channel')->label('Канал')->options(fn () => SaConversation::whereNotNull('channel')->distinct()->pluck('channel', 'channel')->all()),
                SelectFilter::make('bot_mode')->label('Режим бота')->options(['active' => 'Активен', 'paused' => 'Пауза', 'handoff_to_manager' => 'Менеджер']),
            ])->recordActions([Action::make('open')->label('Открыть')->icon('heroicon-o-chat-bubble-left-right')
                ->url(fn ($record) => static::getUrl('view', ['record' => $record]))]);
    }

    public static function getPages(): array
    {
        return ['index' => ListSaConversations::route('/'), 'view' => ViewSaConversation::route('/{record}')];
    }
}
