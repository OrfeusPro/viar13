<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\BlogController;
use App\Http\Middleware\ArticleLocaleSessionRedirect;
use App\Models\BlogPost;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogIntegrationReadApiTest extends TestCase
{
    private const API_KEY = 'test-key';

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareBlogFixtures();
        config(['services.sa_integration.api_key' => self::API_KEY]);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function author_create_requires_name()
    {
        $response = $this->postJson('/api/blog/authors', [
            'image' => 'blog/authors/empty.jpg',
        ], $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    #[Test]
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

    #[Test]
    public function posts_can_omit_html_text()
    {
        $response = $this->getJson('/api/blog/posts?include_text=false', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        $this->assertArrayNotHasKey('text', $response->json('data.posts.0.translations.ru'));
        $this->assertArrayNotHasKey('text', $response->json('data.posts.0.translations.en'));
    }

    #[Test]
    public function category_filter_limits_posts()
    {
        $response = $this->getJson('/api/blog/posts?category_id=2', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('meta.pagination.total', 0)
            ->assertJsonPath('data.posts', []);
    }

    #[Test]
    public function invalid_query_returns_validation_error()
    {
        $response = $this->getJson('/api/blog/posts?per_page=9999', $this->apiHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    #[Test]
    public function missing_api_key_is_rejected()
    {
        $response = $this->getJson('/api/blog/posts');

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'UNAUTHORIZED');
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function patch_updates_existing_post_translations_status_and_categories()
    {
        DB::table('blog_categories')->insert([
            'id' => 2,
            'title' => 'Новая категория',
            'slug' => 'new-category',
            'created_at' => Carbon::parse('2026-05-20 08:00:00'),
            'updated_at' => Carbon::parse('2026-05-20 08:00:00'),
        ]);

        $snapshot = $this->getJson('/api/blog/posts', $this->apiHeaders())->json('data.posts.0');
        $response = $this->patchJson('/api/blog/posts/10', [
            'expected_revision' => $snapshot['revision'],
            'expected_status' => $snapshot['status'],
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function patch_unknown_post_returns_not_found()
    {
        $response = $this->patchJson('/api/blog/posts/999999', [
            'status' => 'draft',
        ], $this->apiHeaders());

        $response->assertStatus(404)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error.code', 'POST_NOT_FOUND');
    }

    #[Test]
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
        $this->assertMatchesRegularExpression('~^blog/api/[a-f0-9]{64}\.jpg$~', $path);
        Storage::disk('public')->assertExists($path);
    }

    #[Test]
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

    public function test_revision_conflict_and_precondition_preserve_post(): void
    {
        $snapshot = $this->getJson('/api/blog/posts', $this->apiHeaders())->json('data.posts.0');
        $this->patchJson('/api/blog/posts/10', ['status' => 'draft'], $this->apiHeaders())
            ->assertStatus(428)->assertJsonPath('error.code', 'PRECONDITION_REQUIRED');
        $payload = ['expected_revision' => $snapshot['revision'], 'expected_status' => 'published', 'status' => 'draft'];
        $this->patchJson('/api/blog/posts/10', $payload, $this->apiHeaders())->assertOk();
        $payload['status'] = 'published';
        $this->patchJson('/api/blog/posts/10', $payload, $this->apiHeaders())
            ->assertStatus(409)->assertJsonPath('error.code', 'CONFLICT');
        $this->assertDatabaseHas('blog_posts', ['id' => 10, 'status' => 'draft']);
    }

    public function test_idempotency_key_cannot_be_reused_for_changed_content(): void
    {
        $payload = ['categories' => [1], 'idempotency_key' => 'ownership', 'title' => 'Original', 'content' => '<p>Original</p>', 'slug' => 'owned'];
        $this->postJson('/api/blog/posts', $payload, $this->apiHeaders())->assertCreated();
        $payload['title'] = 'Changed';
        $this->postJson('/api/blog/posts', $payload, $this->apiHeaders())
            ->assertStatus(409)->assertJsonPath('error.code', 'CONFLICT');
        $this->assertDatabaseCount('blog_posts', 2);
    }

    public function test_missing_translations_are_not_reported_as_native_content(): void
    {
        $post = $this->getJson('/api/blog/posts', $this->apiHeaders())->assertOk()->json('data.posts.0');
        $this->assertNull($post['translations']['de']['title']);
        $this->assertSame([], $post['translations']['de']['present_fields']);
        $this->assertSame('Canvas photo', $post['translations']['en']['title']);
        $this->assertSame('Фото на холсте', $post['translations']['ru']['title']);
        $controller = new BlogController;
        $method = new \ReflectionMethod($controller, 'buildBlogPostLocalizedUrls');
        $urls = $method->invoke($controller, BlogPost::findOrFail(10));
        $this->assertArrayHasKey('en', $urls);
        $this->assertArrayHasKey('ru', $urls);
        $this->assertArrayNotHasKey('de', $urls);
        $this->assertArrayNotHasKey('pl', $urls);
    }

    public function test_pilot_contract_stores_all_alts_and_rejects_modified_media(): void
    {
        Storage::fake('public');
        $hashes = [];
        for ($i = 0; $i < 5; $i++) {
            Storage::disk('public')->put("blog/api/test-$i.jpg", "test-image-$i");
            $hashes["blog/api/test-$i.jpg"] = hash('sha256', "test-image-$i");
        }
        $translations = [];
        foreach (['de', 'ee', 'en', 'lt', 'lv', 'ru'] as $locale) {
            $translations[$locale] = ['title' => "Title $locale", 'slug' => "pilot-$locale", 'content' => "<p>$locale</p>",
                'image_alt' => "Main $locale", 'image_preview_alt' => "Preview $locale", 'image_user_alt' => ''];
        }
        $payload = ['categories' => [1], 'automation_contract' => 3, 'status' => 'draft', 'external_id' => 'pilot', 'idempotency_key' => 'pilot',
            'image' => 'blog/api/test-0.jpg', 'image_preview' => 'blog/api/test-1.jpg', 'image_user' => 'blog/api/test-2.jpg',
            'media_sha256' => $hashes, 'story_alt_mode' => 'decorative', 'translations' => $translations];
        $response = $this->postJson('/api/blog/posts', $payload, $this->apiHeaders())->assertCreated()
            ->assertJsonPath('data.post.translations.ru.image_alt', 'Main ru')
            ->assertJsonPath('data.post.translations.ru.image_user_alt', '');
        $this->assertDatabaseCount('image_alt_suggestions', 18);
        $this->postJson('/api/blog/posts', $payload, $this->apiHeaders())->assertOk()->assertJsonPath('status', 'duplicate');
        $invalid = $payload;
        $invalid['media_sha256'] = null;
        $this->postJson('/api/blog/posts', $invalid, $this->apiHeaders())->assertStatus(422)->assertJsonPath('status', 'error');
        Storage::disk('public')->put('blog/api/test-0.jpg', 'changed');
        $this->postJson('/api/blog/posts', $payload, $this->apiHeaders())->assertStatus(422)
            ->assertJsonPath('status', 'error');
        $this->assertDatabaseCount('image_alt_suggestions', 18);
        $this->assertSame('draft', $response->json('data.post.status'));
    }

    public function test_invalid_contract_shape_is_validation_error(): void
    {
        $this->postJson('/api/blog/posts', ['automation_contract' => 3, 'media_sha256' => 'invalid'], $this->apiHeaders())
            ->assertStatus(400)->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_view_counter_does_not_change_editor_revision(): void
    {
        $before = $this->getJson('/api/blog/posts', $this->apiHeaders())->json('data.posts.0.revision');
        DB::table('blog_posts')->where('id', 10)->increment('count_viewed');
        $after = $this->getJson('/api/blog/posts', $this->apiHeaders())->json('data.posts.0.revision');
        $this->assertSame($before, $after);
    }

    public function test_unique_migration_is_repeatable_and_rejects_duplicates_without_changes(): void
    {
        Schema::table('blog_posts', fn (Blueprint $table) => $table->dropUnique(['idempotency_key']));
        DB::table('blog_posts')->where('id', 10)->update(['idempotency_key' => 'same']);
        DB::table('blog_posts')->insert(['id' => 11, 'idempotency_key' => 'same']);
        $migration = require database_path('migrations/2026_10_09_180000_unique_blog_idempotency.php');
        try {
            $migration->up();
            $this->fail('Duplicate keys must block migration.');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('Duplicate blog', $error->getMessage());
        }
        $this->assertSame(2, DB::table('blog_posts')->where('idempotency_key', 'same')->count());
        DB::table('blog_posts')->where('id', 11)->update(['idempotency_key' => null]);
        $migration->up();
        $migration->up();
        $this->assertTrue(Schema::hasIndex('blog_posts', 'blog_posts_idempotency_unique'));
        $migration->down();
        $this->assertFalse(Schema::hasIndex('blog_posts', 'blog_posts_idempotency_unique'));
    }

    public function test_default_article_does_not_redirect_to_session_language(): void
    {
        $request = Request::create('/blog/canvas-photo');
        $route = new Route('GET', 'blog/{slug}', fn () => null);
        $route->name('blog_inner');
        $request->setRouteResolver(fn () => $route);
        $request->setLaravelSession(app('session.store'));
        $request->session()->put('locale', 'en');
        $response = (new ArticleLocaleSessionRedirect)->handle($request, fn () => response('article'));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ru', app()->getLocale());
        $this->assertSame('en', $request->session()->get('locale'));
    }

    public function test_alt_missing_file_rolls_back_created_post(): void
    {
        $this->postJson('/api/blog/posts', ['categories' => [1], 'title' => 'Missing', 'slug' => 'missing', 'content' => '<p>Missing</p>',
            'translations' => ['en' => ['title' => 'Missing', 'slug' => 'missing', 'content' => '<p>Missing</p>', 'image_alt' => 'ALT']],
            'image' => 'blog/nonexistent.jpg'], $this->apiHeaders())->assertStatus(422);
        $this->assertDatabaseCount('blog_posts', 1);
        $this->assertDatabaseCount('image_alt_suggestions', 0);
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
        Schema::dropIfExists('image_alt_suggestions');
        require_once database_path('migrations/2026_07_01_120000_create_image_alt_suggestions_table.php');
        (new \CreateImageAltSuggestionsTable)->up();
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
            $table->string('idempotency_key')->nullable()->unique();
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
            'title' => 'Canvas photo',
            'slug' => 'canvas-photo',
            'meta_title' => 'Фото на холсте',
            'meta_desc' => 'Описание статьи',
            'text' => '<p>English HTML</p>',
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
            $this->translation('blog_posts', 'title', 10, 'ru', 'Фото на холсте'),
            $this->translation('blog_posts', 'slug', 10, 'en', 'canvas-photo'),
            $this->translation('blog_posts', 'meta_title', 10, 'en', 'Canvas photo'),
            $this->translation('blog_posts', 'meta_desc', 10, 'en', 'Article description'),
            $this->translation('blog_posts', 'excerpt', 10, 'en', 'Short description'),
            $this->translation('blog_posts', 'text', 10, 'ru', '<p>Русский HTML</p>'),
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
