# Blog generation API

API for the external article generation system.

Auth: send `X-Api-Key` with the same integration key used by existing API integrations.

## GET `/api/blog/categories`

Returns all blog categories with translations for all enabled site locales.

Query params:
- `updated_since` optional ISO-8601 date. Returns categories updated after this date.

Response shape:
```json
{
  "status": "ok",
  "meta": {
    "generated_at": "2026-05-20T00:00:00Z",
    "locales": ["lv", "lt", "pl", "ru", "de", "en", "ee"],
    "count": 1
  },
  "data": {
    "categories": [
      {
        "id": 1,
        "created_at": "2026-05-20T00:00:00Z",
        "updated_at": "2026-05-20T00:00:00Z",
        "translations": {
          "ru": {
            "title": "Подарки",
            "slug": "podarki",
            "meta_title": "Подарки",
            "meta_desc": "Описание",
            "seo": "<p>SEO text</p>",
            "url": "https://example.com/blog/category/podarki"
          }
        }
      }
    ]
  }
}
```

## GET `/api/blog/authors`

Returns all blog authors from `blog_authors`.

Query:
- `updated_since` optional ISO-8601 date. Returns authors updated after this date.

Success response:
```json
{
  "status": "ok",
  "meta": {
    "generated_at": "2026-05-21T09:00:00Z",
    "locales": ["lv", "lt", "pl", "ru", "de", "en", "ee"],
    "count": 1
  },
  "data": {
    "authors": [
      {
        "id": 1,
        "name": "Admin",
        "image": "https://example.com/storage/authors/admin.jpg",
        "image_path": "authors/admin.jpg",
        "sort": 1,
        "translations": {
          "ru": {"name": "Admin"},
          "en": {"name": "Editor"}
        }
      }
    ]
  }
}
```

## POST `/api/blog/authors`

Creates a blog author in `blog_authors`.

Important:
- Send either `name` or `translations.{locale}.name`.
- Base table fields use `en` by default (`app.fallback_locale`); other locales are saved into `translations`.
- `image` is optional and may contain an already uploaded path or URL.
- Repeated `name` or repeated translated name returns `200` with `status=duplicate`.
- Use returned `data.author.id` as `author_id` when creating or updating posts.

Single-language request:
```json
{
  "lang": "ru",
  "name": "AI редактор",
  "image": "blog/authors/ai-editor.jpg",
  "sort": 10
}
```

Multilingual request:
```json
{
  "image": "blog/authors/ai-editor.jpg",
  "sort": 10,
  "translations": {
    "ru": {"name": "AI редактор"},
    "en": {"name": "AI editor"}
  }
}
```

Success response:
```json
{
  "status": "ok",
  "data": {
    "author": {
      "id": 4,
      "name": "AI редактор",
      "image": "https://example.com/storage/blog/authors/ai-editor.jpg",
      "image_path": "blog/authors/ai-editor.jpg",
      "sort": 10,
      "translations": {
        "ru": {"name": "AI редактор"},
        "en": {"name": "AI editor"}
      }
    }
  }
}
```

## GET `/api/blog/posts`

Returns blog articles with category links and translations. Article `text` is HTML.

Query params:
- `updated_since` optional ISO-8601 date. Returns posts updated after this date.
- `category_id` optional category filter.
- `include_text` optional, default `true`. Use `false` to omit article HTML.
- `page` optional, default `1`.
- `per_page` optional, default `100`, max `500`.

Response shape:
```json
{
  "status": "ok",
  "meta": {
    "generated_at": "2026-05-20T00:00:00Z",
    "locales": ["lv", "lt", "pl", "ru", "de", "en", "ee"],
    "pagination": {
      "current_page": 1,
      "per_page": 100,
      "total": 1,
      "last_page": 1
    }
  },
  "data": {
    "posts": [
      {
        "id": 1,
        "image": "https://example.com/storage/blog/image.jpg",
        "image_path": "blog/image.jpg",
        "image_preview": "https://example.com/storage/blog/preview.jpg",
        "image_preview_path": "blog/preview.jpg",
        "author": {
          "id": 1,
          "name": "Admin"
        },
        "category_ids": [1],
        "created_at": "2026-05-20T00:00:00Z",
        "updated_at": "2026-05-20T00:00:00Z",
        "translations": {
          "ru": {
            "title": "Название статьи",
            "slug": "nazvanie-stati",
            "meta_title": "Meta title",
            "meta_desc": "Meta description",
            "text": "<p>HTML text</p>",
            "url": "https://example.com/blog/nazvanie-stati"
          }
        }
      }
    ]
  }
}
```

## POST `/api/blog/media`

Uploads an article image into `storage/app/public/blog/YYYY/MM`.

Request type: `multipart/form-data`

Fields:
- `file` required image file. Allowed MIME: `image/jpeg`, `image/png`, `image/webp`, `image/gif`.
- `alt` optional string, max 255.
- `title` optional string, max 255.
- `slug` optional string used as filename base.

