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
            $this->assertIsArray(trans('canvas_catalogue'));
            $this->assertCount(7, trans('canvas_catalogue.quality'));
            $this->assertStringNotContainsString('canvas_ai.', trans('canvas_ai.paid_notice'));
            $this->assertIsString(trans('calculator.upload_error'));
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
    public function test_frame_catalogue_requires_schema_and_displays_only_enabled_frames_in_current_language(): void
    {
        Schema::create('canvas_rams', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('img')->nullable();
            $table->string('img_bg')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('width', 7, 2)->nullable();
            $table->decimal('height', 7, 2)->nullable();
        });
        config(['canvas_ui.new_calculator' => true]);
        app()->setLocale('en');
        $legacy = view('theme.viar.pages.canvas.form.tab2')->render();
        $this->assertStringNotContainsString('calculator-frame-catalogue', $legacy);
        (require database_path('migrations/2026_11_05_100000_add_calculator_fields_to_canvas_rams.php'))->up();
        \Illuminate\Support\Facades\DB::table('canvas_rams')->insert([
            ['id' => 100, 'img' => 'frames/sample.jpg', 'img_bg' => 'frames/detail.jpg', 'source_article' => 'sample.A',
             'calculator_enabled' => true, 'price_per_meter' => 15, 'profile_width_mm' => 33, 'profile_depth_mm' => 22,
             'source_data' => json_encode(['description' => 'Оттенок Белый, выс. 2', 'category' => 'beliy_plastik'])],
            ['id' => 101, 'img' => null, 'img_bg' => null, 'source_article' => 'hidden.B',
             'calculator_enabled' => false, 'price_per_meter' => 20, 'profile_width_mm' => 30, 'profile_depth_mm' => 20,
             'source_data' => null],
        ]);
        $html = view('theme.viar.pages.canvas.form.tab2')->render();
        $this->assertStringContainsString('data-article="sample.A"', $html);
        $this->assertStringNotContainsString('data-article="hidden.B"', $html);
        $this->assertStringContainsString('White classic', $html);
        $this->assertStringContainsString('data-frame-width-mm="33', $html);
        $this->assertStringContainsString('15', $html);
    }

}
