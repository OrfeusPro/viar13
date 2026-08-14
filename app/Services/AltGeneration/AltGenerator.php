<?php

namespace App\Services\AltGeneration;

use App\Models\ImageAltSuggestion;
use App\Services\AltGeneration\Exceptions\DailyLimitReached;
use App\Services\AltGeneration\Exceptions\OpenAiApiException;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Model;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Orchestrates context resolution, OpenAI calls, and suggestion persistence.
 */
class AltGenerator
{
    /**
     * @var \App\Services\AltGeneration\PageContextResolver
     */
    private $contextResolver;

    /**
     * @var \App\Services\AltGeneration\OpenAiVisionClient
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

    /**
     * @param \App\Services\AltGeneration\PageContextResolver $contextResolver
     * @param \App\Services\AltGeneration\OpenAiVisionClient $client
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Contracts\Cache\Repository $cache
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        PageContextResolver $contextResolver,
        OpenAiVisionClient $client,
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
     * Generate and persist an alt/title suggestion for one image.
     *
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @return \App\Models\ImageAltSuggestion
     *
     * @throws \App\Services\AltGeneration\Exceptions\DailyLimitReached
     */
    public function generateFor(
        Model $entity,
        ImageDescriptor $image,
        ?string $locale = null,
        ?ImageAltSuggestion $targetSuggestion = null,
        bool $throwOnFailure = false
    ): ImageAltSuggestion
    {
        $context = $this->contextResolver->resolve($entity, $image, $locale);

        if (!$this->hasPageUrl($context)) {
            $context['page_url'] = null;
            $context['url'] = null;
            $context['page_url_status'] = 'not_resolved';
            $context['page_url_instruction'] = 'No verified page URL is available. Do not infer page meaning from a guessed URL or slug. Use only confirmed model, placement, surrounding text, and visible image content.';
        }

        $payload = $this->buildPayload($context, $image);

        if ($targetSuggestion instanceof ImageAltSuggestion) {
            $payload['generation'] = [
                'mode' => ($targetSuggestion->suggested_alt || $targetSuggestion->suggested_title) ? 'regenerate' : 'generate',
                'previous_alt' => $targetSuggestion->suggested_alt,
                'previous_title' => $targetSuggestion->suggested_title,
                'variation_seed' => bin2hex(random_bytes(6)),
            ];
        }

        $model = (string) $this->config->get('alt_generation.openai.model', 'gpt-4o-mini');
        $fallbackModel = (string) $this->config->get('alt_generation.openai.fallback_model', 'gpt-4o');

        try {
            $result = $this->generateWithLimit($payload, $model);
            $usedModel = $model;
        } catch (OpenAiApiException $exception) {
            if ($exception->shouldTryFallback() && $fallbackModel !== '' && $fallbackModel !== $model) {
                $this->logger->warning('OpenAI alt generation failed, trying fallback model.', [
                    'model' => $model,
                    'fallback_model' => $fallbackModel,
                    'status_code' => $exception->getStatusCode(),
                ]);

                try {
                    $result = $this->generateWithLimit($payload, $fallbackModel);
                    $usedModel = $fallbackModel;
                } catch (DailyLimitReached $limitException) {
                    throw $limitException;
                } catch (Throwable $fallbackException) {
                    $failure = $this->recordFailure(
                        $entity,
                        $image,
                        $context,
                        $fallbackModel,
                        $fallbackException,
                        $targetSuggestion
                    );

                    if ($throwOnFailure) {
                        throw $fallbackException;
                    }

                    return $failure;
                }
            } else {
                $failure = $this->recordFailure(
                    $entity,
                    $image,
                    $context,
                    $model,
                    $exception,
                    $targetSuggestion
                );

                if ($throwOnFailure) {
                    throw $exception;
                }

                return $failure;
            }
        } catch (DailyLimitReached $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            $failure = $this->recordFailure(
                $entity,
                $image,
                $context,
                $model,
                $exception,
                $targetSuggestion
            );

            if ($throwOnFailure) {
                throw $exception;
            }

            return $failure;
        }

        return $this->recordSuccess($entity, $image, $context, $result, $usedModel, $targetSuggestion);
    }

