<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\OrdersResource;
use App\Filament\Resources\Orders\Pages\CreateOrders;
use App\Models\Orders;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\AdminOrderCreationService;
use App\Services\Admin\OrderItemPresentationService;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class AdminOrderCreationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['admin_migration.order_creation_notifications_enabled' => false]);
        Mail::fake();
        Http::preventStrayRequests();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_index')->nullable();
            $table->string('country')->nullable();
            $table->string('client_data')->nullable();
            $table->string('news')->nullable();
            $table->string('avatar')->nullable();
            $table->string('active_coupon')->nullable();
            $table->decimal('bonuses', 10, 2)->default(0);
            $table->json('settings')->nullable();
            $table->string('last_ip')->nullable();
            $table->text('registration_page')->nullable();
            $table->text('referrer_url')->nullable();
            $table->json('utm_parameters')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->boolean('is_admin_order')->default(false);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->decimal('sale_eur', 10, 2)->default(0);
            $table->decimal('sale_percent', 10, 2)->default(0);
            $table->longText('items');
            $table->string('country');
            $table->unsignedBigInteger('a_order_from')->nullable();
            $table->longText('delivery');
            $table->string('payment');
            $table->string('payment_status');
            $table->text('comment')->nullable();
            $table->text('admin_comment')->nullable();
            $table->unsignedBigInteger('catid')->default(0);
            $table->string('status');
            $table->string('order_image')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('use_bonus')->default(false);
            $table->string('ur_name')->nullable();
            $table->string('ur_name_l')->nullable();
            $table->string('ur_reg_num')->nullable();
            $table->string('ur_legal_addr')->nullable();
            $table->string('ur_pnr_nr')->nullable();
            $table->string('ur_bank_name')->nullable();
            $table->string('ur_bank_code')->nullable();
            $table->string('ur_bank_acc_code')->nullable();
            $table->timestamps();
        });
        foreach (['gallery_holsts', 'gallery_boxes', 'gallery_decorations'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_full_create_page_starts_fresh_then_creates_for_source_client_without_cloning_order(): void
    {
        $this->prepareCreatePage(['add_orders', 'read_orders']);
        DB::table('country_tels')->insert(['country_code' => 'NL', 'sort' => 0]);
        $client = User::query()->forceCreate([
            'email' => 'source@example.test', 'first_name' => 'Source client',
            'phone' => '+37129999999', 'country' => 'LV', 'address' => 'Client street', 'bonuses' => 20,
        ]);
        $source = app(AdminOrderCreationService::class)->create($this->validData([
            'email' => $client->email, 'payment_status' => 'payed', 'sale_eur' => 12,
            'items' => [['name' => 'Original portrait', 'price' => 100]],
        ]));
        $before = $source->getRawOriginal();
        $page = Livewire::withQueryParams(['from_order_id' => $source->id])->test(CreateOrders::class)
            ->assertSuccessful()
            ->assertSee('Новый заказ для клиента заказа №'.$source->id)
            ->assertFormSet([
                'email' => $client->email, 'first_name' => 'Source client',
                'client_id' => $client->id, 'source_order_id' => $source->id,
                'address' => 'Client street', 'payment' => 'cash_in_office',
                'country' => 'LV',
                'payment_status' => 'not_payed', 'bonus' => 0, 'sale_eur' => 0, 'sale_percent' => 0,
            ]);
        $items = array_values($page->get('data.items'));
        $this->assertCount(1, $items);
        $this->assertNotSame('Original portrait', $items[0]['name']);
        $this->assertEmpty($items[0]['existing_images']);

        $page->fillForm($this->validData([
            'email' => $client->email, 'source_order_id' => $source->id,
            'client_id' => $client->id, 'sale_eur' => 0,
            'items' => [['name' => 'New canvas', 'price' => 60]],
        ]))->assertSee('Новый заказ для клиента заказа №'.$source->id)
            ->call('create')->assertHasNoFormErrors()
            ->assertRedirect(OrdersResource::getUrl('create'));

        $new = Orders::query()->latest('id')->firstOrFail();
        $this->assertNotSame($source->id, $new->id);
        $this->assertSame($client->id, $new->user_id);
        $this->assertSame('New canvas', json_decode($new->items, true)[0]['name']);
        $this->assertSame(60.0, (float) $new->price);
        $this->assertSame($before, $source->fresh()->getRawOriginal());
        $this->assertSame(20.0, (float) $client->fresh()->bonuses);
        $this->assertDatabaseCount('orders', 2);
        $this->assertDatabaseCount('users', 2);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_full_create_page_uses_first_catalog_country_and_sales_channel_as_voyager(): void
    {
        $this->prepareCreatePage(['add_orders']);
        DB::table('country_tels')->insert(['country_code' => 'NL', 'sort' => 0]);
        DB::table('a_order_from')->insert([['id' => 7, 'title' => 'Later'], ['id' => 1, 'title' => 'Basket']]);
        Livewire::test(CreateOrders::class)->assertSuccessful()
            ->assertFormSet(['country' => 'NL', 'a_order_from' => 1]);
        $this->assertDatabaseCount('orders', 0);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_full_create_page_subtotal_excludes_bonus_discount_delivery_and_status_color_follows_state(): void
    {
        $this->prepareCreatePage(['add_orders']);
        $client = User::query()->forceCreate(['email' => 'totals@example.test', 'bonuses' => 20]);
        $data = $this->validData(['email' => $client->email, 'bonus' => 5, 'sale_eur' => 10, 'delivery_price' => 4]);
        $data['items'][] = array_replace($data['items'][0], ['name' => 'Second item', 'price' => 25]);
        $page = Livewire::test(CreateOrders::class)->fillForm($data)
            ->assertSee('Итого по товарам')->assertSee('125.00 €')
            ->assertSee('Итого к оплате')->assertSee('114.00 €')
            ->assertSee('data-payment-status="not_payed"', false)
            ->set('data.payment_status', 'prepayment')->assertSee('data-payment-status="prepayment"', false)
            ->set('data.payment_status', 'payed')->assertSee('data-payment-status="payed"', false);
        $firstKey = array_key_first($page->get('data.items'));
        $page->set('data.items.'.$firstKey.'.price', 30)
            ->assertSee('55.00 €')->assertSee('44.00 €');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(20.0, (float) $client->fresh()->bonuses);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_full_create_page_validation_does_not_create_an_empty_order(): void
    {
        $this->prepareCreatePage(['add_orders']);
        Livewire::test(CreateOrders::class)->assertSuccessful()
            ->assertFormSet(['email' => null, 'client_id' => null, 'source_order_id' => null])
            ->call('create')->assertHasFormErrors(['email', 'first_name']);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('users', 1);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_full_create_page_requires_add_permission(): void
    {
        $this->prepareCreatePage(['read_orders']);
        Livewire::test(CreateOrders::class)->assertForbidden();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_full_create_page_requires_read_permission_for_source_order(): void
    {
        $this->prepareCreatePage(['add_orders']);
        $source = app(AdminOrderCreationService::class)->create($this->validData());
        Livewire::withQueryParams(['from_order_id' => $source->id])->test(CreateOrders::class)->assertForbidden();
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_full_create_page_switching_email_does_not_keep_previous_client_identity(): void
    {
        $this->prepareCreatePage(['add_orders']);
        $first = User::query()->forceCreate([
            'email' => 'first@example.test', 'first_name' => 'First', 'last_name' => 'Client',
            'phone' => '+37120000001', 'country' => 'LV', 'address' => 'First street', 'bonuses' => 12,
        ]);
        $second = User::query()->forceCreate([
            'email' => 'second@example.test', 'first_name' => 'Second', 'last_name' => 'Client',
            'phone' => '+37120000002', 'country' => 'LV', 'bonuses' => 3,
        ]);
        $before = [$first->fresh()->getRawOriginal(), $second->fresh()->getRawOriginal()];
        Livewire::test(CreateOrders::class)
            ->set('data.client_id', $first->id)
            ->assertFormSet(['email' => $first->email, 'first_name' => 'First', 'recipient_phone' => $first->phone])
            ->assertSee('Доступно бонусов: 12')
            ->set('data.email', 'SECOND@example.test')
            ->assertFormSet(['client_id' => $second->id, 'first_name' => 'Second', 'recipient_phone' => $second->phone])
            ->assertSee('Доступно бонусов: 3')
            ->set('data.email', 'new@example.test')
            ->assertFormSet(['client_id' => null, 'first_name' => null, 'last_name' => null, 'phone' => null, 'recipient_phone' => null])
            ->assertDontSee('Доступно бонусов:')
            ->call('create')->assertHasFormErrors(['first_name']);
        $this->assertSame($before, [$first->fresh()->getRawOriginal(), $second->fresh()->getRawOriginal()]);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('users', 3);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_full_create_page_creates_new_client_and_stores_uploaded_source(): void
    {
        $this->prepareCreatePage(['add_orders', 'read_orders']);
        $data = $this->validData();
        $data['recipient_phone'] = '+371 (29) 999-999';
        $data['items'][0]['images'] = [UploadedFile::fake()->image('new-source.jpg')];
        Livewire::test(CreateOrders::class)->fillForm($data)
            ->call('create')->assertHasNoFormErrors()->assertRedirect();
        $order = Orders::query()->firstOrFail();
        $client = $order->user;
        $this->assertSame('new-client@example.test', $client->email);
        $this->assertSame('Client', $client->first_name);
        $this->assertSame('+37129999999', $client->phone);
        $this->assertNotEmpty($client->password);
        $this->assertSame('watching', $order->status);
        $delivery = json_decode($order->delivery, true);
        $this->assertSame('+37120000000', $delivery['payer_phone']);
        $this->assertSame('+37129999999', $delivery['phone']);
        $item = json_decode($order->items, true)[0];
        $this->assertCount(1, $item['orig_images']);
        $files = Storage::disk('uploads')->allFiles('orders');
        $this->assertCount(1, $files);
        $this->assertStringEndsWith($files[0], $item['orig_images'][0]);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('users', 2);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_full_create_page_missing_source_returns_not_found_without_writes(): void
    {
        $this->prepareCreatePage(['add_orders', 'read_orders']);
        try {
            Livewire::withQueryParams(['from_order_id' => 99999])->test(CreateOrders::class);
            $this->fail('Missing source was accepted.');
        } catch (ModelNotFoundException $exception) {
            $this->assertSame(Orders::class, $exception->getModel());
            $this->assertSame([99999], $exception->getIds());
        }
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_full_create_page_revoked_add_permission_blocks_open_form_submission(): void
    {
        $this->prepareCreatePage(['add_orders']);
        $page = Livewire::test(CreateOrders::class)->fillForm($this->validData());
        DB::table('permission_role')->where('permission_id', Permission::query()->where('key', 'add_orders')->value('id'))->delete();
        auth('filament')->user()->unsetRelations();
        $page->call('create')->assertForbidden();
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('users', 1);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    private function prepareCreatePage(array $permissions): void
    {
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Storage::fake('uploads');
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('key');
            $table->timestamps();
        });
        foreach (['permission_role' => ['permission_id', 'role_id'], 'user_roles' => ['user_id', 'role_id']] as $name => $fields) {
            Schema::create($name, function (Blueprint $table) use ($fields): void {
                foreach ($fields as $field) {
                    $table->unsignedBigInteger($field);
                }
            });
        }
        Schema::table('users', fn (Blueprint $table) => $table->string('nick')->nullable());
        Schema::create('country_tels', function (Blueprint $table): void {
            $table->id();
            $table->string('country_code');
            $table->integer('sort');
        });
        DB::table('country_tels')->insert(['country_code' => 'LV', 'sort' => 1]);
        Schema::create('a_order_from', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
        });
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name');
            $table->string('column_name');
            $table->unsignedBigInteger('foreign_key');
            $table->string('locale');
            $table->text('value');
        });
        Schema::create('gallery_items', function (Blueprint $table): void {
            $table->id();
            $table->integer('id_type');
            $table->string('name');
            foreach (['custom_size_prices', 'custom_size_prices_form2', 'custom_users_prices'] as $field) {
                $table->text($field)->nullable();
            }
        });
        foreach (['canvas_header' => 'c_right_top', 'a_collage_head' => 'name'] as $name => $label) {
            Schema::create($name, function (Blueprint $table) use ($label): void {
                $table->id();
                $table->string($label);
                $table->text('sizes_30x40')->nullable();
                $table->text('sizes_38x38')->nullable();
            });
            DB::table($name)->insert(['id' => 1, $label => $name]);
        }
        $role = Role::query()->create(['name' => 'create-page-operator']);
        foreach (['browse_admin', 'browse_orders', ...$permissions] as $key) {
            $role->permissions()->attach(Permission::query()->create(['key' => $key]));
        }
        $this->actingAs(User::query()->forceCreate(['role_id' => $role->id, 'email' => 'operator@example.test']), 'filament');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_source_order_prefills_only_client_data_as_in_voyager(): void
    {
        $user = User::query()->forceCreate([
            'email' => 'client@example.test', 'first_name' => 'Client', 'phone' => '+371 20-00',
            'country' => 'EE', 'address' => 'Client address', 'bonuses' => 20,
        ]);
        $source = Orders::query()->forceCreate([
            'user_id' => $user->id,
            'manager_id' => 9,
            'items' => json_encode([
                ['name' => 'Portrait', 'price' => 42, 'sizeId' => '40x60', 'terms' => 'Экспресс - 1 сутки',
                    'savedImage' => 'https://viarcanvas.com/orders/source.jpg',
                    'canvasId' => 4, 'boxIds' => [2], 'decorationId' => 2,
                    'formId' => 2, 'manual_baget_code' => 'B1', 'effectId' => 7],
                'totalPrice' => 42,
            ]),
            'country' => 'LV',
            'delivery' => json_encode(['city' => 'Riga', 'phone' => '+3712999', 'sposob' => 'venipak', 'deliv_price' => 5, 'bonus' => 10]),
            'payment' => 'paypalOnetimePayment',
            'payment_status' => 'payed',
            'price' => 42,
            'sale_price' => 42,
            'sale_eur' => 3,
            'sale_percent' => 0,
            'status' => 'pegging',
        ]);

        $defaults = app(AdminOrderCreationService::class)->defaults($source->load('user'));

        $this->assertSame($source->id, $defaults['source_order_id']);
        $this->assertSame($user->id, $defaults['client_id']);
        $this->assertNull($defaults['manager_id']);
        $this->assertSame('client@example.test', $defaults['email']);
        $this->assertSame('to_the_door', $defaults['delivery_method']);
        $this->assertSame('cash_in_office', $defaults['payment']);
        $this->assertSame('not_payed', $defaults['payment_status']);
        $this->assertSame(0.0, $defaults['bonus']);
        $this->assertSame(0.0, $defaults['sale_eur']);
        $this->assertSame('EE', $defaults['country']);
        $this->assertSame('Client address', $defaults['address']);
        $this->assertSame($user->phone, $defaults['recipient_phone']);
        $this->assertSame(app(AdminOrderCreationService::class)->defaults()['items'], $defaults['items']);
        $this->assertSame(20.0, (float) $user->fresh()->bonuses);
        $this->assertSame('payed', $source->fresh()->payment_status);
    }

    public function test_existing_client_order_uses_legacy_shape_and_debits_bonus_atomically_without_notifications(): void
    {
        $user = User::query()->forceCreate([
            'email' => 'client@example.test', 'first_name' => 'Client', 'bonuses' => 15,
            'settings' => ['locale' => 'ru'],
        ]);
        $data = $this->validData(['email' => $user->email, 'bonus' => 5]);

        $order = app(AdminOrderCreationService::class)->create($data);

        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('watching', $order->status);
        $this->assertSame(1, (int) $order->is_admin_order);
        $this->assertSame(95.0, (float) $order->price);
        $this->assertSame(10.0, (float) $user->fresh()->bonuses);
        $delivery = json_decode($order->delivery, true);
        $this->assertSame(5, $delivery['bonus']);
        $this->assertSame('+37120000000', $delivery['payer_phone']);
        $basket = json_decode($order->items, true);
        $this->assertSame('Portrait', $basket[0]['name']);
        $this->assertSame('L2', $basket[0]['manual_lac_code']);
        $this->assertSame('P0', $basket[0]['manual_brushstrokes_code']);
        $this->assertSame(95, $basket['totalPrice']);
        Mail::assertNothingSent();
    }

    public function test_failure_after_new_user_insert_rolls_back_user_and_order(): void
    {
        $presentation = Mockery::mock(OrderItemPresentationService::class);
        $presentation->shouldReceive('apply')->once()->andThrow(new RuntimeException('presentation failed'));
        $this->app->instance(OrderItemPresentationService::class, $presentation);

        try {
            app(AdminOrderCreationService::class)->create($this->validData());
            $this->fail('RuntimeException was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('presentation failed', $exception->getMessage());
        }

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('orders', 0);
        Mail::assertNothingSent();
    }

    public function test_insufficient_bonus_preserves_client_and_creates_no_order(): void
    {
        $user = User::query()->forceCreate(['email' => 'client@example.test', 'bonuses' => 2]);

        try {
            app(AdminOrderCreationService::class)->create($this->validData(['email' => $user->email, 'bonus' => 3]));
            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('bonus', $exception->errors());
        }

        $this->assertSame(2.0, (float) $user->fresh()->bonuses);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_uploaded_source_uses_legacy_order_filename_and_is_stored_in_items(): void
    {
        Storage::fake('uploads');
        $user = User::query()->forceCreate(['email' => 'client@example.test', 'settings' => ['locale' => 'ru']]);
        $data = $this->validData([
            'email' => $user->email,
            'items' => [[
                'name' => 'Portrait', 'price' => 100, 'size' => '40x60', 'terms' => 'custom term',
                'comment' => null, 'canvas_id' => 2, 'gift_code' => 'G0', 'decoration_id' => 5,
                'orientation_code' => 'V2', 'baget_code' => 'B0', 'express' => false,
                'existing_images' => [], 'legacy_payload' => [],
                'images' => [UploadedFile::fake()->image('client-source.jpg', 800, 600)],
            ]],
        ]);

        $order = app(AdminOrderCreationService::class)->create($data);
        $item = json_decode($order->items, true)[0];
        $path = parse_url($item['orig_images'][0], PHP_URL_PATH);

        $this->assertSame('/orders/N'.$order->id.'-1_40x60-X1_E0_D_LV_C_L0_G0_P0_B0_V2.jpg', $path);
        Storage::disk('uploads')->assertExists(ltrim($path, '/'));
        Mail::assertNothingSent();
    }

    public function test_euro_and_percent_discounts_cannot_be_combined(): void
    {
        $this->expectException(ValidationException::class);

        app(AdminOrderCreationService::class)->create($this->validData(['sale_eur' => 10, 'sale_percent' => 10]));
    }

    public function test_new_client_and_delivery_use_recipient_phone_with_payer_fallback(): void
    {
        foreach (['+371 (29) 999-999', '', null] as $index => $recipient) {
            $order = app(AdminOrderCreationService::class)->create($this->validData([
                'email' => 'phone'.$index.'@example.test', 'recipient_phone' => $recipient,
            ]));
            $delivery = json_decode($order->delivery, true);
            $expected = $index === 0 ? '+37129999999' : '+37120000000';
            $this->assertSame($expected, $delivery['phone']);
            $this->assertSame($expected, $order->user->phone);
            $this->assertSame('+37120000000', $delivery['payer_phone']);
        }
        Mail::assertNothingSent();
    }

    public function test_identical_positions_keep_all_uploaded_sources_without_overwriting(): void
    {
        Storage::fake('uploads');
        $data = $this->validData();
        $item = $data['items'][0];
        $files = [
            UploadedFile::fake()->image('first.jpg', 10, 10),
            UploadedFile::fake()->image('second.jpg', 20, 20),
            UploadedFile::fake()->image('third.jpg', 30, 30),
        ];
        $data['items'] = [
            array_replace($item, ['images' => [$files[0], $files[1]]]),
            array_replace($item, ['images' => [$files[2]]]),
        ];

        $order = app(AdminOrderCreationService::class)->create($data);
        $basket = json_decode($order->items, true);
        $urls = array_merge($basket[0]['orig_images'], $basket[1]['orig_images']);

        $this->assertCount(3, array_unique($urls));
        $this->assertCount(3, Storage::disk('uploads')->allFiles('orders'));
        foreach ($urls as $index => $url) {
            $path = ltrim(parse_url($url, PHP_URL_PATH), '/');
            $this->assertStringStartsWith('orders/N'.$order->id.'-2_40x60-X1_', $path);
            $this->assertStringEndsWith('-'.($index + 1).'.jpg', $path);
            $this->assertSame($files[$index]->getContent(), Storage::disk('uploads')->get($path));
        }
        $this->assertDatabaseCount('users', 1);
        Mail::assertNothingSent();
    }

    public function test_image_with_executable_client_extension_is_rejected_before_writes(): void
    {
        Storage::fake('uploads');
        $image = UploadedFile::fake()->image('source.jpg');
        $data = $this->validData();
        $data['items'][0]['images'] = [new UploadedFile($image->getPathname(), 'source.php', null, null, true)];

        try {
            app(AdminOrderCreationService::class)->create($data);
            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('items.0.images.0', $exception->errors());
        }

        $this->assertSame([], Storage::disk('uploads')->allFiles());
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('users', 0);
        Mail::assertNothingSent();
    }

    public function test_failure_after_upload_removes_files_and_rolls_back_bonus_and_order(): void
    {
        Storage::fake('uploads');
        Storage::disk('uploads')->put('orders/existing.jpg', 'keep');
        $user = User::query()->forceCreate(['email' => 'client@example.test', 'bonuses' => 15]);
        $data = $this->validData(['email' => $user->email, 'bonus' => 5]);
        $data['items'][0]['images'] = [UploadedFile::fake()->image('source.jpg')];
        Event::listen('eloquent.saving: '.Orders::class, function (Orders $order): void {
            if ($order->exists) {
                $this->assertCount(2, Storage::disk('uploads')->allFiles('orders'));
                throw new RuntimeException('after upload');
            }
        });

        try {
            app(AdminOrderCreationService::class)->create($data);
            $this->fail('RuntimeException was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('after upload', $exception->getMessage());
        }

        $this->assertSame(['orders/existing.jpg'], Storage::disk('uploads')->allFiles('orders'));
        $this->assertSame('keep', Storage::disk('uploads')->get('orders/existing.jpg'));
        $this->assertSame(15.0, (float) $user->fresh()->bonuses);
        $this->assertDatabaseCount('orders', 0);
        Mail::assertNothingSent();
    }

    public function test_create_rejects_documents_before_creating_user_or_order(): void
    {
        Storage::fake('uploads');
        foreach ([['source.pdf', '%PDF-1.4 test'], ['source.ps', '%!PS-Adobe-3.0 test']] as [$name, $content]) {
            $data = $this->validData();
            $data['items'][0]['images'] = [UploadedFile::fake()->createWithContent($name, $content)];
            try {
                app(AdminOrderCreationService::class)->create($data);
                $this->fail('Document accepted in image-only create form.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('items.0.images.0', $exception->errors());
            }
        }
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame([], Storage::disk('uploads')->allFiles());
        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    public function test_model_veto_rolls_back_each_creation_stage_and_new_files(): void
    {
        Storage::fake('uploads');
        $user = User::query()->forceCreate(['email' => 'existing@example.test', 'bonuses' => 15]);
        Storage::disk('uploads')->put('orders/existing.jpg', 'keep');
        foreach (['user', 'bonus', 'insert', 'final'] as $stage) {
            $data = $this->validData();
            if ($stage === 'bonus' || $stage === 'final') {
                $data['email'] = $user->email;
                $data['bonus'] = 5;
            }
            $data['items'][0]['images'] = [UploadedFile::fake()->image('source.jpg')];
            $model = in_array($stage, ['user', 'bonus'], true) ? User::class : Orders::class;
            $event = 'eloquent.saving: '.$model;
            Event::listen($event, function ($record) use ($stage) {
                $veto = in_array($stage, ['user', 'bonus'], true)
                    || ($stage === 'insert' && ! $record->exists)
                    || ($stage === 'final' && $record->exists);
                if ($veto) {
                    if ($stage === 'final') {
                        $this->assertCount(2, Storage::disk('uploads')->allFiles());
                    }

                    return false;
                }
            });
            try {
                app(AdminOrderCreationService::class)->create($data);
                $this->fail('Model veto reported success: '.$stage);
            } catch (ValidationException $exception) {
                $key = match ($stage) {
                    'user' => 'email', 'bonus' => 'bonus', default => 'items'
                };
                $this->assertArrayHasKey($key, $exception->errors());
            } finally {
                Event::forget($event);
            }
            $this->assertDatabaseCount('orders', 0);
            $this->assertDatabaseCount('users', 1);
            $this->assertSame(15.0, (float) $user->fresh()->bonuses);
            $this->assertSame(['orders/existing.jpg'], Storage::disk('uploads')->allFiles());
            $this->assertSame('keep', Storage::disk('uploads')->get('orders/existing.jpg'));
        }
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_partial_file_write_is_removed_when_adapter_returns_false(): void
    {
        $disk = Storage::fake('uploads');
        $disk->put('orders/existing.jpg', 'keep');
        $adapter = Mockery::mock(FilesystemAdapter::class);
        $adapter->shouldReceive('exists')->andReturn(false);
        $reserved = null;
        $adapter->shouldReceive('putFileAs')->once()->andReturnUsing(function ($directory, $file, $name) use ($disk, &$reserved) {
            $reserved = $directory.'/'.$name;
            $disk->put($reserved, 'partial');

            return false;
        });
        $adapter->shouldReceive('delete')->once()->andReturnUsing(fn ($path) => $disk->delete($path));
        Storage::shouldReceive('disk')->with('uploads')->andReturn($adapter);
        $data = $this->validData();
        $data['items'][0]['images'] = [UploadedFile::fake()->image('source.jpg')];
        try {
            app(AdminOrderCreationService::class)->create($data);
            $this->fail('Partial write accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('items.0.images', $exception->errors());
        }
        $this->assertNotNull($reserved);
        $this->assertFalse($disk->exists($reserved));
        $this->assertSame('keep', $disk->get('orders/existing.jpg'));
        $this->assertSame(['orders/existing.jpg'], $disk->allFiles());
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('users', 0);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_production_codes_and_collision_preserve_existing_and_remote_files(): void
    {
        Storage::fake('uploads');
        $data = $this->validData();
        $data['items'][0]['express'] = true;
        $data['items'][0]['terms'] = 'Экспресс';
        $data['items'][0]['images'] = [UploadedFile::fake()->image('source.jpg')];
        $remote = 'https://example.invalid/orders/existing.jpg';
        $data['items'][0]['existing_images'] = [$remote];
        $occupied = 'orders/N1-1_40x60-X1_E1_D_LV_C_L2_G1_P0_B1_V2.jpg';
        Storage::disk('uploads')->put($occupied, 'preserve existing file');

        $order = app(AdminOrderCreationService::class)->create($data);
        $images = json_decode($order->items, true)[0]['orig_images'];

        $this->assertSame($remote, $images[0]);
        $this->assertSame('/orders/N1-1_40x60-X1_E1_D_LV_C_L2_G1_P0_B1_V2-1.jpg', parse_url($images[1], PHP_URL_PATH));
        $this->assertSame('preserve existing file', Storage::disk('uploads')->get($occupied));
        $this->assertCount(2, Storage::disk('uploads')->allFiles('orders'));
        Mail::assertNothingSent();
    }

    /** @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_replace_recursive([
            'email' => 'new-client@example.test', 'first_name' => 'Client', 'last_name' => 'Test',
            'phone' => '+371 (20) 000-000', 'recipient_phone' => '+37129999999',
            'country' => 'LV', 'payment' => 'transfer', 'payment_status' => 'not_payed',
            'delivery_method' => 'to_the_door', 'city' => 'Riga', 'address' => 'Street 1',
            'postal_index' => 'LV-1001', 'delivery_price' => 5, 'bonus' => 0,
            'sale_eur' => 10, 'sale_percent' => 0,
            'items' => [[
                'name' => 'Portrait', 'price' => 100, 'size' => '40x60', 'terms' => 'custom term',
                'comment' => 'Keep business data', 'canvas_id' => 2, 'gift_code' => 'G1',
                'decoration_id' => 1, 'orientation_code' => 'V2', 'baget_code' => 'B1',
                'express' => false, 'existing_images' => [], 'images' => [],
            ]],
        ], $overrides);
    }
}
