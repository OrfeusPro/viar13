<?php

namespace Tests\Unit;

use App\Models\GalleryItem;
use App\Services\CheckoutCouponService;
use App\Services\CheckoutSizeOfferService;
use Tests\TestCase;

class CheckoutDiscountRulesTest extends TestCase
{
    public function test_promotional_goods_keep_their_price_and_giftcard_covers_all_goods()
    {
        $service = app(CheckoutCouponService::class);
        $basket = ['totalPrice' => 130,
            0 => ['sumPrice' => 100, 'count' => 1],
            1 => ['sumPrice' => 10, 'count' => 1, 'has_special_label' => true],
            2 => ['sumPrice' => 10, 'count' => 1, 'is_canvas_recommendation' => true],
            3 => ['sumPrice' => 10, 'count' => 1, 'is_recommendation' => true]];
        $this->assertSame('120.00', $service->calculate($basket, 'universal', '10%'));
        $this->assertSame('80.00', $service->calculate($basket, 'giftcard', '50'));
        $this->assertSame('0.00', $service->calculate($basket, 'giftcard', '500'));
        $this->assertSame('30.00', $service->calculate($basket, 'universal', '500'));
    }

    public function test_free_painting_skips_promotional_candidates_and_uses_one_unit()
    {
        $basket = ['totalPrice' => 70,
            0 => ['name' => 'Canvas', 'sizeId' => '30x40', 'sumPrice' => 10, 'count' => 1, 'has_special_label' => true],
            1 => ['name' => 'Canvas', 'sizeId' => '30x40', 'sumPrice' => 60, 'count' => 3]];
        $service = app(CheckoutCouponService::class);
        $this->assertSame('50.00', $service->calculate($basket, '30_40', '0'));
        $this->assertSame('50.00', $service->calculate($basket, '1free', '0'));
    }

    public function test_size_offer_skips_cheaper_sizes_and_preserves_option_costs()
    {
        $service = app(CheckoutSizeOfferService::class);
        $item = ['sizeId' => '20x30', 'price' => 25]; // 15 size + 10 options
        $source = '20x30[15],30x40[22-12]s,30x45[15],40x60[20]';
        $offer = $service->select($item, $source);
        $this->assertSame('40x60', $offer['alternative_size']);
        $this->assertSame(27.0, $offer['alternative_price']);
        $this->assertSame(2.0, $offer['extra']);
        $this->assertNull($service->select($item, '20x30[15],30x40[12]s'));
        $this->assertNull($service->select(['sizeId' => 'unknown', 'price' => 25], $source));
        $galleryOffer = $service->select(['sizeId' => 0, 'size_name' => '20x30', 'price' => 25], $source);
        $this->assertSame('40x60', $galleryOffer['alternative_size']);
    }

    public function test_offer_country_multiplier_is_applied_to_delta_only()
    {
        $offer = app(CheckoutSizeOfferService::class)->select(['sizeId' => '20x30', 'price' => 30],
            '20x30[15],40x60[20]', 2);
        $this->assertSame(34.0, $offer['alternative_price']);
        $this->assertSame(17.0, $offer['unit_base_price']);
    }

    public function test_offer_preserves_exact_ratio_and_orientation_or_is_hidden()
    {
        $service = app(CheckoutSizeOfferService::class);
        $source = '30x30[18],45x30[19],40x40[25],60x70[32],60x80[38],120x140[65],140x120[65]';
        $this->assertSame('40x40', $service->select(['sizeId' => '30x30', 'price' => 18], $source)['alternative_size']);
        $this->assertSame('120x140', $service->select(['sizeId' => '60x70', 'price' => 32], $source)['alternative_size']);
        $this->assertNull($service->select(['sizeId' => '60x70', 'price' => 32], '60x70[32],60x80[38],140x120[65]'));
        $this->assertSame('60x40', $service->select(['sizeId' => '30x20', 'price' => 15], '30x20[15],40x60[22],60x40[22]')['alternative_size']);
    }

    public function test_upgrade_and_coupon_discount_only_canvas_and_repeat_upgrade_is_not_compounded()
    {
        $sizes = app(CheckoutSizeOfferService::class);
        $first = $sizes->select(['sizeId' => '30x40', 'price' => 27], '30x40[17],60x80[38],90x120[80]');
        $this->assertSame(42.3, $first['alternative_price']); // 38 * .85 + 10 extras
        $next = $sizes->select(['sizeId' => '60x80', 'price' => 42.3, 'checkout_size_extras_price' => 10],
            '30x40[17],60x80[38],90x120[80]');
        $this->assertSame(78.0, $next['alternative_price']); // 80 * .85 + unchanged extras
        $coupon = app(CheckoutCouponService::class);
        $basket = ['totalPrice' => 64.6, 0 => ['sumPrice' => 64.6, 'count' => 2, 'checkout_size_discounted_base' => 32.3]];
        $this->assertSame('58.14', $coupon->calculate($basket, 'universal', '10%'));
        $basket = ['totalPrice' => 84.6, 0 => ['sumPrice' => 84.6, 'count' => 2, 'checkout_size_discounted_base' => 32.3]];
        $this->assertSame('78.14', $coupon->calculate($basket, 'universal', '10%'));
        foreach (['h', 't', 's'] as $label) {
            $this->assertNull($sizes->select(['sizeId' => '30x40', 'price' => 17], '30x40[17],60x80[38]'.$label));
            $this->assertSame('38.00', $coupon->calculate(['totalPrice' => 38, 0 => ['sizeId' => '60x80'.$label, 'sumPrice' => 38]], 'universal', '10%'));
        }
    }

    public function test_gallery_recommendations_skip_special_prices()
    {
        $service = app(CheckoutSizeOfferService::class);
        $item = new GalleryItem;
        $item->price_from = 10;
        $item->custom_size_prices = '30x40[10]h,40x60[20]t,60x80[38]';
        $this->assertSame(['size' => '60x80', 'price' => 38.0], $service->galleryRecommendation($item));
        $item->custom_size_prices = '30x40[10]h,40x60[20]t,60x80[38]s';
        $this->assertNull($service->galleryRecommendation($item));
    }
}
