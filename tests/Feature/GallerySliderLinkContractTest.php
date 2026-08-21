<?php

namespace Tests\Feature;

use Tests\TestCase;

class GallerySliderLinkContractTest extends TestCase
{
    public function test_gallery_slider_converts_known_catalog_links_to_localized_routes(): void
    {
        $template = file_get_contents(
            resource_path('views/theme/viar/pages/gallery/gallery.blade.php')
        );

        $this->assertStringContainsString(
            "in_array(\$slideType, ['photo', 'module', 'reproduction'], true)",
            $template
        );
        $this->assertStringContainsString(
            "route('hb.gallery.module', ['type' => \$slideType])",
            $template
        );
        $this->assertStringContainsString('href="{{ $slideLink }}"', $template);
        $this->assertStringNotContainsString('href="{{ $item[\'link\'] }}"', $template);
    }
}
