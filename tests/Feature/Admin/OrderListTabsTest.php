<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Tables\OrderSearchFilters;
use App\Models\Orders;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderListTabsTest extends TestCase
{
    public function test_tab_badges_hide_zero_counts_and_completed_total_and_all_tab_is_removed(): void
    {
        $tabs = (new ListOrders)->getTabs();
        $this->assertArrayNotHasKey('all', $tabs);
        $this->assertNull($tabs['completed']->getBadge());
        $this->assertSame('2', $tabs['production']->getBadge());
        $this->assertSame('2', $tabs['sent']->getBadge());
        DB::table('orders')->whereIn('status', ['in_production', 'sended'])->delete();
        $this->assertNull($tabs['production']->getBadge());
        $this->assertNull($tabs['sent']->getBadge());
        $this->assertNull($tabs['sent_today']->getBadge());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Http::preventStrayRequests();
        $this->travelTo(now()->setDate(2026, 10, 17)->setTime(12, 0));
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('status');
            $table->timestamp('created_at');
            $table->timestamp('send_date')->nullable();
        });
        DB::table('orders')->insert([
            ['id' => 1, 'status' => 'completed', 'created_at' => '2026-10-17 23:59:59', 'send_date' => null],
            ['id' => 2, 'status' => 'completed', 'created_at' => '2026-10-16 00:00:00', 'send_date' => null],
            ['id' => 3, 'status' => 'in_production', 'created_at' => '2026-10-17 10:00:00', 'send_date' => null],
            ['id' => 4, 'status' => 'in_production', 'created_at' => '2026-10-15 23:59:59', 'send_date' => null],
            ['id' => 5, 'status' => 'sended', 'created_at' => '2026-10-17 09:00:00', 'send_date' => '2026-10-17 11:00:00'],
            ['id' => 6, 'status' => 'sended', 'created_at' => '2026-10-16 08:00:00', 'send_date' => '2026-10-17 08:00:00'],
            ['id' => 7, 'status' => 'new', 'created_at' => '2026-10-18 00:00:00', 'send_date' => null],
        ]);
    }

    public function test_country_filter_uses_russian_translations_and_catalog_order_like_original(): void
    {
        Schema::create('country_tels', function (Blueprint $table): void {
            $table->id();
            $table->string('country_code');
            $table->string('country_name');
            $table->integer('sort');
        });
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name');
            $table->string('column_name');
            $table->integer('foreign_key');
            $table->string('locale');
            $table->text('value');
        });
        DB::table('country_tels')->insert([
            ['id' => 1, 'country_code' => 'LV', 'country_name' => 'Latvia', 'sort' => 2],
            ['id' => 2, 'country_code' => 'EE', 'country_name' => 'Estonia', 'sort' => 1],
            ['id' => 3, 'country_code' => 'FI', 'country_name' => 'Finland', 'sort' => 3],
        ]);
        DB::table('translations')->insert([
            ['table_name' => 'country_tels', 'column_name' => 'country_name', 'foreign_key' => 1, 'locale' => 'ru', 'value' => 'Латвия'],
            ['table_name' => 'country_tels', 'column_name' => 'country_name', 'foreign_key' => 2, 'locale' => 'ru', 'value' => 'Эстония'],
        ]);
        $this->assertSame(['EE' => 'Эстония', 'LV' => 'Латвия', 'FI' => 'Finland'], OrderSearchFilters::countryOptions());
        $this->assertSame('Latvia', DB::table('country_tels')->where('id', 1)->value('country_name'));
    }

    public function test_painter_filter_preserves_nick_email_and_catalog_order_without_other_roles(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->integer('role_id');
            $table->string('nick')->nullable();
            $table->string('email');
        });
        DB::table('users')->insert([
            ['id' => 10, 'role_id' => 3, 'nick' => '  Художник  ', 'email' => 'z@example.test'],
            ['id' => 11, 'role_id' => 3, 'nick' => null, 'email' => 'a@example.test'],
            ['id' => 12, 'role_id' => 4, 'nick' => 'Manager', 'email' => 'manager@example.test'],
        ]);
        $this->assertSame([10 => 'Художник - z@example.test', 11 => 'a@example.test'], OrderSearchFilters::painterOptions());
        $this->assertDatabaseCount('users', 3);
    }

    private function ids(string $tab, array $filters = []): array
    {
        $page = new OrderListTabsProbe;
        $page->activeTab = $tab;
        $page->tableFilters = $filters;

        return $page->scope(Orders::query())->pluck('id')->all();
    }

    public function test_recent_tab_uses_date_window_for_all_statuses_like_original(): void
    {
        $this->assertSame([6, 5, 3, 2, 1], $this->ids('new_recently'));
    }

    public function test_tab_default_sort_orders_match_original(): void
    {
        $this->assertSame([1, 2], $this->ids('completed'));
        $this->assertSame([4, 3], $this->ids('production'));
        $this->assertSame([6, 5], $this->ids('sent'));
        $this->assertSame([5, 6], $this->ids('sent_today'));
    }

    public function test_active_filter_bypasses_tab_status_and_priority_sort_like_order_filter(): void
    {
        $expected = [7, 1, 3, 5, 6, 2, 4];
        $this->assertSame($expected, $this->ids('completed', ['phone' => ['value' => '+37120000000']]));
        $this->assertSame($expected, $this->ids('current', ['price' => ['value' => 0]]));
        $this->assertSame([1, 2], $this->ids('completed', ['phone' => ['value' => '  '], 'price' => ['value' => null]]));
        Http::assertNothingSent();
    }

    public function test_explicit_number_sort_replaces_tab_order_without_removing_status_predicate(): void
    {
        $page = new OrderListTabsProbe;
        $page->activeTab = 'production';
        $this->assertSame([3, 4], $page->sorted('number_controls:asc')->pluck('id')->all());
        $this->assertSame([4, 3], $page->sorted('number_controls:desc')->pluck('id')->all());
        $this->assertSame([4, 3], $page->sorted(null)->pluck('id')->all());
        $this->assertSame([4, 3], $page->sorted('unknown:asc')->pluck('id')->all());
    }

    public function test_switching_tabs_clears_applied_and_pending_filters_search_sort_and_page(): void
    {
        $page = new OrderListTabsProbe;
        $page->prepareTabSwitch();
        $page->activeTab = 'completed';
        $page->tableFilters = ['price' => ['value' => 22]];
        $page->tableDeferredFilters = ['price' => ['value' => 44]];
        $page->tableSearch = 'old client';
        $page->tableColumnSearches = ['number_controls' => '18451'];
        $page->tableSort = 'number_controls:asc';
        $page->paginators['page'] = 3;

        $page->updatedActiveTab();

        $this->assertFalse($page->hasActiveOrderFilters());
        $this->assertSame($page->tableFilters, $page->tableDeferredFilters);
        $this->assertEmpty($page->tableSearch);
        $this->assertEmpty($page->tableColumnSearches);
        $this->assertNull($page->tableSort);
        $this->assertSame(1, $page->paginators['page']);
        $this->assertSame('completed', $page->activeTab);
        $this->assertSame([1, 2], $page->scope(Orders::query())->pluck('id')->all());
        $this->assertSame(13, $page->getTableRecordsPerPage());
        Http::assertNothingSent();
    }

    public function test_explicit_sort_with_filters_replaces_date_order_and_preserves_other_conditions(): void
    {
        $page = new OrderListTabsProbe;
        $page->activeTab = 'completed';
        $page->tableFilters = ['price' => ['value' => 0]];
        $this->assertSame([1, 2, 3, 4, 5, 6, 7], $page->sorted('number_controls:asc')->pluck('id')->all());
        $this->assertSame([7, 6, 5, 4, 3, 2, 1], $page->sorted('number_controls:desc')->pluck('id')->all());
        $this->assertSame([7, 1, 3, 5, 6, 2, 4], $page->sorted(null)->pluck('id')->all());
        $query = Orders::query()->where('id', '<', 4)->orderByRaw('FIELD(orders.id, 3, 1, 2)');
        $this->assertSame([1, 2, 3], $page->sortQuery($query, 'number_controls:asc')->pluck('id')->all());
        Http::assertNothingSent();
    }

    public function test_default_page_size_tracks_filter_mode_and_preserves_explicit_larger_sizes(): void
    {
        $page = new OrderListTabsProbe;
        $page->tableRecordsPerPage = 13;
        $this->assertSame(13, $page->getTableRecordsPerPage());
        $page->tableFilters = ['price' => ['value' => 0]];
        $this->assertSame(10, $page->getTableRecordsPerPage());
        $page->tableFilters = ['customer' => ['value' => '  ']];
        $this->assertSame(13, $page->getTableRecordsPerPage());
        foreach ([25, 50, 100] as $size) {
            $page->tableRecordsPerPage = $size;
            $page->tableFilters = ['size' => ['value' => '40x40']];
            $this->assertSame($size, $page->getTableRecordsPerPage());
            $page->tableFilters = [];
            $this->assertSame($size, $page->getTableRecordsPerPage());
        }
        $page->tableRecordsPerPage = '10';
        $this->assertSame(13, $page->getTableRecordsPerPage());
        $page->tableFilters = ['created_at' => ['from' => '2026-10-17', 'until' => null]];
        $this->assertSame(10, $page->getTableRecordsPerPage());
    }

    public function test_number_toggle_cycles_desc_asc_default_and_resets_page_without_changing_filters(): void
    {
        $page = new OrderListTabsProbe;
        $page->activeTab = 'completed';
        $filters = ['price' => ['value' => 0]];
        $page->tableFilters = $filters;
        $page->sortQuery(Orders::query(), null);
        foreach (['number_controls:desc', 'number_controls:asc', null] as $expected) {
            $page->paginators['page'] = 3;
            $page->sortTable('number_controls');
            $this->assertSame($expected, $page->tableSort);
            $this->assertSame(1, $page->paginators['page']);
            $this->assertSame($filters, $page->tableFilters);
            $this->assertSame('completed', $page->activeTab);
        }
        $page->sortTable('number_controls', 'asc');
        $this->assertSame('number_controls:asc', $page->tableSort);
        $page->sortTable('number_controls', 'desc');
        $this->assertSame('number_controls:desc', $page->tableSort);
        $page->sortTable('created_at');
        $this->assertSame('created_at:asc', $page->tableSort);
    }
}

class OrderListTabsProbe extends ListOrders
{
    public function prepareTabSwitch(): void
    {
        $this->table = Table::make($this)->query(Orders::query())
            ->columns([TextColumn::make('number_controls')->sortable(['id'])])
            ->filters([OrderSearchFilters::price()])->deferFilters();
        $this->tableRecordsPerPage = 13;
    }

    public function sorted(?string $sort): Builder
    {
        return $this->sortQuery($this->scope(Orders::query()), $sort);
    }

    public function sortQuery(Builder $query, ?string $sort): Builder
    {
        $this->table = Table::make($this)->columns([
            TextColumn::make('number_controls')->sortable(['id']),
        ]);
        $this->tableSort = $sort;

        return $this->applySortingToTableQuery($query);
    }

    public function scope(Builder $query): Builder
    {
        return $this->modifyQueryWithActiveTab($query);
    }
}
