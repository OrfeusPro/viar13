<?php

namespace App\Services\SeoMetaGeneration;

use App\Services\AltGeneration\Exceptions\DailyLimitReached;
use App\Services\AltGeneration\Exceptions\OpenAiApiException;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Model;
use Psr\Log\LoggerInterface;

class SeoMetaGenerator
{
    /**
     * @var \App\Services\SeoMetaGeneration\SeoMetaContextResolver
     */
    private $contextResolver;

    /**
     * @var \App\Services\SeoMetaGeneration\OpenAiSeoMetaClient
     */
    private $client;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    private $cache;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        SeoMetaContextResolver $contextResolver,
        OpenAiSeoMetaClient $client,
        ConfigRepository $config,
        CacheRepository $cache,
        LoggerInterface $logger
    ) {
        $this->contextResolver = $contextResolver;
        $this->client = $client;
        $this->config = $config;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * @param array<int, string> $locales
     * @param array<string, mixed> $options
     * @return array{cached: bool, cache_key: string, context: array<string, mixed>, context_json: string, locales: array<string, array{meta_title: string, meta_description: string}>, tokens: int|null, model: string|null}
     */
    public function generate(Model $entity, array $locales, bool $useCache = true, array $options = []): array
    {
        $modelClass = get_class($entity);
        $target = $this->contextResolver->targetForModel($modelClass);

        if ($target === null) {
            throw new \InvalidArgumentException('Unsupported SEO meta model: ' . $modelClass);
        }

        $locales = $this->contextResolver->normalizeRequestedLocales($locales);
        $context = $this->contextResolver->resolve($entity, $locales);
        $context = $this->applyGenerationOptions($context, $locales, $options);
        $contextJson = $this->contextResolver->compressedJson($context);
        $cacheKey = $this->cacheKey($entity, $locales, $contextJson);

        if ($this->cacheEnabled($useCache) && $this->cache->has($cacheKey)) {
            $cached = $this->cache->get($cacheKey);

            if (is_array($cached)) {
                return [
                    'cached' => true,
                    'cache_key' => $cacheKey,
                    'context' => $context,
                    'context_json' => $contextJson,
                    'locales' => (array) ($cached['locales'] ?? []),
                    'tokens' => isset($cached['tokens']) ? (int) $cached['tokens'] : null,
                    'model' => isset($cached['model']) ? (string) $cached['model'] : null,
                ];
            }
        }

        $model = (string) $this->config->get('seo_meta_generation.openai.model', 'gpt-4o-mini');
        $fallbackModel = (string) $this->config->get('seo_meta_generation.openai.fallback_model', 'gpt-4o');

        try {
            $result = $this->generateWithLimit($contextJson, $locales, $model);
            $usedModel = $model;
        } catch (OpenAiApiException $exception) {
            if ($exception->shouldTryFallback() && $fallbackModel !== '' && $fallbackModel !== $model) {
                $this->logger->warning('OpenAI SEO meta generation failed, trying fallback model.', [
                    'model' => $model,
                    'fallback_model' => $fallbackModel,
                    'status_code' => $exception->getStatusCode(),
                ]);

                $result = $this->generateWithLimit($contextJson, $locales, $fallbackModel);
                $usedModel = $fallbackModel;
            } else {
                throw $exception;
            }
        }

        $payload = [
            'locales' => $result['locales'],
            'tokens' => $result['tokens'],
            'model' => $usedModel,
        ];

        if ($this->cacheEnabled($useCache)) {
            $this->cache->put($cacheKey, $payload, Carbon::now()->addMinutes($this->cacheTtlMinutes()));
        }

        return [
            'cached' => false,
            'cache_key' => $cacheKey,
            'context' => $context,
            'context_json' => $contextJson,
            'locales' => $result['locales'],
            'tokens' => $result['tokens'],
            'model' => $usedModel,
        ];
    }

    /**
     * @param array<int, string> $locales
     * @return array{locales: array<string, array{meta_title: string, meta_description: string}>, tokens: int|null, raw: array<string, mixed>}
     */
    private function generateWithLimit(string $contextJson, array $locales, string $model): array
    {
        $this->incrementDailyCounter();

        return $this->client->generate($contextJson, $locales, $model);
    }

    private function incrementDailyCounter(): void
    {
        $limit = (int) $this->config->get('seo_meta_generation.daily_call_limit', 2000);

        if ($limit <= 0) {
            return;
        }

        $now = Carbon::now();
        $key = 'seo_meta_generation:openai_calls:' . $now->toDateString();

        if (!$this->cache->has($key)) {
            $this->cache->put($key, 0, $now->copy()->endOfDay());
        }

        $count = $this->cache->increment($key);

        if ($count === false || $count === null) {
            $count = ((int) $this->cache->get($key, 0)) + 1;
            $this->cache->put($key, $count, $now->copy()->endOfDay());
        }

        if ((int) $count > $limit) {
            throw new DailyLimitReached('Daily OpenAI SEO meta generation call limit has been reached.');
        }
    }

    /**
     * @param array<string, mixed> $context
     * @param array<int, string> $locales
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function applyGenerationOptions(array $context, array $locales, array $options): array
    {
        $keywords = isset($options['keywords']) ? trim((string) $options['keywords']) : '';

        if ($keywords === '') {
            return $context;
        }

        $keywordContext = [
            'raw' => $keywords,
            'phrases' => $this->keywordPhrases($keywords),
            'required' => true,
            'instruction' => 'These are admin-supplied required SEO/campaign keywords for this exact row. Include at least one phrase naturally in the generated meta title or description. Do not keyword-stuff and do not invent unsupported discount amounts or promises.',
        ];

        $context['generation_keywords'] = $keywordContext;

        if (isset($context['locales']) && is_array($context['locales'])) {
            foreach ($locales as $locale) {
                if (!isset($context['locales'][$locale]) || !is_array($context['locales'][$locale])) {
                    continue;
                }

                $context['locales'][$locale]['generation_keywords'] = $keywordContext;
            }
        }

        return $context;
    }

    /**
     * @return array<int, string>
     */
    private function keywordPhrases(string $keywords): array
    {
        $parts = preg_split('/[\r\n,;]+/u', $keywords);

        if (!is_array($parts)) {
            return [];
        }

        $result = [];
        $seen = [];

        foreach ($parts as $part) {
            $part = trim((string) preg_replace('/\s+/u', ' ', $part));

            if ($part === '') {
                continue;
            }

            $key = function_exists('mb_strtolower') ? mb_strtolower($part, 'UTF-8') : strtolower($part);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $result[] = $part;
        }

        return $result;
    }

    /**
     * @param array<int, string> $locales
     */
    private function cacheKey(Model $entity, array $locales, string $contextJson): string
    {
        return 'seo_meta_generation:' . sha1(get_class($entity) . '|' . $entity->getKey() . '|' . implode(',', $locales) . '|' . $contextJson);
    }

    private function cacheEnabled(bool $requested): bool
    {
        return $requested && (bool) $this->config->get('seo_meta_generation.cache.enabled', true);
    }

    private function cacheTtlMinutes(): int
    {
        return max(1, (int) $this->config->get('seo_meta_generation.cache.ttl_minutes', 1440));
    }
}
