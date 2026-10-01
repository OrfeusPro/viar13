<?php

namespace App\Filament\Bread;

use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use function Filament\Support\original_request;

class BreadMenuTree
{
    public static function make(array $navigation): array
    {
        $available = [];
        foreach ($navigation as $group) {
            foreach ($group->getItems() as $item) {
                if ($item->isVisible()) {
                    $available[$item->getKey()] = $item;
                }
            }
        }
        if (! Schema::hasTable('menus') || ! Schema::hasTable('menu_items')) {
            return array_map(fn ($item) => static::leaf($item), array_values($available));
        }
        $menuId = DB::table('menus')->where('name', 'admin')->value('id');
        $rows = DB::table('menu_items')->where('menu_id', $menuId)->where('status', 1)
            ->orderBy('order')->orderBy('id')->get()->groupBy(fn ($row) => (int) $row->parent_id);
        $used = [];
        $build = function (int $parent, array $ancestors = []) use (&$build, $rows, $available, &$used): array {
            $nodes = [];
            foreach ($rows->get($parent, collect()) as $row) {
                if (in_array($row->id, $ancestors, true)) {
                    continue;
                }
                $children = $build((int) $row->id, [...$ancestors, $row->id]);
                $item = $available['bread-'.$row->id] ?? $available['ui-translations-'.$row->id] ?? null;
                if (! $item) {
                    $path = static::dedicatedPath($row);
                    foreach ($available as $candidate) {
                        if ($path && parse_url($candidate->getUrl() ?? '', PHP_URL_PATH) === $path) {
                            $item = $candidate;
                            break;
                        }
                    }
                }
                if (! $item && ! $children) {
                    continue;
                }
                if ($item) {
                    $used[$item->getKey()] = true;
                }
                $nodes[] = [
                    'id' => 'menu-'.$row->id,
                    'label' => trim((string) $row->title),
                    'icon' => filled($row->icon_class ?? null) ? BreadNavigationIcon::resolve($row->icon_class) : ($item?->getIcon() ?? BreadNavigationIcon::resolve(null)),
                    'url' => $item?->getUrl(),
                    'active' => ($item && static::matches($item->getUrl())) || collect($children)->contains('active', true),
                    'children' => $children,
                ];
            }

            return $nodes;
        };
        $tree = $build(0);
        // Keep migrated tools that have no legacy menu entry reachable at the end.
        $extras = [];
        $usedUrls = array_map(fn ($key) => $available[$key]->getUrl(), array_keys($used));
        foreach ($available as $key => $item) {
            if (! isset($used[$key]) && ! in_array($item->getUrl(), $usedUrls, true)
                && ! str_starts_with((string) $key, 'bread-') && ! str_starts_with((string) $key, 'ui-translations-')) {
                $extras[] = static::leaf($item);
            }
        }
        if ($extras) {
            $tree[] = ['id' => 'menu-extra', 'label' => 'Дополнительно', 'icon' => 'heroicon-o-squares-2x2',
                'url' => null, 'active' => collect($extras)->contains('active', true), 'children' => $extras];
        }

        return $tree;
    }

    public static function matches(?string $url): bool
    {
        if (! $url) {
            return false;
        }
        $path = rtrim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        $request = original_request();
        $current = '/'.trim($request->path(), '/');
        if ($current !== $path && ($path === '/filament' || ! str_starts_with($current, $path.'/'))) {
            return false;
        }
        parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);
        foreach (['type', 'menu'] as $key) {
            if ((string) ($query[$key] ?? '') !== (string) $request->query($key, '')) {
                return false;
            }
        }

        return true;
    }

    public static function activePath(array $tree): array
    {
        foreach ($tree as $node) {
            if (! $node['active']) {
                continue;
            }

            return [$node, ...static::activePath($node['children'])];
        }

        return [];
    }

    private static function leaf(NavigationItem $item): array
    {
        return ['id' => 'extra-'.md5((string) $item->getKey()), 'label' => $item->getLabel(), 'icon' => $item->getIcon(),
            'url' => $item->getUrl(), 'active' => static::matches($item->getUrl()), 'children' => []];
    }

    private static function dedicatedPath(object $row): ?string
    {
        return match (true) {
            $row->route === 'voyager.dashboard' => '/filament',
            $row->route === 'voyager.orders.index' => '/filament/orders',
            $row->route === 'voyager.canvas-slider.index' => '/filament/canvas-sliders',
            $row->url === '/admin/alt-suggestions' => '/filament/image-alt-suggestions',
            $row->url === '/admin/settings' || $row->route === 'voyager.settings.index' => '/filament/voyager-settings',
            $row->url === '/admin/translations' => '/'.config('translation-manager.route.prefix'),
            default => null,
        };
    }
}
