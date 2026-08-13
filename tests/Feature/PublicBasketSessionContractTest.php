<?php

namespace Tests\Feature;

use App\Entity\BasketType;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Requests\PortraitBasketRequest;
use App\Http\Requests\FutureArtRequest;
use App\Http\Requests\ConstructBasketRequest;
use App\Repositories\BasketRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PublicBasketSessionContractTest extends TestCase
{
    public function test_basket_and_cart_mutations_are_not_excluded_from_csrf_verification(): void
    {
        $middleware = new class($this->app, $this->app['encrypter']) extends VerifyCsrfToken
        {
            public function excludes(Request $request): bool
            {
                return $this->inExceptArray($request);
            }
        };

        foreach ([
            '/basket/add',
            '/lv/basket/add/portrait',
            '/basket/update/count',
            '/ru/basket/remove',
            '/cart/add-recommended',
            '/uk/cart/set_email',
        ] as $path) {
            $this->assertFalse(
                $middleware->excludes(Request::create($path, 'POST')),
                "CSRF verification must be enabled for [{$path}]."
            );
        }

        $this->assertTrue(
            $middleware->excludes(Request::create('/admin/upload/tinyimage', 'POST'))
        );
    }

    public function test_canvas_add_rejects_an_incomplete_payload_as_json(): void
    {
        $this->post('/basket/add', [
            'basketType' => BasketType::CANVAS_TYPE,
            'price' => 20,
        ])->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors([
                'userImage',
                'formId',
                'sizeId',
                'executionId',
                'canvasId',
            ]);
    }

    public function test_canvas_add_accepts_a_100_mb_source_without_an_application_size_limit(): void
    {
        $repository = $this->mock(BasketRepository::class);
        $repository->shouldReceive('addToBasket')->once()->andReturn(1);

        $this->post('/basket/add', [
            'basketType' => BasketType::CANVAS_TYPE,
            'price' => 20,
            'userImage' => UploadedFile::fake()->image('print-source.jpg')->size(102400),
            'formId' => 1,
            'sizeId' => '60x40',
            'executionId' => 1,
            'canvasId' => 1,
        ])->assertOk()
            ->assertJson([
                'success' => true,
                'count' => 1,
            ]);
    }

    public function test_current_canvas_builder_optional_fields_match_the_server_contract(): void
    {
        $repository = $this->mock(BasketRepository::class);
        $repository->shouldReceive('addToBasket')->once()->andReturn(1);

        $this->post('/basket/add', [
            'basketType' => BasketType::CANVAS_TYPE,
            'price' => 120,
            'userImage' => UploadedFile::fake()->image('print-source.jpg')->size(102400),
            'formId' => 1,
            'sizeId' => '60x40h',
            'executionId' => 2,
            'canvasId' => 3,
            'decorationId' => 4,
            'ram_id' => 5,
            'terms_price' => 10,
            'boxIds' => '[3,4]',
            'Image3d' => 'data:image/jpeg;base64,' . base64_encode('preview'),
            'photo_ex' => UploadedFile::fake()->image('example.jpg')->size(102400),
            'orig_images' => [
                UploadedFile::fake()->image('original.jpg')->size(102400),
            ],
        ])->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_canvas_add_rejects_malformed_preview_and_packaging_without_mutation(): void
    {
        $this->post('/basket/add', [
            'basketType' => BasketType::CANVAS_TYPE,
            'price' => 120,
            'userImage' => UploadedFile::fake()->image('print-source.jpg'),
            'formId' => 1,
            'sizeId' => '60x40',
            'executionId' => 2,
            'canvasId' => 3,
            'boxIds' => '[0,"forged"]',
            'Image3d' => 'not-an-image',
            'terms_price' => 121,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['boxIds', 'Image3d', 'terms_price'])
            ->assertSessionMissing('basket');
    }

    public function test_portrait_upload_rules_accept_a_100_mb_source(): void
    {
        $validator = Validator::make([
            'price' => 40,
            'orig_images' => [
                UploadedFile::fake()->create('portrait.psd', 102400, 'image/vnd.adobe.photoshop'),
            ],
        ], (new PortraitBasketRequest())->rules());

        $this->assertTrue($validator->passes(), $validator->errors()->toJson());
    }

    public function test_future_art_rules_accept_a_100_mb_print_source(): void
    {
        $validator = Validator::make([
            'name' => 'Print customer',
            'tel' => '+37120123456',
            'email' => 'customer@example.test',
            'images' => [
                UploadedFile::fake()->image('print-source.jpg')->size(102400),
            ],
        ], (new FutureArtRequest())->rules());

        $this->assertTrue($validator->passes(), $validator->errors()->toJson());
    }

    public function test_future_art_rejects_missing_contact_and_media_as_json(): void
    {
        $this->post('/basket/add/future_art')
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid file count or format.')
            ->assertJsonValidationErrors(['name', 'tel', 'email', 'images']);
    }

    public function test_construct_original_file_rules_accept_a_100_mb_source(): void
    {
        $validator = Validator::make([
            'name' => 'Modular pictures',
            'price' => 80,
            'size' => '120x80',
            'is_orig_file' => 1,
            'image' => UploadedFile::fake()->image('modular-source.jpg')->size(102400),
            'collageSvgImage_hash' => md5('modular-source'),
        ], (new ConstructBasketRequest())->rules());

        $this->assertTrue($validator->passes(), $validator->errors()->toJson());
    }

    public function test_construct_rejects_a_payload_without_a_real_image_source(): void
    {
        $this->post('/basket/add/construct', [
            'name' => 'Collage',
            'price' => 50,
            'size' => '60x40',
            'image_offset' => 'not-base64',
        ])->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['image_offset']);

        $this->post('/basket/add/construct', [
            'name' => 'Modular pictures',
            'price' => 80,
            'size' => '120x80',
            'is_orig_file' => 1,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['image', 'collageSvgImage_hash']);
    }

    public function test_construct_base64_family_stores_generated_image_and_session_state(): void
    {
        Storage::fake('uploads');
        $repository = $this->mock(BasketRepository::class);
        $repository->shouldReceive('normalizeBasket')->once()->andReturn([]);
        $repository->shouldReceive('saveBasketToAbandonedCartModel')->once();

        $this->post('/basket/add/construct', [
            'name' => 'Family collage',
            'price' => 50,
            'size' => '60x40',
            'image_offset' => base64_encode('valid-image-payload'),
        ])->assertOk()
            ->assertJsonPath('count', 1);

        $activeImage = session('basket.0.activeImage');

        $this->assertIsString($activeImage);
        Storage::disk('uploads')->assertExists(ltrim($activeImage, '/'));
    }

    public function test_gallery_recommendation_ignores_a_forged_frontend_price(): void
    {
        Schema::create('gallery_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->decimal('price_from', 10, 2);
            $table->text('images')->nullable();
            $table->timestamps();
        });

        try {
            DB::table('gallery_items')->insert([
                'id' => 50,
                'name' => 'Server-priced art',
                'price_from' => 100,
                'images' => '["catalog/art.jpg"]',
            ]);

            $repository = $this->mock(BasketRepository::class);
            $repository->shouldReceive('saveBasketToAbandonedCartModel')->once();

            $this->withSession(['recommendation_discount_50' => 30])
                ->post('/basket/add/recommended', [
                    'item_id' => 50,
                    'price' => 0.01,
                ])->assertOk()
                ->assertJsonPath('success', true)
                ->assertSessionHas('basket.0.original_price', 100.0)
                ->assertSessionHas('basket.0.price', 70.0)
                ->assertSessionMissing('recommendation_discount_50');
        } finally {
            Schema::dropIfExists('gallery_items');
        }
    }

    public function test_recommendation_requires_a_server_issued_session_offer(): void
    {
        Schema::create('gallery_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->decimal('price_from', 10, 2);
            $table->text('images')->nullable();
            $table->timestamps();
        });

        try {
            DB::table('gallery_items')->insert([
                'id' => 51,
                'name' => 'No offer art',
                'price_from' => 100,
            ]);

            $this->post('/basket/add/recommended', [
                'item_id' => 51,
                'price' => 0.01,
            ])->assertBadRequest()
                ->assertJsonPath('success', false);
        } finally {
            Schema::dropIfExists('gallery_items');
        }
    }

    public function test_cart_recommendation_also_ignores_the_frontend_price(): void
    {
        Schema::create('gallery_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('id_type')->nullable();
            $table->string('name');
            $table->decimal('price_from', 10, 2);
            $table->text('images')->nullable();
            $table->text('custom_size_prices')->nullable();
            $table->timestamps();
        });
        Schema::create('gallery_types', function (Blueprint $table): void {
            $table->id();
            $table->string('url')->nullable();
            $table->timestamps();
        });

        try {
            DB::table('gallery_items')->insert([
                'id' => 52,
                'name' => 'Modal art',
                'price_from' => 200,
                'images' => '["catalog/modal.jpg"]',
            ]);

            $repository = $this->mock(BasketRepository::class);
            $repository->shouldReceive('saveBasketToAbandonedCartModel')->once();

            $this->withSession(['recommendation_discount_52' => 30])
                ->post('/cart/add-recommended', [
                    'item_id' => 52,
                    'price' => 0.01,
                ])->assertOk()
                ->assertJsonPath('success', true)
                ->assertSessionHas('basket.0.price', 140.0)
                ->assertSessionMissing('recommendation_discount_52');
        } finally {
            Schema::dropIfExists('gallery_types');
            Schema::dropIfExists('gallery_items');
        }
    }

    public function test_canvas_recommendation_requires_an_existing_base_item(): void
    {
        $this->post('/basket/add-canvas-recommendation', [
            'size' => '60x40',
            'full_size' => '60x40h',
            'price' => 0.01,
            'userImage' => UploadedFile::fake()->image('canvas.jpg'),
        ])->assertBadRequest()
            ->assertJsonPath('success', false);
    }

    public function test_canvas_recommendation_uses_catalog_price_and_accepts_a_100_mb_source(): void
    {
        Storage::fake('uploads');
        Schema::create('canvas_header', function (Blueprint $table): void {
            $table->id();
            $table->text('sizes_30x40');
        });
        Schema::create('a_production_time', function (Blueprint $table): void {
            $table->id();
            $table->string('category');
            $table->string('standart_text');
            $table->decimal('standart_price', 10, 2);
            $table->timestamps();
        });
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name');
            $table->string('column_name');
            $table->unsignedBigInteger('foreign_key');
            $table->string('locale');
            $table->text('value')->nullable();
        });

        try {
            DB::table('canvas_header')->insert([
                'sizes_30x40' => '60x40h[100]',
            ]);
            DB::table('a_production_time')->insert([
                'category' => 'canvas',
                'standart_text' => 'Standard',
                'standart_price' => 5,
            ]);

            $repository = $this->mock(BasketRepository::class);
            $repository->shouldReceive('saveBasketToAbandonedCartModel')->once();

            $this->withSession([
                'basket' => [['pid' => 10, 'name' => 'Base item', 'count' => 1]],
            ])->post('/basket/add-canvas-recommendation', [
                'size' => '60x40',
                'full_size' => '60x40h',
                'price' => 0.01,
                'userImage' => UploadedFile::fake()->image('canvas.jpg')->size(102400),
            ])->assertOk()
                ->assertJsonPath('success', true)
                ->assertSessionHas('basket.1.price', 70.0);
        } finally {
            Schema::dropIfExists('translations');
            Schema::dropIfExists('a_production_time');
            Schema::dropIfExists('canvas_header');
        }
    }

    public function test_orphan_inter_and_module_endpoints_are_explicitly_retired(): void
    {
        Storage::fake('uploads');

        foreach ([
            '/basket/add/inter' => '/basket/add',
            '/basket/add/module' => '/basket/add/construct',
        ] as $endpoint => $replacement) {
            $this->post($endpoint, [
                'image' => 'data:image/png;base64,' . base64_encode('payload'),
                'price' => 0.01,
            ])->assertStatus(410)
                ->assertJsonPath('success', false)
                ->assertJsonPath('code', 'legacy_endpoint_retired')
                ->assertJsonPath('replacement', url($replacement))
                ->assertSessionMissing('basket');
        }

        $this->assertSame([], Storage::disk('uploads')->allFiles());
    }

    public function test_modular_add_normalizes_frontend_image_and_accepts_a_100_mb_source(): void
    {
        $repository = $this->mock(BasketRepository::class);
        $repository->shouldReceive('addToBasket')->once()->andReturn(1);

        $this->post('/basket/add', [
            'basketType' => BasketType::MODULAR_PICTURES_TYPE,
            'price' => 60,
            'size' => '90x60',
            'executionId' => 1,
            'image' => UploadedFile::fake()->image('modular-source.jpg')->size(102400),
        ])->assertOk()
            ->assertJson([
                'success' => true,
                'count' => 1,
            ]);
    }

    public function test_active_oil_portrait_payload_stores_original_once_and_uses_it_as_active_image(): void
    {
        Storage::fake('uploads');
        $repository = $this->mock(BasketRepository::class);
        $repository->shouldReceive('normalizeBasket')->once()->andReturn([]);
        $repository->shouldReceive('saveBasketToAbandonedCartModel')->once();

        $this->post('/basket/add/portrait', [
            'pid' => 'undefined',
            'price' => 40,
            'name' => 'Oil portrait',
            'size' => '60x40',
            'orig_images' => [
                UploadedFile::fake()->image('portrait.jpg')->size(102400),
            ],
        ])->assertOk()
            ->assertJsonPath('count', 1)
            ->assertSessionHas('basket.0.is_oil_portrait', 1)
            ->assertSessionHas('basket.0.activeImage', function ($value): bool {
                return is_string($value) && str_contains($value, '/uploads/');
            });

        $this->assertCount(1, Storage::disk('uploads')->allFiles('uploads'));
    }

    public function test_oil_portrait_requires_an_original_or_generated_image(): void
    {
        $this->post('/basket/add/portrait', [
            'pid' => 'undefined',
            'price' => 40,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('orig_images');
    }

    public function test_simple_generated_portrait_payload_stores_a_valid_data_image(): void
    {
        Storage::fake('uploads');
        $repository = $this->mock(BasketRepository::class);
        $repository->shouldReceive('normalizeBasket')->once()->andReturn([]);
        $repository->shouldReceive('saveBasketToAbandonedCartModel')->once();

        $this->post('/basket/add/portrait', [
            'pid' => 15,
            'price' => 40,
            'name' => 'Generated portrait',
            'is_gall_with_img' => 1,
            'image' => 'data:image/png;base64,'.base64_encode('valid-image-data'),
            'size' => '60x40',
        ])->assertOk()
            ->assertSessionHas('basket.0.is_gall_with_img', 1)
            ->assertSessionHas('basket.0.activeImage', function ($value): bool {
                return is_string($value) && str_ends_with($value, '.png');
            });

        $this->assertCount(1, Storage::disk('uploads')->allFiles('uploads'));
    }

    public function test_generated_portrait_rejects_malformed_base64(): void
    {
        $this->post('/basket/add/portrait', [
            'pid' => 15,
            'price' => 40,
            'is_gall_with_img' => 1,
            'image' => 'data:image/png;base64,not-valid***',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('image');
    }

    public function test_current_portrait_wizard_payload_preserves_style_fields_and_original(): void
    {
        Schema::dropIfExists('gallery_items');
        Schema::create('gallery_items', function (Blueprint $table): void {
            $table->id();
            $table->text('images')->nullable();
        });
        DB::table('gallery_items')->insert([
            'id' => 15,
            'images' => json_encode(['/catalog/portrait.jpg']),
        ]);

        Storage::fake('uploads');
        $repository = $this->mock(BasketRepository::class);
        $repository->shouldReceive('normalizeBasket')->once()->andReturn([]);
        $repository->shouldReceive('saveBasketToAbandonedCartModel')->once();

        $this->post('/basket/add/portrait', [
            'pid' => 15,
            'price' => 55,
            'terms_price' => 5,
            'name' => 'Simpsons portrait',
            'image' => 'undefined',
            'is_gall_with_img' => 'undefined',
            'size' => '60x40',
            'full_size' => '60x40',
            'users_count' => 2,
            'fon' => 'city',
            'obraz' => 'classic',
            'obraz_title' => 'Classic',
            'orig_images' => [
                UploadedFile::fake()->image('family.jpg')->size(102400),
            ],
        ])->assertOk()
            ->assertSessionHas('basket.0.activeImage', '/catalog/portrait.jpg')
            ->assertSessionHas('basket.0.price', 50.0)
            ->assertSessionHas('basket.0.fon', 'city')
            ->assertSessionHas('basket.0.obraz', 'classic')
            ->assertSessionHas('basket.0.obraz_title', 'Classic');

        $this->assertCount(1, Storage::disk('uploads')->allFiles('uploads'));
    }

    public function test_remove_requires_a_valid_basket_index_without_mutating_session(): void
    {
        $basket = [['name' => 'Canvas', 'price' => 20, 'count' => 1]];

        $this->withSession(['basket' => $basket])
            ->post('/basket/remove')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('basketId')
            ->assertSessionHas('basket', $basket);
    }

    public function test_count_update_rejects_zero_negative_and_excessive_values(): void
    {
        foreach ([0, -1, 100] as $count) {
            $this->withSession([
                'basket' => [['name' => 'Canvas', 'price' => 20, 'count' => 1]],
            ])->post('/basket/update/count', [
                'index' => 0,
                'count' => $count,
            ])->assertUnprocessable()
                ->assertJsonValidationErrors('count');
        }
    }

    public function test_count_update_persists_the_valid_session_state(): void
    {
        $basket = [['name' => 'Canvas', 'price' => 20, 'count' => 1]];
        $repository = $this->mock(BasketRepository::class);
        $repository->shouldReceive('normalizeBasket')->once()->andReturn($basket);
        $repository->shouldReceive('saveBasketToAbandonedCartModel')
            ->once()
            ->with([['name' => 'Canvas', 'price' => 20, 'count' => 3, 'total_item_price' => 60]]);

        $this->withSession(['basket' => $basket])
            ->post('/basket/update/count', ['index' => 0, 'count' => 3])
            ->assertOk()
            ->assertContent('{"success":1,"price":"60\u20ac"}')
            ->assertSessionHas('basket.0.count', 3)
            ->assertSessionHas('basket.0.total_item_price', 60);
    }

    public function test_remove_persists_the_remaining_session_state(): void
    {
        $basket = [
            ['name' => 'Canvas', 'price' => 20, 'count' => 1],
            ['name' => 'Portrait', 'price' => 40, 'count' => 1],
        ];
        $remaining = [1 => $basket[1]];
        $repository = $this->mock(BasketRepository::class);
        $repository->shouldReceive('normalizeBasket')->once()->andReturn($basket);
        $repository->shouldReceive('saveBasketToAbandonedCartModel')
            ->once()
            ->with($remaining);

        $this->withSession(['basket' => $basket])
            ->post('/basket/remove', ['basketId' => 0])
            ->assertOk()
            ->assertContent('{"success":1}')
            ->assertSessionHas('basket', $remaining);
    }
}
