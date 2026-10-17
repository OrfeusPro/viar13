<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Schemas\OrderVenipakLabelForm;
use App\Filament\Resources\Orders\Schemas\OrderVenipakPickupForm;
use App\Services\Admin\VenipakPickupCatalog;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Testing\TestAction;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class VenipakPickupCatalogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Http::preventStrayRequests();
        Cache::flush();
        DatabaseSchema::create('venipak_data', function (Blueprint $table): void {
            $table->id();
            $table->string('user');
            $table->string('pass');
            $table->string('login_id');
            $table->boolean('is_active');
        });
        DB::table('venipak_data')->insert(['user' => 'fixture-user', 'pass' => 'fixture-secret', 'login_id' => '123', 'is_active' => true]);
    }

    private function fakeCatalog(): void
    {
        Http::fake([
            '*country=LV' => Http::response([
                ['id' => 10, 'type' => 1, 'city' => 'Rīga', 'address' => 'Street 1', 'zip' => 'LV-1001', 'display_name' => 'Riga shop', 'name' => 'Shop Ltd', 'company_code' => 'LV123'],
                ['id' => 11, 'type' => 2, 'city' => 'Liepāja', 'address' => 'Street 2', 'zip' => 'LV-2001', 'display_name' => 'Liepaja locker', 'code' => 'LOCK11'],
                ['id' => 12, 'type' => 1, 'city' => 'Rīga', 'address' => 'Street 3', 'zip' => 'LV-1003', 'display_name' => 'Another shop'],
                ['error' => 'bad row'],
            ]),
            '*country=EE' => Http::response([
                ['id' => 20, 'type' => 2, 'city' => 'Tallinn', 'address' => 'Street 20', 'zip' => '10101', 'display_name' => 'Tallinn shop'],
            ]),
        ]);
    }

    public function test_catalog_matches_original_all_types_city_order_labels_and_identity_fallbacks(): void
    {
        $this->fakeCatalog();
        $catalog = app(VenipakPickupCatalog::class);
        $points = $catalog->points(' lv ');
        $this->assertCount(3, $points);
        $this->assertSame(['Rīga' => 'Rīga', 'Liepāja' => 'Liepāja'], $catalog->cities('LV'));
        $this->assertSame(['Liepāja, Street 2 (Liepaja locker)'], array_values($catalog->options('LV', 'Liepāja')));
        $id = $catalog->match('LV', ' RIGA SHOP ');
        $this->assertSame($id, $catalog->match('LV', 'Street 1'));
        $this->assertSame($id, $catalog->match('LV', 'Rīga, Street 1 (Riga shop)'));
        $this->assertSame([
            'g_city_pickup' => 'Rīga', 'g_address_pickup' => 'Street 1', 'g_post_pickup' => 'LV-1001',
            'g_name_pickup' => 'Shop Ltd', 'g_code_pickup' => 'LV123',
        ], $catalog->selection('LV', $id));
        $locker = $catalog->selection('LV', $catalog->match('LV', 'Street 2'));
        $this->assertSame('Liepaja locker', $locker['g_name_pickup']);
        $this->assertSame('LOCK11', $locker['g_code_pickup']);
        $this->assertSame('12', $catalog->selection('LV', $catalog->match('LV', 'Street 3'))['g_code_pickup']);
        $this->assertSame([], $catalog->points('../invalid'));
        Http::assertSentCount(1);
        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && $request->url() === 'https://go.venipak.lt/ws/get_pickup_points?country=LV'
            && collect($request->data())->pluck('contents', 'name')->all() === [
                'user' => 'fixture-user', 'pass' => 'fixture-secret', 'login_id' => '123',
            ]);
    }

    public function test_failure_is_short_cached_and_can_recover_without_label_requests(): void
    {
        Http::fakeSequence()->push('bad response', 502)->push([
            ['id' => 1, 'city' => 'Rīga', 'address' => 'Street', 'zip' => 'LV-1', 'display_name' => 'Shop'],
        ]);
        $catalog = app(VenipakPickupCatalog::class);
        $this->assertSame([], $catalog->points('LV'));
        $this->assertSame([], $catalog->points('LV'));
        Http::assertSentCount(1);
        $this->travel(31)->seconds();
        $this->assertCount(1, $catalog->points('LV'));
        Http::assertSentCount(2);
    }

    public function test_form_autoselects_existing_address_then_filters_changes_country_and_dehydrates_identity(): void
    {
        $this->fakeCatalog();
        $catalog = app(VenipakPickupCatalog::class);
        $riga = $catalog->match('LV', 'Street 1');
        $liepaja = $catalog->match('LV', 'Street 2');
        $tallinn = $catalog->match('EE', 'Street 20');
        Livewire::test(VenipakPickupFormHarness::class)
            ->assertSet('data.pickup_point', $riga)
            ->assertSet('data.g_name_pickup', 'Shop Ltd')
            ->set('data.pickup_city', 'Liepāja')
            ->assertSet('data.pickup_point', $liepaja)
            ->assertSet('data.g_city_pickup', 'Liepāja')
            ->assertSet('data.g_code_pickup', 'LOCK11')
            ->set('data.r_country', 'EE')
            ->assertSet('data.pickup_city', null)
            ->assertSet('data.pickup_point', null)
            ->assertSet('data.g_address_pickup', '')
            ->set('data.pickup_point', $tallinn)
            ->assertSet('data.g_post_pickup', '10101')
            ->call('submit')->assertHasNoErrors()
            ->assertSet('submitted.g_name_pickup', 'Tallinn shop')
            ->assertSet('submitted.g_code_pickup', '20');
        Http::assertSentCount(2);
        $this->assertSame(1, DB::table('venipak_data')->count());
    }

    public function test_unmatched_point_remains_unselected_and_address_mode_does_not_require_catalog(): void
    {
        $this->fakeCatalog();
        Livewire::test(VenipakPickupFormHarness::class, ['destination' => 'address'])
            ->call('submit')->assertHasNoErrors();
        Http::assertNothingSent();
        Livewire::test(VenipakPickupFormHarness::class, ['address' => 'Unknown shop'])
            ->assertSet('data.pickup_point', null)
            ->assertSet('data.g_name_pickup', '')
            ->call('submit')->assertHasErrors(['data.pickup_point' => 'required']);
    }

    public function test_full_production_form_accepts_formatted_phones_and_submits_pickup_metadata(): void
    {
        $this->fakeCatalog();
        Livewire::test(VenipakPickupFormHarness::class, ['fullForm' => true])
            ->assertSee('Получатель')->assertSee('Отправитель')->assertSee('Упаковки')
            ->call('submit')->assertHasNoErrors()
            ->assertSet('submitted.g_address_pickup', 'Street 1')
            ->assertSet('submitted.g_name_pickup', 'Shop Ltd')
            ->assertSet('submitted.s_contact_t', '+371 27044470,')
            ->assertSet('submitted.g_contact_t', '+371 (20) 000-001');
        Http::assertSentCount(1);
    }

    public function test_add_package_copies_previous_weight_volume_and_pallet_like_original(): void
    {
        $this->fakeCatalog();
        $test = Livewire::test(VenipakPickupFormHarness::class, ['fullForm' => true]);
        $key = array_key_first($test->get('data.packages'));
        $test->set('data.packages.'.$key, ['weight' => 2.5, 'volume' => 0.4, 'pallet' => '3'])
            ->callAction(TestAction::make('add')->schemaComponent('packages', 'form'))
            ->call('submit')->assertHasNoErrors();
        $this->assertSame([
            ['weight' => 2.5, 'volume' => 0.4, 'pallet' => 3],
            ['weight' => 2.5, 'volume' => 0.4, 'pallet' => 3],
        ], $test->get('submitted.packages'));
        Http::assertSentCount(1);
    }

    public function test_insurance_quote_matches_original_and_is_not_submitted_to_provider(): void
    {
        $this->fakeCatalog();
        foreach (['' => '0 EUR', '10' => '0 EUR', '100' => '0.3 EUR', '500' => '1.5 EUR', '10000' => '30 EUR'] as $amount => $expected) {
            $this->assertSame($expected, OrderVenipakLabelForm::insurancePrice($amount));
        }
        $test = Livewire::test(VenipakPickupFormHarness::class, ['fullForm' => true])
            ->set('data.insur', '500')->assertSee('1.5 EUR')
            ->call('submit')->assertHasNoErrors();
        $this->assertArrayNotHasKey('insur', $test->get('submitted'));
        Http::assertSentCount(1);
    }

    public function test_forged_point_cannot_pass_selection_validation_or_keep_previous_identity(): void
    {
        $this->fakeCatalog();
        Livewire::test(VenipakPickupFormHarness::class)
            ->set('data.pickup_point', 'unavailable-id')
            ->assertSet('data.g_name_pickup', '')
            ->assertSet('data.g_code_pickup', '')
            ->call('submit')->assertHasErrors(['data.pickup_point']);
    }
}