    /**
     * Build the client payload.
     *
     * The nested image metadata keeps the original public URL for logging and
     * prompt context. Only the top-level public_url used by OpenAiVisionClient
     * is replaced with a base64 data URL when a local file is available.
     *
     * @param array<string, mixed> $context
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @return array<string, mixed>
     */
    private function buildPayload(array $context, ImageDescriptor $image): array
    {
        return [
            'context' => $context,
            'image' => $image->toArray(),
            'path' => $image->path,
            'public_url' => $this->imageInputUrl($image),
            'absolute_path' => $image->absolutePath,
        ];
    }

    /**
     * Return a base64 data URL for a readable local image.
     *
     * Remote-only images keep their public URL. Existing data URLs are passed
     * through unchanged.
     *
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @return string|null
     */
    private function imageInputUrl(ImageDescriptor $image): ?string
    {
        $path = trim((string) $image->path);

        if (strpos($path, 'data:image/') === 0) {
            return $path;
        }

        $absolutePath = $image->absolutePath;

        if (
            $absolutePath === null ||
            trim((string) $absolutePath) === '' ||
            !is_file($absolutePath) ||
            !is_readable($absolutePath)
        ) {
            return $image->publicUrl;
        }

        $mimeType = $this->imageMimeType($absolutePath);

        if ($mimeType === null) {
            return $image->publicUrl;
        }

        $contents = file_get_contents($absolutePath);

        if ($contents === false || $contents === '') {
            return $image->publicUrl;
        }

        return 'data:' .
            $mimeType .
            ';base64,' .
            base64_encode($contents);
    }

    /**
     * Detect an OpenAI-compatible image MIME type.
     *
     * @param string $absolutePath
     * @return string|null
     */
    private function imageMimeType(string $absolutePath): ?string
    {
        $mimeType = null;

        if (function_exists('getimagesize')) {
            $imageInfo = @getimagesize($absolutePath);

            if (
                is_array($imageInfo) &&
                isset($imageInfo['mime']) &&
                is_string($imageInfo['mime'])
            ) {
                $mimeType = strtolower(
                    trim($imageInfo['mime'])
                );
            }
        }

        if (
            $mimeType === null &&
            function_exists('finfo_open')
        ) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);

