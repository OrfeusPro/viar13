<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class SaServiceSizesTest extends TestCase
{
    use \Tests\Support\CreatesSaServiceFixtures;
    private const API_KEY = 'test-key';

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSaServiceFixtures();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sizes_endpoint_returns_sizes_for_canvas_service()
    {
        $response = $this->getJson('/api/sa/services/HM-2/sizes?country_code=LV', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.service_id', 'HM-2')
            ->assertJsonPath('meta.country_code', 'LV');

        $sizes = collect($response->json('data.sizes'));
        $size3040 = $sizes->firstWhere('size', '30x40');
        $this->assertNotNull($size3040);
        $this->assertSame(15.0, (float) data_get($size3040, 'price.amount'));
        $this->assertSame(45.0, (float) data_get($size3040, 'price.original_amount'));
        $this->assertTrue((bool) data_get($size3040, 'price.is_discounted'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function price_by_size_returns_exact_size_price()
    {
        $response = $this->getJson('/api/sa/services/HM-2/price-by-size?size=40x60&country_code=LV', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.service_id', 'HM-2')
            ->assertJsonPath('data.size', '40x60')
            ->assertJsonPath('data.price.amount', fn ($value) => (is_int($value) || is_float($value)) && (float) $value === 22.0)
            ->assertJsonPath('data.price.original_amount', fn ($value) => (is_int($value) || is_float($value)) && (float) $value === 65.0)
            ->assertJsonPath('data.price.is_discounted', true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function price_by_size_applies_country_multiplier()
    {
        $response = $this->getJson('/api/sa/services/HM-2/price-by-size?size=40x60&country_code=FI', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('meta.country_multiplier', 1.3)
            ->assertJsonPath('data.price.amount', 28.6);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function unknown_service_returns_404()
    {
        $response = $this->getJson('/api/sa/services/HM-9999/sizes?country_code=LV', $this->apiHeaders());

        $response->assertStatus(404)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'SERVICE_NOT_FOUND');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function unknown_size_returns_404()
    {
        $response = $this->getJson('/api/sa/services/HM-2/price-by-size?size=999x999&country_code=LV', $this->apiHeaders());

        $response->assertStatus(404)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'SIZE_NOT_FOUND');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function missing_api_key_returns_unauthorized()
    {
        $response = $this->getJson('/api/sa/services/HM-2/sizes?country_code=LV');
        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function modular_sizes_use_id_type_2_only()
    {
        $response = $this->getJson('/api/sa/services/HM-43/sizes?country_code=LV', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.service_id', 'HM-43');

        $sizes = collect($response->json('data.sizes'));
        $this->assertNotNull($sizes->firstWhere('size', '40x60'));
        $this->assertNull($sizes->firstWhere('size', '90x90'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function modular_price_by_size_uses_id_type_2_only()
    {
        $response = $this->getJson('/api/sa/services/HM-43/price-by-size?size=40x60&country_code=LV', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.service_id', 'HM-43')
            ->assertJsonPath('data.price.amount', fn ($value) => (is_int($value) || is_float($value)) && (float) $value === 28.0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function gallery_catalog_exact_item_sizes_use_selected_gallery_item_matrix()
    {
        $response = $this->getJson('/api/sa/services/HM-44/sizes?gallery_item_id=1004&country_code=LV', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.service_id', 'HM-44')
            ->assertJsonPath('data.gallery_item_id', 1004);

        $sizes = collect($response->json('data.sizes'));
        $this->assertNotNull($sizes->firstWhere('size', '30x20'));
        $this->assertNull($sizes->firstWhere('size', '90x90'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function gallery_catalog_exact_item_price_by_size_uses_selected_gallery_item_and_country_multiplier()
    {
        $response = $this->getJson('/api/sa/services/HM-44/price-by-size?gallery_item_id=1004&size=50x70&country_code=FI', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.service_id', 'HM-44')
            ->assertJsonPath('data.gallery_item_id', 1004)
            ->assertJsonPath('data.size', '50x70')
            ->assertJsonPath('meta.country_multiplier', 1.3)
            ->assertJsonPath('data.price.amount', 35.1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function gallery_catalog_unknown_exact_item_returns_not_found()
    {
        $response = $this->getJson('/api/sa/services/HM-44/sizes?gallery_item_id=999999&country_code=LV', $this->apiHeaders());

        $response->assertStatus(404)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'GALLERY_ITEM_NOT_FOUND');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function collage_sizes_use_a_collage_head_source()
    {
        $response = $this->getJson('/api/sa/services/HM-3/sizes?country_code=LV', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.service_id', 'HM-3');

        $sizes = collect($response->json('data.sizes'));
        $size3030 = $sizes->firstWhere('size', '40x40');
        $this->assertNotNull($size3030);
        $this->assertSame(22.0, (float) data_get($size3030, 'price.amount'));
        $this->assertSame(47.0, (float) data_get($size3030, 'price.original_amount'));
        $this->assertTrue((bool) data_get($size3030, 'price.is_discounted'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function simpsons_fallback_by_slug_works_for_sizes_and_price()
    {
        $sizesResponse = $this->getJson('/api/sa/services/HM-47/sizes?country_code=LV', $this->apiHeaders());
        $sizesResponse->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.service_id', 'HM-47');

        $sizes = collect($sizesResponse->json('data.sizes'));
        $size3040 = $sizes->firstWhere('size', '30x40');
        $this->assertNotNull($size3040);
        $this->assertSame(44.0, (float) data_get($size3040, 'price.amount'));
        $this->assertSame(90.0, (float) data_get($size3040, 'price.original_amount'));
        $this->assertTrue((bool) data_get($size3040, 'price.is_discounted'));

        $priceResponse = $this->getJson('/api/sa/services/HM-47/price-by-size?size=40x60&country_code=LV', $this->apiHeaders());
        $priceResponse->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.service_id', 'HM-47')
            ->assertJsonPath('data.price.amount', fn ($value) => (is_int($value) || is_float($value)) && (float) $value === 66.0)
            ->assertJsonPath('data.price.original_amount', fn ($value) => (is_int($value) || is_float($value)) && (float) $value === 120.0)
            ->assertJsonPath('data.price.is_discounted', true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function gift_card_sizes_return_nominals_without_country_multiplier()
    {
        $response = $this->getJson('/api/sa/services/GC-5/sizes?country_code=FI', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.service_id', 'GC-5')
            ->assertJsonPath('data.service_path', '/new/gift-card')
            ->assertJsonPath('meta.country_multiplier', 1.3);

        $sizes = collect($response->json('data.sizes'));
        $nominal20 = $sizes->firstWhere('size', '20');
        $this->assertNotNull($nominal20);
        $this->assertSame('nominal', (string) data_get($nominal20, 'format'));
        $this->assertSame(20.0, (float) data_get($nominal20, 'price.amount'));
        $this->assertNull(data_get($nominal20, 'price.original_amount'));
        $this->assertFalse((bool) data_get($nominal20, 'price.is_discounted'));
        $this->assertNull(data_get($nominal20, 'width'));
        $this->assertNull(data_get($nominal20, 'height'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function gift_card_price_by_size_returns_exact_nominal()
    {
        $response = $this->getJson('/api/sa/services/GC-5/price-by-size?size=50&country_code=FI', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.service_id', 'GC-5')
            ->assertJsonPath('data.size', '50')
            ->assertJsonPath('data.format', 'nominal')
            ->assertJsonPath('data.price.amount', fn ($value) => (is_int($value) || is_float($value)) && (float) $value === 50.0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function family_constructor_sizes_are_returned_from_real_family_source()
    {
        $response = $this->getJson('/api/sa/services/FC-1/sizes?country_code=LV', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.service_id', 'FC-1')
            ->assertJsonPath('data.service_path', '/family-constructor');

        $sizes = collect($response->json('data.sizes'));
        $size3040 = $sizes->firstWhere('size', '30x40');
        $this->assertNotNull($size3040);
        $this->assertSame(15.0, (float) data_get($size3040, 'price.amount'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function family_constructor_price_by_size_uses_size_matrix_and_country_multiplier()
    {
        $response = $this->getJson('/api/sa/services/FC-1/price-by-size?size=40x60&country_code=FI', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.service_id', 'FC-1')
            ->assertJsonPath('data.size', '40x60')
            ->assertJsonPath('meta.country_multiplier', 1.3)
            ->assertJsonPath('data.price.amount', 28.6);
    }

    private function apiHeaders(): array
    {
        return [
            'X-Api-Key' => self::API_KEY,
            'Content-Type' => 'application/json',
        ];
    }

}
