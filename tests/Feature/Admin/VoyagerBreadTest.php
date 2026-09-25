<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\VoyagerBread;
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
    }

    public function test_requires_browse_permission(): void
    {
        $this->admin(['browse_admin']);
        $this->get('/filament/bread/pages')->assertForbidden();
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
                'label' => 'name',
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
            'details' => json_encode(['extra_fields' => ['image_alt_ru' => ['type' => 'text', 'title' => 'ALT RU']]]),
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
