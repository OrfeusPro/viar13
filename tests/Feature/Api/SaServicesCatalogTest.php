<?php

namespace Tests\Feature\Api;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SaServicesCatalogTest extends TestCase
{
    private const ENDPOINT = '/api/sa/services-catalog';
    private const FULL_ENDPOINT = '/api/sa/catalog-full';
    private const API_KEY = 'test-key';

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCatalogFixtures();
    }

    /** @test */
    public function t03_001_request_without_params_returns_active_catalog()
    {
        $response = $this->getJson(self::ENDPOINT, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('meta.country_code', 'LV')
            ->assertJsonPath('meta.country_multiplier', 1.0);

        $services = $response->json('data.services');
        $this->assertNotEmpty($services);
        foreach ($services as $service) {
            $this->assertTrue((bool) $service['is_active']);
        }

        $serviceIds = array_column($services, 'id');
        $this->assertNotContains('HM-3', $serviceIds);
    }

    /** @test */
    public function t03_002_lang_ru_returns_ru_content()
    {
        $response = $this->getJson(self::ENDPOINT . '?lang=ru', $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertContains(
            $response->json('data.services.0.name'),
            ['Шарж / Карикатура', 'Custom Caricature']
        );
    }

    /** @test */
    public function t03_003_lang_en_returns_localized_content()
    {
        $response = $this->getJson(self::ENDPOINT . '?lang=en', $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertSame('Custom Caricature', $response->json('data.services.0.name'));
    }

    /** @test */
    public function t03_004_updated_since_filters_out_old_records()
    {
        $response = $this->getJson(
            self::ENDPOINT . '?updated_since=2026-01-01T00:00:00Z&include_inactive=true',
            $this->apiHeaders()
        );

        $response->assertStatus(200);

        $serviceIds = array_column($response->json('data.services'), 'id');
        $this->assertContains('HM-1', $serviceIds);
        $this->assertNotContains('HM-2', $serviceIds);
    }

    /** @test */
    public function t03_005_include_inactive_flag_works()
    {
        $activeOnly = $this->getJson(self::ENDPOINT . '?include_inactive=false', $this->apiHeaders());
        $activeOnly->assertStatus(200);

        $all = $this->getJson(self::ENDPOINT . '?include_inactive=true', $this->apiHeaders());
        $all->assertStatus(200);

        $activeIds = array_column($activeOnly->json('data.services'), 'id');
        $allIds = array_column($all->json('data.services'), 'id');

        $this->assertNotContains('HM-3', $activeIds);
        $this->assertContains('HM-3', $allIds);
        $this->assertGreaterThan(count($activeIds), count($allIds));
    }

    /** @test */
    public function t03_006_invalid_lang_returns_validation_error()
    {
        $response = $this->getJson(self::ENDPOINT . '?lang=invalid-lang', $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function t03_007_missing_api_key_returns_unauthorized()
    {
        $response = $this->getJson(self::ENDPOINT);

        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    /** @test */
    public function t03_008_country_code_lv_returns_meta_multiplier()
    {
        $response = $this->getJson(self::ENDPOINT . '?lang=en&country_code=LV', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('meta.country_code', 'LV')
            ->assertJsonPath('meta.country_multiplier', 1.0);
    }

    /** @test */
    public function t03_009_country_multiplier_affects_price()
    {
        $lv = $this->getJson(self::ENDPOINT . '?lang=en&country_code=LV', $this->apiHeaders());
        $fi = $this->getJson(self::ENDPOINT . '?lang=en&country_code=FI', $this->apiHeaders());

        $lv->assertStatus(200);
        $fi->assertStatus(200);

        $lvPrice = (float) $lv->json('data.services.0.price.amount');
        $fiPrice = (float) $fi->json('data.services.0.price.amount');

        $this->assertGreaterThan(0, $lvPrice);
        $this->assertGreaterThan($lvPrice, $fiPrice);
        $this->assertSame(1.3, (float) $fi->json('meta.country_multiplier'));
    }

    /** @test */
    public function t03_010_invalid_country_code_falls_back_to_lv()
    {
        $response = $this->getJson(self::ENDPOINT . '?lang=en&country_code=ZZZ', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('meta.country_code', 'LV')
            ->assertJsonPath('meta.country_multiplier', 1.0);
    }

    /** @test */
    public function t03_011_new_gallery_price_uses_real_types_2_3_4()
    {
        $response = $this->getJson(self::ENDPOINT . '?lang=en&country_code=LV&include_inactive=true', $this->apiHeaders());
        $response->assertStatus(200);

        $service = collect($response->json('data.services'))
            ->firstWhere('short_description', '/new/gallery');

        $this->assertNotNull($service);
        $this->assertSame(5.0, (float) data_get($service, 'price.amount'));
    }

    /** @test */
    public function t03_012_modular_generator_price_uses_type_2_only()
    {
        $response = $this->getJson(self::ENDPOINT . '?lang=en&country_code=LV&include_inactive=true', $this->apiHeaders());
        $response->assertStatus(200);

        $service = collect($response->json('data.services'))
            ->firstWhere('short_description', '/modular-generator');

        $this->assertNotNull($service);
        $this->assertSame(25.0, (float) data_get($service, 'price.amount'));
    }

    /** @test */
    public function t03_013_simpsons_price_fallbacks_to_slug_simpsons_when_main_slug_missing()
    {
        DB::table('gallery_items')->where('slug', 'simpsons-portrait')->delete();
        DB::table('gallery_items')->insert([
            'id' => 1010,
            'id_type' => 5,
            'name' => 'Simpsons Legacy Slug',
            'active' => 1,
            'price_from' => 52,
            'custom_size_prices' => '30x40[100-44],40x60[120-66]',
            'is_sharj' => '0',
            'slug' => 'simpsons',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->getJson(self::ENDPOINT . '?lang=en&country_code=LV&include_inactive=true', $this->apiHeaders());
        $response->assertStatus(200);

        $service = collect($response->json('data.services'))
            ->firstWhere('short_description', '/simpsons');

        $this->assertNotNull($service);
        $this->assertSame(44.0, (float) data_get($service, 'price.amount'));
    }

    /** @test */
    public function t03_014_ru_uses_translated_header_menu_title_and_link()
    {
        $response = $this->getJson(self::ENDPOINT . '?lang=ru&country_code=LV&include_inactive=true', $this->apiHeaders());
        $response->assertStatus(200);

        $service = collect($response->json('data.services'))
            ->firstWhere('id', 'HM-1');

        $this->assertNotNull($service);
        $serviceName = (string) data_get($service, 'name');
        $this->assertStringContainsString('Карикатура', $serviceName);
        $this->assertStringContainsString('/ru/new/caricature', (string) data_get($service, 'description'));
    }

    /** @test */
    public function t03_015_uk_falls_back_to_ru_when_uk_translation_missing()
    {
        DB::table('translations')
            ->where('table_name', 'header_menu')
            ->where('column_name', 'title')
            ->where('foreign_key', 1)
            ->where('locale', 'uk')
            ->delete();

        DB::table('translations')
            ->where('table_name', 'header_menu')
            ->where('column_name', 'link')
            ->where('foreign_key', 1)
            ->where('locale', 'uk')
            ->delete();

        $response = $this->getJson(self::ENDPOINT . '?lang=uk&country_code=LV&include_inactive=true', $this->apiHeaders());
        $response->assertStatus(200);

        $service = collect($response->json('data.services'))
            ->firstWhere('id', 'HM-1');

        $this->assertNotNull($service);
        $this->assertContains(
            (string) data_get($service, 'name'),
            ['РЁР°СЂР¶ / РљР°СЂРёРєР°С‚СѓСЂР°', 'Шарж / Карикатура']
        );
        $this->assertStringContainsString('/ru/new/caricature', (string) data_get($service, 'description'));
    }

    /** @test */
    public function t03_016_uk_categories_follow_ru_contract()
    {
        $ru = $this->getJson(self::ENDPOINT . '?lang=ru&country_code=LV&include_inactive=true', $this->apiHeaders());
        $uk = $this->getJson(self::ENDPOINT . '?lang=uk&country_code=LV&include_inactive=true', $this->apiHeaders());

        $ru->assertStatus(200);
        $uk->assertStatus(200);

        $ruCategories = collect($ru->json('data.categories'))->pluck('name', 'id')->toArray();
        $ukCategories = collect($uk->json('data.categories'))->pluck('name', 'id')->toArray();

        $this->assertSame($ruCategories, $ukCategories);
    }

    /** @test */
    public function t03_017_uk_full_catalog_matches_ru_when_uk_translations_absent()
    {
        DB::table('translations')
            ->where('table_name', 'header_menu')
            ->where('locale', 'uk')
            ->delete();

        $ru = $this->getJson(self::ENDPOINT . '?lang=ru&country_code=LV&include_inactive=true', $this->apiHeaders());
        $uk = $this->getJson(self::ENDPOINT . '?lang=uk&country_code=LV&include_inactive=true', $this->apiHeaders());

        $ru->assertStatus(200);
        $uk->assertStatus(200);

        $this->assertSame($ru->json('data.categories'), $uk->json('data.categories'));
        $this->assertSame($ru->json('data.services'), $uk->json('data.services'));
    }

    /** @test */
    public function t03_018_gift_card_service_is_exposed_in_catalog_from_real_tables()
    {
        $response = $this->getJson(self::ENDPOINT . '?lang=en&country_code=FI&include_inactive=true', $this->apiHeaders());

        $response->assertStatus(200);

        $service = collect($response->json('data.services'))->firstWhere('id', 'GC-5');
        $this->assertNotNull($service);
        $this->assertSame('CAT-GIFTS', (string) data_get($service, 'category_id'));
        $this->assertSame('/new/gift-card', (string) data_get($service, 'short_description'));
        $this->assertSame(20.0, (float) data_get($service, 'price.amount'));

        $categories = collect($response->json('data.categories'))->pluck('name', 'id');
        $this->assertSame('Gift cards', (string) $categories->get('CAT-GIFTS'));
    }

    /** @test */
    public function t03_019_family_constructor_service_is_exposed_in_catalog()
    {
        $response = $this->getJson(self::ENDPOINT . '?lang=ru&country_code=LV&include_inactive=true', $this->apiHeaders());

        $response->assertStatus(200);

        $service = collect($response->json('data.services'))->firstWhere('id', 'FC-1');
        $this->assertNotNull($service);
        $this->assertSame('CAT-PORTRAITS', (string) data_get($service, 'category_id'));
        $this->assertSame('/family-constructor', (string) data_get($service, 'short_description'));
        $this->assertSame(15.0, (float) data_get($service, 'price.amount'));
        $this->assertStringContainsString('Семей', (string) data_get($service, 'name'));
    }

    /** @test */
    public function t03_020_full_catalog_returns_sizes_and_photo_for_each_service()
    {
        $response = $this->getJson(self::FULL_ENDPOINT . '?lang=en&country_code=LV&include_inactive=true', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('meta.country_code', 'LV')
            ->assertJsonPath('meta.country_multiplier', 1.0);

        $caricature = collect($response->json('data.services'))->firstWhere('id', 'HM-1');
        $this->assertNotNull($caricature);
        $this->assertNotEmpty(data_get($caricature, 'sizes'));
        $this->assertStringContainsString('gallery-items/test-caricature', (string) data_get($caricature, 'photo.url'));
        $this->assertSame('30x40', (string) data_get($caricature, 'sizes.0.size'));
        $this->assertSame(50.0, (float) data_get($caricature, 'sizes.0.price.amount'));
        $this->assertSame(100.0, (float) data_get($caricature, 'sizes.0.price.original_amount'));
        $this->assertTrue((bool) data_get($caricature, 'sizes.0.price.is_discounted'));
        $this->assertTrue((bool) data_get($caricature, 'price.is_discounted'));

        $canvas = collect($response->json('data.services'))->firstWhere('id', 'HM-4');
        $this->assertNotNull($canvas);
        $this->assertNotEmpty(data_get($canvas, 'sizes'));
        $this->assertStringContainsString('site-images/canvas-main.jpg', (string) data_get($canvas, 'photo.url'));
        $canvasSize3040 = collect((array) data_get($canvas, 'sizes'))->firstWhere('size', '30x40');
        $this->assertNotNull($canvasSize3040);
        $this->assertSame(20.0, (float) data_get($canvasSize3040, 'price.amount'));
        $this->assertSame(30.0, (float) data_get($canvasSize3040, 'price.original_amount'));
        $this->assertTrue((bool) data_get($canvasSize3040, 'price.is_discounted'));

        $giftCard = collect($response->json('data.services'))->firstWhere('id', 'GC-5');
        $this->assertNotNull($giftCard);
        $this->assertNotEmpty(data_get($giftCard, 'sizes'));
        $this->assertSame('nominal', (string) data_get($giftCard, 'sizes.0.format'));
        $this->assertStringContainsString('site-images/gift-card-main.png', (string) data_get($giftCard, 'photo.url'));
        $this->assertFalse((bool) data_get($giftCard, 'sizes.0.price.is_discounted'));
    }

    /** @test */
    public function t03_021_full_catalog_infers_country_from_client_phone_when_country_not_provided()
    {
        $response = $this->getJson(self::FULL_ENDPOINT . '?lang=en&include_inactive=true&client_phone=%2B358401234567', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('meta.country_code', 'FI')
            ->assertJsonPath('meta.country_multiplier', 1.3);

        $canvas = collect($response->json('data.services'))->firstWhere('id', 'HM-4');
        $this->assertNotNull($canvas);

        $canvasSize3040 = collect((array) data_get($canvas, 'sizes'))->firstWhere('size', '30x40');
        $this->assertNotNull($canvasSize3040);
        $this->assertSame(26.0, (float) data_get($canvasSize3040, 'price.amount'));
        $this->assertSame(39.0, (float) data_get($canvasSize3040, 'price.original_amount'));
    }

    private function apiHeaders(): array
    {
        return [
            'X-Api-Key' => self::API_KEY,
            'Content-Type' => 'application/json',
        ];
    }

    private function prepareCatalogFixtures(): void
    {
        Schema::dropIfExists('translations');
        Schema::dropIfExists('header_menu');
        Schema::dropIfExists('country_tels');
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('canvas_header');
        Schema::dropIfExists('a_collage_head');
        Schema::dropIfExists('gift_card_noms');
        Schema::dropIfExists('gift_card');
        Schema::dropIfExists('family_constructor');
        Schema::dropIfExists('site_images');

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

        Schema::create('translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('table_name');
            $table->string('column_name');
            $table->integer('foreign_key');
            $table->string('locale', 10);
            $table->text('value');
            $table->timestamps();
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
            $table->text('images')->nullable();
            $table->string('new_main_image')->nullable();
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

        Schema::create('site_images', function (Blueprint $table) {
            $table->increments('id');
            $table->string('page');
            $table->string('position_name');
            $table->string('img')->nullable();
            $table->boolean('is_show')->default(true);
            $table->timestamps();
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
                'id' => 1,
                'title' => 'Custom Caricature',
                'link' => 'https://viarcanvas.com/en/new/caricature',
                'menu_pos' => 1,
                'order' => 1,
                'is_show' => 1,
                'created_at' => Carbon::parse('2026-02-10 10:00:00'),
                'updated_at' => Carbon::parse('2026-02-10 10:00:00'),
            ],
            [
                'id' => 2,
                'title' => 'Custom Art Portrait',
                'link' => 'https://viarcanvas.com/en/new/graphic-portrait/portrait-dream-art',
                'menu_pos' => 1,
                'order' => 2,
                'is_show' => 1,
                'created_at' => Carbon::parse('2020-01-10 10:00:00'),
                'updated_at' => Carbon::parse('2020-01-10 10:00:00'),
            ],
            [
                'id' => 3,
                'title' => 'Simpsons',
                'link' => 'https://viarcanvas.com/en/simpsons',
                'menu_pos' => 1,
                'order' => 3,
                'is_show' => 0,
                'created_at' => Carbon::parse('2026-02-10 10:00:00'),
                'updated_at' => Carbon::parse('2026-02-10 10:00:00'),
            ],
            [
                'id' => 4,
                'title' => 'Canvas Printing',
                'link' => 'https://viarcanvas.com/en/new/canvas',
                'menu_pos' => 2,
                'order' => 1,
                'is_show' => 1,
                'created_at' => Carbon::parse('2026-02-10 10:00:00'),
                'updated_at' => Carbon::parse('2026-02-10 10:00:00'),
            ],
            [
                'id' => 5,
                'title' => 'Canvas Catalog',
                'link' => 'https://viarcanvas.com/en/new/gallery',
                'menu_pos' => 2,
                'order' => 2,
                'is_show' => 1,
                'created_at' => Carbon::parse('2026-02-10 10:00:00'),
                'updated_at' => Carbon::parse('2026-02-10 10:00:00'),
            ],
            [
                'id' => 6,
                'title' => 'Modular Canvas',
                'link' => 'https://viarcanvas.com/en/modular-generator',
                'menu_pos' => 2,
                'order' => 3,
                'is_show' => 1,
                'created_at' => Carbon::parse('2026-02-10 10:00:00'),
                'updated_at' => Carbon::parse('2026-02-10 10:00:00'),
            ],
        ]);

        DB::table('translations')->insert([
            [
                'table_name' => 'header_menu',
                'column_name' => 'title',
                'foreign_key' => 1,
                'locale' => 'ru',
                'value' => 'Шарж / Карикатура',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'table_name' => 'header_menu',
                'column_name' => 'title',
                'foreign_key' => 1,
                'locale' => 'uk',
                'value' => 'Шарж / Карикатура',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'table_name' => 'header_menu',
                'column_name' => 'link',
                'foreign_key' => 1,
                'locale' => 'ru',
                'value' => 'https://viarcanvas.com/ru/new/caricature',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'table_name' => 'header_menu',
                'column_name' => 'link',
                'foreign_key' => 1,
                'locale' => 'uk',
                'value' => 'https://viarcanvas.com/uk/new/caricature',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'table_name' => 'family_constructor',
                'column_name' => 'title',
                'foreign_key' => 1,
                'locale' => 'ru',
                'value' => 'Семейная идиллия',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'table_name' => 'family_constructor',
                'column_name' => 'meta_title',
                'foreign_key' => 1,
                'locale' => 'ru',
                'value' => 'Семейная идиллия',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
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
                'images' => json_encode(['gallery-items/test-caricature.jpg']),
                'new_main_image' => 'gallery-items/test-caricature-main.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 1002,
                'id_type' => 5,
                'name' => 'Portrait Dream Art',
                'active' => 1,
                'price_from' => 55,
                'custom_size_prices' => '30x40[110-70],40x60[130-90]',
                'is_sharj' => '0',
                'slug' => 'portrait-dream-art',
                'images' => json_encode(['gallery-items/test-portrait.jpg']),
                'new_main_image' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 1003,
                'id_type' => 5,
                'name' => 'Simpsons Portrait',
                'active' => 1,
                'price_from' => 50,
                'custom_size_prices' => '30x40[90-40],40x60[110-60]',
                'is_sharj' => '0',
                'slug' => 'simpsons-portrait',
                'images' => json_encode(['gallery-items/test-simpsons.jpg']),
                'new_main_image' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 1004,
                'id_type' => 1,
                'name' => 'Legacy Gallery Type 1',
                'active' => 1,
                'price_from' => 1,
                'custom_size_prices' => null,
                'is_sharj' => '0',
                'slug' => 'legacy-gallery',
                'images' => null,
                'new_main_image' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 1005,
                'id_type' => 2,
                'name' => 'Gallery Type 2',
                'active' => 1,
                'price_from' => 25,
                'custom_size_prices' => null,
                'is_sharj' => '0',
                'slug' => 'gallery-type-2',
                'images' => json_encode(['gallery-items/test-gallery-type-2.jpg']),
                'new_main_image' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 1006,
                'id_type' => 3,
                'name' => 'Gallery Type 3',
                'active' => 1,
                'price_from' => 30,
                'custom_size_prices' => null,
                'is_sharj' => '0',
                'slug' => 'gallery-type-3',
                'images' => json_encode(['gallery-items/test-gallery-type-3.jpg']),
                'new_main_image' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 1007,
                'id_type' => 4,
                'name' => 'Gallery Type 4',
                'active' => 1,
                'price_from' => 22,
                'custom_size_prices' => null,
                'is_sharj' => '0',
                'slug' => 'gallery-type-4',
                'images' => json_encode(['gallery-items/test-gallery-type-4.jpg']),
                'new_main_image' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 1008,
                'id_type' => 2,
                'name' => 'Modular Type 2',
                'active' => 1,
                'price_from' => 40,
                'custom_size_prices' => '40x60[80-28],50x70[95-35]',
                'is_sharj' => '0',
                'slug' => 'modular-type-2',
                'images' => json_encode(['gallery-items/test-modular.jpg']),
                'new_main_image' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 1009,
                'id_type' => 3,
                'name' => 'Non-Modular Type 3',
                'active' => 1,
                'price_from' => 12,
                'custom_size_prices' => '40x60[30-5]',
                'is_sharj' => '0',
                'slug' => 'non-modular-type-3',
                'images' => json_encode(['gallery-items/test-non-modular.jpg']),
                'new_main_image' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        DB::table('canvas_header')->insert([
            'id' => 1,
            'name' => 'Canvas test',
            'sizes_30x40' => '30x40[30-20],40x60[40-25]',
            'sizes_38x38' => '30x30[30-22]',
            'sizes_40x30' => '40x30[30-20]',
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

        DB::table('gift_card')->insert([
            'id' => 1,
            'title' => 'Gift card',
            'desc' => 'Gift card purchase flow',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::parse('2026-03-08 10:00:00'),
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

        DB::table('site_images')->insert([
            [
                'page' => 'canvas',
                'position_name' => 'mcard_about_block_1',
                'img' => 'site-images/canvas-main.jpg',
                'is_show' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'page' => 'gift-card',
                'position_name' => 'gift_card_slide',
                'img' => 'site-images/gift-card-main.png',
                'is_show' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
