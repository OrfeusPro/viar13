<?php

namespace App\Http\Controllers\Voyager;

use App\Models\SeoMetaSuggestion;
use App\Services\SeoMetaGeneration\SeoMetaContextResolver;
use App\Services\SeoMetaGeneration\SeoMetaGenerator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;

class SeoMetaSuggestionController extends VoyagerBaseController
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
     * @var \App\Services\SeoMetaGeneration\SeoMetaGenerator
     */
    private $generator;

    /**
     * @var \App\Services\SeoMetaGeneration\SeoMetaContextResolver
     */
    private $contextResolver;

    public function __construct(SeoMetaGenerator $generator, SeoMetaContextResolver $contextResolver)
    {
        $this->generator = $generator;
        $this->contextResolver = $contextResolver;
    }

    public function index(Request $request): View
    {
        $this->authorizeSeoMetaSuggestions('browse');

        $query = SeoMetaSuggestion::query()
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc');

        $status = (string) $request->query('status', '');
        if (in_array($status, $this->statuses, true)) {
            $query->where('status', $status);
        }

        $metaableType = (string) $request->query('metaable_type', '');
        if ($metaableType !== '') {
            $query->where('metaable_type', $metaableType);
        }

        $locale = trim((string) $request->query('locale', ''));
        if ($locale !== '' && in_array($locale, $this->contextResolver->supportedLocales(), true)) {
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

        $missing = (string) $request->query('missing', '');
        if ($missing === 'title') {
            $query->where(function ($nested): void {
                $nested->whereNull('current_meta_title')->orWhere('current_meta_title', '');
            });
        } elseif ($missing === 'description') {
            $query->where(function ($nested): void {
                $nested->whereNull('current_meta_description')->orWhere('current_meta_description', '');
            });
        } elseif ($missing === 'any') {
            $query->where(function ($nested): void {
                $nested->whereNull('current_meta_title')
                    ->orWhere('current_meta_title', '')
                    ->orWhereNull('current_meta_description')
                    ->orWhere('current_meta_description', '');
            });
        } elseif ($missing === 'both') {
            $query->where(function ($nested): void {
                $nested->where(function ($title): void {
                    $title->whereNull('current_meta_title')->orWhere('current_meta_title', '');
                })->where(function ($description): void {
                    $description->whereNull('current_meta_description')->orWhere('current_meta_description', '');
                });
            });
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($nested) use ($search): void {
                $nested->where('page_url', 'like', '%' . $search . '%')
                    ->orWhere('entity_title', 'like', '%' . $search . '%')
                    ->orWhere('entity_label', 'like', '%' . $search . '%')
                    ->orWhere('current_meta_title', 'like', '%' . $search . '%')
                    ->orWhere('current_meta_description', 'like', '%' . $search . '%')
                    ->orWhere('suggested_meta_title', 'like', '%' . $search . '%')
                    ->orWhere('suggested_meta_description', 'like', '%' . $search . '%')
                    ->orWhere('approved_meta_title', 'like', '%' . $search . '%')
                    ->orWhere('approved_meta_description', 'like', '%' . $search . '%')
                    ->orWhere('seo_keywords', 'like', '%' . $search . '%')
                    ->orWhere('error', 'like', '%' . $search . '%');
            });
        }

        $suggestions = $query->paginate(25)->appends($request->query());
        $dataType = Voyager::model('DataType')->where('slug', 'seo-meta-suggestions')->first();

        return view('voyager::seo-meta-suggestions.browse', [
            'dataType' => $dataType,
            'suggestions' => $suggestions,
            'statuses' => $this->statuses,
            'statusCounts' => $this->statusCounts(),
            'summaryCounts' => $this->summaryCounts(),
            'locales' => $this->contextResolver->supportedLocales(),
            'metaableTypes' => $this->configuredMetaableTypes(),
            'filters' => [
                'status' => $status,
                'metaable_type' => $metaableType,
                'locale' => $locale,
                'page_url' => $pageUrl,
                'has_error' => $hasError,
                'missing' => $missing,
                'search' => $search,
            ],
        ]);
    }

    public function scan(Request $request): RedirectResponse
    {
        $this->authorizeSeoMetaSuggestions('edit');

        $params = [
            '--limit' => (int) $request->input('limit', 1000),
        ];

        foreach (['model', 'locale', 'locales'] as $key) {
            $value = trim((string) $request->input($key, ''));

            if ($value !== '') {
                $params['--' . $key] = $value;
            }
        }

        if ($request->boolean('only_empty')) {
            $params['--only-empty'] = true;
        }

        if ($request->boolean('force')) {
            $params['--force'] = true;
        }

        Artisan::call('seo-meta:scan', $params);

        return redirect()
            ->route('voyager.seo-meta-suggestions.index', $request->query())
            ->with(['message' => trim(Artisan::output()) ?: 'SEO meta scan completed.', 'alert-type' => 'success']);
    }

    public function generate(Request $request, int $id)
    {
        $this->authorizeSeoMetaSuggestions('edit');

        $this->validateKeywords($request);

        $suggestion = SeoMetaSuggestion::query()->findOrFail($id);
        $this->updateKeywordsFromRequest($request, $suggestion);
        $this->generateSuggestion($suggestion, true);

        if ($suggestion->status === SeoMetaSuggestion::STATUS_FAILED) {
            return $this->backWithError($request, $suggestion->error ?: 'SEO meta generation failed.');
        }

        return $this->backWithMessage($request, 'SEO meta generated.');
    }

    public function approve(Request $request, int $id)
    {
        $this->authorizeSeoMetaSuggestions('edit');

        $limits = (array) config('seo_meta_generation.limits', []);
        $request->validate([
            'meta_title' => 'nullable|string|max:' . (int) ($limits['meta_title_max'] ?? 60),
            'meta_description' => 'nullable|string|max:' . (int) ($limits['meta_description_max'] ?? 155),
            'seo_keywords' => 'nullable|string|max:1000',
        ]);

        $suggestion = SeoMetaSuggestion::query()->findOrFail($id);
        $this->updateKeywordsFromRequest($request, $suggestion);

        $title = $request->has('meta_title')
            ? (string) $request->input('meta_title')
            : (string) $suggestion->suggested_meta_title;
        $description = $request->has('meta_description')
            ? (string) $request->input('meta_description')
            : (string) $suggestion->suggested_meta_description;

        if (trim($title) === '' && trim($description) === '') {
            $suggestion->error = 'Nothing to approve. Generate SEO meta first.';
            $suggestion->save();

            return $this->backWithError($request, $suggestion->error);
        }

        $suggestion->approved_meta_title = $title;
        $suggestion->approved_meta_description = $description;
        $suggestion->reviewed_by = auth()->id();
        $suggestion->reviewed_at = Carbon::now();
        $suggestion->error = null;
        $suggestion->setStatus(SeoMetaSuggestion::STATUS_APPROVED);
        $suggestion->save();

        return $this->backWithMessage($request, 'SEO meta approved.');
    }

    public function reject(Request $request, int $id)
    {
        $this->authorizeSeoMetaSuggestions('edit');

        $suggestion = SeoMetaSuggestion::query()->findOrFail($id);
        $this->updateKeywordsFromRequest($request, $suggestion);
        $suggestion->reviewed_by = auth()->id();
        $suggestion->reviewed_at = Carbon::now();
        $suggestion->setStatus(SeoMetaSuggestion::STATUS_REJECTED);
        $suggestion->save();

        return $this->backWithMessage($request, 'SEO meta rejected.');
    }

    public function apply(Request $request, int $id)
    {
        $this->authorizeSeoMetaSuggestions('edit');

        $suggestion = SeoMetaSuggestion::query()->findOrFail($id);
        $this->updateKeywordsFromRequest($request, $suggestion);

        if (!$this->canApplySuggestion($suggestion)) {
            $message = 'Apply is available only after approval.';
            $suggestion->error = $message;
            $suggestion->save();

            return $this->backWithError($request, $message);
        }

        try {
            $this->applySuggestion($suggestion);
        } catch (\Throwable $exception) {
            $suggestion->error = $exception->getMessage();
            $suggestion->save();

            return $this->backWithError($request, $exception->getMessage());
        }

        return $this->backWithMessage($request, 'SEO meta applied.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $this->authorizeSeoMetaSuggestions('edit');

        $request->validate([
            'action' => 'required|in:generate,approve,reject,apply',
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $action = (string) $request->input('action');
        $suggestions = SeoMetaSuggestion::query()
            ->whereIn('id', (array) $request->input('ids', []))
            ->get();
        $count = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($suggestions as $suggestion) {
            try {
                if ($action === 'generate') {
                    $this->generateSuggestion($suggestion, true);
                    $count++;
                } elseif ($action === 'approve') {
                    if (!$this->canApproveSuggestion($suggestion)) {
                        $skipped++;
                        continue;
                    }

                    $suggestion->approved_meta_title = $suggestion->suggested_meta_title;
                    $suggestion->approved_meta_description = $suggestion->suggested_meta_description;
                    $suggestion->reviewed_by = auth()->id();
                    $suggestion->reviewed_at = Carbon::now();
                    $suggestion->error = null;
                    $suggestion->setStatus(SeoMetaSuggestion::STATUS_APPROVED);
                    $suggestion->save();
                    $count++;
                } elseif ($action === 'reject') {
                    if (!$this->canRejectSuggestion($suggestion)) {
                        $skipped++;
                        continue;
                    }

                    $suggestion->reviewed_by = auth()->id();
                    $suggestion->reviewed_at = Carbon::now();
                    $suggestion->setStatus(SeoMetaSuggestion::STATUS_REJECTED);
                    $suggestion->save();
                    $count++;
                } elseif ($action === 'apply') {
                    if (!$this->canApplySuggestion($suggestion)) {
                        $skipped++;
                        continue;
                    }

                    $this->applySuggestion($suggestion);
                    $count++;
                }
            } catch (\Throwable $exception) {
                $failed++;
                $suggestion->error = $exception->getMessage();
                $suggestion->save();
            }
        }

        $message = 'Bulk action completed: ' . $count;

        if ($skipped > 0) {
            $message .= ', skipped: ' . $skipped;
        }

        if ($failed > 0) {
            $message .= ', failed: ' . $failed;
        }

        return redirect()
            ->back()
            ->with(['message' => $message, 'alert-type' => $failed > 0 ? 'warning' : 'success']);
    }

    private function generateSuggestion(SeoMetaSuggestion $suggestion, bool $noCache): SeoMetaSuggestion
    {
        $entity = $this->entityFromSuggestion($suggestion);
        $target = $this->targetForSuggestion($suggestion);

        if (!$entity instanceof Model || $target === null) {
            $suggestion->error = 'Source entity or SEO target configuration is missing.';
            $suggestion->setStatus(SeoMetaSuggestion::STATUS_FAILED);
            $suggestion->save();

            return $suggestion;
        }

        try {
            $this->refreshSuggestionFromEntity($suggestion, $entity, $target);
            $result = $this->generator->generate($entity, [(string) $suggestion->locale], !$noCache, [
                'keywords' => $suggestion->seo_keywords,
            ]);
            $values = $result['locales'][(string) $suggestion->locale] ?? null;

            if (!is_array($values)) {
                throw new \RuntimeException('OpenAI response did not include locale: ' . (string) $suggestion->locale);
            }

            $suggestion->suggested_meta_title = (string) ($values['meta_title'] ?? '');
            $suggestion->suggested_meta_description = (string) ($values['meta_description'] ?? '');
            $suggestion->approved_meta_title = null;
            $suggestion->approved_meta_description = null;
            $suggestion->prompt_context = [
                'context' => $result['context'] ?? [],
                'context_json' => $result['context_json'] ?? null,
                'limits' => config('seo_meta_generation.limits', []),
            ];
            $suggestion->model = isset($result['model']) ? (string) $result['model'] : null;
            $suggestion->tokens_used = isset($result['tokens']) ? (int) $result['tokens'] : null;
            $suggestion->error = null;
            $suggestion->generated_at = Carbon::now();
            $suggestion->setStatus(SeoMetaSuggestion::STATUS_PENDING);
            $suggestion->save();
        } catch (\Throwable $exception) {
            $suggestion->error = $exception->getMessage();
            $suggestion->setStatus(SeoMetaSuggestion::STATUS_FAILED);
            $suggestion->save();
        }

        return $suggestion;
    }

    private function applySuggestion(SeoMetaSuggestion $suggestion): void
    {
        $entity = $this->entityFromSuggestion($suggestion);
        $target = $this->targetForSuggestion($suggestion);

        if (!$entity instanceof Model || $target === null) {
            throw new \RuntimeException('Source entity or SEO target configuration is missing.');
        }

        $title = $suggestion->approved_meta_title ?: $suggestion->suggested_meta_title;
        $description = $suggestion->approved_meta_description ?: $suggestion->suggested_meta_description;

        if (trim((string) $title) === '' && trim((string) $description) === '') {
            throw new \RuntimeException('Generate or approve SEO meta first.');
        }

        DB::transaction(function () use ($entity, $target, $suggestion, $title, $description): void {
            if (trim((string) $title) !== '') {
                $this->writeTranslatedValue($entity, (string) $target['title_field'], (string) $title, (string) $suggestion->locale);
            }

            if (trim((string) $description) !== '') {
                $this->writeTranslatedValue($entity, (string) $target['description_field'], (string) $description, (string) $suggestion->locale);
            }
        });

        $suggestion->approved_meta_title = $title;
        $suggestion->approved_meta_description = $description;
        $suggestion->current_meta_title = $title;
        $suggestion->current_meta_description = $description;
        $suggestion->applied_by = auth()->id();
        $suggestion->applied_at = Carbon::now();
        $suggestion->error = null;
        $suggestion->setStatus(SeoMetaSuggestion::STATUS_APPLIED);
        $suggestion->save();
    }

    private function refreshSuggestionFromEntity(SeoMetaSuggestion $suggestion, Model $entity, array $target): void
    {
        $context = $this->contextResolver->resolve($entity, [(string) $suggestion->locale]);
        $localeContext = isset($context['locales'][$suggestion->locale]) && is_array($context['locales'][$suggestion->locale])
            ? $context['locales'][$suggestion->locale]
            : [];
        $entityContext = isset($context['entity']) && is_array($context['entity']) ? $context['entity'] : [];

        $suggestion->entity_label = (string) ($target['label'] ?? class_basename($entity));
        $suggestion->entity_title = isset($localeContext['source_title']) ? (string) $localeContext['source_title'] : (isset($localeContext['current_meta_title']) ? (string) $localeContext['current_meta_title'] : null);
        $suggestion->page_url = isset($localeContext['page_url']) ? (string) $localeContext['page_url'] : (isset($entityContext['url']) ? (string) $entityContext['url'] : null);
        $suggestion->title_field = (string) $target['title_field'];
        $suggestion->description_field = (string) $target['description_field'];
        $suggestion->current_meta_title = $this->readTranslatedValue($entity, (string) $target['title_field'], (string) $suggestion->locale);
        $suggestion->current_meta_description = $this->readTranslatedValue($entity, (string) $target['description_field'], (string) $suggestion->locale);
    }

    private function entityFromSuggestion(SeoMetaSuggestion $suggestion): ?Model
    {
        $class = (string) $suggestion->metaable_type;

        if ($class === '' || !class_exists($class) || !is_subclass_of($class, Model::class)) {
            return null;
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $class */
        return $class::query()->find((int) $suggestion->metaable_id);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function targetForSuggestion(SeoMetaSuggestion $suggestion): ?array
    {
        return $this->contextResolver->targetForModel((string) $suggestion->metaable_type);
    }

    private function readTranslatedValue(Model $entity, string $field, string $locale): ?string
    {
        if (method_exists($entity, 'getTranslatedAttribute')) {
            try {
                $value = $entity->getTranslatedAttribute($field, $locale, false);

                return $value === null ? null : (string) $value;
            } catch (\Throwable $exception) {
            }
        }

        if (!array_key_exists($field, $entity->getAttributes())) {
            return null;
        }

        $value = $entity->getAttribute($field);

        return $value === null ? null : (string) $value;
    }

    private function writeTranslatedValue(Model $entity, string $field, string $value, string $locale): void
    {
        if (method_exists($entity, 'setAttributeTranslations')) {
            $entity->setAttributeTranslations($field, [$locale => $value], true);
            $entity->save();
            $entity->load('translations');

            return;
        }

        $entity->setAttribute($field, $value);
        $entity->save();
    }

    /**
     * @return array<string, int>
     */
    private function statusCounts(): array
    {
        $counts = array_fill_keys($this->statuses, 0);
        $rows = SeoMetaSuggestion::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        foreach ($rows as $status => $total) {
            if (isset($counts[$status])) {
                $counts[$status] = (int) $total;
            }
        }

        return $counts;
    }

    /**
     * @return array<string, int>
     */
    private function summaryCounts(): array
    {
        return [
            'total' => (int) SeoMetaSuggestion::query()->count(),
            'missing_title' => (int) SeoMetaSuggestion::query()
                ->where(function ($query): void {
                    $query->whereNull('current_meta_title')->orWhere('current_meta_title', '');
                })->count(),
            'missing_description' => (int) SeoMetaSuggestion::query()
                ->where(function ($query): void {
                    $query->whereNull('current_meta_description')->orWhere('current_meta_description', '');
                })->count(),
            'missing_any' => (int) SeoMetaSuggestion::query()
                ->where(function ($query): void {
                    $query->whereNull('current_meta_title')
                        ->orWhere('current_meta_title', '')
                        ->orWhereNull('current_meta_description')
                        ->orWhere('current_meta_description', '');
                })->count(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function configuredMetaableTypes(): array
    {
        $types = [];

        foreach ((array) config('seo_meta_generation.targets', []) as $class => $target) {
            if (!is_string($class) || !is_array($target)) {
                continue;
            }

            $types[$class] = (string) ($target['label'] ?? class_basename($class));
        }

        return $types;
    }

    private function validateKeywords(Request $request): void
    {
        $request->validate([
            'seo_keywords' => 'nullable|string|max:1000',
        ]);
    }

    private function updateKeywordsFromRequest(Request $request, SeoMetaSuggestion $suggestion): void
    {
        if (!$request->has('seo_keywords')) {
            return;
        }

        $keywords = trim((string) $request->input('seo_keywords'));
        $suggestion->seo_keywords = $keywords === '' ? null : $keywords;
        $suggestion->save();
    }

    private function canApproveSuggestion(SeoMetaSuggestion $suggestion): bool
    {
        if (!in_array((string) $suggestion->status, [SeoMetaSuggestion::STATUS_PENDING, SeoMetaSuggestion::STATUS_GENERATED], true)) {
            return false;
        }

        return trim((string) $suggestion->suggested_meta_title) !== ''
            || trim((string) $suggestion->suggested_meta_description) !== '';
    }

    private function canApplySuggestion(SeoMetaSuggestion $suggestion): bool
    {
        if ((string) $suggestion->status !== SeoMetaSuggestion::STATUS_APPROVED) {
            return false;
        }

        return trim((string) $suggestion->approved_meta_title) !== ''
            || trim((string) $suggestion->approved_meta_description) !== '';
    }

    private function canRejectSuggestion(SeoMetaSuggestion $suggestion): bool
    {
        return in_array((string) $suggestion->status, [
            SeoMetaSuggestion::STATUS_PENDING,
            SeoMetaSuggestion::STATUS_GENERATED,
            SeoMetaSuggestion::STATUS_APPROVED,
            SeoMetaSuggestion::STATUS_FAILED,
        ], true);
    }

    private function backWithError(Request $request, string $message): RedirectResponse
    {
        return redirect()
            ->back()
            ->with(['message' => $message, 'alert-type' => 'error']);
    }

    private function backWithMessage(Request $request, string $message): RedirectResponse
    {
        return redirect()
            ->back()
            ->with(['message' => $message, 'alert-type' => 'success']);
    }

    private function authorizeSeoMetaSuggestions(string $ability): void
    {
        $user = auth()->user();

        if (!$user || !method_exists($user, 'hasPermission')) {
            abort(403);
        }

        $dataType = Voyager::model('DataType')->where('slug', 'seo-meta-suggestions')->first();
        $tableName = $dataType ? $dataType->name : 'seo_meta_suggestions';
        $permission = $ability . '_' . $tableName;

        if (!$user->hasPermission($permission)) {
            abort(403);
        }
    }
}
