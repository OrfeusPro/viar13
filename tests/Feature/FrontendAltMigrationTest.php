<?php

namespace Tests\Feature;

use App\Models\FrontendImage;
use App\Services\AltGeneration\FrontendImageRegistry;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FrontendAltMigrationTest extends TestCase
{
    public function test_frontend_catalog_is_lazy_and_migration_creates_image_table(): void
    {
        $this->assertFileDoesNotExist(config_path('frontend_alt_catalog.php'));
        $catalog = require resource_path('frontend_alt_catalog.php');
        $this->assertGreaterThan(300, count($catalog));

        require_once database_path('migrations/2026_09_09_120000_create_frontend_images_table.php');
        (new \CreateFrontendImagesTable())->up();
        $this->assertTrue(Schema::hasTable('frontend_images'));

        $id = FrontendImage::identity('one', '/images/logo.svg');
        $this->assertSame($id, FrontendImage::identity('two', '/images/logo.svg'));
    }

    public function test_canvas_subtitle_migration_is_additive(): void
    {
        Schema::create('canvas_slider', function (Blueprint $table): void {
            $table->id();
            $table->text('sub_title')->nullable();
        });

        $migration = require database_path('migrations/2026_09_10_164500_add_hero_subtitle_to_canvas_slider.php');
        $migration->up();
        $migration->up();

        $this->assertTrue(Schema::hasColumn('canvas_slider', 'hero_subtitle'));
    }

    public function test_frontend_alt_keeps_existing_attributes_before_database_migration(): void
    {
        $attrs = app(FrontendImageRegistry::class)->attributesFor(
            'theme/viar/pages/index/footer.blade.php', '/images/logo.svg', 'viarcanvas', ''
        );
        $this->assertStringContainsString('alt="viarcanvas"', $attrs);
    }
}
