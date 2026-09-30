<?php

namespace App\Filament\Bread;

use Filament\Navigation\NavigationGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BreadNavigationGroups
{
    public static function make(): array
    {
        if (! Schema::hasTable('menus') || ! Schema::hasTable('menu_items')) {
            return [];
        }

        $menuId = DB::table('menus')->where('name', 'admin')->value('id');
        if (! $menuId) {
            return [];
        }

        $parents = DB::table('menu_items')->where('menu_id', $menuId)->where('status', 1)
            ->whereIn('id', DB::table('menu_items')->where('menu_id', $menuId)->where('status', 1)->select('parent_id'))
            ->orderBy('order')->get();

        $icons = $parents->mapWithKeys(fn (object $parent): array => [
            trim((string) $parent->title) => BreadNavigationIcon::resolve($parent->icon_class ?? null),
        ])->all();

        foreach (config('voyager_navigation.group_icons', []) as $label => $icon) {
            $icons[$label] ??= BreadNavigationIcon::resolve($icon);
        }

        $groups = [];
        foreach ($icons as $label => $icon) {
            $svg = file_get_contents(base_path('vendor/blade-ui-kit/blade-heroicons/resources/svg/'.substr($icon, 9).'.svg'));

            // A mask adds the group icon while keeping Filament's child item icons.
            // The URI comes only from a validated bundled SVG, never from menu input.
            $groups[] = NavigationGroup::make($label)
                ->extraSidebarAttributes([
                    'class' => 'viar-navigation-group',
                    'style' => '--viar-group-icon: url(data:image/svg+xml,'.rawurlencode($svg).')',
                ]);
        }

        return $groups;
    }
}