class VenipakPickupFormHarness extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public array $data = [];

    public array $submitted = [];

    public bool $fullForm = false;

    public function mount(string $destination = 'pickup', string $address = 'Street 1', bool $fullForm = false): void
    {
        $this->fullForm = $fullForm;
        $this->form->fill([
            'destination' => $destination, 'r_country' => 'LV', 'g_address_pickup' => $address,
            'g_name' => 'Fixture recipient', 'g_contact_p' => 'FIXTURE (40x60)',
            'g_contact_t' => '+371 (20) 000-001', 'email_receiver' => 'recipient@example.test',
            's_name' => 'Fixture sender', 's_code' => '12345678', 's_country' => 'LV',
            's_city' => 'Rīga', 's_address' => 'Fixture sender street', 's_post' => 'LV-1001',
            's_contact_p' => 'Fixture sender', 's_contact_t' => '+371 27044470,', 'email_sender' => 'sender@example.test',
            'delivery_type' => 'nwd', 'delivery_express' => '0', 'cod_type' => 'EUR',
            'packages' => [['weight' => 1, 'volume' => null, 'pallet' => '0']],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components($this->fullForm ? OrderVenipakLabelForm::components() : [
            OrderVenipakPickupForm::destinationField(), OrderVenipakPickupForm::countryField(),
            ...OrderVenipakPickupForm::fields(),
        ])->statePath('data');
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
