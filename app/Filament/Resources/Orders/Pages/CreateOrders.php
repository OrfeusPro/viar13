<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrdersResource;
use App\Filament\Resources\Orders\Schemas\AdminOrderCreateForm;
use App\Models\Orders;
use App\Services\Admin\AdminOrderCreationService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class CreateOrders extends CreateRecord
{
    protected static string $resource = OrdersResource::class;

    protected static bool $canCreateAnother = false;

    public function mount(): void
    {
        parent::mount();

        $source = null;
        if ($sourceId = request()->integer('from_order_id')) {
            $source = Orders::query()->with('user')->findOrFail($sourceId);
            abort_unless(auth('filament')->user()?->can('view', $source), 403);
        }

        $this->form->fill(app(AdminOrderCreationService::class)->defaults($source));
    }

    public function form(Schema $schema): Schema
    {
        return AdminOrderCreateForm::configure($schema);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(AdminOrderCreationService::class)->create($data);
    }

    protected function getRedirectUrl(): string
    {
        return auth('filament')->user()?->can('view', $this->getRecord())
            ? OrdersResource::getUrl('view', ['record' => $this->getRecord()])
            : OrdersResource::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        $title = 'Заказ №'.$this->getRecord()->id.' создан.';

        return config('admin_migration.order_creation_notifications_enabled', false)
            ? $title
            : $title.' Внешние уведомления отключены для UAT.';
    }

    public function getTitle(): string|Htmlable
    {
        $sourceId = request()->integer('from_order_id');

        return $sourceId ? 'Создание заказа на основе №'.$sourceId : 'Создание заказа менеджером';
    }
}
