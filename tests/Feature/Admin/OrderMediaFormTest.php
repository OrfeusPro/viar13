<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Schemas\OrderMediaForm;
use App\Models\APainterImagesStatus;
use App\Models\OrderPainterImages;
use App\Models\Orders;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderMediaFormTest extends TestCase
{
    public function test_gallery_preserves_original_groups_database_precedence_and_full_image_links(): void
    {
        Http::preventStrayRequests();
        config(['admin_migration.order_media_base_url' => 'https://viarcanvas.com']);
        $order = (new Orders)->forceFill([
            'id' => 42,
            'items' => json_encode([['orig_images' => ['uploads/client.jpg', 'javascript:bad']]]),
            'painter_sketch_images' => 'uploads/legacy-sketch.jpg',
            'painter_images' => ',uploads/legacy-painting.jpg,',
            'client_images' => ',uploads/extra.jpg,',
        ]);
        $image = (new OrderPainterImages)->forceFill([
            'id' => 1, 'order_id' => 42, 'is_img_sketch' => 1, 'image' => 'art/full.jpg',
            'small_image' => 'art/small.jpg', 'updated_at' => '2026-10-17 12:34:00',
        ]);
        $image->setRelation('statusDefinition', (new APainterImagesStatus)->forceFill(['title' => 'На проверке']));
        $order->setRelation('order_painter_images', new Collection([$image]));
        $before = $order->getAttributes();
        $groups = OrderMediaForm::groups($order);
        $this->assertSame(['Фото клиента', 'Наброски художника', 'Картины художника', 'Дополнительные изображения клиента'], array_keys($groups));
        $this->assertCount(1, $groups['Фото клиента']);
        $html = view('filament.orders.media-gallery', compact('groups'))->render();
        foreach (['uploads/client.jpg', 'art/full.jpg', 'art/small.jpg', 'uploads/legacy-painting.jpg', 'uploads/extra.jpg', 'На проверке', '17.10.2026 12:34', 'object-fit: contain'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringNotContainsString('legacy-sketch.jpg', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertSame($before, $order->getAttributes());
        Http::assertNothingSent();
    }

    public function test_empty_gallery_does_not_require_catalog_queries_or_write_defaults(): void
    {
        $order = new Orders;
        $order->setRelation('order_painter_images', new Collection);
        $groups = OrderMediaForm::groups($order);
        $this->assertSame([[], [], [], []], array_values($groups));
        $this->assertSame(4, substr_count(view('filament.orders.media-gallery', compact('groups'))->render(), 'Нет изображений'));
        $this->assertSame([], $order->getAttributes());
    }
}
