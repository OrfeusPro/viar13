<?php

namespace Tests\Feature\Admin;

use App\Models\Orders;
use App\Models\Role;
use App\Models\User;
use App\Support\Admin\OrderProductPreview;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrderProductPreviewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Http::preventStrayRequests();
        Storage::fake('uploads');
        Schema::create('oil_header', function (Blueprint $table): void {
            $table->id();
            $table->text('images')->nullable();
        });
    }

    #[DataProvider('svgPaths')]
    public function test_existing_svg_companions_resolve_without_modifying_files(string $source): void
    {
        Storage::disk('uploads')->put('uploads/fixture.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');
        $before = Storage::disk('uploads')->get('uploads/fixture.svg');
        foreach (['is_construct', 'is_canvas_inter', 'is_modular_inter', 'is_oil_portrait', 'is_gall_with_img'] as $flag) {
            $this->assertSame(asset('uploads/fixture.svg'), OrderProductPreview::svgUrl(['activeImage' => $source, $flag => 1]));
        }
        $this->assertSame($before, Storage::disk('uploads')->get('uploads/fixture.svg'));
        Http::assertNothingSent();
    }

    public static function svgPaths(): array
    {
        return [
            ['/uploads/fixture.jpg'], ['https://viarcanvas.com/uploads/fixture.jpeg?ver=1'],
            ['/storage/fixture.png'], ['/public/storage/fixture.heic'], ['uploads/fixture.heif'],
        ];
    }

    public function test_missing_svg_and_ineligible_types_keep_raster_in_rendered_column(): void
    {
        $source = 'https://example.test/uploads/fixture.jpg';
        $html = $this->render(['activeImage' => $source, 'is_construct' => 1]);
        $this->assertStringContainsString('src="'.$source.'"', $html);
        Storage::disk('uploads')->put('uploads/fixture.svg', '<svg/>');
        foreach ([[], ['is_gall_with_img' => 0], ['is_canvas_collage' => 1], ['basketType' => 1]] as $flags) {
            $this->assertNull(OrderProductPreview::svgUrl(['activeImage' => $source] + $flags));
        }
        $html = $this->render(['activeImage' => $source, 'is_construct' => 1]);
        $this->assertStringContainsString('title="SVG-превью товара"', $html);
        $this->assertStringContainsString('href="'.$source.'"', $html);
        $this->assertStringContainsString('sandbox=""', $html);
        $this->assertStringContainsString('srcdoc="', $html);
        $this->assertStringNotContainsString('<svg', $html);
        Http::assertNothingSent();
    }

    public function test_traversal_and_non_upload_paths_do_not_resolve_svg(): void
    {
        foreach (['/uploads/../fixture.jpg', '/uploads/%2e%2e/fixture.jpg', '/orders/fixture.jpg', '/uploads/fixture.webp', 'data:image/svg+xml,test'] as $path) {
            $this->assertNull(OrderProductPreview::svgUrl(['activeImage' => $path, 'is_construct' => 1]));
        }
    }

    public function test_svg_document_preserves_external_image_composition_inside_an_empty_sandbox(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="400" height="200">'
            .'<defs><clipPath id="crop"><rect width="180" height="200"/></clipPath></defs>'
            .'<g clip-path="url(#crop)"><image xlink:href="https://example.test/source.jpg" width="400" height="200"/></g>'
            .'<text x="200" y="30">Caption</text></svg>';
        Storage::disk('uploads')->put('uploads/fixture.svg', $svg);
        $product = ['activeImage' => '/uploads/fixture.jpg', 'is_construct' => 1];
        $document = OrderProductPreview::svgDocument($product);
        foreach (['viewBox="0 0 400 200"', 'xlink:href="https://example.test/source.jpg"', 'clip-path="url(#crop)"', '>Caption</text>'] as $expected) {
            $this->assertStringContainsString($expected, $document);
        }
        $html = $this->render($product);
        $this->assertStringContainsString('sandbox=""', $html);
        $this->assertStringNotContainsString('allow-scripts', $html);
        $this->assertStringNotContainsString('allow-same-origin', $html);
        $this->assertStringNotContainsString('<svg', $html);
        $this->assertSame($svg, Storage::disk('uploads')->get('uploads/fixture.svg'));
        Http::assertNothingSent();
    }

    public function test_invalid_svg_documents_fall_back_to_the_original_raster(): void
    {
        foreach (['broken', '<html/>', '<!DOCTYPE svg [<!ENTITY x SYSTEM "file:///fixture-only">]><svg/>', '<svg><g></svg>'] as $contents) {
            Storage::disk('uploads')->put('uploads/fixture.svg', $contents);
            $product = ['activeImage' => 'https://example.test/uploads/fixture.jpg', 'is_construct' => 1];
            $this->assertNull(OrderProductPreview::svgDocument($product));
            $html = $this->render($product);
            $this->assertStringContainsString('src="https://example.test/uploads/fixture.jpg"', $html);
            $this->assertStringNotContainsString('<iframe', $html);
        }
    }

    public function test_oil_promo_uses_first_existing_catalog_value_without_creating_catalog_or_files(): void
    {
        DB::table('oil_header')->insert(['images' => json_encode(['oil-header/promo.jpg', 'oil-header/second.jpg'])]);
        $expected = Storage::disk(config('voyager.storage.disk', 'public'))->url('oil-header/promo.jpg');
        $this->assertSame($expected, OrderProductPreview::oilPromoUrl());
        $html = $this->render(['is_oil_portrait' => 1, 'activeImage' => 'https://example.test/portrait.jpg']);
        $this->assertStringContainsString('src="'.$expected.'" alt="Портрет маслом"', $html);
        $this->assertStringContainsString('src="https://example.test/portrait.jpg"', $html);
        $this->assertStringNotContainsString('second.jpg', $html);
        $this->assertSame([], Storage::disk('uploads')->allFiles());
        Http::assertNothingSent();
    }

    public function test_empty_malformed_and_unsafe_promo_values_are_omitted(): void
    {
        $this->assertNull(OrderProductPreview::oilPromoUrl());
        foreach (['', 'broken', '[]', '[null]', '[{}]', '[""]', '["javascript:alert(1)"]'] as $images) {
            DB::table('oil_header')->delete();
            DB::table('oil_header')->insert(['images' => $images]);
            $this->assertNull(OrderProductPreview::oilPromoUrl());
        }
    }

    public function test_legacy_order_description_preserves_formatting_in_the_actual_column(): void
    {
        $content = '<p><strong>Портрет</strong><br>Людей: 2 &amp; питомец</p><ul><li>Фон: сад</li></ul>'
            .'<a href="https://example.test/reference">Фотопример</a><script>alert(1)</script><img src="x" onerror="alert(2)">';
        $html = $this->render(['name' => 'Portrait'], extraItems: [1 => ['content' => $content]]);
        foreach (['<strong>Портрет</strong>', '<br', '<li>Фон: сад</li>', 'href="https://example.test/reference"', 'Людей: 2 &amp; питомец'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
        $this->assertStringNotContainsString('&lt;p&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('onerror=', $html);
    }

    public function test_present_legacy_coupon_flags_and_zero_bonus_match_voyager_presence_rules(): void
    {
        $html = $this->render(['name' => 'Canvas'], 'admin', delivery: ['is_universal' => 0, 'free_delivery' => false, 'bonus' => 0]);
        foreach (['Использован универсальный купон', 'Бесплатная доставка', 'Использовано 0 бонусов'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
        $html = $this->render(['name' => 'Canvas'], 'admin', delivery: ['is_universal' => null, 'bonus' => null]);
        $this->assertStringNotContainsString('Использован универсальный купон', $html);
        $this->assertStringNotContainsString('Использовано 0 бонусов', $html);
    }

    #[DataProvider('productBranches')]
    public function test_original_product_branches_keep_primary_secondary_and_gift_previews(array $flags, bool $secondary, bool $gift): void
    {
        $html = $this->render($flags + ['activeImage' => 'https://example.test/active.jpg', 'savedImage' => 'https://example.test/source.jpg', 'name' => 'Fixture product']);
        $this->assertStringContainsString('alt="Изображение товара"', $html);
        $this->assertSame($secondary, str_contains($html, 'alt="Исходное изображение"'));
        $this->assertSame($gift, str_contains($html, 'alt="Подарочная карта"'));
    }

    public static function productBranches(): array
    {
        return [
            'portrait' => [[], true, false],
            'constructor' => [['is_construct' => 1], false, false],
            'canvas' => [['is_canvas_inter' => 1], false, false],
            'modular' => [['is_modular_inter' => 1, 'wall_size' => '120x80', 'pic_size' => '100x60'], false, false],
            'collage' => [['is_canvas_collage' => 1], false, false],
            'gallery' => [['is_gall_with_img' => 1], false, false],
            'gallery-disabled' => [['is_gall_with_img' => 0], true, false],
            'gift' => [['is_gift_card' => 1, 'pid' => 5, 'price' => 79], true, true],
        ];
    }

    private function render(array $product, string $role = 'printing', array $extraItems = [], array $delivery = []): string
    {
        $user = new User;
        $user->setRelation('role', new Role(['name' => $role]));
        $this->actingAs($user, 'filament');
        $order = (new Orders)->forceFill(['id' => 42, 'items' => json_encode([$product + ['sumPrice' => 1]] + $extraItems), 'delivery' => json_encode($delivery)]);
        $order->setRelation('user', null);

        return view('filament.tables.columns.order-product', ['getRecord' => fn () => $order])->render();
    }
}
