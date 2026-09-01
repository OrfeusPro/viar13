<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Tables\OrderLifecycleActions;
use App\Models\Orders;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\OrderLifecycleService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class OrderLifecycleServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['admin_migration.order_status_notifications_enabled' => false]);
        Mail::fake();

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('key');
            $table->timestamps();
        });
        Schema::create('permission_role', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });
        Schema::create('user_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('email');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('bonuses', 10, 2)->default(0);
            $table->json('settings')->nullable();
            $table->string('last_ip')->nullable();
            $table->text('registration_page')->nullable();
            $table->text('referrer_url')->nullable();
            $table->json('utm_parameters')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->default('watching');
            $table->timestamp('status_date')->nullable();
            $table->timestamp('watching_date')->nullable();
            $table->timestamp('pegging_date')->nullable();
            $table->timestamp('in_production_date')->nullable();
            $table->timestamp('send_date')->nullable();
            $table->timestamp('send_lubanas_date')->nullable();
            $table->timestamp('completed_date')->nullable();
            $table->text('delivery')->nullable();
            $table->boolean('use_bonus')->default(false);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->boolean('is_admin_order')->default(false);
            $table->string('photo')->nullable();
            $table->timestamps();
        });
        Schema::create('vr_numbers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
        });

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        view()->addNamespace('admin-tests', base_path('tests/Fixtures/views'));
    }

    public function test_status_and_delivery_follow_legacy_fields_without_sending_mail(): void
    {
        $order = Orders::query()->forceCreate([
            'status' => 'watching',
            'delivery' => json_encode(['city' => 'Riga', 'when_send' => '2026-09-10']),
        ]);

        $result = app(OrderLifecycleService::class)->updateStatus($order, 'in_production');
        $this->assertTrue($result['changed']);
        $this->assertTrue($result['notifications_suppressed']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'in_production']);
        $this->assertNotNull($order->fresh()->status_date);
        $this->assertNotNull($order->fresh()->in_production_date);

        $updated = app(OrderLifecycleService::class)->updateDeliveryDate($order, '2026-09-20');
        $this->assertSame(['city' => 'Riga', 'when_send' => '2026-09-20'], json_decode($updated->delivery, true));
        Mail::assertNothingSent();
    }

    public function test_invalid_lifecycle_values_do_not_change_order(): void
    {
        $order = Orders::query()->forceCreate(['status' => 'watching', 'delivery' => '{}']);

        foreach ([
            fn () => app(OrderLifecycleService::class)->updateStatus($order, 'unknown'),
            fn () => app(OrderLifecycleService::class)->updateDeliveryDate($order, '20.09.2026'),
        ] as $operation) {
            try {
                $operation();
                $this->fail('ValidationException was not thrown.');
            } catch (ValidationException) {
                $this->assertSame('watching', $order->fresh()->status);
                $this->assertSame([], json_decode($order->fresh()->delivery, true));
            }
        }
    }

    public function test_delete_refunds_legacy_bonus_and_removes_vr_number_atomically(): void
    {
        $user = User::query()->forceCreate(['email' => 'client@example.test', 'bonuses' => 4]);
        $order = Orders::query()->forceCreate([
            'user_id' => $user->id,
            'status' => 'watching',
            'use_bonus' => 1,
            'price' => 100,
            'sale_price' => 85,
        ]);
        DB::table('vr_numbers')->insert(['order_id' => $order->id]);

        $result = app(OrderLifecycleService::class)->delete($order);

        $this->assertSame(15.0, $result['refunded_bonus']);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('vr_numbers', ['order_id' => $order->id]);
        $this->assertSame(19.0, (float) $user->fresh()->bonuses);
    }

    public function test_column_renders_legacy_status_dates_delivery_actions_and_outgoing_label(): void
    {
        $user = User::query()->forceCreate(['email' => 'client@example.test']);
        $order = Orders::query()->forceCreate([
            'user_id' => $user->id,
            'status' => 'pegging',
            'status_date' => '2026-09-01 10:00:00',
            'pegging_date' => '2026-09-01 10:00:00',
            'delivery' => json_encode(['when_send' => '2026-09-20']),
            'photo' => 'https://example.test/label.pdf',
        ]);
        Orders::query()->forceCreate([
            'user_id' => 999,
            'status' => 'watching',
            'delivery' => json_encode(['email' => 'client@example.test']),
        ]);
        $order->forceFill(['delivery' => json_encode(['email' => 'client@example.test', 'when_send' => '2026-09-20'])])->save();
        $record = $order->fresh()->load('user');

        $html = view('filament.tables.columns.order-lifecycle', ['getRecord' => fn () => $record])->render();

        foreach (['Статус заказа:', 'В процессе', 'История статусов', 'номер заказа:', 'желаемая дата доставки:', 'Создать заказ', 'Просмотр клиента', 'Все заказы ранее', 'Исходящая накладная', 'Скачать'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringContainsString('ADM-FIL-010', $html);
        $this->assertStringContainsString('Другие активные заказы:', $html);
    }

    public function test_livewire_lifecycle_actions_enforce_voyager_permissions(): void
    {
        $order = Orders::query()->forceCreate(['status' => 'watching', 'delivery' => '{}']);
        $this->actingAs($this->admin(['edit_orders']), 'filament');
        Livewire::test(LifecycleTestTable::class)
            ->mountAction(TestAction::make('updateOrderStatus')->table($order))
            ->fillForm(['status' => 'pegging'])
            ->callMountedAction()
            ->assertHasNoErrors();
        $this->assertSame('pegging', $order->fresh()->status);

        $this->actingAs($this->admin(['read_orders']), 'filament');
        Livewire::test(LifecycleTestTable::class)
            ->assertActionHidden(TestAction::make('updateOrderStatus')->table($order))
            ->assertActionHidden(TestAction::make('deleteOrder')->table($order));

        $this->actingAs($this->admin(['delete_orders']), 'filament');
        Livewire::test(LifecycleTestTable::class)
            ->mountAction(TestAction::make('deleteOrder')->table($order))
            ->callMountedAction()
            ->assertHasNoErrors();
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    private function admin(array $permissions): User
    {
        $role = Role::query()->create(['name' => 'lifecycle-admin-'.Role::query()->count()]);
        foreach ($permissions as $key) {
            $role->permissions()->attach(Permission::query()->firstOrCreate(['key' => $key]));
        }

        return User::query()->forceCreate(['role_id' => $role->id, 'email' => 'admin-'.$role->id.'@example.test']);
    }
}

class LifecycleTestTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table->query(Orders::query())->columns([TextColumn::make('id')])
            ->recordActions([
                OrderLifecycleActions::updateStatus(),
                OrderLifecycleActions::updateDeliveryDate(),
                OrderLifecycleActions::viewClient(),
                OrderLifecycleActions::delete(),
            ]);
    }

    public function render()
    {
        return view('admin-tests::client-chat-table');
    }
}
