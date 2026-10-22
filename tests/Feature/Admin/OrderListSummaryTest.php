<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Tables\OrderListSummary;
use App\Filament\Resources\Orders\Tables\OrderSearchFilters;
use App\Models\Orders;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class OrderListSummaryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Http::preventStrayRequests();
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2);
        });
        DB::table('orders')->insert([
            ['id' => 1, 'price' => 10.25, 'sale_price' => 1],
            ['id' => 2, 'price' => 10.25, 'sale_price' => 2],
            ['id' => 3, 'price' => 20, 'sale_price' => 3],
        ]);
        $this->actingAs($this->user('admin'), 'filament');
    }

    private function user(string $roleName): User
    {
        $role = new Role(['name' => $roleName]);
        $role->setRelation('permissions', collect([new Permission(['key' => 'browse_orders'])]));
        $user = new User;
        $user->id = 1;
        $user->setRelation('role', $role);

        return $user;
    }

    public function test_summary_uses_all_filtered_orders_not_current_page_or_sale_price(): void
    {
        Livewire::test(OrderListSummaryHarness::class)->assertSee('Заказов: 3 · Сумма: 40.5 €')
            ->filterTable('price', ['value' => 10.25])->assertSee('Заказов: 2 · Сумма: 20.5 €')
            ->call('gotoPage', 2)->assertSee('Заказов: 2 · Сумма: 20.5 €')
            ->filterTable('price', ['value' => 99])->assertSee('Заказов: 0 · Сумма: 0 €')
            ->call('removeTableFilters')->assertSee('Заказов: 3 · Сумма: 40.5 €');
        Http::assertNothingSent();
    }

    public function test_printing_and_lite_manager_never_request_summary_query(): void
    {
        foreach (['printing', 'lite_manager'] as $role) {
            $this->actingAs($this->user($role), 'filament');
            $table = Mockery::mock(HasTable::class);
            $table->shouldNotReceive('getFilteredTableQuery');
            $this->assertNull(OrderListSummary::forTable($table));
            Livewire::test(OrderListSummaryHarness::class)->assertDontSee('Сумма: 40.5')->assertDontSee('Заказов: 3');
        }
    }

    public function test_summary_does_not_change_original_query_or_keep_selected_columns_and_order(): void
    {
        $query = Orders::query()->select('orders.*')->selectRaw('123 AS unrelated')->where('id', '<', 3)->orderByDesc('id');
        $before = $query->toSql();
        $this->assertSame('Заказов: 2 · Сумма: 20.5 €', OrderListSummary::describe($query));
        $this->assertSame($before, $query->toSql());
        $this->assertSame([2, 1], $query->pluck('id')->all());
    }

    public function test_guest_and_user_without_browse_permission_do_not_request_totals(): void
    {
        $table = Mockery::mock(HasTable::class);
        $table->shouldNotReceive('getFilteredTableQuery');
        auth('filament')->forgetUser();
        $this->assertNull(OrderListSummary::forTable($table));
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 2;
        $user->setRelation('role', new Role(['name' => 'reader']));
        $user->shouldReceive('hasPermission')->with('browse_orders')->once()->andReturnFalse();
        $this->actingAs($user, 'filament');
        $this->assertNull(OrderListSummary::forTable($table));
    }
}

class OrderListSummaryHarness extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table->query(Orders::query())->columns([TextColumn::make('id')])
            ->description(fn ($livewire): ?string => OrderListSummary::forTable($livewire))
            ->filters([OrderSearchFilters::price()])->paginated([1])->defaultPaginationPageOption(1);
    }

    public function render(): string
    {
        return '<div>{{ $this->table }}</div>';
    }
}
