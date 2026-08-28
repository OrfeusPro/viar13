<?php

namespace Tests\Feature\Admin;

use App\Models\Orders;
use App\Services\Admin\OrderVenipakLabelService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderVenipakLabelServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('email');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->text('items')->nullable();
            $table->text('delivery')->nullable();
            $table->string('labels')->nullable();
            $table->timestamps();
        });
        Schema::create('venipak_data', function (Blueprint $table): void {
            $table->id();
            $table->string('user');
            $table->string('pass');
            $table->string('login_id');
            $table->string('import_url');
            $table->string('print_url');
            $table->string('s_name')->nullable();
            $table->string('s_code')->nullable();
            $table->string('s_country')->nullable();
            $table->string('s_city')->nullable();
            $table->string('s_address')->nullable();
            $table->string('s_post')->nullable();
            $table->string('s_contact_p')->nullable();
            $table->string('s_contact_t')->nullable();
            $table->string('email_sender')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        DB::table('venipak_data')->insert([
            'user' => 'fake-user',
            'pass' => 'fake-pass',
            'login_id' => '08354',
            'import_url' => 'https://venipak.example.test/import',
            'print_url' => 'https://venipak.example.test/print',
            's_name' => 'VIARSTUDIA, SIA',
            's_code' => '123456789',
            's_country' => 'LV',
            's_city' => 'Daugavpils',
            's_address' => 'Muitas iela 3',
            's_post' => 'LV-5401',
            's_contact_p' => 'Test manager',
            's_contact_t' => '+37120000000',
            'email_sender' => 'sender@example.test',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_create_uses_fake_http_and_stores_all_returned_labels(): void
    {
        Http::fake([
            'https://venipak.example.test/import' => Http::response(
                '<?xml version="1.0"?><response type="1"><text>V08354E7718401</text><text>V08354E7718402</text></response>',
            ),
        ]);
        $order = $this->createOrder('delivery');

        $labels = app(OrderVenipakLabelService::class)->create($order, $this->addressPayload());

        $this->assertSame(['V08354E7718401', 'V08354E7718402'], $labels);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'labels' => 'V08354E7718401,V08354E7718402',
        ]);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://venipak.example.test/import');
        $xml = (new \ReflectionMethod(OrderVenipakLabelService::class, 'buildCreateXml'))->invoke(
            app(OrderVenipakLabelService::class),
            18451,
            $this->addressPayload(),
            $this->addressPayload()['packages'],
            '08354',
        );
        $this->assertStringContainsString('<shipment_code>18451</shipment_code>', $xml);
        $this->assertStringContainsString('<pack_no>V08354E7718453</pack_no>', $xml);
        $this->assertStringContainsString('<comment_door_code></comment_door_code>', $xml);
    }

    public function test_pickup_order_rejects_address_mode_without_external_request(): void
    {
        Http::fake();
        $order = $this->createOrder('pickup_at_viar_workshop');

        try {
            app(OrderVenipakLabelService::class)->create($order, $this->addressPayload());
            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('destination', $exception->errors());
        }

        Http::assertNothingSent();
        $this->assertNull(DB::table('orders')->where('id', $order->id)->value('labels'));
    }

    public function test_print_download_is_faked_and_requires_label_ownership(): void
    {
        Http::fake([
            'https://venipak.example.test/print' => Http::response('%PDF-1.4 fake label', 200, ['Content-Type' => 'application/pdf']),
        ]);
        $order = $this->createOrder('delivery', 'V08354E7718401');
        $service = app(OrderVenipakLabelService::class);

        $download = $service->print($order, 'V08354E7718401');

        $this->assertSame('V08354E7718401.pdf', $download['filename']);
        $this->assertSame('%PDF-1.4 fake label', $download['content']);
        Http::assertSentCount(1);

        try {
            $service->print($order, 'V08354E7799999');
            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('label', $exception->errors());
        }

        Http::assertSentCount(1);
    }

    private function createOrder(string $method, ?string $labels = null): Orders
    {
        $userId = DB::table('users')->insertGetId([
            'email' => 'client@example.test',
            'first_name' => 'Client',
            'last_name' => 'Test',
            'phone' => '+37121111111',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('orders')->insert([
            'id' => 18451,
            'user_id' => $userId,
            'items' => json_encode([['sizeId' => '40x60']]),
            'delivery' => json_encode([
                'sposob' => $method,
                'country' => 'LV',
                'city' => 'Rīga',
                'address' => 'Brīvības iela 1',
                'postal_index' => 'LV-1010',
            ]),
            'labels' => $labels,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Orders::query()->findOrFail(18451);
    }

    private function addressPayload(): array
    {
        return [
            'doc_no' => '',
            'destination' => 'address',
            'g_name' => 'Client Test',
            'g_code' => '',
            'r_country' => 'LV',
            'g_city' => 'Rīga',
            'g_address' => 'Brīvības iela',
            'g_house' => '1',
            'g_flat' => '',
            'g_post' => 'LV-1010',
            'g_contact_p' => '18451 (40x60)',
            'g_contact_t' => '+37121111111',
            'email_receiver' => 'client@example.test',
            'g_city_pickup' => '',
            'g_address_pickup' => '',
            'g_post_pickup' => '',
            'g_name_pickup' => '',
            'g_code_pickup' => '',
            's_name' => 'VIARSTUDIA, SIA',
            's_code' => '123456789',
            's_country' => 'LV',
            's_city' => 'Daugavpils',
            's_address' => 'Muitas iela 3',
            's_post' => 'LV-5401',
            's_contact_p' => 'Test manager',
            's_contact_t' => '+37120000000',
            'email_sender' => 'sender@example.test',
            'delivery_type' => 'nwd',
            'delivery_express' => '0',
            'cod' => '',
            'cod_type' => 'EUR',
            'comment_call' => false,
            'four_hands' => false,
            'packages' => [['weight' => 1, 'volume' => 1, 'pallet' => '0']],
        ];
    }
}
