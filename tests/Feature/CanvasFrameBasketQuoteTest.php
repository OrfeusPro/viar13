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
    public function test_calculator_uses_catalogue_quotes_for_all_frame_choices(): void
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
                    is_string($value) ? $schema->string($column) : $schema->decimal($column, 10, 2)->nullable();
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

        DB::table('canvas_rams')->insert(['id' => 2, 'price' => 25, 'price_per_meter' => null, 'calculator_enabled' => 0]);
        foreach ([[null, 30, 0.0], [2, 55, 25.0]] as [$frameId, $price, $framePrice]) {
            $options = ['canvas_calculator' => 'v2', 'ram_id' => $frameId];
            $this->postJson('/basket/add', array_replace($request($price - 1), $options))->assertUnprocessable();
            $this->postJson('/basket/add', array_replace($request($price), $options))->assertOk();
            $items = session('basket');
            $this->assertSame($framePrice, end($items)['canvas_frame_quote']['price']);
            $this->assertNull(end($items)['canvas_frame_quote']['rate']);
        }
        $this->postJson('/basket/add', array_replace($request(55), [
            'canvas_calculator' => 'v2',
            'ram_id' => 999,
        ]))->assertUnprocessable();
    }
}
