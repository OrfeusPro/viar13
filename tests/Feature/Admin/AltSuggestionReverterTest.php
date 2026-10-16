<?php

namespace Tests\Feature\Admin;

use App\Models\ImageAltApplyLog;
use App\Models\ImageAltSuggestion;
use App\Services\AltGeneration\AltSuggestionReverter;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AltSuggestionReverterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once database_path('migrations/2026_07_01_120000_create_image_alt_suggestions_table.php');
        require_once database_path('migrations/2026_07_01_150000_create_image_alt_apply_log_table.php');
        (new \CreateImageAltSuggestionsTable)->up();
        (new \CreateImageAltApplyLogTable)->up();
        Schema::create('alt_revert_entities', function (Blueprint $table): void {
            $table->id();
            $table->text('image_alt')->nullable();
            $table->text('text')->nullable();
        });
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name');
            $table->string('column_name');
            $table->unsignedBigInteger('foreign_key');
            $table->string('locale');
            $table->text('value')->nullable();
        });
        Schema::create('alt_revert_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id');
            $table->json('custom_properties');
        });
        config(['alt_generation.targets' => [AltRevertEntity::class => ['html_fields' => ['text']]],
            'voyager.multilingual.default' => 'en']);
    }

    private function suggestion(?AltRevertEntity $entity = null): ImageAltSuggestion
    {
        return ImageAltSuggestion::create(['imageable_type' => AltRevertEntity::class,
            'imageable_id' => $entity?->id ?? 999, 'image_path' => '/storage/test.png',
            'locale' => 'ru', 'status' => 'applied', 'applied_at' => now(), 'error' => 'Old']);
    }

    private function log(ImageAltSuggestion $suggestion, string $column, ?string $old, array $extra = []): void
    {
        ImageAltApplyLog::create(array_merge(['suggestion_id' => $suggestion->id,
            'field' => $column, 'column_written' => $column, 'old_value' => $old, 'locale' => 'ru'], $extra));
    }

    public function test_revert_restores_oldest_values_keeps_logs_and_is_repeatable(): void
    {
        $entity = AltRevertEntity::create(['image_alt' => 'Last', 'text' => 'English']);
        $suggestion = $this->suggestion($entity);
        $this->log($suggestion, 'image_alt', null);
        $this->log($suggestion, 'image_alt', 'Middle');
        $service = app(AltSuggestionReverter::class);
        $service->revert($suggestion);
        $this->assertNull($entity->fresh()->image_alt);
        $this->assertSame('approved', $suggestion->fresh()->status);
        $this->assertNull($suggestion->fresh()->applied_at);
        $this->assertNull($suggestion->fresh()->error);
        $this->assertSame(2, $suggestion->applyLogs()->count());
        $service->revert($suggestion);
        $this->assertNull($entity->fresh()->image_alt);
    }

    public function test_html_translation_restores_ru_without_changing_en(): void
    {
        $entity = AltRevertEntity::create(['text' => 'English']);
        $suggestion = $this->suggestion($entity);
        $this->log($suggestion, 'text', '<p>Русский исходник</p>');
        app(AltSuggestionReverter::class)->revert($suggestion);
        $this->assertSame('English', $entity->fresh()->text);
        $this->assertDatabaseHas('translations', ['foreign_key' => $entity->id,
            'locale' => 'ru', 'value' => '<p>Русский исходник</p>']);
    }

    public function test_media_property_restore_and_remove_do_not_touch_other_properties(): void
    {
        $entity = AltRevertEntity::create([]);
        $media = AltRevertMedia::create(['owner_id' => $entity->id,
            'custom_properties' => ['image_alt_ru' => 'New', 'image_title_ru' => 'Title', 'other' => 'Keep']]);
        $suggestion = $this->suggestion($entity);
        $suggestion->update(['prompt_context' => ['image' => ['collection_name' => 'photos']]]);
        $this->log($suggestion, 'image_alt_ru', 'Old', ['field' => 'media:photos', 'source_type' => 'media', 'media_id' => $media->id]);
        $this->log($suggestion, 'image_title_ru', null, ['field' => 'media:photos', 'source_type' => 'media', 'media_id' => $media->id]);
        app(AltSuggestionReverter::class)->revert($suggestion);
        $this->assertSame(['image_alt_ru' => 'Old', 'other' => 'Keep'], $media->fresh()->custom_properties);
    }

    public function test_missing_field_rolls_back_previous_writes_and_status(): void
    {
        $entity = AltRevertEntity::create(['image_alt' => 'New']);
        $suggestion = $this->suggestion($entity);
        $this->log($suggestion, 'removed_column', 'Old');
        $this->log($suggestion, 'image_alt', 'Before');
        try {
            app(AltSuggestionReverter::class)->revert($suggestion);
            $this->fail('Missing field must fail the transaction.');
        } catch (ValidationException) {
            $this->assertSame('New', $entity->fresh()->image_alt);
            $this->assertSame('applied', $suggestion->fresh()->status);
        }
    }

    public function test_virtual_apply_and_missing_source(): void
    {
        $virtual = $this->suggestion();
        app(AltSuggestionReverter::class)->revert($virtual);
        $this->assertSame('approved', $virtual->fresh()->status);
        $missing = $this->suggestion();
        $this->log($missing, 'image_alt', 'Old');
        $this->expectException(ValidationException::class);
        app(AltSuggestionReverter::class)->revert($missing);
    }

    public function test_missing_media_rolls_back_field_write_and_non_applied_without_logs_fails(): void
    {
        $entity = AltRevertEntity::create(['image_alt' => 'New']);
        $suggestion = $this->suggestion($entity);
        $this->log($suggestion, 'image_alt_ru', 'Old', ['field' => 'media:photos', 'source_type' => 'media', 'media_id' => 999]);
        $this->log($suggestion, 'image_alt', 'Before');
        try {
            app(AltSuggestionReverter::class)->revert($suggestion);
            $this->fail('Missing media must fail.');
        } catch (ValidationException) {
            $this->assertSame('New', $entity->fresh()->image_alt);
            $this->assertSame('applied', $suggestion->fresh()->status);
        }
        $noLogs = $this->suggestion($entity);
        $noLogs->update(['status' => 'pending']);
        $this->expectException(ValidationException::class);
        app(AltSuggestionReverter::class)->revert($noLogs);
    }

    public function test_preview_url_is_usable_by_filament_without_treating_url_as_disk_path(): void
    {
        config(['filesystems.disks.public.url' => 'https://viar13.loc/storage']);
        $suggestion = new ImageAltSuggestion(['image_path' => 'site-images/picture.png']);
        $column = ImageColumn::make('preview_url');
        $this->assertSame('https://viar13.loc/storage/site-images/picture.png', $suggestion->preview_url);
        $this->assertSame($suggestion->preview_url, $column->getImageUrl($suggestion->preview_url));
        $suggestion->image_path = '/storage/site-images/picture.png';
        $this->assertStringEndsWith('/storage/site-images/picture.png', $column->getImageUrl($suggestion->preview_url));
        $suggestion->image_path = 'https://example.test/image.png';
        $this->assertSame($suggestion->image_path, $suggestion->preview_url);
    }

    public function test_webp_filter_and_statistics_follow_unique_original_paths(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::disk('public')->put('photos/one.webp', 'fixture');
        $helper = app(\App\Services\AltGeneration\AltWebpInfo::class);
        foreach (['photos/one.jpg', '/storage/photos/one.jpg?version=1', 'https://example.test/public/storage/photos/one.jpg#x', 'photos/one.webp'] as $path) {
            $this->assertTrue($helper->info($path)['exists']);
            $this->assertSame('photos/one.webp', $helper->info($path)['path']);
        }
        $this->assertNull($helper->info('data:image/png;base64,x')['path']);
        $this->assertNull($helper->info('photos/no-extension')['path']);
        foreach (['photos/one.jpg', 'photos/one.jpg', 'photos/two.png'] as $path) {
            ImageAltSuggestion::create(['image_path' => $path, 'status' => 'pending']);
        }
        $stats = $helper->stats(ImageAltSuggestion::query());
        $this->assertSame(['total' => 2, 'with_webp' => 1, 'without_webp' => 1, 'coverage_percent' => 50.0], $stats);
        $present = ImageAltSuggestion::query(); $helper->filter($present, true);
        $this->assertSame(2, $present->count());
        $missing = ImageAltSuggestion::query(); $helper->filter($missing, false);
        $this->assertSame(['photos/two.png'], $missing->pluck('image_path')->all());
        $this->assertSame(1, $helper->stats($missing)['total']);
        $empty = ImageAltSuggestion::where('image_path', 'photos/two.png'); $helper->filter($empty, true);
        $this->assertSame(0, $empty->count());
    }

    public function test_real_spatie_model_restores_properties_without_changing_file_identity(): void
    {
        require_once database_path('migrations/2021_11_19_144609_create_media_table.php');
        (new \CreateMediaTable)->up();
        (require database_path('migrations/2026_09_25_120000_add_generated_conversions_to_media_table.php'))->up();
        $entity = AltRevertSpatieEntity::create([]);
        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::create([
            'model_type' => AltRevertSpatieEntity::class, 'model_id' => $entity->id,
            'collection_name' => 'photos', 'name' => 'Existing', 'file_name' => 'existing.png',
            'mime_type' => 'image/png', 'disk' => 'public', 'size' => 10,
            'manipulations' => [], 'custom_properties' => ['image_alt_ru' => 'New', 'other' => 'Keep'],
            'generated_conversions' => [], 'responsive_images' => [],
        ]);
        $suggestion = $this->suggestion($entity);
        $suggestion->update(['imageable_type' => AltRevertSpatieEntity::class,
            'prompt_context' => ['image' => ['collection_name' => 'photos']]]);
        $this->log($suggestion, 'image_alt_ru', null, ['field' => 'media:photos', 'source_type' => 'media', 'media_id' => $media->id]);
        app(AltSuggestionReverter::class)->revert($suggestion);
        $this->assertSame(['other' => 'Keep'], $media->fresh()->custom_properties);
        $this->assertSame('existing.png', $media->fresh()->file_name);
        $this->assertSame('public', $media->fresh()->disk);
        $this->assertSame(10, $media->fresh()->size);
    }
}

class AltRevertEntity extends Model
{
    protected $table = 'alt_revert_entities';

    protected $guarded = [];

    public $timestamps = false;

    public function getTranslatableAttributes(): array
    {
        return ['text'];
    }

    public function getMedia(string $collection)
    {
        return AltRevertMedia::where('owner_id', $this->id)->get();
    }
}

class AltRevertMedia extends Model
{
    protected $table = 'alt_revert_media';

    protected $guarded = [];

    protected $casts = ['custom_properties' => 'array'];

    public $timestamps = false;

    public function getUrl(): string
    {
        return '/storage/test.png';
    }

    public function setCustomProperty(string $key, $value): void
    {
        $props = $this->custom_properties;
        $props[$key] = $value;
        $this->custom_properties = $props;
    }

    public function forgetCustomProperty(string $key): void
    {
        $props = $this->custom_properties;
        unset($props[$key]);
        $this->custom_properties = $props;
    }
}

class AltRevertSpatieEntity extends AltRevertEntity
{
    public function getMedia(string $collection)
    {
        return \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', static::class)
            ->where('model_id', $this->id)->where('collection_name', $collection)->get();
    }
}
