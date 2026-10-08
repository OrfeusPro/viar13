<?php

namespace Tests\Support;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CreatesSaServiceFixtures
{
    protected function createSaServiceFixtures(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        config(['services.sa_integration.api_key' => 'test-key', 'cache.default' => 'array']);
        \Illuminate\Support\Facades\Http::preventStrayRequests();
        \Illuminate\Support\Facades\Mail::fake();
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
