<?php

namespace App\Filament\Bread;

class BreadNavigationIcon
{
    public static function resolve(?string $legacy, string $slug = ''): string
    {
        $mapping = config('voyager_navigation.icons', []);
        foreach (preg_split('/\s+/', trim($legacy ?? '')) as $icon) {
            if (isset($mapping[$icon])) {
                return $mapping[$icon];
            }
            if (preg_match('/^heroicon-[os]-[a-z0-9-]+$/', $icon)
                && is_file(base_path('vendor/blade-ui-kit/blade-heroicons/resources/svg/' . substr($icon, 9) . '.svg'))) {
                return $icon;
            }
        }

        return 'heroicon-o-' . match (true) {
            str_contains($slug, 'translation'), str_contains($slug, 'locale') => 'language',
            str_contains($slug, 'menu') => 'bars-3',
            str_contains($slug, 'color') => 'swatch',
            str_contains($slug, 'categor'), str_contains($slug, 'tag'), str_contains($slug, 'genre'), str_contains($slug, 'type') => 'tag',
            str_contains($slug, 'user'), str_contains($slug, 'author'), str_contains($slug, 'role') => 'users',
            str_contains($slug, 'order'), str_contains($slug, 'cart') => 'shopping-bag',
            str_contains($slug, 'delivery') => 'truck',
            str_contains($slug, 'address'), str_contains($slug, 'town') => 'map-pin',
            str_contains($slug, 'setting'), str_contains($slug, 'config') => 'cog-6-tooth',
            str_contains($slug, 'mail') => 'envelope',
            str_contains($slug, 'review'), str_contains($slug, 'faq') => 'chat-bubble-left-right',
            str_contains($slug, 'stock'), str_contains($slug, 'coupon'), str_contains($slug, 'price') => 'currency-euro',
            str_contains($slug, 'blog'), str_contains($slug, 'page'), str_contains($slug, 'seo') => 'document-text',
            str_contains($slug, 'gallery'), str_contains($slug, 'canvas'), str_contains($slug, 'slider'), str_contains($slug, 'photo'), str_contains($slug, 'image'), str_contains($slug, 'portrait') => 'photo',
            default => 'squares-2x2',
        };
    }
}
