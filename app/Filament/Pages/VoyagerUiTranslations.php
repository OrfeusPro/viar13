<?php

namespace App\Filament\Pages;

use App\Filament\Bread\BreadNavigationIcon;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VoyagerUiTranslations extends Page
{
    protected static ?string $slug = 'ui-translations';

    protected static ?string $navigationLabel = 'Переводы интерфейса';

    protected string $view = 'filament.pages.voyager-ui-translations';

    public static function canAccess(): bool
    {
        $user = auth('filament')->user();

        return Schema::hasTable('ltm_translations') && $user && method_exists($user, 'hasPermission') && $user->hasPermission('browse_admin');
    }

    public static function getNavigationItems(): array
    {
        if (! static::canAccess()) {
            return [];
        }

        $base = url(config('translation-manager.route.prefix'));
        $items = [NavigationItem::make('Переводы интерфейса')->icon('heroicon-o-language')->url($base)->sort(2)];
        if (Schema::hasTable('menu_items')) {
            foreach (DB::table('menu_items')->where('status', 1)->where('url', 'like', '/admin/translations/view/%')->orderBy('order')->get() as $item) {
                if (preg_match('~^/admin/translations/view/([a-zA-Z0-9_-]+)$~', (string) $item->url, $match)) {
                    $items[] = NavigationItem::make((string) $item->title)
                        ->key('ui-translations-'.$item->id)
                        ->icon(BreadNavigationIcon::resolve($item->icon_class ?? null, 'translations'))
                        ->group('Переводы')->sort((int) $item->order)
                        ->url($base.'/view/'.rawurlencode($match[1]));
                }
            }
        }

        return $items;
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $base = url(config('translation-manager.route.prefix'));
        $group = request()->query('group');
        $target = is_string($group) && preg_match('/\A[a-zA-Z0-9_-]+\z/', $group) ? $base.'/view/'.rawurlencode($group) : $base;
        $this->redirect($target);
    }
}
