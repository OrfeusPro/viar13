<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Schemas\OrderDeliveryForm;
use App\Models\Orders;
use App\Services\Admin\OrderDeliveryEditor;
use App\Services\Admin\UpdateOrderService;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class OrderDeliveryFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Mail::fake();
        Http::preventStrayRequests();
        DatabaseSchema::create('delivery_pickup_at_viar_workshop', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('country_code')->nullable();
            $table->boolean('is_show');
            $table->integer('sort');
            $table->string('photo_short_code')->nullable();
        });
        DatabaseSchema::create('a_delivery_towns', function (Blueprint $table): void {
            $table->id();
            $table->string('city');
            $table->string('country')->nullable();
            $table->string('photo_short_code')->nullable();
        });
        DatabaseSchema::create('country_tels', function (Blueprint $table): void {
            $table->id();
            $table->string('country_code');
            $table->integer('sort');
        });
        DatabaseSchema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name');
            $table->string('column_name');
            $table->unsignedBigInteger('foreign_key');
            $table->string('locale');
            $table->text('value');
        });
        DB::table('delivery_pickup_at_viar_workshop')->insert([
            ['id' => 1, 'title' => 'Riga', 'country_code' => 'LV', 'is_show' => 1, 'sort' => 1],
            ['id' => 2, 'title' => 'Daugavpils', 'country_code' => 'LV', 'is_show' => 1, 'sort' => 2],
            ['id' => 3, 'title' => 'Tallinn', 'country_code' => 'EE', 'is_show' => 1, 'sort' => 3],
            ['id' => 4, 'title' => 'All', 'country_code' => ' ALL ', 'is_show' => 1, 'sort' => 4],
            ['id' => 5, 'title' => 'Hidden', 'country_code' => null, 'is_show' => 0, 'sort' => 5],
            ['id' => 6, 'title' => 'Multi', 'country_code' => 'EE, LV', 'is_show' => 1, 'sort' => 6],
        ]);
        DB::table('a_delivery_towns')->insert([
            ['id' => 7, 'city' => 'Jelgava', 'country' => 'LV'],
            ['id' => 8, 'city' => 'Tartu', 'country' => 'EE'],
            ['id' => 9, 'city' => 'Any', 'country' => ''],
        ]);
        DB::table('country_tels')->insert([['country_code' => 'LV', 'sort' => 1], ['country_code' => 'EE', 'sort' => 2]]);
        DB::table('translations')->insert([
            ['table_name' => 'delivery_pickup_at_viar_workshop', 'column_name' => 'title', 'foreign_key' => 1, 'locale' => 'ru', 'value' => 'Рига'],
            ['table_name' => 'a_delivery_towns', 'column_name' => 'city', 'foreign_key' => 7, 'locale' => 'ru', 'value' => 'Елгава'],
        ]);
    }

    public function test_catalogs_follow_original_country_visibility_order_and_russian_labels(): void
    {
        $service = app(OrderDeliveryEditor::class);
        $this->assertSame([1 => 'Рига', 2 => 'Daugavpils', 4 => 'All', 6 => 'Multi'], $service->pickups('lv'));
        $this->assertSame([3 => 'Tallinn', 4 => 'All', 6 => 'Multi'], $service->pickups('EE'));
        $this->assertSame([7 => 'Елгава', 9 => 'Any'], $service->towns('LV'));
        $this->assertSame([8 => 'Tartu', 9 => 'Any'], $service->towns('ee'));
        $this->assertSame(['LV' => 'LV', 'EE' => 'EE'], $service->countries());
    }

    public function test_form_method_country_and_select_changes_update_visible_address_without_writes(): void
    {
        Livewire::test(OrderDeliveryFormHarness::class)
            ->assertSee('Телефон заказчика')->assertSee('Телефон получателя')
            ->set('data.delivery_edit.sposob', 'pickup_at_viar_workshop')
            ->assertSet('data.delivery_edit.pickup_workshop_id', 1)
            ->assertSet('data.delivery_edit.address', 'Рига')
            ->assertSet('data.delivery_edit.city', null)
            ->set('data.delivery_edit.country', 'EE')
            ->assertSet('data.delivery_edit.pickup_workshop_id', 3)
            ->assertSet('data.delivery_edit.address', 'Tallinn')
            ->set('data.delivery_edit.pickup_workshop_id', 6)
            ->assertSet('data.delivery_edit.address', 'Multi')
            ->set('data.delivery_edit.sposob', 'city_delivery')
            ->assertSet('data.delivery_edit.pickup_workshop_id', null)
            ->assertSet('data.delivery_edit.delivery_town_id', 8)
            ->assertSet('data.delivery_edit.city', 'Tartu')
            ->set('data.delivery_edit.country', 'LV')
            ->assertSet('data.delivery_edit.delivery_town_id', 7)
            ->assertSet('data.delivery_edit.city', 'Елгава')
            ->set('data.delivery_edit.delivery_town_id', 9)
            ->assertSet('data.delivery_edit.city', 'Any')
            ->set('data.delivery_edit.sposob', 'pickup_Daugavpils')
            ->assertSet('data.delivery_edit.pickup_workshop_id', 2)
            ->assertSet('data.delivery_edit.delivery_town_id', null)
            ->set('data.delivery_edit.sposob', 'to_the_door')
            ->assertSet('data.delivery_edit.pickup_workshop_id', null)
            ->assertSet('data.delivery_edit.delivery_town_id', null)
            ->call('submit')->assertHasNoErrors()
            ->assertSet('submitted.delivery_edit.sposob', 'to_the_door');
        $this->assertSame(6, DB::table('delivery_pickup_at_viar_workshop')->count());
        Mail::assertNothingSent();
    }

    public function test_legacy_state_separates_phones_and_infers_catalog_ids_and_express(): void
    {
        $service = app(OrderDeliveryEditor::class);
        $order = new Orders;
        $order->forceFill(['delivery' => json_encode(['sposob' => 'pickup_at_viar_workshop',
            'address' => 'рига', 'country' => 'LV', 'phone' => 'recipient', 'payer_phone' => 'payer']),
            'items' => json_encode([['is_manual_express' => 'on']]), 'comment' => 'Order comment']);
        $state = $service->state($order);
        $this->assertSame(1, $state['pickup_workshop_id']);
        $this->assertSame('payer', $state['phone']);
        $this->assertSame('recipient', $state['phone_rec']);
        $this->assertTrue($state['is_manual_express']);
        $this->assertSame('Order comment', $state['comment']);
        $order->forceFill(['delivery' => json_encode(['sposob' => 'city_delivery', 'country' => 'LV', 'city' => 'Елгава', 'phone' => 'old']),
            'items' => json_encode([['content' => 'Email: fallback@example.test<br>Phone: fallback']])]);
        $state = $service->state($order);
        $this->assertSame(7, $state['delivery_town_id']);
        $this->assertSame('old', $state['phone']);
        $this->assertNull($state['phone_rec']);
        $this->assertSame('fallback@example.test', $state['email']);
        $order->forceFill(['delivery' => null]);
        $this->assertSame('fallback', $service->state($order)['phone']);
    }

    public function test_original_legacy_location_without_selector_retains_id_and_photo_code(): void
    {
        $service = app(OrderDeliveryEditor::class);
        foreach ([['sposob' => 'city_delivery', 'delivery_town_id' => 7],
            ['sposob' => 'pickup_at_viar_workshop', 'pickup_workshop_id' => 1]] as $location) {
            $order = new Orders;
            $order->forceFill(['delivery' => json_encode($location + ['delivery_photo_short_code' => 'KEPT']), 'items' => '[]']);
            $attributes = $service->attributes($order, ['sposob' => $location['sposob'], 'first_name' => 'Updated']);
            $delivery = json_decode($attributes['delivery'], true);
            foreach ($location as $key => $value) {
                $this->assertSame($value, $delivery[$key]);
            }
            $this->assertSame('KEPT', $delivery['delivery_photo_short_code']);
        }
    }

    public function test_production_form_submit_can_be_saved_and_refilled_without_losing_order_items(): void
    {
        DatabaseSchema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('status');
            $table->string('payment_status');
            $table->decimal('price', 10, 2);
            $table->text('delivery')->nullable();
            $table->text('items')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
        $order = Orders::query()->forceCreate([
            'status' => 'watching', 'payment_status' => 'not_payed', 'price' => 100,
            'delivery' => json_encode(['sposob' => 'to_the_door', 'country' => 'LV',
                'phone' => '+37120000000', 'extension' => ['image' => 'orders/keep.jpg']]),
            'items' => json_encode([['image' => 'orders/keep.jpg', 'name' => 'Keep']]),
        ]);
        $test = Livewire::test(OrderDeliveryFormHarness::class)
            ->set('data.delivery_edit', app(OrderDeliveryEditor::class)->state($order))
            ->set('data.delivery_edit.sposob', 'pickup_at_viar_workshop')
            ->set('data.delivery_edit.first_name', 'Получатель')
            ->set('data.delivery_edit.phone_rec', '+371 (29) 111-222')
            ->set('data.delivery_edit.deliv_price', 10)
            ->set('data.delivery_edit.when_send', '2026-10-20')
            ->call('submit')->assertHasNoErrors();
        $updated = app(UpdateOrderService::class)->update($order, $test->get('submitted') + [
            'status' => 'watching', 'payment_status' => 'not_payed', 'price' => 100,
        ]);
        $state = app(OrderDeliveryEditor::class)->state($updated);
        $this->assertSame(1, $state['pickup_workshop_id']);
        $this->assertSame('Riga', $state['address']); // Original server persists the base catalog title.
        $this->assertSame('Получатель', $state['first_name']);
        $this->assertSame('+37129111222', $state['phone_rec']);
        $this->assertSame($order->items, $updated->items);
        $this->assertSame(['image' => 'orders/keep.jpg'], json_decode($updated->delivery, true)['extension']);
        Livewire::test(OrderDeliveryFormHarness::class)
            ->set('data.delivery_edit', $state)->call('submit')->assertHasNoErrors()
            ->assertSet('submitted.delivery_edit.pickup_workshop_id', 1)
            ->assertSet('submitted.delivery_edit.when_send', '2026-10-20');
        Mail::assertNothingSent();
    }
}

class OrderDeliveryFormHarness extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public array $data = [];

    public array $submitted = [];

    public function mount(): void
    {
        $this->form->fill(['delivery_edit' => ['sposob' => 'to_the_door', 'country' => 'LV', 'is_manual_express' => false]]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([OrderDeliveryForm::section()])->statePath('data');
    }

    public function submit(): void
    {
        $this->submitted = $this->form->getState();
    }

    public function render(): string
    {
        return '<div>{{ $this->form }}</div>';
    }
}
