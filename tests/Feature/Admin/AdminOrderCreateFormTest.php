<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Schemas\AdminOrderCreateForm;
use App\Services\Admin\AdminOrderCreationService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Testing\TestAction;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
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
        foreach (AdminOrderCreateForm::configure($schema)->getComponents() as $section) {
            if (! $section instanceof Section) {
                continue;
            }
            foreach ($section->getDefaultChildComponents() as $field) {
                if (in_array($field->getName(), ['sale_eur', 'sale_percent', 'items'], true)) {
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
