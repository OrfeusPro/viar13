<?php

namespace Tests\Feature\Admin;

use App\Models\Orders;
use App\Services\Admin\OrderInvoiceService;
use App\Services\Admin\UpdateOrderService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateOrderServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mock(OrderInvoiceService::class)->shouldReceive('refreshDocument')->andReturn('https://example.test/invoice.pdf')->byDefault();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('a_order_from', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manager_id')->nullable();
            $table->string('status');
            $table->string('payment_status');
            $table->string('payment')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('prepayment_price', 10, 2)->nullable();
            $table->text('admin_comment')->nullable();
            $table->text('delivery')->nullable();
            $table->text('items')->nullable();
            $table->text('comment')->nullable();
            $table->decimal('sale_eur', 10, 2)->nullable();
            $table->decimal('sale_percent', 10, 2)->nullable();
            $table->unsignedInteger('catid')->nullable();
            $table->unsignedInteger('a_order_from')->nullable();
            $table->string('approved_date')->nullable();
            $table->string('ur_name')->nullable();
            foreach (UpdateOrderService::COMPANY_FIELDS as $field) {
                $table->string($field)->nullable();
            }
            $table->timestamp('status_date')->nullable();
            $table->timestamp('watching_date')->nullable();
            $table->timestamp('pegging_date')->nullable();
            $table->timestamp('in_production_date')->nullable();
            $table->timestamp('send_date')->nullable();
            $table->timestamp('send_lubanas_date')->nullable();
            $table->timestamp('completed_date')->nullable();
            $table->timestamps();
        });
    }

    public function test_it_updates_allowed_admin_fields_and_status_dates_atomically(): void
    {
        DB::table('users')->insert(['id' => 7]);
        $orderId = DB::table('orders')->insertGetId([
            'status' => 'watching',
            'payment_status' => 'not_payed',
            'price' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = app(UpdateOrderService::class)->update(Orders::findOrFail($orderId), [
            'manager_id' => 7,
            'status' => 'in_production',
            'payment_status' => 'prepayment',
            'payment' => 'transfer',
            'price' => 120,
            'sale_price' => 110,
            'prepayment_price' => 50,
            'admin_comment' => 'Проверено',
        ]);

        $this->assertSame('in_production', $order->status);
        $this->assertNotNull($order->in_production_date);
        $this->assertNotNull($order->status_date);
        $this->assertSame(7, (int) $order->manager_id);
    }

    public function test_it_rejects_unknown_order_status_without_changing_order(): void
    {
        $orderId = DB::table('orders')->insertGetId([
            'status' => 'watching',
            'payment_status' => 'not_payed',
            'price' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            app(UpdateOrderService::class)->update(Orders::findOrFail($orderId), [
                'status' => 'invalid',
                'payment_status' => 'not_payed',
                'price' => 100,
            ]);
            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException) {
            $this->assertSame('watching', DB::table('orders')->where('id', $orderId)->value('status'));
        }
    }

    public function test_delivery_edit_preserves_extensions_items_and_normalizes_both_phones(): void
    {
        $order = $this->deliveryOrder();
        $items = $order->items;
        $updated = app(UpdateOrderService::class)->update($order, $this->updateData([
            'first_name' => 'Получатель', 'email' => 'recipient@example.test',
            'phone' => '+371 (20) 111-222', 'phone_rec' => '+371 (29) 333-444',
            'address' => 'Новый адрес', 'city' => 'Рига', 'country' => 'LV',
            'sposob' => 'to_the_door', 'when_send' => '2026-10-20', 'deliv_price' => 12.5,
            'comment' => '', 'ignored_upload_path' => 'changed.jpg',
        ]));
        $delivery = json_decode($updated->delivery, true);
        $this->assertSame('+37120111222', $delivery['payer_phone']);
        $this->assertSame('+37129333444', $delivery['phone']);
        $this->assertSame('Получатель', $delivery['first_name']);
        $this->assertSame('2026-10-20', $delivery['when_send']);
        $this->assertSame(12.5, $delivery['deliv_price']);
        $this->assertSame(['path' => 'orders/orig_images/keep.jpg'], $delivery['extension']);
        $this->assertArrayNotHasKey('ignored_upload_path', $delivery);
        $this->assertNull($delivery['delivery_photo_short_code']);
        $this->assertSame(1, $delivery['is_manual_express']);
        $this->assertSame('', $updated->comment);
        $this->assertSame($items, $updated->items);
    }

    public function test_cleared_recipient_phone_uses_payer_and_express_can_be_disabled(): void
    {
        $updated = app(UpdateOrderService::class)->update($this->deliveryOrder(), $this->updateData([
            'phone' => '20 111-222', 'phone_rec' => '', 'is_manual_express' => false,
        ]));
        $delivery = json_decode($updated->delivery, true);
        $this->assertSame('20111222', $delivery['phone']);
        $this->assertSame('20111222', $delivery['payer_phone']);
        $this->assertSame(0, $delivery['is_manual_express']);
        $this->assertSame('OLD', $delivery['delivery_photo_short_code']);
    }

    public function test_old_callers_without_delivery_payload_preserve_delivery_bytes(): void
    {
        $order = $this->deliveryOrder();
        $before = $order->delivery;
        $data = $this->updateData([]);
        unset($data['delivery_edit']);
        $updated = app(UpdateOrderService::class)->update($order, $data);
        $this->assertSame($before, $updated->delivery);
        $this->assertSame($order->items, $updated->items);
    }

    public function test_pickup_and_town_use_catalog_values_and_clear_previous_location(): void
    {
        Schema::create('delivery_pickup_at_viar_workshop', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('photo_short_code')->nullable();
        });
        Schema::create('a_delivery_towns', function (Blueprint $table): void {
            $table->id();
            $table->string('city');
            $table->string('photo_short_code')->nullable();
        });
        DB::table('delivery_pickup_at_viar_workshop')->insert([
            ['id' => 1, 'title' => 'Рига', 'photo_short_code' => ' R '],
            ['id' => 2, 'title' => 'Даугавпилс', 'photo_short_code' => ' D '],
        ]);
        DB::table('a_delivery_towns')->insert(['id' => 7, 'city' => 'Елгава', 'photo_short_code' => ' J ']);
        $order = $this->deliveryOrder();
        foreach (['pickup_Riga' => 1, 'pickup_Daugavplis' => 2, 'pickup_Daugavpils' => 2] as $method => $id) {
            $order = app(UpdateOrderService::class)->update($order, $this->updateData(['sposob' => $method]));
            $delivery = json_decode($order->delivery, true);
            $this->assertSame($id, $delivery['pickup_workshop_id']);
            $this->assertSame($id === 1 ? 'R' : 'D', $delivery['delivery_photo_short_code']);
            $this->assertSame($delivery['city'], $delivery['address']);
        }
        $order = app(UpdateOrderService::class)->update($order, $this->updateData([
            'sposob' => 'pickup_at_viar_workshop', 'pickup_workshop_id' => 1, 'address' => 'Untrusted',
        ]));
        $delivery = json_decode($order->delivery, true);
        $this->assertSame('Рига', $delivery['address']);
        $this->assertNull($delivery['city']);
        $order = app(UpdateOrderService::class)->update($order, $this->updateData([
            'sposob' => 'city_delivery', 'delivery_town_id' => 7, 'city' => 'Untrusted',
        ]));
        $delivery = json_decode($order->delivery, true);
        $this->assertSame('Елгава', $delivery['city']);
        $this->assertSame(7, $delivery['delivery_town_id']);
        $this->assertNull($delivery['pickup_workshop_id']);
        $this->assertSame('J', $delivery['delivery_photo_short_code']);
        $order = app(UpdateOrderService::class)->update($order, $this->updateData(['sposob' => 'venipak']));
        $delivery = json_decode($order->delivery, true);
        $this->assertNull($delivery['delivery_town_id']);
        $this->assertNull($delivery['delivery_photo_short_code']);
    }

    public function test_invalid_delivery_cannot_partially_change_status_or_price(): void
    {
        $order = $this->deliveryOrder();
        $before = $order->getAttributes();
        foreach ([['email' => 'invalid'], ['deliv_price' => -1], ['when_send' => '2026-02-30'],
            ['sposob' => 'unknown'], ['phone_rec' => ['invalid']], ['is_manual_express' => 'yes']] as $input) {
            try {
                app(UpdateOrderService::class)->update($order, array_replace($this->updateData($input), ['status' => 'completed', 'price' => 999]));
                $this->fail('Invalid delivery accepted.');
            } catch (ValidationException $exception) {
                $this->assertStringStartsWith('delivery_edit.', array_key_first($exception->errors()));
                $this->assertSame($before, $order->refresh()->getAttributes());
            }
        }
    }

    public function test_unknown_catalog_id_and_broken_json_are_rejected_without_writes(): void
    {
        Schema::create('a_delivery_towns', function (Blueprint $table): void {
            $table->id();
        });
        $order = $this->deliveryOrder();
        $before = $order->getAttributes();
        try {
            app(UpdateOrderService::class)->update($order, $this->updateData(['sposob' => 'city_delivery', 'delivery_town_id' => 999]));
            $this->fail('Missing town accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('delivery_edit.delivery_town_id', $exception->errors());
            $this->assertSame($before, $order->refresh()->getAttributes());
        }
        DB::table('orders')->where('id', $order->id)->update(['delivery' => '{broken']);
        try {
            app(UpdateOrderService::class)->update($order, $this->updateData(['first_name' => 'New']));
            $this->fail('Broken JSON overwritten.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('delivery_edit', $exception->errors());
            $this->assertSame('{broken', $order->refresh()->delivery);
        }
    }

    public function test_merge_uses_latest_json_instead_of_stale_form_record(): void
    {
        $order = $this->deliveryOrder();
        $delivery = json_decode($order->delivery, true);
        $delivery['extension']['new_key'] = 'concurrent';
        DB::table('orders')->where('id', $order->id)->update(['delivery' => json_encode($delivery)]);
        $updated = app(UpdateOrderService::class)->update($order, $this->updateData(['first_name' => 'New']));
        $this->assertSame('concurrent', json_decode($updated->delivery, true)['extension']['new_key']);
    }

    public function test_it_saves_discounts_company_and_parameters_without_recalculating_base_price(): void
    {
        DB::table('a_order_from')->insert(['id' => 8, 'title' => 'Offline']);
        $order = $this->deliveryOrder();
        $beforeDelivery = $order->delivery;
        $data = [
            'status' => 'watching', 'payment_status' => 'not_payed', 'price' => 100, 'sale_price' => 90,
            'manager_id' => 0, 'sale_eur' => 10, 'sale_percent' => 20, 'catid' => 2, 'a_order_from' => 8,
            'approved_date' => '16.10.2026 12:34', 'ur_name' => true,
            'ur_name_l' => 'Fixture Ltd', 'ur_reg_num' => '012345', 'ur_legal_addr' => 'Test address',
            'ur_pnr_nr' => 'LV000123', 'ur_bank_name' => 'Test bank', 'ur_bank_code' => 'TESTLV',
            'ur_bank_acc_code' => 'LV00TEST0000',
        ];
        $updated = app(UpdateOrderService::class)->update($order, $data);
        foreach (UpdateOrderService::COMPANY_FIELDS as $field) {
            $this->assertSame($data[$field], $updated->{$field});
        }
        $this->assertSame('on', $updated->ur_name);
        $this->assertSame(0, (int) $updated->manager_id);
        $this->assertSame(2, (int) $updated->catid);
        $this->assertSame(8, (int) $updated->a_order_from);
        $this->assertSame('16.10.2026 12:34', $updated->approved_date);
        $this->assertSame(10.0, (float) $updated->sale_eur);
        $this->assertSame(20.0, (float) $updated->sale_percent);
        $this->assertSame(90.0, (float) $updated->sale_price);
        $this->assertSame(100.0, (float) $updated->price);
        $this->assertSame($beforeDelivery, $updated->delivery);
        $this->assertSame($order->items, $updated->items);
    }

    public function test_date_blank_preserves_original_value_and_company_toggle_retains_requisites(): void
    {
        $order = $this->deliveryOrder();
        $order->forceFill(['approved_date' => '16.10.2026', 'ur_name' => 'on', 'ur_name_l' => 'Keep'])->save();
        $data = ['status' => 'watching', 'payment_status' => 'not_payed', 'price' => 100];
        foreach (['', null] as $date) {
            $updated = app(UpdateOrderService::class)->update($order, $data + ['approved_date' => $date, 'ur_name' => false]);
            $this->assertSame('16.10.2026', $updated->approved_date);
            $this->assertSame('', $updated->ur_name);
            $this->assertSame('Keep', $updated->ur_name_l);
        }
        $updated = app(UpdateOrderService::class)->update($order, $data + ['ur_name' => 'on', 'ur_name_l' => null]);
        $this->assertSame('on', $updated->ur_name);
        $this->assertSame('', $updated->ur_name_l);
    }

    public function test_old_callers_preserve_discounts_company_source_category_and_date(): void
    {
        $order = $this->deliveryOrder();
        $values = ['sale_eur' => 7, 'sale_percent' => 3, 'ur_name' => 'on',
            'ur_name_l' => 'Keep', 'a_order_from' => 15, 'catid' => 2, 'approved_date' => 'Legacy date'];
        $order->forceFill($values)->save();
        $before = $order->refresh()->getAttributes();
        $updated = app(UpdateOrderService::class)->update($order, [
            'status' => 'watching', 'payment_status' => 'not_payed', 'price' => 100,
        ]);
        foreach (array_keys($values) as $field) {
            $this->assertSame($before[$field], $updated->getRawOriginal($field));
        }
    }

    public function test_invalid_parameters_company_and_discounts_cannot_partially_update_order(): void
    {
        $order = $this->deliveryOrder();
        $before = $order->getAttributes();
        foreach ([['sale_eur' => -1], ['sale_percent' => 101], ['ur_name' => 'invalid'],
            ['ur_bank_acc_code' => ['array']], ['ur_name_l' => str_repeat('x', 256)],
            ['a_order_from' => 999], ['manager_id' => 999], ['catid' => -1], ['approved_date' => ['array']]] as $invalid) {
            try {
                app(UpdateOrderService::class)->update($order, $invalid + [
                    'status' => 'completed', 'payment_status' => 'payed', 'price' => 999,
                ]);
                $this->fail('Invalid parameter accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey(array_key_first($invalid), $exception->errors());
                $this->assertSame($before, $order->refresh()->getAttributes());
            }
        }
    }

    public function test_model_veto_is_reported_as_error_without_changing_order(): void
    {
        $order = $this->deliveryOrder();
        $before = $order->getAttributes();
        Orders::saving(fn () => false);
        try {
            app(UpdateOrderService::class)->update($order, $this->updateData(['first_name' => 'Changed']));
            $this->fail('Model veto reported success.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
            $this->assertSame($before, $order->refresh()->getAttributes());
        } finally {
            Orders::flushEventListeners();
        }
    }

    public function test_pdf_refresh_sees_saved_delivery_and_failure_rolls_back_edit(): void
    {
        $order = $this->deliveryOrder();
        $before = $order->getRawOriginal();
        $this->mock(OrderInvoiceService::class)->shouldReceive('refreshDocument')->once()
            ->withArgs(function (Orders $saved): bool {
                $this->assertSame('New address', json_decode($saved->fresh()->delivery, true)['address']);

                return true;
            })->andThrow(new \RuntimeException('PDF failed'));
        try {
            app(UpdateOrderService::class)->update($order, $this->updateData(['address' => 'New address']));
            $this->fail('PDF failure must reject the edit.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('PDF failed', $exception->getMessage());
        }
        $this->assertSame($before, $order->fresh()->getRawOriginal());
    }

    private function deliveryOrder(): Orders
    {
        return Orders::findOrFail(DB::table('orders')->insertGetId([
            'status' => 'watching', 'payment_status' => 'not_payed', 'price' => 100,
            'delivery' => json_encode(['sposob' => 'to_the_door', 'country' => 'LV', 'phone' => 'old',
                'delivery_photo_short_code' => 'OLD', 'extension' => ['path' => 'orders/orig_images/keep.jpg']]),
            'items' => json_encode([['image' => 'orders/keep.jpg', 'is_manual_express' => 'yes']]),
            'comment' => 'Before', 'created_at' => now(), 'updated_at' => now(),
        ]));
    }

    private function updateData(array $delivery): array
    {
        return ['status' => 'watching', 'payment_status' => 'not_payed', 'price' => 100, 'delivery_edit' => $delivery];
    }
}
