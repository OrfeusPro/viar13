<?php

namespace Tests\Feature\Admin;

use App\Models\OrderPainterImages;
use App\Models\Orders;
use App\Support\Admin\OrderMediaUrl;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrderMediaUrlTest extends TestCase
{
    #[DataProvider('paths')]
    public function test_resolves_production_paths_without_downloading_files(?string $path, ?string $expected): void
    {
        config(['admin_migration.order_media_base_url' => 'https://viarcanvas.com/']);
        Http::preventStrayRequests();
        $this->assertSame($expected, OrderMediaUrl::resolve($path));
        Http::assertNothingSent();
    }

    public static function paths(): array
    {
        return [
            ['orders/art.jpg', 'https://viarcanvas.com/orders/art.jpg'],
            ['/storage/orders/art.jpg', 'https://viarcanvas.com/storage/orders/art.jpg'],
            ['orders/with space.jpg', 'https://viarcanvas.com/orders/with%20space.jpg'],
            ['https://viarcanvas.com/orders/art.jpg?ver=1', 'https://viarcanvas.com/orders/art.jpg?ver=1'],
            [null, null], ['', null], ['/', null],
            ['javascript:alert(1)', null], ['data:image/svg+xml,unsafe', null],
            ['//other.example/image.jpg', null], ['../.env', null],
            ['orders/%2e%2e/.env', null], ["orders/line\nbreak.jpg", null],
            ['orders\\image.jpg', null],
        ];
    }

    public function test_configured_media_host_is_used_and_invalid_host_is_rejected(): void
    {
        config(['admin_migration.order_media_base_url' => 'https://media.example.test']);
        $this->assertSame('https://media.example.test/orders/art.jpg', OrderMediaUrl::resolve('orders/art.jpg'));
        config(['admin_migration.order_media_base_url' => 'javascript:unsafe']);
        $this->assertNull(OrderMediaUrl::resolve('orders/art.jpg'));
    }

    public function test_chat_uses_remote_small_image_and_original_link_without_local_file_check(): void
    {
        $html = $this->renderArtwork('orders/art.jpg', 'orders/small-art.jpg');
        $this->assertStringContainsString('href="https://viarcanvas.com/orders/art.jpg"', $html);
        $this->assertStringContainsString('src="https://viarcanvas.com/orders/small-art.jpg"', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringNotContainsString('Файл отсутствует', $html);
        Http::assertNothingSent();
    }

    public function test_chat_uses_original_when_thumbnail_is_absent_and_local_icons_for_documents(): void
    {
        $this->assertStringContainsString('src="https://viarcanvas.com/orders/art.jpg"', $this->renderArtwork('orders/art.jpg'));
        foreach (['pdf', 'psd'] as $extension) {
            $html = $this->renderArtwork('orders/art.'.$extension);
            $this->assertStringContainsString('href="https://viarcanvas.com/orders/art.'.$extension.'"', $html);
            $this->assertStringContainsString('src="'.asset('img/'.$extension.'.svg').'"', $html);
        }
        Http::assertNothingSent();
    }

    private function renderArtwork(string $path, ?string $thumbnail = null): string
    {
        config(['admin_migration.order_media_base_url' => 'https://viarcanvas.com']);
        Http::preventStrayRequests();
        $artwork = (new OrderPainterImages)->forceFill([
            'id' => 7357, 'image' => $path, 'small_image' => $thumbnail, 'is_img_painter' => 1,
        ]);
        $artwork->setRelation('statusDefinition', null);
        $order = (new Orders)->forceFill(['id' => 18380, 'status' => 'watching']);
        $order->setRelation('order_painter_images', new Collection([$artwork]));
        $order->setRelation('order_user_comments', new Collection);

        return view('filament.tables.modals.order-client-chat', ['record' => $order])->render();
    }
}