            if ($finfo !== false) {
                $detected = @finfo_file(
                    $finfo,
                    $absolutePath
                );

                finfo_close($finfo);

                if (is_string($detected)) {
                    $mimeType = strtolower(
                        trim($detected)
                    );
                }
            }
        }

        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ];

        return in_array(
            $mimeType,
            $allowedMimeTypes,
            true
        )
            ? $mimeType
            : null;
    }

    /**
     * @param array<string, mixed> $payload
     * @param string $model
     * @return array{alt: string, title: string, tokens: int|null, raw: array<string, mixed>}
     */
    private function generateWithLimit(array $payload, string $model): array
    {
        $this->incrementDailyCounter();

        return $this->client->generate($payload, $model);
    }

    /**
     * @return void
     *
     * @throws \App\Services\AltGeneration\Exceptions\DailyLimitReached
     */
    private function incrementDailyCounter(): void
    {
        $limit = (int) $this->config->get('alt_generation.daily_call_limit', 2000);

        if ($limit <= 0) {
            return;
        }

        $now = Carbon::now();
        $key = 'alt_generation:openai_calls:' . $now->toDateString();

        if (!$this->cache->has($key)) {
            $this->cache->put($key, 0, $now->copy()->endOfDay());
        }

        $count = $this->cache->increment($key);

        if ($count === false || $count === null) {
            $count = ((int) $this->cache->get($key, 0)) + 1;
            $this->cache->put($key, $count, $now->copy()->endOfDay());
        }

        if ((int) $count > $limit) {
            throw new DailyLimitReached('Daily OpenAI alt-generation call limit has been reached.');
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @param array<string, mixed> $context
     * @param array{alt: string, title: string, tokens: int|null, raw: array<string, mixed>} $result
     * @param string $model
     * @param \App\Models\ImageAltSuggestion|null $targetSuggestion
     * @return \App\Models\ImageAltSuggestion
     */
    private function recordSuccess(
        Model $entity,
        ImageDescriptor $image,
        array $context,
        array $result,
        string $model,
        ?ImageAltSuggestion $targetSuggestion = null
    ): ImageAltSuggestion {
        $suggestion = $targetSuggestion ?: ImageAltSuggestion::firstOrNew($this->identity($entity, $image, $context));
        $suggestion->fill([
            'imageable_type' => get_class($entity),
            'imageable_id' => $entity->getKey(),
            'image_path' => $image->path,
            'field' => $image->field,
            'locale' => $this->localeFromContext($context),
            'page_url' => $this->pageUrlFromContext($context),
            'current_alt' => $context['current_alt'] ?? $image->currentAlt,
            'current_title' => $context['current_title'] ?? $image->currentTitle,
            'suggested_alt' => $result['alt'],
            'suggested_title' => $result['title'],
            'prompt_context' => $this->promptContext($context, $image),
            'model' => $model,
            'tokens_used' => $result['tokens'],
            'error' => null,
            'generated_at' => Carbon::now(),
            'image_hash' => $this->imageHash($image),
        ]);
        $suggestion->setStatus(ImageAltSuggestion::STATUS_GENERATED);

        if (!(bool) $this->config->get('alt_generation.auto_apply', false)) {
            $suggestion->setStatus(ImageAltSuggestion::STATUS_PENDING);
        }

        $suggestion->save();

        return $suggestion;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @param array<string, mixed> $context
     * @param string $model
     * @param \Throwable $exception
     * @param \App\Models\ImageAltSuggestion|null $targetSuggestion
     * @return \App\Models\ImageAltSuggestion
     */
    private function recordFailure(
        Model $entity,
        ImageDescriptor $image,
        array $context,
        string $model,
        Throwable $exception,
        ?ImageAltSuggestion $targetSuggestion = null
    ): ImageAltSuggestion {
        $this->logger->error('OpenAI alt generation failed.', [
            'model' => $model,
            'image_path' => $image->path,
            'error' => $exception->getMessage(),
        ]);

        $suggestion = $targetSuggestion ?: ImageAltSuggestion::firstOrNew($this->identity($entity, $image, $context));
        $suggestion->fill([
            'imageable_type' => get_class($entity),
            'imageable_id' => $entity->getKey(),
            'image_path' => $image->path,
            'field' => $image->field,
            'locale' => $this->localeFromContext($context),
            'page_url' => $this->pageUrlFromContext($context),
            'current_alt' => $context['current_alt'] ?? $image->currentAlt,
            'current_title' => $context['current_title'] ?? $image->currentTitle,
            'prompt_context' => $this->promptContext($context, $image),
            'model' => $model,
            'tokens_used' => null,
            'error' => $exception->getMessage(),
            'image_hash' => $this->imageHash($image),
        ]);
        $suggestion->setStatus(ImageAltSuggestion::STATUS_FAILED);
        $suggestion->save();

        return $suggestion;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function identity(Model $entity, ImageDescriptor $image, array $context): array
    {
        return [
            'imageable_type' => get_class($entity),
            'imageable_id' => $entity->getKey(),
            'image_path' => $image->path,
            'field' => $image->field,
            'locale' => $this->localeFromContext($context),
        ];
    }

    /**
     * @param array<string, mixed> $context
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @return array<string, mixed>
     */
    private function promptContext(array $context, ImageDescriptor $image): array
    {
        return [
            'context' => $context,
            'image' => $image->toArray(),
            'limits' => $this->config->get('alt_generation.limits', []),
            'instructions' => [
                'Do not repeat wording from sibling_image_alts.',
                'Language MUST match language.',
                'Alt <= 125 chars, Title <= 70 chars.',
                'Describe what is actually visible.',
                'No keyword stuffing.',
                'Mention brand only if visible or contextually required.',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $context
     * @return bool
     */
    private function hasPageUrl(array $context): bool
    {
        $pageUrl = $this->pageUrlFromContext($context);

        return $pageUrl !== null && trim((string) $pageUrl) !== '';
    }

    /**
     * @param array<string, mixed> $context
     * @return string|null
     */
    private function pageUrlFromContext(array $context): ?string
    {
        $pageUrl = $context['page_url'] ?? ($context['url'] ?? null);

        return $pageUrl === null || trim((string) $pageUrl) === '' ? null : (string) $pageUrl;
    }

    /**
     * @param array<string, mixed> $context
     * @return string|null
     */
    private function localeFromContext(array $context): ?string
    {
        $locale = $context['language'] ?? null;

        return is_string($locale) && trim($locale) !== '' ? trim($locale) : null;
    }

    /**
     * @param \App\Services\AltGeneration\ImageDescriptor $image
     * @return string|null
     */
    private function imageHash(ImageDescriptor $image): ?string
    {
        $path = $image->absolutePath;

        if ($path === null || preg_match('#^https?://#i', $path) || !is_file($path)) {
            return null;
        }

        $hash = hash_file('sha256', $path);

        return $hash === false ? null : $hash;
    }
}
