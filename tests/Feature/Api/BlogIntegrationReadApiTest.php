<?php

namespace Tests\Feature\Api;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogIntegrationReadApiTest extends TestCase
{
    private const API_KEY = 'test-key';

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareBlogFixtures();
    }

    /** @test */
    public function categories_returns_multilingual_payload()
    {
        $response = $this->getJson('/api/blog/categories', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.categories.0.id', 1)
            ->assertJsonPath('data.categories.0.translations.ru.title', 'Подарки')
            ->assertJsonPath('data.categories.0.translations.en.title', 'Gifts')
            ->assertJsonPath('data.categories.0.translations.en.slug', 'gifts');

        $this->assertContains('ru', $response->json('meta.locales'));
        $this->assertContains('en', $response->json('meta.locales'));
    }

    /** @test */
    public function authors_returns_multilingual_payload()
    {
        $response = $this->getJson('/api/blog/authors', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.authors.0.id', 1)
            ->assertJsonPath('data.authors.0.name', 'Admin')
            ->assertJsonPath('data.authors.0.translations.ru.name', 'Admin')
            ->assertJsonPath('data.authors.0.translations.en.name', 'Editor');
    }

    /** @test */
    public function author_can_be_created_with_translations()
    {
        $response = $this->postJson('/api/blog/authors', [
            'image' => 'blog/authors/ai-author.jpg',
            'sort' => 5,
            'translations' => [
                'ru' => [
                    'name' => 'AI Редактор',
                ],
                'en' => [
                    'name' => 'AI Editor',
                ],
            ],
        ], $this->apiHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.author.name', 'AI Editor')
            ->assertJsonPath('data.author.image_path', 'blog/authors/ai-author.jpg')
            ->assertJsonPath('data.author.sort', 5)
            ->assertJsonPath('data.author.translations.ru.name', 'AI Редактор')
            ->assertJsonPath('data.author.translations.en.name', 'AI Editor');

        $this->assertDatabaseHas('blog_authors', [
            'name' => 'AI Editor',
            'image' => 'blog/authors/ai-author.jpg',
            'sort' => 5,
        ]);
    }

    /** @test */
    public function repeated_author_name_returns_duplicate()
    {
        $payload = [
            'name' => 'Admin',
            'lang' => 'ru',
        ];

        $response = $this->postJson('/api/blog/authors', $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'duplicate')
            ->assertJsonPath('data.author.id', 1);
    }

    /** @test */
    public function author_create_requires_name()
    {
        $response = $this->postJson('/api/blog/authors', [
            'image' => 'blog/authors/empty.jpg',
        ], $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function posts_returns_multilingual_html_text_and_categories()
    {
        $response = $this->getJson('/api/blog/posts?per_page=10', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('meta.pagination.total', 1)
            ->assertJsonPath('data.posts.0.id', 10)
            ->assertJsonPath('data.posts.0.category_ids.0', 1)
            ->assertJsonPath('data.posts.0.translations.ru.title', 'Фото на холсте')
            ->assertJsonPath('data.posts.0.translations.ru.text', '<p>Русский HTML</p>')
            ->assertJsonPath('data.posts.0.translations.en.title', 'Canvas photo')
            ->assertJsonPath('data.posts.0.translations.en.text', '<p>English HTML</p>');
    }

    /** @test */
    public function posts_can_omit_html_text()
    {
        $response = $this->getJson('/api/blog/posts?include_text=false', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        $this->assertArrayNotHasKey('text', $response->json('data.posts.0.translations.ru'));
        $this->assertArrayNotHasKey('text', $response->json('data.posts.0.translations.en'));
    }

    /** @test */
    public function category_filter_limits_posts()
    {
        $response = $this->getJson('/api/blog/posts?category_id=2', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('meta.pagination.total', 0)
            ->assertJsonPath('data.posts', []);
    }

    /** @test */
    public function invalid_query_returns_validation_error()
    {
        $response = $this->getJson('/api/blog/posts?per_page=9999', $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function missing_api_key_is_rejected()
    {
        $response = $this->getJson('/api/blog/posts');

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'UNAUTHORIZED');
    }

    /** @test */
    public function wp_like_payload_creates_draft_post_with_custom_slug()
    {
        $response = $this->postJson('/api/blog/posts', [
            'idempotency_key' => 'article-canvas-001',
            'type' => 'post',
            'status' => 'draft',
            'slug' => 'custom-canvas-slug',
            'tags' => ['canvas', 'gift'],
            'categories' => [1],
            'title' => 'Custom canvas article',
            'excerpt' => 'Short intro',
            'content' => '<p>Article HTML</p>',
            'featured_media' => 123,
            'image_preview' => 'blog/preview.jpg',
            'author_id' => 1,
            'is_idea' => true,
            'is_stories' => false,
            'is_blogwant' => 1,
            'meta' => [
                'rank_math_title' => 'SEO title',
                'rank_math_description' => 'SEO description',
                'rank_math_robots' => ['index', 'follow'],
            ],
        ], $this->apiHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.post.status', 'draft')
            ->assertJsonPath('data.post.author.id', 1)
            ->assertJsonPath('data.post.image_path', 'wp_media:123')
            ->assertJsonPath('data.post.image_preview_path', 'blog/preview.jpg')
            ->assertJsonPath('data.post.flags.is_idea', true)
            ->assertJsonPath('data.post.flags.is_stories', false)
            ->assertJsonPath('data.post.flags.is_blogwant', true)
            ->assertJsonPath('data.post.translations.en.slug', 'custom-canvas-slug')
            ->assertJsonPath('data.post.translations.en.title', 'Custom canvas article')
            ->assertJsonPath('data.post.translations.en.text', '<p>Article HTML</p>');

        $this->assertDatabaseHas('blog_posts', [
            'slug' => 'custom-canvas-slug',
            'status' => 'draft',
            'meta_robots' => 'index, follow',
            'image' => 'wp_media:123',
            'image_preview' => 'blog/preview.jpg',
            'idempotency_key' => 'article-canvas-001',
            'author_id' => 1,
            'is_idea' => 1,
            'is_stories' => 0,
            'is_blogwant' => 1,
        ]);
    }

    /** @test */
    public function repeated_payload_returns_duplicate()
    {
        $payload = [
            'idempotency_key' => 'article-duplicate-001',
            'status' => 'draft',
            'slug' => 'duplicate-slug',
            'categories' => [1],
            'title' => 'Duplicate article',
            'content' => '<p>Duplicate HTML</p>',
        ];

        $this->postJson('/api/blog/posts', $payload, $this->apiHeaders())->assertStatus(201);
        $response = $this->postJson('/api/blog/posts', $payload, $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'duplicate')
            ->assertJsonPath('data.post.translations.en.slug', 'duplicate-slug');
    }

    /** @test */
    public function translations_payload_creates_language_specific_slugs()
    {
        $response = $this->postJson('/api/blog/posts', [
            'idempotency_key' => 'article-translations-001',
            'status' => 'published',
            'categories' => [1],
            'translations' => [
                'ru' => [
                    'title' => 'Русская статья',
                    'slug' => 'russkaya-statya',
                    'content' => '<p>RU HTML</p>',
                    'meta_title' => 'RU SEO',
                    'meta_desc' => 'RU desc',
                ],
                'en' => [
                    'title' => 'English article',
                    'slug' => 'english-article',
                    'content' => '<p>EN HTML</p>',
                    'meta_title' => 'EN SEO',
                    'meta_desc' => 'EN desc',
                ],
            ],
        ], $this->apiHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.post.status', 'published')
            ->assertJsonPath('data.post.translations.ru.slug', 'russkaya-statya')
            ->assertJsonPath('data.post.translations.en.slug', 'english-article')
            ->assertJsonPath('data.post.translations.en.url', url('en/blog/english-article'));

        $this->assertDatabaseHas('blog_posts', [
            'slug' => 'english-article',
            'title' => 'English article',
            'meta_title' => 'EN SEO',
            'meta_robots' => 'index, follow',
        ]);
    }

    /** @test */
    public function patch_updates_existing_post_translations_status_and_categories()
    {
        DB::table('blog_categories')->insert([
            'id' => 2,
            'title' => 'Новая категория',
            'slug' => 'new-category',
            'created_at' => Carbon::parse('2026-05-20 08:00:00'),
            'updated_at' => Carbon::parse('2026-05-20 08:00:00'),
        ]);

        $response = $this->patchJson('/api/blog/posts/10', [
            'status' => 'draft',
            'categories' => [2],
            'author_id' => 1,
            'image' => 'blog/main-updated.jpg',
            'image_preview' => 'blog/preview-updated.jpg',
            'tags' => ['updated'],
            'is_idea' => false,
            'is_stories' => true,
            'is_blogwant' => true,
            'translations' => [
                'ru' => [
                    'title' => 'Обновленное название',
                    'slug' => 'obnovlennyj-slug',
                ],
                'en' => [
                    'slug' => 'updated-english-slug',
                    'content' => '<p>Updated EN HTML</p>',
                ],
            ],
        ], $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.post.id', 10)
            ->assertJsonPath('data.post.status', 'draft')
            ->assertJsonPath('data.post.author.id', 1)
            ->assertJsonPath('data.post.image_path', 'blog/main-updated.jpg')
            ->assertJsonPath('data.post.image_preview_path', 'blog/preview-updated.jpg')
            ->assertJsonPath('data.post.flags.is_idea', false)
            ->assertJsonPath('data.post.flags.is_stories', true)
            ->assertJsonPath('data.post.flags.is_blogwant', true)
            ->assertJsonPath('data.post.category_ids.0', 2)
            ->assertJsonPath('data.post.translations.ru.title', 'Обновленное название')
            ->assertJsonPath('data.post.translations.ru.slug', 'obnovlennyj-slug')
            ->assertJsonPath('data.post.translations.en.slug', 'updated-english-slug')
            ->assertJsonPath('data.post.translations.en.text', '<p>Updated EN HTML</p>');

        $this->assertDatabaseHas('blog_posts', [
            'id' => 10,
            'status' => 'draft',
            'slug' => 'updated-english-slug',
            'text' => '<p>Updated EN HTML</p>',
            'image' => 'blog/main-updated.jpg',
            'image_preview' => 'blog/preview-updated.jpg',
            'is_idea' => 0,
            'is_stories' => 1,
            'is_blogwant' => 1,
        ]);
    }

    /** @test */
    public function patch_rejects_slug_used_by_another_post()
    {
        DB::table('blog_posts')->insert([
            'id' => 11,
            'title' => 'Another post',
            'slug' => 'another-post',
            'text' => '<p>Another</p>',
            'status' => 'published',
            'created_at' => Carbon::parse('2026-05-20 09:00:00'),
            'updated_at' => Carbon::parse('2026-05-20 09:00:00'),
        ]);

        $response = $this->patchJson('/api/blog/posts/10', [
            'translations' => [
                'ru' => [
                    'slug' => 'another-post',
                ],
            ],
        ], $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function create_rejects_unknown_author()
    {
        $response = $this->postJson('/api/blog/posts', [
            'status' => 'draft',
            'slug' => 'unknown-author',
            'categories' => [1],
            'author_id' => 999,
            'title' => 'Unknown author',
            'content' => '<p>HTML</p>',
        ], $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function patch_unknown_post_returns_not_found()
    {
        $response = $this->patchJson('/api/blog/posts/999999', [
            'status' => 'draft',
        ], $this->apiHeaders());

        $response->assertStatus(404)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'POST_NOT_FOUND');
    }

    /** @test */
    public function media_upload_stores_image_and_returns_path_and_url()
    {
        Storage::fake('public');

        $response = $this->post('/api/blog/media', [
            'file' => UploadedFile::fake()->image('Article Header.jpg', 1200, 630),
            'alt' => 'Article header',
            'title' => 'Header image',
            'slug' => 'article-header',
        ], $this->multipartApiHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('data.media.disk', 'public')
            ->assertJsonPath('data.media.alt', 'Article header');

        $path = $response->json('data.media.path');
        $this->assertStringStartsWith('blog/', $path);
        $this->assertStringContainsString('article-header', $path);
        Storage::disk('public')->assertExists($path);
    }

    /** @test */
    public function media_upload_rejects_non_image_file()
    {
        Storage::fake('public');

        $response = $this->post('/api/blog/media', [
            'file' => UploadedFile::fake()->create('article.txt', 1, 'text/plain'),
        ], $this->multipartApiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    private function apiHeaders(): array
    {
        return [
            'X-Api-Key' => self::API_KEY,
            'Content-Type' => 'application/json',
        ];
    }

    private function multipartApiHeaders(): array
    {
        return [
            'X-Api-Key' => self::API_KEY,
        ];
    }

    private function prepareBlogFixtures(): void
    {
        Schema::dropIfExists('blog_category_blog_post');
        Schema::dropIfExists('translations');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('blog_authors');

        Schema::create('blog_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_desc')->nullable();
            $table->text('seo')->nullable();
            $table->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('meta_title')->nullable();
            $table->text('meta_desc')->nullable();
            $table->string('title')->nullable();
            $table->text('text')->nullable();
            $table->text('excerpt')->nullable();
            $table->text('tags')->nullable();
            $table->string('idempotency_key')->nullable();
            $table->string('external_id')->nullable();
            $table->longText('api_payload')->nullable();
            $table->string('slug')->nullable();
            $table->string('image')->nullable();
            $table->string('image_preview')->nullable();
            $table->string('image_user')->nullable();
            $table->integer('author_id')->nullable();
            $table->integer('is_idea')->nullable();
            $table->integer('is_stories')->nullable();
            $table->integer('is_blogwant')->nullable();
            $table->integer('count_viewed')->nullable();
            $table->integer('right_banner')->nullable();
            $table->string('status', 32)->default('published');
            $table->string('meta_robots')->nullable();
            $table->timestamps();
        });

        Schema::create('blog_authors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('image')->nullable();
            $table->integer('sort')->nullable();
            $table->timestamps();
        });

        Schema::create('blog_category_blog_post', function (Blueprint $table) {
            $table->integer('blog_category_id');
            $table->integer('blog_post_id');
        });

        Schema::create('translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('table_name');
            $table->string('column_name');
            $table->integer('foreign_key');
            $table->string('locale', 10);
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('blog_authors')->insert([
            'id' => 1,
            'name' => 'Admin',
            'image' => 'authors/admin.jpg',
            'sort' => 1,
            'created_at' => Carbon::parse('2026-05-20 08:00:00'),
            'updated_at' => Carbon::parse('2026-05-20 08:00:00'),
        ]);

        DB::table('blog_categories')->insert([
            'id' => 1,
            'title' => 'Подарки',
            'slug' => 'podarki',
            'meta_title' => 'Подарки',
            'meta_desc' => 'Описание подарков',
            'seo' => '<p>SEO</p>',
            'created_at' => Carbon::parse('2026-05-20 08:00:00'),
            'updated_at' => Carbon::parse('2026-05-20 08:00:00'),
        ]);

        DB::table('blog_posts')->insert([
            'id' => 10,
            'title' => 'Фото на холсте',
            'slug' => 'foto-na-holste',
            'meta_title' => 'Фото на холсте',
            'meta_desc' => 'Описание статьи',
            'text' => '<p>Русский HTML</p>',
            'excerpt' => 'Короткое описание',
            'image' => 'blog/photo.jpg',
            'author_id' => 1,
            'status' => 'published',
            'created_at' => Carbon::parse('2026-05-20 09:00:00'),
            'updated_at' => Carbon::parse('2026-05-20 09:00:00'),
        ]);

        DB::table('blog_category_blog_post')->insert([
            'blog_category_id' => 1,
            'blog_post_id' => 10,
        ]);

        DB::table('translations')->insert([
            $this->translation('blog_categories', 'title', 1, 'en', 'Gifts'),
            $this->translation('blog_categories', 'slug', 1, 'en', 'gifts'),
            $this->translation('blog_categories', 'meta_title', 1, 'en', 'Gifts'),
            $this->translation('blog_authors', 'name', 1, 'en', 'Editor'),
            $this->translation('blog_posts', 'title', 10, 'en', 'Canvas photo'),
            $this->translation('blog_posts', 'slug', 10, 'en', 'canvas-photo'),
            $this->translation('blog_posts', 'meta_title', 10, 'en', 'Canvas photo'),
            $this->translation('blog_posts', 'meta_desc', 10, 'en', 'Article description'),
            $this->translation('blog_posts', 'excerpt', 10, 'en', 'Short description'),
            $this->translation('blog_posts', 'text', 10, 'en', '<p>English HTML</p>'),
        ]);
    }

    private function translation(string $table, string $column, int $id, string $locale, string $value): array
    {
        return [
            'table_name' => $table,
            'column_name' => $column,
            'foreign_key' => $id,
            'locale' => $locale,
            'value' => $value,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
