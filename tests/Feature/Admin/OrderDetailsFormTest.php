<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Pages\EditOrders;
use App\Filament\Resources\Orders\Schemas\OrderDetailsForm;
use App\Models\Orders;
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

class OrderDetailsFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Mail::fake();
        Http::preventStrayRequests();
        DatabaseSchema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->integer('role_id');
            $table->string('nick')->nullable();
            $table->string('email');
        });
        DatabaseSchema::create('a_order_from', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
        });
        DatabaseSchema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('manager_id')->nullable();
            $table->string('status');
            $table->string('payment_status');
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('sale_eur', 10, 2)->nullable();
            $table->decimal('sale_percent', 10, 2)->nullable();
            $table->unsignedInteger('a_order_from')->nullable();
            $table->unsignedInteger('catid')->nullable();
            $table->string('approved_date')->nullable();
            $table->string('ur_name')->nullable();
            foreach (UpdateOrderService::COMPANY_FIELDS as $field) {
                $table->string($field)->nullable();
            }
            $table->text('delivery')->nullable();
            $table->text('items')->nullable();
            $table->timestamps();
        });
        DatabaseSchema::create('gallery_items', function (Blueprint $table): void {
            $table->id();
            $table->integer('id_type');
            $table->string('name');
            $table->text('custom_size_prices')->nullable();
            $table->text('custom_size_prices_form2')->nullable();
            $table->text('custom_users_prices')->nullable();
        });
        foreach (['canvas_header', 'a_collage_head'] as $name) {
            DatabaseSchema::create($name, function (Blueprint $table) use ($name): void {
                $table->id();
                $table->string($name === 'canvas_header' ? 'c_right_top' : 'name');
                $table->text('sizes_30x40')->nullable();
                $table->text('sizes_38x38')->nullable();
            });
        }
        DatabaseSchema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name');
            $table->string('column_name');
            $table->unsignedBigInteger('foreign_key');
            $table->string('locale');
            $table->text('value');
        });
        DB::table('users')->insert([
            ['id' => 7, 'role_id' => 4, 'nick' => 'Manager', 'email' => 'manager@example.test'],
            ['id' => 8, 'role_id' => 2, 'nick' => 'Client', 'email' => 'client@example.test'],
        ]);
        DB::table('a_order_from')->insert([['id' => 3, 'title' => 'Site'], ['id' => 4, 'title' => 'Office']]);
        DB::table('gallery_items')->insert([
            ['id' => 10, 'id_type' => 5, 'name' => '<span>Portrait</span>'],
            ['id' => 11, 'id_type' => 2, 'name' => 'Excluded'],
        ]);
        DB::table('canvas_header')->insert(['id' => 5, 'c_right_top' => 'Canvas']);
        DB::table('a_collage_head')->insert(['id' => 6, 'name' => 'Collage']);
        DB::table('orders')->insert([
            'id' => 1, 'manager_id' => 0, 'status' => 'watching', 'payment_status' => 'not_payed',
            'price' => 100, 'sale_price' => 100, 'a_order_from' => 3, 'catid' => 0,
            'ur_name' => 'on', 'ur_name_l' => 'Initial company', 'delivery' => '{"country":"LV"}',
            'items' => '[{"image":"orders/keep.jpg"}]',
            'created_at' => '2026-10-10 12:34:00', 'updated_at' => '2026-10-10 12:34:00',
        ]);
    }

    public function test_real_fields_roundtrip_through_edit_page_hooks_preserving_price_delivery_and_items(): void
    {
        $before = Orders::findOrFail(1);
        $test = Livewire::test(OrderDetailsFormHarness::class)
            ->assertSet('data.manager_id', 0)
            ->assertSet('data.approved_date', '10.10.2026 12:34')
            ->assertSet('data.ur_name', true)
            ->assertSee('Manager — manager@example.test')->assertDontSee('client@example.test')
            ->assertSee('Portrait')->assertSee('Canvas')->assertSee('Collage')->assertDontSee('Excluded')
            ->set('data.sale_eur', 10)->set('data.sale_percent', 20)
            ->assertSet('data.sale_eur', 10)
            ->set('data.manager_id', 7)->set('data.catid', 2)->set('data.a_order_from', 4)
            ->set('data.ur_name_l', 'Edited company')->set('data.ur_reg_num', '000123')
            ->set('data.ur_legal_addr', 'Fixture address')->set('data.ur_pnr_nr', 'LV000')
            ->set('data.ur_bank_name', 'Fixture bank')->set('data.ur_bank_code', 'TESTLV')
            ->set('data.ur_bank_acc_code', 'LV00TEST')
            ->set('data.approved_date', '16.10.2026 15:45')->call('saveOrder')->assertHasNoErrors();
        $updated = Orders::findOrFail(1);
        $this->assertSame('on', $updated->ur_name);
        $this->assertSame('Edited company', $updated->ur_name_l);
        $this->assertSame('000123', $updated->ur_reg_num);
        $this->assertSame('LV00TEST', $updated->ur_bank_acc_code);
        $this->assertSame(7, (int) $updated->manager_id);
        $this->assertSame(2, (int) $updated->catid);
        $this->assertSame(4, (int) $updated->a_order_from);
        $this->assertSame(10.0, (float) $updated->sale_eur);
        $this->assertSame(20.0, (float) $updated->sale_percent);
        $this->assertSame(100.0, (float) $updated->sale_price);
        $this->assertSame($before->delivery, $updated->delivery);
        $this->assertSame($before->items, $updated->items);
        Livewire::test(OrderDetailsFormHarness::class)
            ->assertSet('data.approved_date', '16.10.2026 15:45')
            ->assertSet('data.ur_name', true)->set('data.ur_name', false)
            ->call('saveOrder')->assertHasNoErrors();
        $this->assertSame('', $updated->refresh()->ur_name);
        $this->assertSame('Edited company', $updated->ur_name_l);
        Mail::assertNothingSent();
    }

    public function test_missing_source_stays_hidden_and_clear_date_does_not_erase_previous_date(): void
    {
        DB::table('orders')->where('id', 1)->update(['a_order_from' => null, 'approved_date' => '12.10.2026']);
        Livewire::test(OrderDetailsFormHarness::class)->assertDontSee('Канал продаж')
            ->set('data.approved_date', '')->call('saveOrder')->assertHasNoErrors();
        $order = Orders::findOrFail(1);
        $this->assertNull($order->a_order_from);
        $this->assertSame('12.10.2026', $order->approved_date);
    }
}

class OrderDetailsEditPageProbe extends EditOrders
{
    public function prepare(Orders $order): array
    {
        $this->record = $order;

        return $this->mutateFormDataBeforeFill($order->attributesToArray());
    }

    public function persist(Orders $order, array $data): void
    {
        $this->handleRecordUpdate($order, $data);
    }
}

class OrderDetailsFormHarness extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public array $data = [];

    public Orders $order;

    public function mount(): void
    {
        $this->order = Orders::findOrFail(1);
        $this->form->fill(app(OrderDetailsEditPageProbe::class)->prepare($this->order));
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([OrderDetailsForm::manager(), OrderDetailsForm::parameters(), OrderDetailsForm::company()])
            ->record($this->order)->statePath('data');
    }

    public function saveOrder(): void
    {
        app(OrderDetailsEditPageProbe::class)->persist($this->order, $this->form->getState() + [
            'status' => $this->order->status, 'payment_status' => $this->order->payment_status, 'price' => $this->order->price,
        ]);
    }

    public function render(): string
    {
        return '<div>{{ $this->form }}</div>';
    }
}
