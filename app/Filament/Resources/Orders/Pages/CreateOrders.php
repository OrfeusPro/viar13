<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrdersResource;
use App\Filament\Resources\Orders\Schemas\AdminOrderCreateForm;
use App\Models\AOrderFrom;
use App\Models\CountryTel;
use App\Models\Orders;
use App\Services\Admin\AdminOrderCreationService;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class CreateOrders extends CreateRecord
{
    protected static string $resource = OrdersResource::class;

    protected static bool $canCreateAnother = false;

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Добавить');
    }

    public function mount(): void
    {
        parent::mount();

        $source = null;
        if ($sourceId = request()->integer('from_order_id')) {
            $source = Orders::query()->with('user')->findOrFail($sourceId);
            abort_unless(auth('filament')->user()?->can('view', $source), 403);
        }

        $defaults = app(AdminOrderCreationService::class)->defaults($source);
        // Voyager selects the first real option when the client has no country.
        if (! $source?->user?->country) {
            $defaults['country'] = CountryTel::query()->orderBy('sort')->value('country_code') ?: $defaults['country'];
        }
        $defaults['a_order_from'] = AOrderFrom::query()->orderBy('id')->value('id');
        $this->form->fill($defaults);
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
        $sourceId = $this->data['source_order_id'] ?? request()->integer('from_order_id');

        return $sourceId ? 'Новый заказ для клиента заказа №'.$sourceId : 'Создание заказа';
    }
}
