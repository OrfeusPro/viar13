<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Tables\OrderSearchFilters;
use App\Models\Orders;
use App\Services\Admin\OrderCustomerSearch;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class OrderSearchFiltersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Http::preventStrayRequests();
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('phone')->nullable();
            $table->integer('role_id')->nullable();
            $table->string('nick')->nullable();
            foreach (['email', 'first_name', 'last_name', 'address', 'postal_index', 'country'] as $column) {
                $table->string($column)->nullable();
            }
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('manager_id')->nullable();
            $table->text('delivery')->nullable();
            $table->text('items')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->string('status');
            $table->dateTime('created_at')->nullable();
        });
        Schema::create('order_payment_requests', function (Blueprint $table): void {
            $table->id();
            $table->integer('order_id');
            $table->string('public_number');
        });
        Schema::create('vr_numbers', function (Blueprint $table): void {
            $table->id();
            $table->integer('order_id');
            foreach (['vrv_1', 'vrv_2', 'vrv_3', 'vrv_4'] as $column) {
                $table->string($column)->nullable();
            }
        });
        DB::table('users')->insert([
            ['id' => 1, 'phone' => 'different'], ['id' => 2, 'phone' => '+37128888888'], ['id' => 3, 'phone' => 'different'],
        ]);
        DB::table('orders')->insert([
            ['id' => 1, 'user_id' => 1, 'delivery' => json_encode(['phone' => '20000000']), 'price' => 10, 'sale_price' => 8, 'status' => 'watching'],
            ['id' => 2, 'user_id' => 1, 'delivery' => json_encode(['phone' => 'different']), 'price' => 20, 'sale_price' => 10, 'status' => 'completed'],
            ['id' => 3, 'user_id' => 2, 'delivery' => json_encode(['phone' => 'different']), 'price' => 0, 'sale_price' => 0, 'status' => 'watching'],
            ['id' => 4, 'user_id' => 3, 'delivery' => json_encode(['phone' => 'different', 'comment' => '+37120000000']), 'price' => 10, 'sale_price' => 9, 'status' => 'watching'],
        ]);
        DB::table('order_payment_requests')->insert(['id' => 1, 'order_id' => 2, 'public_number' => 'OPR-000042']);
        DB::table('users')->where('id', 1)->update([
            'first_name' => 'Yuliya', 'last_name' => 'Petrova', 'email' => 'client1@example.test',
            'address' => 'Alpha Street 42', 'postal_index' => 'AA-123', 'country' => 'EE',
        ]);
        DB::table('orders')->where('id', 1)->update(['delivery' => json_encode([
            'phone' => '20000000', 'first_name' => 'Mariya', 'last_name' => 'Lazareva',
            'email' => 'delivery@example.test', 'address' => 'Delivery Street 9', 'postal_index' => 'D-11',
            'comment' => 'Exact delivery note', 'internal_tag' => 'OnlyMetadata',
        ])]);
        DB::table('vr_numbers')->insert(['order_id' => 2, 'vrv_1' => '123', 'vrv_2' => '456', 'vrv_3' => '789', 'vrv_4' => '321']);
    }

    public function test_phone_uses_delivery_phone_variants_and_returns_other_orders_of_the_same_client(): void
    {
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('phone', ['value' => '+371 20000000'])
            ->assertCanSeeTableRecords(Orders::findMany([1, 2]))
            ->assertCanNotSeeTableRecords(Orders::findMany([3, 4]));
        Http::assertNothingSent();
    }

    public function test_size_matches_each_of_the_first_ten_item_ids_without_matching_names_or_later_items(): void
    {
        DB::table('orders')->where('id', 2)->update(['items' => json_encode([['size_name' => '42']])]);
        DB::table('orders')->where('id', 3)->update(['items' => json_encode(array_merge(array_fill(0, 10, []), [['sizeId' => '42']]))]);
        DB::table('orders')->where('id', 4)->update(['items' => json_encode([['sizeId' => '142']])]);
        foreach (range(0, 9) as $index) {
            $items = array_fill(0, $index + 1, []);
            $items[$index] = ['sizeId' => '42'];
            DB::table('orders')->where('id', 1)->update(['items' => json_encode($items)]);
            Livewire::test(OrderSearchFiltersHarness::class)->filterTable('size', ['value' => '42'])
                ->assertCanSeeTableRecords(Orders::findMany([1]))
                ->assertCanNotSeeTableRecords(Orders::findMany([2, 3, 4]));
        }
        Http::assertNothingSent();
    }

    public function test_size_preserves_exact_value_and_combines_with_other_filters(): void
    {
        DB::table('orders')->whereIn('id', [1, 2])->update(['items' => json_encode([['sizeId' => '42']])]);
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('size', ['value' => '42'])
            ->filterTable('price', ['value' => 10])
            ->assertCanSeeTableRecords(Orders::findMany([1]))
            ->assertCanNotSeeTableRecords(Orders::findMany([2, 3, 4]));
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('size', ['value' => ' 42 '])
            ->assertCanNotSeeTableRecords(Orders::all());
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('size', ['value' => ''])
            ->assertCanSeeTableRecords(Orders::all());
    }

    public function test_size_supports_legacy_numeric_object_keys_and_zero_value(): void
    {
        DB::table('orders')->where('id', 1)->update(['items' => json_encode((object) [0 => ['sizeId' => '0']])]);
        DB::table('orders')->where('id', 2)->update(['items' => json_encode((object) [9 => ['sizeId' => '0']])]);
        DB::table('orders')->where('id', 3)->update(['items' => json_encode((object) [10 => ['sizeId' => '0']])]);
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('size', ['value' => '0'])
            ->assertCanSeeTableRecords(Orders::findMany([1, 2]))
            ->assertCanNotSeeTableRecords(Orders::findMany([3, 4]));
    }

    public function test_admin_manager_filter_excludes_manager_assignments_and_null_like_original(): void
    {
        DB::table('users')->where('id', 2)->update(['role_id' => 4, 'email' => 'manager@example.test']);
        DB::table('orders')->where('id', 1)->update(['manager_id' => 1]);
        DB::table('orders')->where('id', 2)->update(['manager_id' => 2]);
        DB::table('orders')->where('id', 4)->update(['manager_id' => 0]);
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('manager_id', '__admin__')
            ->assertCanSeeTableRecords(Orders::findMany([1, 4]))
            ->assertCanNotSeeTableRecords(Orders::findMany([2, 3]));
        Http::assertNothingSent();
    }

    public function test_specific_manager_filter_combines_with_price_and_empty_selection_keeps_rows(): void
    {
        DB::table('users')->where('id', 2)->update(['role_id' => 4, 'email' => 'manager@example.test']);
        DB::table('orders')->whereIn('id', [1, 2])->update(['manager_id' => 2]);
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('manager_id', '2')
            ->assertCanSeeTableRecords(Orders::findMany([1, 2]))
            ->assertCanNotSeeTableRecords(Orders::findMany([3, 4]))
            ->filterTable('price', ['value' => 10])
            ->assertCanSeeTableRecords(Orders::findMany([1]))
            ->assertCanNotSeeTableRecords(Orders::findMany([2, 3, 4]));
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('manager_id', null)
            ->assertCanSeeTableRecords(Orders::all());
    }

    public function test_admin_without_managers_retains_original_equality_branch(): void
    {
        $component = Livewire::test(OrderSearchFiltersHarness::class)->filterTable('manager_id', '__admin__');
        $query = $component->instance()->getFilteredTableQuery();
        $this->assertContains('all', $query->getBindings());
        $this->assertStringNotContainsString('is null', strtolower($query->toSql()));
        $component->assertCanNotSeeTableRecords(Orders::all());
    }

    public function test_creation_period_preserves_original_midnight_boundaries(): void
    {
        foreach ([1 => '2026-10-16 23:59:59', 2 => '2026-10-17 00:00:00', 3 => '2026-10-17 00:00:01', 4 => '2026-10-18 00:00:00'] as $id => $date) {
            DB::table('orders')->where('id', $id)->update(['created_at' => $date]);
        }
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('created_at', ['until' => '2026-10-17'])
            ->assertCanSeeTableRecords(Orders::findMany([1, 2]))
            ->assertCanNotSeeTableRecords(Orders::findMany([3, 4]));
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('created_at', ['from' => '2026-10-17'])
            ->assertCanSeeTableRecords(Orders::findMany([2, 3, 4]))
            ->assertCanNotSeeTableRecords(Orders::findMany([1]));
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('created_at', ['from' => '2026-10-17', 'until' => '2026-10-17'])
            ->assertCanSeeTableRecords(Orders::findMany([2]))
            ->assertCanNotSeeTableRecords(Orders::findMany([1, 3, 4]));
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('created_at', ['from' => null, 'until' => null])
            ->assertCanSeeTableRecords(Orders::all());
    }

    public function test_active_conditions_are_visible_and_removing_one_date_preserves_other_conditions(): void
    {
        DB::table('orders')->where('id', 1)->update(['created_at' => '2026-10-17 12:00:00']);
        DB::table('orders')->where('id', 4)->update(['created_at' => '2026-10-18 12:00:00']);
        $component = Livewire::test(OrderSearchFiltersHarness::class)
            ->filterTable('created_at', ['from' => '2026-10-17', 'until' => '2026-10-18'])
            ->filterTable('price', ['value' => 10])
            ->assertSee('Создан с: 17.10.2026')->assertSee('Создан по: 18.10.2026')->assertSee('Сумма: 10')
            ->assertCanSeeTableRecords(Orders::findMany([1]))
            ->assertCanNotSeeTableRecords(Orders::findMany([4]));
        $component->call('removeTableFilter', 'created_at', 'until')
            ->assertSet('tableFilters.created_at.from', '2026-10-17')
            ->assertSet('tableFilters.created_at.until', null)
            ->assertSet('tableFilters.price.value', 10)
            ->assertSee('Создан с: 17.10.2026')->assertDontSee('Создан по: 18.10.2026')
            ->assertCanSeeTableRecords(Orders::findMany([1, 4]))
            ->assertCanNotSeeTableRecords(Orders::findMany([2, 3]));
        $component->call('removeTableFilters')->assertCanSeeTableRecords(Orders::all());
    }

    public function test_zero_price_indicator_is_visible_and_blank_text_conditions_have_no_indicators(): void
    {
        $component = Livewire::test(OrderSearchFiltersHarness::class)
            ->filterTable('price', ['value' => 0])->assertSee('Сумма: 0');
        $component->call('removeTableFilter', 'price', 'value')->assertCanSeeTableRecords(Orders::all());
        foreach (['customer', 'phone', 'payment_request'] as $filter) {
            $component->filterTable($filter, ['value' => '  ']);
            $this->assertSame([], $component->instance()->getTable()->getFilter($filter)->getIndicators());
        }
    }

    public function test_indicators_of_multiple_value_filters_do_not_replace_each_other(): void
    {
        $component = Livewire::test(OrderSearchFiltersHarness::class)
            ->filterTable('customer', ['value' => 'Юлия'])
            ->filterTable('phone', ['value' => '20000000'])
            ->filterTable('price', ['value' => 10])
            ->filterTable('size', ['value' => '42'])
            ->filterTable('payment_request', ['value' => 'opr-00004']);
        $labels = array_map(fn ($indicator) => $indicator->getLabel(), $component->instance()->getTable()->getFilterIndicators());
        foreach (['Клиент: Юлия', 'Телефон: 20000000', 'Сумма: 10', 'Размер: 42', 'Заявка на оплату: OPR-00004'] as $label) {
            $this->assertContains($label, $labels);
            $component->assertSee($label);
        }
        $component->call('removeTableFilter', 'size', 'value')
            ->assertDontSee('Размер: 42')->assertSee('Сумма: 10')->assertSee('Клиент: Юлия');
    }

    public function test_phone_matches_user_phone_but_does_not_search_delivery_comments(): void
    {
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('phone', ['value' => '28888888'])
            ->assertCanSeeTableRecords(Orders::findMany([3]))
            ->assertCanNotSeeTableRecords(Orders::findMany([1, 2, 4]));
    }

    public function test_exact_price_only_matches_price_including_zero_not_sale_price(): void
    {
        $test = Livewire::test(OrderSearchFiltersHarness::class)->filterTable('price', ['value' => 10])
            ->assertCanSeeTableRecords(Orders::findMany([1, 4]))
            ->assertCanNotSeeTableRecords(Orders::findMany([2, 3]));
        $test->filterTable('price', ['value' => 0])
            ->assertCanSeeTableRecords(Orders::findMany([3]))
            ->assertCanNotSeeTableRecords(Orders::findMany([1, 2, 4]));
    }

    public function test_phone_price_and_status_are_combined_without_or_leaking_other_rows(): void
    {
        Livewire::test(OrderSearchFiltersHarness::class)
            ->filterTable('phone', ['value' => '+37120000000'])->filterTable('price', ['value' => 10])
            ->filterTable('status', 'watching')
            ->assertCanSeeTableRecords(Orders::findMany([1]))
            ->assertCanNotSeeTableRecords(Orders::findMany([2, 3, 4]));
    }

    public function test_payment_request_is_trimmed_uppercased_and_partial_matching_like_original(): void
    {
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('payment_request', ['value' => ' opr-00004 '])
            ->assertCanSeeTableRecords(Orders::findMany([2]))
            ->assertCanNotSeeTableRecords(Orders::findMany([1, 3, 4]));
    }

    public function test_empty_phone_and_price_do_not_filter_and_unknown_phone_returns_no_rows(): void
    {
        $test = Livewire::test(OrderSearchFiltersHarness::class)
            ->filterTable('phone', ['value' => '  '])->filterTable('price', ['value' => ''])
            ->assertCanSeeTableRecords(Orders::all());
        $test->filterTable('phone', ['value' => '99999999999'])->assertCanNotSeeTableRecords(Orders::all());
    }

    public function test_customer_matches_original_user_fields_and_cyrillic_transliteration(): void
    {
        DB::table('users')->where('id', 2)->update(['phone' => 'no-numeric-match']);
        foreach (['Юлия', 'Yul', 'Petro', 'client1@example.test', 'Alpha Street', 'AA-123', 'EE', '1'] as $term) {
            Livewire::test(OrderSearchFiltersHarness::class)->filterTable('customer', ['value' => $term])
                ->assertCanSeeTableRecords(Orders::findMany([1, 2]))
                ->assertCanNotSeeTableRecords(Orders::findMany([3, 4]));
        }
        Http::assertNothingSent();
    }

    public function test_delivery_matches_return_other_orders_of_the_same_client(): void
    {
        DB::table('users')->where('id', 2)->update(['phone' => 'no-numeric-match']);
        foreach (['Мария', 'Лазарева', 'delivery@example.test', 'Delivery Street 9', 'D-11', 'Exact delivery note', '20000000', '20', '8'] as $term) {
            Livewire::test(OrderSearchFiltersHarness::class)->filterTable('customer', ['value' => $term])
                ->assertCanSeeTableRecords(Orders::findMany([1, 2]))
                ->assertCanNotSeeTableRecords(Orders::findMany([3, 4]));
        }
    }

    public function test_customer_does_not_search_json_keys_metadata_or_partial_delivery_values(): void
    {
        foreach (['phone', 'OnlyMetadata', 'Delivery Street', 'delivery@example', 'not-found'] as $term) {
            Livewire::test(OrderSearchFiltersHarness::class)->filterTable('customer', ['value' => $term])
                ->assertCanNotSeeTableRecords(Orders::all());
        }
    }

    public function test_invoice_prefix_search_returns_only_the_specific_order_not_all_client_orders(): void
    {
        foreach (['VR00123', 'BAW456', 'VRR445789', 'DS020321', ' baw456 '] as $term) {
            Livewire::test(OrderSearchFiltersHarness::class)->filterTable('customer', ['value' => $term])
                ->assertCanSeeTableRecords(Orders::findMany([2]))
                ->assertCanNotSeeTableRecords(Orders::findMany([1, 3, 4]));
        }
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('customer', ['value' => 'BAW999'])
            ->assertCanNotSeeTableRecords(Orders::all());
    }

    public function test_customer_combines_with_other_filters_and_blank_search_keeps_rows(): void
    {
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('customer', ['value' => 'Юлия'])
            ->filterTable('price', ['value' => 10])->filterTable('status', 'watching')
            ->assertCanSeeTableRecords(Orders::findMany([1]))
            ->assertCanNotSeeTableRecords(Orders::findMany([2, 3, 4]));
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('customer', ['value' => '  '])
            ->assertCanSeeTableRecords(Orders::all());
    }

    public function test_shared_transliteration_keeps_original_h_y_sch_and_apostrophe_rules(): void
    {
        $this->assertSame("Schuka, Yuliya, Yakov, Hleb, mysh', pod'ezd", OrderCustomerSearch::transliterate('Щука, Юлия, Яков, Хлеб, мышь, подъезд'));
        $this->assertSame('Mariya / LV-1001', OrderCustomerSearch::transliterate('Мария / LV-1001'));
    }

    public function test_whitespace_payment_request_filter_does_not_limit_to_orders_with_requests(): void
    {
        Livewire::test(OrderSearchFiltersHarness::class)->filterTable('payment_request', ['value' => '  '])
            ->assertCanSeeTableRecords(Orders::all());
    }
}

class OrderSearchFiltersHarness extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table->query(Orders::query())->columns([TextColumn::make('id')])->filters([
            OrderSearchFilters::customer(), OrderSearchFilters::phone(), OrderSearchFilters::price(), OrderSearchFilters::paymentRequest(), OrderSearchFilters::size(), OrderSearchFilters::manager(), OrderSearchFilters::createdAt(),
            SelectFilter::make('status')->options(['watching' => 'В процессе', 'completed' => 'Завершён']),
        ]);
    }

    public function render(): string
    {
        return '<div>{{ $this->table }}</div>';
    }
}
