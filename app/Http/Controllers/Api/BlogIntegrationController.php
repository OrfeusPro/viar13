<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogAuthor;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BlogIntegrationController extends Controller
{
    public function categories(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'updated_since' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $query = BlogCategory::query()->orderBy('id', 'asc');
        if ($request->query('updated_since')) {
            $query->where('updated_at', '>', Carbon::parse($request->query('updated_since')));
        }

        $categories = $query->get();
        $translations = $this->translationsFor('blog_categories', $categories->pluck('id')->all());

        return response()->json([
            'status' => 'ok',
            'meta' => [
                'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
                'locales' => $this->supportedLocales(),
                'pilot_contract' => 3, 'story_image' => true, 'alt_storage' => 'image_alt_suggestions',
                'count' => $categories->count(),
            ],
            'data' => [
                'categories' => $categories->map(function (BlogCategory $category) use ($translations) {
                    return [
                        'id' => (int) $category->id,
                        'created_at' => $this->isoDate($category->created_at),
                        'updated_at' => $this->isoDate($category->updated_at),
                        'translations' => $this->localizedPayload(
                            $category,
                            $translations[(int) $category->id] ?? [],
                            ['title', 'slug', 'meta_title', 'meta_desc', 'seo'],
                            'category'
                        ),
                    ];
                })->values()->all(),
            ],
        ], 200);
    }

    public function authors(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'updated_since' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $query = BlogAuthor::query()->orderBy('sort', 'asc')->orderBy('id', 'asc');
        if ($request->query('updated_since')) {
            $query->where('updated_at', '>', Carbon::parse($request->query('updated_since')));
        }

        $authors = $query->get();
        $translations = $this->translationsFor('blog_authors', $authors->pluck('id')->all());

        return response()->json([
            'status' => 'ok',
            'meta' => [
                'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
                'locales' => $this->supportedLocales(),
                'pilot_contract' => 3, 'story_image' => true, 'alt_storage' => 'image_alt_suggestions',
                'count' => $authors->count(),
            ],
            'data' => [
                'authors' => $authors->map(function (BlogAuthor $author) use ($translations) {
                    return [
                        'id' => (int) $author->id,
                        'name' => (string) $author->name,
                        'image' => $this->imageUrl($author->image),
                        'image_path' => $author->image,
                        'sort' => (int) ($author->sort ?? 0),
                        'created_at' => $this->isoDate($author->created_at),
                        'updated_at' => $this->isoDate($author->updated_at),
                        'translations' => $this->localizedPayload(
                            $author,
                            $translations[(int) $author->id] ?? [],
                            ['name'],
                            'author'
                        ),
                    ];
                })->values()->all(),
            ],
        ], 200);
    }

    public function storeAuthor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lang' => 'nullable|in:'.implode(',', $this->supportedLocales()),
            'name' => 'required_without:translations|string|max:255',
            'image' => 'nullable|string|max:2048',
            'sort' => 'nullable|integer|min:0',
            'translations' => 'nullable|array',
            'translations.*.name' => 'required_with:translations|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $defaultLocale = (string) $request->input('lang', $this->baseContentLocale());
        $translations = $this->normalizeIncomingAuthorTranslations($request, $defaultLocale);
        if (empty($translations)) {
            return $this->validationErrorFromDetails([
                [
                    'field' => 'translations',
                    'issue' => 'At least one author name is required',
                ],
            ]);
        }

        $duplicate = $this->findDuplicateAuthor($translations);
        if ($duplicate) {
            return response()->json([
                'status' => 'duplicate',
                'data' => [
                    'author' => $this->authorSummary($duplicate),
                ],
            ], 200);
        }

        $base = $translations[$defaultLocale] ?? reset($translations);

        $author = DB::transaction(function () use ($request, $translations, $base) {
            $author = new BlogAuthor;
            $author->name = $base['name'] ?? null;
            $author->image = $request->input('image');
            $author->sort = (int) $request->input('sort', 0);
            $author->save();

            foreach ($translations as $locale => $fields) {
                if (! array_key_exists('name', $fields)) {
                    continue;
                }

                $this->upsertTranslation('blog_authors', 'name', (int) $author->id, $locale, (string) $fields['name']);
            }

            return $author->fresh();
        });

        return response()->json([
            'status' => 'ok',
            'data' => [
                'author' => $this->authorSummary($author),
            ],
        ], 201);
    }

    public function posts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'updated_since' => 'nullable|date',
            'category_id' => 'nullable|integer|min:1',
            'include_text' => 'nullable|in:true,false,1,0',
            'include_drafts' => 'nullable|in:true,false,1,0',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $includeText = filter_var((string) $request->query('include_text', 'true'), FILTER_VALIDATE_BOOLEAN);
        $includeDrafts = filter_var((string) $request->query('include_drafts', 'true'), FILTER_VALIDATE_BOOLEAN);
        $perPage = (int) $request->query('per_page', 100);

        $query = BlogPost::query()
            ->with(['categoryes', 'author'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if (! $includeDrafts) {
            $query->published();
        }

        if ($request->query('updated_since')) {
            $query->where('updated_at', '>', Carbon::parse($request->query('updated_since')));
        }

        if ($request->query('category_id')) {
            $categoryId = (int) $request->query('category_id');
            $query->whereHas('categoryes', function ($query) use ($categoryId) {
                $query->where('blog_categories.id', $categoryId);
            });
        }

        $posts = $query->paginate($perPage);
        $postCollection = $posts->getCollection();
        $postTranslations = $this->translationsFor('blog_posts', $postCollection->pluck('id')->all());
        $categoryIds = $postCollection
            ->flatMap(function (BlogPost $post) {
                return $post->categoryes->pluck('id');
            })
            ->unique()
            ->values()
            ->all();
        $categoryTranslations = $this->translationsFor('blog_categories', $categoryIds);

        return response()->json([
            'status' => 'ok',
            'meta' => [
                'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
                'locales' => $this->supportedLocales(),
                'pilot_contract' => 3, 'story_image' => true, 'alt_storage' => 'image_alt_suggestions',
                'pagination' => [
                    'current_page' => $posts->currentPage(),
                    'per_page' => $posts->perPage(),
                    'total' => $posts->total(),
                    'last_page' => $posts->lastPage(),
                ],
            ],
            'data' => [
                'posts' => $postCollection->map(function (BlogPost $post) use ($postTranslations, $categoryTranslations, $includeText) {
                    return [
                        'id' => (int) $post->id,
                        'revision' => $this->revision($post),
                        'content_revision' => $this->revision($post, true),
                        'external_id' => $post->external_id,
                        'idempotency_key' => $post->idempotency_key,
                        'media_sha256' => $this->mediaHashes($post),
                        'story_alt_mode' => data_get(json_decode($post->api_payload ?: '{}', true), 'story_alt_mode'),
                        'image' => $this->imageUrl($post->image),
                        'image_path' => $post->image,
                        'image_preview' => $this->imageUrl($post->image_preview),
                        'image_preview_path' => $post->image_preview,
                        'image_user' => $this->imageUrl($post->image_user),
                        'image_user_path' => $post->image_user,
                        'status' => $this->postStatus($post),
                        'flags' => $this->postFlags($post),
                        'excerpt' => $post->excerpt ?? null,
                        'tags' => $this->decodeJsonArray($post->tags ?? null),
                        'author' => $post->author ? [
                            'id' => (int) $post->author->id,
                            'name' => (string) $post->author->name,
                        ] : null,
                        'category_ids' => $post->categoryes->pluck('id')->map(function ($id) {
                            return (int) $id;
                        })->values()->all(),
                        'categories' => $post->categoryes->map(function (BlogCategory $category) use ($categoryTranslations) {
                            return [
                                'id' => (int) $category->id,
                                'translations' => $this->localizedPayload(
                                    $category,
                                    $categoryTranslations[(int) $category->id] ?? [],
                                    ['title', 'slug'],
                                    'category'
                                ),
                            ];
                        })->values()->all(),
                        'created_at' => $this->isoDate($post->created_at),
                        'updated_at' => $this->isoDate($post->updated_at),
                        'translations' => $this->localizedPayload(
                            $post,
                            $postTranslations[(int) $post->id] ?? [],
                            $includeText
                                ? ['title', 'slug', 'meta_title', 'meta_desc', 'excerpt', 'text', 'image_alt', 'image_preview_alt', 'image_user_alt']
                                : ['title', 'slug', 'meta_title', 'meta_desc', 'excerpt'],
                            'post'
                        ),
                    ];
                })->values()->all(),
            ],
        ], 200);
    }

    public function storePost(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'idempotency_key' => 'nullable|string|max:255',
            'external_id' => 'nullable|string|max:255',
            'lang' => 'nullable|in:'.implode(',', $this->supportedLocales()),
            'status' => 'nullable|in:draft,published,publish',
            'slug' => 'required_without:translations|string|max:255',
            'title' => 'required_without:translations|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required_without:translations|string',
            'categories' => 'required|array|min:1',
            'categories.*' => 'integer|min:1',
            'author_id' => 'nullable|integer|min:1',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:255',
            'featured_media' => 'nullable|integer|min:0',
            'image' => 'nullable|string|max:2048',
            'image_preview' => 'nullable|string|max:2048',
            'image_user' => 'nullable|string|max:255',
            'automation_contract' => 'nullable|integer|in:3',
            'story_alt_mode' => 'nullable|string|in:decorative,descriptive',
            'media_sha256' => 'nullable|array',
            'media_sha256.*' => 'required|string|regex:/^[a-f0-9]{64}$/',
            'expected_revision' => 'nullable|string|size:64',
            'expected_status' => 'nullable|string|in:draft,published',
            'is_idea' => 'nullable|boolean',
            'is_stories' => 'nullable|boolean',
            'is_blogwant' => 'nullable|boolean',
            'meta' => 'nullable|array',
            'meta.rank_math_title' => 'nullable|string|max:255',
            'meta.rank_math_description' => 'nullable|string|max:255',
            'meta.rank_math_focus_keyword' => 'nullable|string|max:255',
            'meta.rank_math_canonical_url' => 'nullable|string|max:2048',
            'meta.rank_math_robots' => 'nullable',
            'meta.rank_math_primary_category' => 'nullable',
            'translations' => 'nullable|array',
            'translations.*.title' => 'required_with:translations|string|max:255',
            'translations.*.slug' => 'required_with:translations|string|max:255',
            'translations.*.excerpt' => 'nullable|string',
            'translations.*.content' => 'nullable|string',
            'translations.*.html' => 'nullable|string',
            'translations.*.text' => 'nullable|string',
            'translations.*.meta_title' => 'nullable|string|max:255',
            'translations.*.meta_desc' => 'nullable|string|max:255',
            'translations.*.image_user_alt' => 'nullable|string|max:255',
            'translations.*.image_alt' => 'nullable|string|max:255',
            'translations.*.image_preview_alt' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $categoryIds = collect($request->input('categories', []))
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $existingCategoryIds = BlogCategory::whereIn('id', $categoryIds)->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        $missingCategoryIds = array_values(array_diff($categoryIds, $existingCategoryIds));
        if (! empty($missingCategoryIds)) {
            return $this->validationErrorFromDetails([
                [
                    'field' => 'categories',
                    'issue' => 'Unknown category ids: '.implode(',', $missingCategoryIds),
                ],
            ]);
        }

        if ($request->filled('author_id') && ! $this->authorExists((int) $request->input('author_id'))) {
            return $this->validationErrorFromDetails([
                [
                    'field' => 'author_id',
                    'issue' => 'Unknown author id',
                ],
            ]);
        }

        $status = $this->normalizePostStatus((string) $request->input('status', 'draft'));
        $defaultLocale = $this->baseContentLocale();
        $translations = $this->normalizeIncomingTranslations($request, $defaultLocale);
        if (empty($translations)) {
            return $this->validationErrorFromDetails([
                [
                    'field' => 'translations',
                    'issue' => 'At least one complete translation with title, slug and content is required',
                ],
            ]);
        }

        if ((int) $request->input('automation_contract') === 3) {
            if (config('alt_generation.auto_observer')) {
                abort(503, 'Automatic paid ALT observer must be disabled');
            }
            if (count(array_unique([$request->input('image'), $request->input('image_preview'), $request->input('image_user')])) !== 3) {
                abort(422, 'Distinct image files required');
            }
            $keys = array_keys($translations);
            sort($keys);
            if ($keys !== ['de', 'ee', 'en', 'lt', 'lv', 'ru'] || $status !== 'draft') {
                abort(422, 'Six complete draft locales required');
            }
            $slugs = array_column($translations, 'slug');
            if (count(array_unique($slugs)) !== 6) {
                abort(422, 'Localized slugs required');
            }
            if (! $request->filled('idempotency_key') || ! $request->filled('external_id')) {
                abort(422, 'Ownership required');
            }
            $mode = $request->input('story_alt_mode');
            if (! in_array($mode, ['decorative', 'descriptive'], true)) {
                abort(422, 'Story ALT purpose required');
            }
            foreach ($translations as $fields) {
                foreach (['image_alt', 'image_preview_alt', 'image_user_alt'] as $field) {
                    if (! array_key_exists($field, $fields) || ($field !== 'image_user_alt' || $mode === 'descriptive') && trim($fields[$field]) === '') {
                        abort(422, 'Explicit ALT required');
                    }
                    if ($field === 'image_user_alt' && $mode === 'decorative' && $fields[$field] !== '') {
                        abort(422, 'Decorative ALT must be empty');
                    }
                }
            }
            $hashes = (array) $request->input('media_sha256', []);
            if (count($hashes) !== 5 || count(array_unique(array_keys($hashes))) !== 5) {
                abort(422, 'Five media required');
            }
            foreach (['image', 'image_preview', 'image_user'] as $field) {
                if (! isset($hashes[$request->input($field)])) {
                    abort(422, 'Media binding missing');
                }
            }
            foreach ($hashes as $path => $hash) {
                if (! preg_match('/^[a-f0-9]{64}$/', $hash) || $this->fileHash($path) !== $hash) {
                    abort(422, 'Media hash mismatch');
                }
            }
        }

        $duplicate = $this->findDuplicatePost($translations, (string) $request->input('idempotency_key', ''));
        if ($duplicate) {
            $this->assertSameCreation($duplicate, $request);

            return response()->json([
                'status' => 'duplicate',
                'data' => [
                    'post' => $this->postSummary($duplicate),
                ],
            ], 200);
        }

        $base = $translations[$defaultLocale] ?? [];
        if (! $base && ! empty($translations)) {
            abort(422, 'Explicit native EN required');
        }

        try {
            $post = DB::transaction(function () use ($request, $status, $categoryIds, $translations, $base) {
                $post = new BlogPost;
                $post->title = $base['title'] ?? null;
                $post->slug = $base['slug'] ?? null;
                $post->text = $base['text'] ?? null;
                $post->excerpt = $base['excerpt'] ?? null;
                $post->meta_title = $base['meta_title'] ?? ($base['title'] ?? null);
                $post->meta_desc = $base['meta_desc'] ?? ($base['excerpt'] ?? null);
                $post->meta_robots = $this->metaRobotsToString($request->input('meta.rank_math_robots')) ?: 'index, follow';
                $post->status = $status;
                $post->image = $this->normalizeImagePath($request);
                $post->image_preview = $request->input('image_preview');
                $post->image_user = $request->input('image_user');
                $post->tags = $request->filled('tags') ? json_encode($request->input('tags'), JSON_UNESCAPED_UNICODE) : null;
                $post->idempotency_key = $request->input('idempotency_key');
                $post->external_id = $request->input('external_id');
                $post->api_payload = json_encode(array_merge($request->all(), ['_creation_sha256' => $this->creationFingerprint($request->all())]), JSON_UNESCAPED_UNICODE);
                $post->author_id = (int) $request->input('author_id', $this->defaultAuthorId());
                $post->right_banner = (int) $request->input('right_banner', 0);
                $post->count_viewed = 1;
                $post->is_idea = $this->requestBoolean($request, 'is_idea') ? 1 : 0;
                $post->is_stories = $this->requestBoolean($request, 'is_stories') ? 1 : 0;
                $post->is_blogwant = $this->requestBoolean($request, 'is_blogwant') ? 1 : 0;
                $post->save();

                $post->categoryes()->sync($categoryIds);

                foreach ($translations as $locale => $fields) {
                    foreach (['title', 'slug', 'text', 'excerpt', 'meta_title', 'meta_desc', 'image_alt', 'image_preview_alt', 'image_user_alt'] as $column) {
                        if (! array_key_exists($column, $fields)) {
                            continue;
                        }

                        if (in_array($column, ['image_alt', 'image_preview_alt', 'image_user_alt'], true)) {
                            $this->saveImageAlt($post, $column, $locale, (string) $fields[$column]);
                        } elseif ($locale !== $this->baseContentLocale()) {
                            $this->upsertTranslation('blog_posts', $column, (int) $post->id, $locale, (string) $fields[$column]);
                        }
                    }
                }

                return $post->fresh(['categoryes', 'author']);
            });

        } catch (QueryException $error) {
            if (! ($error instanceof UniqueConstraintViolationException) || ! $request->filled('idempotency_key')) {
                throw $error;
            }
            $post = BlogPost::where('idempotency_key', $request->input('idempotency_key'))->first();
            if (! $post) {
                throw $error;
            }
            $this->assertSameCreation($post, $request);

            return response()->json(['status' => 'duplicate', 'data' => ['post' => $this->postSummary($post)]]);
        }

        return response()->json([
            'status' => 'ok',
            'data' => [
                'post' => $this->postSummary($post),
            ],
        ], 201);
    }

    public function updatePost(Request $request, int $postId): JsonResponse
    {
        $post = BlogPost::with(['categoryes', 'author'])->find($postId);
        if (! $post) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'POST_NOT_FOUND',
                    'message' => 'Blog post not found',
                    'details' => [
                        ['field' => 'post_id', 'issue' => 'not_found'],
                    ],
                ],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'external_id' => 'nullable|string|max:255',
            'lang' => 'nullable|in:'.implode(',', $this->supportedLocales()),
            'status' => 'nullable|in:draft,published,publish',
            'slug' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'categories' => 'nullable|array|min:1',
            'categories.*' => 'integer|min:1',
            'author_id' => 'nullable|integer|min:1',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:255',
            'featured_media' => 'nullable|integer|min:0',
            'image' => 'nullable|string|max:2048',
            'image_preview' => 'nullable|string|max:2048',
            'image_user' => 'nullable|string|max:255',
            'automation_contract' => 'nullable|integer|in:3',
            'story_alt_mode' => 'nullable|string|in:decorative,descriptive',
            'media_sha256' => 'nullable|array',
            'media_sha256.*' => 'required|string|regex:/^[a-f0-9]{64}$/',
            'expected_revision' => 'nullable|string|size:64',
            'expected_status' => 'nullable|string|in:draft,published',
            'is_idea' => 'nullable|boolean',
            'is_stories' => 'nullable|boolean',
            'is_blogwant' => 'nullable|boolean',
            'meta' => 'nullable|array',
            'meta.rank_math_title' => 'nullable|string|max:255',
            'meta.rank_math_description' => 'nullable|string|max:255',
            'meta.rank_math_focus_keyword' => 'nullable|string|max:255',
            'meta.rank_math_canonical_url' => 'nullable|string|max:2048',
            'meta.rank_math_robots' => 'nullable',
            'meta.rank_math_primary_category' => 'nullable',
            'translations' => 'nullable|array',
            'translations.*.title' => 'nullable|string|max:255',
            'translations.*.slug' => 'nullable|string|max:255',
            'translations.*.excerpt' => 'nullable|string',
            'translations.*.content' => 'nullable|string',
            'translations.*.html' => 'nullable|string',
            'translations.*.text' => 'nullable|string',
            'translations.*.meta_title' => 'nullable|string|max:255',
            'translations.*.meta_desc' => 'nullable|string|max:255',
            'translations.*.image_user_alt' => 'nullable|string|max:255',
            'translations.*.image_alt' => 'nullable|string|max:255',
            'translations.*.image_preview_alt' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $categoryIds = null;
        if ($request->has('categories')) {
            $categoryIds = collect($request->input('categories', []))
                ->map(function ($id) {
                    return (int) $id;
                })
                ->filter()
                ->unique()
                ->values()
                ->all();

            $existingCategoryIds = BlogCategory::whereIn('id', $categoryIds)->pluck('id')->map(function ($id) {
                return (int) $id;
            })->all();

            $missingCategoryIds = array_values(array_diff($categoryIds, $existingCategoryIds));
            if (! empty($missingCategoryIds)) {
                return $this->validationErrorFromDetails([
                    [
                        'field' => 'categories',
                        'issue' => 'Unknown category ids: '.implode(',', $missingCategoryIds),
                    ],
                ]);
            }
        }

        if ($request->filled('author_id') && ! $this->authorExists((int) $request->input('author_id'))) {
            return $this->validationErrorFromDetails([
                [
                    'field' => 'author_id',
                    'issue' => 'Unknown author id',
                ],
            ]);
        }

        $defaultLocale = $this->baseContentLocale();
        $translations = $this->normalizeIncomingTranslations($request, $defaultLocale, false);
        $slugConflict = $this->findConflictingSlugPost($translations, (int) $post->id);
        if ($slugConflict) {
            return $this->validationErrorFromDetails([
                [
                    'field' => 'slug',
                    'issue' => 'Slug already exists for another post',
                ],
            ]);
        }

        $post = DB::transaction(function () use ($request, $post, $categoryIds, $translations, $defaultLocale) {
            // Acquire locks before comparing; never trust the model read before the transaction.
            $post = BlogPost::where('id', $post->id)->lockForUpdate()->firstOrFail();
            DB::table('translations')->where('table_name', 'blog_posts')->where('foreign_key', $post->id)->lockForUpdate()->get();
            DB::table('blog_category_blog_post')->where('blog_post_id', $post->id)->lockForUpdate()->get();
            DB::table('image_alt_suggestions')->where('imageable_type', BlogPost::class)->where('imageable_id', $post->id)->lockForUpdate()->get();
            $post->load(['categoryes', 'author']);
            $expected = (string) $request->input('expected_revision', '');
            if (! preg_match('/^[a-f0-9]{64}$/', $expected)) {
                abort(428, 'expected_revision required');
            }
            if (! hash_equals($this->revision($post), $expected) ||
                $request->input('expected_status') !== $this->postStatus($post)) {
                abort(409, 'Revision/status conflict');
            }

            if ($request->filled('status')) {
                $post->status = $this->normalizePostStatus((string) $request->input('status'));
            }

            if ($request->has('tags')) {
                $post->tags = json_encode($request->input('tags', []), JSON_UNESCAPED_UNICODE);
            }

            if ($request->has('external_id')) {
                $post->external_id = $request->input('external_id');
            }

            if ($request->filled('author_id')) {
                $post->author_id = (int) $request->input('author_id');
            }

            if ($request->has('meta.rank_math_robots')) {
                $post->meta_robots = $this->metaRobotsToString($request->input('meta.rank_math_robots'));
            }

            if ($request->filled('image') || $request->filled('featured_media')) {
                $post->image = $this->normalizeImagePath($request);
            }
            foreach (['image_preview', 'image_user'] as $imageField) {
                if ($request->has($imageField)) {
                    $post->{$imageField} = $request->input($imageField);
                }
            }

            foreach (['is_idea', 'is_stories', 'is_blogwant'] as $flag) {
                if ($request->has($flag)) {
                    $post->{$flag} = $this->requestBoolean($request, $flag) ? 1 : 0;
                }
            }

            if (! empty($translations)) {
                $base = $translations[$defaultLocale] ?? [];
                if (! $base && ! empty($translations)) {
                    abort(422, 'Explicit native EN required');
                }
                foreach ([
                    'title' => 'title',
                    'slug' => 'slug',
                    'text' => 'text',
                    'excerpt' => 'excerpt',
                    'meta_title' => 'meta_title',
                    'meta_desc' => 'meta_desc',
                ] as $field => $column) {
                    if (array_key_exists($field, $base)) {
                        $post->{$column} = $base[$field];
                    }
                }
            }

            $post->api_payload = json_encode(array_replace(json_decode($post->api_payload ?: '{}', true) ?: [], $request->except('_creation_sha256')), JSON_UNESCAPED_UNICODE);
            $post->save();

            if ($categoryIds !== null) {
                $post->categoryes()->sync($categoryIds);
            }

            foreach ($translations as $locale => $fields) {
                foreach (['title', 'slug', 'text', 'excerpt', 'meta_title', 'meta_desc', 'image_alt', 'image_preview_alt', 'image_user_alt'] as $column) {
                    if (! array_key_exists($column, $fields)) {
                        continue;
                    }

                    if (in_array($column, ['image_alt', 'image_preview_alt', 'image_user_alt'], true)) {
                        $this->saveImageAlt($post, $column, $locale, (string) $fields[$column]);
                    } elseif ($locale !== $this->baseContentLocale()) {
                        $this->upsertTranslation('blog_posts', $column, (int) $post->id, $locale, (string) $fields[$column]);
                    }
                }
            }

            return $post->fresh(['categoryes', 'author']);
        });

        return response()->json([
            'status' => 'ok',
            'data' => [
                'post' => $this->postSummary($post),
            ],
        ], 200);
    }

    public function storeMedia(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimetypes:image/jpeg,image/png,image/webp,image/gif|max:5120',
            'alt' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg');
        $baseName = $request->input('slug') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $baseName = Str::slug((string) $baseName);
        if ($baseName === '') {
            $baseName = 'blog-image';
        }

        $directory = 'blog/api';
        $extension = $file->guessExtension() ?: $extension;
        $filename = hash_file('sha256', $file->getRealPath()).'.'.$extension;
        $path = $file->storeAs($directory, $filename, 'public');

        return response()->json([
            'status' => 'ok',
            'data' => [
                'media' => [
                    'path' => $path,
                    'url' => $this->imageUrl($path),
                    'disk' => 'public',
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'original_name' => $file->getClientOriginalName(),
                    'alt' => $request->input('alt'),
                    'title' => $request->input('title'),
                ],
            ],
        ], 201);
    }

    private function translationsFor(string $table, array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return [];
        }

        $rows = DB::table('translations')
            ->where('table_name', $table)
            ->whereIn('foreign_key', $ids)
            ->get();

        $translations = [];
        foreach ($rows as $row) {
            $translations[(int) $row->foreign_key][(string) $row->locale][(string) $row->column_name] = $row->value;
        }

        return $translations;
    }

    private function normalizeIncomingTranslations(Request $request, string $defaultLocale, bool $requireComplete = true): array
    {
        $translations = [];

        if (is_array($request->input('translations'))) {
            foreach ($request->input('translations') as $locale => $payload) {
                if (! in_array($locale, $this->supportedLocales(), true) || ! is_array($payload)) {
                    continue;
                }

                $translations[$locale] = $this->normalizeTranslationPayload($payload, $request, $requireComplete);
            }
        }

        if ($request->filled('title') || $request->filled('slug') || $request->filled('content')) {
            $translations[$defaultLocale] = array_replace($translations[$defaultLocale] ?? [], $this->normalizeTranslationPayload([
                'title' => $request->input('title'),
                'slug' => $request->input('slug'),
                'excerpt' => $request->input('excerpt'),
                'content' => $request->input('content'),
            ], $request, $requireComplete));
        }

        return array_filter($translations, function (array $payload) {
            return ! empty($payload);
        });
    }

    private function normalizeIncomingAuthorTranslations(Request $request, string $defaultLocale): array
    {
        $translations = [];

        if (is_array($request->input('translations'))) {
            foreach ($request->input('translations') as $locale => $payload) {
                if (! in_array($locale, $this->supportedLocales(), true) || ! is_array($payload) || ! array_key_exists('name', $payload)) {
                    continue;
                }

                $name = trim((string) $payload['name']);
                if ($name !== '') {
                    $translations[$locale] = ['name' => $name];
                }
            }
        }

        if ($request->filled('name')) {
            $translations[$defaultLocale] = ['name' => trim((string) $request->input('name'))];
        }

        return array_filter($translations, function (array $payload) {
            return ! empty($payload['name']);
        });
    }

    private function normalizeTranslationPayload(array $payload, Request $request, bool $requireComplete): array
    {
        $meta = (array) $request->input('meta', []);
        $normalized = [];

        if (array_key_exists('title', $payload)) {
            $normalized['title'] = (string) $payload['title'];
        }

        if (array_key_exists('slug', $payload)) {
            $normalized['slug'] = trim((string) $payload['slug'], " \t\n\r\0\x0B/");
        }

        if (array_key_exists('excerpt', $payload) || $request->has('excerpt')) {
            $normalized['excerpt'] = (string) ($payload['excerpt'] ?? $request->input('excerpt', ''));
        }

        if (array_key_exists('content', $payload) || array_key_exists('html', $payload) || array_key_exists('text', $payload)) {
            $normalized['text'] = (string) ($payload['content'] ?? $payload['html'] ?? $payload['text'] ?? '');
        }

        if (array_key_exists('meta_title', $payload) || array_key_exists('rank_math_title', $meta) || array_key_exists('title', $payload)) {
            $normalized['meta_title'] = (string) ($payload['meta_title'] ?? ($meta['rank_math_title'] ?? ($payload['title'] ?? '')));
        }

        if (array_key_exists('meta_desc', $payload) || array_key_exists('rank_math_description', $meta) || array_key_exists('excerpt', $payload) || $request->has('excerpt')) {
            $normalized['meta_desc'] = (string) ($payload['meta_desc'] ?? ($meta['rank_math_description'] ?? ($payload['excerpt'] ?? $request->input('excerpt', ''))));
        }

        foreach (['image_alt', 'image_preview_alt', 'image_user_alt'] as $altField) {
            if (array_key_exists($altField, $payload)) {
                $normalized[$altField] = (string) $payload[$altField];
            }
        }

        if ($requireComplete && (empty($normalized['title']) || empty($normalized['slug']) || empty($normalized['text']))) {
            return [];
        }

        return $normalized;
    }

    private function findDuplicatePost(array $translations, string $idempotencyKey = ''): ?BlogPost
    {
        if ($idempotencyKey !== '' && Schema::hasColumn('blog_posts', 'idempotency_key')) {
            $post = BlogPost::where('idempotency_key', $idempotencyKey)->first();
            if ($post) {
                return $post;
            }
        }

        foreach ($translations as $locale => $payload) {
            $slug = (string) ($payload['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $post = BlogPost::where('slug', $slug)->first();
            if ($post) {
                return $post;
            }

            $translation = DB::table('translations')
                ->where('table_name', 'blog_posts')
                ->where('column_name', 'slug')
                ->where('locale', $locale)
                ->where('value', $slug)
                ->first();

            if ($translation) {
                return BlogPost::where('id', (int) $translation->foreign_key)->first();
            }
        }

        return null;
    }

    private function findConflictingSlugPost(array $translations, int $currentPostId): ?BlogPost
    {
        foreach ($translations as $locale => $payload) {
            $slug = (string) ($payload['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $post = BlogPost::where('slug', $slug)
                ->where('id', '<>', $currentPostId)
                ->first();
            if ($post) {
                return $post;
            }

            $translation = DB::table('translations')
                ->where('table_name', 'blog_posts')
                ->where('column_name', 'slug')
                ->where('locale', $locale)
                ->where('value', $slug)
                ->where('foreign_key', '<>', $currentPostId)
                ->first();

            if ($translation) {
                return BlogPost::where('id', (int) $translation->foreign_key)->first();
            }
        }

        return null;
    }

    private function findDuplicateAuthor(array $translations): ?BlogAuthor
    {
        foreach ($translations as $locale => $payload) {
            $name = trim((string) ($payload['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $author = BlogAuthor::where('name', $name)->first();
            if ($author) {
                return $author;
            }

            $translation = DB::table('translations')
                ->where('table_name', 'blog_authors')
                ->where('column_name', 'name')
                ->where('locale', $locale)
                ->where('value', $name)
                ->first();

            if ($translation) {
                return BlogAuthor::where('id', (int) $translation->foreign_key)->first();
            }
        }

        return null;
    }

    private function upsertTranslation(string $table, string $column, int $id, string $locale, string $value): void
    {
        DB::table('translations')->updateOrInsert(
            [
                'table_name' => $table,
                'column_name' => $column,
                'foreign_key' => $id,
                'locale' => $locale,
            ],
            [
                'value' => $value,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ]
        );
    }

    private function fileHash(string $path): ?string
    {
        $root = realpath(Storage::disk('public')->path(''));
        $file = realpath(Storage::disk('public')->path($path));
        if (! $file || ! $root || strpos(str_replace('\\', '/', $file), str_replace('\\', '/', $root).'/') !== 0 || ! is_file($file)) {
            return null;
        }

        return hash_file('sha256', $file);
    }

    private function mediaHashes(BlogPost $post): array
    {
        $payload = json_decode($post->api_payload ?: '{}', true) ?: [];
        $out = [];
        foreach (array_keys($payload['media_sha256'] ?? []) as $path) {
            $out[$path] = $this->fileHash($path);
        }
        ksort($out);

        return $out;
    }

    private function saveImageAlt(BlogPost $post, string $column, string $locale, string $alt): void
    {
        $field = ['image_alt' => 'image', 'image_preview_alt' => 'image_preview', 'image_user_alt' => 'image_user'][$column];
        $path = (string) $post->{$field};
        if (! $this->fileHash($path)) {
            abort(422, 'ALT image missing');
        }
        $key = ['imageable_type' => BlogPost::class, 'imageable_id' => $post->id, 'field' => $field, 'image_path' => $path, 'locale' => $locale];
        DB::table('image_alt_suggestions')->updateOrInsert($key, ['image_hash' => $this->fileHash($path), 'approved_alt' => $alt, 'suggested_alt' => $alt, 'status' => 'applied', 'reviewed_at' => now(), 'applied_at' => now(), 'updated_at' => now()]);
    }

    private function readImageAlt(BlogPost $post, string $column, string $locale): ?string
    {
        $field = ['image_alt' => 'image', 'image_preview_alt' => 'image_preview', 'image_user_alt' => 'image_user'][$column];
        $row = DB::table('image_alt_suggestions')->where('imageable_type', BlogPost::class)->where('imageable_id', $post->id)->where('field', $field)->where('image_path', $post->{$field})->where('locale', $locale)->where('status', 'applied')->orderByDesc('id')->first();

        return $row ? $row->approved_alt : null;
    }

    private function revision(BlogPost $post, bool $contentOnly = false): string
    {
        // Deterministic content snapshot; view counters are intentionally excluded.
        // Include every stored post field; only the view counter is non-editorial.
        $data = $post->getAttributes();
        unset($data['count_viewed']);
        if ($contentOnly) {
            unset($data['status'], $data['updated_at'], $data['api_payload']);
        }
        ksort($data);
        $rows = DB::table('translations')->where('table_name', 'blog_posts')->where('foreign_key', $post->id)
            ->orderBy('locale')->orderBy('column_name')->orderBy('id')->get(['locale', 'column_name', 'value'])->toArray();
        $categories = DB::table('blog_category_blog_post')->where('blog_post_id', $post->id)
            ->orderBy('blog_category_id')->pluck('blog_category_id')->all();
        $alts = DB::table('image_alt_suggestions')->where('imageable_type', BlogPost::class)->where('imageable_id', $post->id)->orderBy('id')->get()->toArray();

        return hash('sha256', json_encode([$data, $rows, $categories, $alts, $this->mediaHashes($post)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function postSummary(BlogPost $post): array
    {
        $translations = $this->translationsFor('blog_posts', [(int) $post->id]);

        return [
            'id' => (int) $post->id,
            'revision' => $this->revision($post),
            'content_revision' => $this->revision($post, true),
            'external_id' => $post->external_id,
            'idempotency_key' => $post->idempotency_key,
            'media_sha256' => $this->mediaHashes($post),
            'story_alt_mode' => data_get(json_decode($post->api_payload ?: '{}', true), 'story_alt_mode'),
            'status' => $this->postStatus($post),
            'image' => $this->imageUrl($post->image),
            'image_path' => $post->image,
            'image_preview' => $this->imageUrl($post->image_preview),
            'image_preview_path' => $post->image_preview,
            'image_user' => $this->imageUrl($post->image_user),
            'image_user_path' => $post->image_user,
            'flags' => $this->postFlags($post),
            'author' => $post->author ? [
                'id' => (int) $post->author->id,
                'name' => (string) $post->author->name,
            ] : null,
            'category_ids' => $post->categoryes->pluck('id')->map(function ($id) {
                return (int) $id;
            })->values()->all(),
            'created_at' => $this->isoDate($post->created_at),
            'updated_at' => $this->isoDate($post->updated_at),
            'translations' => $this->localizedPayload(
                $post,
                $translations[(int) $post->id] ?? [],
                ['title', 'slug', 'meta_title', 'meta_desc', 'excerpt', 'text', 'image_alt', 'image_preview_alt', 'image_user_alt'],
                'post'
            ),
        ];
    }

    private function authorSummary(BlogAuthor $author): array
    {
        $translations = $this->translationsFor('blog_authors', [(int) $author->id]);

        return [
            'id' => (int) $author->id,
            'name' => (string) $author->name,
            'image' => $this->imageUrl($author->image),
            'image_path' => $author->image,
            'sort' => (int) ($author->sort ?? 0),
            'created_at' => $this->isoDate($author->created_at),
            'updated_at' => $this->isoDate($author->updated_at),
            'translations' => $this->localizedPayload(
                $author,
                $translations[(int) $author->id] ?? [],
                ['name'],
                'author'
            ),
        ];
    }

    private function postFlags(BlogPost $post): array
    {
        return [
            'is_idea' => (bool) $post->is_idea,
            'is_stories' => (bool) $post->is_stories,
            'is_blogwant' => (bool) $post->is_blogwant,
        ];
    }

    private function postStatus(BlogPost $post): string
    {
        return (string) ($post->status ?: 'published');
    }

    private function creationFingerprint(array $payload): string
    {
        unset($payload['_creation_sha256']);
        $sort = function ($value) use (&$sort) {
            if (! is_array($value)) {
                return $value;
            }
            if (array_keys($value) !== range(0, count($value) - 1)) {
                ksort($value);
            }
            foreach ($value as $key => $item) {
                $value[$key] = $sort($item);
            }

            return $value;
        };

        return hash('sha256', json_encode($sort($payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function assertSameCreation(BlogPost $post, Request $request): void
    {
        $key = (string) $request->input('idempotency_key', '');
        if ($key === '' || $post->idempotency_key !== $key) {
            return;
        }
        $original = json_decode($post->api_payload ?: '{}', true) ?: [];
        $fingerprint = $original['_creation_sha256'] ?? null;
        // Legacy payloads may have been overwritten by PATCH; never guess their original request.
        if (! $fingerprint || ! hash_equals($fingerprint, $this->creationFingerprint($request->all()))) {
            abort(409, 'Idempotency key already belongs to another or unverifiable request');
        }
    }

    private function baseContentLocale(): string
    {
        $fallbackLocale = (string) config('voyager.multilingual.default', 'en');
        if (in_array($fallbackLocale, $this->supportedLocales(), true)) {
            return $fallbackLocale;
        }

        return (string) config('app.locale', 'ru');
    }

    private function authorExists(int $authorId): bool
    {
        return BlogAuthor::where('id', $authorId)->exists();
    }

    private function defaultAuthorId(): int
    {
        return (int) (BlogAuthor::query()->orderBy('sort', 'asc')->orderBy('id', 'asc')->value('id') ?: 1);
    }

    private function normalizePostStatus(string $status): string
    {
        return $status === 'publish' ? 'published' : ($status ?: 'draft');
    }

    private function normalizeImagePath(Request $request): ?string
    {
        if ($request->filled('image')) {
            return (string) $request->input('image');
        }

        $featuredMedia = (int) $request->input('featured_media', 0);

        return $featuredMedia > 0 ? 'wp_media:'.$featuredMedia : null;
    }

    private function requestBoolean(Request $request, string $field, bool $default = false): bool
    {
        if (! $request->has($field)) {
            return $default;
        }

        return filter_var($request->input($field), FILTER_VALIDATE_BOOLEAN);
    }

    private function metaRobotsToString($robots): ?string
    {
        if (is_array($robots)) {
            return implode(', ', array_filter(array_map('strval', $robots)));
        }

        return $robots !== null ? (string) $robots : null;
    }

    private function decodeJsonArray(?string $value): array
    {
        if (! $value) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function uniqueStorageFilename(string $directory, string $baseName, string $extension): string
    {
        $filename = $baseName.'.'.$extension;
        $counter = 1;

        while (Storage::disk('public')->exists($directory.'/'.$filename)) {
            $filename = $baseName.'-'.$counter.'.'.$extension;
            $counter++;
        }

        return $filename;
    }

    private function localizedPayload($model, array $translations, array $fields, string $type): array
    {
        $payload = [];
        foreach ($this->supportedLocales() as $locale) {
            $item = [];
            foreach ($fields as $field) {
                if ($type === 'post' && in_array($field, ['image_alt', 'image_preview_alt', 'image_user_alt'], true)) {
                    $item[$field] = $this->readImageAlt($model, $field, $locale);
                } elseif ($type === 'post') {
                    $item[$field] = $locale === $this->baseContentLocale() ? ($model->{$field} ?? null) : ($translations[$locale][$field] ?? null);
                } else {
                    $item[$field] = $translations[$locale][$field] ?? $model->{$field} ?? null;
                }
            }

            if ($type === 'post') {
                $item['present_fields'] = array_keys(array_filter($item, function ($v) {
                    return $v !== null;
                }));
            }
            if (in_array($type, ['post', 'category'], true)) {
                $slug = (string) ($item['slug'] ?? ($type === 'post' ? '' : ($model->slug ?? '')));
                $item['url'] = $slug !== '' ? $this->localizedUrl($locale, $type, $slug) : null;
            }

            $payload[$locale] = $item;
        }

        return $payload;
    }

    private function localizedUrl(string $locale, string $type, string $slug): string
    {
        $defaultLocale = (string) config('app.locale', 'ru');
        $prefix = $locale === $defaultLocale ? '' : $locale.'/';
        $path = $type === 'category'
            ? $prefix.'blog/category/'.ltrim($slug, '/').'/'
            : $prefix.'blog/'.ltrim($slug, '/');

        return url($path);
    }

    private function imageUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        return url('storage/'.ltrim($path, '/'));
    }

    private function supportedLocales(): array
    {
        $locales = array_keys((array) config('laravellocalization.supportedLocales', []));
        if (empty($locales)) {
            $locales = ['ru', 'en'];
        }

        return array_values($locales);
    }

    private function isoDate($value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z');
    }

    private function validationError($validator): JsonResponse
    {
        $details = [];
        foreach ($validator->errors()->toArray() as $field => $messages) {
            $details[] = [
                'field' => $field,
                'issue' => $messages[0] ?? 'invalid',
            ];
        }

        Log::warning('Blog API validation rejected', ['details' => array_column($details, 'field')]);

        return response()->json([
            'status' => 'error',
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => $validator->errors()->first(),
                'details' => $details,
            ],
        ], 400);
    }

    private function validationErrorFromDetails(array $details): JsonResponse
    {
        Log::warning('Blog API validation rejected', ['details' => array_column($details, 'field')]);

        return response()->json([
            'status' => 'error',
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => (string) ($details[0]['issue'] ?? 'Validation error'),
                'details' => $details,
            ],
        ], 400);
    }
}
