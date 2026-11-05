<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Schemas\AdminOrderCreateForm;
use App\Services\Admin\AdminOrderCreationService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Testing\TestAction;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
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

class AdminOrderCreateFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Http::preventStrayRequests();
    }

    public function test_client_selection_and_email_lookup_show_current_bonus_limit_without_debiting(): void
    {
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        DatabaseSchema::create('users', function (Blueprint $table): void {
            $table->id();
            foreach (['email', 'first_name', 'last_name', 'phone', 'country', 'address', 'postal_index'] as $column) {
                $table->string($column)->nullable();
            }
            $table->decimal('bonuses', 10, 2)->default(0);
        });
        DB::table('users')->insert([
            ['id' => 1, 'email' => 'one@example.test', 'first_name' => 'One', 'last_name' => 'Client', 'phone' => '+37120000001', 'bonuses' => 12.5],
            ['id' => 2, 'email' => 'two@example.test', 'first_name' => 'Two', 'last_name' => 'Client', 'phone' => '+37120000002', 'bonuses' => 3],
        ]);
        $test = Livewire::test(AdminCreateBonusHarness::class)
            ->assertDontSee('Доступно бонусов:')
            ->set('data.client_id', 1)
            ->assertSet('data.email', 'one@example.test')->assertSet('data.recipient_phone', '+37120000001')
            ->assertSee('Доступно бонусов: 12.5')
            ->set('data.bonus', 13)->call('submit')->assertHasErrors(['data.bonus' => 'max'])
            ->set('data.bonus', 12.5)->call('submit')->assertHasNoErrors()
            ->set('data.email', 'TWO@example.test')->assertSet('data.client_id', 2)
            ->assertSet('data.first_name', 'Two')->assertSet('data.phone', '+37120000002')
            ->assertSet('data.recipient_phone', '+37120000002')->assertSee('Доступно бонусов: 3')
            ->call('submit')->assertHasErrors(['data.bonus' => 'max'])
            ->set('data.bonus', 3)->call('submit')->assertHasNoErrors();
        DB::table('users')->where('id', 2)->update(['bonuses' => 1]);
        $test->call('submit')->assertHasErrors(['data.bonus' => 'max'])
            ->set('data.email', 'new@example.test')->assertSet('data.client_id', null)
            ->assertDontSee('Доступно бонусов:')->assertSet('data.first_name', null)
            ->call('submit')->assertHasErrors(['data.first_name' => 'required'])
            ->set('data.first_name', 'New client')->call('submit')->assertHasNoErrors();
        $this->assertArrayNotHasKey('bonus', $test->get('submitted'));
        $this->assertSame(12.5, (float) DB::table('users')->where('id', 1)->value('bonuses'));
        $this->assertSame(1.0, (float) DB::table('users')->where('id', 2)->value('bonuses'));
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_discount_updates_clear_only_the_opposite_positive_discount(): void
    {
        Livewire::test(AdminOrderCreateFormHarness::class)
            ->set('data.sale_percent', 15)
            ->assertSet('data.sale_eur', 0)
            ->set('data.sale_eur', 20)
            ->assertSet('data.sale_percent', 0)
            ->set('data.sale_percent', 5)
            ->assertSet('data.sale_eur', 0)
            ->set('data.sale_eur', 0)
            ->assertSet('data.sale_percent', 5);

        Mail::assertNothingSent();
    }

    public function test_total_placeholder_uses_original_rounding_stages_and_discount_order(): void
    {
        foreach ([
            [[1.004], 0, 0, 0, 0.004, '1.00 €'],
            [[10.004, 10.004], 0, 0, 0, 0, '20.01 €'],
            [[100, 25], 5, 10, 0, 4, '114.00 €'],
            [[100, 25], 5, 0, 10, 4, '112.00 €'],
            [[10], 0, 50, 0, 4, '4.00 €'],
            [[10], 20, 0, 0, 4, '4.00 €'],
            [[100], 0, 0, 100, 4, '4.00 €'],
            [[1234.56, 20], 0, 0, 0, 4, '1 258.56 €'],
        ] as [$prices, $bonus, $eur, $percent, $delivery, $expected]) {
            $state = app(AdminOrderCreationService::class)->defaults();
            $state['items'] = array_map(fn ($price) => array_replace($state['items'][0], ['price' => $price]), $prices);
            $state = array_replace($state, ['bonus' => $bonus, 'sale_eur' => $eur, 'sale_percent' => $percent, 'delivery_price' => $delivery]);
            Livewire::test(AdminOrderCreateFormHarness::class)->set('data', $state)->assertSee($expected);
        }
        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    public function test_adding_a_position_uses_legacy_defaults_without_changing_existing_item(): void
    {
        $test = Livewire::test(AdminOrderCreateFormHarness::class);
        $initial = $test->get('data.items');
        $key = array_key_first($initial);
        $test->set('data.items.'.$key.'.gift_code', 'G2')
            ->callAction(TestAction::make('add')->schemaComponent('items', 'form'))
            ->assertHasNoErrors();

        $items = array_values($test->get('data.items'));
        $this->assertCount(2, $items);
        $this->assertSame('G2', $items[0]['gift_code']);
        foreach (['canvas_id' => 2, 'gift_code' => 'G0', 'decoration_id' => 5,
            'orientation_code' => 'V0', 'baget_code' => 'B0'] as $field => $expected) {
            $this->assertEquals($expected, $items[1][$field], $field);
        }
        Mail::assertNothingSent();
    }
}

class AdminOrderCreateFormHarness extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill(app(AdminOrderCreationService::class)->defaults());
    }

    public function form(Schema $schema): Schema
    {
        // Exercise production fields without unrelated catalog queries or order writes.
        $fields = [];
        $sections = collect(AdminOrderCreateForm::configure($schema)->getComponents())
            ->flatMap(fn ($component) => $component instanceof Group ? $component->getDefaultChildComponents() : [$component]);
        foreach ($sections as $section) {
            if (! $section instanceof Section) {
                continue;
            }
            foreach ($section->getDefaultChildComponents() as $field) {
                if (in_array($field->getName(), ['sale_eur', 'sale_percent', 'items', 'items_subtotal', 'order_total'], true)) {
                    $fields[] = $field;
                }
            }
        }

        return $schema->components($fields)->statePath('data');
    }

    public function render(): string
    {
        return '<div>{{ $this->form }}</div>';
    }
}

class AdminCreateBonusHarness extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public array $data = [];

    public array $submitted = [];

    public function mount(): void
    {
        $this->form->fill(app(AdminOrderCreationService::class)->defaults());
    }

    public function form(Schema $schema): Schema
    {
        $fields = [];
        $sections = collect(AdminOrderCreateForm::configure($schema)->getComponents())
            ->flatMap(fn ($component) => $component instanceof Group ? $component->getDefaultChildComponents() : [$component]);
        foreach ($sections as $section) {
            if (! $section instanceof Section) {
                continue;
            }
            foreach ($section->getDefaultChildComponents() as $field) {
                if (in_array($field->getName(), ['client_id', 'email', 'first_name', 'last_name', 'phone', 'recipient_phone', 'bonus'], true)) {
                    $fields[] = $field;
                }
            }
        }

        return $schema->components($fields)->statePath('data');
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
