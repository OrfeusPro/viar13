<?php

namespace Tests\Feature\Admin;

use App\Models\Orders;
use App\Models\User;
use App\Services\Admin\AdminOrderCreationService;
use App\Services\Admin\OrderItemPresentationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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

    public function test_copy_defaults_preserve_client_delivery_payment_discounts_and_items(): void
    {
        $user = User::query()->forceCreate([
            'email' => 'client@example.test', 'first_name' => 'Client', 'phone' => '+371 20-00',
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
            'delivery' => json_encode(['city' => 'Riga', 'phone' => '+3712999', 'sposob' => 'venipak', 'deliv_price' => 5]),
            'payment' => 'paypalOnetimePayment',
            'payment_status' => 'prepayment',
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
        $this->assertSame('venipak', $defaults['delivery_method']);
        $this->assertSame('paypalOnetimePayment', $defaults['payment']);
        $this->assertSame(3.0, $defaults['sale_eur']);
        $this->assertSame('Portrait', $defaults['items'][0]['name']);
        $this->assertSame('40x60', $defaults['items'][0]['size']);
        $this->assertSame('G1', $defaults['items'][0]['gift_code']);
        $this->assertSame('V2', $defaults['items'][0]['orientation_code']);
        $this->assertTrue($defaults['items'][0]['express']);
        $this->assertSame(['https://viarcanvas.com/orders/source.jpg'], $defaults['items'][0]['existing_images']);
        $this->assertSame(7, $defaults['items'][0]['legacy_payload']['effectId']);
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

        $this->assertStringStartsWith('/orders/N'.$order->id.'-1_40x60-X1_V1_Portrait_', $path);
        $this->assertStringEndsWith('_0.jpg', $path);
        Storage::disk('uploads')->assertExists(ltrim($path, '/'));
        Mail::assertNothingSent();
    }

    public function test_euro_and_percent_discounts_cannot_be_combined(): void
    {
        $this->expectException(ValidationException::class);

        app(AdminOrderCreationService::class)->create($this->validData(['sale_eur' => 10, 'sale_percent' => 10]));
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
        $presentation = Mockery::mock(OrderItemPresentationService::class);
        $presentation->shouldReceive('apply')->once()->andThrow(new RuntimeException('after upload'));
        $this->app->instance(OrderItemPresentationService::class, $presentation);

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
