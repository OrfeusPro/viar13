<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Schemas\OrderVenipakActions;
use App\Models\Orders;
use App\Models\User;
use App\Services\Admin\OrderVenipakLabelService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Livewire;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class OrderVenipakActionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Http::preventStrayRequests();
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->string('email');
        });
        foreach (['roles' => 'name', 'permissions' => 'key'] as $name => $column) {
            Schema::create($name, function (Blueprint $table) use ($column): void {
                $table->id();
                $table->string($column);
            });
        }
        foreach (['permission_role' => ['permission_id', 'role_id'], 'user_roles' => ['user_id', 'role_id']] as $name => $columns) {
            Schema::create($name, function (Blueprint $table) use ($columns): void {
                foreach ($columns as $column) {
                    $table->unsignedBigInteger($column);
                }
            });
        }
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('labels')->nullable();
            $table->timestamps();
        });
        Orders::query()->forceCreate(['id' => 1, 'labels' => 'OLD-LABEL']);
        DB::table('roles')->insert(['id' => 1, 'name' => 'fixture-editor']);
        DB::table('permissions')->insert(['id' => 1, 'key' => 'edit_orders']);
        DB::table('permission_role')->insert(['permission_id' => 1, 'role_id' => 1]);
        DB::table('users')->insert(['id' => 1, 'role_id' => 1, 'email' => 'fixture@example.test']);
        $this->actingAs(User::findOrFail(1), 'filament');
    }

    private function service(): MockInterface
    {
        $service = Mockery::mock(OrderVenipakLabelService::class);
        $service->shouldReceive('defaultData')->andReturn([
            'destination' => 'address', 'r_country' => 'LV', 'g_name' => 'Fixture recipient', 'g_contact_p' => 'Fixture',
            'g_contact_t' => '+37120000000', 'g_city' => 'Rīga', 'g_address' => 'Fixture street', 'g_post' => 'LV-1',
            's_name' => 'Fixture sender', 's_code' => '123', 's_country' => 'LV', 's_city' => 'Rīga',
            's_address' => 'Fixture sender street', 's_post' => 'LV-2', 's_contact_p' => 'Sender', 's_contact_t' => '+37120000001',
            'delivery_type' => 'nwd', 'delivery_express' => '0', 'cod_type' => 'EUR',
            'packages' => [['weight' => 1, 'volume' => null, 'pallet' => '0']],
        ]);
        $this->app->instance(OrderVenipakLabelService::class, $service);

        return $service;
    }

    public function test_create_error_is_visible_and_modal_stays_open_without_success(): void
    {
        $this->service()->shouldReceive('create')->once()->andThrow(ValidationException::withMessages(['venipak' => 'Fixture provider failure']));
        Livewire::test(VenipakActionHarness::class)->mountAction('createVenipakLabel')
            ->callMountedAction()->assertHasErrors()
            ->assertActionMounted('createVenipakLabel');
        Notification::assertNotified('Ошибка Venipak');
        Notification::assertNotNotified('Этикетка Venipak создана');
        $this->assertSame('OLD-LABEL', Orders::find(1)->labels);
        Http::assertNothingSent();
    }

    public function test_create_success_uses_production_callback(): void
    {
        $this->service()->shouldReceive('create')->once()->andReturn(['FIXTURE-NEW']);
        Livewire::test(VenipakActionHarness::class)->callAction('createVenipakLabel')->assertHasNoErrors();
        Notification::assertNotified('Этикетка Venipak создана');
        Http::assertNothingSent();
    }

    public function test_print_download_uses_production_callback(): void
    {
        $service = $this->service();
        $service->shouldReceive('print')->once()->with(Mockery::type(Orders::class), 'OLD-LABEL')
            ->andReturn(['filename' => 'OLD-LABEL.pdf', 'content' => '%PDF-1.4 fixture']);
        Livewire::test(VenipakActionHarness::class)->callAction('printVenipakLabel', arguments: ['label' => 'OLD-LABEL'])
            ->assertFileDownloaded('OLD-LABEL.pdf', '%PDF-1.4 fixture');
        Http::assertNothingSent();
    }

    public function test_print_failure_notifies_without_downloading_a_file(): void
    {
        $this->service()->shouldReceive('print')->once()->andThrow(ValidationException::withMessages(['label' => 'Fixture PDF unavailable']));
        Livewire::test(VenipakActionHarness::class)->callAction('printVenipakLabel', arguments: ['label' => 'OLD-LABEL'])
            ->assertNoFileDownloaded();
        Notification::assertNotified('Ошибка Venipak');
        Http::assertNothingSent();
    }

    public function test_revoked_permission_blocks_create_and_print_before_service_call(): void
    {
        $service = $this->service();
        $service->shouldNotReceive('create');
        $service->shouldNotReceive('print');
        $test = Livewire::test(VenipakActionHarness::class)->mountAction('createVenipakLabel');
        DB::table('permission_role')->delete();
        $this->actingAs(User::findOrFail(1), 'filament');
        $test->callMountedAction();
        Livewire::test(VenipakActionHarness::class)->assertActionHidden('printVenipakLabel')
            ->call('mountAction', 'printVenipakLabel', ['label' => 'OLD-LABEL']);
        $this->assertSame('OLD-LABEL', Orders::find(1)->labels);
        Http::assertNothingSent();
    }
}

class VenipakActionHarness extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions, InteractsWithSchemas;

    public Orders $record;

    public function mount(): void
    {
        $this->record = Orders::findOrFail(1);
    }

    public function createVenipakLabelAction(): Action
    {
        return OrderVenipakActions::create()->record($this->record);
    }

    public function printVenipakLabelAction(): Action
    {
        return OrderVenipakActions::print()->record($this->record);
    }

    public function render(): string
    {
        return '<div>{{ $this->createVenipakLabelAction }}{{ $this->printVenipakLabelAction }}<x-filament-actions::modals /></div>';
    }
}