Limits:
- max file size: 5 MB.

Example:
```bash
curl -X POST "https://example.com/api/blog/media" \
  -H "X-Api-Key: <key>" \
  -F "file=@/path/to/image.jpg" \
  -F "alt=Article image alt" \
  -F "title=Article image title" \
  -F "slug=article-image"
```

Success response:
```json
{
  "status": "ok",
  "data": {
    "media": {
      "path": "blog/2026/05/article-image.jpg",
      "url": "https://example.com/storage/blog/2026/05/article-image.jpg",
      "disk": "public",
      "mime": "image/jpeg",
      "size": 123456,
      "original_name": "image.jpg",
      "alt": "Article image alt",
      "title": "Article image title"
    }
  }
}
```

Use `data.media.path` as the `image` value when creating or updating an article.

## POST `/api/blog/posts`

Creates a blog article. Supports a WordPress-like single-language payload and an extended multilingual payload. `slug` can be set explicitly. For multilingual articles, send a separate `slug` inside every locale.

Important:
- `status=draft` creates an admin draft and does not publish it on the public blog.
- `status=published` or `status=publish` publishes the article.
- `categories` must contain existing `blog_categories.id` values.
- Base table fields use `en` by default (`app.fallback_locale`); other locales are saved into `translations`.
- `author_id` is optional. If sent, it must exist in `blog_authors`; if omitted, API uses the first author by `sort/id`.
- `is_idea`, `is_stories`, `is_blogwant` fill the three admin columns: "Идея", "Истории людей", "Как создают шедевры".
- Repeated `idempotency_key` or repeated slug returns `200` with `status=duplicate`.
- To upload images, call `POST /api/blog/media` first and pass the returned `data.media.path` as `image` or `image_preview`; `featured_media` is stored only as `wp_media:{id}` placeholder when provided.

WordPress-like request:
```json
{
  "idempotency_key": "article-2026-05-20-canvas-001",
  "type": "post",
  "status": "draft",
  "lang": "en",
  "slug": "canvas-prints-as-a-gift",
  "tags": ["canvas", "gift"],
  "categories": [2],
  "author_id": 1,
  "image": "blog/2026/05/article-main.jpg",
  "image_preview": "blog/2026/05/article-preview.jpg",
  "is_idea": true,
  "is_stories": false,
  "is_blogwant": true,
  "title": "Canvas Prints as a Gift",
  "excerpt": "Short article excerpt.",
  "content": "<p>Article HTML...</p>",
  "featured_media": 123,
  "meta": {
    "rank_math_title": "SEO title",
    "rank_math_description": "SEO description",
    "rank_math_focus_keyword": "canvas gift",
    "rank_math_canonical_url": "",
    "rank_math_robots": ["index", "follow"],
    "rank_math_primary_category": 2
  }
}
```

Multilingual request:
```json
{
  "idempotency_key": "article-2026-05-20-canvas-multilang-001",
  "status": "draft",
  "categories": [2],
  "author_id": 1,
  "image": "blog/2026/05/article-main.jpg",
  "image_preview": "blog/2026/05/article-preview.jpg",
  "tags": ["canvas", "gift"],
  "is_idea": false,
  "is_stories": true,
  "is_blogwant": false,
  "translations": {
    "ru": {
      "title": "Картины на холсте для подарка",
      "slug": "kartiny-na-holste-dlya-podarka",
      "excerpt": "Короткое описание.",
      "content": "<p>HTML статьи...</p>",
      "meta_title": "SEO title RU",
      "meta_desc": "SEO description RU"
    },
    "en": {
      "title": "Canvas Prints as a Gift",
      "slug": "canvas-prints-as-a-gift",
      "excerpt": "Short description.",
      "content": "<p>Article HTML...</p>",
      "meta_title": "SEO title EN",
      "meta_desc": "SEO description EN"
    }
  }
}
```

Success response:
```json
{
  "status": "ok",
  "data": {
    "post": {
      "id": 80,
      "status": "draft",
      "image": "https://example.com/storage/blog/2026/05/article-main.jpg",
      "image_path": "blog/2026/05/article-main.jpg",
      "image_preview": "https://example.com/storage/blog/2026/05/article-preview.jpg",
      "image_preview_path": "blog/2026/05/article-preview.jpg",
      "author": {
        "id": 1,
        "name": "Admin"
      },
      "flags": {
        "is_idea": false,
        "is_stories": true,
        "is_blogwant": false
      },
      "category_ids": [2],
      "translations": {
        "ru": {
          "title": "Картины на холсте для подарка",
          "slug": "kartiny-na-holste-dlya-podarka",
          "meta_title": "SEO title RU",
          "meta_desc": "SEO description RU",
          "excerpt": "Короткое описание.",
          "text": "<p>HTML статьи...</p>",
          "url": "https://example.com/blog/kartiny-na-holste-dlya-podarka"
        }
      }
    }
  }
}
```

