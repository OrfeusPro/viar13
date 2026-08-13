<?php

namespace App\Jobs;

use App\Models\ImageAltSuggestion;
use App\Services\AltGeneration\AltGenerator;
use App\Services\AltGeneration\Exceptions\DailyLimitReached;
use App\Services\AltGeneration\Exceptions\NoPublicUrl;
use App\Services\AltGeneration\ImageDescriptor;
use App\Services\AltGeneration\LocaleResolver;
use App\Services\AltGeneration\SkippedAltGenerationLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use Throwable;

/**
 * Generates one alt/title suggestion asynchronously.
 */
class GenerateImageAltJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int
     */
    public $tries = 3;

    /**
     * @var array<int, int>
     */
    public $backoff = [60, 300, 900];

    /**
     * @var int
     */
    private $suggestionId;

    /**
     * @var bool
     */
    private $force;

    /**
     * @param int $suggestionId
     * @param bool $force
     */
    public function __construct(int $suggestionId, bool $force = false)
    {
        $this->suggestionId = $suggestionId;
        $this->force = $force;

        $this->onQueue(config('alt_generation.queue', 'alt-gen'));
    }

    /**
     * Exponential backoff for queue workers that support the method form.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return $this->backoff;
    }

    /**
     * @param \App\Services\AltGeneration\AltGenerator $gen
     * @param \App\Services\AltGeneration\SkippedAltGenerationLogger $skippedLogger
     * @param \App\Services\AltGeneration\LocaleResolver $localeResolver
     * @param \Illuminate\Contracts\Config\Repository $config
     * @return void
     */
    public function handle(
        AltGenerator $gen,
        SkippedAltGenerationLogger $skippedLogger,
        LocaleResolver $localeResolver,
        ConfigRepository $config
    ): void {
        $suggestion = $this->resolveSuggestion();

        if (!$this->force && in_array($suggestion->status, [
            ImageAltSuggestion::STATUS_PENDING,
            ImageAltSuggestion::STATUS_APPROVED,
            ImageAltSuggestion::STATUS_APPLIED,
        ], true)) {
            return;
        }

        if ($this->force && in_array($suggestion->status, [
            ImageAltSuggestion::STATUS_PENDING,
            ImageAltSuggestion::STATUS_APPROVED,
            ImageAltSuggestion::STATUS_APPLIED,
        ], true)) {
            $suggestion->setStatus(ImageAltSuggestion::STATUS_NEW);
            $suggestion->save();
        }

        $locale = $localeResolver->normalizeOrDefault($suggestion->locale);
        $config->set('app.locale', $locale);
        app()->setLocale($locale);
        $entity = $this->resolveEntity($suggestion);
        $descriptor = $this->buildDescriptor($suggestion, $config);

        try {
            $gen->generateFor($entity, $descriptor, $locale, $suggestion);
        } catch (NoPublicUrl $exception) {
            $skippedLogger->log($entity, 'no_public_url', [
                'source' => 'GenerateImageAltJob',
                'suggestion_id' => $this->suggestionId,
                'locale' => $locale,
                'image_path' => $suggestion->image_path,
                'field' => $suggestion->field,
            ]);

            $this->markFailed($suggestion, $exception);
        } catch (DailyLimitReached $exception) {
            if ($this->job !== null) {
                $this->release(3600);

                return;
            }

            $this->markFailed($suggestion, $exception);
        } catch (Throwable $exception) {
            $this->markFailed($suggestion, $exception);
        }
    }

    /**
     * Mark the matching suggestion as failed when all queue attempts fail.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        $suggestion = ImageAltSuggestion::query()->find($this->suggestionId);

        if ($suggestion instanceof ImageAltSuggestion) {
            $this->markFailed($suggestion, $exception);
        }
    }

    /**
     * @return \App\Models\ImageAltSuggestion
     */
    private function resolveSuggestion(): ImageAltSuggestion
    {
        $suggestion = ImageAltSuggestion::query()->find($this->suggestionId);

        if (!$suggestion instanceof ImageAltSuggestion) {
            throw new RuntimeException('Image alt suggestion was not found: ' . $this->suggestionId);
        }

        return $suggestion;
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param \Throwable $exception
     * @return void
     */
    private function markFailed(ImageAltSuggestion $suggestion, Throwable $exception): void
    {
        $suggestion->setStatus(ImageAltSuggestion::STATUS_FAILED);
        $suggestion->error = $exception->getMessage();
        $suggestion->save();
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function resolveEntity(ImageAltSuggestion $suggestion): Model
    {
        $imageableType = (string) $suggestion->imageable_type;

        if (!class_exists($imageableType) || !is_subclass_of($imageableType, Model::class)) {
            throw new RuntimeException('Invalid imageable model type: ' . $imageableType);
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
        $modelClass = $imageableType;
        $entity = $modelClass::query()->find($suggestion->imageable_id);

        if (!$entity instanceof Model) {
            throw new RuntimeException('Imageable model was not found.');
        }

        return $entity;
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param \Illuminate\Contracts\Config\Repository $config
     * @return \App\Services\AltGeneration\ImageDescriptor
     */
    private function buildDescriptor(ImageAltSuggestion $suggestion, ConfigRepository $config): ImageDescriptor
    {
        $promptContext = (array) $suggestion->prompt_context;
        $imageContext = isset($promptContext['image']) && is_array($promptContext['image'])
            ? $promptContext['image']
            : [];
        $imagePath = (string) $suggestion->image_path;
        $field = $suggestion->field === null ? null : (string) $suggestion->field;
        $meta = [];

        foreach (['media_id', 'collection_name', 'custom_alt_key', 'custom_title_key'] as $key) {
            if (array_key_exists($key, $imageContext)) {
                $meta[$key] = $imageContext[$key];
            }
        }

        return new ImageDescriptor(
            $imagePath,
            $field === '' ? null : $field,
            $suggestion->current_alt,
            $suggestion->current_title,
            (string) ($imageContext['source_type'] ?? $this->sourceTypeFromConfig($suggestion, $config)),
            isset($imageContext['xpath']) ? (string) $imageContext['xpath'] : null,
            $this->absolutePathFor($imagePath),
            isset($imageContext['public_url']) && is_string($imageContext['public_url'])
                ? $imageContext['public_url']
                : $this->publicUrlFor($imagePath),
            $meta
        );
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param \Illuminate\Contracts\Config\Repository $config
     * @return string
     */
    private function sourceTypeFromConfig(ImageAltSuggestion $suggestion, ConfigRepository $config): string
    {
        $target = $config->get('alt_generation.targets.' . $suggestion->imageable_type, []);
        $htmlFields = isset($target['html_fields']) && is_array($target['html_fields'])
            ? $target['html_fields']
            : [];

        return in_array($suggestion->field, $htmlFields, true) ? 'html' : 'field';
    }

    /**
     * @param string $path
     * @return string|null
     */
    private function absolutePathFor(string $path): ?string
    {
        if (strpos($path, 'data:image/') === 0 || preg_match('#^https?://#i', $path)) {
            return null;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        foreach (['uploads/', 'theme/', 'admin/', 'images/', 'img/'] as $publicPrefix) {
            if (strpos($normalized, $publicPrefix) === 0) {
                return public_path($normalized);
            }
        }

        $storagePath = storage_path('app/public/' . $normalized);

        return is_file($storagePath) ? $storagePath : public_path('storage/' . $normalized);
    }

    /**
     * @param string $path
     * @return string|null
     */
    private function publicUrlFor(string $path): ?string
    {
        if (strpos($path, 'data:image/') === 0) {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        foreach (['uploads/', 'theme/', 'admin/', 'images/', 'img/'] as $publicPrefix) {
            if (strpos($normalized, $publicPrefix) === 0) {
                return url('/' . $normalized);
            }
        }

        return url('/storage/' . $normalized);
    }
}
