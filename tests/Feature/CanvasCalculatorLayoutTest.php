<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CanvasCalculatorLayoutTest extends TestCase
{
    public function test_layout_renders_all_languages_with_existing_frame_schema(): void
    {
        Schema::create('canvas_rams', fn (Blueprint $table) => $table->increments('id'));
        foreach (['ru', 'lv', 'en', 'lt', 'ee', 'et', 'de', 'pl'] as $locale) {
            app()->setLocale($locale);
            $html = view('theme.viar.pages.canvas.calculator-layout')->render();
            $this->assertStringContainsString('calculator-navigation', $html);
            $this->assertStringNotContainsString('calculator.title', $html);
            $this->assertStringNotContainsString('calculator.ui', $html);
            $this->assertStringContainsString('id="calculator-frame-rates" type="application/json">[]', $html);
            $this->assertIsArray(trans('calculator.preparation'));
            $this->assertIsArray(trans('calculator.ui'));
        }
    }

    public function test_canvas_basket_preserves_edge_configuration_photo_comments_and_original(): void
    {
        \Illuminate\Support\Facades\Storage::fake('uploads');
        $configuration = json_encode(['version' => 2, 'edge_mode' => 'mirror', 'front' => ['zoom' => 1.2]]);
        $request = \Illuminate\Http\Request::create('/basket/add', 'POST', [
            'basketType' => 1, 'price' => '48.00', 'formId' => 1, 'sizeId' => '40x50',
            'ram_id' => 16, 'canvas_configuration' => $configuration,
            'userComment' => "Order comment\nPhoto comment", 'photoComment' => 'Photo comment',
            'improve_photo' => 'Basic', 'boxIds' => '[1]',
        ], [], ['userImage' => \Illuminate\Http\UploadedFile::fake()->image('canvas.jpg')]);
        $this->app->instance('request', $request);
        $repository = app(\App\Repositories\BasketRepository::class);
        $method = new \ReflectionMethod($repository, 'formCanvasTypeData');
        $data = $method->invoke($repository);
        $this->assertSame($configuration, $data['canvas_configuration']);
        $this->assertSame("Order comment\nPhoto comment", $data['userComment']);
        $this->assertSame('Photo comment', $data['photoComment']);
        $this->assertSame(16, $data['ram_id']);
        $this->assertSame('48.00', $data['price']);
        $this->assertSame([1], $data['boxIds']);
        $this->assertSame($data['savedImage'], $data['activeImage']);
        $this->assertCount(1, \Illuminate\Support\Facades\Storage::disk('uploads')->allFiles());
    }

    public function test_edge_controls_have_real_modes_without_unavailable_test_ai(): void
    {
        app()->setLocale('lv');
        $html = view('theme.viar.pages.canvas.wrap-controls')->render();
        foreach (['wrap', 'mirror', 'solid'] as $mode) {
            $this->assertStringContainsString('value="'.$mode.'"', $html);
        }
        $this->assertStringNotContainsString('value="ai"', $html);
        $this->assertStringNotContainsString('canvas_ai.', $html);
        $this->assertStringNotContainsString('canvas-outpaint.js', $html);
    }
}
