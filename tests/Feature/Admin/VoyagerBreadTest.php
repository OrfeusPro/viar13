<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\VoyagerBread;
use App\Filament\Pages\VoyagerBreadEdit;
use App\Filament\Pages\VoyagerMenuItems;
use App\Filament\Pages\VoyagerUiTranslations;
use App\Filament\Pages\VoyagerSettings;
use App\Filament\Bread\BreadRegistry;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class VoyagerBreadTest extends TestCase
{
    public function test_bread_file_previews_encode_literal_voyager_filenames(): void
    {
        Storage::fake('public');
        $paths = ['gallery-items/BeautyART/004 Коррекция.jpg', 'gallery-items/a#b%20&c.jpg'];
        foreach ($paths as $path) {
            Storage::disk('public')->put($path, UploadedFile::fake()->image('image.jpg')->getContent());
        }

        $field = \App\Filament\Bread\BreadFileUpload::make('images')->disk('public')->multiple();
        foreach ($paths as $path) {
            $preview = $field->getUploadedFile($path, null);
            $encoded = implode('/', array_map(rawurlencode(...), explode('/', $path)));
            $this->assertSame(Storage::disk('public')->url($encoded), $preview['url']);
            $this->assertSame(basename($path), $preview['name']);
            $this->assertSame('image/jpeg', $preview['type']);
            $this->assertSame($path, rawurldecode($encoded));
        }
        $this->assertNull($field->getUploadedFile('gallery-items/missing.jpg', null));
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('roles', function (Blueprint $table): void {
            $table->id(); $table->string('name'); $table->string('display_name')->nullable(); $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id(); $table->string('key'); $table->string('table_name')->nullable(); $table->timestamps();
        });
        Schema::create('permission_role', function (Blueprint $table): void {
            $table->foreignId('permission_id'); $table->foreignId('role_id');
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id(); $table->foreignId('role_id')->nullable(); $table->string('email');
            $table->string('password'); $table->string('last_ip')->nullable();
            $table->text('registration_page')->nullable(); $table->text('referrer_url')->nullable();
            $table->json('utm_parameters')->nullable(); $table->text('user_agent')->nullable();
            $table->rememberToken(); $table->timestamps();
        });
        Schema::create('user_roles', function (Blueprint $table): void {
            $table->foreignId('user_id'); $table->foreignId('role_id');
        });
        Schema::create('data_types', function (Blueprint $table): void {
            $table->id(); $table->string('name'); $table->string('slug'); $table->string('model_name');
            $table->string('display_name_plural')->nullable();
        });
        Schema::create('data_rows', function (Blueprint $table): void {
            $table->id(); $table->foreignId('data_type_id'); $table->string('field'); $table->string('type');
            $table->string('display_name')->nullable(); $table->boolean('required')->default(false);
            $table->boolean('browse')->default(true); $table->boolean('read')->default(true);
            $table->boolean('edit')->default(true); $table->boolean('add')->default(true);
            $table->boolean('delete')->default(true); $table->text('details')->nullable(); $table->integer('order')->default(0);
        });
        Schema::create('pages', function (Blueprint $table): void {
            $table->id(); $table->string('title')->nullable(); $table->string('meta_description')->nullable();
            $table->foreignId('author_id')->nullable();
            $table->string('image')->nullable();
            $table->text('flags')->nullable();
            $table->timestamps();
        });
        Schema::create('translations', function (Blueprint $table): void {
            $table->id(); $table->string('table_name'); $table->string('column_name');
            $table->unsignedBigInteger('foreign_key'); $table->string('locale'); $table->text('value')->nullable();
        });

        $typeId = DB::table('data_types')->insertGetId([
            'name' => 'pages', 'slug' => 'pages', 'model_name' => \App\Models\Page::class,
            'display_name_plural' => 'Pages',
        ]);
        foreach (['title', 'meta_description'] as $index => $field) {
            DB::table('data_rows')->insert([
                'data_type_id' => $typeId, 'field' => $field, 'type' => 'text',
                'display_name' => $field, 'details' => '{}', 'order' => $index,
            ]);
        }
    }

    public function test_metadata_changes_are_read_without_code_generation(): void
    {
        $registry = app(BreadRegistry::class);
        $type = $registry->type('pages');
        $this->assertCount(2, $registry->editableRows($type, 'edit'));

        DB::table('data_rows')->where('field', 'title')->update(['edit' => false]);
        $this->assertCount(1, $registry->editableRows($type, 'edit'));

        DB::table('data_rows')->insert([
            'data_type_id' => $type->id, 'field' => 'image', 'type' => 'image',
            'display_name' => 'Изображение', 'details' => '{}', 'order' => 3,
        ]);
        $this->assertCount(2, $registry->editableRows($type, 'edit'));
    }

    public function test_empty_form_cannot_create_or_edit_a_record(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'add_pages', 'edit_pages']);
        DB::table('data_rows')->update(['add' => false, 'edit' => false]);
        $id = DB::table('pages')->insertGetId(['title' => 'Existing']);

        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->assertDontSee('Создать запись')
            ->call('openCreate')->assertStatus(409);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->call('openEdit', $id)->assertStatus(409);
        $this->assertDatabaseCount('pages', 1);
    }

    public function test_requires_browse_permission(): void
    {
        $this->admin(['browse_admin']);
        $this->get('/filament/bread/pages')->assertForbidden();
    }

    public function test_edit_route_groups_metadata_tabs_and_saves_all_fields(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        DB::table('data_rows')->where('field', 'meta_description')
            ->update(['details' => json_encode(['tab_title' => 'SEO'])]);
        $id = DB::table('pages')->insertGetId(['title' => 'Before', 'meta_description' => 'Before SEO']);
        $this->get('/filament/bread/pages/' . $id . '/edit?type=2')->assertOk();

        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->assertSee('Main')->assertSee('SEO')
            ->set('data.title', 'After')
            ->set('data.meta_description', 'After SEO')
            ->call('save')->assertHasNoErrors()->assertSet('editing', true)
            ->call('changeLocale', 'ru')
            ->set('data.meta_description', 'Русское SEO')
            ->call('save')->assertHasNoErrors()->assertSet('editing', true);
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => 'After', 'meta_description' => 'After SEO']);
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'locale' => 'ru', 'value' => 'Русское SEO']);
        Livewire::withQueryParams(['type' => '2'])->test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('cancel')->assertRedirect(VoyagerBread::getUrl(['type' => 'pages']) . '?type=2');
    }

    public function test_edit_route_requires_edit_permission_and_existing_record(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        $id = DB::table('pages')->insertGetId(['title' => 'Protected']);
        $this->get('/filament/bread/pages/' . $id . '/edit')->assertForbidden();
        Permission::query()->firstOrCreate(['key' => 'edit_pages'])->roles()->attach(auth('filament')->user()->role_id);
        $this->app->forgetInstance(BreadRegistry::class);
        $this->getJson('/filament/bread/pages/999/edit')->assertNotFound();
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => 'Protected']);
    }

    public function test_read_only_audit_checks_the_available_bread_type(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'add_pages', 'edit_pages']);
        DB::table('pages')->insert(['title' => 'Audit sample']);

        $this->assertSame(0, \Illuminate\Support\Facades\Artisan::call('bread:audit'));
        $output = \Illuminate\Support\Facades\Artisan::output();
        $this->assertStringContainsString('pages: OK', $output);
        $this->assertStringContainsString('Checked 1 types; 0 with issues', $output);
        $this->assertDatabaseCount('pages', 1);
    }

    public function test_audit_reports_invalid_relationship_metadata(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages']);
        DB::table('data_rows')->insert([
            'data_type_id' => DB::table('data_types')->where('slug', 'pages')->value('id'),
            'field' => 'missing_relation', 'type' => 'relationship',
            'display_name' => 'Missing relation', 'details' => json_encode([
                'type' => 'belongsTo', 'table' => 'missing_table',
                'column' => 'author_id', 'key' => 'id', 'label' => 'name',
            ]),
            'order' => 3,
        ]);

        $this->assertSame(1, \Illuminate\Support\Facades\Artisan::call('bread:audit'));
        $this->assertStringContainsString('недоступна связь missing_relation', \Illuminate\Support\Facades\Artisan::output());
    }

    public function test_saves_base_and_ru_translation_separately(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        $id = DB::table('pages')->insertGetId(['title' => 'English', 'meta_description' => 'Base']);
        $this->get('/filament/bread/pages')->assertOk();

        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->call('openEdit', $id)
            ->set('data.meta_description', 'Changed base')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => 'Changed base']);

        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->call('openEdit', $id)
            ->call('changeLocale', 'ru')
            ->set('data.meta_description', 'Русский текст')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('translations', [
            'table_name' => 'pages', 'column_name' => 'meta_description',
            'foreign_key' => $id, 'locale' => 'ru', 'value' => 'Русский текст',
        ]);
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => 'Changed base']);
    }

    public function test_language_switch_keeps_shared_fields_and_unsaved_translation_drafts(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('pages/original.jpg', UploadedFile::fake()->image('original.jpg')->getContent());
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        $type = DB::table('data_types')->where('slug', 'pages')->value('id');
        DB::table('data_rows')->insert(['data_type_id' => $type, 'field' => 'image', 'type' => 'image', 'display_name' => 'Image', 'details' => '{}', 'order' => 3]);
        $id = DB::table('pages')->insertGetId(['title' => 'Shared', 'meta_description' => 'Base', 'image' => 'pages/original.jpg']);
        $component = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->set('data.title', 'Shared draft')
            ->set('data.meta_description', 'EN draft')
            ->call('changeLocale', 'ru')
            ->assertSee('Image')
            ->assertSee('meta_description (RU)')
            ->assertSet('data.title', 'Shared draft')
            ->set('data.meta_description', 'RU draft')
            ->call('changeLocale', 'en')
            ->assertSet('data.meta_description', 'EN draft')
            ->call('changeLocale', 'ru')
            ->assertSet('data.meta_description', 'RU draft')
            ->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => 'Shared draft', 'meta_description' => 'Base', 'image' => 'pages/original.jpg']);
        $this->assertDatabaseHas('translations', ['table_name' => 'pages', 'column_name' => 'meta_description', 'foreign_key' => $id, 'locale' => 'ru', 'value' => 'RU draft']);
        $component->call('changeLocale', 'en')->assertSet('data.meta_description', 'EN draft');
        $this->assertSame(['en', 'ru', 'lv', 'ee', 'lt', 'de', 'pl'], app(BreadRegistry::class)->locales());
    }

    private function altFixture(): array
    {
        require_once database_path('migrations/2026_07_01_120000_create_image_alt_suggestions_table.php');
        require_once database_path('migrations/2026_07_01_150000_create_image_alt_apply_log_table.php');
        (new \CreateImageAltSuggestionsTable)->up();
        (new \CreateImageAltApplyLogTable)->up();
        $type = DB::table('data_types')->where('slug', 'pages')->value('id');
        DB::table('data_rows')->insert(['data_type_id' => $type, 'field' => 'image', 'type' => 'image', 'display_name' => 'Image', 'details' => '{}', 'order' => 3]);
        $id = DB::table('pages')->insertGetId(['title' => 'Shared', 'image' => 'pages/photo.jpg']);
        $rows = [];
        foreach (['en', 'ru'] as $locale) {
            $rows[$locale] = \App\Models\ImageAltSuggestion::create([
                'imageable_type' => \App\Models\Page::class, 'imageable_id' => $id,
                'field' => 'image', 'image_path' => 'pages/photo.jpg', 'locale' => $locale,
                'suggested_alt' => 'Original ' . $locale, 'suggested_title' => 'Title ' . $locale,
                'status' => 'applied', 'prompt_context' => ['image' => ['source_type' => 'field', 'field' => 'image']],
            ]);
        }

        return [$id, $rows];
    }

    public function test_holst_shared_price_and_false_toggles_save_from_ru_without_changing_image_path(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('gallery-holsts/original.jpg', UploadedFile::fake()->image('original.jpg')->getContent());
        Schema::create('gallery_holsts', function (Blueprint $table): void {
            $table->id(); $table->string('name')->nullable(); $table->string('density')->nullable();
            $table->string('hint')->nullable(); $table->decimal('price', 8, 2);
            $table->boolean('hit'); $table->boolean('default'); $table->string('image')->nullable(); $table->timestamps();
        });
        $type = DB::table('data_types')->insertGetId(['name' => 'gallery_holsts', 'slug' => 'gallery-holsts', 'model_name' => \App\Models\GalleryHolst::class, 'display_name_plural' => 'Gallery Holsts']);
        foreach (['name' => 'text', 'density' => 'text', 'hint' => 'text', 'price' => 'number', 'hit' => 'checkbox', 'image' => 'image', 'default' => 'checkbox'] as $field => $kind) {
            DB::table('data_rows')->insert(['data_type_id' => $type, 'field' => $field, 'type' => $kind, 'display_name' => ucfirst($field), 'required' => $kind !== 'image', 'details' => '{}']);
        }
        $id = DB::table('gallery_holsts')->insertGetId(['name' => 'Gloss', 'density' => 'Shine', 'hint' => 'Hint', 'price' => 1.05, 'hit' => true, 'default' => true, 'image' => 'gallery-holsts/original.jpg']);
        $this->admin(['browse_admin', 'browse_gallery_holsts', 'edit_gallery_holsts']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'gallery-holsts', 'record' => $id])
            ->call('changeLocale', 'ru')->assertSee('Price')->assertSee('Image')->assertSee('Default')
            ->set('data.name', 'Глянцевый')->set('data.price', 2.15)->set('data.hit', false)->set('data.default', false)
            ->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('gallery_holsts', ['id' => $id, 'name' => 'Gloss', 'price' => 2.15, 'hit' => false, 'default' => false, 'image' => 'gallery-holsts/original.jpg']);
        $this->assertDatabaseHas('translations', ['table_name' => 'gallery_holsts', 'column_name' => 'name', 'foreign_key' => $id, 'locale' => 'ru', 'value' => 'Глянцевый']);
    }

    public function test_alt_panel_saves_scoped_language_and_reuses_application_workflow(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages', 'browse_alt_suggestions', 'edit_alt_suggestions']);
        [$id, $rows] = $this->altFixture();
        $component = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('openAltPanel')->assertSee('ALT/TITLE изображений')
            ->call('changeAltLocale', 'ru')
            ->set('altValues.' . $rows['ru']->id . '.alt', 'Новый ALT')
            ->call('saveAltPanel', 'language', $rows['en']->id)->assertHasNoErrors();
        $this->assertDatabaseHas('image_alt_suggestions', ['id' => $rows['ru']->id, 'approved_alt' => 'Новый ALT', 'status' => 'applied']);
        $this->assertDatabaseHas('image_alt_suggestions', ['id' => $rows['en']->id, 'approved_alt' => null]);
        $this->assertDatabaseHas('pages', ['id' => $id, 'image' => 'pages/photo.jpg']);
        $component->set('altValues.' . $rows['en']->id . '.title', str_repeat('x', 500))
            ->call('saveAltPanel', 'image', $rows['en']->id)->assertHasErrors(['altValues.' . $rows['en']->id . '.title']);
        $this->assertDatabaseHas('image_alt_suggestions', ['id' => $rows['en']->id, 'approved_title' => null]);
        $component->set('altValues.' . $rows['en']->id . '.title', 'Updated EN')
            ->call('saveAltPanel', 'image', $rows['en']->id)->assertHasNoErrors();
        $this->assertDatabaseHas('image_alt_suggestions', ['id' => $rows['en']->id, 'approved_title' => 'Updated EN', 'status' => 'applied']);
        $before = app()->getLocale();
        $component->instance()->saveAltPanel('all');
        $this->assertSame($before, app()->getLocale());
    }

    public function test_alt_panel_rejects_foreign_record_and_missing_edit_permission(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages', 'browse_alt_suggestions']);
        [$id, $rows] = $this->altFixture();
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('openAltPanel')->call('saveAltPanel', 'all')->assertForbidden();
        $role = Role::first();
        $role->permissions()->attach(Permission::create(['key' => 'edit_alt_suggestions']));
        $foreign = \App\Models\ImageAltSuggestion::create([
            'imageable_type' => \App\Models\Page::class, 'imageable_id' => $id + 1,
            'field' => 'image', 'image_path' => 'pages/foreign.jpg', 'locale' => 'en', 'status' => 'new',
        ]);
        config(['app.debug' => false]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('openAltPanel')->call('saveAltPanel', 'image', $foreign->id)->assertNotFound();
        $this->assertDatabaseHas('image_alt_suggestions', ['id' => $foreign->id, 'status' => 'new']);
    }

    public function test_creates_and_deletes_record_with_bread_permissions(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'add_pages', 'delete_pages']);

        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->call('openCreate')
            ->set('data.title', 'Created in isolated database')
            ->call('save')
            ->assertHasNoErrors();

        $id = DB::table('pages')->where('title', 'Created in isolated database')->value('id');
        $this->assertNotNull($id);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->call('deleteRecord', $id)
            ->assertHasNoErrors();
        $this->assertDatabaseCount('pages', 0);
    }

    public function test_browse_uses_all_metadata_columns_and_switches_translated_cells(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')
            ->update(['display_name' => 'Описание']);
        foreach (['id', 'image', 'flags', 'author_id'] as $order => $field) {
            DB::table('data_rows')->insert([
                'data_type_id' => $typeId, 'field' => $field, 'type' => 'text',
                'display_name' => strtoupper($field), 'details' => '{}', 'order' => $order + 3,
            ]);
        }
        $id = DB::table('pages')->insertGetId(['title' => 'English title', 'meta_description' => 'English description']);
        DB::table('translations')->insert([
            'table_name' => 'pages', 'column_name' => 'meta_description',
            'foreign_key' => $id, 'locale' => 'ru', 'value' => 'Русское описание',
        ]);

        $component = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        $this->assertCount(6, $component->instance()->records()['columns']);
        $this->assertContains('Описание', array_column($component->instance()->records()['columns'], 'label'));
        $this->assertSame('English description', $component->instance()->records()['rows']->first()->meta_description);

        $component->call('changeLocale', 'ru')->assertHasNoErrors();
        $this->assertSame('Русское описание', $component->instance()->records()['rows']->first()->meta_description);
        $this->assertSame('English title', $component->instance()->records()['rows']->first()->title);
        $component->call('openView', $id);
        $labels = collect($component->instance()->viewedFields())->pluck('value', 'label');
        $this->assertSame('Русское описание', $labels['Описание']);
        $this->assertSame('English title', $labels['title']);
    }

    public function test_image_fields_render_thumbnails_in_browse_and_view(): void
    {
        Storage::fake('public');
        $this->admin(['browse_admin', 'browse_pages', 'read_pages']);
        DB::table('data_rows')->insert([
            'data_type_id' => DB::table('data_types')->where('slug', 'pages')->value('id'),
            'field' => 'image', 'type' => 'image', 'display_name' => 'Картинка',
            'order' => 3, 'details' => '{}',
        ]);
        $path = 'pages/example.jpg';
        Storage::disk('public')->put($path, 'image');
        $id = DB::table('pages')->insertGetId(['title' => 'Page', 'image' => $path]);
        $url = Storage::disk('public')->url($path);

        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->assertSeeHtml('src="' . $url . '"')
            ->call('openView', $id)
            ->assertSeeHtml('src="' . $url . '"');
    }

    public function test_belongs_to_relation_uses_validated_column_and_table(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages']);
        $author = User::forceCreate(['email' => 'author@example.test', 'password' => Hash::make('password')]);
        $id = DB::table('pages')->insertGetId(['title' => 'Page']);
        DB::table('data_rows')->insert([
            'data_type_id' => DB::table('data_types')->where('slug', 'pages')->value('id'),
            'field' => 'page_belongsto_user_relationship', 'type' => 'relationship',
            'display_name' => 'Автор', 'order' => 3,
            'details' => json_encode([
                'type' => 'belongsTo', 'table' => 'users', 'column' => 'author_id',
                'key' => 'id', 'label' => 'email',
            ]),
        ]);
        $type = app(BreadRegistry::class)->type('pages');
        $this->assertCount(1, app(BreadRegistry::class)->belongsToRows($type, 'edit'));
        $browse = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->instance()->records();
        $this->assertContains('Автор', array_column($browse['columns'], 'label'));
        $this->assertNull($browse['rows']->first()->page_belongsto_user_relationship);

        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->call('openEdit', $id)
            ->set('data.author_id', $author->id)
            ->call('save')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'author_id' => $author->id]);
        $browse = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->instance()->records();
        $this->assertSame('author@example.test', $browse['rows']->first()->page_belongsto_user_relationship);
        $view = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openView', $id)->instance()->viewedFields();
        $this->assertSame('author@example.test', collect($view)->pluck('value', 'label')['Автор']);

        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->call('openEdit', $id)
            ->set('data.author_id', $author->id + 1000)
            ->call('save')
            ->assertHasErrors(['data.author_id']);
        $this->assertDatabaseHas('pages', ['id' => $id, 'author_id' => $author->id]);
    }

    public function test_delete_requires_separate_permission(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        $id = DB::table('pages')->insertGetId(['title' => 'Retain']);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->call('deleteRecord', $id)
            ->assertForbidden();
        $this->assertDatabaseHas('pages', ['id' => $id]);
    }

    public function test_validated_many_to_many_relation_syncs_pivot(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id(); $table->string('name');
        });
        Schema::create('category_page', function (Blueprint $table): void {
            $table->id(); $table->foreignId('page_id'); $table->foreignId('category_id');
        });
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        $id = DB::table('pages')->insertGetId(['title' => 'Page']);
        $categoryId = DB::table('categories')->insertGetId(['name' => 'Art']);
        $rowId = DB::table('data_rows')->insertGetId([
            'data_type_id' => DB::table('data_types')->where('slug', 'pages')->value('id'),
            'field' => 'page_belongstomany_category_relationship', 'type' => 'relationship',
            'display_name' => 'Категории', 'order' => 3,
            'details' => json_encode([
                'type' => 'belongsToMany', 'table' => 'categories', 'pivot_table' => 'category_page',
                'label' => 'name', 'tab_title' => 'Фильтры',
            ]),
        ]);
        $type = app(BreadRegistry::class)->type('pages');
        $this->assertCount(1, app(BreadRegistry::class)->manyToManyRows($type, 'edit'));

        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->call('openEdit', $id)
            ->set('data.__pivot_' . $rowId, [$categoryId])
            ->call('save')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('category_page', ['page_id' => $id, 'category_id' => $categoryId]);
    }

    public function test_image_upload_writes_to_public_disk_and_record(): void
    {
        Storage::fake('public');
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        $id = DB::table('pages')->insertGetId(['title' => 'Page']);
        DB::table('data_rows')->insert([
            'data_type_id' => DB::table('data_types')->where('slug', 'pages')->value('id'),
            'field' => 'image', 'type' => 'image', 'display_name' => 'Изображение',
            'order' => 3, 'details' => '{}',
        ]);

        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->call('openEdit', $id)
            ->set('data.image', UploadedFile::fake()->image('example.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $path = DB::table('pages')->where('id', $id)->value('image');
        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_spatie_media_collection_upload_and_properties(): void
    {
        Storage::fake('public');
        require_once database_path('migrations/2021_11_19_144609_create_media_table.php');
        (new \CreateMediaTable)->up();
        (require database_path('migrations/2026_09_25_120000_add_generated_conversions_to_media_table.php'))->up();
        Schema::create('canvas_new', function (Blueprint $table): void {
            $table->id(); $table->timestamps();
        });
        $this->admin(['browse_admin', 'browse_canvas_new', 'read_canvas_new', 'edit_canvas_new']);
        $typeId = DB::table('data_types')->insertGetId([
            'name' => 'canvas_new', 'slug' => 'canvas-new',
            'model_name' => \App\Models\CanvasNew::class,
            'display_name_plural' => 'Canvas New',
        ]);
        DB::table('data_rows')->insert([
            'data_type_id' => $typeId, 'field' => 'tab_examples', 'type' => 'adv_media_files',
            'display_name' => 'Примеры', 'order' => 1,
            'details' => json_encode(['extra_fields' => [
                'image_alt_ru' => ['type' => 'text', 'title' => 'ALT RU'],
                'layout' => ['type' => 'dropdown', 'title' => 'Layout', 'options' => ['wide' => 'Wide', 'square' => 'Square']],
            ]]),
        ]);
        $id = DB::table('canvas_new')->insertGetId([]);

        Livewire::test(VoyagerBread::class, ['type' => 'canvas-new'])
            ->call('openEdit', $id)
            ->set('mediaUpload', UploadedFile::fake()->image('example.jpg'))
            ->call('uploadMedia', 'tab_examples')
            ->assertHasNoErrors();
        $mediaId = DB::table('media')->where('collection_name', 'tab_examples')->value('id');
        $this->assertNotNull($mediaId);

        $mediaUrl = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($mediaId)->getUrl();
        $component = Livewire::test(VoyagerBread::class, ['type' => 'canvas-new']);
        $component->instance()->records();
        $component
            ->assertSeeHtml('src="' . $mediaUrl . '"')
            ->call('openView', $id);
        $component->assertSeeHtml('src="' . $mediaUrl . '"');

        Livewire::test(VoyagerBread::class, ['type' => 'canvas-new'])
            ->call('openEdit', $id)
            ->set('mediaProperties.' . $mediaId . '.image_alt_ru', 'Описание')
            ->call('saveMediaProperties', 'tab_examples', $mediaId)
            ->assertHasNoErrors();
        $this->assertSame('Описание', json_decode(DB::table('media')->where('id', $mediaId)->value('custom_properties'), true)['image_alt_ru']);

        $editor = Livewire::test(VoyagerBread::class, ['type' => 'canvas-new'])->call('openEdit', $id);
        $editor->set('mediaUploads.tab_examples', [UploadedFile::fake()->image('second.jpg'), UploadedFile::fake()->image('third.jpg')])
            ->call('uploadMedia', 'tab_examples')->assertHasNoErrors();
        $files = DB::table('media')->where('model_id', $id)->orderBy('order_column')->pluck('id')->all();
        $this->assertCount(3, $files);
        $editor->call('moveMedia', 'tab_examples', $files[2], -1)->assertHasNoErrors();
        $this->assertSame([$files[0], $files[2], $files[1]], DB::table('media')->where('model_id', $id)->orderBy('order_column')->pluck('id')->all());
        $editor->call('moveMedia', 'tab_examples', $files[0], -1);
        $this->assertSame(1, DB::table('media')->where('id', $files[0])->value('order_column'));
        $editor->set('mediaProperties.' . $mediaId . '.layout', 'invalid')
            ->call('saveMediaProperties', 'tab_examples', $mediaId)->assertHasErrors(['mediaProperties.' . $mediaId . '.layout']);
        $this->assertNull(json_decode(DB::table('media')->where('id', $mediaId)->value('custom_properties'), true)['layout']);
        $editor->set('mediaProperties.' . $mediaId . '.layout', 'wide')
            ->call('saveMediaProperties', 'tab_examples', $mediaId)->assertHasNoErrors();
        $oldMedia = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($mediaId);
        $oldPath = $oldMedia->getPathRelativeToRoot();
        $editor->set('mediaReplacements.' . $mediaId, UploadedFile::fake()->create('unsafe.txt'))
            ->call('replaceMedia', 'tab_examples', $mediaId)->assertHasErrors(['mediaReplacements.' . $mediaId]);
        Storage::disk('public')->assertExists($oldPath);
        $editor->set('mediaReplacements.' . $mediaId, UploadedFile::fake()->image('replacement.jpg'))
            ->call('replaceMedia', 'tab_examples', $mediaId)->assertHasNoErrors();
        $this->assertFalse(DB::table('media')->where('id', $mediaId)->exists());
        Storage::disk('public')->assertMissing($oldPath);
        $replacement = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('file_name', 'replacement.jpg')->firstOrFail();
        $this->assertSame(1, $replacement->order_column);
        $this->assertSame('Описание', $replacement->getCustomProperty('image_alt_ru'));
        $this->assertSame('wide', $replacement->getCustomProperty('layout'));
        Storage::disk('public')->assertExists($replacement->getPathRelativeToRoot());

        $otherId = DB::table('canvas_new')->insertGetId([]);
        $other = \App\Models\CanvasNew::findOrFail($otherId)->addMedia(UploadedFile::fake()->image('other.jpg'))->toMediaCollection('tab_examples', 'public');
        // The isolated admin fixture has no public theme tables for an HTML 404.
        // Use production error fallback instead of rethrowing that view's debug error.
        config(['app.debug' => false]);
        Livewire::test(VoyagerBread::class, ['type' => 'canvas-new'])->call('openEdit', $id)
            ->call('moveMedia', 'tab_examples', $other->id, 1)->assertNotFound();
        Livewire::test(VoyagerBread::class, ['type' => 'canvas-new'])->call('openEdit', $id)
            ->call('replaceMedia', 'tab_examples', $other->id)->assertNotFound();
        $this->assertSame($otherId, (int) $other->fresh()->model_id);
        $editor->set('mediaUploads.tab_examples', [UploadedFile::fake()->create('unsafe.txt')])
            ->call('uploadMedia', 'tab_examples')->assertHasErrors(['files.0']);
        $this->assertSame(4, DB::table('media')->count());
        $editor->set('mediaUploads.tab_examples', [UploadedFile::fake()->image('retry.jpg')])
            ->call('uploadMedia', 'tab_examples')->assertHasNoErrors();
        $this->assertSame(5, DB::table('media')->count());
        $bulk = Livewire::test(VoyagerBread::class, ['type' => 'canvas-new'])->call('openEdit', $id);
        $bulk->call('selectAllMedia', 'tab_examples');
        $this->assertCount(4, $bulk->get('selectedMedia.tab_examples'));
        $bulk->call('clearMediaSelection', 'tab_examples')->assertSet('selectedMedia.tab_examples', []);
        $ownedIds = DB::table('media')->where('model_id', $id)->pluck('id')->map(fn ($value) => (string) $value)->all();
        $bulk->set('selectedMedia.tab_examples', [$ownedIds[0], (string) $other->id])
            ->call('deleteSelectedMedia', 'tab_examples')->assertNotFound();
        $this->assertSame(5, DB::table('media')->count());
        Livewire::test(VoyagerBread::class, ['type' => 'canvas-new'])->call('openEdit', $id)
            ->call('selectAllMedia', 'tab_examples')->call('deleteSelectedMedia', 'tab_examples')
            ->assertSet('selectedMedia.tab_examples', []);
        $this->assertSame(1, DB::table('media')->count());
        Storage::disk('public')->assertMissing($replacement->getPathRelativeToRoot());
        Storage::disk('public')->assertExists($other->getPathRelativeToRoot());
    }

    public function test_multiple_checkbox_preserves_voyager_json_shape(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        $id = DB::table('pages')->insertGetId(['title' => 'Page']);
        DB::table('data_rows')->insert([
            'data_type_id' => DB::table('data_types')->where('slug', 'pages')->value('id'),
            'field' => 'flags', 'type' => 'multiple_checkbox', 'display_name' => 'Flags',
            'order' => 3, 'details' => json_encode(['options' => ['main' => 'Main', 'other' => 'Other']]),
        ]);

        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->call('openEdit', $id)
            ->set('data.flags', ['main'])
            ->call('save')
            ->assertHasNoErrors();
        $this->assertSame(['main' => 'main'], json_decode(DB::table('pages')->where('id', $id)->value('flags'), true));
    }

    public function test_menu_item_and_its_translation_are_editable(): void
    {
        Schema::create('menus', function (Blueprint $table): void {
            $table->id(); $table->string('name'); $table->timestamps();
        });
        Schema::create('menu_items', function (Blueprint $table): void {
            $table->id(); $table->foreignId('menu_id'); $table->boolean('status')->default(true);
            $table->string('title'); $table->text('url')->nullable(); $table->string('target')->nullable();
            $table->string('icon_class')->nullable(); $table->string('color')->nullable();
            $table->foreignId('parent_id')->nullable(); $table->integer('order');
            $table->string('route')->nullable(); $table->text('parameters')->nullable(); $table->timestamps();
        });
        $this->admin(['browse_admin', 'browse_menus', 'edit_menus', 'add_menus']);
        $menuId = DB::table('menus')->insertGetId(['name' => 'admin']);
        $id = DB::table('menu_items')->insertGetId(['menu_id' => $menuId, 'title' => 'Pages', 'order' => 1]);

        $this->get('/filament/menu-items')->assertOk();
        Livewire::test(VoyagerMenuItems::class)
            ->call('openEdit', $id)
            ->set('data.title', 'Content')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('menu_items', ['id' => $id, 'title' => 'Content']);

        Livewire::test(VoyagerMenuItems::class)
            ->call('openEdit', $id)
            ->call('changeLocale', 'ru')
            ->set('data.title', 'Страницы')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('translations', [
            'table_name' => 'menu_items', 'column_name' => 'title', 'foreign_key' => $id,
            'locale' => 'ru', 'value' => 'Страницы',
        ]);

        $childId = DB::table('menu_items')->insertGetId([
            'menu_id' => $menuId, 'title' => 'Child', 'parent_id' => $id, 'order' => 2,
        ]);
        Livewire::test(VoyagerMenuItems::class)
            ->call('openEdit', $id)
            ->set('data.parent_id', $childId)
            ->call('save')
            ->assertHasErrors(['data.parent_id']);
        $this->assertDatabaseHas('menu_items', ['id' => $id, 'parent_id' => null]);
    }

    public function test_ui_translation_is_saved_to_database_and_published_to_dictionary(): void
    {
        Schema::create('ltm_translations', function (Blueprint $table): void {
            $table->id(); $table->boolean('status')->default(false);
            $table->string('locale'); $table->string('group'); $table->string('key');
            $table->text('value')->nullable(); $table->timestamps();
        });
        $this->admin(['browse_admin']);
        DB::table('ltm_translations')->insert([
            'locale' => 'ru', 'group' => 'homepage_new', 'key' => 'title', 'value' => 'Старое',
        ]);
        DB::table('ltm_translations')->insert([
            'locale' => 'en', 'group' => 'homepage_new', 'key' => 'new_only', 'value' => 'New',
        ]);
        DB::table('ltm_translations')->insert([
            'locale' => 'ru', 'group' => '_json', 'key' => 'A sentence.', 'value' => 'Старый текст',
        ]);
        $oldPath = app()->langPath();
        $directory = storage_path('framework/testing/ui-translations-' . uniqid());
        \Illuminate\Support\Facades\File::makeDirectory($directory . '/ru', 0755, true);
        \Illuminate\Support\Facades\File::put($directory . '/ru/homepage_new.php', "<?php return ['title' => 'Старое', 'other' => 'Не менять'];");
        \Illuminate\Support\Facades\File::put($directory . '/ru.json', json_encode(['A sentence.' => 'Старый текст', 'other' => 'Не менять']));
        app()->useLangPath($directory);
        try {
            $this->get('/filament/ui-translations')->assertOk();
            Livewire::test(VoyagerUiTranslations::class)
                ->call('openEdit', 'title')
                ->set('data.value', 'Новое')
                ->call('save')
                ->assertHasNoErrors();
            $this->assertDatabaseHas('ltm_translations', [
                'locale' => 'ru', 'group' => 'homepage_new', 'key' => 'title', 'value' => 'Новое',
            ]);
            $this->assertSame(['title' => 'Новое', 'other' => 'Не менять'], require $directory . '/ru/homepage_new.php');

            Livewire::test(VoyagerUiTranslations::class)
                ->assertSee('new_only')
                ->call('openEdit', 'new_only')
                ->set('data.value', 'Новое значение')
                ->call('save')
                ->assertHasNoErrors();
            $this->assertDatabaseHas('ltm_translations', [
                'locale' => 'ru', 'group' => 'homepage_new', 'key' => 'new_only', 'value' => 'Новое значение',
            ]);

            Livewire::test(VoyagerUiTranslations::class)
                ->call('selectGroup', '_json')
                ->call('openEdit', 'A sentence.')
                ->set('data.value', 'Новый текст')
                ->call('save')
                ->assertHasNoErrors();
            $this->assertSame(['A sentence.' => 'Новый текст', 'other' => 'Не менять'],
                json_decode(\Illuminate\Support\Facades\File::get($directory . '/ru.json'), true));
        } finally {
            app()->useLangPath($oldPath);
            \Illuminate\Support\Facades\File::delete($directory . '/ru/homepage_new.php');
            \Illuminate\Support\Facades\File::delete($directory . '/ru.json');
            rmdir($directory . '/ru');
            rmdir($directory);
        }
    }

    public function test_voyager_setting_requires_permission_and_saves_value(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id(); $table->string('key'); $table->string('display_name');
            $table->text('value')->nullable(); $table->text('details')->nullable();
            $table->string('type'); $table->integer('order')->default(0); $table->string('group');
        });
        $id = DB::table('settings')->insertGetId([
            'key' => 'site.title', 'display_name' => 'Site title', 'value' => 'Old',
            'type' => 'text', 'group' => 'Site',
        ]);
        $this->admin(['browse_admin', 'browse_settings', 'edit_settings']);
        $this->get('/filament/voyager-settings')->assertOk();
        Livewire::test(VoyagerSettings::class)
            ->call('openEdit', $id)
            ->set('data.value', 'New title')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('settings', ['id' => $id, 'value' => 'New title']);
    }

    public function test_gallery_clone_resets_files_and_opens_copy_without_changing_source(): void
    {
        $this->admin(['browse_gallery_items', 'add_gallery_items', 'edit_gallery_items']);
        Schema::create('gallery_items', function (Blueprint $table): void {
            $table->id(); $table->string('name'); $table->string('slug')->unique();
            $table->string('image')->nullable(); $table->integer('id_type'); $table->timestamps();
        });
        $type = DB::table('data_types')->insertGetId([
            'name' => 'gallery_items', 'slug' => 'gallery-items',
            'model_name' => \App\Models\GalleryItem::class,
        ]);
        foreach (['name' => 'text', 'slug' => 'text', 'image' => 'image'] as $field => $kind) {
            DB::table('data_rows')->insert(['data_type_id' => $type, 'field' => $field, 'type' => $kind]);
        }
        $id = DB::table('gallery_items')->insertGetId(['name' => 'Flowers', 'slug' => 'flowers', 'image' => 'flowers.jpg', 'id_type' => 2]);
        Livewire::withQueryParams(['type' => 2])->test(VoyagerBread::class, ['type' => 'gallery-items'])
            ->call('cloneRecord', $id)
            ->assertRedirect(VoyagerBreadEdit::getUrl(['type' => 'gallery-items', 'record' => $id + 1]) . '?type=2');
        $copy = DB::table('gallery_items')->where('id', $id + 1)->first();
        $this->assertSame('Flowers (clone)', $copy->name);
        $this->assertNotSame('flowers', $copy->slug);
        $this->assertNull($copy->image);
        $this->assertSame(2, $copy->id_type);
        $this->assertSame('flowers.jpg', DB::table('gallery_items')->where('id', $id)->value('image'));
    }

    public function test_clone_cannot_be_called_without_add_permission_or_for_other_tables(): void
    {
        $this->admin(['browse_pages', 'edit_pages']);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('cloneRecord', 1)->assertForbidden();
        $this->assertSame(0, DB::table('pages')->count());
    }

    public function test_seo_generation_saves_locales_and_preserves_unsaved_form_fields(): void
    {
        $this->admin(['browse_pages', 'edit_pages']);
        Schema::table('pages', fn (Blueprint $table) => $table->string('meta_title')->nullable());
        DB::table('data_rows')->insert(['data_type_id' => 1, 'field' => 'meta_title', 'type' => 'text']);
        $id = DB::table('pages')->insertGetId(['title' => 'Original', 'meta_title' => 'Old', 'meta_description' => 'Old description']);
        $this->mock(\App\Services\SeoMetaGeneration\SeoMetaGenerator::class, function ($mock): void {
            $mock->shouldReceive('generate')->once()->andReturn(['locales' => [
                'en' => ['meta_title' => 'Generated EN', 'meta_description' => 'Description EN'],
                'ru' => ['meta_title' => 'Заголовок RU', 'meta_description' => 'Описание RU'],
            ]]);
        });
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->set('data.title', 'Unsaved')->call('generateSeoMeta')
            ->assertSet('data.title', 'Unsaved')->assertSet('data.meta_title', 'Generated EN');
        $this->assertSame('Original', DB::table('pages')->where('id', $id)->value('title'));
        $this->assertSame('Generated EN', DB::table('pages')->where('id', $id)->value('meta_title'));
        $this->assertSame('Заголовок RU', DB::table('translations')->where('column_name', 'meta_title')->where('locale', 'ru')->value('value'));
        $this->assertSame('Описание RU', DB::table('translations')->where('column_name', 'meta_description')->where('locale', 'ru')->value('value'));
    }

    public function test_seo_generation_failure_keeps_existing_values(): void
    {
        $this->admin(['browse_pages', 'edit_pages']);
        Schema::table('pages', fn (Blueprint $table) => $table->string('meta_title')->nullable());
        DB::table('data_rows')->insert(['data_type_id' => 1, 'field' => 'meta_title', 'type' => 'text']);
        $id = DB::table('pages')->insertGetId(['meta_title' => 'Original']);
        $this->mock(\App\Services\SeoMetaGeneration\SeoMetaGenerator::class, function ($mock): void {
            $mock->shouldReceive('generate')->once()->andThrow(new \RuntimeException('Generator unavailable'));
        });
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('generateSeoMeta')->assertSet('data.meta_title', 'Original');
        $this->assertSame('Original', DB::table('pages')->where('id', $id)->value('meta_title'));
        $this->assertSame(0, DB::table('translations')->count());
    }

    public function test_seo_generation_requires_edit_permission(): void
    {
        $this->admin(['browse_pages']);
        $this->mock(\App\Services\SeoMetaGeneration\SeoMetaGenerator::class, function ($mock): void {
            $mock->shouldNotReceive('generate');
        });
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('generateSeoMeta')->assertForbidden();
    }

    public function test_metadata_default_applies_only_to_null_values(): void
    {
        $this->admin(['browse_pages', 'edit_pages']);
        DB::table('data_rows')->where('field', 'meta_description')->update(['details' => json_encode(['default' => 'Default'])]);
        $id = DB::table('pages')->insertGetId(['title' => 'Page', 'meta_description' => null]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSet('data.meta_description', 'Default');
        DB::table('pages')->where('id', $id)->update(['meta_description' => '']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSet('data.meta_description', '');
    }

    public function test_media_picker_selects_existing_files_and_saves_voyager_json(): void
    {
        Storage::fake('public');
        $this->admin(['browse_pages', 'edit_pages']);
        DB::table('data_rows')->insert([
            'data_type_id' => 1, 'field' => 'flags', 'type' => 'media_picker',
            'details' => json_encode(['base_path' => '/pages/library/', 'show_folders' => true, 'allowed' => ['image/jpeg'], 'max' => 2]),
        ]);
        $image = UploadedFile::fake()->image('one.jpg');
        foreach (['one.jpg', 'two.jpg', 'three.jpg'] as $name) {
            Storage::disk('public')->put('pages/library/folder/' . $name, file_get_contents($image->getRealPath()));
        }
        $id = DB::table('pages')->insertGetId(['title' => 'Page', 'flags' => '[]']);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $editor->call('changeLocale', 'ru')->call('openFileLibrary', 'flags')->assertSee('folder')
            ->call('browseFileLibrary', 'pages/library/folder')->assertSee('one.jpg')
            ->call('chooseLibraryFile', 'flags', 'pages/library/folder/one.jpg')->assertHasNoErrors()
            ->call('chooseLibraryFile', 'flags', 'pages/library/folder/one.jpg')->assertHasNoErrors();
        $this->assertCount(1, $editor->get('data.flags'));
        $editor->call('chooseLibraryFile', 'flags', 'pages/library/folder/two.jpg')->assertHasNoErrors()
            ->call('chooseLibraryFile', 'flags', 'pages/library/folder/three.jpg')->assertHasErrors(['data.flags']);
        $this->assertCount(2, $editor->get('data.flags'));
        $editor->call('save')->assertHasNoErrors();
        $this->assertSame(['pages/library/folder/one.jpg', 'pages/library/folder/two.jpg'], json_decode(DB::table('pages')->where('id', $id)->value('flags'), true));
        Storage::disk('public')->assertExists('pages/library/folder/one.jpg');
    }

    public function test_media_picker_rejects_outside_paths_and_disallowed_mime(): void
    {
        Storage::fake('public');
        $this->admin(['browse_pages', 'edit_pages']);
        DB::table('data_rows')->insert([
            'data_type_id' => 1, 'field' => 'flags', 'type' => 'media_picker',
            'details' => json_encode(['base_path' => 'pages/library', 'allowed' => ['image/jpeg']]),
        ]);
        $id = DB::table('pages')->insertGetId(['title' => 'Page']);
        Storage::disk('public')->put('pages/library/text.jpg', 'plain text');
        $outside = UploadedFile::fake()->image('outside.jpg');
        Storage::disk('public')->put('other/outside.jpg', file_get_contents($outside->getRealPath()));
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('chooseLibraryFile', 'flags', 'pages/library/text.jpg')->assertHasErrors(['data.flags']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('chooseLibraryFile', 'flags', 'pages/library/../../secret.jpg')->assertStatus(422);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('openFileLibrary', 'flags')->call('browseFileLibrary', 'other')->assertStatus(422);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('openFileLibrary', 'meta_description')->assertForbidden();
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->set('data.flags', ['other/outside.jpg'])->call('save')->assertStatus(422);
        $this->assertNull(DB::table('pages')->where('id', $id)->value('flags'));
    }

    public function test_library_folder_creation_respects_metadata_and_root(): void
    {
        Storage::fake('public');
        $this->admin(['browse_pages', 'edit_pages']);
        DB::table('data_rows')->insert([
            'data_type_id' => 1, 'field' => 'flags', 'type' => 'media_picker',
            'details' => json_encode(['base_path' => 'pages/library', 'show_folders' => true, 'allow_create_folder' => true]),
        ]);
        $id = DB::table('pages')->insertGetId(['title' => 'Page']);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $editor->call('openFileLibrary', 'flags')->assertSee('Создать папку')
            ->set('libraryNewFolder', '../outside')->call('createLibraryFolder')->assertHasErrors(['libraryNewFolder'])
            ->set('libraryNewFolder', 'NUL')->call('createLibraryFolder')->assertHasErrors(['libraryNewFolder'])
            ->set('libraryNewFolder', 'Новые работы')->call('createLibraryFolder')->assertHasNoErrors()->assertSee('Новые работы');
        $this->assertTrue(Storage::disk('public')->directoryExists('pages/library/Новые работы'));
        $editor->set('libraryNewFolder', 'Новые работы')->call('createLibraryFolder')->assertHasErrors(['libraryNewFolder'])
            ->call('browseFileLibrary', 'pages/library/Новые работы')->set('libraryNewFolder', '2026')
            ->call('createLibraryFolder')->assertHasNoErrors();
        $this->assertTrue(Storage::disk('public')->directoryExists('pages/library/Новые работы/2026'));
        $editor->set('libraryFolder', 'other')->assertStatus(422);
        $this->assertFalse(Storage::disk('public')->directoryExists('other/outside'));
        DB::table('data_rows')->where('field', 'flags')->update(['details' => json_encode(['base_path' => 'pages/library', 'show_folders' => true, 'allow_create_folder' => false])]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('openFileLibrary', 'flags')->assertDontSee('Создать папку')->set('libraryNewFolder', 'blocked')
            ->call('createLibraryFolder')->assertForbidden();
        $this->assertFalse(Storage::disk('public')->directoryExists('pages/library/blocked'));
    }

    public function test_library_relocation_updates_shared_paths_and_keeps_original_content_links(): void
    {
        Storage::fake('public');
        $this->admin(['browse_pages', 'edit_pages']);
        DB::table('data_rows')->insert([
            ['data_type_id' => 1, 'field' => 'flags', 'type' => 'media_picker', 'details' => json_encode(['base_path' => 'pages', 'show_folders' => true, 'allow_rename' => true, 'allow_move' => true])],
            ['data_type_id' => 1, 'field' => 'image', 'type' => 'image', 'details' => '{}'],
        ]);
        $source = 'pages/старое фото.jpg';
        $target = 'pages/archive/новое фото.jpg';
        Storage::disk('public')->put($source, UploadedFile::fake()->image('photo.jpg')->getContent());
        Storage::disk('public')->makeDirectory('pages/archive');
        $id = DB::table('pages')->insertGetId(['title' => 'Before', 'flags' => json_encode([$source]), 'meta_description' => '<img src="/storage/' . $source . '">']);
        $other = DB::table('pages')->insertGetId(['image' => $source, 'flags' => json_encode([$source, $source . '.other'])]);
        DB::table('translations')->insert(['table_name' => 'pages', 'column_name' => 'image', 'foreign_key' => $other, 'locale' => 'ru', 'value' => $source]);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $editor->set('data.title', 'Unsaved title')->call('openFileLibrary', 'flags')
            ->call('openLibraryRelocation', $source)->set('libraryTargetName', 'новое фото.jpg')
            ->set('libraryTargetFolder', 'pages/archive')->call('relocateLibraryFile')->assertHasNoErrors();
        $this->assertSame('Unsaved title', $editor->get('data.title'));
        $this->assertSame([$target], array_values($editor->get('data.flags')));
        $this->assertSame([$target], json_decode(DB::table('pages')->where('id', $id)->value('flags'), true));
        $this->assertSame($target, DB::table('pages')->where('id', $other)->value('image'));
        $this->assertSame([$target, $source . '.other'], json_decode(DB::table('pages')->where('id', $other)->value('flags'), true));
        $this->assertSame($target, DB::table('translations')->value('value'));
        $this->assertSame('Before', DB::table('pages')->where('id', $id)->value('title'));
        $this->assertStringContainsString($source, DB::table('pages')->where('id', $id)->value('meta_description'));
        Storage::disk('public')->assertExists([$source, $target]);
        $this->assertSame(Storage::disk('public')->get($source), Storage::disk('public')->get($target));
        $editor->call('openLibraryRelocation', $target)->set('libraryTargetName', 'bad.png')
            ->call('relocateLibraryFile')->assertHasErrors(['libraryTargetName']);
        $editor->set('libraryTargetName', 'старое фото.jpg')->set('libraryTargetFolder', 'pages')
            ->call('relocateLibraryFile')->assertHasErrors(['libraryTargetName']);
        $editor->set('libraryTargetFolder', 'outside')->call('relocateLibraryFile')->assertStatus(422);
        Storage::disk('public')->assertMissing('outside/старое фото.jpg');
    }

    public function test_library_relocation_rolls_back_when_shared_type_is_not_editable(): void
    {
        Storage::fake('public');
        $this->admin(['browse_pages', 'edit_pages']);
        DB::table('data_rows')->insert(['data_type_id' => 1, 'field' => 'flags', 'type' => 'media_picker', 'details' => json_encode(['base_path' => 'pages', 'allow_rename' => true])]);
        Schema::create('shared_images', function (Blueprint $table): void { $table->id(); $table->string('image'); });
        $typeId = DB::table('data_types')->insertGetId(['name' => 'shared_images', 'slug' => 'shared-images', 'display_name_plural' => 'Shared', 'model_name' => \App\Models\Page::class]);
        DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => 'image', 'type' => 'image', 'details' => '{}']);
        $source = 'pages/shared.jpg';
        Storage::disk('public')->put($source, UploadedFile::fake()->image('photo.jpg')->getContent());
        $id = DB::table('pages')->insertGetId(['flags' => json_encode([$source])]);
        DB::table('shared_images')->insert(['image' => $source]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('openFileLibrary', 'flags')->call('openLibraryRelocation', $source)
            ->set('libraryTargetName', 'renamed.jpg')->call('relocateLibraryFile')->assertForbidden();
        $this->assertSame([$source], json_decode(DB::table('pages')->where('id', $id)->value('flags'), true));
        $this->assertSame($source, DB::table('shared_images')->value('image'));
        Storage::disk('public')->assertExists($source);
        Storage::disk('public')->assertMissing('pages/renamed.jpg');
        DB::table('data_rows')->where('field', 'flags')->update(['details' => json_encode(['base_path' => 'pages', 'allow_rename' => false, 'allow_move' => false])]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('openFileLibrary', 'flags')->call('openLibraryRelocation', $source)->assertForbidden();
    }

    public function test_library_crop_creates_new_image_and_only_changes_form_until_save(): void
    {
        Storage::fake('public');
        $this->admin(['browse_pages', 'edit_pages']);
        DB::table('data_rows')->insert(['data_type_id' => 1, 'field' => 'flags', 'type' => 'media_picker', 'details' => json_encode(['base_path' => 'pages', 'allow_crop' => true, 'max' => 1])]);
        $source = 'pages/original.png';
        $image = imagecreatetruecolor(100, 80);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagefilledrectangle($image, 20, 10, 39, 29, imagecolorallocate($image, 255, 0, 0));
        ob_start(); imagepng($image); $bytes = ob_get_clean(); imagedestroy($image);
        Storage::disk('public')->put($source, $bytes);
        $id = DB::table('pages')->insertGetId(['flags' => json_encode([$source])]);
        $other = DB::table('pages')->insertGetId(['flags' => json_encode([$source])]);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $editor->call('openFileLibrary', 'flags')->call('openLibraryCrop', $source)
            ->set('libraryCrop', ['x' => 20, 'y' => 10, 'width' => 40, 'height' => 30])
            ->call('cropLibraryFile')->assertHasNoErrors()->assertSet('libraryCropFile', null);
        $target = array_values($editor->get('data.flags'))[0];
        $this->assertNotSame($source, $target);
        $output = Storage::disk('public')->get($target);
        $info = getimagesizefromstring($output);
        $this->assertSame([40, 30, 'image/png'], [$info[0], $info[1], $info['mime']]);
        $cropped = imagecreatefromstring($output);
        $this->assertSame(0xff0000, imagecolorat($cropped, 0, 0));
        $this->assertSame(127, imagecolorsforindex($cropped, imagecolorat($cropped, 39, 29))['alpha']);
        imagedestroy($cropped);
        $this->assertSame($bytes, Storage::disk('public')->get($source));
        $this->assertSame([$source], json_decode(DB::table('pages')->where('id', $id)->value('flags'), true));
        $editor->call('save')->assertHasNoErrors();
        $this->assertSame([$target], json_decode(DB::table('pages')->where('id', $id)->value('flags'), true));
        $this->assertSame([$source], json_decode(DB::table('pages')->where('id', $other)->value('flags'), true));
    }

    public function test_library_crop_rejects_invalid_region_limit_and_metadata(): void
    {
        Storage::fake('public');
        $this->admin(['browse_pages', 'edit_pages']);
        DB::table('data_rows')->insert(['data_type_id' => 1, 'field' => 'flags', 'type' => 'media_picker', 'details' => json_encode(['base_path' => 'pages', 'allow_crop' => true, 'max' => 1])]);
        $source = 'pages/original.jpg';
        Storage::disk('public')->put($source, UploadedFile::fake()->image('original.jpg', 100, 80)->getContent());
        $id = DB::table('pages')->insertGetId(['flags' => json_encode([$source])]);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $editor->call('openFileLibrary', 'flags')->call('openLibraryCrop', $source)
            ->set('libraryCrop.width', 101)->call('cropLibraryFile')->assertHasErrors(['libraryCrop'])
            ->set('libraryCrop.width', 0)->call('cropLibraryFile')->assertHasErrors(['libraryCrop.width'])
            ->set('libraryCrop', ['x' => 0, 'y' => 0, 'width' => 20, 'height' => 20])
            ->call('cropLibraryFile')->assertHasNoErrors();
        $this->assertCount(2, Storage::disk('public')->files('pages'));
        $editor->call('openLibraryCrop', $source)->call('cropLibraryFile')->assertHasErrors(['libraryCrop']);
        $this->assertCount(2, Storage::disk('public')->files('pages'));
        DB::table('data_rows')->where('field', 'flags')->update(['details' => json_encode(['base_path' => 'pages', 'allow_crop' => false])]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('openFileLibrary', 'flags')->call('openLibraryCrop', $source)->assertForbidden();
        $this->assertSame([$source], json_decode(DB::table('pages')->where('id', $id)->value('flags'), true));
    }

    public function test_folder_rename_preserves_nested_files_and_updates_exact_folder_references(): void
    {
        Storage::fake('public');
        $this->admin(['browse_pages', 'edit_pages']);
        DB::table('data_rows')->insert([
            ['data_type_id' => 1, 'field' => 'flags', 'type' => 'media_picker', 'details' => json_encode(['base_path' => 'pages', 'show_folders' => true, 'allow_rename' => true, 'allow_delete' => true])],
            ['data_type_id' => 1, 'field' => 'image', 'type' => 'image', 'details' => '{}'],
        ]);
        $source = 'pages/Old'; $target = 'pages/Новая папка';
        Storage::disk('public')->put($source . '/one.jpg', 'original one');
        Storage::disk('public')->put($source . '/nested/two.jpg', 'original two');
        Storage::disk('public')->put($source . 'Suffix/three.jpg', 'unrelated');
        Storage::disk('public')->makeDirectory($source . '/empty');
        $values = [$source . '/one.jpg', $source . '/nested/two.jpg', $source . 'Suffix/three.jpg'];
        $id = DB::table('pages')->insertGetId(['title' => 'Before', 'flags' => json_encode($values)]);
        $other = DB::table('pages')->insertGetId(['image' => $source . '/missing.jpg']);
        DB::table('translations')->insert(['table_name' => 'pages', 'column_name' => 'image', 'foreign_key' => $other, 'locale' => 'ru', 'value' => $source . '/one.jpg']);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $editor->set('data.title', 'Unsaved')->call('openFileLibrary', 'flags')->call('browseFileLibrary', $source . '/nested')
            ->call('openLibraryFolderRename', $source)->set('libraryFolderName', '../outside')
            ->call('renameLibraryFolder')->assertHasErrors(['libraryFolderName'])
            ->set('libraryFolderName', 'Новая папка')->call('renameLibraryFolder')->assertHasNoErrors()
            ->assertSet('libraryFolder', $target . '/nested')->assertSet('data.title', 'Unsaved');
        $expected = [$target . '/one.jpg', $target . '/nested/two.jpg', $source . 'Suffix/three.jpg'];
        $this->assertSame($expected, json_decode(DB::table('pages')->where('id', $id)->value('flags'), true));
        $this->assertSame($expected, array_values($editor->get('data.flags')));
        $this->assertSame($target . '/missing.jpg', DB::table('pages')->where('id', $other)->value('image'));
        $this->assertSame($target . '/one.jpg', DB::table('translations')->value('value'));
        $this->assertSame('Before', DB::table('pages')->where('id', $id)->value('title'));
        foreach (['/one.jpg' => 'original one', '/nested/two.jpg' => 'original two'] as $file => $bytes) {
            $this->assertSame($bytes, Storage::disk('public')->get($target . $file));
            $this->assertSame($bytes, Storage::disk('public')->get($source . $file));
        }
        $this->assertTrue(Storage::disk('public')->directoryExists($target . '/empty'));
        $editor->call('openLibraryFolderRename', $target)->set('libraryFolderName', 'Old')->call('renameLibraryFolder')->assertHasErrors(['libraryFolderName']);
        $editor->call('deleteEmptyLibraryFolder', $target)->assertHasErrors(['libraryFolderName']);
        $editor->call('deleteEmptyLibraryFolder', $target . '/empty')->assertHasNoErrors();
        $this->assertFalse(Storage::disk('public')->directoryExists($target . '/empty'));
        $this->assertTrue(Storage::disk('public')->directoryExists($source . '/empty'));
    }

    public function test_folder_actions_guard_roots_metadata_and_nonempty_directories(): void
    {
        Storage::fake('public');
        $this->admin(['browse_pages', 'edit_pages']);
        DB::table('data_rows')->insert(['data_type_id' => 1, 'field' => 'flags', 'type' => 'media_picker', 'details' => json_encode(['base_path' => 'pages', 'show_folders' => true, 'allow_rename' => true, 'allow_delete' => true])]);
        Storage::disk('public')->makeDirectory('pages/empty');
        Storage::disk('public')->makeDirectory('pages/parent/child');
        Storage::disk('public')->put('pages/hidden/.hidden', 'keep');
        $id = DB::table('pages')->insertGetId(['flags' => '[]']);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $editor->call('openFileLibrary', 'flags')->call('browseFileLibrary', 'pages/empty')
            ->call('deleteEmptyLibraryFolder', 'pages/empty')->assertHasNoErrors()->assertSet('libraryFolder', 'pages');
        $this->assertFalse(Storage::disk('public')->directoryExists('pages/empty'));
        $editor->call('deleteEmptyLibraryFolder', 'pages/parent')->assertHasErrors(['libraryFolderName'])
            ->call('deleteEmptyLibraryFolder', 'pages/hidden')->assertHasErrors(['libraryFolderName']);
        Storage::disk('public')->assertExists('pages/hidden/.hidden');
        foreach (['pages', 'pages/../outside', 'other'] as $path) {
            Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
                ->call('openFileLibrary', 'flags')->call('deleteEmptyLibraryFolder', $path)->assertStatus(422);
        }
        DB::table('data_rows')->where('field', 'flags')->update(['details' => json_encode(['base_path' => 'pages', 'show_folders' => true])]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('openFileLibrary', 'flags')->call('openLibraryFolderRename', 'pages/parent')->assertForbidden();
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('openFileLibrary', 'flags')->call('deleteEmptyLibraryFolder', 'pages/parent')->assertForbidden();
    }

    public function test_folder_rename_removes_new_copies_on_permission_failure(): void
    {
        Storage::fake('public');
        $this->admin(['browse_pages', 'edit_pages']);
        DB::table('data_rows')->insert(['data_type_id' => 1, 'field' => 'flags', 'type' => 'media_picker', 'details' => json_encode(['base_path' => 'pages', 'show_folders' => true, 'allow_rename' => true])]);
        Schema::create('shared_images', function (Blueprint $table): void { $table->id(); $table->string('image'); });
        $typeId = DB::table('data_types')->insertGetId(['name' => 'shared_images', 'slug' => 'shared-images', 'display_name_plural' => 'Shared', 'model_name' => \App\Models\Page::class]);
        DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => 'image', 'type' => 'image', 'details' => '{}']);
        Storage::disk('public')->put('pages/Old/nested/file.jpg', 'original');
        $id = DB::table('pages')->insertGetId(['flags' => json_encode(['pages/Old/nested/file.jpg'])]);
        DB::table('shared_images')->insert(['image' => 'pages/Old/nested/file.jpg']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('openFileLibrary', 'flags')->call('openLibraryFolderRename', 'pages/Old')
            ->set('libraryFolderName', 'New')->call('renameLibraryFolder')->assertForbidden();
        $this->assertSame(['pages/Old/nested/file.jpg'], json_decode(DB::table('pages')->where('id', $id)->value('flags'), true));
        $this->assertSame('pages/Old/nested/file.jpg', DB::table('shared_images')->value('image'));
        $this->assertSame('original', Storage::disk('public')->get('pages/Old/nested/file.jpg'));
        $this->assertFalse(Storage::disk('public')->directoryExists('pages/New'));
    }

    private function admin(array $permissions): void
    {
        $role = Role::create(['name' => 'admin']);
        foreach ($permissions as $key) {
            $role->permissions()->attach(Permission::create(['key' => $key]));
        }
        $user = User::forceCreate(['email' => fake()->unique()->safeEmail(), 'password' => Hash::make('password'), 'role_id' => $role->id]);
        $this->actingAs($user, 'filament');
    }
}
