<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Orders;
use App\Services\Admin\OrderItemEditor;
use App\Services\Admin\OrderItemUploads;
use App\Support\Admin\OrderFileUploadLimits;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderItemActions
{
    public static function add(): Action
    {
        return Action::make('addOrderItem')->label('Добавить товар')->icon('heroicon-o-plus')
            ->modalHeading('Добавить товар')->modalSubmitActionLabel('Добавить')->modalWidth('3xl')
            ->schema([
                TextInput::make('name')->label('Название')->required()->maxLength(1000),
                TextInput::make('price')->label('Цена')->numeric()->minValue(0)->required(),
                TextInput::make('size')->label('Размер')->maxLength(255),
                TextInput::make('terms')->label('Изготовление (к-во дней)')->maxLength(255),
                ...OrderItemsForm::productionFields(),
                Textarea::make('userComment')->label('Комментарий пользователя')->maxLength(10000),
                self::files(false),
            ])
            ->fillForm(fn (): array => app(OrderItemEditor::class)->newState())
            ->authorize(fn (Orders $record): bool => auth('filament')->user()?->can('update', $record) ?? false)
            ->action(function (Orders $record, array $data, $livewire, Schema $schema): void {
                abort_unless(auth('filament')->user()?->can('update', $record), 403);
                try {
                    $updated = app(OrderItemEditor::class)->add($record, $data);
                } catch (ValidationException $exception) {
                    throw self::modalErrors($exception, $schema);
                }
                $existing = array_column($livewire->data['items_edit'] ?? [], 'index');
                foreach (app(OrderItemEditor::class)->state($updated) as $state) {
                    if (! in_array($state['index'], $existing, true)) {
                        $livewire->data['items_edit'][(string) Str::uuid()] = $state;
                    }
                }
                $livewire->record = $updated;
                $livewire->data['price'] = $updated->price;
                $livewire->data['sale_price'] = $updated->sale_price;
                Notification::make()->success()->title('Товар добавлен')->send();
            });
    }

    public static function attach(): Action
    {
        return Action::make('attachOrderItemImages')->label('Добавить фото')->button()->icon('heroicon-o-photo')
            ->modalHeading('Добавить фото к позиции')->modalSubmitActionLabel('Добавить фото')
            ->schema([self::files(true)->required()])
            ->authorize(fn (Orders $record): bool => auth('filament')->user()?->can('update', $record) ?? false)
            ->action(function (Orders $record, Repeater $component, array $arguments, array $data, $livewire, Schema $schema): void {
                abort_unless(auth('filament')->user()?->can('update', $record), 403);
                $key = $arguments['item'] ?? '';
                $states = $component->getState();
                abort_unless(is_string($key) && isset($states[$key]), 422);
                try {
                    $updated = app(OrderItemEditor::class)->attach($record, [
                        'index' => $states[$key]['index'], 'fingerprint' => $states[$key]['fingerprint'],
                        'files' => $data['files'] ?? [],
                    ]);
                } catch (ValidationException $exception) {
                    throw self::modalErrors($exception, $schema);
                }
                foreach (app(OrderItemEditor::class)->state($updated) as $state) {
                    if ($state['index'] === (int) $states[$key]['index']) {
                        // Attaching files must not submit or discard the item's other unsaved fields.
                        $states[$key]['fingerprint'] = $state['fingerprint'];
                        break;
                    }
                }
                $component->state($states);
                $record->setRawAttributes($updated->getAttributes(), true);
                $livewire->record = $updated;
                $livewire->forceRender();
                Notification::make()->success()->title('Фото добавлены')->send();
            });
    }

    private static function files(bool $attachments): FileUpload
    {
        return FileUpload::make('files')->label('Исходники')->multiple()->storeFiles(false)
            ->extraAlpineAttributes(OrderFileUploadLimits::alpineAttributes(OrderItemUploads::MAX_KB))
            ->acceptedFileTypes(OrderItemUploads::acceptedTypes($attachments))
            ->maxFiles(30)->maxSize(OrderItemUploads::MAX_KB);
    }

    private static function modalErrors(ValidationException $exception, Schema $schema): ValidationException
    {
        $messages = [];
        foreach ($exception->errors() as $field => $errors) {
            $target = $schema->getStatePath().'.'.(in_array($field, ['index', 'fingerprint'], true) ? 'files' : $field);
            $messages[$target] = [...($messages[$target] ?? []), ...$errors];
        }

        return ValidationException::withMessages($messages);
    }
}
