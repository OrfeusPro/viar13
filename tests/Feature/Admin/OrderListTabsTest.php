<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Orders;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderListTabsTest extends TestCase
{
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
}

class OrderListTabsProbe extends ListOrders
{
    public function scope(Builder $query): Builder
    {
        return $this->modifyQueryWithActiveTab($query);
    }
}
