<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\AOrderFrom;
use App\Models\CountryTel;
use App\Models\User;
use App\Http\Controllers\IndexController;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with([
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
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
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
                    ->wrap(),
                TextColumn::make('user.email')
                    ->label('Клиент')
                    ->description(fn ($record): string => trim(($record->user?->first_name ?? '').' '.($record->user?->last_name ?? '')))
                    ->searchable(),
                TextColumn::make('manager.email')
                    ->label('Менеджер')
                    ->placeholder('Не назначен')
                    ->searchable(),
                TextColumn::make('country')
                    ->label('Страна')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('recipient')
                    ->label('Получатель')
                    ->state(function ($record): string {
                        $delivery = json_decode((string) $record->delivery, true) ?: [];
                        $name = trim(($delivery['first_name'] ?? '').' '.($delivery['last_name'] ?? ''));
                        $phone = $delivery['phone'] ?? $record->user?->phone;

                        return trim($name.($phone ? "\n{$phone}" : '')) ?: '—';
                    })
                    ->wrap()
                    ->toggleable(),
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
                    ->toggleable(),
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
                    ->badge(),
                TextColumn::make('a_order_from')
                    ->label('Канал')
                    ->formatStateUsing(fn ($state): string => AOrderFrom::query()->whereKey($state)->value('title') ?? (string) $state)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('catid')
                    ->label('Категория')
                    ->formatStateUsing(fn ($state): string => filled($state) && (int) $state !== 0 ? '#'.$state : 'нет')
                    ->color(fn ($state): string => filled($state) && (int) $state !== 0 ? 'gray' : 'danger')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('painterAssignment.user.email')
                    ->label('Художник')
                    ->placeholder('Не назначен')
                    ->toggleable(),
                TextColumn::make('printingAssignment.user.email')
                    ->label('Печатник')
                    ->placeholder('Не назначен')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('chats')
                    ->label('Чаты')
                    ->state(fn ($record): string => implode(' · ', [
                        'К:'.$record->client_messages_count.($record->unread_client_messages_count ? '/'.$record->unread_client_messages_count.'!' : ''),
                        'А:'.$record->admin_messages_count,
                        'Х:'.$record->painter_messages_count,
                        'SA:'.$record->unread_sa_messages_count,
                    ]))
                    ->badge()
                    ->color(fn ($record): string => ($record->unread_client_messages_count || $record->unread_sa_messages_count) ? 'danger' : 'gray'),
                TextColumn::make('product_summary')
                    ->label('Товары')
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
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::statusLabels()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'sended', 'send_lubanas' => 'info',
                        'in_production', 'pegging' => 'warning',
                        default => 'gray',
                    })
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

                        return $record->payment_status === 'prepayment' && filled($record->prepayment_price)
                            ? $method.' · '.number_format((float) $record->prepayment_price, 2).' €'
                            : $method;
                    })
                    ->sortable(),
                TextColumn::make('sale_price')
                    ->label('Сумма')
                    ->formatStateUsing(fn ($state, $record): string => number_format((float) ($state ?: $record->price), 2).' €')
                    ->summarize(
                        Sum::make()
                            ->label('Сумма заказов')
                            ->using(fn ($query) => $query->sum('price'))
                            ->money('EUR'),
                    )
                    ->sortable(),
                IconColumn::make('is_admin_order')
                    ->label('Админ-заказ')
                    ->boolean(),
                TextColumn::make('labels')
                    ->label('Этикетки')
                    ->formatStateUsing(fn ($state): string => filled(trim((string) $state, ',')) ? trim((string) $state, ',') : '—')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->sortable(),
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
                ViewAction::make(),
            ]);
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

    private static function categoryOptions(): array
    {
        return IndexController::get_styles_for_quiz('ru')
            ->mapWithKeys(fn ($category): array => [
                $category->id => trim(strip_tags((string) $category->getTranslatedAttribute('name'))),
            ])
            ->all();
    }
}
