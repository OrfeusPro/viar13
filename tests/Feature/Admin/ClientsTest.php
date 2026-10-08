<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\Clients;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class ClientsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Schema::create('users', function (Blueprint $table): void {
            $table->id(); $table->integer('role_id');
            foreach (['email','first_name','last_name','phone','address','postal_index','country','news'] as $field) { $table->string($field)->nullable(); }
        });
        Schema::create('orders', function (Blueprint $table): void { $table->id(); $table->integer('user_id'); });
        foreach ([[1,2,'alpha@example.invalid','LV','YES'],[2,2,'beta@example.invalid','DE','NO'],[3,1,'admin@example.invalid','LV','YES']] as [$id,$role,$email,$country,$news]) {
            DB::table('users')->insert(['id'=>$id,'role_id'=>$role,'email'=>$email,'country'=>$country,'news'=>$news,
                'first_name'=>'Fixture'.$id,'address'=>'Address'.$id,'postal_index'=>'ZIP'.$id]);
        }
        DB::table('orders')->insert([['user_id'=>1],['user_id'=>1],['user_id'=>3]]);
        $this->actingAs($this->actor(), 'filament');
    }

    private function actor(bool $edit = true, bool $orders = true, bool $browse = true): User
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->id = 101;
        $user->shouldReceive('hasPermission')->andReturnUsing(fn ($permission) => match ($permission) {
            'edit_users'=>$edit, 'browse_orders'=>$orders, 'browse_admin','browse_users'=>$browse, default=>false,
        });
        return $user;
    }

    public function test_clients_match_legacy_role_and_show_all_fields_and_exact_order_filter(): void
    {
        $page = Livewire::test(Clients::class)->assertSee('alpha@example.invalid')->assertSee('beta@example.invalid')
            ->assertDontSee('admin@example.invalid')->assertSee('Address1')->assertSee('ZIP1')->assertSee('Список (2)')->assertSee('Нет заказов');
        $records = $page->instance()->clients();
        $this->assertSame(2, $records->total());
        $this->assertSame(2, $records->firstWhere('id', 1)->orders_count);
        parse_str(parse_url($page->instance()->ordersUrl(1), PHP_URL_QUERY), $query);
        $this->assertSame('1', $query['filters']['user_id']['value']);
        $this->assertSame('all', $query['tab']);
        $ordersPage = new \App\Filament\Resources\Orders\Pages\ListOrders();
        $this->assertSame('current', $ordersPage->getDefaultActiveTab());
        $builder = $ordersPage->getTabs()['all']->modifyQuery(\App\Models\Orders::query());
        $this->assertSame(3, $builder->count());
        $header = new \ReflectionMethod($ordersPage, 'getHeaderActions');
        foreach ($header->invoke($ordersPage) as $action) {
            $this->assertInstanceOf(\Filament\Actions\Action::class, $action);
        }
    }

    public function test_search_country_subscription_intersect_and_exclude_staff(): void
    {
        Livewire::test(Clients::class)->set('country','LV')->set('subscription','YES')
            ->set('search','Address1')->assertSee('alpha@example.invalid')->assertDontSee('beta@example.invalid')
            ->set('search','admin')->assertSee('Клиенты не найдены')->assertDontSee('admin@example.invalid')
            ->set('country','')->set('subscription','')->set('search','2')->assertSee('beta@example.invalid');
    }

    public function test_sort_and_pagination_are_bounded_and_filters_reset_page(): void
    {
        for ($id=10;$id<25;$id++) { DB::table('users')->insert(['id'=>$id,'role_id'=>2,'email'=>'client'.$id.'@example.invalid']); }
        $page = Livewire::test(Clients::class)->set('perPage',10)->call('gotoPage',2);
        $this->assertSame(2, $page->instance()->clients()->currentPage());
        $page->set('search','alpha');
        $this->assertSame(1, $page->instance()->clients()->currentPage());
        $page->set('search','')->call('sortBy','email')->assertSet('direction','asc')
            ->call('sortBy','email')->assertSet('direction','desc');
        $page->call('sortBy','password')->assertStatus(422);
    }

    public function test_links_require_permissions_and_revocation_blocks_read(): void
    {
        $this->actingAs($this->actor(false,false), 'filament');
        $page = Livewire::test(Clients::class)->assertDontSee('/filament/bread/users/1/edit',false)
            ->assertDontSee('/filament/orders?',false)->assertSee('alpha@example.invalid');
        $this->actingAs($this->actor(false,false,false),'filament');
        $page->set('search','alpha')->assertForbidden();
        $this->assertFalse(Clients::canAccess());
    }
}
