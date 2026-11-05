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
use Illuminate\Support\Collection;
use Throwable;

/**
 * Dispatches or synchronously runs generation jobs for image suggestions.
 *
 * In --sync mode, prints detailed exception and HTTP response information.
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
        {--dry-run : Show matching suggestion IDs without dispatching or calling OpenAI}
        {--sync : Generate immediately and print detailed errors}
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
        $sync = (bool) $this->option('sync');

        if (
            !$dryRun &&
            trim((string) $this->config->get('alt_generation.openai.api_key')) === ''
        ) {
            $this->error(
                'OPENAI_API_KEY is not configured. Set it before running alt:generate.'
            );

            return 1;
        }

        $statuses = $this->statusesFromOption(
            (string) $this->option('status')
        );

        $limit = max(
            1,
            (int) $this->option('limit')
        );

        $model = $this->resolveModelOption(
            trim((string) $this->option('model'))
        );

        $locale = trim(
            (string) $this->option('locale')
        );

        $force = (bool) $this->option('force');

        if (
            $locale !== '' &&
            !$this->localeResolver->isSupported($locale)
        ) {
            $this->error(
                'Unsupported locale for alt generation: ' . $locale
            );

            $this->line(
                'Supported locales: ' .
                implode(
                    ', ',
                    $this->localeResolver->supportedLocales()
                )
            );

            return 1;
        }

        $locale = $locale === ''
            ? null
            : $this->localeResolver->normalizeOrDefault($locale);

        $query = ImageAltSuggestion::query()
            ->whereIn('status', $statuses)
            ->whereNotNull('imageable_type')
            ->whereNotNull('imageable_id')
            ->orderBy('id');

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
        $suggestions = $this->loadValidSuggestions(
            $query,
            $limit
        );

        if ($suggestions->isEmpty()) {
            $this->info('No image alt suggestions matched the filters.');

            return 0;
        }

        if ($dryRun) {
            foreach ($suggestions as $suggestion) {
                $this->line(
                    sprintf(
                        '#%d %s:%d %s %s',
                        (int) $suggestion->id,
                        class_basename((string) $suggestion->imageable_type),
                        (int) $suggestion->imageable_id,
                        (string) $suggestion->locale,
                        (string) $suggestion->image_path
                    )
                );
            }

            return 0;
        }

        if (!$sync) {
            foreach ($suggestions as $suggestion) {
                $this->dispatcher->dispatch(
                    new GenerateImageAltJob(
                        (int) $suggestion->id,
                        $force
                    )
                );
            }

            $this->info(
                'Queued image alt generation jobs: ' .
                $suggestions->count()
            );

            return 0;
        }

        $failed = 0;

        foreach ($suggestions as $suggestion) {
            $this->line(
                sprintf(
                    'Processing suggestion #%d: %s',
                    (int) $suggestion->id,
                    (string) $suggestion->image_path
                )
            );

            $job = new GenerateImageAltJob(
                (int) $suggestion->id,
                $force
            );

            if (!$this->runSyncJob($job, $suggestion)) {
                $failed++;
            }

            $this->line('');
        }

        if ($failed > 0) {
            $this->error(
                sprintf(
                    'Finished with errors: %d of %d failed.',
                    $failed,
                    $suggestions->count()
                )
            );

            return 1;
        }

        $this->info(
            'Processed image alt generation jobs: ' .
            $suggestions->count()
        );

        return 0;
    }

    /**
     * Load valid suggestions until the requested limit is reached.
     *
     * Broken local references are skipped silently and do not consume --limit.
     *
     * @param mixed $query
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    private function loadValidSuggestions(
        $query,
        int $limit
    ): Collection {
        $suggestions = new Collection();
        $lastId = 0;
        $batchSize = max(
            100,
            min(
                500,
                $limit * 3
            )
        );

        while ($suggestions->count() < $limit) {
            $batchQuery = clone $query;

            /** @var \Illuminate\Support\Collection<int, \App\Models\ImageAltSuggestion> $batch */
            $batch = $batchQuery
                ->where('id', '>', $lastId)
                ->limit($batchSize)
                ->get();

            if ($batch->isEmpty()) {
                break;
            }

            foreach ($batch as $suggestion) {
                $lastId = max(
                    $lastId,
                    (int) $suggestion->id
                );

                if (
                    $this->shouldSkipImageReference(
                        (string) $suggestion->image_path
                    )
                ) {
                    continue;
                }

                $suggestions->push($suggestion);

                if ($suggestions->count() >= $limit) {
                    break;
                }
            }

            if ($batch->count() < $batchSize) {
                break;
            }
        }

        return $suggestions;
    }

    /**
     * Skip clearly unusable local/browser image references.
     *
     * Nothing is printed to the console for skipped rows.
     *
     * @param string $path
     * @return bool
     */
    private function shouldSkipImageReference(
        string $path
    ): bool {
        $path = trim($path);

        if ($path === '') {
            return true;
        }

        $normalized = rawurldecode($path);
        $lower = strtolower($normalized);

        /*
         * Examples:
         * file:/C:/Users/...
         * file:///C:/Users/...
         * file%3A%2FC%3A%2FUsers%2F...
         */
        if (
            strpos($lower, 'file:') === 0 ||
            strpos($lower, '/file:') !== false
        ) {
            return true;
        }

        /*
         * Plain Windows paths:
         * C:\Users\...
         * C:/Users/...
         */
        if (
            preg_match(
                '~^[a-z]:[\\\\/]~i',
                $normalized
            ) === 1
        ) {
            return true;
        }

        /*
         * Temporary paths commonly pasted from Microsoft Office.
         */
        if (
            strpos($lower, 'msohtmlclip') !== false ||
            strpos($lower, '/appdata/local/temp/') !== false ||
            strpos($lower, '\\appdata\\local\\temp\\') !== false
        ) {
            return true;
        }

        if (
            strpos($lower, 'blob:') === 0 ||
            strpos($lower, 'cid:') === 0
        ) {
            return true;
        }

        return false;
    }

    /**
     * Run one job and print all error information available to this command.
     *
     * @param \App\Jobs\GenerateImageAltJob $job
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @return bool
     */
    private function runSyncJob(
        GenerateImageAltJob $job,
        ImageAltSuggestion $suggestion
    ): bool {
        try {
            $job->handle(
                $this->generator,
                $this->skippedLogger,
                $this->localeResolver,
                $this->config
            );
        } catch (Throwable $exception) {
            $suggestion->setStatus(
                ImageAltSuggestion::STATUS_FAILED
            );

            $suggestion->error = $exception->getMessage();
            $suggestion->save();

            $this->printThrowable(
                $exception
            );

            return false;
        }

        /*
         * The job may catch the API exception internally and only save the
         * result to the database. Refresh so that this command still reports it.
         */
        $suggestion->refresh();

        if (
            (string) $suggestion->status ===
            ImageAltSuggestion::STATUS_FAILED
        ) {
            $this->error(
                sprintf(
                    'Suggestion #%d failed.',
                    (int) $suggestion->id
                )
            );

            $this->line(
                'Stored error: ' .
                (
                trim((string) $suggestion->error) !== ''
                    ? (string) $suggestion->error
                    : '[empty]'
                )
            );

            $this->line(
                'No exception reached the command. ' .
                'If the stored error contains only an HTTP status, ' .
                'the response body was discarded inside GenerateImageAltJob or AltGenerator.'
            );

            return false;
        }

        $this->info(
            sprintf(
                'Suggestion #%d completed with status: %s',
                (int) $suggestion->id,
                (string) $suggestion->status
            )
        );

        return true;
    }

    /**
     * Print the complete exception chain, request and response information
     * available from the thrown exception.
     *
     * @param \Throwable $exception
     * @return void
     */
    private function printThrowable(
        Throwable $exception
    ): void {
        $this->error('Exception details');

        $current = $exception;
        $level = 1;

        while ($current !== null) {
            $this->line(
                str_repeat('=', 80)
            );

            $this->line(
                'Exception level: ' . $level
            );

            $this->line(
                'Class: ' . get_class($current)
            );

            $this->line(
                'Code: ' . (string) $current->getCode()
            );

            $this->line(
                'Message: ' . $current->getMessage()
            );

            $this->line(
                sprintf(
                    'Location: %s:%d',
                    $current->getFile(),
                    $current->getLine()
                )
            );

            $request = $this->extractRequest(
                $current
            );

            if ($request !== null) {
                $this->printRequest(
                    $request
                );
            }

            $response = $this->extractResponse(
                $current
            );

            if ($response !== null) {
                $this->printResponse(
                    $response
                );
            }

            $this->line('Stack trace:');
            $this->line(
                $current->getTraceAsString()
            );

            $current = $current->getPrevious();
            $level++;
        }

        $this->line(
            str_repeat('=', 80)
        );
    }

    /**
     * @param \Throwable $exception
     * @return mixed|null
     */
    private function extractRequest(
        Throwable $exception
    ) {
        if (method_exists($exception, 'getRequest')) {
            try {
                return $exception->getRequest();
            } catch (Throwable $ignored) {
                // Try a public property below.
            }
        }

        try {
            return isset($exception->request)
                ? $exception->request
                : null;
        } catch (Throwable $ignored) {
            return null;
        }
    }

    /**
     * @param \Throwable $exception
     * @return mixed|null
     */
    private function extractResponse(
        Throwable $exception
    ) {
        if (method_exists($exception, 'getResponse')) {
            try {
                return $exception->getResponse();
            } catch (Throwable $ignored) {
                // Try a public property below.
            }
        }

        try {
            return isset($exception->response)
                ? $exception->response
                : null;
        } catch (Throwable $ignored) {
            return null;
        }
    }

    /**
     * @param mixed $request
     * @return void
     */
    private function printRequest(
        $request
    ): void {
        if (!is_object($request)) {
            return;
        }

        $this->line('HTTP request:');

        $method = $this->callMethod(
            $request,
            [
                'getMethod',
                'method',
            ]
        );

        $uri = $this->callMethod(
            $request,
            [
                'getUri',
                'url',
            ]
        );

        $headers = $this->callMethod(
            $request,
            [
                'getHeaders',
                'headers',
            ]
        );

        if ($method !== null) {
            $this->line(
                '  Method: ' . (string) $method
            );
        }

        if ($uri !== null) {
            $this->line(
                '  URL: ' . (string) $uri
            );
        }

        if (is_array($headers)) {
            $this->line('  Headers:');

            foreach ($headers as $name => $value) {
                $this->line(
                    '    ' .
                    (string) $name .
                    ': ' .
                    $this->formatHeaderValue(
                        (string) $name,
                        $value
                    )
                );
            }
        }

        $body = $this->callMethod(
            $request,
            [
                'getBody',
                'body',
            ]
        );

        if ($body !== null) {
            $this->line('  Body:');
            $this->line(
                $this->stringifyBody($body)
            );
        }
    }

    /**
     * @param mixed $response
     * @return void
     */
    private function printResponse(
        $response
    ): void {
        if (!is_object($response)) {
            return;
        }

        $this->line('HTTP response:');

        $status = $this->callMethod(
            $response,
            [
                'getStatusCode',
                'status',
            ]
        );

        $reason = $this->callMethod(
            $response,
            [
                'getReasonPhrase',
            ]
        );

        $headers = $this->callMethod(
            $response,
            [
                'getHeaders',
                'headers',
            ]
        );

        if ($status !== null) {
            $this->line(
                '  Status: ' .
                (string) $status .
                (
                $reason !== null && (string) $reason !== ''
                    ? ' ' . (string) $reason
                    : ''
                )
            );
        }

        if (is_array($headers)) {
            $this->line('  Headers:');

            foreach ($headers as $name => $value) {
                $this->line(
                    '    ' .
                    (string) $name .
                    ': ' .
                    $this->formatHeaderValue(
                        (string) $name,
                        $value
                    )
                );
            }
        }

        $body = $this->callMethod(
            $response,
            [
                'getBody',
                'body',
            ]
        );

        if ($body !== null) {
            $this->line('  Body:');
            $this->line(
                $this->stringifyBody($body)
            );
        }
    }

    /**
     * @param object $object
     * @param array<int, string> $methods
     * @return mixed|null
     */
    private function callMethod(
        $object,
        array $methods
    ) {
        foreach ($methods as $method) {
            if (!method_exists($object, $method)) {
                continue;
            }

            try {
                return $object->{$method}();
            } catch (Throwable $ignored) {
                // Try the next compatible method.
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return string
     */
    private function formatHeaderValue(
        string $name,
               $value
    ): string {
        $normalized = strtolower($name);

        if (
            in_array(
                $normalized,
                [
                    'authorization',
                    'api-key',
                    'x-api-key',
                    'openai-api-key',
                    'cookie',
                    'set-cookie',
                ],
                true
            )
        ) {
            return '[REDACTED]';
        }

        if (is_array($value)) {
            return implode(
                ', ',
                array_map(
                    static function ($item): string {
                        return (string) $item;
                    },
                    $value
                )
            );
        }

        return (string) $value;
    }

    /**
     * @param mixed $body
     * @return string
     */
    private function stringifyBody(
        $body
    ): string {
        try {
            if (is_string($body)) {
                return $body;
            }

            if (is_scalar($body)) {
                return (string) $body;
            }

            if (
                is_object($body) &&
                method_exists($body, '__toString')
            ) {
                return (string) $body;
            }

            $encoded = json_encode(
                $body,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE
            );

            return $encoded === false
                ? '[unable to serialize body]'
                : $encoded;
        } catch (Throwable $exception) {
            return '[unable to read body: ' .
                $exception->getMessage() .
                ']';
        }
    }

    /**
     * @param string $statusOption
     * @return array<int, string>
     */
    private function statusesFromOption(
        string $statusOption
    ): array {
        $statuses = array_values(
            array_filter(
                array_map(
                    'trim',
                    explode(
                        ',',
                        $statusOption
                    )
                )
            )
        );

        return empty($statuses)
            ? [ImageAltSuggestion::STATUS_NEW]
            : $statuses;
    }

    /**
     * @param string $model
     * @return string
     */
    private function resolveModelOption(
        string $model
    ): string {
        if (
            $model === '' ||
            class_exists($model)
        ) {
            return $model;
        }

        foreach (
            array_keys(
                (array) $this->config->get(
                    'alt_generation.targets',
                    []
                )
            ) as $class
        ) {
            if (
                is_string($class) &&
                class_basename($class) === $model
            ) {
                return $class;
            }
        }

        return $model;
    }
}
