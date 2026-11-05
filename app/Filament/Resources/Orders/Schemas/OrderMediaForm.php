<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Filament\Resources\Orders\Tables\OrderArtistActions;
use App\Models\APainterImagesStatus;
use App\Models\Orders;
use App\Services\Admin\OrderLegacyMediaStatusService;
use App\Services\Admin\OrderMediaRemovalService;
use App\Support\Admin\OrderMediaUrl;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

class OrderMediaForm
{
    public static function section(): Section
    {
        return Section::make('Изображения заказа')->columnSpanFull()->schema([
            ...self::legacyControls('sketch', 'Наброски художника'),
            ...self::legacyControls('painting', 'Картины художника'),
            Placeholder::make('order_media')->key('mediaGallery')->label('Изображения заказа')->hiddenLabel()->registerActions([self::removeAction()])
                ->content(fn (Orders $record, Placeholder $component) => view('filament.orders.media-gallery', [
                    'groups' => self::groups($record),
                    'removeAction' => fn (string $token) => (clone $component->getAction('removeOrderMedia'))->arguments(['token' => $token]),
                ])),
        ])->footerActions([OrderArtistActions::manage()]);
    }

    private static function legacyControls(string $type, string $label): array
    {
        $prefix = $type === 'sketch' ? 'painter_sketch_images' : 'painter_images';
        $flag = $type === 'sketch' ? 'is_img_sketch' : 'is_img_painter';
        $visible = fn (Orders $record): bool => OrderMediaRemovalService::paths($record->{$prefix}) !== []
            && $record->order_painter_images->where($flag, 1)->isEmpty();

        return [
            Select::make($prefix.'_status')->label($label.' — общий статус')
                ->options(fn (): array => APainterImagesStatus::query()->orderBy('id')->pluck('title', 'id')->all())
                ->dehydrated(false)->validatedWhenNotDehydrated(false)->visible($visible)
                ->disabled(fn (Orders $record): bool => ! (auth('filament')->user()?->can('update', $record) ?? false))
                ->afterStateHydrated(fn (Select $component, Orders $record) => $component->state($record->{$prefix.'_status'}))
                ->live()->afterStateUpdated(function ($state, Orders $record, Select $component, $livewire) use ($type, $prefix): void {
                    abort_unless(auth('filament')->user()?->can('update', $record), 403);
                    try {
                        app(OrderLegacyMediaStatusService::class)->update($record, auth('filament')->user(), $type, $state);
                    } catch (ValidationException $exception) {
                        $record->refresh();
                        $component->state($record->{$prefix.'_status'});
                        throw ValidationException::withMessages([
                            $component->getStatePath() => collect($exception->errors())->flatten()->all(),
                        ]);
                    }
                    $livewire->resetValidation($component->getStatePath());
                    Notification::make()->success()->title('Общий статус изображений обновлён')->send();
                }),
            Placeholder::make($prefix.'_date_display')->label('Дата изменения статуса')
                ->visible($visible)->content(fn (Orders $record): string => (string) ($record->{$prefix.'_status_date'} ?: '—')),
        ];
    }

    public static function groups(Orders $record): array
    {
        $itemImages = [];
        $items = is_array($record->items) ? $record->items : (json_decode((string) $record->items, true) ?: []);
        foreach ($items as $item) {
            if (is_array($item)) {
                foreach ((array) ($item['orig_images'] ?? []) as $path) {
                    if (is_string($path) && ($url = OrderMediaUrl::resolve($path))) {
                        $itemImages[] = ['url' => $url, 'preview' => $url, 'status' => null, 'date' => null];
                    }
                }
            }
        }
        $groups = ['Фото клиента' => $itemImages];
        $images = $record->order_painter_images;
        $images->loadMissing('statusDefinition');
        foreach ([['Наброски художника', 'is_img_sketch', 'painter_sketch_images', 'sketch'], ['Картины художника', 'is_img_painter', 'painter_images', 'painting']] as [$label, $flag, $legacy, $type]) {
            $rows = $images->where($flag, 1);
            $groups[$label] = $rows->isNotEmpty()
                ? $rows->map(fn ($image) => [
                    'url' => OrderMediaUrl::resolve($image->image),
                    'preview' => OrderMediaUrl::resolve($image->small_image ?: $image->image),
                    'status' => $image->statusDefinition?->title,
                    'date' => $image->updated_at?->format('d.m.Y H:i'),
                    'removal' => self::token($record, ['type' => $type, 'row_id' => $image->id, 'fingerprint' => OrderMediaRemovalService::fingerprint($image->image)]),
                ])->filter(fn ($image) => $image['url'])->values()->all()
                : self::legacyImages($record->{$legacy}, $record, $type);
        }
        $groups['Дополнительные изображения клиента'] = self::legacyImages($record->client_images, $record, 'client');

        return $groups;
    }

    private static function legacyImages(?string $paths, Orders $record, string $type): array
    {
        return collect(OrderMediaRemovalService::paths($paths))->map(function ($path, $index) use ($paths, $record, $type) {
            $url = OrderMediaUrl::resolve($path);

            return $url ? ['url' => $url, 'preview' => $url, 'status' => null, 'date' => null,
                'removal' => self::token($record, ['type' => $type, 'index' => $index, 'fingerprint' => OrderMediaRemovalService::fingerprint($paths)]),
            ] : null;
        })->filter()->values()->all();
    }

    private static function token(Orders $record, array $target): string
    {
        return Crypt::encryptString(json_encode(['order_id' => $record->id] + $target, JSON_THROW_ON_ERROR));
    }

    private static function removeAction(): Action
    {
        return Action::make('removeOrderMedia')->label('Удалить из заказа')->icon('heroicon-o-trash')->iconButton()->color('danger')
            ->requiresConfirmation()->modalHeading('Удалить изображение из заказа?')
            ->modalDescription('Физический файл сохраняется, как в оригинальной админке.')
            ->authorize(fn (Orders $record): bool => auth('filament')->user()?->can('update', $record) ?? false)
            ->action(function (Orders $record, array $arguments, Action $action): void {
                abort_unless(auth('filament')->user()?->can('update', $record), 403);
                try {
                    $target = json_decode(Crypt::decryptString((string) ($arguments['token'] ?? '')), true, 512, JSON_THROW_ON_ERROR);
                } catch (DecryptException|\JsonException) {
                    Notification::make()->warning()->title('Обновите галерею перед удалением')->send();
                    $action->halt();

                    return;
                }
                abort_unless(is_array($target) && (int) ($target['order_id'] ?? 0) === (int) $record->id, 403);
                try {
                    app(OrderMediaRemovalService::class)->remove($record, auth('filament')->user(), $target);
                } catch (ValidationException $exception) {
                    Notification::make()->warning()->title('Изображение не удалено')->body(collect($exception->errors())->flatten()->first())->send();
                    $action->halt();

                    return;
                }
                Notification::make()->success()->title('Изображение удалено из заказа')->send();
            });
    }
}
