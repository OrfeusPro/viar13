<?php

namespace Tests\Feature\Admin;

use App\Models\Orders;
use App\Services\Admin\OrderVenipakLabelService;
use App\Services\Admin\VenipakPickupCatalog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
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
        Http::preventStrayRequests();

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

    public function test_label_defaults_prefer_the_order_recipient_phone(): void
    {
        $order = $this->createOrder('delivery');
        $delivery = json_decode($order->delivery, true);
        $delivery['phone'] = '+37120085999';
        $delivery['payer_phone'] = '+37120000000';
        $order->delivery = json_encode($delivery);
        $order->save();

        $service = app(OrderVenipakLabelService::class);
        $this->assertSame('+37120085999', $service->defaultData($order)['g_contact_t']);

        unset($delivery['phone']);
        $order->delivery = json_encode($delivery);
        $this->assertSame('+37120000000', $service->defaultData($order)['g_contact_t']);

        unset($delivery['payer_phone']);
        $order->delivery = json_encode($delivery);
        $this->assertSame('+37121111111', $service->defaultData($order)['g_contact_t']);
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

    public function test_label_defaults_use_delivery_identity_when_linked_client_is_missing(): void
    {
        $order = $this->createOrder('delivery');
        DB::table('users')->where('id', $order->user_id)->delete();
        $delivery = json_decode($order->delivery, true);
        $order->forceFill(['delivery' => json_encode($delivery + [
            'first_name' => 'Recipient', 'last_name' => 'Fixture', 'phone' => '+37120000001',
            'email' => 'recipient@example.test',
        ])])->save();
        $defaults = app(OrderVenipakLabelService::class)->defaultData($order);
        $this->assertSame('Fixture Recipient', $defaults['g_name']);
        $this->assertSame('recipient@example.test', $defaults['email_receiver']);
        $this->assertSame('+37120000001', $defaults['g_contact_t']);
        Http::assertNothingSent();
    }

    public function test_pickup_defaults_use_the_selected_workshop_title_without_provider_requests(): void
    {
        Schema::create('delivery_pickup_at_viar_workshop', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
        });
        DB::table('delivery_pickup_at_viar_workshop')->insert(['id' => 5, 'title' => 'Workshop pickup title']);
        $order = $this->createOrder('pickup_at_viar_workshop');
        $delivery = json_decode($order->delivery, true);
        $delivery['pickup_workshop_id'] = 5;
        $order->delivery = json_encode($delivery);
        $this->assertSame('Workshop pickup title', app(OrderVenipakLabelService::class)->defaultData($order)['pickup_match']);
        Http::assertNothingSent();
    }

    public function test_selected_catalog_identity_is_serialized_into_pickup_label_xml(): void
    {
        Cache::flush();
        Http::fake([
            '*country=LV' => Http::response([
                ['id' => 10, 'city' => 'Rīga', 'address' => 'Street & 1', 'zip' => 'LV-1001', 'name' => 'Shop & Co', 'display_name' => 'Shop', 'company_code' => 'LV123'],
            ]),
            'https://venipak.example.test/import' => Http::response('<response type="1"><text>V08354E7718401</text></response>'),
        ]);
        $order = $this->createOrder('pickup_at_viar_workshop');
        $catalog = app(VenipakPickupCatalog::class);
        $payload = array_replace($this->addressPayload(), ['destination' => 'pickup'],
            $catalog->selection('LV', $catalog->match('LV', 'Shop')));
        app(OrderVenipakLabelService::class)->create($order, $payload);
        $request = Http::recorded(fn ($request): bool => $request->url() === 'https://venipak.example.test/import')->first()[0];
        $xml = $request->body();
        $this->assertStringContainsString('<name>Shop &amp; Co</name>', $xml);
        $this->assertStringContainsString('<company_code>LV123</company_code>', $xml);
        $this->assertStringContainsString('<address>Street &amp; 1</address>', $xml);
        $this->assertStringContainsString('<post_code>LV-1001</post_code>', $xml);
        Http::assertSentCount(2);
        $this->assertSame('V08354E7718401', $order->fresh()->labels);
    }

    public function test_print_rejects_html_empty_error_and_http_failures_without_changing_order(): void
    {
        $order = $this->createOrder('delivery', 'V08354E7718401');
        Http::fakeSequence()->push('<html>Login required</html>', 200)
            ->push('', 200)->push('Error: unavailable', 200)->push('%PDF-1.4 unavailable', 503);
        foreach (range(1, 4) as $attempt) {
            try {
                app(OrderVenipakLabelService::class)->print($order, 'V08354E7718401');
                $this->fail('Invalid PDF accepted on attempt '.$attempt);
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('label', $exception->errors());
            }
        }
        $this->assertSame('V08354E7718401', $order->fresh()->labels);
        Http::assertSentCount(4);
        $request = Http::recorded()->first()[0];
        $this->assertSame('POST', $request->method());
        foreach (['user', 'pass', 'login_id', 'pack_no'] as $field) {
            $this->assertStringContainsString('name="'.$field.'"', $request->body());
        }
        $this->assertStringNotContainsString('name="format"', $request->body());
    }

    public function test_rejected_create_responses_preserve_previous_labels_and_restore_libxml_setting(): void
    {
        $order = $this->createOrder('delivery', 'OLD-LABEL');
        Http::fakeSequence()->push('not xml', 200)
            ->push('<response><error><text>Rejected fixture</text></error></response>', 200)
            ->push('<response/>', 200)->push('unavailable', 503);
        $previous = libxml_use_internal_errors(false);
        try {
            foreach (range(1, 4) as $attempt) {
                try {
                    app(OrderVenipakLabelService::class)->create($order, $this->addressPayload());
                    $this->fail('Invalid create response accepted on attempt '.$attempt);
                } catch (ValidationException $exception) {
                    $this->assertArrayHasKey('venipak', $exception->errors());
                }
                $this->assertSame('OLD-LABEL', $order->fresh()->labels);
                $this->assertFalse(libxml_use_internal_errors());
            }
        } finally {
            libxml_use_internal_errors($previous);
        }
        Http::assertSentCount(4);
    }

    public function test_created_labels_are_reported_if_model_vetoes_saving_and_no_retry_is_sent(): void
    {
        $order = $this->createOrder('delivery', 'OLD-LABEL');
        Http::fake(['*' => Http::response('<response><text>V08354E7718401</text></response>')]);
        $dispatcher = Orders::getEventDispatcher();
        Orders::setEventDispatcher(clone $dispatcher);
        Orders::saving(fn () => false);
        try {
            app(OrderVenipakLabelService::class)->create($order, $this->addressPayload());
            $this->fail('Vetoed save returned success.');
        } catch (ValidationException $exception) {
            $message = $exception->errors()['venipak'][0];
            $this->assertStringContainsString('V08354E7718401', $message);
            $this->assertStringContainsString('Не отправляйте запрос повторно', $message);
        } finally {
            Orders::setEventDispatcher($dispatcher);
        }
        $this->assertSame('OLD-LABEL', $order->fresh()->labels);
        Http::assertSentCount(1);
    }

    public function test_repeat_create_replaces_previous_labels_like_original(): void
    {
        $order = $this->createOrder('delivery', 'OLD-LABEL');
        Http::fakeSequence()->push('<response><text>NEW-1</text></response>')
            ->push('<response><text>NEW-2</text><text>NEW-3</text></response>');
        $service = app(OrderVenipakLabelService::class);
        $service->create($order, $this->addressPayload());
        $this->assertSame('NEW-1', $order->fresh()->labels);
        $service->create($order, $this->addressPayload());
        $this->assertSame('NEW-2,NEW-3', $order->fresh()->labels);
        Http::assertSentCount(2);
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
