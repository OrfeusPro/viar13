<?php

namespace App\Filament\Bread;

use App\Models\User;
use App\Services\Admin\OrderReviewRequestService;
use App\Services\Admin\UserPainterAssignments;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

trait BreadUserActions
{
    private function actionClient(): User
    {
        $bread = $this->requireBread('edit');
        abort_unless($bread->name === 'users' && $this->editing && $this->recordId !== null, 403);
        return User::findOrFail($this->recordId);
    }

    public function requestClientReviewAction(): Action
    {
        return Action::make('requestClientReview')->label('Запросить отзыв')->icon('heroicon-o-envelope')
            ->visible(fn (): bool => $this->type === 'users' && $this->editing && $this->recordId !== null)
            ->requiresConfirmation()->modalHeading('Запросить отзыв у пользователя?')
            ->modalDescription(fn (): string => 'Получатель: '.$this->actionClient()->email.'.')
            ->action(function (): void {
                $result = app(OrderReviewRequestService::class)->sendToUser($this->actionClient());
                $notification = Notification::make()->title($result['suppressed'] ? 'Запрос проверен; внешняя почта отключена'
                    : ($result['sent'] ? 'Запрос отзыва отправлен' : 'Письмо не удалось отправить'));
                ($result['suppressed'] || $result['sent'] ? $notification->success() : $notification->warning())->send();
            });
    }

    public function painterAssignmentsVisible(): bool
    {
        return $this->type === 'users' && $this->editing && $this->recordId !== null
            && auth('filament')->user()?->hasPermission('edit_orders')
            && $this->actionClient()->role?->name === 'painter';
    }

    public function painterAssignmentsAction(): Action
    {
        return Action::make('painterAssignments')->label('Заказы художника')->icon('heroicon-o-paint-brush')
            ->visible(fn (): bool => $this->painterAssignmentsVisible())
            ->modalHeading('Назначения художника')->modalSubmitActionLabel('Обновить')
            ->modalDescription('Выбранные заказы будут назначены этому художнику. Пустой выбор снимает все его назначения.')
            ->schema([
                Select::make('orders')->label('Заказы')->multiple()->searchable()
                    ->options(fn (): array => app(UserPainterAssignments::class)->options(auth('filament')->user(), $this->actionClient()))
                    ->getSearchResultsUsing(fn (string $search): array => app(UserPainterAssignments::class)->options(auth('filament')->user(), $this->actionClient(), $search))
                    ->getOptionLabelsUsing(function (array $values): array {
                        $client = $this->actionClient();
                        app(UserPainterAssignments::class)->authorize(auth('filament')->user(), $client);
                        return DB::table('orders')->whereIn('id', $values)->pluck('id')->mapWithKeys(fn ($id): array => [$id => 'Заказ №'.$id])->all();
                    }),
            ])->fillForm(function (): array {
                $client = $this->actionClient();
                app(UserPainterAssignments::class)->authorize(auth('filament')->user(), $client);
                return ['orders' => DB::table('painter_orders')->where('user_id', $client->id)->pluck('order_id')->all()];
            })->action(function (array $data): void {
                app(UserPainterAssignments::class)->sync(auth('filament')->user(), $this->actionClient(), $data['orders'] ?? []);
                Notification::make()->success()->title('Назначения художника обновлены')->send();
            });
    }
}
