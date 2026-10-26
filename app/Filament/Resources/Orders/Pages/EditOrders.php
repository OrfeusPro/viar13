<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrdersResource;
use App\Models\Orders;
use App\Models\User;
use App\Services\Admin\OrderDeliveryEditor;
use App\Services\Admin\OrderItemEditor;
use App\Services\Admin\UpdateOrderService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOrders extends EditRecord
{
    protected static string $resource = OrdersResource::class;

    public function getTitle(): string
    {
        return 'Заказ №'.$this->getRecord()->getKey();
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Обновить');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['delivery_edit'] = app(OrderDeliveryEditor::class)->state($this->getRecord());
        $data['items_edit'] = app(OrderItemEditor::class)->state($this->getRecord());
        $data['manager_id'] = $data['manager_id'] ?? 0;
        if ($data['manager_id'] && ! User::query()->whereKey($data['manager_id'])->where('role_id', 4)->exists()) {
            $data['manager_id'] = 0;
        }
        $data['catid'] = $data['catid'] ?? 0;
        $data['sale_eur'] = $data['sale_eur'] ?: 0;
        $data['sale_percent'] = $data['sale_percent'] ?: 0;
        if (empty($data['approved_date']) && $this->getRecord()->created_at) {
            $data['approved_date'] = $this->getRecord()->created_at->format('d.m.Y H:i');
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Orders $record */
        return app(UpdateOrderService::class)->update($record, $data);
    }
}
