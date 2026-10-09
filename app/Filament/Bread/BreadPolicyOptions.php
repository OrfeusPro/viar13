<?php

namespace App\Filament\Bread;

class BreadPolicyOptions
{
    public const BASE = 'TCG\\Voyager\\Policies\\BasePolicy';
    public const USER = 'TCG\\Voyager\\Policies\\UserPolicy';

    public function normalize(?string $policy): ?string
    {
        return filled($policy) ? ltrim($policy, '\\') : null;
    }

    public function options(object $type): array
    {
        if (app(BreadRegistry::class)->isDedicated((object) $type)) { return []; }
        $options = [self::BASE => 'Стандартные права BREAD'];
        if ($type->name === 'users') { $options[self::USER] = 'Пользователи: доступ к собственному профилю'; }
        $current = $type->policy_name ?? null;
        if (filled($current)) { $options[$current] = $options[$this->normalize($current)] ?? $current; }

        return $options;
    }

    public function supports(object $type, ?string $policy): bool
    {
        if (app(BreadRegistry::class)->isDedicated((object) $type)) { return false; }
        $class = $this->normalize($policy);

        return $class === null || $class === self::BASE || ($type->name === 'users' && $class === self::USER);
    }
}
