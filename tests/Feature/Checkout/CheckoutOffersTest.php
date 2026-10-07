<?php

namespace Tests\Feature\Checkout;

use App\Http\Controllers\BasketController;
use App\Repositories\BasketRepository;
use App\Services\CheckoutCouponService;
use App\Services\ImageSaverService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CheckoutOffersTest extends TestCase
{
    public function test_size_endpoint_preserves_photo_options_and_ignores_browser_price()
    {
        Schema::create('canvas_header', function (Blueprint $table) {
            $table->increments('id');
            $table->text('sizes_30x40');
        });
        DB::table('canvas_header')->insert(['sizes_30x40' => '20x30[15],30x40[22-12]s,40x60[20]']);
        $item = ['basketType' => '1', 'is_canvas_inter' => 1, 'sizeId' => '20x30', 'price' => 25,
            'add_price' => 25, 'count' => 2, 'sumPrice' => 50, 'savedImage' => '/test/photo.jpg',
            'canvasId' => 2, 'executionId' => 2, 'boxIds' => [3], 'terms_price' => 7];
        $controller = \Mockery::mock(BasketController::class, [app(ImageSaverService::class)])->makePartial();
        $controller->shouldReceive('get_basket')->andReturnUsing(function () {
            return array_merge(session('basket'), ['totalPrice' => 50, 'coupon_id' => 0]);
        });
        $this->app->instance(BasketController::class, $controller);
        $this->withSession(['basket' => [0 => $item]])->postJson(route('cart.replace.size'),
            ['basket_key' => 0, 'new_size' => '30x40s', 'new_price' => .01])->assertStatus(422);
        $this->postJson(route('cart.replace.size'),
            ['basket_key' => 0, 'new_size' => '40x60', 'new_price' => .01])->assertOk()->assertJson(['success' => true]);
        $saved = session('basket');
        $this->assertCount(1, $saved);
        $this->assertSame('/test/photo.jpg', $saved[0]['savedImage']);
        $this->assertSame([3], $saved[0]['boxIds']);
        $this->assertSame(7, $saved[0]['terms_price']);
        $this->assertSame(27.0, $saved[0]['price']);
        $this->assertSame(27.0, $saved[0]['checkout_unit_price']);
        $repo = app(BasketRepository::class);
        $this->assertEquals(54, $repo->getBasketProperties($saved)['totalPrice']);
        $this->assertEquals(108, $repo->getBasketProperties($saved, 2)['totalPrice']);
        $this->assertEquals(54, $repo->getBasketProperties($saved)['totalPrice']);
        $this->assertEquals(2, session('basket.0.count'));
        $this->assertSame(20.0, $saved[0]['checkout_size_base_price']);
        $this->assertSame(10.0, $saved[0]['checkout_size_extras_price']);
        $this->assertSame('50.60', app(CheckoutCouponService::class)->calculate($repo->getBasketProperties($saved), 'universal', '10%'));
        $this->assertSame('101.20', app(CheckoutCouponService::class)->calculate($repo->getBasketProperties($saved, 2), 'universal', '10%'));
        $this->postJson(route('cart.replace.size'), ['basket_key' => -1, 'new_size' => '40x60'])->assertStatus(422);
    }

    public function test_recommendation_replay_recognizes_both_legacy_markers()
    {
        foreach (['is_recommendation', 'is_canvas_recommendation'] as $flag) {
            $this->withSession(['basket' => [['basketType' => '1', $flag => true]]])
                ->postJson(route('basket.add_canvas_recommendation'), [])
                ->assertOk()->assertJson(['success' => true, 'status' => 'duplicate']);
            $this->postJson(route('cart.add.recommended'), ['price' => .01])
                ->assertOk()->assertJson(['status' => 'duplicate']);
            $this->assertCount(1, session('basket'));
        }
    }

    public function test_gallery_recommendation_price_is_calculated_from_catalog()
    {
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->decimal('price_from', 10, 2);
            $table->integer('active')->default(1);
            $table->integer('id_type')->nullable();
            $table->text('image')->nullable();
            $table->text('images')->nullable();
            $table->text('custom_size_prices')->nullable();
        });
        Schema::create('gallery_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url');
        });
        $id = DB::table('gallery_items')->insertGetId(['name' => 'Test canvas', 'price_from' => 100,
            'active' => 1, 'custom_size_prices' => '30x40[100]']);
        $this->withSession(['basket' => [], 'recommendation_discount_'.$id => 30])->postJson(route('cart.add.recommended'),
            ['item_id' => $id, 'price' => .01])->assertOk()->assertJson(['success' => true]);
        $this->assertSame(70.0, session('basket.0.price'));
        $this->assertSame(70.0, session('basket.0.checkout_unit_price'));
        $this->postJson(route('cart.add.recommended'), ['item_id' => $id, 'price' => .01])
            ->assertOk()->assertJson(['status' => 'duplicate']);
        $this->assertCount(1, session('basket'));
        DB::table('gallery_items')->where('id', $id)->update(['custom_size_prices' => '30x40[100]h,40x60[120]t,60x80[140]s']);
        $this->withSession(['basket' => [], 'recommendation_discount_'.$id => 30])->postJson(route('cart.add.recommended'), ['item_id' => $id, 'price' => .01])->assertStatus(422);
        $this->assertCount(0, session('basket'));
    }

    public function test_canvas_recommendation_skips_hit_top_and_super_deal_sizes()
    {
        Schema::create('canvas_header', function (Blueprint $table) {
            $table->increments('id');
            $table->text('sizes_30x40');
        });
        Schema::create('gallery_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url');
        });
        DB::table('canvas_header')->insert(['sizes_30x40' => '20x30[15],30x40[17]h,30x45[19]t,40x60[24]s,60x80[38]']);
        $method = new \ReflectionMethod(BasketRepository::class, 'generateCanvasRecommendation');
        $method->setAccessible(true);
        $offer = $method->invoke(app(BasketRepository::class), '20x30', 2);
        $this->assertSame('60x80', $offer['size']);
        $this->assertSame('76.00', $offer['discounted_price']);
        DB::table('canvas_header')->update(['sizes_30x40' => '30x40[17]h,30x45[19]t,40x60[24]s']);
        $this->assertNull($method->invoke(app(BasketRepository::class), '20x30', 1));
    }
}
