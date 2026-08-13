<?php

namespace App\Http\Controllers\Voyager;

use App\Jobs\GenerateImageAltJob;
use App\Models\ImageAltApplyLog;
use App\Models\ImageAltSuggestion;
use App\Services\AltGeneration\LocaleResolver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;

/**
 * Voyager moderation controller for generated image alt/title suggestions.
 */
class AltSuggestionController extends VoyagerBaseController
{
    /**
     * @var array<int, string>
     */
    private $statuses = [
        'new',
        'generated',
        'pending',
        'approved',
        'applied',
        'rejected',
        'failed',
    ];

    /**
     * Show the moderation queue.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $this->authorizeAltSuggestions('browse');

        $query = ImageAltSuggestion::query()
            ->withCount('applyLogs')
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc');
        /** @var \App\Services\AltGeneration\LocaleResolver $localeResolver */
        $localeResolver = app(LocaleResolver::class);
        $locales = $localeResolver->supportedLocales();

        $status = (string) $request->query('status', '');
        if (in_array($status, $this->statuses, true)) {
            $query->where('status', $status);
        }

        $imageableType = (string) $request->query('imageable_type', '');
        if ($imageableType !== '') {
            $query->where('imageable_type', $imageableType);
        }

        $locale = trim((string) $request->query('locale', ''));
        if ($locale !== '' && $localeResolver->isSupported($locale)) {
            $locale = $localeResolver->normalizeOrDefault($locale);
            $query->where('locale', $locale);
        }

        $pageUrl = trim((string) $request->query('page_url', ''));
        if ($pageUrl !== '') {
            $query->where('page_url', 'like', '%' . $pageUrl . '%');
        }

        $hasError = (string) $request->query('has_error', '');
        if ($hasError === '1') {
            $query->whereNotNull('error')->where('error', '<>', '');
        } elseif ($hasError === '0') {
            $query->where(function ($nested): void {
                $nested->whereNull('error')->orWhere('error', '');
            });
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $hasErrorMessage = Schema::hasColumn((new ImageAltSuggestion())->getTable(), 'error_message');

            $query->where(function ($nested) use ($search, $hasErrorMessage) {
                $nested->where('page_url', 'like', '%' . $search . '%')
                    ->orWhere('image_path', 'like', '%' . $search . '%')
                    ->orWhere('current_alt', 'like', '%' . $search . '%')
                    ->orWhere('current_title', 'like', '%' . $search . '%')
                    ->orWhere('suggested_alt', 'like', '%' . $search . '%')
                    ->orWhere('suggested_title', 'like', '%' . $search . '%')
                    ->orWhere('approved_alt', 'like', '%' . $search . '%')
                    ->orWhere('approved_title', 'like', '%' . $search . '%')
                    ->orWhere('error', 'like', '%' . $search . '%');

                if ($hasErrorMessage) {
                    $nested->orWhere('error_message', 'like', '%' . $search . '%');
                }
            });
        }

        $webp = (string) $request->query('webp', '');
        if (!in_array($webp, ['1', '0'], true)) {
            $webp = '';
        }

        if ($webp !== '') {
            $this->applyWebpFilterToQuery($query, $webp === '1');
        }

        $webpStats = $this->webpStatsForQuery(clone $query);

        $suggestions = $query->paginate(25)->appends($request->query());
        $webpByImagePath = $this->webpInfoForPaths(
            $suggestions->pluck('image_path')->filter()->unique()->values()->all()
        );

        $imageableTypes = ImageAltSuggestion::query()
            ->whereNotNull('imageable_type')
            ->select('imageable_type')
            ->distinct()
            ->orderBy('imageable_type')
            ->pluck('imageable_type');
        $statusCounts = $this->statusCounts();
        $dataType = Voyager::model('DataType')->where('slug', 'alt-suggestions')->first();

