<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Orders;
use App\Services\Admin\OrderVenipakLabelService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Validation\ValidationException;

class OrderVenipakActions
{
    public static function create(): Action
    {
        return Action::make('createVenipakLabel')
            ->label('Создание этикетки')
            ->modalHeading(fn (Orders $record): string => 'Посылка для заказа: №'.$record->id)
            ->modalSubmitActionLabel('Внести данные')->modalWidth('7xl')
            ->extraModalWindowAttributes(['class' => 'viar-venipak-modal'])
            ->modalFooterActionsAlignment(Alignment::End)
            ->schema(OrderVenipakLabelForm::components())
            ->fillForm(fn (Orders $record): array => app(OrderVenipakLabelService::class)->defaultData($record))
            ->authorize(fn (Orders $record): bool => auth('filament')->user()?->can('update', $record) ?? false)
            ->action(function (Orders $record, array $data, Schema $schema): void {
                abort_unless(auth('filament')->user()?->can('update', $record), 403);
                try {
                    $labels = app(OrderVenipakLabelService::class)->create($record, $data);
                } catch (ValidationException $exception) {
                    self::notifyError($exception);
                    $errors = [];
                    foreach ($exception->errors() as $key => $messages) {
                        $errors[$schema->getStatePath().'.'.$key] = $messages;
                    }
                    throw ValidationException::withMessages($errors);
                }
                $record->refresh();
                Notification::make()->success()->title('Этикетка Venipak создана')
                    ->body('Номера: '.implode(', ', $labels))->send();
            });
    }

    public static function print(): Action
    {
        return Action::make('printVenipakLabel')->label('Печать этикетки')
            ->authorize(fn (Orders $record): bool => auth('filament')->user()?->can('update', $record) ?? false)
            ->action(function (Orders $record, array $arguments, Action $action) {
                abort_unless(auth('filament')->user()?->can('update', $record), 403);
                try {
                    $download = app(OrderVenipakLabelService::class)->print($record, (string) ($arguments['label'] ?? ''));
                } catch (ValidationException $exception) {
                    self::notifyError($exception);
                    $action->cancel();
                }

                return response()->streamDownload(static fn () => print ($download['content']), $download['filename'], [
                    'Content-Type' => 'application/pdf', 'Cache-Control' => 'no-store, no-cache',
                ]);
            });
    }

    private static function notifyError(ValidationException $exception): void
    {
        Notification::make()->danger()->title('Ошибка Venipak')
            ->body(collect($exception->errors())->flatten()->map(fn ($message): string => e((string) $message))->implode('<br>'))
            ->persistent()->send();
    }
}
