<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Http\Controllers\IndexController;
use App\Models\ADeliveryTown;
use App\Models\AOrderFrom;
use App\Models\CountryTel;
use App\Models\DeliveryPickupAtViarWorkshop;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdminOrderCreateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Hidden::make('source_order_id'),
            Section::make('Клиент и заказ')
                ->columnSpan(1)
                ->columns(2)
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
                    TextInput::make('email')->label('Email')->email()->required()->maxLength(255),
                    TextInput::make('first_name')->label('Имя')->required()->maxLength(255),
                    TextInput::make('last_name')->label('Фамилия')->maxLength(255),
                    TextInput::make('phone')->label('Телефон заказчика')->tel()->maxLength(50),
                    TextInput::make('recipient_phone')->label('Телефон получателя')->tel()->maxLength(50),
                    Textarea::make('comment')->label('Общий комментарий')->rows(3)->columnSpanFull(),
                    Select::make('a_order_from')->label('Канал продаж')
                        ->options(fn (): array => AOrderFrom::query()->orderBy('id')->pluck('title', 'id')->all())
                        ->searchable()->preload(),
                    Select::make('catid')->label('Категория')->options(fn (): array => self::styleOptions())
                        ->searchable()->preload()->default(0),
                ]),
            Section::make('Оплата и доставка')
                ->columnSpan(1)
                ->columns(2)
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
                    Select::make('payment_status')->label('Статус оплаты')->required()->options([
                        'not_payed' => 'Не оплачено', 'prepayment' => 'Предоплата', 'payed' => 'Оплачено',
                    ]),
                    Select::make('delivery_method')->label('Способ доставки')->required()->live()->options([
                        'to_the_door' => 'До дверей дома или работы',
                        'pickup_Riga' => 'Самовывоз Рига',
                        'pickup_Daugavplis' => 'Самовывоз Даугавпилс',
                        'venipak' => 'Venipak',
                        'pickup_at_viar_workshop' => 'Мастерская VIAR',
                        'city_delivery' => 'Доставка по городу',
                    ]),
                    Select::make('country')->label('Страна')->required()
                        ->options(fn (): array => CountryTel::query()->orderBy('sort')->pluck('country_code', 'country_code')->all())
                        ->searchable()->preload(),
                    Select::make('pickup_workshop_id')->label('Мастерская VIAR')
                        ->options(fn (): array => DeliveryPickupAtViarWorkshop::query()->where('is_show', 1)->orderBy('sort')->pluck('title', 'id')->all())
                        ->visible(fn ($get): bool => in_array($get('delivery_method'), ['pickup_at_viar_workshop', 'pickup_Riga', 'pickup_Daugavplis', 'pickup_Daugavpils'], true)),
                    Select::make('delivery_town_id')->label('Город доставки')
                        ->options(fn (): array => ADeliveryTown::query()->orderBy('id')->pluck('city', 'id')->all())
                        ->visible(fn ($get): bool => $get('delivery_method') === 'city_delivery'),
                    TextInput::make('city')->label('Город')->maxLength(255),
                    TextInput::make('address')->label('Адрес')->maxLength(1000),
                    TextInput::make('postal_index')->label('Почтовый индекс')->maxLength(30),
                    DatePicker::make('when_send')->label('Желаемая дата доставки'),
                    TextInput::make('delivery_price')->label('Цена доставки')->numeric()->minValue(0)->default(0)->live(),
                    TextInput::make('bonus')->label('Использовать бонусы')->numeric()->minValue(0)->default(0)->live(),
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
                    Toggle::make('is_manual_express')->label('Экспресс для заказа'),
                    Placeholder::make('order_total')->label('Итого к оплате')
                        ->content(fn ($get): string => number_format(self::total($get), 2, '.', ' ').' €'),
                ]),
            Section::make('Юридическое лицо')
                ->columnSpanFull()->columns(3)->collapsible()->collapsed()
                ->schema([
                    Toggle::make('is_legal_entity')->label('Оформить на юридическое лицо')->live()->columnSpanFull(),
                    TextInput::make('legal_name')->label('Имя юридического лица'),
                    TextInput::make('legal_registration_number')->label('Регистрационный номер'),
                    TextInput::make('legal_vat_number')->label('VAT NUMBER'),
                    Textarea::make('legal_address')->label('Юридический адрес')->rows(2),
                    TextInput::make('legal_bank_name')->label('Банк'),
                    TextInput::make('legal_bank_code')->label('Код банка'),
                    TextInput::make('legal_bank_account')->label('Номер счёта'),
                ]),
            Section::make('Состав заказа')
                ->columnSpanFull()
                ->schema([
                    Repeater::make('items')->label('Позиции')->minItems(1)->defaultItems(1)
                        ->addActionLabel('Добавить позицию')->reorderable()->collapsible()
                        ->itemLabel(fn (array $state): ?string => filled($state['name'] ?? null) ? $state['name'] : 'Новая позиция')
                        ->columns(4)
                        ->schema([
                            Hidden::make('existing_images'),
                            Hidden::make('legacy_payload'),
                            TextInput::make('name')->label('Название')->required()->maxLength(500)->columnSpan(2),
                            TextInput::make('price')->label('Цена, EUR')->numeric()->minValue(0)->required()->live(),
                            TextInput::make('size')->label('Размер')->required()->maxLength(255),
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
                            FileUpload::make('images')->label('Добавить исходники')->multiple()->storeFiles(false)
                                ->acceptedFileTypes(['image/*', 'image/heic', 'image/heif', 'application/pdf', 'application/postscript'])
                                ->maxSize(102400)->columnSpan(2),
                            Placeholder::make('copied_images')->label('Скопированные исходники')
                                ->content(fn ($get): string => count((array) $get('existing_images')).' файл(ов)'),
                            Textarea::make('comment')->label('Комментарий пользователя')->rows(3)->columnSpanFull(),
                        ]),
                ]),
        ]);
    }

    private static function userLabel(User $user): string
    {
        return trim($user->email.' — '.trim($user->first_name.' '.$user->last_name).' '.$user->phone);
    }

    /** @return array<int, string> */
    private static function styleOptions(): array
    {
        $options = [0 => 'Нет категории'];
        foreach (IndexController::get_styles_for_quiz('ru') as $style) {
            $options[(int) $style->id] = trim(strip_tags((string) $style->getTranslatedAttribute('name')));
        }

        return $options;
    }

    private static function total($get): float
    {
        $items = collect((array) $get('items'))->sum(fn (array $item): float => (float) ($item['price'] ?? 0));
        $subtotal = max(0, $items - (float) $get('bonus'));
        $saleEur = (float) $get('sale_eur');
        $salePercent = (float) $get('sale_percent');
        if ($saleEur > 0) {
            $subtotal -= $saleEur;
        }
        if ($salePercent > 0) {
            $subtotal -= $subtotal * ($salePercent / 100);
        }

        return max(0, $subtotal) + max(0, (float) $get('delivery_price'));
    }
}
