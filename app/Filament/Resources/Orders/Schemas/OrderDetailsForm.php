<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\AOrderFrom;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class OrderDetailsForm
{
    public static function manager(): Select
    {
        return Select::make('manager_id')->label('Менеджер')
            ->options(fn (): array => [0 => 'Админ'] + User::query()->where('role_id', 4)->orderBy('id')->get()
                ->mapWithKeys(fn (User $user): array => [$user->id => trim(($user->nick ? $user->nick.' — ' : '').$user->email)])->all())
            ->searchable()->preload()->selectablePlaceholder(false);
    }

    public static function parameters(): Section
    {
        return Section::make('Параметры заказа')->columnSpanFull()->columns(2)->schema([
            Select::make('a_order_from')->label('Канал продаж')
                ->options(fn (): array => AOrderFrom::query()->orderBy('id')->pluck('title', 'id')->all())
                ->visible(fn ($record): bool => $record?->a_order_from !== null)
                ->searchable()->preload()->selectablePlaceholder(false),
            Select::make('catid')->label('Категория')
                ->options(fn (): array => AdminOrderCreateForm::styleOptions())
                ->searchable()->preload()->selectablePlaceholder(false),
            // The original accepts both discounts; only the PDF applies them, EUR before percent.
            TextInput::make('sale_eur')->label('Скидка в евро')->numeric()->minValue(0),
            TextInput::make('sale_percent')->label('Скидка в %')->numeric()->minValue(0)->maxValue(100),
            TextInput::make('approved_date')->label('Дата заказа')->maxLength(255),
        ]);
    }

    public static function company(): Section
    {
        return Section::make('Юридическая информация')->columnSpanFull()->columns(2)->schema([
            Toggle::make('ur_name')->label('Юр. лицо (включать для вывода в счёте)')
                ->formatStateUsing(fn ($state): bool => (bool) $state)->columnSpanFull(),
            TextInput::make('ur_name_l')->label('Имя юр. лица')->maxLength(255),
            TextInput::make('ur_reg_num')->label('Рег. номер')->maxLength(255),
            Textarea::make('ur_legal_addr')->label('Адрес')->maxLength(255)->rows(2)->columnSpanFull(),
            TextInput::make('ur_pnr_nr')->label('VAT NUMBER')->maxLength(255),
            TextInput::make('ur_bank_name')->label('Имя банка')->maxLength(255),
            TextInput::make('ur_bank_code')->label('Код банка')->maxLength(255),
            TextInput::make('ur_bank_acc_code')->label('Номер счёта банка')->maxLength(255),
        ]);
    }
}
