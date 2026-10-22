<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Http\Controllers\IndexController;
use App\Models\AOrderFrom;
use App\Models\User;
use App\Services\Admin\OrderDeliveryEditor;
use App\Support\Admin\OrderFileUploadLimits;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdminOrderCreateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->extraAttributes(['class' => 'viar-order-create-form'])
            ->columns(['default' => 1, 'lg' => 2])->components([
                Hidden::make('source_order_id'),
                Group::make([
                    Section::make('Клиент и заказ')
                        ->columnSpan(1)
                        ->columns(1)
                        ->schema([
                            Select::make('manager_id')->label('Менеджер')
                                ->options(fn (): array => User::query()->where('role_id', 4)->orderBy('email')->get()
                                    ->mapWithKeys(fn (User $user): array => [$user->id => trim(($user->nick ? $user->nick.' — ' : '').$user->email)])->all())
                                ->searchable()->preload()->placeholder('Админ'),
                            TextInput::make('admin_comment')->label('Комментарий админа')->maxLength(10000),
                            Select::make('client_id')->label('Найти существующего клиента')
                                ->searchable()
                                ->getSearchResultsUsing(fn (string $search): array => User::query()
                                    ->where(function ($query) use ($search): void {
                                        $query->where('email', 'like', '%'.$search.'%')
                                            ->orWhere('phone', 'like', '%'.$search.'%')
                                            ->orWhere('first_name', 'like', '%'.$search.'%')
                                            ->orWhere('last_name', 'like', '%'.$search.'%');
                                    })->limit(30)->get()->mapWithKeys(fn (User $user): array => [$user->id => self::userLabel($user)])->all())
                                ->getOptionLabelUsing(fn ($value): ?string => ($user = User::find($value)) ? self::userLabel($user) : null)
                                ->afterStateUpdated(function ($state, $set): void {
                                    if (! $user = User::find($state)) {
                                        return;
                                    }
                                    foreach (['email', 'first_name', 'last_name', 'phone', 'country', 'address', 'postal_index'] as $field) {
                                        $set($field, $user->{$field});
                                    }
                                    $set('recipient_phone', $user->phone);
                                })
                                ->live()
                                ->columnSpanFull(),
                            TextInput::make('email')->label('Email')->email()->required()->maxLength(255)
                                ->live(onBlur: true)->afterStateUpdated(function ($state, $set): void {
                                    $user = self::clientForEmail($state);
                                    $set('client_id', $user?->id);
                                    if ($user) {
                                        foreach (['first_name', 'last_name', 'phone'] as $field) {
                                            $set($field, $user->{$field});
                                        }
                                        $set('recipient_phone', $user->phone);
                                    } elseif (mb_strlen(trim((string) $state)) >= 3) {
                                        // Voyager clears the previous client's identity when searching a new email.
                                        foreach (['first_name', 'last_name', 'phone', 'recipient_phone'] as $field) {
                                            $set($field, null);
                                        }
                                    }
                                }),
                            TextInput::make('first_name')->label('Имя')->required()->maxLength(255),
                            TextInput::make('last_name')->label('Фамилия')->maxLength(255),
                            TextInput::make('phone')->label('Телефон заказчика')->type('tel')->maxLength(50),
                            TextInput::make('recipient_phone')->label('Телефон получателя')->type('tel')->maxLength(50),
                            Textarea::make('comment')->label('Общий комментарий')->rows(1)->columnSpanFull(),
                            Select::make('a_order_from')->label('Канал продаж')
                                ->options(fn (): array => AOrderFrom::query()->orderBy('id')->pluck('title', 'id')->all())
                                ->searchable()->preload(),
                            Select::make('catid')->label('Категория')->options(fn (): array => self::styleOptions())
                                ->searchable()->preload()->default(0),
                        ]),
                    Section::make('Оплата и доставка')
                        ->columnSpan(1)
                        ->columns(1)
                        ->schema([
                            Select::make('payment')->label('Оплата')->required()->options([
                                'cash_in_office' => 'Наличными при получении в офисе',
                                'on_delivery' => 'Оплата во время доставки',
                                'transfer' => 'Оплата перечислением',
                                'online_paysera' => 'Онлайн банкинг',
                                'google_pay' => 'Google Pay', 'apple_pay' => 'Apple Pay',
                                'paypalOnetimePayment' => 'PayPal', 'creditcart' => 'Картой онлайн',
                                'prepayment' => 'Предоплата',
                            ]),
                            Select::make('payment_status')->label('Статус оплаты')->required()->live()
                                ->extraInputAttributes(fn ($state): array => ['class' => 'viar-create-payment-status', 'data-payment-status' => $state])
                                ->options([
                                    'not_payed' => 'Не оплачено', 'prepayment' => 'Предоплата', 'payed' => 'Оплачено',
                                ]),
                            Select::make('delivery_method')->label('Способ доставки')->required()->live()
                                ->afterStateUpdated(fn ($get, $set) => self::synchronizeDelivery($get, $set))->options([
                                    'to_the_door' => 'До дверей дома или работы',
                                    'pickup_Riga' => 'Самовывоз Рига',
                                    'pickup_Daugavplis' => 'Самовывоз Даугавпилс',
                                    'venipak' => 'Venipak',
                                    'pickup_at_viar_workshop' => 'Мастерская VIAR',
                                    'city_delivery' => 'Доставка по городу',
                                ]),
                            Select::make('country')->label('Страна')->required()
                                ->options(fn (): array => app(OrderDeliveryEditor::class)->countries())
                                ->searchable()->preload()->live()
                                ->afterStateUpdated(fn ($get, $set) => self::synchronizeDelivery($get, $set)),
                            Select::make('pickup_workshop_id')->label('Мастерская VIAR')
                                ->options(fn ($get): array => app(OrderDeliveryEditor::class)->pickups($get('country')))
                                ->visible(fn ($get): bool => in_array($get('delivery_method'), OrderDeliveryEditor::PICKUP_METHODS, true))
                                ->live()->afterStateUpdated(function ($state, $get, $set): void {
                                    $title = app(OrderDeliveryEditor::class)->pickups($get('country'))[$state] ?? null;
                                    if ($title !== null) {
                                        $set('address', $title);
                                        $set('city', $get('delivery_method') === 'pickup_at_viar_workshop' ? null : $title);
                                    }
                                }),
                            Select::make('delivery_town_id')->label('Город доставки')
                                ->options(fn ($get): array => app(OrderDeliveryEditor::class)->towns($get('country')))
                                ->visible(fn ($get): bool => $get('delivery_method') === 'city_delivery')
                                ->live()->afterStateUpdated(function ($state, $get, $set): void {
                                    $title = app(OrderDeliveryEditor::class)->towns($get('country'))[$state] ?? null;
                                    if ($title !== null) {
                                        $set('city', $title);
                                    }
                                }),
                            TextInput::make('city')->label('Город')->maxLength(255)
                                ->visible(fn ($get): bool => ! in_array($get('delivery_method'), OrderDeliveryEditor::PICKUP_METHODS, true)),
                            TextInput::make('address')->label('Адрес')->maxLength(1000),
                            TextInput::make('postal_index')->label('Почтовый индекс')->maxLength(30),
                            DatePicker::make('when_send')->label('Желаемая дата доставки'),
                            TextInput::make('delivery_price')->label('Цена доставки')->numeric()->minValue(0)->default(0)->live(),
                            TextInput::make('bonus')->label('Использовать бонусы')->numeric()->minValue(0)->default(0)->live()
                                ->maxValue(fn ($get): float => self::bonusBalance($get('email')))
                                ->helperText(fn ($get): string => 'Доступно бонусов: '.self::bonusBalance($get('email')))
                                ->visible(fn ($get): bool => self::bonusBalance($get('email')) > 0),
                            TextInput::make('sale_eur')->label('Скидка, EUR')->numeric()->minValue(0)->default(0)->live()
                                ->afterStateUpdated(function ($state, $set): void {
                                    if ((float) $state > 0) {
                                        $set('sale_percent', 0);
                                    }
                                }),
                            TextInput::make('sale_percent')->label('Скидка, %')->numeric()->minValue(0)->maxValue(100)->default(0)->live()
                                ->afterStateUpdated(function ($state, $set): void {
                                    if ((float) $state > 0) {
                                        $set('sale_eur', 0);
                                    }
                                }),
                            Hidden::make('is_manual_express')->default(false),
                            Placeholder::make('items_subtotal')->label('Итого по товарам')
                                ->content(fn ($get): string => number_format(self::itemsSubtotal($get), 2, '.', ' ').' €'),
                            Placeholder::make('order_total')->label('Итого к оплате')
                                ->content(fn ($get): string => number_format(self::total($get), 2, '.', ' ').' €'),
                        ]),
                    Section::make('Юридическое лицо')
                        ->columnSpanFull()->columns(1)->collapsible()
                        ->schema([
                            Toggle::make('is_legal_entity')->label('Оформить на юридическое лицо')->live()->columnSpanFull(),
                            TextInput::make('legal_name')->label('Имя юридического лица'),
                            TextInput::make('legal_registration_number')->label('Регистрационный номер'),
                            Textarea::make('legal_address')->label('Юридический адрес')->rows(2),
                            TextInput::make('legal_vat_number')->label('VAT NUMBER'),
                            TextInput::make('legal_bank_name')->label('Банк'),
                            TextInput::make('legal_bank_code')->label('Код банка'),
                            TextInput::make('legal_bank_account')->label('Номер счёта'),
                        ]),
                ])->columnSpan(1),
                Section::make('Состав заказа')
                    ->columnSpan(1)
                    ->schema([
                        Repeater::make('items')->label('Позиции')->minItems(1)->defaultItems(1)
                            ->addActionLabel('Добавить позицию')->reorderable()->collapsible()
                            ->itemLabel(fn (array $state): ?string => filled($state['name'] ?? null) ? $state['name'] : 'Новая позиция')
                            ->columns(1)
                            ->schema([
                                Hidden::make('existing_images'),
                                Hidden::make('legacy_payload'),
                                TextInput::make('name')->label('Название')->required()->maxLength(500)->columnSpanFull(),
                                TextInput::make('price')->label('Цена, EUR')->numeric()->minValue(0)->required()->live(),
                                TextInput::make('size')->label('Размер')->required()->maxLength(255),
                                FileUpload::make('images')->label('Добавить исходники')->multiple()->storeFiles(false)
                                    ->extraAlpineAttributes(OrderFileUploadLimits::alpineAttributes(102400))
                                    ->acceptedFileTypes(['image/*', 'image/heic', 'image/heif'])
                                    ->maxSize(102400)->columnSpanFull(),
                                TextInput::make('terms')->label('Изготовление / срок')->maxLength(255),
                                Select::make('canvas_id')->label('Вид холста')->required()->default(2)->options([
                                    1 => 'Эконом (S)', 2 => 'Интерьерный (S)', 3 => 'Синтетический (S)',
                                    4 => 'Хлопковый (C)', 5 => 'Глянцевый (G)',
                                ]),
                                Select::make('gift_code')->label('Упаковка')->required()->default('G0')->options([
                                    'G0' => 'Обычная (G0)', 'G1' => 'Подарочная (G1)', 'G2' => 'Эксклюзивная (G2)',
                                ]),
                                Select::make('decoration_id')->label('Лак / мазки')->required()->default(5)->options([
                                    5 => 'Без дополнений (L0, P0)', 1 => 'Арт-гель (L2, P0)',
                                    2 => 'Художественные мазки (L0, P1)', 3 => 'Даммарный лак (L1, P0)',
                                ]),
                                Select::make('orientation_code')->label('Ориентация')->required()->default('V0')->options([
                                    'V0' => 'Авто (V0)', 'V1' => 'Вертикальная (V1)', 'V2' => 'Горизонтальная (V2)',
                                    'V3' => 'Квадрат (V3)', 'V4' => 'Панорама (V4)',
                                ]),
                                Select::make('baget_code')->label('Оформление')->required()->default('B0')->options([
                                    'B0' => 'Без рамки (B0)', 'B1' => 'Рамка (B1)', 'B2' => 'На бумаге, в рамке (B2)',
                                ]),
                                Toggle::make('express')->label('Экспресс'),
                                Placeholder::make('copied_images')->label('Скопированные исходники')
                                    ->visible(fn ($get): bool => count((array) $get('existing_images')) > 0)
                                    ->content(fn ($get): string => count((array) $get('existing_images')).' файл(ов)'),
                                Textarea::make('comment')->label('Комментарий пользователя')->rows(3)->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    private static function clientForEmail(?string $email): ?User
    {
        $email = mb_strtolower(trim((string) $email));
        if ($email === '') {
            return null;
        }

        return User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
    }

    private static function bonusBalance(?string $email): float
    {
        return max(0, (float) (self::clientForEmail($email)?->bonuses ?? 0));
    }

    private static function synchronizeDelivery(callable $get, callable $set): void
    {
        $key = static fn (string $path): string => $path === 'delivery_edit.sposob'
            ? 'delivery_method' : substr($path, strlen('delivery_edit.'));
        app(OrderDeliveryEditor::class)->synchronize(
            fn (string $path) => $get($key($path)),
            fn (string $path, $value) => $set($key($path), $value),
        );
        // Original creation clears the hidden city when switching to any pickup method.
        if (in_array($get('delivery_method'), OrderDeliveryEditor::PICKUP_METHODS, true)) {
            $set('city', null);
        }
    }

    private static function userLabel(User $user): string
    {
        return trim($user->email.' — '.trim($user->first_name.' '.$user->last_name).' '.$user->phone);
    }

    /** @return array<int, string> */
    public static function styleOptions(): array
    {
        $options = [0 => 'Нет категории'];
        foreach (IndexController::get_styles_for_quiz('ru') as $style) {
            $options[(int) $style->id] = trim(strip_tags((string) $style->getTranslatedAttribute('name')));
        }

        return $options;
    }

    private static function itemsSubtotal($get): float
    {
        return round(collect((array) $get('items'))->sum(fn (array $item): float => (float) ($item['price'] ?? 0)), 2);
    }

    private static function total($get): float
    {
        $items = self::itemsSubtotal($get);
        $subtotal = max(0, round($items - (float) $get('bonus'), 2));
        $saleEur = (float) $get('sale_eur');
        $salePercent = (float) $get('sale_percent');
        if ($saleEur > 0) {
            $subtotal -= $saleEur;
        }
        if ($salePercent > 0) {
            $subtotal -= $subtotal * ($salePercent / 100);
        }

        $subtotal = max(0, round($subtotal, 2));

        return round($subtotal + max(0, (float) $get('delivery_price')), 2);
    }
}
