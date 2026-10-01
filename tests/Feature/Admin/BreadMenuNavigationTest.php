<?php

namespace Tests\Feature\Admin;

use App\Filament\Bread\BreadMenuTree;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BreadMenuNavigationTest extends TestCase
{
    public function test_menu_preserves_order_hierarchy_active_path_and_authorized_links(): void
    {
        Schema::create('menus', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('menu_items', function (Blueprint $table): void {
            $table->id();
            $table->integer('menu_id');
            $table->integer('parent_id')->nullable();
            $table->string('title');
            $table->integer('order');
            $table->boolean('status')->default(true);
            $table->string('icon_class')->nullable();
            $table->string('url')->nullable();
            $table->string('route')->nullable();
        });
        DB::table('menus')->insert(['id' => 1, 'name' => 'admin']);
        foreach ([
            [1, null, 'Dashboard', 1, 'voyager.dashboard', null],
            [2, null, 'Общие страницы', 3, null, null],
            [3, 2, 'Блог', 1, null, null],
            [4, 3, 'Статьи', 1, null, null],
            [5, 2, 'FAQ', 2, null, null],
            [6, null, 'Заказы', 2, 'voyager.orders.index', null],
            [7, 2, 'Без прав', 3, null, null],
            [8, null, 'Скрытая категория', 4, null, null],
            [9, 8, 'Скрытый потомок', 1, null, null],
        ] as [$id, $parent, $title, $order, $route, $url]) {
            DB::table('menu_items')->insert(['id' => $id, 'menu_id' => 1, 'parent_id' => $parent,
                'title' => $title, 'order' => $order, 'route' => $route, 'url' => $url, 'status' => $id !== 8,
                'icon_class' => $id === 6 ? 'voyager-basket' : null]);
        }
        $item = fn (string $key, string $url) => NavigationItem::make($key)->key($key)->url($url)->icon('heroicon-o-document-text');
        $navigation = [NavigationGroup::make()->items([
            $item('dashboard', '/filament'), $item('orders', '/filament/orders'),
            $item('bread-4', '/filament/bread/blog-posts'), $item('bread-5', '/filament/bread/page-faq-desc'),
            $item('bread-7', '/filament/bread/private')->visible(false), $item('bread-9', '/filament/bread/hidden'),
            $item('tool', '/filament/voyager-catalog'),
        ])];
        $this->app->instance('originalRequest', Request::create('/filament/bread/page-faq-desc/1/edit'));
        $tree = BreadMenuTree::make($navigation);
        $this->assertSame(['Dashboard', 'Заказы', 'Общие страницы', 'Дополнительно'], array_column($tree, 'label'));
        $this->assertSame(['Блог', 'FAQ'], array_column($tree[2]['children'], 'label'));
        $this->assertSame('Статьи', $tree[2]['children'][0]['children'][0]['label']);
        $this->assertSame(['Общие страницы', 'FAQ'], array_column(BreadMenuTree::activePath($tree), 'label'));
        $this->assertFalse($tree[0]['active']);
        $this->assertTrue($tree[2]['children'][1]['active']);
        $this->assertCount(1, $tree[3]['children']);
        $this->assertSame('heroicon-o-shopping-bag', $tree[1]['icon']);

        $rendered = view('filament.components.menu-tree', compact('navigation'))->render();
        $this->assertStringContainsString('aria-controls="menu-2-children"', $rendered);
        $this->assertStringContainsString('aria-current="page"', $rendered);
        $this->assertStringNotContainsString('/filament/bread/private', $rendered);
        $this->assertStringNotContainsString('/filament/bread/hidden', $rendered);
    }

    public function test_active_matching_distinguishes_filtered_products_and_menu_builders(): void
    {
        $this->app->instance('originalRequest', Request::create('/filament/bread/gallery-items/974/edit?type=2'));
        $this->assertTrue(BreadMenuTree::matches('/filament/bread/gallery-items?type=2'));
        $this->assertFalse(BreadMenuTree::matches('/filament/bread/gallery-items?type=5'));
        $this->assertFalse(BreadMenuTree::matches('/filament/bread/gallery-items'));
        $this->assertFalse(BreadMenuTree::matches('/filament'));
        $this->assertFalse(BreadMenuTree::matches('/filament/bread/gallery-item'));

        $this->app->instance('originalRequest', Request::create('/filament/menu-items?menu=3'));
        $this->assertTrue(BreadMenuTree::matches('/filament/menu-items?menu=3'));
        $this->assertFalse(BreadMenuTree::matches('/filament/menu-items?menu=2'));
        $this->assertFalse(BreadMenuTree::matches('/filament/menu-items'));
    }
}
