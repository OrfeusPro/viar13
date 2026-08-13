<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SaLeadsCreateTest extends TestCase
{
    private const ENDPOINT = '/api/sa/leads';
    private const API_KEY = 'test-key';

    /** @test */
    public function t05_001_minimal_valid_payload_returns_ok()
    {
        $response = $this->postJson(self::ENDPOINT, $this->payloadMinimal(), $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure([
                'data' => ['lead_id', 'contact_id', 'deal_id'],
            ]);
    }

    /** @test */
    public function t05_002_full_payload_with_service_fields_returns_ok()
    {
        $response = $this->postJson(self::ENDPOINT, $this->payloadFull(), $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.pipeline.id', 'PIPE-1')
            ->assertJsonPath('data.stage.id', 'pegging');
    }

    /** @test */
    public function t05_003_duplicate_idempotency_key_returns_duplicate()
    {
        $payload = $this->payloadMinimal();

        $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders())->assertStatus(200);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'duplicate');
    }

    /** @test */
    public function t05_004_missing_client_phone_returns_validation_error()
    {
        $payload = $this->payloadMinimal();
        unset($payload['lead']['client']['phone']);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t05_005_invalid_channel_returns_validation_error()
    {
        $payload = $this->payloadMinimal();
        $payload['idempotency_key'] = 'create_lead:invalid_channel:' . $this->uniqueSuffix();
        $payload['lead']['channel'] = 'invalid-channel';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t05_005j_legacy_stg_stage_is_rejected_with_validation_error()
    {
        $payload = $this->payloadMinimal();
        $payload['idempotency_key'] = 'create_lead:legacy_stage:' . $this->uniqueSuffix();
        $payload['lead']['stage'] = ['id' => 'STG-20'];

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t05_005a_overlong_phone_returns_validation_error()
    {
        $payload = $this->payloadMinimal();
        $payload['idempotency_key'] = 'create_lead:long_phone:' . $this->uniqueSuffix();
        $payload['lead']['client']['phone'] = '+38017756437000000000000';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t05_005b_phone_is_sanitized_before_persist()
    {
        $payload = $this->payloadMinimal();
        $payload['idempotency_key'] = 'create_lead:sanitize_phone:' . $this->uniqueSuffix();
        $payload['lead']['client']['phone'] = '+371 (29) 123-45-67 ext.89';
        $payload['lead']['client']['name'] = 'Sanitize Phone';
        $payload['lead']['fields']['email'] = 'sanitize_phone_' . $this->uniqueSuffix() . '@example.com';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'sa_client_phone')) {
            $this->markTestSkipped('orders.sa_client_phone is not available in current test DB connection');
        }

        $leadIdRaw = (string) $response->json('data.lead_id', '');
        $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.fields.email'));
        $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for phone sanitize test');

        $storedPhone = (string) DB::table('orders')->where('id', $leadId)->value('sa_client_phone');
        $this->assertSame('+37129123456789', $storedPhone);
    }

    /** @test */
    public function t05_005c_recipient_block_is_persisted_into_delivery_json()
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'delivery')) {
            $this->markTestSkipped('orders.delivery is not available in current test DB connection');
        }

        $payload = $this->payloadWithService('HM-2', [
            'size' => '60x80',
        ]);
        $payload['idempotency_key'] = 'create_lead:recipient:' . $this->uniqueSuffix();
        $payload['lead']['recipient'] = [
            'name' => 'Aleksandra',
            'last_name' => 'Recipient',
            'phone' => '+371 (22) 333-44-55',
            'address' => 'Recipient Street 7',
            'postal_index' => 'LV-2020',
        ];

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        $leadIdRaw = (string) $response->json('data.lead_id', '');
        $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.client.email'));
        $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for recipient test');

        $deliveryRaw = DB::table('orders')->where('id', $leadId)->value('delivery');
        $delivery = json_decode((string) $deliveryRaw, true);
        $this->assertIsArray($delivery);
        $this->assertSame('Aleksandra', (string) ($delivery['first_name'] ?? ''));
        $this->assertSame('Recipient', (string) ($delivery['last_name'] ?? ''));
        $this->assertSame('+371223334455', (string) ($delivery['phone'] ?? ''));
        $this->assertSame('Recipient Street 7', (string) ($delivery['address'] ?? ''));
        $this->assertSame('LV-2020', (string) ($delivery['postal_index'] ?? ''));
        $this->assertEquals(1, (int) ($delivery['is_no_payer'] ?? 0));
    }

    /** @test */
    public function t05_005d_billing_company_fields_are_persisted_to_order()
    {
        $requiredColumns = [
            'ur_name',
            'ur_name_l',
            'ur_reg_num',
            'ur_legal_addr',
            'ur_pnr_nr',
            'ur_bank_name',
            'ur_bank_code',
            'ur_bank_acc_code',
        ];
        foreach ($requiredColumns as $column) {
            if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', $column)) {
                $this->markTestSkipped('orders.' . $column . ' is not available in current test DB connection');
            }
        }

        $payload = $this->payloadWithService('HM-2', [
            'size' => '60x80',
        ]);
        $payload['idempotency_key'] = 'create_lead:billing:' . $this->uniqueSuffix();
        $payload['lead']['billing'] = [
            'company' => [
                'name' => 'SIA Example Print',
                'name_l' => 'SIA Example Print LV',
                'registration_number' => '40123456789',
                'legal_address' => 'Riga Legal Street 5',
                'pnr_nr' => 'LV99HABA0551000000001',
                'bank_name' => 'Swedbank',
                'bank_code' => 'HABA',
                'bank_account_code' => 'LV99HABA0551000000001',
            ],
        ];

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        $leadIdRaw = (string) $response->json('data.lead_id', '');
        $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.client.email'));
        $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for billing company test');

        $order = DB::table('orders')
            ->where('id', $leadId)
            ->select($requiredColumns)
            ->first();

        $this->assertNotNull($order);
        $this->assertSame('SIA Example Print', (string) ($order->ur_name ?? ''));
        $this->assertSame('SIA Example Print LV', (string) ($order->ur_name_l ?? ''));
        $this->assertSame('40123456789', (string) ($order->ur_reg_num ?? ''));
        $this->assertSame('Riga Legal Street 5', (string) ($order->ur_legal_addr ?? ''));
        $this->assertSame('LV99HABA0551000000001', (string) ($order->ur_pnr_nr ?? ''));
        $this->assertSame('Swedbank', (string) ($order->ur_bank_name ?? ''));
        $this->assertSame('HABA', (string) ($order->ur_bank_code ?? ''));
        $this->assertSame('LV99HABA0551000000001', (string) ($order->ur_bank_acc_code ?? ''));
    }

    /** @test */
    public function t05_005e_coupon_and_bonus_together_return_validation_error()
    {
        $payload = $this->payloadWithService('HM-2', [
            'size' => '60x80',
        ]);
        $payload['lead']['pricing'] = [
            'coupon_code' => 'ANY-COUPON',
            'use_bonus' => true,
        ];

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonPath('error.details.0.field', 'lead.pricing');
    }

    /** @test */
    public function t05_005f_invalid_coupon_returns_validation_error()
    {
        if (!Schema::hasTable('coupons')) {
            $this->markTestSkipped('coupons table is not available in current test DB connection');
        }

        $payload = $this->payloadWithService('HM-2', [
            'size' => '60x80',
        ]);
        $payload['lead']['pricing'] = [
            'coupon_code' => 'INVALID-' . strtoupper($this->uniqueSuffix()),
        ];

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonPath('error.details.0.field', 'lead.pricing.coupon_code');
    }

    /** @test */
    public function t05_005g_universal_coupon_sets_sale_price_on_order()
    {
        if (!Schema::hasTable('coupons') || !Schema::hasTable('orders')) {
            $this->markTestSkipped('coupons/orders tables are not available in current test DB connection');
        }

        if (!Schema::hasColumn('orders', 'sale_price') || !Schema::hasColumn('orders', 'delivery')) {
            $this->markTestSkipped('orders.sale_price or orders.delivery is not available in current test DB connection');
        }

        $couponCode = 'SA-UNIV-' . strtoupper($this->uniqueSuffix());
        $couponId = $this->insertTestCoupon([
            'text' => $couponCode,
            'value' => '10',
            'is_universal' => 1,
        ]);

        try {
            $payload = $this->payloadWithService('HM-2', [
                'size' => '60x80',
            ]);
            $payload['lead']['pricing'] = [
                'coupon_code' => $couponCode,
            ];

            $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
            $response->assertStatus(200)->assertJsonPath('status', 'ok');

            $leadIdRaw = (string) $response->json('data.lead_id', '');
            $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.client.email'));
            $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for universal coupon test');

            $order = DB::table('orders')
                ->where('id', $leadId)
                ->select(['price', 'sale_price', 'delivery'])
                ->first();

            $this->assertNotNull($order);
            $this->assertGreaterThan((float) $order->sale_price, (float) $order->price);
            $this->assertEquals(round((float) $order->price - 10, 2), round((float) $order->sale_price, 2));

            $delivery = json_decode((string) $order->delivery, true);
            $this->assertIsArray($delivery);
            $this->assertSame('universal', (string) ($delivery['coupon_type'] ?? ''));
        } finally {
            DB::table('coupons')->where('id', $couponId)->delete();
        }
    }

    /** @test */
    public function t05_005h_free_delivery_coupon_zeroes_delivery_price()
    {
        if (!Schema::hasTable('coupons') || !Schema::hasTable('orders')) {
            $this->markTestSkipped('coupons/orders tables are not available in current test DB connection');
        }

        if (!Schema::hasColumn('orders', 'delivery') || !Schema::hasColumn('orders', 'sale_price')) {
            $this->markTestSkipped('orders.delivery or orders.sale_price is not available in current test DB connection');
        }

        $couponCode = 'SA-FREE-' . strtoupper($this->uniqueSuffix());
        $couponId = $this->insertTestCoupon([
            'text' => $couponCode,
            'value' => '0',
            'free_delivery' => 1,
        ]);

        try {
            $payload = $this->payloadWithService('HM-2', [
                'size' => '60x80',
            ]);
            $payload['lead']['delivery']['deliv_price'] = 11.25;
            $payload['lead']['pricing'] = [
                'coupon_code' => $couponCode,
            ];

            $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
            $response->assertStatus(200)->assertJsonPath('status', 'ok');

            $leadIdRaw = (string) $response->json('data.lead_id', '');
            $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.client.email'));
            $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for free-delivery coupon test');

            $order = DB::table('orders')
                ->where('id', $leadId)
                ->select(['price', 'sale_price', 'delivery'])
                ->first();

            $this->assertNotNull($order);
            $this->assertEquals(round((float) $order->price, 2), round((float) $order->sale_price, 2));

            $delivery = json_decode((string) $order->delivery, true);
            $this->assertIsArray($delivery);
            $this->assertSame('free_delivery', (string) ($delivery['coupon_type'] ?? ''));
            $this->assertEquals(0.0, round((float) ($delivery['deliv_price'] ?? 999), 2));
        } finally {
            DB::table('coupons')->where('id', $couponId)->delete();
        }
    }

    /** @test */
    public function t05_005i_use_bonus_persists_sale_price_and_decrements_user_balance()
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'use_bonus') || !Schema::hasColumn('orders', 'sale_price')) {
            $this->markTestSkipped('orders.use_bonus or orders.sale_price is not available in current test DB connection');
        }

        $payload = $this->payloadWithService('HM-2', [
            'size' => '60x80',
        ]);

        $initialBonuses = 5.0;
        $userId = $this->insertTestUser([
            'email' => (string) data_get($payload, 'lead.client.email'),
            'phone' => (string) data_get($payload, 'lead.client.phone'),
            'first_name' => 'Bonus',
            'last_name' => 'Holder',
            'bonuses' => $initialBonuses,
        ]);

        $payload['lead']['pricing'] = [
            'use_bonus' => true,
        ];

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        $leadIdRaw = (string) $response->json('data.lead_id', '');
        $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.client.email'));
        $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for bonus test');

        $order = DB::table('orders')
            ->where('id', $leadId)
            ->select(['price', 'sale_price', 'use_bonus', 'delivery', 'user_id'])
            ->first();

        $this->assertNotNull($order);
        $this->assertSame($userId, (int) ($order->user_id ?? 0));
        $this->assertSame(1, (int) ($order->use_bonus ?? 0));
        $this->assertGreaterThan((float) $order->sale_price, (float) $order->price);

        $usedBonus = round((float) $order->price - (float) $order->sale_price, 2);
        $expectedUsedBonus = round(min($initialBonuses, (float) $order->price), 2);
        $this->assertEquals($expectedUsedBonus, $usedBonus);

        $delivery = json_decode((string) $order->delivery, true);
        $this->assertIsArray($delivery);
        $this->assertSame('bonus', (string) ($delivery['coupon_type'] ?? ''));

        $remainingBonuses = (float) DB::table('users')->where('id', $userId)->value('bonuses');
        $this->assertEquals(round($initialBonuses - $usedBonus, 2), round($remainingBonuses, 2));
    }

    /** @test */
    public function t05_005j_supported_delivery_payment_code_is_persisted()
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'payment') || !Schema::hasColumn('orders', 'delivery')) {
            $this->markTestSkipped('orders.payment or orders.delivery is not available in current test DB connection');
        }

        $payload = $this->payloadWithService('HM-2', [
            'size' => '60x80',
        ]);
        $payload['idempotency_key'] = 'create_lead:payment_supported:' . $this->uniqueSuffix();
        $payload['lead']['delivery']['payment'] = 'online_paysera';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        $leadIdRaw = (string) $response->json('data.lead_id', '');
        $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.client.email'));
        $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for payment persistence test');

        $order = DB::table('orders')
            ->where('id', $leadId)
            ->select(['payment', 'delivery'])
            ->first();

        $this->assertNotNull($order);
        $this->assertSame('online_paysera', (string) ($order->payment ?? ''));

        $delivery = json_decode((string) $order->delivery, true);
        $this->assertIsArray($delivery);
        $this->assertSame('online_paysera', (string) ($delivery['payment'] ?? ''));
    }

    /** @test */
    public function t05_005k_legacy_delivery_payment_alias_is_normalized()
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'payment') || !Schema::hasColumn('orders', 'delivery')) {
            $this->markTestSkipped('orders.payment or orders.delivery is not available in current test DB connection');
        }

        $payload = $this->payloadWithService('HM-2', [
            'size' => '60x80',
        ]);
        $payload['idempotency_key'] = 'create_lead:payment_alias:' . $this->uniqueSuffix();
        $payload['lead']['delivery']['payment'] = 'online_banking';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        $leadIdRaw = (string) $response->json('data.lead_id', '');
        $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.client.email'));
        $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for payment alias test');

        $order = DB::table('orders')
            ->where('id', $leadId)
            ->select(['payment', 'delivery'])
            ->first();

        $this->assertNotNull($order);
        $this->assertSame('online_paysera', (string) ($order->payment ?? ''));

        $delivery = json_decode((string) $order->delivery, true);
        $this->assertIsArray($delivery);
        $this->assertSame('online_paysera', (string) ($delivery['payment'] ?? ''));
    }

    /** @test */
    public function t05_005l_invalid_delivery_payment_returns_validation_error()
    {
        $payload = $this->payloadWithService('HM-2', [
            'size' => '60x80',
        ]);
        $payload['idempotency_key'] = 'create_lead:payment_invalid:' . $this->uniqueSuffix();
        $payload['lead']['delivery']['payment'] = 'crypto_magic';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonPath('error.details.0.field', 'lead.delivery.payment');
    }

    /** @test */
    public function t05_006_missing_api_key_returns_unauthorized()
    {
        $response = $this->postJson(self::ENDPOINT, $this->payloadMinimal());

        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    /** @test */
    public function t05_007_hm27_persists_portrait_completeness_fields()
    {
        if (!Schema::hasTable('orders')) {
            $this->markTestSkipped('orders table is not available in current test DB connection');
        }

        $payload = $this->payloadWithService('HM-27', [
            'size' => '30x40',
        ]);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        $leadIdRaw = (string) $response->json('data.lead_id', '');
        $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.client.email'));
        $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for HM-27 test');

        $item = $this->findServiceItemFromOrder($leadId, 'HM-27');
        $this->assertNotNull($item);

        $requiredKeys = [
            'is_port_product',
            'orig_images',
            'pack',
            'terms',
            'forma_id',
            'users_count',
            'type',
            'holst_id',
            'hud_of',
            'compl_id',
            'size_name',
            'total_item_price',
            'service_path',
        ];
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $item, 'Missing portrait key: ' . $key);
        }

        $this->assertEquals(1, (int) $item['is_port_product']);
        $this->assertIsArray($item['orig_images']);
        $this->assertNotSame('', trim((string) $item['pack']));
        $this->assertNotSame('', trim((string) $item['terms']));
        $this->assertNotSame('', trim((string) $item['size_name']));
        $this->assertEquals('/new/caricature', (string) $item['service_path']);
    }

    /** @test */
    public function t05_008_hm44_persists_gallery_completeness_fields()
    {
        if (!Schema::hasTable('orders')) {
            $this->markTestSkipped('orders table is not available in current test DB connection');
        }

        $payload = $this->payloadWithService('HM-44', [
            'size' => '30x20',
        ]);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        $leadIdRaw = (string) $response->json('data.lead_id', '');
        $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.client.email'));
        $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for HM-44 test');

        $item = $this->findServiceItemFromOrder($leadId, 'HM-44');
        $this->assertNotNull($item);

        $requiredKeys = [
            'size',
            'size_name',
            'pack',
            'terms',
            'boxIds',
            'show',
            'orig_images',
            'total_item_price',
            'is_construct',
            'service_path',
        ];
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $item, 'Missing HM-44 key: ' . $key);
        }

        $this->assertIsArray($item['boxIds']);
        $this->assertIsArray($item['show']);
        $this->assertEquals((string) $item['size'], (string) $item['size_name']);
        $this->assertEquals('/new/gallery', (string) $item['service_path']);
        $this->assertArrayHasKey('size', (array) $item['show']);
    }

    /** @test */
    public function t05_009_hm2_persists_canvas_size_fields()
    {
        if (!Schema::hasTable('orders')) {
            $this->markTestSkipped('orders table is not available in current test DB connection');
        }

        $payload = $this->payloadWithService('HM-2', [
            'size' => '60x80',
            'production_mode' => 'standard',
        ]);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        $leadIdRaw = (string) $response->json('data.lead_id', '');
        $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.client.email'));
        $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for HM-2 test');

        $item = $this->findServiceItemFromOrder($leadId, 'HM-2');
        $this->assertNotNull($item);

        $this->assertArrayHasKey('size', $item);
        $this->assertArrayHasKey('size_name', $item);
        $this->assertEquals('60x80', (string) $item['size']);
        $this->assertEquals('60x80', (string) $item['size_name']);
    }

    /** @test */
    public function t05_010_real_delivery_methods_are_accepted_and_persisted()
    {
        if (!Schema::hasTable('orders')) {
            $this->markTestSkipped('orders table is not available in current test DB connection');
        }

        $cases = [
            [
                'method' => 'venipak',
                'delivery' => [
                    'delivery_photo_short_code' => 'Pi',
                ],
            ],
            [
                'method' => 'pickup_Riga',
                'delivery' => [
                    'pickup_workshop_id' => 1,
                    'delivery_photo_short_code' => 'o-R',
                ],
            ],
            [
                'method' => 'pickup_Daugavpils',
                'delivery' => [
                    'pickup_workshop_id' => 2,
                    'delivery_photo_short_code' => 'o-D',
                ],
            ],
            [
                'method' => 'pickup_Daugavplis',
                'delivery' => [
                    'pickup_workshop_id' => 2,
                    'delivery_photo_short_code' => 'o-D',
                ],
            ],
        ];

        foreach ($cases as $case) {
            $payload = $this->payloadWithService('HM-2', [
                'size' => '60x80',
            ]);
            $payload['lead']['delivery']['method'] = $case['method'];
            $payload['lead']['delivery'] = array_merge($payload['lead']['delivery'], $case['delivery']);

            $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
            $response->assertStatus(200)->assertJsonPath('status', 'ok');

            $leadIdRaw = (string) $response->json('data.lead_id', '');
            $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.client.email'));
            $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for delivery method ' . $case['method']);

            $deliveryRaw = DB::table('orders')->where('id', $leadId)->value('delivery');
            $delivery = json_decode((string) $deliveryRaw, true);
            $this->assertIsArray($delivery);
            $this->assertSame($case['method'], (string) ($delivery['sposob'] ?? ''));

            foreach ($case['delivery'] as $key => $expectedValue) {
                $this->assertSame((string) $expectedValue, (string) ($delivery[$key] ?? ''));
            }
        }
    }

    /** @test */
    public function t05_011_invalid_delivery_method_returns_validation_error()
    {
        $payload = $this->payloadWithService('HM-2', [
            'size' => '60x80',
        ]);
        $payload['lead']['delivery']['method'] = 'postal_pigeon';

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t05_012_hm3_persists_collage_completeness_fields()
    {
        if (!Schema::hasTable('orders')) {
            $this->markTestSkipped('orders table is not available in current test DB connection');
        }

        $payload = $this->payloadWithService('HM-3', [
            'size' => '40x30',
            'form_id' => 1,
            'holst_id' => 2,
            'decor_id' => 5,
            'compl_id' => 3,
            'ram_id' => 12,
            'production_mode' => 'express',
        ]);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        $leadIdRaw = (string) $response->json('data.lead_id', '');
        $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.client.email'));
        $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for HM-3 test');

        $item = $this->findServiceItemFromOrder($leadId, 'HM-3');
        $this->assertNotNull($item);

        $requiredKeys = [
            'pid',
            'basketType',
            'is_construct',
            'is_port_product',
            'is_def_product',
            'size',
            'sizeId',
            'size_name',
            'formId',
            'holst_id',
            'decor_id',
            'compl_id',
            'ram_id',
            'pack',
            'hud_of',
            'boxIds',
            'terms',
            'terms_price',
            'orig_images',
            'photo_ex',
            'show',
            'total_item_price',
            'service_path',
        ];
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $item, 'Missing HM-3 key: ' . $key);
        }

        $this->assertSame('/collage', (string) $item['service_path']);
        $this->assertSame('40x30', (string) $item['size']);
        $this->assertSame('40x30', (string) $item['sizeId']);
        $this->assertSame('40x30', (string) $item['size_name']);
        $this->assertSame('1', (string) $item['basketType']);
        $this->assertEquals(1, (int) $item['is_construct']);
        $this->assertEquals(2, (int) $item['pid']);
        $this->assertIsArray($item['boxIds']);
        $this->assertIsArray($item['orig_images']);
        $this->assertIsArray($item['show']);
        $this->assertArrayHasKey('size', (array) $item['show']);
        $this->assertArrayHasKey('box', (array) $item['show']);
        $this->assertArrayHasKey('decoration', (array) $item['show']);
        $this->assertGreaterThan(0, (float) $item['terms_price']);
        $this->assertEquals(
            round((float) $item['price'] + (float) $item['terms_price'], 2),
            round((float) $item['total_item_price'], 2)
        );
    }

    /** @test */
    public function t05_013_hm43_persists_modular_completeness_fields()
    {
        if (!Schema::hasTable('orders')) {
            $this->markTestSkipped('orders table is not available in current test DB connection');
        }

        $payload = $this->payloadWithService('HM-43', [
            'size' => '120x80',
            'form_id' => 1,
            'holst_id' => 5,
            'packaging_id' => 3,
            'execution_id' => 2,
            'production_mode' => 'express',
            'wall_size_mod' => '250x300',
        ]);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        $leadIdRaw = (string) $response->json('data.lead_id', '');
        $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.client.email'));
        $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for HM-43 test');

        $item = $this->findServiceItemFromOrder($leadId, 'HM-43');
        $this->assertNotNull($item);

        $requiredKeys = [
            'basketType',
            'is_construct',
            'is_port_product',
            'is_def_product',
            'name',
            'size',
            'size_name',
            'sizeId',
            'formId',
            'holst_id',
            'boxIds',
            'terms',
            'terms_price',
            'pack',
            'hud_of',
            'show',
            'manual_canvas_id',
            'manual_gift_code',
            'manual_decoration_id',
            'manual_lac_code',
            'manual_brushstrokes_code',
            'service_path',
            'total_item_price',
        ];
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $item, 'Missing HM-43 key: ' . $key);
        }

        $this->assertSame('/modular-generator', (string) $item['service_path']);
        $this->assertSame('Modular pictures', (string) $item['name']);
        $this->assertSame('120x80', (string) $item['size']);
        $this->assertSame('120x80', (string) $item['size_name']);
        $this->assertSame('1', (string) $item['basketType']);
        $this->assertEquals(1, (int) $item['is_construct']);
        $this->assertIsArray($item['boxIds']);
        $this->assertIsArray($item['show']);
        $this->assertArrayHasKey('box', (array) $item['show']);
        $this->assertArrayHasKey('decoration', (array) $item['show']);
        $this->assertArrayHasKey('canvas', (array) $item['show']);
        $this->assertGreaterThan(0, (float) $item['terms_price']);
        $this->assertEquals(
            round((float) $item['price'] + (float) $item['terms_price'], 2),
            round((float) $item['total_item_price'], 2)
        );
    }

    /** @test */
    public function t05_014_hm43_layout_svg_is_used_for_exact_price()
    {
        if (!Schema::hasTable('orders')) {
            $this->markTestSkipped('orders table is not available in current test DB connection');
        }

        $payload = $this->payloadWithService('HM-43', [
            'size' => '120x80',
            'holst_id' => 5,
            'packaging_id' => 3,
            'execution_id' => 1,
            'production_mode' => 'standard',
            'layout_svg' => '<svg viewBox="0 0 120 80" xmlns="http://www.w3.org/2000/svg"><rect x="0" y="0" width="120" height="80"/></svg>',
        ]);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        $leadIdRaw = (string) $response->json('data.lead_id', '');
        $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.client.email'));
        $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for HM-43 SVG test');

        $item = $this->findServiceItemFromOrder($leadId, 'HM-43');
        $this->assertNotNull($item);

        $area = round((120 * 80) / 10000, 4);
        $expectedExecutionPrice = round($area * (float) setting('modulnye-kartiny.area_less_1', 1.5), 2);
        $expectedExecutionPrice = round($expectedExecutionPrice * (float) setting('modulnye-kartiny.area_is_1', 55), 2);

        $this->assertSame('layout_svg', (string) ($item['sa_modular_price_mode'] ?? ''));
        $this->assertEquals($area, round((float) ($item['sa_modular_layout_area'] ?? 0), 4));
        $this->assertEquals($expectedExecutionPrice, round((float) $item['price'], 2));
        $this->assertEquals($expectedExecutionPrice, round((float) $item['sumPrice'], 2));
        $this->assertEquals($expectedExecutionPrice, round((float) $item['total_item_price'], 2));
    }

    /** @test */
    public function t05_015_hm43_invalid_layout_blocks_return_validation_error()
    {
        $payload = $this->payloadWithService('HM-43', [
            'size' => '120x80',
            'layout_blocks' => [
                ['width' => 60],
            ],
        ]);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t05_016_gc5_persists_gift_card_item_and_email_delivery_for_online_card()
    {
        if (!Schema::hasTable('orders') || !Schema::hasTable('gift_card_noms')) {
            $this->markTestSkipped('orders or gift_card_noms tables are not available in current test DB connection');
        }

        $nominalText = DB::table('gift_card_noms')->orderBy('id')->value('text');
        if (!is_string($nominalText) || trim($nominalText) === '') {
            $this->markTestSkipped('gift_card_noms does not contain any nominal value');
        }

        preg_match('/\d+(?:[.,]\d+)?/', $nominalText, $matches);
        $nominal = isset($matches[0]) ? str_replace(',', '.', $matches[0]) : null;
        if ($nominal === null) {
            $this->markTestSkipped('Unable to parse nominal from gift_card_noms.text');
        }

        $payload = $this->payloadWithService('GC-5', [
            'amount' => $nominal,
            'card_type' => 'online',
        ]);
        $payload['lead']['service_request']['notes'] = 'PHPUnit gift card check';
        unset($payload['lead']['delivery']);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        $leadIdRaw = (string) $response->json('data.lead_id', '');
        $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.client.email'));
        $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for GC-5 test');

        $item = $this->findServiceItemFromOrder($leadId, 'GC-5');
        $this->assertNotNull($item);

        $requiredKeys = [
            'pid',
            'basketType',
            'is_gift_card',
            'price',
            'sumPrice',
            'total_item_price',
            'terms',
            'terms_price',
            'size',
            'size_name',
            'whom',
            'card_type',
            'sender',
            'reseiver',
            'date',
            'torjname',
            'torjtext',
            'hide_nom',
            'service_path',
        ];
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $item, 'Missing GC-5 key: ' . $key);
        }

        $expectedAmount = round((float) $nominal, 2);
        $this->assertSame(5, (int) $item['pid']);
        $this->assertSame('5', (string) $item['basketType']);
        $this->assertEquals(1, (int) $item['is_gift_card']);
        $this->assertSame('online', (string) $item['card_type']);
        $this->assertSame('', (string) $item['sender']);
        $this->assertSame('', (string) $item['reseiver']);
        $this->assertSame('', (string) $item['date']);
        $this->assertSame('', (string) $item['torjname']);
        $this->assertSame('PHPUnit gift card check', (string) $item['torjtext']);
        $this->assertSame('false', (string) $item['hide_nom']);
        $this->assertSame('/new/gift-card', (string) $item['service_path']);
        $this->assertEquals($expectedAmount, round((float) $item['price'], 2));
        $this->assertEquals($expectedAmount, round((float) $item['sumPrice'], 2));
        $this->assertEquals($expectedAmount, round((float) $item['total_item_price'], 2));

        $deliveryRaw = DB::table('orders')->where('id', $leadId)->value('delivery');
        $delivery = json_decode((string) $deliveryRaw, true);
        $this->assertIsArray($delivery);
        $this->assertSame('email', (string) ($delivery['sposob'] ?? ''));
        $this->assertEquals(0.0, round((float) ($delivery['deliv_price'] ?? 999), 2));
    }

    /** @test */
    public function t05_017_gc5_rejects_pricing_and_non_email_delivery_for_online_card()
    {
        if (!Schema::hasTable('gift_card_noms')) {
            $this->markTestSkipped('gift_card_noms table is not available in current test DB connection');
        }

        $nominalText = DB::table('gift_card_noms')->orderBy('id')->value('text');
        if (!is_string($nominalText) || trim($nominalText) === '') {
            $this->markTestSkipped('gift_card_noms does not contain any nominal value');
        }

        preg_match('/\d+(?:[.,]\d+)?/', $nominalText, $matches);
        $nominal = isset($matches[0]) ? str_replace(',', '.', $matches[0]) : null;
        if ($nominal === null) {
            $this->markTestSkipped('Unable to parse nominal from gift_card_noms.text');
        }

        $payload = $this->payloadWithService('GC-5', [
            'amount' => $nominal,
            'card_type' => 'online',
        ]);
        $payload['lead']['delivery']['method'] = 'to_the_door';
        $payload['lead']['pricing'] = [
            'coupon_code' => '',
            'use_bonus' => true,
        ];

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t05_018_fc1_persists_family_constructor_item()
    {
        if (!Schema::hasTable('family_constructor')) {
            $this->markTestSkipped('family_constructor table is not available in current test DB connection');
        }

        $sizesRaw = (string) DB::table('family_constructor')->orderBy('id')->value('sizes');
        if (trim($sizesRaw) === '') {
            $this->markTestSkipped('family_constructor.sizes is empty');
        }

        $payload = $this->payloadWithService('FC-1', [
            'size' => '40x60',
            'holst_id' => 2,
            'packaging_id' => 3,
            'production_mode' => 'express',
        ]);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        $leadIdRaw = (string) $response->json('data.lead_id');
        $email = (string) data_get($payload, 'lead.client.email', '');
        $orderId = $this->resolveOrderIdForLeadResponse($leadIdRaw, $email);
        $this->assertGreaterThan(0, $orderId);

        $item = $this->findServiceItemFromOrder($orderId, 'FC-1');
        $this->assertNotNull($item);
        $this->assertSame('/family-constructor', (string) ($item['service_path'] ?? ''));
        $this->assertSame('1', (string) ($item['basketType'] ?? ''));
        $this->assertSame('1', (string) ($item['is_construct'] ?? ''));
        $this->assertSame('40x60', (string) ($item['size'] ?? ''));
        $this->assertSame('40x60', (string) ($item['size_name'] ?? ''));
        $this->assertNotEmpty($item['pack'] ?? '');
        $this->assertNotEmpty($item['terms'] ?? '');
        $this->assertGreaterThan(0, (float) ($item['price'] ?? 0));
        $this->assertGreaterThanOrEqual((float) ($item['price'] ?? 0), (float) ($item['total_item_price'] ?? 0));
    }

    /** @test */
    public function t05_019_hm44_exact_gallery_item_is_persisted()
    {
        if (!Schema::hasTable('gallery_items') || !Schema::hasTable('orders')) {
            $this->markTestSkipped('gallery_items or orders tables are not available in current test DB connection');
        }

        $galleryItem = DB::table('gallery_items')
            ->where('active', 1)
            ->whereIn('id_type', [2, 3, 4])
            ->whereNotNull('custom_size_prices')
            ->where('custom_size_prices', '<>', '')
            ->orderBy('id')
            ->first(['id', 'name', 'custom_size_prices']);
        if (!$galleryItem) {
            $this->markTestSkipped('No active gallery_items with custom_size_prices found for HM-44 exact test');
        }

        if (!preg_match('/([0-9]+\s*x\s*[0-9]+)\s*\[/', (string) $galleryItem->custom_size_prices, $matches)) {
            $this->markTestSkipped('Unable to parse size from gallery_items.custom_size_prices');
        }

        $size = strtolower(str_replace(' ', '', (string) $matches[1]));
        $payload = $this->payloadWithService('HM-44', [
            'gallery_item_id' => (int) $galleryItem->id,
            'size' => $size,
            'holst_id' => 2,
            'decor_id' => 5,
            'packaging_id' => 3,
            'execution_id' => 1,
            'production_mode' => 'express',
        ]);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());
        $response->assertStatus(200)->assertJsonPath('status', 'ok');

        $leadIdRaw = (string) $response->json('data.lead_id', '');
        $leadId = $this->resolveOrderIdForLeadResponse($leadIdRaw, (string) data_get($payload, 'lead.client.email'));
        $this->assertGreaterThan(0, $leadId, 'Unable to resolve real order id for HM-44 exact test');

        $item = $this->findServiceItemFromOrder($leadId, 'HM-44');
        $this->assertNotNull($item);
        $this->assertSame('/new/gallery', (string) ($item['service_path'] ?? ''));
        $this->assertSame((string) $galleryItem->id, (string) ($item['pid'] ?? ''));
        $this->assertSame((string) $galleryItem->id, (string) ($item['gallery_item_id'] ?? ''));
        $this->assertSame($size, (string) ($item['size'] ?? ''));
        $this->assertSame($size, (string) ($item['size_name'] ?? ''));
        $this->assertSame('1', (string) ($item['basketType'] ?? ''));
        $this->assertEquals(1, (int) ($item['is_construct'] ?? 0));
        $this->assertNotSame('', trim((string) ($item['name'] ?? '')));
        $this->assertNotSame('', trim((string) ($item['type'] ?? '')));
        $this->assertGreaterThan(0, (float) ($item['price'] ?? 0));
        $this->assertGreaterThan(0, (float) ($item['terms_price'] ?? 0));
        $this->assertEquals(
            round((float) ($item['price'] ?? 0) + (float) ($item['terms_price'] ?? 0), 2),
            round((float) ($item['total_item_price'] ?? 0), 2)
        );
    }

    /** @test */
    public function t05_020_hm44_invalid_gallery_item_returns_validation_error()
    {
        $payload = $this->payloadWithService('HM-44', [
            'gallery_item_id' => 999999999,
            'size' => '30x20',
        ]);

        $response = $this->postJson(self::ENDPOINT, $payload, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    private function apiHeaders(): array
    {
        return [
            'X-Api-Key' => self::API_KEY,
            'Content-Type' => 'application/json',
        ];
    }

    private function payloadMinimal(): array
    {
        $uniq = $this->uniqueSuffix();
        $phone = '+38068' . substr($uniq, -7);

        return [
            'idempotency_key' => 'create_lead:minimal:' . $uniq,
            'source' => 'SA',
            'lead' => [
                'client' => [
                    'phone' => $phone,
                ],
                'channel' => 'whatsapp',
            ],
        ];
    }

    private function payloadFull(): array
    {
        $uniq = $this->uniqueSuffix();
        $phone = '+38068' . substr($uniq, -7);

        return [
            'idempotency_key' => 'create_lead:full:' . $uniq,
            'source' => 'SA',
            'lead' => [
                'external_ids' => [
                    'conversation_id' => 'CONV-' . $uniq,
                ],
                'client' => [
                    'phone' => $phone,
                    'name' => 'Ivan',
                ],
                'channel' => 'whatsapp',
                'initial_message' => 'Need consultation',
                'pipeline' => [
                    'id' => 'PIPE-1',
                ],
                'stage' => [
                    'id' => 'pegging',
                ],
                'service_request' => [
                    'service_id' => 'SRV-101',
                    'budget' => 1500,
                ],
                'fields' => [
                    'email' => 'ivan+' . $uniq . '@example.com',
                    'city' => 'Kyiv',
                ],
            ],
        ];
    }

    private function payloadWithService(string $serviceId, array $options = []): array
    {
        $uniq = $this->uniqueSuffix();
        $phone = '+37129' . substr($uniq, -6);
        $email = 'sa_lead_test_' . strtolower($serviceId) . '_' . $uniq . '@example.com';

        return [
            'idempotency_key' => 'create_lead:' . strtolower($serviceId) . ':' . $uniq,
            'source' => 'SA',
            'lead' => [
                'external_ids' => [
                    'conversation_id' => 'CONV-' . $serviceId . '-' . $uniq,
                ],
                'client' => [
                    'phone' => $phone,
                    'name' => 'SA Test',
                    'email' => $email,
                ],
                'channel' => 'whatsapp',
                'service_request' => [
                    'service_id' => $serviceId,
                    'country_code' => 'LV',
                    'options' => $options,
                    'notes' => 'PHPUnit completeness check',
                ],
                'delivery' => [
                    'method' => 'to_the_door',
                    'payment' => 'transfer',
                    'city' => 'Riga',
                    'address' => 'Test Address',
                    'postal_index' => 'LV-1010',
                    'country' => 'LV',
                ],
                'fields' => [
                    'email' => $email,
                    'city' => 'Riga',
                    'country_code' => 'LV',
                    'postal_index' => 'LV-1010',
                    'address' => 'Test Address',
                ],
            ],
        ];
    }

    private function uniqueSuffix(): string
    {
        return str_replace('.', '', (string) microtime(true)) . mt_rand(100, 999);
    }

    private function insertTestCoupon(array $overrides = []): int
    {
        $now = now();

        return (int) DB::table('coupons')->insertGetId(array_merge([
            'text' => 'SA-COUPON-' . strtoupper($this->uniqueSuffix()),
            'created_at' => $now,
            'updated_at' => $now,
            'value' => '10',
            'is_30_40_free' => 0,
            'is_dates_sale' => 0,
            'is_universal' => 0,
            'is_active' => 1,
            'is_multiuse' => 1,
            'user_id' => 0,
            'sale_date' => null,
            'is_facebook' => 0,
            'is_40_60' => 0,
            'is_1free' => 0,
            'free_delivery' => 0,
            'is_abandoned_basket' => 0,
            'is_giftcard' => 0,
            'is_offline' => 0,
            'order_id' => 0,
            'pdf' => null,
        ], $overrides));
    }

    private function insertTestUser(array $overrides = []): int
    {
        $now = now();
        $uniq = $this->uniqueSuffix();

        return (int) DB::table('users')->insertGetId(array_merge([
            'role_id' => 2,
            'email' => 'sa_user_' . $uniq . '@example.com',
            'avatar' => 'users/default.png',
            'password' => bcrypt('secret'),
            'first_name' => 'SA',
            'last_name' => 'User',
            'phone' => '+3712' . substr($uniq, -7),
            'news' => 'NO',
            'ad' => 'NO',
            'client_data' => 'NO',
            'registration_page' => 'https://viarcanvas.loc/api/sa/leads',
            'referrer_url' => null,
            'utm_parameters' => '[]',
            'user_agent' => 'PHPUnit',
            'last_ip' => '127.0.0.1',
            'settings' => json_encode(['locale' => 'lv']),
            'created_at' => $now,
            'updated_at' => $now,
            'type_id' => 1,
            'bonuses' => 0,
            'country' => 'LV',
            'is_30_40' => 0,
        ], $overrides));
    }

    private function findServiceItemFromOrder(int $orderId, string $serviceId): ?array
    {
        $rawItems = DB::table('orders')->where('id', $orderId)->value('items');
        if (!is_string($rawItems) || $rawItems === '') {
            return null;
        }

        $items = json_decode($rawItems, true);
        if (!is_array($items)) {
            return null;
        }

        foreach ($items as $item) {
            if (is_array($item) && (string) ($item['id'] ?? '') === $serviceId) {
                return $item;
            }
        }

        return null;
    }

    private function resolveOrderIdForLeadResponse(string $leadIdRaw, string $email): int
    {
        if ($leadIdRaw !== '' && ctype_digit($leadIdRaw)) {
            $orderId = (int) $leadIdRaw;
            if ($orderId > 0 && DB::table('orders')->where('id', $orderId)->exists()) {
                return $orderId;
            }
        }

        if ($leadIdRaw !== '') {
            $byConversation = DB::table('orders')->where('sa_conversation_id', $leadIdRaw)->orderByDesc('id')->value('id');
            if ($byConversation) {
                return (int) $byConversation;
            }
        }

        if ($email !== '') {
            $userId = DB::table('users')->where('email', $email)->value('id');
            if ($userId) {
                $orderId = DB::table('orders')->where('user_id', (int) $userId)->orderByDesc('id')->value('id');
                if ($orderId) {
                    return (int) $orderId;
                }
            }
        }

        return 0;
    }
}
