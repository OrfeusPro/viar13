<?php

namespace App\Console\Commands;

use App\Jobs\GenerateImageAltJob;
use App\Models\ImageAltSuggestion;
use App\Services\AltGeneration\AltGenerator;
use App\Services\AltGeneration\LocaleResolver;
use App\Services\AltGeneration\SkippedAltGenerationLogger;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Throwable;

/**
 * Dispatches generation jobs for queued image suggestions.
 */
class AltGenerateCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'alt:generate
        {--status=new,failed : Comma-separated statuses to process}
        {--locale= : Limit generation to one supported locale}
        {--model= : Limit to one imageable model FQCN or class basename}
        {--limit=50 : Maximum suggestions to process}
        {--dry-run : Show matching suggestions without dispatching or calling OpenAI}
        {--sync : Generate immediately instead of dispatching queue jobs}
        {--force : Allow regeneration of pending/approved/applied rows when explicitly selected}';

    /**
     * @var string
     */
    protected $description = 'Generate alt/title suggestions for queued images';

    /**
     * @var \Illuminate\Contracts\Bus\Dispatcher
     */
    private $dispatcher;

    /**
     * @var \App\Services\AltGeneration\AltGenerator
     */
    private $generator;

    /**
     * @var \App\Services\AltGeneration\LocaleResolver
     */
    private $localeResolver;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @var \App\Services\AltGeneration\SkippedAltGenerationLogger
     */
    private $skippedLogger;

    /**
     * @param \Illuminate\Contracts\Bus\Dispatcher $dispatcher
     * @param \App\Services\AltGeneration\AltGenerator $generator
     * @param \App\Services\AltGeneration\LocaleResolver $localeResolver
     * @param \App\Services\AltGeneration\SkippedAltGenerationLogger $skippedLogger
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(
        Dispatcher $dispatcher,
        AltGenerator $generator,
        LocaleResolver $localeResolver,
        SkippedAltGenerationLogger $skippedLogger,
        ConfigRepository $config
    ) {
        parent::__construct();

        $this->dispatcher = $dispatcher;
        $this->generator = $generator;
        $this->localeResolver = $localeResolver;
        $this->skippedLogger = $skippedLogger;
        $this->config = $config;
    }

    /**
     * @return int
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (!$dryRun && trim((string) $this->config->get('alt_generation.openai.api_key')) === '') {
            $this->error('OPENAI_API_KEY is not configured. Set it before running alt:generate.');

            return 1;
        }

        $statuses = $this->statusesFromOption((string) $this->option('status'));
        $limit = max(1, (int) $this->option('limit'));
        $model = $this->resolveModelOption(trim((string) $this->option('model')));
        $locale = trim((string) $this->option('locale'));
        $force = (bool) $this->option('force');

        if ($locale !== '' && !$this->localeResolver->isSupported($locale)) {
            $this->error('Unsupported locale for alt generation: ' . $locale);
            $this->line('Supported locales: ' . implode(', ', $this->localeResolver->supportedLocales()));

            return 1;
        }

        $locale = $locale === '' ? null : $this->localeResolver->normalizeOrDefault($locale);

        $query = ImageAltSuggestion::query()
            ->whereIn('status', $statuses)
            ->whereNotNull('imageable_type')
            ->whereNotNull('imageable_id')
            ->orderBy('id')
            ->limit($limit);

        if (!$force) {
            $query->whereNotIn('status', [
                ImageAltSuggestion::STATUS_PENDING,
                ImageAltSuggestion::STATUS_APPROVED,
                ImageAltSuggestion::STATUS_APPLIED,
            ]);
        }

        if ($locale !== null) {
            $query->where('locale', $locale);
        }

        if ($model !== '') {
            $query->where('imageable_type', $model);
        }

        /** @var \Illuminate\Support\Collection<int, \App\Models\ImageAltSuggestion> $suggestions */
        $suggestions = $query->get();

        if ($suggestions->isEmpty()) {
            $this->info('No image alt suggestions matched the filters.');

            return 0;
        }

        $this->table(
            ['id', 'model', 'imageable_id', 'field', 'locale', 'image_path', 'page_url', 'status'],
            $suggestions->map(function (ImageAltSuggestion $suggestion): array {
                return [
                    (string) $suggestion->id,
                    class_basename((string) $suggestion->imageable_type),
                    (string) $suggestion->imageable_id,
                    (string) $suggestion->field,
                    (string) $suggestion->locale,
                    (string) $suggestion->image_path,
                    (string) $suggestion->page_url,
                    (string) $suggestion->status,
                ];
            })->all()
        );

        if ($dryRun) {
            $this->info('Dry-run image alt generation suggestions: ' . $suggestions->count());

            return 0;
        }

        $bar = $this->output->createProgressBar($suggestions->count());
        $bar->start();

        $suggestions->each(function (ImageAltSuggestion $suggestion) use ($bar, $force): void {
            $job = new GenerateImageAltJob((int) $suggestion->id, $force);

            if ($this->option('sync')) {
                $this->runSyncJob($job, $suggestion);
            } else {
                $this->dispatcher->dispatch($job);
            }

            $bar->advance();
        });

        $bar->finish();
        $this->line('');
        $this->info(((bool) $this->option('sync') ? 'Processed' : 'Queued') . ' image alt generation jobs: ' . $suggestions->count());

        return 0;
    }

    /**
     * @param \App\Jobs\GenerateImageAltJob $job
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @return void
     */
    private function runSyncJob(GenerateImageAltJob $job, ImageAltSuggestion $suggestion): void
    {
        try {
            $job->handle($this->generator, $this->skippedLogger, $this->localeResolver, $this->config);
        } catch (Throwable $exception) {
            $suggestion->setStatus(ImageAltSuggestion::STATUS_FAILED);
            $suggestion->error = $exception->getMessage();
            $suggestion->save();
        }
    }

    /**
     * @param string $statusOption
     * @return array<int, string>
     */
    private function statusesFromOption(string $statusOption): array
    {
        $statuses = array_values(array_filter(array_map('trim', explode(',', $statusOption))));

        return empty($statuses) ? [ImageAltSuggestion::STATUS_NEW] : $statuses;
    }

    /**
     * @param string $model
     * @return string
     */
    private function resolveModelOption(string $model): string
    {
        if ($model === '' || class_exists($model)) {
            return $model;
        }

        foreach (array_keys((array) $this->config->get('alt_generation.targets', [])) as $class) {
            if (is_string($class) && class_basename($class) === $model) {
                return $class;
            }
        }

        return $model;
    }
}