## PATCH `/api/blog/posts/{post_id}`

Partially updates an existing article. Send only fields that should be changed.

Common use cases:
- publish draft: `{"status":"published"}`
- change categories: `{"categories":[2,5]}`
- change author: `{"author_id":1}`
- change images: `{"image":"blog/2026/05/main.jpg","image_preview":"blog/2026/05/preview.jpg"}`
- change admin flags: `{"is_idea":true,"is_stories":false,"is_blogwant":true}`
- update one language slug/content:

```json
{
  "status": "draft",
  "categories": [2],
  "author_id": 1,
  "image": "blog/2026/05/article-main-updated.jpg",
  "image_preview": "blog/2026/05/article-preview-updated.jpg",
  "is_idea": true,
  "is_stories": false,
  "is_blogwant": true,
  "translations": {
    "ru": {
      "title": "Новое название",
      "slug": "novyj-slug"
    },
    "en": {
      "slug": "new-english-slug",
      "content": "<p>Updated English HTML...</p>"
    }
  }
}
```

Success response uses the same `data.post` shape as `POST /api/blog/posts`.

Errors:
- `404 POST_NOT_FOUND` when `post_id` does not exist.
- `400 VALIDATION_ERROR` when category ids or author id are unknown, or a new slug belongs to another post.


## Перенос Blog API из 5fa03e77 на Laravel 13 — 2026-10-27

Статус: DONE локальная реализация и автоматическая проверка; production rollout/browser QA не выполнены. Подробные evidence: MIGRATION_PROGRESS.md, BLOG-5FA03E77.

- GET /api/blog/categories, /api/blog/authors, /api/blog/posts: meta.pilot_contract=3, story_image=true, alt_storage=image_alt_suggestions. Posts возвращают revision/content_revision, external_id/idempotency_key, media_sha256, story_alt_mode, image_user; ALT и present_fields возвращаются по фактически сохранённым локалям. Native поля blog_posts — EN (voyager.multilingual.default); другие локали без fallback.
- POST /api/blog/posts: image_user и translations.*.{image_alt,image_preview_alt,image_user_alt}; automation_contract=3 требует draft, шесть полных de/ee/en/lt/lv/ru с разными slug, idempotency_key/external_id, пять проверенных локальных файлов media_sha256 и story_alt_mode. Decorative image_user_alt должен быть пустым, descriptive — непустым. ALT сохраняются как applied в существующую image_alt_suggestions, новые таблицы/колонки не добавлены. Ответ201 status=ok; точный повтор200 status=duplicate; изменённый/непроверяемый payload с тем же ключом409 CONFLICT. Fingerprint в api_payload._creation_sha256 переживает PATCH; уникальный индекс защищает гонку.
- PATCH /api/blog/posts/{id}: обязательны expected_revision (64hex из GET) и expected_status; сравнение внутри DB transaction после lockForUpdate статьи/переводов/категорий/ALT. Отсутствующая revision428 PRECONDITION_REQUIRED; устаревшая revision/status409 CONFLICT. Успех200 с новой revision. Счётчик просмотров не меняет revision. EN обязателен при передаче translations, чужой язык не подставляется в native поля.
- POST /api/blog/media: прежние MIME/size ограничения сохранены; путь blog/api/<SHA256>.<MIME extension>, повтор файла использует тот же путь.
- Auth прежний X-Api-Key из .env/config services.sa_integration.api_key. Нет нового CRM/SA webhook/event_id контракта: blog использует idempotency_key; CRM/SA event_id и orders/чаты не менялись.
- Ошибки: прежний совместимый JSON status=error,error={code,message,details};400 VALIDATION_ERROR для обычной валидации,422 VALIDATION_ERROR для семантики контракта/ALT/хешей,409 CONFLICT,428 PRECONDITION_REQUIRED,503 AUTOMATION_UNAVAILABLE если auto_observer включён;401 auth/404 неизвестная статья прежние. Отклонения логируются без API key/токенов/тела статьи; ошибки обработки остаются в штатном exception logging.
- БД: migration 2026_10_09_180000_unique_blog_idempotency.php добавляет blog_posts_idempotency_unique. На duplicates останавливается без изменения данных; nullable keys допускаются. Локальная MySQL: duplicate_groups0, применена, индекс подтверждён. На сервере выполнить php artisan migrate --path=database/migrations/2026_10_09_180000_unique_blog_idempotency.php --force после доставки файла.
- Что осталось статическим: только исходные translation PHP arrays и локализованные контентные строки; ALT API пишет реальные existing records, не вызывает платный генератор. Slider subtitle остаётся в canvas_slider/translations.
- Проверки:69PHPtests506assertions и3JS PASS, Blade/PHP lint/diffcheck PASS. Следующий шаг: серверная миграция/Blade cache; обновить PATCH-клиентов для revision, затем browser QA статьи и симулятора. Hardcoded Voyager edit для записи82 не нужен в Filament; транзакции/lock BREAD уже реализованы.
