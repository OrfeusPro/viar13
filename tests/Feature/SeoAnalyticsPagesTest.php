<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoAnalyticsPagesTest extends TestCase
{
    public function test_public_oauth_information_pages_render_without_storefront_redirects(): void
    {
        foreach (['/seo-analytics', '/seo-analytics/privacy', '/seo-analytics/terms'] as $path) {
            $response = $this->get($path);
            $response->assertOk();
            $response->assertSee('ViarCanvas SEO Analytics');
            $response->assertSee('<html lang="en">', false);
        }
    }
}
