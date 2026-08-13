<?php

namespace Tests\Feature\Api;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SaServiceSizesTest extends TestCase
{
    private const API_KEY = 'test-key';

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareFixtures();
    }

    /** @test */
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

    /** @test */
    public function price_by_size_returns_exact_size_price()
    {
        $response = $this->getJson('/api/sa/services/HM-2/price-by-size?size=40x60&country_code=LV', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.service_id', 'HM-2')
            ->assertJsonPath('data.size', '40x60')
            ->assertJsonPath('data.price.amount', 22.0)
            ->assertJsonPath('data.price.original_amount', 65.0)
            ->assertJsonPath('data.price.is_discounted', true);
    }

    /** @test */
    public function price_by_size_applies_country_multiplier()
    {
        $response = $this->getJson('/api/sa/services/HM-2/price-by-size?size=40x60&country_code=FI', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('meta.country_multiplier', 1.3)
            ->assertJsonPath('data.price.amount', 28.6);
    }

    /** @test */
    public function unknown_service_returns_404()
    {
        $response = $this->getJson('/api/sa/services/HM-9999/sizes?country_code=LV', $this->apiHeaders());

        $response->assertStatus(404)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'SERVICE_NOT_FOUND');
    }

    /** @test */
    public function unknown_size_returns_404()
    {
        $response = $this->getJson('/api/sa/services/HM-2/price-by-size?size=999x999&country_code=LV', $this->apiHeaders());

        $response->assertStatus(404)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'SIZE_NOT_FOUND');
    }

    /** @test */
    public function missing_api_key_returns_unauthorized()
    {
        $response = $this->getJson('/api/sa/services/HM-2/sizes?country_code=LV');
        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    /** @test */
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

    /** @test */
    public function modular_price_by_size_uses_id_type_2_only()
    {
        $response = $this->getJson('/api/sa/services/HM-43/price-by-size?size=40x60&country_code=LV', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.service_id', 'HM-43')
            ->assertJsonPath('data.price.amount', 28.0);
    }

    /** @test */
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

    /** @test */
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

    /** @test */
    public function gallery_catalog_unknown_exact_item_returns_not_found()
    {
        $response = $this->getJson('/api/sa/services/HM-44/sizes?gallery_item_id=999999&country_code=LV', $this->apiHeaders());

        $response->assertStatus(404)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'GALLERY_ITEM_NOT_FOUND');
    }

    /** @test */
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

    /** @test */
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
            ->assertJsonPath('data.price.amount', 66.0)
            ->assertJsonPath('data.price.original_amount', 120.0)
            ->assertJsonPath('data.price.is_discounted', true);
    }

    /** @test */
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

    /** @test */
    public function gift_card_price_by_size_returns_exact_nominal()
    {
        $response = $this->getJson('/api/sa/services/GC-5/price-by-size?size=50&country_code=FI', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.service_id', 'GC-5')
            ->assertJsonPath('data.size', '50')
            ->assertJsonPath('data.format', 'nominal')
            ->assertJsonPath('data.price.amount', 50.0);
    }

    /** @test */
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

    /** @test */
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

    private function prepareFixtures(): void
    {
        Schema::dropIfExists('header_menu');
        Schema::dropIfExists('country_tels');
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('canvas_header');
        Schema::dropIfExists('a_collage_head');
        Schema::dropIfExists('gift_card_noms');
        Schema::dropIfExists('gift_card');
        Schema::dropIfExists('family_constructor');

        Schema::create('header_menu', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->string('link')->nullable();
            $table->text('images')->nullable();
            $table->integer('menu_pos')->nullable();
            $table->integer('order')->nullable();
            $table->timestamps();
            $table->integer('is_show')->nullable();
            $table->text('png')->nullable();
        });

        Schema::create('country_tels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('phone_code')->nullable();
            $table->string('country_name')->nullable();
            $table->timestamps();
            $table->string('country_code', 3)->nullable();
            $table->decimal('deliv_price', 8, 2)->nullable();
            $table->decimal('high_price', 8, 2)->nullable();
            $table->decimal('price_country_mltpr', 8, 3)->nullable();
            $table->string('mask')->nullable();
            $table->string('placeholder')->nullable();
            $table->integer('sort')->nullable();
            $table->integer('delivery_venipak')->nullable();
        });

        Schema::create('gallery_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_type')->nullable();
            $table->string('name')->nullable();
            $table->integer('active')->nullable();
            $table->decimal('price_from', 10, 2)->nullable();
            $table->text('custom_size_prices')->nullable();
            $table->string('is_sharj')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();
        });

        Schema::create('canvas_header', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->text('sizes_30x40')->nullable();
            $table->text('sizes_38x38')->nullable();
            $table->text('sizes_40x30')->nullable();
            $table->timestamps();
        });

        Schema::create('a_collage_head', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->text('sizes_30x40')->nullable();
            $table->text('sizes_38x38')->nullable();
            $table->text('sizes_40x30')->nullable();
            $table->timestamps();
        });

        Schema::create('gift_card', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->text('desc')->nullable();
            $table->timestamps();
        });

        Schema::create('gift_card_noms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('text')->nullable();
            $table->timestamps();
        });

        Schema::create('family_constructor', function (Blueprint $table) {
            $table->increments('id');
            $table->string('meta_title')->nullable();
            $table->string('title')->nullable();
            $table->text('sizes')->nullable();
            $table->timestamps();
            $table->text('meta_desc')->nullable();
        });

        DB::table('country_tels')->insert([
            [
                'phone_code' => '+371',
                'country_name' => 'Latvia',
                'country_code' => 'LV',
                'price_country_mltpr' => 1.0,
                'sort' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'phone_code' => '+358',
                'country_name' => 'Finland',
                'country_code' => 'FI',
                'price_country_mltpr' => 1.3,
                'sort' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        DB::table('header_menu')->insert([
            [
                'id' => 2,
                'title' => 'Canvas Printing',
                'link' => 'https://viarcanvas.com/en/new/canvas',
                'menu_pos' => 2,
                'order' => 1,
                'is_show' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'title' => 'Collage on Canvas',
                'link' => 'https://viarcanvas.com/en/collage',
                'menu_pos' => 2,
                'order' => 2,
                'is_show' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 27,
                'title' => 'Custom Caricature',
                'link' => 'https://viarcanvas.com/en/new/caricature',
                'menu_pos' => 1,
                'order' => 1,
                'is_show' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 43,
                'title' => 'Modular Canvas',
                'link' => 'https://viarcanvas.com/en/modular-generator',
                'menu_pos' => 2,
                'order' => 3,
                'is_show' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 44,
                'title' => 'Canvas Catalog',
                'link' => 'https://viarcanvas.com/en/new/gallery',
                'menu_pos' => 2,
                'order' => 4,
                'is_show' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 47,
                'title' => 'Simpsons',
                'link' => 'https://viarcanvas.com/en/simpsons',
                'menu_pos' => 1,
                'order' => 2,
                'is_show' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        DB::table('canvas_header')->insert([
            'id' => 1,
            'name' => 'Canvas test',
            'sizes_30x40' => '30x40[45-15],40x60[65-22]',
            'sizes_38x38' => '30x30[24-18]',
            'sizes_40x30' => '40x30[45-19]',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('a_collage_head')->insert([
            'id' => 1,
            'name' => 'Collage test',
            'sizes_30x40' => '30x40[45-15],40x60[65-22]',
            'sizes_38x38' => '40x40[47-22]',
            'sizes_40x30' => '40x30[45-19]',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('gallery_items')->insert([
            [
                'id' => 1001,
                'id_type' => 5,
                'name' => 'Caricature',
                'active' => 1,
                'price_from' => 45,
                'custom_size_prices' => '30x40[100-50],40x60[120-60]',
                'is_sharj' => '1',
                'slug' => 'caricature',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 1002,
                'id_type' => 2,
                'name' => 'Modular Type 2',
                'active' => 1,
                'price_from' => 40,
                'custom_size_prices' => '40x60[80-28],50x70[95-35]',
                'is_sharj' => '0',
                'slug' => 'modular-type-2',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 1003,
                'id_type' => 3,
                'name' => 'Non-Modular Type 3',
                'active' => 1,
                'price_from' => 12,
                'custom_size_prices' => '90x90[20-5]',
                'is_sharj' => '0',
                'slug' => 'non-modular-type-3',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 1004,
                'id_type' => 4,
                'name' => 'Gallery exact item',
                'active' => 1,
                'price_from' => 18,
                'custom_size_prices' => '30x20[18],50x70[35-27]',
                'is_sharj' => '0',
                'slug' => 'gallery-exact-item',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 1010,
                'id_type' => 5,
                'name' => 'Simpsons Legacy Slug',
                'active' => 1,
                'price_from' => 50,
                'custom_size_prices' => '30x40[90-44],40x60[120-66]',
                'is_sharj' => '0',
                'slug' => 'simpsons',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        DB::table('gift_card')->insert([
            'id' => 1,
            'title' => 'Gift card',
            'desc' => 'Gift card purchase flow',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('gift_card_noms')->insert([
            [
                'id' => 1,
                'text' => '20',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'text' => '50',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        DB::table('family_constructor')->insert([
            'id' => 1,
            'title' => 'Family idyll',
            'meta_title' => 'Family idyll',
            'sizes' => '30x40[45-15],40x60[65-22],50x50[68-30]',
            'meta_desc' => 'Family constructor test',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::parse('2026-03-08 10:00:00'),
        ]);
    }
}
