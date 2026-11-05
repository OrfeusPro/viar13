<?php

namespace Tests\Feature\Admin;

use App\Models\APainterImagesStatus;
use App\Models\OrderPainterImages;
use App\Models\Orders;
use App\Models\PainterOrder;
use App\Models\User;
use App\Support\Admin\OrderMediaUrl;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
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

    public function test_artist_column_renders_remote_original_and_thumbnail_without_local_copies(): void
    {
        Schema::create((new APainterImagesStatus)->getTable(), function (Blueprint $table): void {
            $table->id();
            $table->string('title');
        });
        $image = (new OrderPainterImages)->forceFill([
            'id' => 7353, 'image' => 'orders/remote-art.jpg', 'small_image' => 'orders/remote-small.jpg',
            'is_img_sketch' => 1, 'updated_at' => '2026-10-30 12:00:00',
        ]);
        $image->setRelation('statusDefinition', (new APainterImagesStatus)->forceFill(['title' => 'Work accepted']));
        $html = $this->renderArtistColumn(new Collection([$image]));
        $this->assertStringContainsString('href="https://viarcanvas.com/orders/remote-art.jpg"', $html);
        $this->assertStringContainsString('src="https://viarcanvas.com/orders/remote-small.jpg"', $html);
        $this->assertStringContainsString('Work accepted', $html);
        $this->assertStringContainsString('2026-10-30 12:00:00', $html);
        $this->assertStringNotContainsString('/no_image.png', $html);
        Http::assertNothingSent();
    }

    public function test_artist_column_preserves_legacy_and_client_remote_links_and_rejects_unsafe_urls(): void
    {
        $html = $this->renderArtistColumn(new Collection, [
            'painter_sketch_images' => 'orders/legacy-sketch.jpg',
            'painter_images' => 'https://media.example.test/legacy-painting.jpg',
            'client_images' => 'uploads/client-image.jpg,javascript:unsafe',
        ]);
        foreach (['https://viarcanvas.com/orders/legacy-sketch.jpg', 'https://media.example.test/legacy-painting.jpg', 'https://viarcanvas.com/uploads/client-image.jpg'] as $url) {
            $this->assertStringContainsString('href="'.$url.'"', $html);
            $this->assertStringContainsString('src="'.$url.'"', $html);
        }
        $this->assertStringNotContainsString('javascript:unsafe', $html);
        Http::assertNothingSent();
    }

    private function renderArtistColumn(Collection $images, array $attributes = []): string
    {
        config(['admin_migration.order_media_base_url' => 'https://viarcanvas.com']);
        Http::preventStrayRequests();
        $assignment = new PainterOrder;
        $assignment->setRelation('user', new User(['first_name' => 'Fixture artist']));
        $order = (new Orders)->forceFill(['id' => 42, ...$attributes]);
        $order->setRelation('painterAssignment', $assignment);
        $order->setRelation('printingAssignment', null);
        $order->setRelation('order_painter_images', $images);

        return view('filament.tables.columns.order-artist', ['getRecord' => fn () => $order])->render();
    }
}
