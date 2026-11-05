<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use ReflectionMethod;
use Tests\TestCase;

class SitemapHtmlContractTest extends TestCase
{
    public function test_sitemap_subcategories_remain_models_for_the_blade_template(): void
    {
        $method = new ReflectionMethod(PageController::class, 'generate_sitemap_html');
        $lines = file($method->getFileName());
        $source = implode('', array_slice(
            $lines,
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));

        $this->assertStringContainsString(
            "->translate(App::getLocale(), 'ru');",
            $source
        );
        $this->assertStringNotContainsString(
            "->translate(App::getLocale(), 'ru')\n                ->toArray()",
            str_replace("\r\n", "\n", $source)
        );
    }
}
