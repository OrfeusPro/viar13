<?php

namespace Tests\Unit;

use App\Support\StorefrontLocale;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class StorefrontLocaleUrlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.locale', 'ru');
        config()->set('app.url', 'https://viarcanvas.com');
        config()->set('laravellocalization.hideDefaultLocaleInURL', true);
        URL::forceRootUrl('https://viarcanvas.com');
    }

    public function test_it_adds_the_current_non_default_locale_prefix(): void
    {
        app()->setLocale('lv');

        $this->assertSame(
            'https://viarcanvas.com/lv/new/canvas',
            StorefrontLocale::url('https://viarcanvas.com/new/canvas')
        );
        $this->assertSame(
            'https://viarcanvas.com/lv/page/contacts?from=menu#top',
            StorefrontLocale::url('/en/page/contacts?from=menu#top')
        );
    }

    public function test_it_supports_every_prefixed_storefront_locale(): void
    {
        foreach (['lv', 'lt', 'pl', 'de', 'en', 'ee'] as $locale) {
            app()->setLocale($locale);

            $this->assertSame(
                'https://viarcanvas.com/' . $locale . '/new/canvas',
                StorefrontLocale::url('/new/canvas')
            );
        }
    }

    public function test_it_keeps_the_default_russian_storefront_without_a_prefix(): void
    {
        app()->setLocale('ru');

        $this->assertSame(
            'https://viarcanvas.com/new/canvas',
            StorefrontLocale::url('/lv/new/canvas')
        );
    }

    public function test_it_does_not_rewrite_external_or_non_http_links(): void
    {
        app()->setLocale('lv');

        $this->assertSame('https://example.com/new/canvas', StorefrontLocale::url('https://example.com/new/canvas'));
        $this->assertSame('mailto:orders@viarcanvas.com', StorefrontLocale::url('mailto:orders@viarcanvas.com'));
        $this->assertSame('#faq', StorefrontLocale::url('#faq'));
    }

    public function test_it_localizes_internal_links_inside_trusted_translated_html(): void
    {
        app()->setLocale('lv');

        $html = '<a href="/condition">Policy</a> <a href="https://example.com/help">Help</a>';

        $this->assertSame(
            '<a href="https://viarcanvas.com/lv/condition">Policy</a> <a href="https://example.com/help">Help</a>',
            storefront_html($html)
        );
    }
}
