<?php

namespace Tests\Feature\Checkout;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class CheckoutUiRegressionTest extends TestCase
{
    public function test_phone_renders_one_matching_flag_even_when_country_rows_repeat(): void
    {
        $template = file_get_contents(resource_path('views/theme/viar/cart/step2_data.blade.php'));
        $start = strpos($template, '<div class="country-item country-item-active">');
        $end = strpos($template, '<div class="country-list">', $start);
        $template = substr($template, $start, $end - $start);
        $countries = collect([
            ['country_code' => 'LV', 'country_name' => 'Latvija'],
            ['country_code' => 'LV', 'country_name' => 'Latvija duplicate'],
            ['country_code' => 'RU', 'country_name' => 'Russia'],
        ]);
        foreach (['LV', 'RU', 'unknown'] as $country) {
            $html = Blade::render($template, ['user' => ['country' => $country], 'c_tels' => $countries]);
            $this->assertSame(1, substr_count($html, '<img '));
            $this->assertStringContainsString('data-country="LV"', $html);
            $this->assertStringContainsString('/lv.svg', $html);
        }
    }

    public function test_cart_notice_uses_page_locale_and_accessible_dialog(): void
    {
        foreach (['lv', 'ru', 'en', 'lt', 'et', 'de', 'pl'] as $locale) {
            app()->setLocale($locale);
            $html = view('theme.viar.cart.notice')->render();
            $this->assertStringContainsString('<dialog ', $html);
            $this->assertStringContainsString('aria-describedby="cart-notice-message"', $html);
            $this->assertStringContainsString(trans('cart_new.notice_title'), $html);
            $this->assertStringNotContainsString('alert(', $html);
            $this->assertNotSame('cart_new.notice_close', trans('cart_new.notice_close'));
            if ($locale !== 'ru') {
                $this->assertStringNotContainsString('Невозможно удалить', $html);
            }
        }
    }

    public function test_main_product_removal_is_blocked_without_changing_basket(): void
    {
        $basket = [
            ['basketType' => '1', 'price' => 20, 'count' => 1],
            ['basketType' => '1', 'price' => 10, 'count' => 1, 'is_recommendation' => true],
        ];
        $response = $this->withSession(['basket' => $basket])->post(route('remove_item_from_basket'), ['basketId' => 0]);
        $response->assertOk();
        $data = json_decode($response->getContent(), true);
        $this->assertSame(0, $data['success']);
        $this->assertSame('cannot_remove_main_product', $data['error_code']);
        $this->assertSame($basket, session('basket'));
    }
}
