<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Schemas\OrderPaymentRequestsForm;
use App\Filament\Resources\Orders\Schemas\OrderVenipakActions;
use App\Http\Controllers\IndexController;
use App\Models\AOrderFrom;
use App\Models\Orders;
use App\Models\UserType;
use App\Services\Admin\OrderAdminChatService;
use App\Services\Admin\OrderInvoiceService;
use App\Services\Admin\OrderPaymentService;
use App\Services\Admin\OrderRecipientEmailService;
use App\Services\Admin\OrderReviewRequestService;
use App\Services\Admin\OrderUserService;
use App\Services\Admin\UpdateOrderVrNumberService;
use App\Services\Payment\OrderPaymentRequestService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Заказы не найдены')
            ->extraAttributes(['class' => 'viar-orders-table'])
            ->recordUrl(null)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with([
                    'user' => fn ($query) => $query->withCount('orders'),
                    'manager',
                    'vrNumber',
                    'adminChats.user',
                    'order_user_comments' => fn ($query) => $query->oldest('created_at')->oldest('id'),
                    'order_painter_comments' => fn ($query) => $query->latest('created_at')->latest('id'),
                    'orders_chats' => fn ($query) => $query->oldest('id'),
                    'saMessages' => fn ($query) => $query->oldest('id'),
                    'saConversations',
                    'order_payment_requests' => fn ($query) => $query->latest('id')->limit(3),
                    'painterAssignment.user',
                    'printingAssignment.user',
                    'order_painter_images.statusDefinition',
                ])
                ->withCount([
                    'order_user_comments as client_messages_count',
                    'order_user_comments as unread_client_messages_count' => fn ($query) => $query
                        ->where('is_admin', 0)->where('admin_is_read', 0),
                    'adminChats as admin_messages_count',
                    'orders_chats as painter_messages_count',
                    'orders_chats as unread_painter_messages_count' => fn ($query) => $query
                        ->where('is_admin', 0)->where('admin_is_read', 0),
                    'saConversations as unread_sa_conversations_count' => fn ($query) => $query
                        ->where('unread_for_manager', 1),
                ]))
            ->paginationPageOptions(fn ($livewire): array => [
                $livewire instanceof ListOrders && $livewire->hasActiveOrderFilters() ? 10 : 13,
                25, 50, 100,
            ])
            ->defaultPaginationPageOption(13)
            ->columns([
                ViewColumn::make('number_controls')
                    ->label('Номер')
                    ->searchable(['id'])
                    ->sortable(['id'])
                    ->view('filament.tables.columns.order-number'),
                ViewColumn::make('payment_controls')
                    ->label('Оплата')
                    ->view('filament.tables.columns.order-payment'),
                TextColumn::make('id')
                    ->label('Номер')
                    ->description(function ($record): string {
                        $vr = $record->vrNumber;
                        $vrNumber = match (true) {
                            filled($vr?->vrv_1) => 'VR00'.$vr->vrv_1,
                            filled($vr?->vrv_2) => 'BAW'.$vr->vrv_2,
                            filled($vr?->vrv_3) => 'VRR445'.str_pad((string) $vr->vrv_3, 3, '0', STR_PAD_LEFT),
                            filled($vr?->vrv_4) => 'DS020'.$vr->vrv_4,
                            default => null,
                        };
                        $request = $record->order_payment_requests->first()?->public_number;

                        return collect([$vrNumber, $request])->filter()->implode(' · ') ?: 'Без накладной';
                    })
                    ->action(
                        Action::make('manageVrNumber')
                            ->label('Номер, счёт и платежи')
                            ->modalHeading(fn ($record): string => 'Номер, счёт и платежи заказа №'.$record->id)
                            ->modalSubmitActionLabel('Выполнить')
                            ->schema([
                                Select::make('operation')
                                    ->label('Действие')
                                    ->options([
                                        'vr' => 'Обновить или удалить накладную',
                                        'payment_request' => 'Создать заявку на оплату',
                                    ])
                                    ->default('vr')
                                    ->live()
                                    ->required(),
                                TextInput::make('number')
                                    ->label('Номер накладной')
                                    ->placeholder('VR00…, BAW…, VRR445… или DS020…')
                                    ->helperText('Оставьте пустым, чтобы удалить номер.')
                                    ->visible(fn ($get): bool => $get('operation') === 'vr'),
                                TextInput::make('amount')
                                    ->label('Сумма, EUR')
                                    ->numeric()
                                    ->minValue(0.01)
                                    ->maxValue(999999.99)
                                    ->required(fn ($get): bool => $get('operation') === 'payment_request')
                                    ->visible(fn ($get): bool => $get('operation') === 'payment_request'),
                                TextInput::make('purpose')
                                    ->label('Назначение платежа')
                                    ->maxLength(255)
                                    ->visible(fn ($get): bool => $get('operation') === 'payment_request'),
                                Placeholder::make('invoice')
                                    ->label('Счёт')
                                    ->content(fn ($record): HtmlString => self::invoiceSummary($record)),
                                Placeholder::make('company')
                                    ->label('Данные юридического лица / фирмы')
                                    ->content(fn ($record): string => self::companySummary($record)),
                                Placeholder::make('payment_requests')
                                    ->label('Последние заявки на оплату')
                                    ->content(fn ($record): HtmlString => self::paymentRequestsSummary($record)),
                            ])
                            ->fillForm(function ($record, array $arguments): array {
                                $vr = $record->vrNumber;

                                return [
                                    'operation' => $arguments['operation'] ?? 'vr',
                                    'number' => match (true) {
                                        filled($vr?->vrv_1) => 'VR00'.$vr->vrv_1,
                                        filled($vr?->vrv_2) => 'BAW'.$vr->vrv_2,
                                        filled($vr?->vrv_3) => 'VRR445'.str_pad((string) $vr->vrv_3, 3, '0', STR_PAD_LEFT),
                                        filled($vr?->vrv_4) => 'DS020'.$vr->vrv_4,
                                        default => null,
                                    },
                                    'amount' => number_format((float) ($record->sale_price ?: $record->price), 2, '.', ''),
                                ];
                            })
                            ->action(function ($record, array $data): void {
                                if ($data['operation'] === 'payment_request') {
                                    app(OrderPaymentRequestService::class)->createForOrder($record, [
                                        'amount' => $data['amount'],
                                        'purpose' => $data['purpose'] ?? null,
                                    ], auth('filament')->user());
                                    $record->unsetRelation('order_payment_requests');

                                    Notification::make()->success()->title('Заявка на оплату создана')->send();

                                    return;
                                }

                                app(UpdateOrderVrNumberService::class)->update($record, $data['number'] ?? null);
                                $record->unsetRelation('vrNumber');

                                Notification::make()->success()->title('Номер накладной обновлён')->send();
                            })
                            ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false),
                    )
                    ->searchable()
                    ->sortable()
                    ->visible(false),
                TextColumn::make('payment_request_action')
                    ->action(
                        Action::make('createPaymentRequest')
                            ->modalHeading(fn ($record): string => 'Создать платёж для заказа №'.$record->id)
                            ->modalSubmitActionLabel('Создать платёж')
                            ->schema([
                                TextInput::make('amount')
                                    ->label('Сумма, EUR')
                                    ->numeric()
                                    ->minValue(0.01)
                                    ->maxValue(999999.99)
                                    ->required(),
                                TextInput::make('purpose')
                                    ->label('Назначение платежа')
                                    ->maxLength(255),
                            ])
                            ->fillForm(fn ($record): array => [
                                'amount' => number_format((float) ($record->sale_price ?: $record->price), 2, '.', ''),
                            ])
                            ->action(function ($record, array $data): void {
                                app(OrderPaymentRequestService::class)->createForOrder($record, $data, auth('filament')->user());
                                $record->unsetRelation('order_payment_requests');

                                Notification::make()->success()->title('Заявка на оплату создана')->send();
                            })
                            ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false),
                    )
                    ->visible(false),
                TextColumn::make('vr_number')
                    ->label('VR / запрос оплаты')
                    ->state(function ($record): string {
                        $vr = $record->vrNumber;
                        $number = match (true) {
                            filled($vr?->vrv_1) => 'VR00'.$vr->vrv_1,
                            filled($vr?->vrv_2) => 'BAW'.$vr->vrv_2,
                            filled($vr?->vrv_3) => 'VRR445'.str_pad((string) $vr->vrv_3, 3, '0', STR_PAD_LEFT),
                            filled($vr?->vrv_4) => 'DS020'.$vr->vrv_4,
                            default => '—',
                        };
                        $paymentRequest = $record->order_payment_requests->first()?->public_number;

                        return $paymentRequest ? "{$number} · {$paymentRequest}" : $number;
                    })
                    ->wrap()
                    ->visible(fn (): bool => auth('filament')->user()?->role?->name !== 'printing')
                    ->toggleable(isToggledHiddenByDefault: true),
                ViewColumn::make('user_controls')
                    ->label('Пользователь')
                    ->view('filament.tables.columns.order-user'),
                TextColumn::make('manager.email')
                    ->label('Менеджер')
                    ->placeholder('Не назначен')
                    ->searchable()
                    ->visible(fn (): bool => auth('filament')->user()?->role?->name !== 'printing')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('country')
                    ->label('Страна')
                    ->badge()
                    ->visible(false),
                ViewColumn::make('recipient_controls')
                    ->label('Получатель')
                    ->view('filament.tables.columns.order-recipient'),
                ViewColumn::make('product_controls')
                    ->label('Товар')
                    ->view('filament.tables.columns.order-product'),
                ViewColumn::make('comments_controls')
                    ->label('Комментарии')
                    ->view('filament.tables.columns.order-comments'),
                ViewColumn::make('artist_controls')
                    ->label('Художник')
                    ->view('filament.tables.columns.order-artist'),
                TextColumn::make('delivery_method')
                    ->label('Доставка')
                    ->state(function ($record): string {
                        $delivery = self::decodeJson($record->delivery);

                        return match ($delivery['sposob'] ?? null) {
                            'to_the_door' => 'До двери',
                            'pickup_at_viar_workshop' => 'Самовывоз Viar',
                            'pickup' => 'Самовывоз',
                            'parcel_terminal', 'post_machine' => 'Постамат',
                            default => (string) ($delivery['sposob'] ?? '—'),
                        };
                    })
                    ->description(fn ($record): ?string => filled(data_get(self::decodeJson($record->delivery), 'city'))
                        ? (string) data_get(self::decodeJson($record->delivery), 'city')
                        : null)
                    ->wrap()
                    ->visible(false),
                TextColumn::make('delivery_date')
                    ->label('Доставить')
                    ->state(function ($record): string {
                        $delivery = json_decode((string) $record->delivery, true) ?: [];
                        $date = $delivery['when_send'] ?? null;

                        return $date ? (string) $date : '—';
                    })
                    ->color(function ($record): string {
                        $delivery = json_decode((string) $record->delivery, true) ?: [];
                        $date = isset($delivery['when_send']) ? strtotime((string) $delivery['when_send']) : false;
                        if (! $date) {
                            return 'gray';
                        }

                        $days = (int) floor(($date - today()->timestamp) / 86400);

                        return $days < 3 ? 'danger' : ($days < 8 ? 'warning' : 'success');
                    })
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('a_order_from')
                    ->label('Канал')
                    ->formatStateUsing(fn ($state): string => AOrderFrom::query()->whereKey($state)->value('title') ?? (string) $state)
                    ->placeholder('—')
                    ->visible(fn (): bool => auth('filament')->user()?->role?->name !== 'printing')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('catid')
                    ->label('Категория')
                    ->formatStateUsing(fn ($state): string => filled($state) && (int) $state !== 0 ? '#'.$state : 'нет')
                    ->color(fn ($state): string => filled($state) && (int) $state !== 0 ? 'gray' : 'danger')
                    ->visible(fn (): bool => auth('filament')->user()?->role?->name !== 'printing')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('painterAssignment.user.email')
                    ->label('Художник')
                    ->placeholder('Не назначен')
                    ->description(function ($record): string {
                        $printing = $record->printingAssignment?->user?->email;

                        return collect([
                            $printing ? 'Печатник: '.$printing : 'Печатник не назначен',
                            filled($record->painter_endtime) ? 'Дедлайн: '.date('d.m.Y', strtotime((string) $record->painter_endtime)) : null,
                            $record->painter_payed ? 'Работа оплачена' : null,
                        ])->filter()->implode(' · ');
                    })
                    ->visible(false),
                TextColumn::make('printingAssignment.user.email')
                    ->label('Печатник')
                    ->placeholder('Не назначен')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('chats')
                    ->label('Комментарии')
                    ->state(fn ($record): string => implode(' · ', [
                        'К:'.$record->client_messages_count.($record->unread_client_messages_count ? '/'.$record->unread_client_messages_count.'!' : ''),
                        'А:'.$record->admin_messages_count,
                        'Х:'.$record->painter_messages_count,
                        'SA:'.$record->unread_sa_conversations_count,
                    ]))
                    ->description(function ($record): string {
                        $deliveryComment = data_get(self::decodeJson($record->delivery), 'comment');

                        return collect([$deliveryComment, $record->comment, $record->admin_comment, $record->painter_comment])
                            ->filter()->take(2)->implode(' · ');
                    })
                    ->badge()
                    ->color(fn ($record): string => ($record->unread_client_messages_count || $record->unread_sa_conversations_count) ? 'danger' : 'gray')
                    ->visible(false),
                TextColumn::make('product_summary')
                    ->label('Товар')
                    ->state(function ($record): string {
                        $items = self::decodeJson($record->items);
                        $products = collect($items)->filter(fn ($item, $key): bool => is_int($key) && is_array($item));

                        if ($products->isEmpty()) {
                            return '—';
                        }

                        return $products->take(2)->map(function (array $item): string {
                            $name = $item['name'] ?? $item['type'] ?? 'Товар';
                            $size = $item['size_name'] ?? data_get($item, 'show.size') ?? null;
                            $count = (int) ($item['count'] ?? 1);

                            return trim($name.($size ? " · {$size}" : '').($count > 1 ? " ×{$count}" : ''));
                        })->implode("\n").($products->count() > 2 ? "\n+".($products->count() - 2) : '');
                    })
                    ->wrap()
                    ->visible(false),
                ViewColumn::make('lifecycle_controls')
                    ->label('Заказ')
                    ->view('filament.tables.columns.order-lifecycle'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn (string $state): string => self::statusLabels()[$state] ?? $state)
                    ->sortable()
                    ->visible(false),
                TextColumn::make('payment_status')
                    ->label('Оплата')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::paymentLabels()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'payed' => 'success',
                        'prepayment' => 'warning',
                        default => 'danger',
                    })
                    ->description(function ($record): string {
                        $method = match ((string) $record->payment) {
                            'transfer' => 'Перевод',
                            'paypalOnetimePayment' => 'PayPal',
                            'paysera' => 'Paysera',
                            'cash' => 'Наличные',
                            default => (string) ($record->payment ?: 'Способ не указан'),
                        };

                        $parts = [$method, 'Заказ: '.number_format((float) $record->price, 2).' €'];
                        if (filled($record->sale_price) && (float) $record->sale_price !== (float) $record->price) {
                            $parts[] = 'Итого: '.number_format((float) $record->sale_price, 2).' €';
                        }
                        if ($record->payment_status === 'prepayment' && filled($record->prepayment_price)) {
                            $parts[] = 'Предоплата: '.number_format((float) $record->prepayment_price, 2).' €';
                        }
                        if (filled(trim((string) $record->labels, ','))) {
                            $parts[] = 'Этикетки: '.trim((string) $record->labels, ',');
                        }

                        return implode(' · ', $parts);
                    })
                    ->sortable()
                    ->visible(false),
                TextColumn::make('sale_price')
                    ->label('Сумма')
                    ->formatStateUsing(fn ($state, $record): string => number_format((float) ($state ?: $record->price), 2).' €')
                    ->summarize(
                        Sum::make()
                            ->label('Сумма заказов')
                            ->using(fn ($query) => $query->sum('price'))
                            ->money('EUR'),
                    )
                    ->sortable()
                    ->visible(fn (): bool => auth('filament')->user()?->role?->name !== 'printing')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_admin_order')
                    ->label('Админ-заказ')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('labels')
                    ->label('Этикетки')
                    ->formatStateUsing(fn ($state): string => filled(trim((string) $state, ',')) ? trim((string) $state, ',') : '—')
                    ->wrap()
                    ->visible(false),
                TextColumn::make('comments_summary')
                    ->label('Комментарии')
                    ->state(function ($record): string {
                        $deliveryComment = data_get(self::decodeJson($record->delivery), 'comment');

                        return collect([
                            filled($deliveryComment) ? 'Доставка: '.$deliveryComment : null,
                            filled($record->comment) ? 'Заказ: '.$record->comment : null,
                            filled($record->admin_comment) ? 'Админ: '.$record->admin_comment : null,
                            filled($record->painter_comment) ? 'Художник: '.$record->painter_comment : null,
                        ])->filter()->implode("\n") ?: '—';
                    })
                    ->wrap()
                    ->limit(140)
                    ->tooltip(fn ($record): string => collect([
                        data_get(self::decodeJson($record->delivery), 'comment'),
                        $record->comment,
                        $record->admin_comment,
                        $record->painter_comment,
                    ])->filter()->implode("\n"))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('painter_endtime')
                    ->label('Дедлайн художника')
                    ->date('d.m.Y')
                    ->placeholder('—')
                    ->color(fn ($state): string => filled($state) && strtotime((string) $state) <= today()->addDay()->timestamp ? 'danger' : 'gray')
                    ->visible(fn (): bool => auth('filament')->user()?->role?->name !== 'printing')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('order_id')
                    ->label('ID заказа')
                    ->schema([TextInput::make('value')->label('ID заказа')->numeric()])
                    ->indicateUsing(fn (array $data): array => filled($data['value'] ?? null)
                        ? [Indicator::make('Заказ: '.$data['value'])->removeField('value')] : [])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => $query->whereKey((int) $data['value']),
                    )),
                OrderSearchFilters::paymentRequest(),
                OrderSearchFilters::customer(),
                OrderSearchFilters::phone(),
                OrderSearchFilters::price(),
                SelectFilter::make('payment_status')
                    ->label('Оплата')
                    ->options(self::paymentLabels()),
                SelectFilter::make('a_order_from')
                    ->label('Канал продаж')
                    ->options(fn (): array => AOrderFrom::query()->orderBy('sortorder')->pluck('title', 'id')->all())
                    ->searchable(),
                SelectFilter::make('catid')
                    ->label('Категория')
                    ->options(fn (): array => self::categoryOptions())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('country')
                    ->label('Страна')
                    ->options(fn (): array => OrderSearchFilters::countryOptions())
                    ->searchable(),
                SelectFilter::make('painter')
                    ->label('Художник')
                    ->options(fn (): array => OrderSearchFilters::painterOptions())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => $query->whereHas('painterAssignment', fn (Builder $query): Builder => $query
                            ->where('user_id', $data['value'])),
                    ))
                    ->searchable()
                    ->preload(),
                OrderSearchFilters::manager(),
                OrderSearchFilters::size(),
                OrderSearchFilters::createdAt(),
                Filter::make('user_id')
                    ->label('ID клиента')
                    ->schema([TextInput::make('value')->label('ID клиента')->numeric()])
                    ->indicateUsing(fn (array $data): array => filled($data['value'] ?? null)
                        ? [Indicator::make('ID клиента: '.$data['value'])->removeField('value')] : [])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => $query->where('user_id', (int) $data['value']),
                    )),
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(self::statusLabels()),
                TernaryFilter::make('is_admin_order')
                    ->label('Административный заказ'),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(['default' => 1, 'sm' => 2, 'lg' => 4, 'xl' => 8, '2xl' => 10])
            ->recordActions([
                OrderInlineActions::update()->extraAttributes(['class' => 'hidden']),
                OrderInlineActions::updateFirm()->extraAttributes(['class' => 'hidden']),
                Action::make('createVrNumber')
                    ->label('Создать накладную')
                    ->modalHeading(fn ($record): string => 'Создать накладную заказа №'.$record->id)
                    ->schema([
                        Select::make('prefix')->label('Фирма')->options(fn (): array => UpdateOrderVrNumberService::prefixOptions())->required(),
                    ])
                    ->fillForm(fn (array $arguments): array => ['prefix' => $arguments['prefix'] ?? null])
                    ->action(function ($record, array $data): void {
                        abort_unless(auth('filament')->user()?->can('update', $record), 403);
                        app(UpdateOrderVrNumberService::class)->generate($record, $data['prefix']);
                        $record->unsetRelation('vrNumber');
                        Notification::make()->success()->title('Накладная создана')->send();
                    })
                    ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
                    ->extraAttributes(['class' => 'hidden']),

                Action::make('manageVrNumber')
                    ->label('Накладная')
                    ->modalHeading(fn ($record): string => 'Накладная заказа №'.$record->id)
                    ->schema([
                        TextInput::make('number')
                            ->label('Номер накладной')
                            ->placeholder('VR00…, BAW…, VRR445… или DS020…')
                            ->helperText('Оставьте пустым, чтобы удалить номер.'),
                    ])
                    ->fillForm(fn ($record, array $arguments): array => [
                        'number' => ($arguments['delete'] ?? false)
                            ? null
                            : (self::vrNumber($record) ?: ($arguments['prefix'] ?? null)),
                    ])
                    ->action(function ($record, array $data): void {
                        app(UpdateOrderVrNumberService::class)->update($record, $data['number'] ?? null);
                        $record->unsetRelation('vrNumber');
                        Notification::make()->success()->title('Номер накладной обновлён')->send();
                    })
                    ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
                    ->extraAttributes(['class' => 'hidden']),
                OrderPaymentRequestsForm::createAction()->extraAttributes(['class' => 'hidden']),
                Action::make('updatePaymentStatus')
                    ->label('Статус оплаты')
                    ->modalHeading(fn ($record): string => 'Статус оплаты заказа №'.$record->id)
                    ->modalSubmitActionLabel('Обновить статус')
                    ->schema([
                        Select::make('payment_status')
                            ->label('Статус оплаты')
                            ->options(self::paymentLabels())
                            ->required(),
                    ])
                    ->fillForm(fn ($record, array $arguments): array => [
                        'payment_status' => $arguments['payment_status'] ?? $record->payment_status,
                    ])
                    ->action(function ($record, array $data): void {
                        $result = app(OrderPaymentService::class)->updateStatus($record, $data['payment_status']);
                        $notification = Notification::make()->success()->title('Статус оплаты обновлён');
                        if ($result['notifications_suppressed']) {
                            $notification->body('Внешние письма и CRM webhook отключены на период UAT; письмо записано в локальный лог.');
                        }
                        $notification->send();
                    })
                    ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
                    ->extraAttributes(['class' => 'hidden']),
                Action::make('updatePrepayment')
                    ->label('Сумма предоплаты')
                    ->modalHeading(fn ($record): string => 'Предоплата заказа №'.$record->id)
                    ->modalSubmitActionLabel('Обновить')
                    ->schema([
                        TextInput::make('prepayment_price')
                            ->label('Сумма предоплаты, EUR')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(999999.99)
                            ->required(),
                    ])
                    ->fillForm(fn ($record): array => [
                        'prepayment_price' => number_format((float) $record->prepayment_price, 2, '.', ''),
                    ])
                    ->action(function ($record, array $data): void {
                        app(OrderPaymentService::class)->updatePrepayment($record, $data['prepayment_price']);
                        Notification::make()->success()->title('Сумма предоплаты обновлена')->send();
                    })
                    ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
                    ->extraAttributes(['class' => 'hidden']),
                OrderVenipakActions::create()->extraAttributes(['class' => 'hidden']),
                OrderVenipakActions::print()->extraAttributes(['class' => 'hidden']),
                Action::make('updateUserPdfLocale')
                    ->label('Язык счёта')
                    ->modalHeading(fn ($record): string => 'Язык счёта пользователя заказа №'.$record->id)
                    ->modalSubmitActionLabel('Обновить язык')
                    ->schema([
                        Select::make('pdf_locale')
                            ->label('Язык счёта')
                            ->options(self::localeOptions())
                            ->required(),
                    ])
                    ->fillForm(fn ($record, array $arguments): array => [
                        'pdf_locale' => $arguments['pdf_locale']
                            ?? $record->user?->pdf_locale
                            ?? $record->user?->preferredLocale(),
                    ])
                    ->action(function ($record, array $data): void {
                        app(OrderUserService::class)->updatePdfLocale($record, $data['pdf_locale']);
                        $record->unsetRelation('user');
                        Notification::make()->success()->title('Язык счёта обновлён')->send();
                    })
                    ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
                    ->extraAttributes(['class' => 'hidden']),
                Action::make('updateClientStatus')
                    ->label('Статус клиента')
                    ->modalHeading(fn ($record): string => 'Статус клиента заказа №'.$record->id)
                    ->modalSubmitActionLabel('Обновить статус')
                    ->schema([
                        Select::make('client_status')
                            ->label('Статус клиента')
                            ->options(self::clientStatusOptions())
                            ->required(),
                    ])
                    ->fillForm(fn ($record, array $arguments): array => [
                        'client_status' => $arguments['client_status'] ?? $record->user?->client_status,
                    ])
                    ->action(function ($record, array $data): void {
                        app(OrderUserService::class)->updateClientStatus($record, $data['client_status']);
                        $record->unsetRelation('user');
                        Notification::make()->success()->title('Статус клиента обновлён')->send();
                    })
                    ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
                    ->extraAttributes(['class' => 'hidden']),
                Action::make('composeRecipientEmail')
                    ->label('Написать email')
                    ->modalHeading(fn ($record): string => 'Email пользователю заказа №'.$record->id)
                    ->modalDescription(fn ($record): string => (string) ($record->user?->email ?: 'Email пользователя отсутствует'))
                    ->modalSubmitActionLabel('Отправить')
                    ->schema([
                        TextInput::make('subject')->label('Тема')->required()->maxLength(255),
                        TextInput::make('greetings')->label('Поздравление')->required()->maxLength(255),
                        Textarea::make('line')->label('Основной текст')->required()->rows(8)->maxLength(10000),
                        TextInput::make('salutation')->label('Прощание')->required()->maxLength(255),
                    ])
                    ->fillForm([
                        'subject' => 'Скидки!',
                        'greetings' => 'Привет от viarcanvas',
                        'salutation' => 'Спасибо, что пользуетесь нашим ресурсом!',
                    ])
                    ->action(function ($record, array $data): void {
                        $result = app(OrderRecipientEmailService::class)->send($record, $data);
                        $notification = Notification::make()->success()->title(
                            $result['suppressed'] ? 'Письмо проверено; внешняя почта отключена' : 'Письмо поставлено в очередь',
                        );
                        if ($result['suppressed']) {
                            $notification->body('Во время UAT письмо клиенту не отправляется.');
                        }
                        $notification->send();
                    })
                    ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
                    ->extraAttributes(['class' => 'hidden']),
                Action::make('requestReview')
                    ->label('Запрос отзыва')
                    ->modalHeading(fn ($record): string => 'Запрос отзыва по заказу №'.$record->id)
                    ->modalDescription(fn ($record): string => 'Письмо будет адресовано только пользователю заказа: '.($record->user?->email ?: 'email отсутствует').'.')
                    ->modalSubmitActionLabel('Отправить запрос')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $result = app(OrderReviewRequestService::class)->send($record);
                        $notification = Notification::make()->success()->title(
                            $result['suppressed'] ? 'Запрос проверен; внешняя почта отключена' : 'Запрос отзыва отправлен',
                        );
                        if ($result['suppressed']) {
                            $notification->body('Во время UAT письмо клиенту не отправляется.');
                        }
                        $notification->send();
                    })
                    ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
                    ->extraAttributes(['class' => 'hidden']),
                OrderClientChatActions::history(),
                OrderClientChatActions::reply(),
                OrderClientChatActions::read(),
                Action::make('manageAdminChat')
                    ->label('Чат для администраторов')
                    ->modalHeading(fn ($record): string => 'Внутренний чат · заказ №'.$record->id)
                    ->modalWidth('4xl')
                    ->extraModalWindowAttributes(['class' => 'adm-chat-modal adm-chat-modal--admin'])
                    ->modalContent(fn ($record) => view('filament.tables.modals.order-chat-history', ['record' => $record, 'stream' => 'admin']))
                    ->modalSubmitActionLabel('Отправить')
                    ->modalCancelAction(false)
                    ->schema([
                        Textarea::make('comment')
                            ->label('Новое внутреннее сообщение')
                            ->placeholder('Комментарий')
                            ->required()
                            ->rows(4)
                            ->maxLength(10000),
                    ])
                    ->action(function ($record, array $data): void {
                        app(OrderAdminChatService::class)->add($record, auth('filament')->user(), $data['comment']);
                        $record->unsetRelation('adminChats');
                        Notification::make()->success()->title('Внутреннее сообщение добавлено')->send();
                    })
                    ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
                    ->extraAttributes(['class' => 'hidden']),
                OrderSaChatActions::history(),
                OrderSaChatActions::read(),
                OrderSaChatActions::reply(),
                OrderSaChatActions::bot(),
                OrderPainterChatActions::history(),
                OrderPainterChatActions::reply(),
                OrderPainterChatActions::read(),
                OrderArtistActions::manage()->extraAttributes(['class' => 'hidden']),
                OrderArtistActions::updateField()->extraAttributes(['class' => 'hidden']),
                OrderArtistActions::updateMediaStatus()->extraAttributes(['class' => 'hidden']),
                OrderArtistActions::paintingStatuses()->extraAttributes(['class' => 'hidden']),
                OrderLifecycleActions::updateStatus()->extraAttributes(['class' => 'hidden']),
                OrderLifecycleActions::updateDeliveryDate()->extraAttributes(['class' => 'hidden']),
                OrderLifecycleActions::viewClient()->extraAttributes(['class' => 'hidden']),
                OrderLifecycleActions::delete()->extraAttributes(['class' => 'hidden']),
                Action::make('editInvoiceFirm')
                    ->label('Данные фирмы')
                    ->modalHeading(fn ($record): string => 'Данные фирмы для счёта №'.$record->id)
                    ->modalSubmitActionLabel('Сохранить и обновить счёт')
                    ->schema([
                        TextInput::make('name')->label('Фирма')->required()->maxLength(255),
                        TextInput::make('reg_num')->label('Рег. номер')->maxLength(255),
                        TextInput::make('addr')->label('Адрес')->required()->maxLength(500),
                        TextInput::make('bank')->label('Банк')->maxLength(255),
                        TextInput::make('vat_num')->label('VAT номер')->maxLength(255),
                        TextInput::make('bank_code')->label('Код банка')->maxLength(255),
                        TextInput::make('office_addr')->label('Адрес офиса')->maxLength(500),
                        TextInput::make('acc_num')->label('Номер счёта')->maxLength(255),
                    ])
                    ->fillForm(fn ($record): array => app(OrderInvoiceService::class)->defaultFirmData($record))
                    ->action(function ($record, array $data): void {
                        app(OrderInvoiceService::class)->generate($record, $data);
                        Notification::make()->success()->title('Данные фирмы сохранены в PDF-счёте')->send();
                    })
                    ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
                    ->extraAttributes(['class' => 'hidden']),
                Action::make('generateInvoice')
                    ->label('Сформировать счёт')
                    ->modalHeading(fn ($record): string => ($record->has_pdf ? 'Обновить' : 'Сформировать').' счёт заказа №'.$record->id)
                    ->modalDescription('PDF будет сформирован заново из текущих данных заказа.')
                    ->modalSubmitActionLabel(fn ($record): string => $record->has_pdf ? 'Обновить счёт' : 'Сформировать счёт')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        app(OrderInvoiceService::class)->generate($record);
                        Notification::make()->success()->title('PDF-счёт сформирован')->send();
                    })
                    ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
                    ->extraAttributes(['class' => 'hidden']),
                Action::make('approveInvoice')
                    ->label('Подтвердить счёт')
                    ->modalHeading(fn ($record): string => ($record->pdf_approved ? 'Повторно подтвердить' : 'Подтвердить').' счёт заказа №'.$record->id)
                    ->modalDescription('Клиенту будет отправлено письмо с PDF-счётом. При первом подтверждении может быть начислен referral-бонус.')
                    ->modalSubmitActionLabel(fn ($record): string => $record->pdf_approved ? 'Повторно подтвердить и отправить' : 'Подтвердить и отправить')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $result = app(OrderInvoiceService::class)->approve($record);
                        if ($result['email_suppressed']) {
                            Notification::make()
                                ->warning()
                                ->title('Счёт подтверждён; внешняя почта отключена')
                                ->body($result['mail_logged'] ? 'Тестовое письмо записано в локальный лог.' : 'Не удалось сформировать тестовое письмо, подробности записаны в лог.')
                                ->send();

                            return;
                        }

                        $notification = Notification::make()->title(
                            $result['mail_sent'] ? 'Счёт подтверждён и отправлен' : 'Счёт подтверждён, но письмо не отправлено',
                        );
                        ($result['mail_sent'] ? $notification->success() : $notification->warning())->send();
                    })
                    ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
                    ->extraAttributes(['class' => 'hidden']),
                ViewAction::make()->extraAttributes(['class' => 'hidden']),
            ], position: RecordActionsPosition::AfterContent);
    }

    private static function statusLabels(): array
    {
        return [
            'new' => 'Новый',
            'watching' => 'На рассмотрении',
            'pegging' => 'В процессе',
            'in_production' => 'В производстве',
            'sended' => 'Отправлен',
            'send_lubanas' => 'Отправлен на Лубанас 65',
            'completed' => 'Завершён',
        ];
    }

    private static function paymentLabels(): array
    {
        return [
            'not_payed' => 'Не оплачено',
            'prepayment' => 'Предоплата',
            'payed' => 'Оплачено',
        ];
    }

    private static function decodeJson(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function categoryLabel(int|string|null $categoryId): ?string
    {
        if (! $categoryId || (int) $categoryId === 0) {
            return null;
        }

        return self::categoryOptions()[(int) $categoryId] ?? null;
    }

    public static function salesChannelLabel(int|string|null $channelId): ?string
    {
        static $channels;
        $channels ??= AOrderFrom::query()->pluck('title', 'id')->all();

        return $channels[$channelId] ?? null;
    }

    public static function clientStatusOptions(): array
    {
        return once(fn (): array => UserType::query()->pluck('name', 'id')->all());
    }

    public static function localeOptions(): array
    {
        return collect(config('laravellocalization.supportedLocales', []))
            ->mapWithKeys(fn (array $properties, string $locale): array => [
                $locale => $locale.' — '.($properties['native'] ?? $properties['name'] ?? $locale),
            ])->all();
    }

    public static function relatedActiveOrderIds(Orders $order): array
    {
        static $activeOrders;

        $activeOrders ??= Orders::query()
            ->where('status', '!=', 'completed')
            ->get(['id', 'user_id', 'delivery'])
            ->map(function (Orders $activeOrder): array {
                $delivery = self::decodeJson($activeOrder->delivery);

                return [
                    'id' => (int) $activeOrder->id,
                    'user_id' => (int) $activeOrder->user_id,
                    'email' => strtolower(trim((string) ($delivery['email'] ?? ''))),
                    'phone' => self::normalizePhone($delivery['phone'] ?? ''),
                    'payer_phone' => self::normalizePhone($delivery['payer_phone'] ?? ''),
                ];
            });

        $delivery = self::decodeJson($order->delivery);
        $email = strtolower(trim((string) ($delivery['email'] ?? $order->user?->email ?? '')));
        $phone = self::normalizePhone($delivery['phone'] ?? $order->user?->phone ?? '');
        $payerPhone = self::normalizePhone($delivery['payer_phone'] ?? '');

        return $activeOrders->filter(function (array $active) use ($order, $email, $phone, $payerPhone): bool {
            return $active['user_id'] === (int) $order->user_id
                || ($email !== '' && $active['email'] !== '' && $active['email'] === $email)
                || ($phone !== '' && ($active['phone'] === $phone || $active['payer_phone'] === $phone))
                || ($payerPhone !== '' && ($active['phone'] === $payerPhone || $active['payer_phone'] === $payerPhone));
        })->pluck('id')->values()->all();
    }

    private static function normalizePhone(mixed $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone) ?: '';
    }

    private static function categoryOptions(): array
    {
        static $options;

        return $options ??= IndexController::get_styles_for_quiz('ru')
            ->mapWithKeys(fn ($category): array => [
                $category->id => trim(strip_tags((string) $category->getTranslatedAttribute('name'))),
            ])
            ->all();
    }

    private static function vrNumber($record): ?string
    {
        $vr = $record->vrNumber;

        return match (true) {
            filled($vr?->vrv_1) => 'VR00'.$vr->vrv_1,
            filled($vr?->vrv_2) => 'BAW'.$vr->vrv_2,
            filled($vr?->vrv_3) => 'VRR445'.str_pad((string) $vr->vrv_3, 3, '0', STR_PAD_LEFT),
            filled($vr?->vrv_4) => 'DS020'.$vr->vrv_4,
            default => null,
        };
    }

    private static function invoiceSummary($record): HtmlString
    {
        if ((int) $record->has_pdf !== 1) {
            return new HtmlString('<span class="text-gray-500">Счёт ещё не сгенерирован.</span>');
        }

        $url = asset('storage/pdf/'.$record->id.'.pdf');
        $approval = (int) $record->pdf_approved === 1 ? 'подтверждён' : 'не подтверждён';

        return new HtmlString('<a class="text-primary-600 underline" target="_blank" href="'.e($url).'">Открыть PDF-счёт</a> · '.e($approval));
    }

    private static function companySummary($record): string
    {
        if (! filled($record->ur_name)) {
            return 'Данные юридического лица не заполнены.';
        }

        return collect([
            $record->ur_name_l ?: $record->ur_name,
            $record->ur_reg_num ? 'Рег. № '.$record->ur_reg_num : null,
            $record->ur_pnr_nr ? 'VAT '.$record->ur_pnr_nr : null,
            $record->ur_legal_addr,
            $record->ur_bank_name,
            $record->ur_bank_acc_code,
        ])->filter()->implode(' · ');
    }

    private static function paymentRequestsSummary($record): HtmlString
    {
        $requests = $record->order_payment_requests;
        if ($requests->isEmpty()) {
            return new HtmlString('<span class="text-gray-500">Заявок ещё нет.</span>');
        }

        $html = $requests->map(function ($request): string {
            $status = $request->status === 'paid' ? 'Оплачено' : 'Ожидает оплаты';

            return '<div><strong>'.e($request->public_number).'</strong> · '
                .e(number_format((float) $request->amount, 2).' '.$request->currency).' · '
                .e($status).' · <a class="text-primary-600 underline" target="_blank" href="'
                .e($request->publicUrl()).'">Открыть ссылку</a></div>';
        })->implode('');

        return new HtmlString($html);
    }
}
