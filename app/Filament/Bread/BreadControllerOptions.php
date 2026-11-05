<?php

namespace App\Filament\Bread;

class BreadControllerOptions
{
    public function normalize(?string $controller): ?string
    {
        return filled($controller) ? ltrim($controller, '\\') : null;
    }

    public function options(object $type): array
    {
        if (app(BreadRegistry::class)->isDedicated((object) $type)) { return []; }
        $options = ['TCG\\Voyager\\Http\\Controllers\\VoyagerBaseController' => 'Стандартный BREAD'];
        $special = match ($type->name) {
            'users' => ['TCG\\Voyager\\Http\\Controllers\\VoyagerUserController', 'Пользователи'],
            'roles' => ['TCG\\Voyager\\Http\\Controllers\\VoyagerRoleController', 'Роли и разрешения'],
            'locales' => ['App\\Http\\Controllers\\Admin\\AdminLocaleController', 'Языки и каталоги переводов'],
            default => null,
        };
        if ($special) { $options[$special[0]] = $special[1]; }
        $current = $type->controller ?? null;
        if (filled($current)) { $options[$current] = $options[$this->normalize($current)] ?? $current; }

        return $options;
    }

    public function supports(object $type, ?string $controller): bool
    {
        if (app(BreadRegistry::class)->isDedicated((object) $type)) { return false; }
        $class = $this->normalize($controller);
        if ($class === null || $class === 'TCG\\Voyager\\Http\\Controllers\\VoyagerBaseController') { return true; }

        return $class === match ($type->name) {
            'users' => 'TCG\\Voyager\\Http\\Controllers\\VoyagerUserController',
            'roles' => 'TCG\\Voyager\\Http\\Controllers\\VoyagerRoleController',
            'locales' => 'App\\Http\\Controllers\\Admin\\AdminLocaleController',
            default => '',
        };
    }
}
