<?php

namespace App\Services\AltGeneration;

use Illuminate\Contracts\Config\Repository as ConfigRepository;

/**
 * Resolves locales used by the alt-generation workflow.
 */
class LocaleResolver
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(ConfigRepository $config)
    {
        $this->config = $config;
    }

    /**
     * @return array<int, string>
     */
    public function supportedLocales(): array
    {
        $locales = $this->localesFromLaravelLocalization();

        if (empty($locales)) {
            $locales = $this->configuredFallbackLocales();
        }

        if (empty($locales)) {
            $locales = ['ru', 'en', 'de', 'lv', 'lt', 'pl', 'ee'];
        }

        return array_values(array_unique($locales));
    }

    /**
     * @return string
     */
    public function defaultLocale(): string
    {
        $supported = $this->supportedLocales();
        $configuredDefault = $this->normalizeLocale($this->config->get('alt_generation.locale_fallback.default_locale'));

        if ($configuredDefault !== null && in_array($configuredDefault, $supported, true)) {
            return $configuredDefault;
        }

        $appLocale = $this->normalizeLocale($this->config->get('app.locale'));

        if ($appLocale !== null && in_array($appLocale, $supported, true)) {
            return $appLocale;
        }

        if (in_array('ru', $supported, true)) {
            return 'ru';
        }

        return $supported[0] ?? 'ru';
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function normalizeOrDefault(?string $locale): string
    {
        $normalized = $this->normalizeLocale($locale);
        $supported = $this->supportedLocales();

        if ($normalized !== null && in_array($normalized, $supported, true)) {
            return $normalized;
        }

        return $this->defaultLocale();
    }

    /**
     * @return string
     */
    public function currentLocale(): string
    {
        return $this->normalizeOrDefault(app()->getLocale());
    }

    /**
     * @param string|null $locale
     * @return bool
     */
    public function isSupported(?string $locale): bool
    {
        $normalized = $this->normalizeLocale($locale);

        return $normalized !== null && in_array($normalized, $this->supportedLocales(), true);
    }

    /**
     * @return array<int, string>
     */
    private function localesFromLaravelLocalization(): array
    {
        $supported = $this->config->get('laravellocalization.supportedLocales', []);

        if (!is_array($supported) || empty($supported)) {
            return [];
        }

        return $this->normalizeLocaleList(array_keys($supported));
    }

    /**
     * @return array<int, string>
     */
    private function configuredFallbackLocales(): array
    {
        $configured = $this->config->get('alt_generation.locale_fallback.supported_locales', []);

        if (is_string($configured)) {
            $configured = explode(',', $configured);
        }

        return is_array($configured) ? $this->normalizeLocaleList($configured) : [];
    }

    /**
     * @param array<int|string, mixed> $locales
     * @return array<int, string>
     */
    private function normalizeLocaleList(array $locales): array
    {
        $normalized = [];

        foreach ($locales as $locale) {
            $value = $this->normalizeLocale(is_string($locale) ? $locale : null);

            if ($value !== null) {
                $normalized[] = $value;
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @param mixed $locale
     * @return string|null
     */
    private function normalizeLocale($locale): ?string
    {
        if (!is_string($locale)) {
            return null;
        }

        $locale = strtolower(trim(str_replace('_', '-', $locale)));

        return $locale === '' ? null : $locale;
    }
}
