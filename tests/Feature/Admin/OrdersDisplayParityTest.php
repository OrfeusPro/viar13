<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Orders;
use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrdersDisplayParityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('status');
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->json('settings')->nullable();
            $table->string('pdf_locale')->nullable();
            $table->integer('client_status')->nullable();
        });
        Schema::create('user_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('a_order_from', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
        });
        DB::table('user_types')->insert([
            ['id' => 1, 'name' => 'Новый'], ['id' => 2, 'name' => 'Бывалый'],
        ]);
        view()->addNamespace('admin-tests', base_path('tests/Fixtures/views'));
    }

    public function test_visible_number_column_is_searchable_without_hidden_columns_and_respects_scope(): void
    {
        DB::table('orders')->insert([
            ['id' => 18380, 'status' => 'watching'],
            ['id' => 18381, 'status' => 'watching'],
            ['id' => 28380, 'status' => 'completed'],
        ]);
        Livewire::test(OrderNumberSearchTable::class)
            ->searchTable('18380')->assertCountTableRecords(1)
            ->assertCanSeeTableRecords([Orders::findOrFail(18380)])
            ->assertCanNotSeeTableRecords([Orders::findOrFail(18381)])
            ->searchTable('8380')->assertCountTableRecords(1)
            ->searchTable('28380')->assertCountTableRecords(0)
            ->searchTable('no-such-number')->assertCountTableRecords(0)
            ->searchTable('')->assertCountTableRecords(2);
    }

    public function test_production_columns_remain_in_the_same_eight_column_order(): void
    {
        $component = Livewire::test(OrderNumberSearchTable::class)->instance();
        $table = OrdersTable::configure(Table::make($component));
        $names = collect($table->getColumns())
            ->filter(fn ($column): bool => ! $column->isHidden() && ! $column->isToggledHiddenByDefault())
            ->map(fn ($column): string => $column->getLabel())->values()->all();
        $this->assertSame(['Номер', 'Оплата', 'Пользователь', 'Получатель', 'Товар', 'Комментарии', 'Художник', 'Заказ'], $names);
    }

    #[DataProvider('locales')]
    public function test_voyager_locale_is_read_from_settings_without_a_locale_column(mixed $settings, ?string $expected): void
    {
        $id = DB::table('users')->insertGetId(['settings' => $settings === null ? null : json_encode($settings)]);
        $user = User::findOrFail($id);
        $original = $user->getAttributes();
        $this->assertSame($expected, $user->locale);
        $this->assertSame($expected, $user->preferredLocale());
        $this->assertSame($original, $user->getAttributes());
    }

    public static function locales(): array
    {
        return [
            'Russian' => [['locale' => 'ru'], 'ru'],
            'Latvian' => [['locale' => 'lv'], 'lv'],
            'unset' => [null, null],
            'no locale' => [['other' => 'keep'], null],
            'malformed locale' => [['locale' => ['ru']], null],
        ];
    }

    public function test_null_pdf_locale_and_client_status_match_legacy_without_writing_defaults(): void
    {
        [$user, $html] = $this->renderUser(['settings' => json_encode(['locale' => 'ru'])]);
        $this->assertStringContainsString('Язык пользователя: ru', $html);
        $this->assertSame('ru', $this->selected($html, 'Язык счета пользователя заказа №18380'));
        $this->assertSame('1', $this->selected($html, 'Статус клиента заказа №18380'));
        $this->assertNull($user->fresh()->pdf_locale);
        $this->assertNull($user->fresh()->client_status);
    }

    public function test_explicit_pdf_locale_and_client_status_override_display_defaults(): void
    {
        [, $html] = $this->renderUser([
            'settings' => json_encode(['locale' => 'ru']), 'pdf_locale' => 'pl', 'client_status' => 2,
        ]);
        $this->assertStringContainsString('Язык пользователя: ru', $html);
        $this->assertSame('pl', $this->selected($html, 'Язык счета пользователя заказа №18380'));
        $this->assertSame('2', $this->selected($html, 'Статус клиента заказа №18380'));
    }

    private function renderUser(array $attributes): array
    {
        $id = DB::table('users')->insertGetId($attributes);
        $user = User::findOrFail($id);
        $user->setAttribute('orders_count', 1);
        $order = (new Orders)->forceFill(['id' => 18380, 'user_id' => $id]);
        $order->setRelation('user', $user)->setRelation('manager', null);

        return [$user, view('filament.tables.columns.order-user', ['getRecord' => fn () => $order])->render()];
    }

    private function selected(string $html, string $label): string
    {
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$html);
            $xpath = new \DOMXPath($document);
            $options = $xpath->query('//select[@aria-label="'.$label.'"]/option[@selected]');
            $this->assertCount(1, $options);

            return $options->item(0)->getAttribute('value');
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}

class OrderNumberSearchTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        // Reuse the production column's search configuration; isolate unrelated relation queries and controls.
        $configured = OrdersTable::configure(Table::make($this));
        $number = $configured->getColumn('number_controls')->view('admin-tests::order-id-column');

        return $table->query(Orders::query()->where('status', 'watching'))->columns([$number]);
    }

    public function render()
    {
        return view('admin-tests::client-chat-table');
    }
}
