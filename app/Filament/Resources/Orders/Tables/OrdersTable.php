<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\AOrderFrom;
use App\Models\CountryTel;
use App\Models\User;
use App\Http\Controllers\IndexController;
use App\Services\Admin\UpdateOrderVrNumberService;
use App\Services\Admin\OrderInvoiceService;
use App\Services\Admin\OrderPaymentService;
use App\Services\Admin\OrderRecipientEmailService;
use App\Services\Admin\OrderUserService;
use App\Services\Admin\OrderVenipakLabelService;
use App\Services\Payment\OrderPaymentRequestService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with([
                    'user' => fn ($query) => $query->withCount('orders'),
                    'manager',
                    'vrNumber',
                    'order_payment_requests' => fn ($query) => $query->latest('id')->limit(3),
                    'painterAssignment.user',
                    'printingAssignment.user',
                ])
                ->withCount([
                    'order_user_comments as client_messages_count',
                    'order_user_comments as unread_client_messages_count' => fn ($query) => $query
                        ->where('is_admin', 0)->where('admin_is_read', 0),
                    'adminChats as admin_messages_count',
                    'orders_chats as painter_messages_count',
                    'saMessages as unread_sa_messages_count' => fn ($query) => $query
                        ->where('status', '!=', 'read'),
                ]))
            ->paginated([13, 25, 50, 100])
            ->defaultPaginationPageOption(13)
            ->columns([
                ViewColumn::make('number_controls')
                    ->label('Номер')
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
                    ->toggleable(isToggledHiddenByDefault: true),
                ViewColumn::make('user_controls')
                    ->label('Пользователь')
                    ->view('filament.tables.columns.order-user'),
                TextColumn::make('manager.email')
                    ->label('Менеджер')
                    ->placeholder('Не назначен')
                    ->searchable()
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
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('catid')
                    ->label('Категория')
                    ->formatStateUsing(fn ($state): string => filled($state) && (int) $state !== 0 ? '#'.$state : 'нет')
                    ->color(fn ($state): string => filled($state) && (int) $state !== 0 ? 'gray' : 'danger')
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
                        'SA:'.$record->unread_sa_messages_count,
                    ]))
                    ->description(function ($record): string {
                        $deliveryComment = data_get(self::decodeJson($record->delivery), 'comment');

                        return collect([$deliveryComment, $record->comment, $record->admin_comment, $record->painter_comment])
                            ->filter()->take(2)->implode(' · ');
                    })
                    ->badge()
                    ->color(fn ($record): string => ($record->unread_client_messages_count || $record->unread_sa_messages_count) ? 'danger' : 'gray')
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
                TextColumn::make('status')
                    ->label('Заказ')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::statusLabels()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'sended', 'send_lubanas' => 'info',
                        'in_production', 'pegging' => 'warning',
                        default => 'gray',
                    })
                    ->description(fn ($record): string => collect([
                        '№'.$record->id,
                        $record->is_admin_order ? 'Админ-заказ' : null,
                        $record->status_date ? 'Изменён: '.date('d.m.Y H:i', strtotime((string) $record->status_date)) : null,
                        'Создан: '.$record->created_at?->format('d.m.Y H:i'),
                    ])->filter()->implode(' · '))
                    ->sortable(),
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
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => $query->whereKey((int) $data['value']),
                    )),
                Filter::make('payment_request')
                    ->label('Номер запроса оплаты')
                    ->schema([TextInput::make('value')->label('Номер (OPR-...)')])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => $query->whereHas(
                            'order_payment_requests',
                            fn (Builder $query): Builder => $query->where('public_number', 'like', '%'.trim($data['value']).'%'),
                        ),
                    )),
                Filter::make('customer')
                    ->label('Клиент')
                    ->schema([TextInput::make('value')->label('Email, имя, телефон или ID')])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        function (Builder $query) use ($data): Builder {
                            $value = trim($data['value']);

                            if (preg_match('/^(VR00|BAW|VRR445|DS020)(\d+)$/i', $value, $matches)) {
                                $column = match (strtoupper($matches[1])) {
                                    'VR00' => 'vrv_1',
                                    'BAW' => 'vrv_2',
                                    'VRR445' => 'vrv_3',
                                    default => 'vrv_4',
                                };

                                return $query->whereHas('vrNumber', fn (Builder $query): Builder => $query
                                    ->where($column, (int) $matches[2]));
                            }

                            return $query->where(function (Builder $query) use ($value): void {
                                $query->whereHas('user', function (Builder $query) use ($value): void {
                                    $query->where('email', 'like', "%{$value}%")
                                        ->orWhere('first_name', 'like', "%{$value}%")
                                        ->orWhere('last_name', 'like', "%{$value}%")
                                        ->orWhere('phone', 'like', "%{$value}%");

                                    if (ctype_digit($value)) {
                                        $query->orWhereKey((int) $value);
                                    }
                                })->orWhere('delivery', 'like', "%{$value}%");
                            });
                        },
                    )),
                Filter::make('phone')
                    ->label('Телефон')
                    ->schema([TextInput::make('value')->label('Телефон')])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        function (Builder $query) use ($data): Builder {
                            $phone = preg_replace('/[^0-9+]/', '', $data['value']);

                            return $query->where(fn (Builder $query): Builder => $query
                                ->whereHas('user', fn (Builder $query): Builder => $query->where('phone', 'like', "%{$phone}%"))
                                ->orWhere('delivery', 'like', "%{$phone}%"));
                        },
                    )),
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(self::statusLabels()),
                SelectFilter::make('payment_status')
                    ->label('Оплата')
                    ->options(self::paymentLabels()),
                SelectFilter::make('manager_id')
                    ->label('Менеджер')
                    ->options(fn (): array => ['__admin__' => 'Админ / без менеджера'] + User::query()
                        ->where('role_id', 4)
                        ->orderBy('email')
                        ->get()
                        ->mapWithKeys(fn (User $user): array => [$user->id => trim(($user->nick ? $user->nick.' — ' : '').$user->email)])
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => $data['value'] === '__admin__'
                            ? $query->where(function (Builder $query): void {
                                $managerIds = User::query()->where('role_id', 4)->pluck('id');
                                $query->whereNull('manager_id')->orWhereNotIn('manager_id', $managerIds);
                            })
                            : $query->where('manager_id', $data['value']),
                    ))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('painter')
                    ->label('Художник')
                    ->options(fn (): array => User::query()->where('role_id', 3)->orderBy('email')->pluck('email', 'id')->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => $query->whereHas('painterAssignment', fn (Builder $query): Builder => $query
                            ->where('user_id', $data['value'])),
                    ))
                    ->searchable()
                    ->preload(),
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
                    ->options(fn (): array => CountryTel::query()->orderBy('sort')->pluck('country_name', 'country_code')->all())
                    ->searchable(),
                Filter::make('price')
                    ->label('Точная сумма')
                    ->schema([TextInput::make('value')->label('Сумма')->numeric()])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => $query->where(function (Builder $query) use ($data): void {
                            $query->where('price', $data['value'])->orWhere('sale_price', $data['value']);
                        }),
                    )),
                Filter::make('size')
                    ->label('Размер')
                    ->schema([TextInput::make('value')->label('Размер или ID размера')])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        function (Builder $query) use ($data): Builder {
                            $size = trim((string) $data['value']);

                            return $query->where(function (Builder $query) use ($size): void {
                                foreach (range(0, 9) as $index) {
                                    $method = $index === 0 ? 'whereJsonContains' : 'orWhereJsonContains';
                                    $query->{$method}("items->{$index}->sizeId", $size)
                                        ->orWhereJsonContains("items->{$index}->size_name", $size);
                                }
                            });
                        },
                    )),
                Filter::make('created_at')
                    ->label('Дата создания')
                    ->schema([
                        DatePicker::make('from')->label('С'),
                        DatePicker::make('until')->label('По'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date))),
                TernaryFilter::make('is_admin_order')
                    ->label('Административный заказ'),
            ])
            ->recordActions([
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
                Action::make('createPaymentRequest')
                    ->label('Создать платёж')
                    ->modalHeading(fn ($record): string => 'Создать платёж для заказа №'.$record->id)
                    ->modalSubmitActionLabel('Создать платёж')
                    ->schema([
                        TextInput::make('amount')->label('Сумма, EUR')->numeric()->minValue(0.01)->maxValue(999999.99)->required(),
                        TextInput::make('purpose')->label('Назначение платежа')->maxLength(255),
                    ])
                    ->fillForm(fn ($record): array => [
                        'amount' => number_format((float) ($record->sale_price ?: $record->price), 2, '.', ''),
                    ])
                    ->action(function ($record, array $data): void {
                        app(OrderPaymentRequestService::class)->createForOrder($record, $data, auth('filament')->user());
                        $record->unsetRelation('order_payment_requests');
                        Notification::make()->success()->title('Заявка на оплату создана')->send();
                    })
                    ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
                    ->extraAttributes(['class' => 'hidden']),
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
                Action::make('createVenipakLabel')
                    ->label('Создание этикетки')
                    ->modalHeading(fn ($record): string => 'Этикетка Venipak для заказа №'.$record->id)
                    ->modalSubmitActionLabel('Создать этикетку')
                    ->modalWidth('5xl')
                    ->schema([
                        Section::make('Получатель')
                            ->columns(3)
                            ->schema([
                                TextInput::make('doc_no')->label('№ документа на посылку')->maxLength(16),
                                Select::make('destination')
                                    ->label('Направление')
                                    ->options(['address' => 'На адрес', 'pickup' => 'На отделение'])
                                    ->required()
                                    ->live(),
                                TextInput::make('r_country')->label('Страна')->required()->length(2),
                                TextInput::make('g_name')->label('Название / получатель')->required()->maxLength(60),
                                TextInput::make('g_code')->label('Код компании')->maxLength(20),
                                TextInput::make('g_contact_p')->label('Контактное лицо')->required()->maxLength(80),
                                TextInput::make('g_contact_t')->label('Телефон')->tel()->required()->maxLength(30),
                                TextInput::make('email_receiver')->label('Email')->email()->maxLength(100),
                                TextInput::make('g_city')->label('Город')->required()->maxLength(40)
                                    ->visible(fn ($get): bool => $get('destination') === 'address'),
                                TextInput::make('g_address')->label('Улица')->required()->maxLength(100)
                                    ->visible(fn ($get): bool => $get('destination') === 'address'),
                                TextInput::make('g_house')->label('Дом')->maxLength(15)
                                    ->visible(fn ($get): bool => $get('destination') === 'address'),
                                TextInput::make('g_flat')->label('Квартира')->maxLength(15)
                                    ->visible(fn ($get): bool => $get('destination') === 'address'),
                                TextInput::make('g_post')->label('Почтовый индекс')->required()->maxLength(12)
                                    ->visible(fn ($get): bool => $get('destination') === 'address'),
                                TextInput::make('door_code')->label('Код двери')->maxLength(10)
                                    ->visible(fn ($get): bool => $get('destination') === 'address'),
                                TextInput::make('office_no')->label('Номер офиса')->maxLength(10)
                                    ->visible(fn ($get): bool => $get('destination') === 'address'),
                                TextInput::make('warehous_no')->label('Номер склада')->maxLength(10)
                                    ->visible(fn ($get): bool => $get('destination') === 'address'),
                                TextInput::make('g_city_pickup')->label('Город отделения')->required()->maxLength(40)
                                    ->visible(fn ($get): bool => $get('destination') === 'pickup'),
                                TextInput::make('g_address_pickup')->label('Адрес отделения')->required()->maxLength(120)
                                    ->visible(fn ($get): bool => $get('destination') === 'pickup'),
                                TextInput::make('g_post_pickup')->label('Индекс отделения')->required()->maxLength(12)
                                    ->visible(fn ($get): bool => $get('destination') === 'pickup'),
                                TextInput::make('g_name_pickup')
                                    ->label('Название пункта Venipak')
                                    ->helperText('Укажите название выбранного пункта выдачи Venipak.')
                                    ->required()
                                    ->maxLength(100)
                                    ->visible(fn ($get): bool => $get('destination') === 'pickup'),
                                TextInput::make('g_code_pickup')
                                    ->label('Код пункта Venipak')
                                    ->required()
                                    ->maxLength(30)
                                    ->visible(fn ($get): bool => $get('destination') === 'pickup'),
                            ]),
                        Section::make('Отправитель')
                            ->columns(3)
                            ->collapsible()
                            ->schema([
                                TextInput::make('s_name')->label('Название')->required()->maxLength(60),
                                TextInput::make('s_code')->label('Код компании')->required()->maxLength(20),
                                TextInput::make('s_country')->label('Страна')->required()->length(2),
                                TextInput::make('s_city')->label('Город')->required()->maxLength(40),
                                TextInput::make('s_address')->label('Адрес')->required()->maxLength(100),
                                TextInput::make('s_post')->label('Почтовый индекс')->required()->maxLength(12),
                                TextInput::make('s_contact_p')->label('Контактное лицо')->required()->maxLength(60),
                                TextInput::make('s_contact_t')->label('Телефон')->tel()->required()->maxLength(30),
                                TextInput::make('email_sender')->label('Email')->email()->maxLength(100),
                            ]),
                        Section::make('Доставка и услуги')
                            ->columns(3)
                            ->schema([
                                Select::make('delivery_type')
                                    ->label('Срок доставки')
                                    ->options([
                                        'nwd' => 'Следующий рабочий день',
                                        'nwd10' => 'Следующий рабочий день до 10:00',
                                        'nwd12' => 'Следующий рабочий день до 12:00',
                                        'nwd8_14' => 'Следующий рабочий день 8:00–14:00',
                                        'nwd14_17' => 'Следующий рабочий день 14:00–17:00',
                                        'nwd18_22' => 'Следующий рабочий день 18:00–22:00',
                                        'sat' => 'Суббота',
                                    ])->required(),
                                Select::make('delivery_express')
                                    ->label('Экспресс')
                                    ->options(['0' => '48 часов', '1' => '24 часа'])
                                    ->required(),
                                TextInput::make('cod')->label('Наложенный платёж')->numeric()->minValue(0),
                                Select::make('cod_type')->label('Валюта COD')->options(['EUR' => 'EUR', 'PLN' => 'PLN', 'CZK' => 'CZK'])->required(),
                                Toggle::make('comment_call')->label('Позвонить перед доставкой'),
                                Toggle::make('four_hands')->label('Нужны четыре руки'),
                            ]),
                        Section::make('Посылки')
                            ->schema([
                                Repeater::make('packages')
                                    ->label('')
                                    ->minItems(1)
                                    ->maxItems(20)
                                    ->defaultItems(1)
                                    ->addActionLabel('Добавить посылку')
                                    ->columns(3)
                                    ->schema([
                                        TextInput::make('weight')->label('Вес, кг')->numeric()->minValue(0.01)->required(),
                                        TextInput::make('volume')->label('Объём')->numeric()->minValue(0),
                                        Select::make('pallet')->label('Паллет')->options([
                                            '0' => 'Нет', '2' => '1.2m / 0.8m', '6' => '1.2m / 1m', '7' => '1.2m / 1.2m',
                                            '3' => '0.8m / 0.6m', '4' => 'Другое',
                                        ])->required(),
                                    ]),
                            ]),
                    ])
                    ->fillForm(fn ($record): array => app(OrderVenipakLabelService::class)->defaultData($record))
                    ->action(function ($record, array $data): void {
                        $labels = app(OrderVenipakLabelService::class)->create($record, $data);
                        $record->refresh();
                        Notification::make()
                            ->success()
                            ->title('Этикетка Venipak создана')
                            ->body('Номера: '.implode(', ', $labels))
                            ->send();
                    })
                    ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
                    ->extraAttributes(['class' => 'hidden']),
                Action::make('printVenipakLabel')
                    ->label('Печать этикетки')
                    ->action(function ($record, array $arguments) {
                        $download = app(OrderVenipakLabelService::class)->print($record, (string) ($arguments['label'] ?? ''));

                        return response()->streamDownload(
                            static fn () => print($download['content']),
                            $download['filename'],
                            ['Content-Type' => 'application/pdf', 'Cache-Control' => 'no-store, no-cache'],
                        );
                    })
                    ->authorize(fn ($record): bool => auth('filament')->user()?->can('update', $record) ?? false)
                    ->extraAttributes(['class' => 'hidden']),
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
                        \Filament\Forms\Components\Textarea::make('line')->label('Основной текст')->required()->rows(8)->maxLength(10000),
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
        static $statuses;

        return $statuses ??= \App\Models\UserType::query()->pluck('name', 'id')->all();
    }

    public static function localeOptions(): array
    {
        return collect(config('laravellocalization.supportedLocales', []))
            ->mapWithKeys(fn (array $properties, string $locale): array => [
                $locale => $locale.' — '.($properties['native'] ?? $properties['name'] ?? $locale),
            ])->all();
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
