<?php

namespace Tests\Feature;

use App\Models\Orders;
use stdClass;
use Tests\TestCase;

class OrderFilenameSanitizationTest extends TestCase
{
    /** @test */
    public function it_removes_slashes_from_generated_order_filenames()
    {
        $order = new stdClass();
        $order->id = 17120;
        $order->delivery = json_encode([
            'when_send' => '04/15/2026',
            'country' => 'EE',
        ]);
        $order->items = json_encode([
            [
                'show' => ['size' => '70x100'],
                'manual_orientation_code' => 'V1',
                'manual_canvas_id' => 2,
                'manual_gift_code' => 'G0',
                'manual_decoration_id' => 5,
            ],
        ]);

        $generated = Orders::getOrderImageName($order, 'picture', false, false, false, false, false, true);

        $this->assertStringNotContainsString('/', $generated);
        $this->assertStringNotContainsString('\\', $generated);
        $this->assertStringContainsString('picture', $generated);
    }

    /** @test */
    public function it_marks_a_manually_created_framed_paper_item_with_b2()
    {
        $order = new stdClass();
        $order->id = 17121;
        $order->delivery = json_encode(['country' => 'LV']);
        $order->items = json_encode([
            [
                'show' => ['size' => '50x70'],
                'manual_baget_code' => 'B2',
            ],
        ]);

        $generated = Orders::getOrderImageName($order, 'picture', false, false, false, false, false, false);

        $this->assertStringContainsString('_B2_', $generated);
    }

    /** @test */
    public function it_marks_collage_gift_paper_with_g1_from_its_packaging_id()
    {
        $order = new stdClass();
        $order->id = 17122;
        $order->delivery = json_encode(['country' => 'LV']);
        $item = [
            'show' => ['size' => '30x40'],
            'boxIds' => 2,
        ];

        $generated = Orders::generateImageName($order, $item, 1);

        $this->assertStringContainsString('_G1_', $generated);
    }
}
