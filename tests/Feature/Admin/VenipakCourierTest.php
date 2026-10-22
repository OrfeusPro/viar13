<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Schemas\VenipakCourierAction;
use App\Models\User;
use App\Services\Admin\VenipakCourierService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class VenipakCourierTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Http::preventStrayRequests();
        Schema::create('venipak_data', function (Blueprint $table): void {
            $table->id();
            foreach (['user', 'pass', 'login_id', 'import_url', 's_name', 's_code', 's_country', 's_city', 's_address', 's_post', 's_contact_p', 's_contact_t', 'email_sender'] as $key) {
                $table->string($key)->nullable();
            }
            $table->boolean('is_active');
        });
        DB::table('venipak_data')->insert(['user' => 'fixture-user', 'pass' => 'fixture-password', 'login_id' => '123',
            'import_url' => 'https://venipak.example.test/import', 'is_active' => true, 's_name' => 'Sender & company',
            's_country' => 'LV', 's_city' => 'Riga', 's_address' => 'Fixture street', 's_post' => 'LV-1001', 'email_sender' => 'sender@example.test']);
        foreach (['roles' => 'name', 'permissions' => 'key'] as $name => $column) {
            Schema::create($name, function (Blueprint $table) use ($column): void {
                $table->id();
                $table->string($column);
            });
        }
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->integer('role_id');
            $table->string('email');
        });
        foreach (['permission_role' => ['permission_id', 'role_id'], 'user_roles' => ['user_id', 'role_id']] as $name => $columns) {
            Schema::create($name, function (Blueprint $table) use ($columns): void {
                foreach ($columns as $column) {
                    $table->integer($column);
                }
            });
        }
        DB::table('roles')->insert(['id' => 1, 'name' => 'manager']);
        DB::table('permissions')->insert(['id' => 1, 'key' => 'edit_orders']);
        DB::table('permission_role')->insert(['permission_id' => 1, 'role_id' => 1]);
        DB::table('users')->insert(['id' => 1, 'role_id' => 1, 'email' => 'manager@example.test']);
        $this->actingAs(User::findOrFail(1), 'filament');
    }

    private function data(): array
    {
        return array_replace(app(VenipakCourierService::class)->defaults(), ['svoris_s' => '12.5', 'metai_s' => '2026-10-20',
            'turis_s' => '0.3', 'pallets_s' => '2', 'pastabos_s' => 'Call <before> & collect', 'doknr_s' => 'DOC-1']);
    }

    public function test_sender_defaults_and_original_xml_contract_without_database_writes(): void
    {
        Http::fake(['venipak.example.test/*' => Http::response('<response type="success"><text>CALL-1</text><text>CALL-2</text></response>')]);
        $before = DB::table('venipak_data')->first();
        $service = app(VenipakCourierService::class);
        $this->assertSame('09', $service->defaults()['val_nuo_s']);
        $this->assertSame('17', $service->defaults()['val_iki_s']);
        $this->assertNull($service->defaults()['metai_s']);
        $this->assertSame(['CALL-1', 'CALL-2'], $service->send($this->data()));
        Http::assertSent(function ($request): bool {
            preg_match_all('/name="([^"]+)"\r\n(?:[^\r\n]+\r\n)*\r\n(.*?)\r\n--/s', $request->body(), $parts, PREG_SET_ORDER);
            $fields = array_column($parts, 2, 1);
            $this->assertNotEmpty($fields);
            $this->assertSame('POST', $request->method());
            $this->assertSame('fixture-user', $fields['user']);
            $this->assertSame('fixture-password', $fields['pass']);
            $xml = simplexml_load_string($fields['xml_text']);
            $this->assertSame('3', (string) $xml['type']);
            $this->assertSame('Sender & company', (string) $xml->sender->name);
            $this->assertSame('sender@example.test', (string) $xml->sender->contact_email);
            foreach (['weight' => '12.5', 'volume' => '0.3', 'pallets' => '2', 'date_y' => '2026', 'date_m' => '10', 'date_d' => '20',
                'hour_from' => '09', 'min_from' => '00', 'hour_to' => '17', 'min_to' => '00', 'comment' => 'Call <before> & collect', 'doc_no' => 'DOC-1'] as $key => $value) {
                $this->assertSame($value, (string) $xml->{$key});
            }

            return true;
        });
        Http::assertSentCount(1);
        $this->assertEquals($before, DB::table('venipak_data')->first());
    }

    public function test_invalid_fields_and_missing_configuration_never_send_requests(): void
    {
        foreach ([['svoris_s' => 0], ['metai_s' => 'invalid'], ['s_country' => 'XX'], ['val_nuo_s' => '18'],
            ['min_nuo_s' => '07'], ['pallets_s' => 41]] as $bad) {
            try {
                app(VenipakCourierService::class)->send(array_replace($this->data(), $bad));
                $this->fail('Invalid courier input accepted.');
            } catch (ValidationException) {
                Http::assertNothingSent();
            }
        }
        DB::table('venipak_data')->update(['is_active' => false]);
        $this->expectException(ValidationException::class);
        app(VenipakCourierService::class)->send($this->data());
    }

    public function test_rejected_and_uncertain_provider_responses_are_not_retried(): void
    {
        foreach ([['<response><error><text>Courier rejected</text></error></response>', 200], ['<response type="error"><text>Denied</text></response>', 200],
            ['<html>bad</html>', 200], ['malformed', 200], ['unavailable', 503]] as [$body, $status]) {
            Http::swap(new Factory);
            Http::preventStrayRequests();
            Http::fake(['venipak.example.test/*' => Http::response($body, $status)]);
            try {
                app(VenipakCourierService::class)->send($this->data());
                $this->fail('Bad provider response accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('venipak', $exception->errors());
                Http::assertSentCount(1);
            }
        }
    }

    public function test_actual_action_success_error_and_revoked_open_form(): void
    {
        Http::fake(['venipak.example.test/*' => Http::response('<response><text>CALL-1</text></response>')]);
        Livewire::test(CourierActionHarness::class)->mountAction('callVenipakCourier')->fillForm($this->data())
            ->callMountedAction()->assertHasNoErrors()->assertNotified('Вызов курьера принят');
        Http::assertSentCount(1);
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['venipak.example.test/*' => Http::response('<response><error><text>Rejected</text></error></response>')]);
        $page = Livewire::test(CourierActionHarness::class)->mountAction('callVenipakCourier')->fillForm($this->data());
        $page->callMountedAction()->assertHasErrors(['mountedActions.0.data.venipak'])->assertNotified('Ошибка Venipak');
        Http::assertSentCount(1);
        Http::fake();
        DB::table('permission_role')->delete();
        $this->actingAs(User::findOrFail(1), 'filament');
        $page->callMountedAction();
        Http::assertNothingSent();
        Livewire::test(CourierActionHarness::class)->assertActionHidden('callVenipakCourier');
    }

    public function test_printing_role_cannot_call_courier_even_with_edit_permission(): void
    {
        DB::table('roles')->update(['name' => 'printing']);
        $this->actingAs(User::findOrFail(1), 'filament');
        Livewire::test(CourierActionHarness::class)->assertActionHidden('callVenipakCourier');
        Http::assertNothingSent();
    }

    public function test_connection_failure_is_reported_without_retry_or_exposing_credentials(): void
    {
        $calls = 0;
        Http::fake(function () use (&$calls) {
            $calls++;
            throw new ConnectionException('Fixture transport failure');
        });
        try {
            app(VenipakCourierService::class)->send($this->data());
            $this->fail('Connection failure was accepted.');
        } catch (ValidationException $exception) {
            $message = implode(' ', $exception->errors()['venipak']);
            $this->assertStringContainsString('проверьте', $message);
            $this->assertStringNotContainsString('fixture-password', $message);
            $this->assertSame(1, $calls);
        }
    }
}

class CourierActionHarness extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions, InteractsWithSchemas;

    public function callVenipakCourierAction(): Action
    {
        return VenipakCourierAction::make();
    }

    public function render(): string
    {
        return '<div>{{ $this->callVenipakCourierAction }}<x-filament-actions::modals /></div>';
    }
}
