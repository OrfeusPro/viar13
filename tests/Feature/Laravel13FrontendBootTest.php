<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use TCG\Voyager\Models\MenuItem;
use TCG\Voyager\Traits\Translatable;

class Laravel13FrontendBootTest extends TestCase
{
    public function test_voyager_frontend_menu_adapter_resolves_legacy_links(): void
    {
        $item = new MenuItem([
            'url' => '/new/canvas',
            'route' => null,
            'parameters' => null,
        ]);

        $this->assertSame('/new/canvas', $item->link());
        $this->assertSame(url('/new/canvas'), $item->link(true));

        $missingRoute = new MenuItem([
            'url' => '/fallback',
            'route' => 'missing.frontend.route',
            'parameters' => '[]',
        ]);

        $this->assertSame('#', $missingRoute->link());
    }

    public function test_frontend_menu_renders_nested_items_without_admin_views(): void
    {
        $child = new MenuItem([
            'title' => 'Child',
            'url' => '/child',
            'target' => '_self',
        ]);
        $child->setRelation('children', collect());

        $parent = new MenuItem([
            'title' => 'Parent',
            'url' => '/parent',
            'target' => '_self',
        ]);
        $parent->setRelation('children', collect([$child]));

        $html = view('layots.menu.default', [
            'items' => collect([$parent]),
            'options' => (object) ['locale' => 'ru'],
        ])->render();

        $this->assertStringContainsString('href="'.url('/parent').'"', $html);
        $this->assertStringContainsString('href="'.url('/child').'"', $html);
    }

    public function test_voyager_translatable_adapter_ignores_invalid_attribute_names(): void
    {
        $model = new class extends Model
        {
            use Translatable;

            protected $translatable = ['title', '', null];
        };

        $this->assertSame(['title'], $model->getTranslatableAttributes());
    }

    public function test_laravel_13_frontend_runtime_boots_without_admin_routes(): void
    {
        $this->assertStringStartsWith('13.', app()->version());
        $this->assertTrue(Route::has('home'));
        $this->assertTrue(Route::has('login'));
        $this->assertFalse((bool) config('app.admin_enabled'));
        $this->assertFalse(Route::has('voyager.dashboard'));
        $this->assertFalse(Route::has('verification.notice'));
    }

    public function test_frontend_route_names_are_unique_and_keep_legacy_urls(): void
    {
        $duplicates = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => $route->getName() !== null)
            ->groupBy(fn ($route) => $route->getName())
            ->filter(fn ($routes) => $routes->count() > 1)
            ->keys()
            ->all();

        $this->assertSame([], $duplicates);
        $this->assertSame('user/send_photo_form', Route::getRoutes()->getByName('send_photo_form')->uri());
        $this->assertSame('get/ram_search', Route::getRoutes()->getByName('hb.gallery.ram_search')->uri());
        $this->assertSame('salidzini.xml', Route::getRoutes()->getByName('kurpirkt')->uri());
        $this->assertSame('basket/submitbonuses', Route::getRoutes()->getByName('submit_bonuses')->uri());
    }

    public function test_voyager_frontend_media_adapter_preserves_public_storage_url(): void
    {
        $this->assertStringEndsWith('/storage/example/image.jpg', \Voyager::image('example/image.jpg'));
        $this->assertSame('https://cdn.example.test/image.jpg', \Voyager::image('https://cdn.example.test/image.jpg'));
    }

    public function test_image_scheme_normalizer_preserves_local_http_urls(): void
    {
        config(['app.url' => 'http://localhost']);

        $this->assertSame(
            'http://localhost/images/example.jpg',
            image_normalize_url_scheme('http://localhost/images/example.jpg')
        );
        $this->assertSame(
            '//127.0.0.1/images/example.jpg',
            image_normalize_url_scheme('//127.0.0.1/images/example.jpg')
        );
        $this->assertSame(
            'https://viarcanvas.com/images/example.jpg',
            image_normalize_url_scheme('http://viarcanvas.com/images/example.jpg')
        );
    }
}
