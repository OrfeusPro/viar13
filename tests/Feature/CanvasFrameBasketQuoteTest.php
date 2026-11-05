<?php

namespace Tests\Feature;

use App\Repositories\BasketRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CanvasFrameBasketQuoteTest extends TestCase
{
    public function test_meter_frame_uses_catalogue_quote_and_rejects_tampering_before_upload(): void
    {
        Storage::fake('uploads');
        $fixtures = [
            'canvas_header' => ['sizes_30x40' => '40x50[29-23]s'],
            'gallery_holsts' => ['price' => 1],
            'gallery_decorations' => ['price' => 0, 'coef_sm' => 0],
            'gallery_boxes' => ['price' => 2],
            'canvas_rams' => ['price' => 0, 'price_per_meter' => 15, 'calculator_enabled' => 1],
            'a_production_time' => ['category' => 'canvas', 'standart_price' => 0, 'express_price' => 5],
        ];
        foreach ($fixtures as $table => $row) {
            Schema::create($table, function (Blueprint $schema) use ($row): void {
                $schema->id();
                foreach ($row as $column => $value) {
                    is_string($value) ? $schema->string($column) : $schema->decimal($column, 10, 2);
                }
            });
            DB::table($table)->insert($row);
        }
        $request = fn (float $price) => [
            'basketType' => 1,
            'userImage' => UploadedFile::fake()->image('canvas.jpg'),
            'formId' => 1,
            'sizeId' => '40x50',
            'executionId' => 2,
            'canvasId' => 1,
            'decorationId' => 1,
            'ram_id' => 1,
            'boxIds' => '[1]',
            'terms_price' => 5,
            'price' => $price,
        ];

        $this->postJson('/basket/add', $request(1))->assertUnprocessable();
        $this->postJson('/basket/add', $request(50))->assertUnprocessable()
            ->assertJsonPath('success', false);
        $this->assertSame([], Storage::disk('uploads')->allFiles());

        $this->postJson('/basket/add', $request(57))->assertOk()
            ->assertJsonPath('success', true);
        $item = session('basket')[0];
        $this->assertSame(1.8, $item['canvas_frame_quote']['meters']);
        $this->assertSame(27.0, $item['canvas_frame_quote']['price']);
        $this->assertSame(52.0, $item['price']);
        $this->assertSame('s', $item['price_label']);
    }
}