        return view('voyager::alt-suggestions.browse', [
            'dataType' => $dataType,
            'suggestions' => $suggestions,
            'statuses' => $this->statuses,
            'statusCounts' => $statusCounts,
            'webpStats' => $webpStats,
            'webpByImagePath' => $webpByImagePath,
            'locales' => $locales,
            'imageableTypes' => $imageableTypes,
            'filters' => [
                'status' => $status,
                'imageable_type' => $imageableType,
                'locale' => $locale,
                'page_url' => $pageUrl,
                'has_error' => $hasError,
                'webp' => $webp,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Approve an edited suggestion.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, int $id)
    {
        $this->authorizeAltSuggestions('edit');

        $limits = (array) config('alt_generation.limits', []);
        $request->validate([
            'alt' => 'nullable|string|max:' . (int) ($limits['alt_max'] ?? 125),
            'title' => 'nullable|string|max:' . (int) ($limits['title_max'] ?? 70),
        ]);

        $suggestion = ImageAltSuggestion::query()->findOrFail($id);
        $suggestion->approved_alt = $request->has('alt')
            ? $request->input('alt')
            : $suggestion->suggested_alt;
        $suggestion->approved_title = $request->has('title')
            ? $request->input('title')
            : $suggestion->suggested_title;
        $suggestion->setStatus(ImageAltSuggestion::STATUS_APPROVED);
        $suggestion->reviewed_by = auth()->id();
        $suggestion->reviewed_at = Carbon::now();
        $suggestion->save();

        return $this->respond($request, $suggestion, 'Suggestion approved.');
    }

    /**
     * Reject a suggestion.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, int $id)
    {
        $this->authorizeAltSuggestions('edit');

        $suggestion = ImageAltSuggestion::query()->findOrFail($id);
        $suggestion->setStatus(ImageAltSuggestion::STATUS_REJECTED);
        $suggestion->reviewed_by = auth()->id();
        $suggestion->reviewed_at = Carbon::now();
        $suggestion->save();

        return $this->respond($request, $suggestion, 'Suggestion rejected.');
    }

    /**
     * Queue regeneration for a suggestion.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function regenerate(Request $request, int $id)
    {
        $this->authorizeAltSuggestions('edit');

        $suggestion = ImageAltSuggestion::query()->findOrFail($id);

        if (!$this->canDispatch($suggestion)) {
            return $this->respondWithError($request, 'This suggestion is missing its source model.');
        }

        $suggestion->setStatus(ImageAltSuggestion::STATUS_NEW);
        $suggestion->error = null;
        $suggestion->save();

        GenerateImageAltJob::dispatch((int) $suggestion->id);

        return $this->respond($request, $suggestion, 'Regeneration queued.');
    }

    /**
     * Apply a bulk moderation action.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulk(Request $request): RedirectResponse
    {
        $this->authorizeAltSuggestions('edit');

        $request->validate([
            'action' => 'required|in:approve,reject,regenerate,apply',
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $action = (string) $request->input('action');
        $suggestions = ImageAltSuggestion::query()
            ->whereIn('id', (array) $request->input('ids', []))
            ->get();

        $count = 0;

        foreach ($suggestions as $suggestion) {
            if ($action === 'approve') {
                $this->approveSuggestion($suggestion);
                $count++;
                continue;
            }

            if ($action === 'reject') {
                $suggestion->setStatus(ImageAltSuggestion::STATUS_REJECTED);
                $suggestion->reviewed_by = auth()->id();
                $suggestion->reviewed_at = Carbon::now();
                $suggestion->save();
                $count++;
                continue;
            }

            if ($action === 'regenerate' && $this->canDispatch($suggestion)) {
                $suggestion->setStatus(ImageAltSuggestion::STATUS_NEW);
                $suggestion->error = null;
                $suggestion->save();

                GenerateImageAltJob::dispatch((int) $suggestion->id);
                $count++;
            }

            if ($action === 'apply') {
                continue;
            }
        }

        if ($action === 'apply') {
            $count = $this->applySuggestions((array) $request->input('ids', []));
        }

        return redirect()->back()->with([
            'message' => ucfirst($action) . ' processed for ' . $count . ' suggestion(s).',
            'alert-type' => 'success',
        ]);
    }

    /**
     * Revert writes made for one suggestion.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function revert(Request $request, int $id)
    {
        $this->authorizeAltSuggestions('edit');

        $suggestion = ImageAltSuggestion::query()->findOrFail($id);
        $logs = ImageAltApplyLog::query()
            ->where('suggestion_id', $suggestion->id)
            ->orderByDesc('id')
            ->get();

        if ($logs->isEmpty()) {
            if ($suggestion->status === ImageAltSuggestion::STATUS_APPLIED) {
                $suggestion->setStatus(ImageAltSuggestion::STATUS_APPROVED);
                $suggestion->applied_at = null;
                $suggestion->error = null;
                $suggestion->save();

                return $this->respond($request, $suggestion, 'Virtual applied values were reverted.');
            }

            return $this->respondWithError($request, 'No apply log entries were found for this suggestion.');
        }

        $entity = $this->resolveEntity($suggestion);

        if (!$entity) {
            return $this->respondWithError($request, 'This suggestion is missing its source model.');
        }

        /** @var \App\Services\AltGeneration\LocaleResolver $localeResolver */
        $localeResolver = app(LocaleResolver::class);

        DB::transaction(function () use ($entity, $suggestion, $logs, $localeResolver): void {
            foreach ($logs as $log) {
                if (!$log instanceof ImageAltApplyLog) {
                    continue;
                }

                $locale = $localeResolver->normalizeOrDefault($log->locale ?: $suggestion->locale);
                $field = (string) $log->field;
                $column = (string) $log->column_written;

                if ($this->isMediaApplyLog($log, $suggestion)) {
                    $this->writeMediaCustomProperty($entity, $suggestion, $log, $field, $column);

                    continue;
                }

                $preferTranslation = $field !== ''
                    && $field === $column
                    && $this->isConfiguredHtmlField($entity, $field);

                $this->writeEntityAttribute($entity, $column, $log->old_value, $locale, $preferTranslation);
            }

            $suggestion->setStatus(ImageAltSuggestion::STATUS_APPROVED);
            $suggestion->applied_at = null;
            $suggestion->error = null;
            $suggestion->save();
        });

        return $this->respond($request, $suggestion, 'Applied values were reverted.');
    }

    /**
     * @param \App\Models\ImageAltApplyLog $log
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @return bool
     */
    private function isMediaApplyLog(ImageAltApplyLog $log, ImageAltSuggestion $suggestion): bool
    {
        if ((string) $log->source_type === 'media') {
            return true;
        }

        $context = (array) $suggestion->prompt_context;
        $image = isset($context['image']) && is_array($context['image']) ? $context['image'] : [];

        return (string) ($image['source_type'] ?? '') === 'media' || strpos((string) $log->field, 'media:') === 0;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param \App\Models\ImageAltApplyLog $log
     * @param string $field
     * @param string $column
     * @return void
     */
    private function writeMediaCustomProperty(
        Model $entity,
        ImageAltSuggestion $suggestion,
        ImageAltApplyLog $log,
        string $field,
        string $column
    ): void {
        $context = (array) $suggestion->prompt_context;
        $image = isset($context['image']) && is_array($context['image']) ? $context['image'] : [];
        $collection = isset($image['collection_name']) && is_string($image['collection_name'])
            ? $image['collection_name']
            : null;

        if ($collection === null && strpos($field, 'media:') === 0) {
            $collection = substr($field, 6);
        }

        $mediaId = $log->media_id !== null
            ? (int) $log->media_id
            : (isset($image['media_id']) ? (int) $image['media_id'] : null);
        $media = $this->resolveMediaItem($entity, $collection, $mediaId, (string) $suggestion->image_path);

        if (!is_object($media) || !method_exists($media, 'setCustomProperty') || !method_exists($media, 'save')) {
            throw new \RuntimeException('MediaLibrary item for revert was not found or is not writable.');
        }

        if ($log->old_value === null && method_exists($media, 'forgetCustomProperty')) {
            $media->forgetCustomProperty($column);
        } else {
            $media->setCustomProperty($column, $log->old_value);
        }

        $media->save();
    }

    /**
     * @param \\Illuminate\\Database\\Eloquent\\Builder $query
     * @return array{total: int, with_webp: int, without_webp: int, coverage_percent: float}
     */
    private function webpStatsForQuery($query): array
    {
        $baseQuery = clone $query;
        $baseQuery->getQuery()->orders = null;
        $baseQuery->getQuery()->unionOrders = null;

        $paths = $baseQuery
            ->whereNotNull('image_path')
            ->where('image_path', '<>', '')
            ->select('image_path')
            ->groupBy('image_path')
            ->pluck('image_path')
            ->filter()
            ->values()
            ->all();

        $total = count($paths);
        $withWebp = 0;

        foreach ($this->webpInfoForPaths($paths) as $info) {
            if (!empty($info['exists'])) {
                $withWebp++;
            }
        }

        return [
            'total' => $total,
            'with_webp' => $withWebp,
            'without_webp' => max(0, $total - $withWebp),
            'coverage_percent' => $total > 0 ? round(($withWebp / $total) * 100, 1) : 0.0,
        ];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $mustHaveWebp
     * @return void
     */
    private function applyWebpFilterToQuery($query, bool $mustHaveWebp): void
    {
        $baseQuery = clone $query;
        $baseQuery->getQuery()->orders = null;
        $baseQuery->getQuery()->unionOrders = null;

        $paths = $baseQuery
            ->whereNotNull('image_path')
            ->where('image_path', '<>', '')
            ->select('image_path')
            ->groupBy('image_path')
            ->pluck('image_path')
            ->filter()
            ->values()
            ->all();

        $matchingPaths = [];

        foreach ($this->webpInfoForPaths($paths) as $path => $info) {
            $exists = !empty($info['exists']);

            if (($mustHaveWebp && $exists) || (!$mustHaveWebp && !$exists)) {
                $matchingPaths[] = $path;
            }
        }

        if (empty($matchingPaths)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('image_path', array_values(array_unique($matchingPaths)));
    }

    /**
     * @param array<int, mixed> $paths
     * @return array<string, array{exists: bool, path: string|null, url: string|null, original_path: string, original_is_webp: bool}>
     */
    private function webpInfoForPaths(array $paths): array
    {
        $result = [];

        foreach ($paths as $path) {
            $path = trim((string) $path);

            if ($path === '') {
                continue;
            }

            $result[$path] = $this->webpInfo($path);
        }

        return $result;
    }

    /**
     * @param string $imagePath
     * @return array{exists: bool, path: string|null, url: string|null, original_path: string, original_is_webp: bool}
     */
    private function webpInfo(string $imagePath): array
    {
        $originalPath = $imagePath;
        $storagePath = $this->normalizeStorageImagePath($imagePath);
        $webpPath = $this->webpPathFromStoragePath($storagePath);
        $exists = $webpPath !== null && Storage::disk('public')->exists($webpPath);

        return [
            'exists' => $exists,
            'path' => $webpPath,
            'url' => $webpPath !== null ? Storage::disk('public')->url($webpPath) : null,
            'original_path' => $originalPath,
            'original_is_webp' => strtolower((string) pathinfo($storagePath, PATHINFO_EXTENSION)) === 'webp',
        ];
    }

    /**
     * @param string|null $path
     * @return string|null
     */
    private function normalizeStorageImagePath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $path = trim(str_replace('\\\\', '/', $path));

        if ($path === '' || strpos($path, 'data:image/') === 0) {
            return null;
        }

        $parts = preg_split('/[?#]/', $path, 2);
        $path = $parts[0] ?? $path;

        if (preg_match('#^https?://#i', $path)) {
            $parsedPath = parse_url($path, PHP_URL_PATH);
            $path = is_string($parsedPath) ? $parsedPath : '';
        }

        $path = ltrim($path, '/');

        if (strpos($path, 'public/storage/') === 0) {
            $path = substr($path, 15);
        }

        if (strpos($path, 'storage/') === 0) {
            $path = substr($path, 8);
        }

        return trim($path) === '' ? null : $path;
    }

    /**
     * @param string|null $storagePath
     * @return string|null
     */
    private function webpPathFromStoragePath(?string $storagePath): ?string
    {
        if ($storagePath === null || trim($storagePath) === '') {
            return null;
        }

        $storagePath = trim(str_replace('\\\\', '/', $storagePath));
        $extension = pathinfo($storagePath, PATHINFO_EXTENSION);

        if ($extension === '') {
            return null;
        }

        if (strtolower($extension) === 'webp') {
            return $storagePath;
        }

        return preg_replace('/\\.[^\\.\\/]+$/', '.webp', $storagePath) ?: null;
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @return void
     */
    private function approveSuggestion(ImageAltSuggestion $suggestion): void
    {
        $suggestion->approved_alt = $suggestion->suggested_alt;
        $suggestion->approved_title = $suggestion->suggested_title;
        $suggestion->setStatus(ImageAltSuggestion::STATUS_APPROVED);
        $suggestion->reviewed_by = auth()->id();
        $suggestion->reviewed_at = Carbon::now();
        $suggestion->save();
    }

    /**
     * @param array<int, mixed> $ids
     * @return int
     */
    private function applySuggestions(array $ids): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            return 0;
        }

        Artisan::call('alt:apply', [
            '--ids' => implode(',', $ids),
            '--status' => ImageAltSuggestion::STATUS_APPROVED,
            '--source' => 'admin',
            '--applied-by' => auth()->id(),
            '--limit' => count($ids),
        ]);

        return ImageAltSuggestion::query()
            ->whereIn('id', $ids)
            ->where('status', ImageAltSuggestion::STATUS_APPLIED)
            ->count();
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @return bool
     */
    private function canDispatch(ImageAltSuggestion $suggestion): bool
    {
        return $suggestion->imageable_type !== null
            && $suggestion->imageable_id !== null
            && $suggestion->image_path !== '';
    }

    /**
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private function resolveEntity(ImageAltSuggestion $suggestion): ?Model
    {
        $class = (string) $suggestion->imageable_type;

        if (!class_exists($class) || !is_subclass_of($class, Model::class)) {
            return null;
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $class */
        $entity = $class::query()->find($suggestion->imageable_id);

        return $entity instanceof Model ? $entity : null;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param string $field
     * @param string|null $value
     * @param string $locale
     * @param bool $preferTranslation
     * @return bool
     */
    private function writeEntityAttribute(
        Model $entity,
        string $field,
        ?string $value,
        string $locale,
        bool $preferTranslation
    ): bool
    {
        if ($preferTranslation && $this->writeTranslatedAttribute($entity, $field, $value, $locale)) {
            return true;
        }

        if (array_key_exists($field, $entity->getAttributes())) {
            $entity->setAttribute($field, $value);
            $entity->save();

            return true;
        }

        return $this->writeTranslatedAttribute($entity, $field, $value, $locale);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param string $field
     * @param string|null $value
     * @param string $locale
     * @return bool
     */
    private function writeTranslatedAttribute(Model $entity, string $field, ?string $value, string $locale): bool
    {
        if (!method_exists($entity, 'translateOrNew')) {
            return false;
        }

        try {
            $translation = $entity->translateOrNew($locale);

            if (is_object($translation)) {
                $translation->{$field} = $value;

                if (method_exists($translation, 'save')) {
                    $translation->save();

                    return true;
                }
            }
        } catch (\Throwable $exception) {
            return false;
        }

        return false;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param string $field
     * @return bool
     */
    private function isConfiguredHtmlField(Model $entity, string $field): bool
    {
        $target = $this->targetForEntity($entity);
        $htmlFields = isset($target['html_fields']) && is_array($target['html_fields'])
            ? $target['html_fields']
            : [];

        return in_array($field, $htmlFields, true);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @return array<string, mixed>
     */
    private function targetForEntity(Model $entity): array
    {
        $targets = (array) config('alt_generation.targets', []);
        $entityClass = get_class($entity);

        if (isset($targets[$entityClass]) && is_array($targets[$entityClass])) {
            return $targets[$entityClass];
        }

        foreach ($targets as $class => $target) {
            if (is_string($class) && $entity instanceof $class && is_array($target)) {
                return $target;
            }
        }

        return [];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param string|null $collection
     * @param int|null $mediaId
     * @param string $imagePath
     * @return mixed|null
     */
    private function resolveMediaItem(Model $entity, ?string $collection, ?int $mediaId, string $imagePath)
    {
        if ($collection === null || $collection === '' || !method_exists($entity, 'getMedia')) {
            return null;
        }

        try {
            $items = $entity->getMedia($collection);
        } catch (\Throwable $exception) {
            return null;
        }

        if (!is_iterable($items)) {
            return null;
        }

        $normalizedImagePath = $this->normalizeImagePath($imagePath);

        foreach ($items as $media) {
            if (!is_object($media)) {
                continue;
            }

            if ($mediaId !== null && isset($media->id) && (int) $media->id === $mediaId) {
                return $media;
            }

            if (!method_exists($media, 'getUrl')) {
                continue;
            }

            try {
                $mediaPath = $this->normalizeImagePath((string) $media->getUrl());
            } catch (\Throwable $exception) {
                continue;
            }

            if ($mediaPath === $normalizedImagePath) {
                return $media;
            }
        }

        return null;
    }

    /**
     * @param string $path
     * @return string
     */
    private function normalizeImagePath(string $path): string
    {
        $path = trim(html_entity_decode($path, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $parts = preg_split('/[?#]/', $path, 2);
        $path = str_replace('\\', '/', $parts[0] ?? $path);

        if (preg_match('#^https?://#i', $path)) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        $path = ltrim($path, '/');

        return strpos($path, 'storage/') === 0 ? substr($path, 8) : $path;
    }

    /**
     * @return array<string, int>
     */
    private function statusCounts(): array
    {
        $counts = array_fill_keys($this->statuses, 0);
        $rows = ImageAltSuggestion::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        foreach ($rows as $status => $count) {
            if (array_key_exists((string) $status, $counts)) {
                $counts[(string) $status] = (int) $count;
            }
        }

        return $counts;
    }

    /**
     * @param string $action
     * @return void
     */
    private function authorizeAltSuggestions(string $action): void
    {
        $user = auth()->user();

        if (!$user || !method_exists($user, 'hasPermission')) {
            abort(403);
        }

        $allowed = $user->hasPermission($action . '_alt_suggestions')
            || $user->hasPermission($action . '_image_alt_suggestions');

        if (!$allowed) {
            abort(403);
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ImageAltSuggestion $suggestion
     * @param string $message
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    private function respond(Request $request, ImageAltSuggestion $suggestion, string $message)
    {
        if ($request->ajax()) {
            return new JsonResponse([
                'message' => $message,
                'suggestion' => [
                    'id' => $suggestion->id,
                    'status' => $suggestion->status,
                    'approved_alt' => $suggestion->approved_alt,
                    'approved_title' => $suggestion->approved_title,
                ],
            ]);
        }

        return redirect()->back()->with([
            'message' => $message,
            'alert-type' => 'success',
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string $message
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    private function respondWithError(Request $request, string $message)
    {
        if ($request->ajax()) {
            return new JsonResponse([
                'message' => $message,
            ], 422);
        }

        return redirect()->back()->with([
            'message' => $message,
            'alert-type' => 'error',
        ]);
    }
}
