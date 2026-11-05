<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Orders;
use App\Services\Admin\OrderItemEditor;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Illuminate\Validation\ValidationException;

class OrderItemsForm
{
    public static function section(): Section
    {
        return Section::make('Товары в заказе')->columnSpanFull()->schema([
            Repeater::make('items_edit')->label('Позиции')
                ->helperText('Изменения позиции сохраняются кнопкой «Обновить позицию».')
                ->dehydrated(false)->validatedWhenNotDehydrated(false)
                ->addable(false)->deletable(false)->reorderable(false)
                ->columns(1)->itemLabel(fn (array $state): string => $state['name'] ?? 'Позиция')
                ->schema([
                    Hidden::make('index'), Hidden::make('fingerprint'),
                    TextInput::make('name')->label('Имя')->maxLength(1000)->columnSpanFull(),
                    TextInput::make('size_name')->label('Размер')->maxLength(255),
                    TextInput::make('sizeId')->label('Размер (sizeId)')->maxLength(255),
                    TextInput::make('price')->label('Цена')->numeric()->minValue(0)->required(),
                    Textarea::make('userComment')->label('Комментарий к позиции')->maxLength(10000)->columnSpanFull(),
                    ...self::productionFields(),
                    Placeholder::make('source_images')->label('Фото клиента')->columnSpanFull()
                        ->content(fn ($record, $get) => view('filament.orders.item-images', [
                            'images' => app(OrderItemEditor::class)->images($record, (int) $get('index')),
                        ])),
                ])
                ->extraItemActions([
                    OrderItemActions::attach(),
                    Action::make('updateOrderItem')->label('Обновить позицию')->button()->color('primary')->icon('heroicon-o-check')
                        ->authorize(fn (Orders $record): bool => auth('filament')->user()?->can('update', $record) ?? false)
                        ->action(function (array $arguments, Repeater $component, Orders $record, $livewire): void {
                            abort_unless(auth('filament')->user()?->can('update', $record), 403);
                            $key = $arguments['item'] ?? '';
                            $states = $component->getState();
                            abort_unless(is_string($key) && isset($states[$key]), 422);
                            try {
                                $updated = app(OrderItemEditor::class)->update($record, $component->getRawItemState($key));
                            } catch (ValidationException $exception) {
                                throw ValidationException::withMessages(collect($exception->errors())->mapWithKeys(
                                    fn ($messages, $field): array => [$component->getStatePath().'.'.$key.'.'.$field => $messages]
                                )->all());
                            }
                            foreach (app(OrderItemEditor::class)->state($updated) as $state) {
                                if ($state['index'] === (int) $states[$key]['index']) {
                                    $states[$key] = $state;
                                    break;
                                }
                            }
                            $component->state($states);
                            $livewire->record = $updated;
                            $livewire->data['price'] = $updated->price;
                            $livewire->data['sale_price'] = $updated->sale_price;
                            Notification::make()->success()->title('Позиция обновлена')->send();
                        }),
                ]),
            Actions::make([OrderItemActions::add()])->key('orderItemActions'),
        ]);
    }

    public static function productionFields(): array
    {
        return [
            Select::make('manual_canvas_id')->label('Вид холста')->required()->options([
                1 => 'Эконом (S)', 2 => 'Интерьерный (S)', 3 => 'Синтетический (S)',
                4 => 'Хлопковый (C)', 5 => 'Глянцевый (G)',
            ]),
            Select::make('manual_gift_code')->label('Подарочная упаковка')->required()->options([
                'G0' => 'Обычная упаковка (G0)', 'G1' => 'Подарочная бумага (G1)', 'G2' => 'Эксклюзивная упаковка (G2)',
            ]),
            Select::make('manual_decoration_id')->label('Лак / мазки')->required()->options([
                5 => 'Стандарт. Без дополнений (L0, P0)', 1 => 'Арт-гель (L2, P0)',
                2 => 'Художественные мазки (L0, P1)', 3 => 'Даммарный лак (L1, P0)',
            ]),
            Select::make('manual_orientation_code')->label('Ориентация')->required()->options([
                'V0' => 'Авто по размеру (V0)', 'V1' => 'Вертикальная (V1)', 'V2' => 'Горизонтальная (V2)',
                'V3' => 'Квадрат (V3)', 'V4' => 'Панорама (V4)',
            ]),
            Select::make('manual_baget_code')->label('Оформление')->required()->options([
                'B0' => 'Без рамки (B0)', 'B1' => 'Рамка (B1)', 'B2' => 'На бумаге, в рамке (B2)',
            ]),
            Toggle::make('manual_express')->label('Экспресс'),
        ];
    }
}
