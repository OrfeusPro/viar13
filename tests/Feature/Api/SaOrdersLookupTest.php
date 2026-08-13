<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SaOrdersLookupTest extends TestCase
{
    private const ENDPOINT = '/api/sa/orders/lookup';
    private const API_KEY = 'test-key';

    /** @test */
    public function t18_001_lookup_by_order_id_returns_order_payload()
    {
        $order = $this->latestOrder();
        if (!$order) {
            $this->markTestSkipped('No orders found in current test DB connection');
        }

        $response = $this->getJson(self::ENDPOINT . '?order_id=' . $order->id, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.orders.0.order_id', (int) $order->id)
            ->assertJsonStructure([
                'data' => [
                    'orders' => [
                        [
                            'lead_id',
                            'order_id',
                            'status' => ['id', 'title'],
                            'payment' => ['method', 'status', 'status_title'],
                            'pricing' => ['currency', 'items_amount', 'delivery_amount', 'total_amount'],
                            'client',
                            'recipient',
                            'delivery',
                            'billing' => ['is_company', 'invoice_uuid', 'company'],
                            'comments',
                            'products',
                            'sa',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function t18_002_lookup_by_phone_returns_all_matching_orders()
    {
        $phone = $this->knownOrderPhone();
        if ($phone === null) {
            $this->markTestSkipped('No order phone found in current test DB connection');
        }

        $response = $this->getJson(self::ENDPOINT . '?phone=' . urlencode($phone), $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        $this->assertGreaterThanOrEqual(1, (int) $response->json('meta.count'));
        $this->assertIsArray($response->json('data.orders'));
    }

    /** @test */
    public function t18_003_missing_lookup_key_returns_validation_error()
    {
        $response = $this->getJson(self::ENDPOINT, $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t18_003a_lang_changes_human_readable_titles_only()
    {
        $order = $this->latestOrderWithStatus('completed');
        if (!$order) {
            $this->markTestSkipped('No completed order found in current test DB connection');
        }

        $response = $this->getJson(self::ENDPOINT . '?order_id=' . $order->id . '&lang=en', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('meta.lookup.lang', 'en')
            ->assertJsonPath('data.orders.0.status.id', 'completed')
            ->assertJsonPath('data.orders.0.status.title', 'Completed');
    }

    /** @test */
    public function t18_003b_product_image_reads_active_image_and_cleans_undefined_options()
    {
        $order = DB::table('orders')->where('id', 17335)->first();
        if (!$order) {
            $order = DB::table('orders')
                ->where('items', 'like', '%activeImage%')
                ->where('items', 'like', '%undefined%')
                ->orderBy('id', 'desc')
                ->first();
        }

        if (!$order) {
            $this->markTestSkipped('No order with activeImage and undefined options found in current test DB connection');
        }

        $response = $this->getJson(self::ENDPOINT . '?order_id=' . $order->id . '&lang=ru', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        $product = collect($response->json('data.orders.0.products'))
            ->first(function ($product) {
                return !empty($product['image'] ?? null);
            });

        $this->assertNotNull($product);
        $this->assertNotEmpty($product['image'] ?? null);
        $this->assertContains($product['service_id'] ?? '', ['HM-2', 'HM-3']);
        $this->assertNotSame('undefined', data_get($product, 'options.form'));
        $this->assertNotSame('undefined', data_get($product, 'options.canvas'));
    }

    /** @test */
    public function t18_003c_artist_flow_order_returns_artist_images_and_caricature_service_id()
    {
        $order = DB::table('orders')
            ->where(function ($query) {
                $query->whereNotNull('painter_images')
                    ->orWhereNotNull('painter_sketch_images');
            })
            ->where('items', 'like', '%Caricature%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$order) {
            $this->markTestSkipped('No artist-flow caricature order found in current test DB connection');
        }

        $response = $this->getJson(self::ENDPOINT . '?order_id=' . $order->id . '&lang=ru', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.orders.0.products.0.service_id', 'HM-27');

        $this->assertNotEmpty($response->json('data.orders.0.artist.painter_images') ?: $response->json('data.orders.0.artist.painter_sketch_images'));
    }

    /** @test */
    public function t18_003d_company_order_returns_billing_company_block()
    {
        $order = DB::table('orders')
            ->where(function ($query) {
                $query->where('ur_name', '<>', '')
                    ->orWhere('ur_name_l', '<>', '')
                    ->orWhere('ur_reg_num', '<>', '')
                    ->orWhere('ur_legal_addr', '<>', '')
                    ->orWhere('ur_bank_acc_code', '<>', '');
            })
            ->orderBy('id', 'desc')
            ->first();

        if (!$order) {
            $this->markTestSkipped('No company order found in current test DB connection');
        }

        $response = $this->getJson(self::ENDPOINT . '?order_id=' . $order->id . '&lang=ru', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.orders.0.billing.is_company', true);

        $this->assertNotEmpty(array_filter((array) $response->json('data.orders.0.billing.company')));
    }

    /** @test */
    public function t18_004_invalid_phone_returns_validation_error()
    {
        $response = $this->getJson(self::ENDPOINT . '?phone=123', $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t18_005_missing_api_key_returns_unauthorized()
    {
        $response = $this->getJson(self::ENDPOINT . '?order_id=1');

        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    private function latestOrder()
    {
        if (!Schema::hasTable('orders')) {
            return null;
        }

        return DB::table('orders')
            ->orderBy('id', 'desc')
            ->first();
    }

    private function latestOrderWithStatus(string $status)
    {
        if (!Schema::hasTable('orders')) {
            return null;
        }

        return DB::table('orders')
            ->where('status', $status)
            ->orderBy('id', 'desc')
            ->first();
    }

    private function knownOrderPhone(): ?string
    {
        if (!Schema::hasTable('orders')) {
            return null;
        }

        $orders = DB::table('orders')
            ->select(['delivery'])
            ->whereNotNull('delivery')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        foreach ($orders as $order) {
            $delivery = json_decode((string) $order->delivery, true);
            if (!is_array($delivery)) {
                continue;
            }

            foreach (['payer_phone', 'phone'] as $key) {
                $phone = trim((string) ($delivery[$key] ?? ''));
                if ($phone !== '') {
                    return $phone;
                }
            }
        }

        return null;
    }

    private function apiHeaders(): array
    {
        return [
            'X-Api-Key' => self::API_KEY,
            'Content-Type' => 'application/json',
        ];
    }
}
