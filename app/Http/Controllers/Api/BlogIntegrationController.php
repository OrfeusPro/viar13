<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogAuthor;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
            'lang' => 'nullable|in:' . implode(',', $this->supportedLocales()),
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
            $author = new BlogAuthor();
            $author->name = $base['name'] ?? null;
            $author->image = $request->input('image');
            $author->sort = (int) $request->input('sort', 0);
            $author->save();

            foreach ($translations as $locale => $fields) {
                if (!array_key_exists('name', $fields)) {
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

        if (!$includeDrafts) {
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
                        'image' => $this->imageUrl($post->image),
                        'image_path' => $post->image,
                        'image_preview' => $this->imageUrl($post->image_preview),
                        'image_preview_path' => $post->image_preview,
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
                                ? ['title', 'slug', 'meta_title', 'meta_desc', 'excerpt', 'text']
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
            'lang' => 'nullable|in:' . implode(',', $this->supportedLocales()),
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
        if (!empty($missingCategoryIds)) {
            return $this->validationErrorFromDetails([
                [
                    'field' => 'categories',
                    'issue' => 'Unknown category ids: ' . implode(',', $missingCategoryIds),
                ],
            ]);
        }

        if ($request->filled('author_id') && !$this->authorExists((int) $request->input('author_id'))) {
            return $this->validationErrorFromDetails([
                [
                    'field' => 'author_id',
                    'issue' => 'Unknown author id',
                ],
            ]);
        }

        $status = $this->normalizePostStatus((string) $request->input('status', 'draft'));
        $defaultLocale = (string) $request->input('lang', $this->baseContentLocale());
        $translations = $this->normalizeIncomingTranslations($request, $defaultLocale);
        if (empty($translations)) {
            return $this->validationErrorFromDetails([
                [
                    'field' => 'translations',
                    'issue' => 'At least one complete translation with title, slug and content is required',
                ],
            ]);
        }

        $duplicate = $this->findDuplicatePost($translations, (string) $request->input('idempotency_key', ''));
        if ($duplicate) {
            return response()->json([
                'status' => 'duplicate',
                'data' => [
                    'post' => $this->postSummary($duplicate),
                ],
            ], 200);
        }

        $base = $translations[$defaultLocale] ?? reset($translations);

        $post = DB::transaction(function () use ($request, $status, $categoryIds, $translations, $base) {
            $post = new BlogPost();
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
            $post->tags = $request->filled('tags') ? json_encode($request->input('tags'), JSON_UNESCAPED_UNICODE) : null;
            $post->idempotency_key = $request->input('idempotency_key');
            $post->external_id = $request->input('external_id');
            $post->api_payload = json_encode($request->all(), JSON_UNESCAPED_UNICODE);
            $post->author_id = (int) $request->input('author_id', $this->defaultAuthorId());
            $post->right_banner = (int) $request->input('right_banner', 0);
            $post->count_viewed = 1;
            $post->is_idea = $this->requestBoolean($request, 'is_idea') ? 1 : 0;
            $post->is_stories = $this->requestBoolean($request, 'is_stories') ? 1 : 0;
            $post->is_blogwant = $this->requestBoolean($request, 'is_blogwant') ? 1 : 0;
            $post->save();

            $post->categoryes()->sync($categoryIds);

            foreach ($translations as $locale => $fields) {
                foreach (['title', 'slug', 'text', 'excerpt', 'meta_title', 'meta_desc'] as $column) {
                    if (!array_key_exists($column, $fields)) {
                        continue;
                    }

                    $this->upsertTranslation('blog_posts', $column, (int) $post->id, $locale, (string) $fields[$column]);
                }
            }

            return $post->fresh(['categoryes', 'author']);
        });

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
        if (!$post) {
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
            'lang' => 'nullable|in:' . implode(',', $this->supportedLocales()),
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
            if (!empty($missingCategoryIds)) {
                return $this->validationErrorFromDetails([
                    [
                        'field' => 'categories',
                        'issue' => 'Unknown category ids: ' . implode(',', $missingCategoryIds),
                    ],
                ]);
            }
        }

        if ($request->filled('author_id') && !$this->authorExists((int) $request->input('author_id'))) {
            return $this->validationErrorFromDetails([
                [
                    'field' => 'author_id',
                    'issue' => 'Unknown author id',
                ],
            ]);
        }

        $defaultLocale = (string) $request->input('lang', $this->baseContentLocale());
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
            foreach (['image_preview'] as $imageField) {
                if ($request->has($imageField)) {
                    $post->{$imageField} = $request->input($imageField);
                }
            }

            foreach (['is_idea', 'is_stories', 'is_blogwant'] as $flag) {
                if ($request->has($flag)) {
                    $post->{$flag} = $this->requestBoolean($request, $flag) ? 1 : 0;
                }
            }

            if (!empty($translations)) {
                $base = $translations[$defaultLocale] ?? reset($translations);
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

            $post->api_payload = json_encode($request->all(), JSON_UNESCAPED_UNICODE);
            $post->save();

            if ($categoryIds !== null) {
                $post->categoryes()->sync($categoryIds);
            }

            foreach ($translations as $locale => $fields) {
                foreach (['title', 'slug', 'text', 'excerpt', 'meta_title', 'meta_desc'] as $column) {
                    if (!array_key_exists($column, $fields)) {
                        continue;
                    }

                    $this->upsertTranslation('blog_posts', $column, (int) $post->id, $locale, (string) $fields[$column]);
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

        $directory = 'blog/' . gmdate('Y/m');
        $filename = $this->uniqueStorageFilename($directory, $baseName, $extension);
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
                if (!in_array($locale, $this->supportedLocales(), true) || !is_array($payload)) {
                    continue;
                }

                $translations[$locale] = $this->normalizeTranslationPayload($payload, $request, $requireComplete);
            }
        }

        if ($request->filled('title') || $request->filled('slug') || $request->filled('content')) {
            $translations[$defaultLocale] = $this->normalizeTranslationPayload([
                'title' => $request->input('title'),
                'slug' => $request->input('slug'),
                'excerpt' => $request->input('excerpt'),
                'content' => $request->input('content'),
            ], $request, $requireComplete);
        }

        return array_filter($translations, function (array $payload) {
            return !empty($payload);
        });
    }

    private function normalizeIncomingAuthorTranslations(Request $request, string $defaultLocale): array
    {
        $translations = [];

        if (is_array($request->input('translations'))) {
            foreach ($request->input('translations') as $locale => $payload) {
                if (!in_array($locale, $this->supportedLocales(), true) || !is_array($payload) || !array_key_exists('name', $payload)) {
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
            return !empty($payload['name']);
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

    private function postSummary(BlogPost $post): array
    {
        $translations = $this->translationsFor('blog_posts', [(int) $post->id]);

        return [
            'id' => (int) $post->id,
            'status' => $this->postStatus($post),
            'image' => $this->imageUrl($post->image),
            'image_path' => $post->image,
            'image_preview' => $this->imageUrl($post->image_preview),
            'image_preview_path' => $post->image_preview,
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
                ['title', 'slug', 'meta_title', 'meta_desc', 'excerpt', 'text'],
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

    private function baseContentLocale(): string
    {
        $fallbackLocale = (string) config('app.fallback_locale', 'en');
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
        return $featuredMedia > 0 ? 'wp_media:' . $featuredMedia : null;
    }

    private function requestBoolean(Request $request, string $field, bool $default = false): bool
    {
        if (!$request->has($field)) {
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
        if (!$value) {
            return [];
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function uniqueStorageFilename(string $directory, string $baseName, string $extension): string
    {
        $filename = $baseName . '.' . $extension;
        $counter = 1;

        while (Storage::disk('public')->exists($directory . '/' . $filename)) {
            $filename = $baseName . '-' . $counter . '.' . $extension;
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
                $item[$field] = $translations[$locale][$field] ?? $model->{$field} ?? null;
            }

            if (in_array($type, ['post', 'category'], true)) {
                $slug = (string) ($item['slug'] ?? $model->slug ?? '');
                $item['url'] = $slug !== '' ? $this->localizedUrl($locale, $type, $slug) : null;
            }

            $payload[$locale] = $item;
        }

        return $payload;
    }

    private function localizedUrl(string $locale, string $type, string $slug): string
    {
        $defaultLocale = (string) config('app.locale', 'ru');
        $prefix = $locale === $defaultLocale ? '' : $locale . '/';
        $path = $type === 'category'
            ? $prefix . 'blog/category/' . ltrim($slug, '/') . '/'
            : $prefix . 'blog/' . ltrim($slug, '/') . '/';

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

        return url('storage/' . ltrim($path, '/'));
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
        if (!$value) {
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
