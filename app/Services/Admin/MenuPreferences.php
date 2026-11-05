<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class MenuPreferences
{
    public function read(User $user): array
    {
        $settings = DB::table('users')->where('id', $user->getKey())->value('settings');
        $settings = $settings ? json_decode($settings, true, flags: JSON_THROW_ON_ERROR) : [];
        $preferences = $settings['filament_menu'] ?? [];

        return [
            'show_all' => (bool) ($preferences['show_all'] ?? true),
            'hidden' => array_values(array_filter((array) ($preferences['hidden'] ?? []), 'is_string')),
        ];
    }

    public function save(User $user, array $preferences): void
    {
        DB::transaction(function () use ($user, $preferences): void {
            $row = DB::table('users')->where('id', $user->getKey())->lockForUpdate()->first(['settings']);
            abort_unless($row, 403);
            $settings = $row->settings ? json_decode($row->settings, true, flags: JSON_THROW_ON_ERROR) : [];
            $settings['filament_menu'] = array_replace($settings['filament_menu'] ?? [], $preferences);
            DB::table('users')->where('id', $user->getKey())->update([
                'settings' => json_encode($settings, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ]);
        });
    }

    public static function groups(array $tree): array
    {
        $flatten = function (array $nodes, string $prefix = '') use (&$flatten): array {
            $items = [];
            foreach ($nodes as $node) {
                $label = $prefix.$node['label'];
                if ($node['children']) {
                    $items += $flatten($node['children'], $label.' / ');
                } else {
                    $items[$node['id']] = $label;
                }
            }

            return $items;
        };
        $groups = [];
        foreach ($tree as $node) {
            $groups[] = ['label' => $node['children'] ? $node['label'] : 'Основные разделы',
                'items' => $flatten($node['children'] ?: [$node])];
        }

        return $groups;
    }

    public static function filter(array $tree, array $hidden): array
    {
        $filtered = [];
        foreach ($tree as $node) {
            if ($node['children']) {
                $node['children'] = self::filter($node['children'], $hidden);
                if (! $node['children']) {
                    continue;
                }
            } elseif (in_array($node['id'], $hidden, true)) {
                continue;
            }
            $filtered[] = $node;
        }

        return $filtered;
    }
}
