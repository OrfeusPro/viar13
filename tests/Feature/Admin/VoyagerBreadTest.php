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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class VoyagerBreadTest extends TestCase
{
    public function test_blog_combined_document_saves_and_reopens_without_losing_features(): void
    {
        $this->admin(['browse_admin', 'browse_blog_posts', 'read_blog_posts', 'edit_blog_posts']);
        Schema::create('blog_posts', function (Blueprint $table): void { $table->id(); $table->string('title')->nullable(); $table->string('slug')->nullable(); $table->text('text')->nullable(); $table->timestamps(); });
        $type = DB::table('data_types')->insertGetId(['name' => 'blog_posts', 'slug' => 'blog-posts', 'model_name' => 'App\\Models\\BlogPost', 'display_name_singular' => 'Blog', 'display_name_plural' => 'Blog']);
        DB::table('data_rows')->insert(['data_type_id' => $type, 'field' => 'text', 'type' => 'rich_text_box', 'display_name' => 'Text', 'edit' => 1, 'add' => 1, 'browse' => 1, 'read' => 1, 'details' => '{}']);
        $id = DB::table('blog_posts')->insertGetId(['title' => 'Initial', 'slug' => 'initial', 'text' => '<p>Original EN</p>']);
        $page = Livewire::test(VoyagerBreadEdit::class, ['type' => 'blog-posts', 'record' => $id]);
        $component = collect($page->instance()->form->getFlatComponents())->first(fn ($field) => $field instanceof \Filament\Forms\Components\RichEditor);
        $html = '<h1 style="padding-left:40px"><span style="font-size:18pt;background-color:#ffcc00">Заголовок</span></h1><p style="text-align:justify"><a href="/blog#mixed" title="Подсказка" target="_blank" rel="noopener"><strong>Ссылка</strong><img src="/keep.jpg" alt="Keep" width="180" height="90"></a><br>Текст</p><figure class="image"><img src="/caption.jpg" title="Title" width="200" height="100"><figcaption><em>Подпись</em></figcaption></figure><p class="image-row"><img src="/one.jpg"><img src="/two.jpg"></p><table><tbody><tr><th colspan="2" colwidth="180,220"><p>Header</p></th></tr><tr><td rowspan="2"><p>Left</p></td><td><p>Right</p></td></tr><tr><td><p>Bottom</p></td></tr></tbody></table>';
        $output = $component->getTipTapEditor()->setContent($html)->getHTML();
        $this->assertFalse(app(\App\Filament\Bread\BreadRichEditor::class)->requiresSource($output, 'blog_posts'));
        $this->assertSame($output, $component->getTipTapEditor()->setContent($output)->getHTML());
        foreach (['Подпись', 'image-row', 'colwidth="180,220"', 'rowspan="2"', 'title="Подсказка"', 'rel="noopener"', 'padding-left: 40px', 'font-size: 18pt', 'background-color:', '<br>'] as $feature) { $this->assertStringContainsString($feature, $output); }
        $page->call('changeLocale', 'ru')->set('data.text', $output)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('translations', ['table_name' => 'blog_posts', 'foreign_key' => $id, 'column_name' => 'text', 'locale' => 'ru', 'value' => $output]);
        $this->assertDatabaseHas('blog_posts', ['id' => $id, 'text' => '<p>Original EN</p>']);
        $reopened = Livewire::test(VoyagerBreadEdit::class, ['type' => 'blog-posts', 'record' => $id])->call('changeLocale', 'ru');
        $field = collect($reopened->instance()->form->getFlatComponents())->first(fn ($field) => $field instanceof \Filament\Forms\Components\RichEditor);
        $this->assertNotNull($field);
        $this->assertSame($output, $field->getTipTapEditor()->setContent($field->getRawState())->getHTML());
    }

    public function test_rich_editor_merged_table_cells_roundtrip_and_legacy_dimensions_use_source(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages']);
        $id = DB::table('pages')->insertGetId(['title' => 'Table', 'meta_description' => '<p>Original</p>']);
        $page = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $service = app(\App\Filament\Bread\BreadRichEditor::class);
        $component = $service->component('body', [], 'blog_posts')->container($page->instance()->form);
        $html = '<table><tbody><tr><th colspan="2" data-colwidth="180,220"><p>Header</p></th></tr><tr><td rowspan="2"><p><strong>Left</strong></p></td><td><p>Right</p></td></tr><tr><td><p>Bottom</p></td></tr></tbody></table>';
        $output = $component->getTipTapEditor()->setContent($html)->getHTML();
        $this->assertStringContainsString('colspan="2"', $output);
        $this->assertStringContainsString('rowspan="2"', $output);
        $this->assertStringContainsString('colwidth="180,220"', $output);
        $this->assertStringContainsString('<strong>Left</strong>', $output);
        $this->assertSame(substr_count($output, '<td'), substr_count($output, '</td>'));
        $this->assertSame(substr_count($output, '<th'), substr_count($output, '</th>'));
        $this->assertSame($output, $component->getTipTapEditor()->setContent($output)->getHTML());
        foreach (['blog_posts', 'pages'] as $table) {
            $this->assertFalse($service->requiresSource($output, $table));
            foreach (['<table width="80%"><tr><td>A</td></tr></table>', '<table><tr><td width="100">A</td></tr></table>', '<table><tr><td rowspan="-1">A</td></tr></table>', '<table><tr><td colspan="2" data-colwidth="200">A</td></tr></table>', '<table style="border-collapse:collapse"><tr><td>A</td></tr></table>'] as $legacy) {
                $this->assertTrue($service->requiresSource($legacy, $table));
            }
        }
    }

    public function test_blog_link_title_and_display_text_action_preserve_html(): void
    {
        $this->admin(['browse_admin', 'browse_blog_posts', 'read_blog_posts', 'edit_blog_posts']);
        Schema::create('blog_posts', function (Blueprint $table): void { $table->id(); $table->text('text')->nullable(); });
        $type = DB::table('data_types')->insertGetId(['name' => 'blog_posts', 'slug' => 'blog-posts', 'model_name' => 'App\\Models\\BlogPost', 'display_name_singular' => 'Blog', 'display_name_plural' => 'Blog']);
        DB::table('data_rows')->insert(['data_type_id' => $type, 'field' => 'text', 'type' => 'rich_text_box', 'display_name' => 'Text', 'edit' => 1, 'add' => 1, 'browse' => 1, 'read' => 1, 'details' => '{}']);
        $id = DB::table('blog_posts')->insertGetId(['text' => '<p>Original</p>']);
        $page = Livewire::test(VoyagerBreadEdit::class, ['type' => 'blog-posts', 'record' => $id]);
        $component = collect($page->instance()->form->getFlatComponents())->first(fn ($field) => $field instanceof \Filament\Forms\Components\RichEditor);
        $html = '<p><a href="/blog#section" title="Подсказка" target="_blank"><strong>Текст</strong></a></p>';
        $output = $component->getTipTapEditor()->setContent($html)->getHTML();
        $this->assertStringContainsString('title="Подсказка"', $output);
        $this->assertStringContainsString('<strong>Текст</strong>', $output);
        $this->assertSame($output, $component->getTipTapEditor()->setContent($output)->getHTML());
        $selection = ['anchor' => 1, 'head' => 5, 'type' => 'text'];
        $action = \Filament\Actions\Testing\TestAction::make('breadLink')->schemaComponent('text', 'form')->arguments(['editorSelection' => $selection, 'text' => 'Before']);
        $page->callAction($action, ['url' => 'javascript:alert(1)', 'text' => 'After'])->assertHasActionErrors(['url']);
        $page->callAction($action, ['url' => '/blog#section', 'text' => 'After', 'title' => 'Tooltip', 'newTab' => true])
            ->assertHasNoActionErrors()->assertDispatched('run-rich-editor-commands', fn ($name, $parameters) => $parameters['editorSelection'] === $selection && $parameters['commands'] === [['name' => 'applyBreadLink', 'arguments' => [['href' => '/blog#section', 'title' => 'Tooltip', 'target' => '_blank', 'rel' => 'noopener'], 'After', 'Before', true]]]);
        $this->assertDatabaseHas('blog_posts', ['id' => $id, 'text' => '<p>Original</p>']);
        $imageHtml = '<p>Before <a href="/blog#image" title="Image"><img src="/keep.jpg" alt="Keep" width="180" height="90"></a> after <strong>Bold</strong></p>';
        $imageOutput = $component->getTipTapEditor()->setContent($imageHtml)->getHTML();
        $this->assertStringContainsString('href="/blog#image"', $imageOutput);
        $this->assertStringContainsString('src="/keep.jpg"', $imageOutput);
        $this->assertStringContainsString('width="180"', $imageOutput);
        $this->assertStringContainsString('<strong>Bold</strong>', $imageOutput);
        $this->assertSame($imageOutput, $component->getTipTapEditor()->setContent($imageOutput)->getHTML());
        $imageAction = \Filament\Actions\Testing\TestAction::make('breadLink')->schemaComponent('text', 'form')
            ->arguments(['editorSelection' => ['anchor' => 1, 'type' => 'node'], 'canEditText' => false]);
        $page = Livewire::test(VoyagerBreadEdit::class, ['type' => 'blog-posts', 'record' => $id]);
        $page->mountAction($imageAction)->assertSchemaComponentHidden('text', 'mountedActionSchema0');
        $page->callMountedAction(['url' => '/blog#image', 'text' => 'Forged replacement', 'title' => 'Image', 'newTab' => false])
            ->assertHasNoActionErrors()->assertDispatched('run-rich-editor-commands', fn ($name, $parameters) => $parameters['commands'][0]['arguments'][3] === false);
    }

    public function test_database_editor_explains_existing_structure_constraints_without_modifying_schema(): void
    {
        $this->admin(['browse_admin', 'browse_database']);
        Schema::create('notice_fixture', function (Blueprint $table): void {
            $table->id(); $table->string('label'); $table->string('code');
            $table->unique(['label', 'code'], 'notice_composite');
            $table->integer('doubled')->virtualAs('id * 2');
        });
        Schema::create('notice_child', function (Blueprint $table): void {
            $table->id(); $table->foreignId('parent_id')->constrained('notice_fixture');
        });
        DB::table('notice_fixture')->insert(['label' => 'Stay', 'code' => '001']);
        $service = app(\App\Services\Admin\DatabaseTableEditingService::class);
        $before = $service->fingerprint($service->snapshot('notice_fixture'));
        Livewire::withQueryParams(['table' => 'notice_fixture'])->test(\App\Filament\Pages\DatabaseEdit::class)
            ->assertSee('Особенности этой таблицы')->assertSee('notice_composite (label, code)')
            ->assertSee('У таблицы есть внешние связи')->assertSee('Вычисляемые колонки: doubled');
        Livewire::withQueryParams(['table' => 'notice_child'])->test(\App\Filament\Pages\DatabaseEdit::class)
            ->assertSee('У таблицы есть внешние связи')->assertDontSee('Составные индексы:');
        Schema::create('notice_plain', fn (Blueprint $table) => $table->id());
        Livewire::withQueryParams(['table' => 'notice_plain'])->test(\App\Filament\Pages\DatabaseEdit::class)
            ->assertSet('structureNotices', [])->assertDontSee('Особенности этой таблицы');
        $this->assertSame($before, $service->fingerprint($service->snapshot('notice_fixture')));
        $this->assertDatabaseHas('notice_fixture', ['label' => 'Stay', 'code' => '001', 'doubled' => 2]);
    }

    public function test_database_drop_requires_bread_removal_and_rechecks_it_after_preview(): void
    {
        Schema::create('drop_bread_fixture', function (Blueprint $table): void { $table->id(); });
        DB::table('drop_bread_fixture')->insert(['id' => 1]);
        $service = app(\App\Services\Admin\DatabaseTableEditingService::class);
        $hash = $service->fingerprint($service->snapshot('drop_bread_fixture'));
        $this->assertNotEmpty($service->dropPreview('drop_bread_fixture', $hash));
        DB::table('data_types')->insert(['name' => 'drop_bread_fixture', 'slug' => 'drop-bread-fixture', 'model_name' => '', 'display_name_singular' => 'Fixture', 'display_name_plural' => 'Fixtures']);
        foreach (['dropPreview', 'drop'] as $method) {
            try { $service->{$method}('drop_bread_fixture', $hash); $this->fail('Table with BREAD was dropped'); }
            catch (\Illuminate\Validation\ValidationException $exception) { $this->assertStringContainsString('Сначала удалите BREAD', $exception->getMessage()); }
        }
        $this->assertDatabaseHas('drop_bread_fixture', ['id' => 1]);
        DB::table('data_types')->where('name', 'drop_bread_fixture')->delete();
        $service->drop('drop_bread_fixture', $hash);
        $this->assertFalse(Schema::hasTable('drop_bread_fixture'));
    }

    public function test_database_edit_keeps_existing_disabled_text_default_and_index_on_noop(): void
    {
        $this->admin(['browse_admin', 'browse_database']);
        Schema::create('text_options_fixture', function (Blueprint $table): void {
            $table->id(); $table->text('note')->default('kept'); $table->index('note', 'note_index');
        });
        DB::table('text_options_fixture')->insert(['id' => 1]);
        $page = Livewire::withQueryParams(['table' => 'text_options_fixture'])->test(\App\Filament\Pages\DatabaseEdit::class)
            ->call('preview')->assertHasNoErrors()->assertSet('sql', []);
        $page->set('data.name', 'text_options_renamed')->call('prepareSaveStructure')->assertHasNoErrors()
            ->call('saveStructure')->assertHasNoErrors();
        $this->assertDatabaseHas('text_options_renamed', ['id' => 1, 'note' => 'kept']);
        $this->assertTrue(collect(Schema::getIndexes('text_options_renamed'))->contains(fn ($index) => $index['name'] === 'note_index'));
        DB::table('text_options_renamed')->insert(['id' => 2]);
        $this->assertDatabaseHas('text_options_renamed', ['id' => 2, 'note' => 'kept']);
    }

    public function test_database_mysql_extra_lookup_uses_schema_and_prefixed_table_bindings(): void
    {
        $connection = \Mockery::mock(\Illuminate\Database\MySqlConnection::class);
        $connection->shouldReceive('getDatabaseName')->once()->andReturn('fixture');
        $connection->shouldReceive('getTablePrefix')->once()->andReturn('tenant_');
        $connection->shouldReceive('select')->once()->with(
            'select COLUMN_NAME as name, EXTRA as extra from information_schema.COLUMNS where TABLE_SCHEMA = ? and TABLE_NAME = ?',
            ['fixture', 'tenant_records']
        )->andReturn([(object) ['name' => 'stamp', 'extra' => 'DEFAULT_GENERATED on update CURRENT_TIMESTAMP(6)']]);
        $manager = DB::getFacadeRoot(); DB::shouldReceive('connection')->once()->andReturn($connection);
        $service = new class extends \App\Services\Admin\DatabaseInspectionService {
            public function extras(string $table): array { return $this->columnExtras($table); }
        };
        try { $this->assertSame(['stamp' => 'DEFAULT_GENERATED on update CURRENT_TIMESTAMP(6)'], $service->extras('records')); }
        finally { DB::swap($manager); }
    }

    public function test_database_inspection_keeps_extra_and_detects_auto_increment_with_other_flags(): void
    {
        Schema::create('extra_inspection_fixture', function (Blueprint $table): void { $table->id(); });
        $service = new class extends \App\Services\Admin\DatabaseInspectionService {
            public function tables(string $search = ''): array { return [['name' => 'extra_inspection_fixture']]; }
            protected function columnExtras(string $table): array { return ['id' => 'auto_increment INVISIBLE']; }
        };
        $manager = DB::getFacadeRoot(); $connection = DB::connection();
        DB::shouldReceive('getDriverName')->once()->andReturn('mysql');
        DB::shouldReceive('connection')->andReturn($connection);
        try {
            $snapshot = $service->describe('extra_inspection_fixture');
            $this->assertSame('auto_increment INVISIBLE', $snapshot['columns'][0]['extra']);
            $this->assertTrue($snapshot['columns'][0]['auto_increment']);
        } finally { DB::swap($manager); }
    }

    public function test_database_edit_mysql_preserves_extra_expressions_and_zerofill_or_rejects_incompatible_changes(): void
    {
        Schema::create('extras_fixture', function (Blueprint $table): void {
            $table->id(); $table->timestamp('stamp')->nullable(); $table->integer('code')->default(0);
            $table->string('token', 40); $table->string('literal', 40);
        });
        $fixture = app(\App\Services\Admin\DatabaseTableEditingService::class)->snapshot('extras_fixture');
        $fixture['structure']['columns'][1]['type_name'] = 'timestamp';
        $fixture['structure']['columns'][1]['type'] = 'timestamp(6)';
        $fixture['structure']['columns'][1]['default'] = 'current_timestamp(6)';
        $fixture['structure']['columns'][1]['extra'] = 'DEFAULT_GENERATED on update CURRENT_TIMESTAMP(6)';
        $fixture['structure']['columns'][2]['type_name'] = 'int';
        $fixture['structure']['columns'][2]['type'] = 'int(6) unsigned zerofill';
        foreach ([3, 4] as $position) {
            $fixture['structure']['columns'][$position]['type_name'] = 'varchar';
            $fixture['structure']['columns'][$position]['type'] = 'varchar(40)';
        }
        $fixture['structure']['columns'][3]['default'] = 'uuid()';
        $fixture['structure']['columns'][3]['extra'] = 'DEFAULT_GENERATED';
        $fixture['structure']['columns'][4]['default'] = "'uuid()'";
        config(['database.connections.mysql_extra_compile' => ['driver' => 'mysql', 'database' => 'fixture', 'prefix' => '']]);
        $default = DB::getDefaultConnection(); DB::setDefaultConnection('mysql_extra_compile');
        Schema::clearResolvedInstance('db.schema'); Schema::shouldReceive('hasTable')->andReturn(false);
        $connection = DB::connection(); $pdo = \Mockery::mock(\PDO::class);
        $pdo->shouldReceive('getAttribute')->with(\PDO::ATTR_SERVER_VERSION)->andReturn('8.0.36'); $connection->setPdo($pdo);
        $grammar = $connection->getSchemaGrammar();
        $service = new class($fixture) extends \App\Services\Admin\DatabaseTableEditingService {
            public function __construct(public array $fixture) {}
            public function snapshot(string $table): array { return $this->fixture; }
        };
        try {
            $input = $service->definition('extras_fixture', $fixture);
            $input['columns'][1]['nullable'] = false;
            $input['columns'][2]['nullable'] = true;
            $input['columns'][3]['length'] = '60'; $input['columns'][4]['length'] = '60';
            $sql = implode("\n", $service->preview('extras_fixture', $input, $service->fingerprint($fixture))['sql']);
            $this->assertStringContainsString('timestamp(6) not null default current_timestamp(6) on update CURRENT_TIMESTAMP(6)', $sql);
            $this->assertStringContainsString('modify `code` int(6) unsigned zerofill null default', $sql);
            $this->assertStringContainsString('modify `token` varchar(60) not null default (uuid())', $sql);
            $this->assertStringContainsString("modify `literal` varchar(60) not null default 'uuid()'", $sql);
            $this->assertSame($grammar, $connection->getSchemaGrammar());
            $service->fixture['structure']['columns'][3]['extra'] = '';
            $service->fixture['structure']['columns'][3]['mariadb_quoted_defaults'] = true;
            $mariaSql = implode("\n", $service->preview('extras_fixture', $input, $service->fingerprint($service->fixture))['sql']);
            $this->assertStringContainsString('default (uuid())', $mariaSql);
            $service->fixture = $fixture;
            $invalid = $input; $invalid['columns'][2]['unsigned'] = false;
            try { $service->preview('extras_fixture', $invalid, $service->fingerprint($fixture)); $this->fail('Zerofill unsigned change accepted'); }
            catch (\Illuminate\Validation\ValidationException $exception) { $this->assertStringContainsString('ZEROFILL', $exception->getMessage()); }
            $invalid = $input; $invalid['columns'][1]['type'] = 'varchar'; $invalid['columns'][1]['default'] = null;
            try { $service->preview('extras_fixture', $invalid, $service->fingerprint($fixture)); $this->fail('Invalid ON UPDATE type accepted'); }
            catch (\Illuminate\Validation\ValidationException $exception) { $this->assertStringContainsString('ON UPDATE', $exception->getMessage()); }
            $invalid = $input; $invalid['columns'][3]['type'] = 'text';
            try { $service->preview('extras_fixture', $invalid, $service->fingerprint($fixture)); $this->fail('Expression type change accepted'); }
            catch (\Illuminate\Validation\ValidationException $exception) { $this->assertStringContainsString('выражением default', $exception->getMessage()); }
            $invalid = $input; $invalid['columns'][3]['default'] = 'uuid(); drop table anything';
            $sql = implode("\n", $service->preview('extras_fixture', $invalid, $service->fingerprint($fixture))['sql']);
            $this->assertStringContainsString("default 'uuid(); drop table anything'", $sql);
            $service->fixture['structure']['columns'][2]['extra'] = 'INVISIBLE';
            try { $service->preview('extras_fixture', $input, $service->fingerprint($service->fixture)); $this->fail('Unknown EXTRA lost'); }
            catch (\Illuminate\Validation\ValidationException $exception) { $this->assertStringContainsString('дополнительные свойства', $exception->getMessage()); }
        } finally { DB::setDefaultConnection($default); $this->app->forgetInstance('db.schema'); Schema::clearResolvedInstance('db.schema'); DB::purge('mysql_extra_compile'); }
        $this->assertTrue(Schema::hasColumn('extras_fixture', 'stamp'));
    }

    public function test_database_edit_renames_expression_default_without_rewriting_it(): void
    {
        Schema::create('expression_fixture', function (Blueprint $table): void { $table->id(); $table->timestamp('published_at')->useCurrent(); });
        DB::table('expression_fixture')->insert(['id' => 1]);
        $before = DB::table('expression_fixture')->value('published_at');
        $service = app(\App\Services\Admin\DatabaseTableEditingService::class);
        $snapshot = $service->snapshot('expression_fixture');
        $input = $service->definition('expression_fixture', $snapshot);
        $input['columns'][1]['name'] = 'released_at';
        $plan = $service->preview('expression_fixture', $input, $service->fingerprint($snapshot));
        $this->assertSame([], $plan['changed']);
        $service->apply('expression_fixture', $input, $service->fingerprint($snapshot));
        $this->assertSame($before, DB::table('expression_fixture')->value('released_at'));
        DB::table('expression_fixture')->insert(['id' => 2]);
        $this->assertNotNull(DB::table('expression_fixture')->where('id', 2)->value('released_at'));
        $this->assertSame('CURRENT_TIMESTAMP', collect(Schema::getColumns('expression_fixture'))->firstWhere('name', 'released_at')['default']);
        $snapshot = $service->snapshot('expression_fixture');
        $input = $service->definition('expression_fixture', $snapshot);
        $input['columns'][1]['nullable'] = true;
        $service->apply('expression_fixture', $input, $service->fingerprint($snapshot));
        DB::table('expression_fixture')->insert(['id' => 3]);
        $this->assertNotNull(DB::table('expression_fixture')->where('id', 3)->value('released_at'));
        $this->assertSame($before, DB::table('expression_fixture')->where('id', 1)->value('released_at'));
    }

    public function test_database_edit_mysql_primary_auto_transition_is_one_alter_and_guards_dependencies(): void
    {
        Schema::create('primary_fixture', function (Blueprint $table): void { $table->id(); $table->integer('code'); $table->timestamp('clock')->nullable(); $table->index('code', 'code_index'); });
        $fixture = app(\App\Services\Admin\DatabaseTableEditingService::class)->snapshot('primary_fixture');
        $fixture['structure']['columns'][2]['type'] = 'timestamp(6)';
        $fixture['structure']['columns'][2]['type_name'] = 'timestamp';
        config(['database.connections.mysql_primary_compile' => ['driver' => 'mysql', 'database' => 'fixture', 'prefix' => '']]);
        $default = DB::getDefaultConnection(); DB::setDefaultConnection('mysql_primary_compile');
        Schema::clearResolvedInstance('db.schema'); Schema::shouldReceive('hasTable')->andReturn(false);
        $connection = DB::connection(); $pdo = \Mockery::mock(\PDO::class);
        $pdo->shouldReceive('getAttribute')->with(\PDO::ATTR_SERVER_VERSION)->andReturn('8.0.36'); $connection->setPdo($pdo);
        $grammar = $connection->getSchemaGrammar();
        $service = new class($fixture) extends \App\Services\Admin\DatabaseTableEditingService {
            public function __construct(public array $fixture) {}
            public function snapshot(string $table): array { return $this->fixture; }
        };
        try {
            $input = $service->definition('primary_fixture', $fixture);
            $input['columns'][0]['auto_increment'] = false; $input['columns'][0]['index'] = '';
            $input['columns'][1]['auto_increment'] = true; $input['columns'][1]['index'] = 'PRIMARY';
            $input['columns'][2]['nullable'] = false;
            $plan = $service->preview('primary_fixture', $input, $service->fingerprint($fixture));
            $this->assertCount(1, $plan['sql']);
            $sql = $plan['sql'][0];
            $this->assertStringContainsString('drop primary key', $sql);
            $this->assertStringContainsString('modify `id`', $sql);
            $this->assertStringContainsString('modify `code` int not null auto_increment', $sql);
            $this->assertStringContainsString('add primary key (`code`)', $sql);
            $this->assertStringContainsString('modify `clock` timestamp(6) not null', $sql);
            $this->assertStringNotContainsString('auto_increment primary key', $sql);
            $this->assertSame($grammar, $connection->getSchemaGrammar());
            // Keeping AUTO_INCREMENT while removing its key is rejected before SQL.
            $invalid = $service->definition('primary_fixture', $fixture); $invalid['columns'][0]['index'] = '';
            try { $service->preview('primary_fixture', $invalid, $service->fingerprint($fixture)); $this->fail('Unkeyed auto increment accepted'); }
            catch (\Illuminate\Validation\ValidationException) { $this->assertTrue(true); }
            $service->fixture['incoming'] = [['table' => 'referencing', 'key' => ['foreign_columns' => ['code']]]];
            $invalid = $service->definition('primary_fixture', $service->fixture); $invalid['columns'][1]['index'] = '';
            try { $service->preview('primary_fixture', $invalid, $service->fingerprint($service->fixture)); $this->fail('Referenced index drop accepted'); }
            catch (\Illuminate\Validation\ValidationException $exception) { $this->assertStringContainsString('входящей связью', $exception->getMessage()); }
            $service->fixture = $fixture;
            $service->fixture['structure']['indexes'][0]['columns'] = ['id', 'code'];
            $service->fixture['structure']['indexes'][0]['primary'] = true;
            $invalid = $service->definition('primary_fixture', $service->fixture);
            $invalid['columns'][] = ['name' => 'new_key', 'type' => 'integer', 'length' => '', 'nullable' => false, 'unsigned' => false, 'auto_increment' => false, 'index' => 'PRIMARY', 'default' => null, 'old_name' => null];
            try { $service->preview('primary_fixture', $invalid, $service->fingerprint($service->fixture)); $this->fail('Second primary accepted'); }
            catch (\Illuminate\Validation\ValidationException $exception) { $this->assertStringContainsString('Составной PRIMARY', $exception->getMessage()); }
        } finally { DB::setDefaultConnection($default); $this->app->forgetInstance('db.schema'); Schema::clearResolvedInstance('db.schema'); DB::purge('mysql_primary_compile'); }
        $this->assertTrue(Schema::hasColumn('primary_fixture', 'id'));
    }

    public function test_database_optional_model_generation_matches_original_and_does_not_overwrite(): void
    {
        $this->admin(['browse_admin', 'browse_database']);
        $originalPath = app_path();
        $temporaryPath = sys_get_temp_dir().'/viar-model-test-'.bin2hex(random_bytes(8));
        mkdir($temporaryPath);
        $this->app->useAppPath($temporaryPath);
        config(['voyager.models.namespace' => 'App\\Models\\']);
        try {
            $page = Livewire::test(\App\Filament\Pages\DatabaseCreate::class)
                ->assertSee('Создать модель для таблицы')
                ->set('data.name', 'audit_model_widgets')->set('data.create_model', true)
                ->call('preview')->assertHasNoErrors()->assertSee('App\\Models\\AuditModelWidget');
            $file = $temporaryPath.'/Models/AuditModelWidget.php';
            $this->assertFileDoesNotExist($file);
            $this->assertFalse(Schema::hasTable('audit_model_widgets'));
            $page->call('createTable')->assertHasNoErrors();
            $this->assertTrue(Schema::hasTable('audit_model_widgets'));
            $source = file_get_contents($file);
            token_get_all($source, TOKEN_PARSE);
            $this->assertStringContainsString('class AuditModelWidget extends Model', $source);
            $this->assertStringNotContainsString('SoftDeletes', $source);
            $this->assertStringNotContainsString('protected $table', $source);
            $this->assertDatabaseCount('data_types', 1);
            // Another plural maps to the same class: collision must reject before table DDL.
            Livewire::test(\App\Filament\Pages\DatabaseCreate::class)
                ->set('data.name', 'audit_model_widget')->set('data.create_model', true)
                ->call('preview')->assertHasErrors(['data.create_model']);
            $this->assertFalse(Schema::hasTable('audit_model_widget'));
            $this->assertSame($source, file_get_contents($file));
        } finally {
            $this->app->useAppPath($originalPath);
            \Illuminate\Support\Facades\File::deleteDirectory($temporaryPath);
        }
    }

    public function test_database_model_preview_rechecks_options_and_file_collision_before_ddl(): void
    {
        $this->admin(['browse_admin', 'browse_database']);
        $originalPath = app_path();
        $temporaryPath = sys_get_temp_dir().'/viar-model-test-'.bin2hex(random_bytes(8));
        mkdir($temporaryPath);
        $this->app->useAppPath($temporaryPath);
        config(['voyager.models.namespace' => 'App\\Models\\']);
        try {
            $page = Livewire::test(\App\Filament\Pages\DatabaseCreate::class)
                ->set('data.name', 'audit_model_samples')->call('preview')->assertHasNoErrors()
                ->set('data.create_model', true)->call('createTable')->assertHasErrors(['data.name']);
            $this->assertFalse(Schema::hasTable('audit_model_samples'));
            $page->call('preview')->assertHasNoErrors();
            mkdir($temporaryPath.'/Models');
            file_put_contents($temporaryPath.'/Models/AuditModelSample.php', 'existing file');
            $page->call('createTable')->assertHasErrors(['data.create_model']);
            $this->assertFalse(Schema::hasTable('audit_model_samples'));
            $this->assertSame('existing file', file_get_contents($temporaryPath.'/Models/AuditModelSample.php'));
            $service = app(\App\Services\Admin\DatabaseModelGenerationService::class);
            foreach (['../escape', 'class'] as $invalid) {
                try { $service->preview($invalid); $this->fail('Invalid class accepted'); }
                catch (\Illuminate\Validation\ValidationException $exception) { $this->assertArrayHasKey('data.create_model', $exception->errors()); }
            }
            config(['voyager.models.namespace' => 'Outside\\Models']);
            try { $service->preview('valid_names'); $this->fail('External namespace accepted'); }
            catch (\Illuminate\Validation\ValidationException $exception) { $this->assertArrayHasKey('data.create_model', $exception->errors()); }
        } finally {
            $this->app->useAppPath($originalPath);
            \Illuminate\Support\Facades\File::deleteDirectory($temporaryPath);
        }
    }

    public function test_database_model_failure_after_ddl_reports_created_table_and_invalidates_preview(): void
    {
        $this->admin(['browse_admin', 'browse_database']);
        $service = \Mockery::mock(\App\Services\Admin\DatabaseModelGenerationService::class);
        $plan = ['class' => 'App\\Models\\AuditFailure', 'path' => '/not-written', 'source' => 'preview'];
        $service->shouldReceive('preview')->with('audit_failures')->twice()->andReturn($plan);
        $service->shouldReceive('generate')->once()->andThrow(\Illuminate\Validation\ValidationException::withMessages(['data.create_model' => 'Каталог недоступен.']));
        $this->app->instance(\App\Services\Admin\DatabaseModelGenerationService::class, $service);
        $page = Livewire::test(\App\Filament\Pages\DatabaseCreate::class)
            ->set('data.name', 'audit_failures')->set('data.create_model', true)->call('preview')->assertHasNoErrors()
            ->call('createTable')->assertHasErrors(['data.create_model'])->assertSet('previewHash', null);
        $this->assertTrue(Schema::hasTable('audit_failures'));
        $page->call('createTable')->assertHasErrors(['data.name']);
    }

    public function test_database_edit_primary_save_button_prepares_sql_and_keeps_nullable_semantics(): void
    {
        $this->admin(['browse_admin', 'browse_database']);
        Schema::create('editor_ui_fixture', function (Blueprint $table): void { $table->id(); $table->string('required'); $table->string('optional')->nullable(); });
        DB::table('editor_ui_fixture')->insert(['required' => 'Keep']);
        $page = Livewire::withQueryParams(['table' => 'editor_ui_fixture'])->test(\App\Filament\Pages\DatabaseEdit::class)
            ->assertSee('Not Null')->assertSee('Сохранить таблицу');
        $before = array_values($page->get('data.columns'));
        $this->assertFalse($before[1]['nullable']); $this->assertTrue($before[2]['nullable']);
        $page->set('data.name', 'editor_ui_renamed')->call('prepareSaveStructure')->assertHasNoErrors()
            ->assertDispatched('open-modal', id: 'database-save-structure');
        $this->assertTrue(Schema::hasTable('editor_ui_fixture')); $this->assertFalse(Schema::hasTable('editor_ui_renamed'));
        $page->call('saveStructure')->assertHasNoErrors();
        $this->assertDatabaseHas('editor_ui_renamed', ['required' => 'Keep', 'optional' => null]);
        $columns = array_column(Schema::getColumns('editor_ui_renamed'), null, 'name');
        $this->assertFalse($columns['required']['nullable']); $this->assertTrue($columns['optional']['nullable']);
        $creation = Livewire::test(\App\Filament\Pages\DatabaseCreate::class)->call('addColumn');
        $columns = array_values($creation->get('data.columns'));
        $this->assertCount(2, $columns); $this->assertSame('integer', $columns[1]['type']); $this->assertTrue($columns[1]['nullable']);
    }

    public function test_database_catalog_bread_deletion_requires_confirmation_and_preserves_physical_records(): void
    {
        $this->admin(['browse_admin', 'browse_database', 'browse_bread', 'browse_pages']);
        $type = DB::table('data_types')->where('name', 'pages')->first();
        $row = DB::table('data_rows')->insertGetId(['data_type_id' => $type->id, 'field' => 'title', 'type' => 'text', 'display_name' => 'Title']);
        $record = DB::table('pages')->insertGetId(['title' => 'Keep record']);
        if (! Schema::hasTable('translations')) {
            Schema::create('translations', function (Blueprint $table): void {
                $table->id(); $table->string('table_name'); $table->string('column_name'); $table->unsignedBigInteger('foreign_key'); $table->string('locale'); $table->text('value');
            });
        }
        DB::table('translations')->insert([
            ['table_name' => 'data_types', 'column_name' => 'display_name_plural', 'foreign_key' => $type->id, 'locale' => 'ru', 'value' => 'Pages'],
            ['table_name' => 'data_rows', 'column_name' => 'display_name', 'foreign_key' => $row, 'locale' => 'ru', 'value' => 'Title'],
            ['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => $record, 'locale' => 'ru', 'value' => 'Keep translation'],
        ]);
        DB::table('permissions')->where('key', 'browse_pages')->update(['table_name' => 'pages']);
        $permission = DB::table('permissions')->where('key', 'browse_pages')->value('id');
        $page = Livewire::test(\App\Filament\Pages\DatabaseTables::class)->set('search', 'pages')
            ->assertSee('Открыть BREAD')->call('deleteBread')->assertHasErrors('breadConfirmation');
        $this->assertStringNotContainsString('@js(', $page->html());
        $page->call('prepareBreadDeletion', 'pages')->assertSet('breadDeletionTable', 'pages')
            ->set('breadConfirmation', 'wrong')->call('deleteBread')->assertHasErrors('breadConfirmation');
        $this->assertDatabaseHas('data_types', ['id' => $type->id]);
        DB::table('data_rows')->where('id', $row)->update(['display_name' => 'Changed']);
        $page->set('breadConfirmation', 'pages')->call('deleteBread')->assertHasErrors('breadConfirmation');
        DB::unprepared("CREATE TRIGGER reject_bread_delete BEFORE DELETE ON permissions BEGIN SELECT RAISE(ABORT, 'fixture failure'); END");
        $page->call('prepareBreadDeletion', 'pages')->set('breadConfirmation', 'pages')->call('deleteBread')->assertHasErrors('breadConfirmation');
        $this->assertDatabaseHas('data_types', ['id' => $type->id]);
        $this->assertDatabaseHas('data_rows', ['id' => $row]);
        $this->assertDatabaseHas('translations', ['table_name' => 'data_rows', 'foreign_key' => $row]);
        $this->assertDatabaseHas('permission_role', ['permission_id' => $permission]);
        DB::unprepared('DROP TRIGGER reject_bread_delete');
        $page->call('prepareBreadDeletion', 'pages')->set('breadConfirmation', 'pages')->call('deleteBread')->assertHasNoErrors();
        $this->assertDatabaseMissing('data_types', ['id' => $type->id]);
        $this->assertDatabaseMissing('data_rows', ['data_type_id' => $type->id]);
        $this->assertDatabaseMissing('permissions', ['id' => $permission]);
        $this->assertDatabaseMissing('permission_role', ['permission_id' => $permission]);
        $this->assertDatabaseMissing('translations', ['table_name' => 'data_rows', 'foreign_key' => $row]);
        $this->assertDatabaseMissing('translations', ['table_name' => 'data_types', 'foreign_key' => $type->id]);
        $this->assertDatabaseHas('pages', ['id' => $record, 'title' => 'Keep record']);
        $this->assertDatabaseHas('translations', ['table_name' => 'pages', 'value' => 'Keep translation']);
        $this->assertTrue(Schema::hasTable('pages'));
    }

    public function test_database_catalog_bread_deletion_rechecks_permission(): void
    {
        $this->admin(['browse_admin', 'browse_database', 'browse_bread']);
        $page = Livewire::test(\App\Filament\Pages\DatabaseTables::class)->call('prepareBreadDeletion', 'pages')->set('breadConfirmation', 'pages');
        $role = auth('filament')->user()->role;
        $role->permissions()->detach(DB::table('permissions')->where('key', 'browse_bread')->value('id'));
        auth('filament')->user()->unsetRelation('role');
        $page->call('deleteBread')->assertStatus(403);
        $this->assertDatabaseHas('data_types', ['name' => 'pages']);
    }

    public function test_database_edit_distinguishes_sql_null_default_from_literal_null_text(): void
    {
        $service = app(\App\Services\Admin\DatabaseTableEditingService::class);
        $snapshot = ['structure' => ['columns' => [], 'indexes' => []]];
        foreach (['NULL', "'NULL'", '0'] as $index => $default) {
            $snapshot['structure']['columns'][] = ['name' => 'field_'.$index, 'type_name' => 'varchar',
                'type' => 'varchar(255)', 'nullable' => true, 'auto_increment' => false, 'default' => $default];
        }
        $columns = $service->definition('fixture', $snapshot)['columns'];
        $this->assertSame([null, 'NULL', '0'], array_column($columns, 'default'));
    }

    public function test_database_edit_mysql_dependency_lookup_is_targeted_and_keeps_hidden_tables(): void
    {
        $connection = \Mockery::mock(\Illuminate\Database\MySqlConnection::class);
        $connection->shouldReceive('getDriverName')->once()->andReturn('mysql');
        $connection->shouldReceive('getDatabaseName')->twice()->andReturn('fixture');
        $connection->shouldReceive('getTablePrefix')->once()->andReturn('tenant_');
        $connection->shouldReceive('select')->once()->with(
            'select distinct TABLE_NAME as table_name from information_schema.KEY_COLUMN_USAGE '
            .'where TABLE_SCHEMA = ? and REFERENCED_TABLE_SCHEMA = ? and REFERENCED_TABLE_NAME = ? order by TABLE_NAME',
            ['fixture', 'fixture', 'tenant_parent']
        )->andReturn([(object) ['table_name' => 'tenant_hidden_child']]);
        $manager = DB::getFacadeRoot();
        DB::shouldReceive('connection')->once()->andReturn($connection);
        $service = new class extends \App\Services\Admin\DatabaseTableEditingService {
            public function references(string $table): array { return $this->referencingTables($table); }
        };
        try { $this->assertSame(['tenant_hidden_child'], $service->references('parent')); }
        finally { DB::swap($manager); }
    }

    public function test_database_edit_preserves_generated_and_multiple_indexes_and_rolls_back_failed_sqlite_ddl(): void
    {
        Schema::create('generated_edit_fixture', function (Blueprint $table): void {
            $table->id(); $table->string('label')->default('old'); $table->integer('doubled')->virtualAs('id * 2');
            $table->index('label', 'label_index_a'); $table->index('label', 'label_index_b');
        });
        DB::table('generated_edit_fixture')->insert(['label' => 'same']);
        DB::table('generated_edit_fixture')->insert(['label' => 'same']);
        $service = app(\App\Services\Admin\DatabaseTableEditingService::class);
        $snapshot = $service->snapshot('generated_edit_fixture'); $hash = $service->fingerprint($snapshot);
        $input = $service->definition('generated_edit_fixture', $snapshot);
        $this->assertSame([], $service->preview('generated_edit_fixture', $input, $hash)['sql']);
        $bad = $input; $bad['columns'][2]['name'] = 'other_generated';
        try { $service->apply('generated_edit_fixture', $bad, $hash); $this->fail('Generated column altered'); }
        catch (\Illuminate\Validation\ValidationException) { $this->assertTrue(Schema::hasColumn('generated_edit_fixture', 'doubled')); }
        $bad = $input; $bad['columns'][1]['index'] = 'UNIQUE';
        $foreignKeys = DB::connection()->scalar('pragma foreign_keys');
        try { $service->apply('generated_edit_fixture', $bad, $hash); $this->fail('Duplicate unique index accepted'); }
        catch (\Illuminate\Database\QueryException) {
            $this->assertSame($snapshot, $service->snapshot('generated_edit_fixture'));
            $this->assertSame($foreignKeys, DB::connection()->scalar('pragma foreign_keys'));
            $this->assertDatabaseCount('generated_edit_fixture', 2);
        }
        $input['columns'][1]['length'] = '100';
        $service->apply('generated_edit_fixture', $input, $hash);
        $this->assertDatabaseHas('generated_edit_fixture', ['id' => 1, 'label' => 'same', 'doubled' => 2]);
        $this->assertDatabaseHas('generated_edit_fixture', ['id' => 2, 'doubled' => 4]);
        $this->assertEqualsCanonicalizing(['label_index_b', 'label_index_a', 'primary'], array_column(Schema::getIndexes('generated_edit_fixture'), 'name'));
    }

    public function test_database_composite_column_rename_preserves_indexes_on_modern_mysql_and_mariadb(): void
    {
        Schema::create('composite_rename_fixture', function (Blueprint $table): void {
            $table->id(); $table->string('title', 50); $table->string('code', 50);
            $table->unique(['title', 'code'], 'composite_title_code');
        });
        DB::table('composite_rename_fixture')->insert(['title' => 'Keep', 'code' => '001']);
        $fixture = app(\App\Services\Admin\DatabaseTableEditingService::class)->snapshot('composite_rename_fixture');
        foreach ($fixture['structure']['columns'] as &$column) {
            if (in_array($column['name'], ['title', 'code'])) { $column['type'] = 'varchar(50)'; }
        }
        unset($column);
        $default = DB::getDefaultConnection();
        config(['database.connections.composite_compile' => ['driver' => 'mysql', 'database' => 'fixture', 'prefix' => '']]);
        Schema::clearResolvedInstance('db.schema'); Schema::shouldReceive('hasTable')->andReturn(false);
        try {
            foreach (['8.0.36', '10.6.18-MariaDB', '8.0.2', '10.5.1-MariaDB'] as $version) {
                DB::setDefaultConnection('composite_compile');
                $connection = DB::connection(); $pdo = \Mockery::mock(\PDO::class);
                $pdo->shouldReceive('getAttribute')->with(\PDO::ATTR_SERVER_VERSION)->andReturn($version);
                $connection->setPdo($pdo); $connection->useDefaultSchemaGrammar(); $grammar = $connection->getSchemaGrammar();
                $service = new class($fixture) extends \App\Services\Admin\DatabaseTableEditingService {
                    public function __construct(private array $fixture) {}
                    public function snapshot(string $table): array { return $this->fixture; }
                };
                $input = $service->definition('composite_rename_fixture', $fixture);
                $input['columns'][1]['name'] = 'caption';
                $hash = $service->fingerprint($fixture);
                if (in_array($version, ['8.0.2', '10.5.1-MariaDB'])) {
                    try { $service->preview('composite_rename_fixture', $input, $hash); $this->fail('Legacy composite rename accepted'); }
                    catch (\Illuminate\Validation\ValidationException) { $this->assertTrue(true); }
                } else {
                    $plan = $service->preview('composite_rename_fixture', $input, $hash);
                    $this->assertSame(['alter table `composite_rename_fixture` rename column `title` to `caption`'], $plan['sql']);
                    $this->assertSame([], $plan['dropIndexes']); $this->assertSame([], $plan['addIndexes']);
                    foreach (['length', 'index', 'drop'] as $kind) {
                        $bad = $input;
                        if ($kind === 'length') { $bad['columns'][1]['length'] = '120'; }
                        if ($kind === 'index') { $bad['columns'][1]['index'] = 'INDEX'; }
                        if ($kind === 'drop') { array_splice($bad['columns'], 1, 1); }
                        try { $service->preview('composite_rename_fixture', $bad, $hash); $this->fail('Unsupported composite edit accepted'); }
                        catch (\Illuminate\Validation\ValidationException) { $this->assertTrue(true); }
                    }
                }
                $this->assertSame($grammar, $connection->getSchemaGrammar());
                DB::purge('composite_compile');
            }
        } finally {
            DB::setDefaultConnection($default); DB::purge('composite_compile');
            $this->app->forgetInstance('db.schema'); Schema::clearResolvedInstance('db.schema');
        }
        $this->assertDatabaseHas('composite_rename_fixture', ['title' => 'Keep', 'code' => '001']);
        $this->assertTrue(Schema::hasColumn('composite_rename_fixture', 'title'));
        $this->assertTrue(collect(Schema::getIndexes('composite_rename_fixture'))->contains(fn ($index) => $index['name'] === 'composite_title_code'));
    }

    public function test_database_foreign_key_column_rename_uses_native_sql_and_preserves_dependency_guards(): void
    {
        Schema::create('fk_rename_fixture', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('parent_id'); $table->string('note', 50);
            $table->index('parent_id', 'parent_lookup');
        });
        DB::table('fk_rename_fixture')->insert(['id' => 1, 'parent_id' => 1, 'note' => 'Keep']);
        $base = app(\App\Services\Admin\DatabaseTableEditingService::class)->snapshot('fk_rename_fixture');
        $base['structure']['columns'][2]['type'] = 'varchar(50)';
        $key = ['name' => 'retained_fk', 'columns' => ['parent_id'], 'foreign_schema' => 'fixture',
            'foreign_table' => 'parent_fixture', 'foreign_columns' => ['id'], 'on_update' => 'cascade', 'on_delete' => 'restrict'];
        $fixtures = [];
        $fixtures['outgoing'] = $base;
        $fixtures['outgoing']['structure']['foreign_keys'] = [$key];
        $fixtures['incoming'] = $base;
        $fixtures['incoming']['incoming'] = [['table' => 'child_fixture', 'key' => $key]];
        $fixtures['self'] = $fixtures['incoming'];
        $fixtures['self']['structure']['foreign_keys'] = [[...$key, 'foreign_table' => 'fk_rename_fixture']];
        $fixtures['composite'] = $base;
        $fixtures['composite']['structure']['foreign_keys'] = [[...$key, 'columns' => ['parent_id', 'note'], 'foreign_columns' => ['id', 'code']]];
        $fixtures['composite']['structure']['indexes'][] = ['name' => 'compound_lookup', 'columns' => ['parent_id', 'note'], 'primary' => false, 'unique' => false];
        foreach ($fixtures as $kind => $fixture) {
            $service = new class($fixture) extends \App\Services\Admin\DatabaseTableEditingService {
                public function __construct(private array $fixture) {}
                public function snapshot(string $table): array { return $this->fixture; }
            };
            $input = $service->definition('fk_rename_fixture', $fixture);
            $input['columns'][$kind === 'incoming' ? 0 : 1]['name'] = 'renamed_key';
            try { $service->preview('fk_rename_fixture', $input, $service->fingerprint($fixture)); $this->fail('SQLite FK rename accepted'); }
            catch (\Illuminate\Validation\ValidationException) { $this->assertTrue(true); }
        }
        $default = DB::getDefaultConnection();
        config(['database.connections.fk_rename_compile' => ['driver' => 'mysql', 'database' => 'fixture', 'prefix' => '']]);
        Schema::clearResolvedInstance('db.schema'); Schema::shouldReceive('hasTable')->andReturn(false);
        try {
            foreach (['8.0.36', '10.6.18-MariaDB', '8.0.3', '10.5.2-MariaDB', '8.0.2', '10.5.1-MariaDB'] as $version) {
                DB::setDefaultConnection('fk_rename_compile');
                $connection = DB::connection(); $pdo = \Mockery::mock(\PDO::class);
                // Any attempted query/statement would fail: this fixture has no database connection.
                $pdo->shouldReceive('getAttribute')->with(\PDO::ATTR_SERVER_VERSION)->andReturn($version);
                $connection->setPdo($pdo); $connection->useDefaultSchemaGrammar(); $grammar = $connection->getSchemaGrammar();
                foreach ($fixtures as $kind => $fixture) {
                    $service = new class($fixture) extends \App\Services\Admin\DatabaseTableEditingService {
                        public function __construct(private array $fixture) {}
                        public function snapshot(string $table): array { return $this->fixture; }
                    };
                    $input = $service->definition('fk_rename_fixture', $fixture); $hash = $service->fingerprint($fixture);
                    $this->assertSame([], $service->preview('fk_rename_fixture', $input, $hash)['sql']);
                    $offset = $kind === 'incoming' ? 0 : 1;
                    $old = $input['columns'][$offset]['name'];
                    $input['columns'][$offset]['name'] = 'renamed_key';
                    if (in_array($version, ['8.0.2', '10.5.1-MariaDB'])) {
                        try { $service->preview('fk_rename_fixture', $input, $hash); $this->fail('Legacy FK rename accepted'); }
                        catch (\Illuminate\Validation\ValidationException) { $this->assertTrue(true); }
                        continue;
                    }
                    $plan = $service->preview('fk_rename_fixture', $input, $hash);
                    $this->assertSame(["alter table `fk_rename_fixture` rename column `$old` to `renamed_key`"], $plan['sql']);
                    $this->assertSame([], $plan['changed']); $this->assertSame([], $plan['dropIndexes']); $this->assertSame([], $plan['addIndexes']);
                    $this->assertSame($fixture, $plan['snapshot']);
                    foreach (['nullable', 'type', 'index', 'drop'] as $change) {
                        $bad = $input;
                        if ($change === 'nullable') { $bad['columns'][$offset]['nullable'] = ! $bad['columns'][$offset]['nullable']; }
                        if ($change === 'type') { $bad['columns'][$offset]['type'] = 'smallint'; }
                        if ($change === 'index') { $bad['columns'][$offset]['index'] = null; }
                        if ($change === 'drop') { array_splice($bad['columns'], $offset, 1); }
                        try { $service->preview('fk_rename_fixture', $bad, $hash); $this->fail('Unsafe FK change accepted: '.$kind.'/'.$change); }
                        catch (\Illuminate\Validation\ValidationException) { $this->assertTrue(true); }
                    }
                    // The original updater also performs renames before unrelated property changes.
                    if ($kind !== 'composite') {
                        $mixed = $input; $mixed['columns'][2]['length'] = '80';
                        $sql = $service->preview('fk_rename_fixture', $mixed, $hash)['sql'];
                        $this->assertSame($plan['sql'][0], $sql[0]);
                        $this->assertStringContainsString('modify `note` varchar(80)', $sql[1]);
                    }
                }
                $this->assertSame($grammar, $connection->getSchemaGrammar());
                DB::purge('fk_rename_compile');
            }
        } finally {
            DB::setDefaultConnection($default); DB::purge('fk_rename_compile');
            $this->app->forgetInstance('db.schema'); Schema::clearResolvedInstance('db.schema');
        }
        $this->assertDatabaseHas('fk_rename_fixture', ['id' => 1, 'parent_id' => 1, 'note' => 'Keep']);
        $this->assertTrue(Schema::hasColumn('fk_rename_fixture', 'parent_id'));
        $this->assertFalse(Schema::hasColumn('fk_rename_fixture', 'renamed_key'));
        $this->assertTrue(collect(Schema::getIndexes('fk_rename_fixture'))->contains(fn ($index) => $index['name'] === 'parent_lookup'));
    }

    public function test_database_edit_mysql_preview_compiles_changes_without_server_connection(): void
    {
        Schema::create('mysql_edit_fixture', function (Blueprint $table): void { $table->id(); $table->string('title', 50); $table->index('title', 'old_title_index'); });
        $fixture = app(\App\Services\Admin\DatabaseTableEditingService::class)->snapshot('mysql_edit_fixture');
        $fixture['structure']['columns'][1]['type'] = 'varchar(50)';
        $fixture['structure']['columns'][1]['collation'] = 'utf8mb4_unicode_ci';
        $fixture['structure']['columns'][1]['comment'] = 'Retained comment';
        config(['database.connections.mysql_edit_compile' => ['driver' => 'mysql', 'database' => 'fixture', 'prefix' => '']]);
        $default = DB::getDefaultConnection(); DB::setDefaultConnection('mysql_edit_compile');
        Schema::clearResolvedInstance('db.schema'); Schema::shouldReceive('hasTable')->andReturn(false);
        $connection = DB::connection(); $pdo = \Mockery::mock(\PDO::class);
        $pdo->shouldReceive('getAttribute')->with(\PDO::ATTR_SERVER_VERSION)->andReturn('8.0.36'); $connection->setPdo($pdo);
        $grammar = $connection->getSchemaGrammar();
        $service = new class($fixture) extends \App\Services\Admin\DatabaseTableEditingService {
            public function __construct(private array $fixture) {}
            public function snapshot(string $table): array { return $this->fixture; }
        };
        try {
            $input = $service->definition('mysql_edit_fixture', $fixture);
            $input['name'] = 'mysql_renamed_fixture';
            $input['columns'][1]['name'] = 'caption'; $input['columns'][1]['length'] = '120';
            $input['columns'][1]['nullable'] = true; $input['columns'][1]['index'] = 'UNIQUE';
            $result = $service->preview('mysql_edit_fixture', $input, $service->fingerprint($fixture));
            $sql = implode("\n", $result['sql']);
            $this->assertStringContainsString('drop index `old_title_index`', $sql);
            $this->assertStringContainsString('rename column `title` to `caption`', $sql);
            $this->assertStringContainsString('modify `caption` varchar(120)', $sql);
            $this->assertStringContainsString("collate 'utf8mb4_unicode_ci'", $sql);
            $this->assertStringContainsString("comment 'Retained comment'", $sql);
            $this->assertStringContainsString('add unique', $sql);
            $this->assertStringContainsString('rename table `mysql_edit_fixture` to `mysql_renamed_fixture`', $sql);
            $this->assertSame($grammar, $connection->getSchemaGrammar());
            $input['columns'][1]['type'] = 'timestamp';
            $input['columns'][1]['length'] = '';
            $input['columns'][1]['index'] = null;
            $sql = implode("\n", $service->preview('mysql_edit_fixture', $input, $service->fingerprint($fixture))['sql']);
            $this->assertStringContainsString('modify `caption` timestamp', $sql);
            $this->assertStringNotContainsString('collate', $sql);
        } finally { DB::setDefaultConnection($default); $this->app->forgetInstance('db.schema'); Schema::clearResolvedInstance('db.schema'); DB::purge('mysql_edit_compile'); }
        $this->assertTrue(Schema::hasColumn('mysql_edit_fixture', 'title'));
        $this->assertFalse(Schema::hasTable('mysql_renamed_fixture'));
    }

    public function test_database_edit_diff_preserves_records_renames_changes_adds_drops_and_indexes(): void
    {
        Schema::create('edit_fixture', function (Blueprint $table): void {
            $table->id(); $table->string('title', 50)->default('old'); $table->string('obsolete')->nullable();
            $table->index('title', 'fixture_title_index');
        });
        DB::table('edit_fixture')->insert(['title' => 'Retained', 'obsolete' => 'discard']);
        $service = app(\App\Services\Admin\DatabaseTableEditingService::class);
        $snapshot = $service->snapshot('edit_fixture'); $hash = $service->fingerprint($snapshot);
        $input = $service->definition('edit_fixture', $snapshot);
        $this->assertSame([], $service->preview('edit_fixture', $input, $hash)['sql']);
        $input['name'] = 'edited_fixture';
        $input['columns'][1]['name'] = 'caption'; $input['columns'][1]['length'] = '100';
        $input['columns'][1]['index'] = 'UNIQUE'; $input['columns'][1]['default'] = 'new';
        array_pop($input['columns']);
        $input['columns'][] = ['name' => 'score', 'type' => 'integer', 'length' => '', 'nullable' => false,
            'unsigned' => false, 'auto_increment' => false, 'index' => 'INDEX', 'default' => '0', 'old_name' => null];
        $preview = $service->preview('edit_fixture', $input, $hash);
        $this->assertNotEmpty($preview['sql']);
        $this->assertTrue(Schema::hasColumn('edit_fixture', 'title'));
        $this->assertDatabaseHas('edit_fixture', ['title' => 'Retained', 'obsolete' => 'discard']);
        $service->apply('edit_fixture', $input, $hash);
        $this->assertFalse(Schema::hasTable('edit_fixture'));
        $this->assertSame(['id', 'caption', 'score'], Schema::getColumnListing('edited_fixture'));
        $this->assertDatabaseHas('edited_fixture', ['id' => 1, 'caption' => 'Retained', 'score' => 0]);
        DB::table('edited_fixture')->insert(['score' => 2]);
        $this->assertDatabaseHas('edited_fixture', ['id' => 2, 'caption' => 'new', 'score' => 2]);
        $this->assertTrue(collect(Schema::getIndexes('edited_fixture'))->contains(fn ($index) => $index['unique'] && $index['columns'] === ['caption']));
        $this->assertDatabaseCount('data_types', 1);
    }

    public function test_database_edit_page_requires_current_preview_and_rechecks_schema_permissions_and_drop_name(): void
    {
        $this->admin(['browse_admin', 'browse_database']);
        Schema::create('page_edit_fixture', fn (Blueprint $table) => $table->id());
        $this->get('/filament/database-tables/edit?table=page_edit_fixture')->assertOk()->assertSee('Редактирование таблицы');
        $page = Livewire::withQueryParams(['table' => 'page_edit_fixture'])->test(\App\Filament\Pages\DatabaseEdit::class);
        $page->set('data.name', 'renamed_page_fixture')->call('saveStructure')->assertHasErrors(['data.name']);
        $page->call('preview')->assertHasNoErrors()->set('data.name', 'another_name')->call('saveStructure')->assertHasErrors(['data.name']);
        $page->set('data.name', 'renamed_page_fixture')->call('preview')->assertHasNoErrors();
        Schema::table('page_edit_fixture', fn (Blueprint $table) => $table->string('external')->nullable());
        $page->call('saveStructure')->assertHasErrors(['data.name']);
        $this->assertTrue(Schema::hasTable('page_edit_fixture'));
        $page = Livewire::withQueryParams(['table' => 'page_edit_fixture'])->test(\App\Filament\Pages\DatabaseEdit::class);
        $page->set('data.name', 'renamed_page_fixture')->call('preview')->assertHasNoErrors();
        $this->assertCount(1, $page->get('sql'), print_r($page->get('sql'), true));
        $page->call('saveStructure')->assertHasNoErrors()
            ->assertRedirect(\App\Filament\Pages\DatabaseTables::getUrl().'?table=renamed_page_fixture');
        $page = Livewire::withQueryParams(['table' => 'renamed_page_fixture'])->test(\App\Filament\Pages\DatabaseEdit::class);
        $page->set('dropConfirmation', 'renamed_page_fixture')->call('dropTable')->assertHasErrors(['dropConfirmation']);
        $page->call('previewDrop')->assertHasNoErrors()->set('dropConfirmation', 'wrong')->call('dropTable')->assertHasErrors(['dropConfirmation']);
        $this->assertTrue(Schema::hasTable('renamed_page_fixture'));
        $page->set('dropConfirmation', 'renamed_page_fixture')->call('dropTable')->assertHasNoErrors()->assertRedirect(\App\Filament\Pages\DatabaseTables::getUrl());
        $this->assertFalse(Schema::hasTable('renamed_page_fixture'));
        $this->assertDatabaseCount('data_types', 1);
        $page = Livewire::withQueryParams(['table' => 'pages'])->test(\App\Filament\Pages\DatabaseEdit::class);
        $page->call('previewDrop')->assertHasErrors(['data.name'])->set('dropConfirmation', 'pages');
        auth('filament')->user()->role->permissions()->detach(Permission::where('key', 'browse_database')->value('id'));
        auth('filament')->user()->unsetRelation('role');
        $page->call('dropTable')->assertStatus(403);
        $this->assertTrue(Schema::hasTable('pages'));
    }

    public function test_database_edit_guards_foreign_keys_composites_generated_columns_and_forged_origins(): void
    {
        Schema::create('guard_fixture', function (Blueprint $table): void { $table->id(); $table->string('label'); $table->string('code'); $table->unique(['label', 'code']); });
        Schema::create('guard_child', function (Blueprint $table): void { $table->id(); $table->foreignId('parent_id')->constrained('guard_fixture'); });
        DB::table('guard_fixture')->insert(['id' => 1, 'label' => 'Stay', 'code' => 'X']);
        DB::table('guard_child')->insert(['parent_id' => 1]);
        $service = app(\App\Services\Admin\DatabaseTableEditingService::class);
        $snapshot = $service->snapshot('guard_fixture'); $hash = $service->fingerprint($snapshot); $input = $service->definition('guard_fixture', $snapshot);
        foreach (['origin', 'renameComposite', 'referenced', 'tableRename', 'collision'] as $case) {
            $bad = $input;
            if ($case === 'origin') { $bad['columns'][1]['old_name'] = 'unknown'; }
            if ($case === 'renameComposite') { $bad['columns'][1]['name'] = 'new_label'; }
            if ($case === 'referenced') { array_shift($bad['columns']); }
            if ($case === 'tableRename') { $bad['name'] = 'guard_new'; }
            if ($case === 'collision') { $bad['name'] = 'pages'; }
            try { $service->apply('guard_fixture', $bad, $hash); $this->fail('Unsafe alteration accepted'); }
            catch (\Illuminate\Validation\ValidationException) { $this->assertDatabaseHas('guard_fixture', ['label' => 'Stay']); }
        }
        try { $service->drop('guard_fixture', $hash); $this->fail('Referenced table dropped'); }
        catch (\Illuminate\Validation\ValidationException) { $this->assertDatabaseHas('guard_child', ['parent_id' => 1]); }
        $this->get('/filament/database-tables/edit?table=missing')->assertRedirect();
        $this->admin(['browse_admin', 'browse_database']);
        $this->withoutExceptionHandling();
        try { $this->get('/filament/database-tables/edit?table=missing'); $this->fail('Missing table accepted'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { $this->assertSame(404, $exception->getStatusCode()); }
        config(['voyager.database.tables.hidden' => ['guard_fixture']]);
        try { $this->get('/filament/database-tables/edit?table=guard_fixture'); $this->fail('Hidden table accepted'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { $this->assertSame(404, $exception->getStatusCode()); }
    }

    public function test_database_creation_timestamp_helpers_match_original_and_do_not_duplicate_columns(): void
    {
        $this->admin(['browse_admin', 'browse_database']);
        $page = Livewire::test(\App\Filament\Pages\DatabaseCreate::class)->set('data.name', 'helpers_fixture')
            ->call('preview')->assertHasNoErrors()->call('addTimestamps')->assertSet('previewHash', null)->assertSet('sql', [])
            ->call('addTimestamps')->call('addSoftDeletes')->call('addSoftDeletes')->assertHasNoErrors();
        $columns = array_values($page->get('data.columns'));
        $this->assertSame(['id', 'created_at', 'updated_at', 'deleted_at'], array_column($columns, 'name'));
        foreach (array_slice($columns, 1) as $column) {
            $this->assertSame('timestamp', $column['type']);
            $this->assertTrue($column['nullable']);
            $this->assertFalse($column['unsigned']);
            $this->assertFalse($column['auto_increment']);
            $this->assertNull($column['default']);
            $this->assertNull($column['index']);
        }
        $this->assertFalse(Schema::hasTable('helpers_fixture'));
        $page->call('preview')->assertHasNoErrors()->call('createTable')->assertHasNoErrors();
        DB::table('helpers_fixture')->insert(['id' => 1]);
        $this->assertDatabaseHas('helpers_fixture', ['id' => 1, 'created_at' => null, 'updated_at' => null, 'deleted_at' => null]);
        $page = Livewire::test(\App\Filament\Pages\DatabaseCreate::class);
        $column = $columns[0];
        $many = [];
        for ($i = 0; $i < 99; $i++) { $many['c'.$i] = array_replace($column, ['name' => 'c'.$i]); }
        $page->set('data.columns', $many)->call('addTimestamps')->assertHasErrors(['data.columns']);
        $this->assertCount(99, $page->get('data.columns'));
        auth('filament')->user()->role->permissions()->detach(Permission::where('key', 'browse_database')->value('id'));
        auth('filament')->user()->unsetRelation('role');
        $page->call('addSoftDeletes')->assertStatus(403);
    }

    public function test_database_creation_compiles_original_mysql_types_without_connecting_or_executing_ddl(): void
    {
        config(['database.connections.compile_fixture' => ['driver' => 'mysql', 'database' => 'fixture', 'host' => '127.0.0.1', 'port' => 1, 'username' => 'fixture', 'password' => '', 'prefix' => '', 'version' => '8.0.36']]);
        $default = DB::getDefaultConnection();
        DB::setDefaultConnection('compile_fixture');
        Schema::clearResolvedInstance('db.schema');
        // Only the existence check is stubbed. Schema creation uses the real MySQL
        // compiler under pretend. A mock PDO supplies version metadata only;
        // any attempted statement or query fails the test.
        $connection = DB::connection();
        Schema::shouldReceive('hasTable')->andReturn(false);
        $pdo = \Mockery::mock(\PDO::class);
        $pdo->shouldReceive('getAttribute')->with(\PDO::ATTR_SERVER_VERSION)->andReturn('8.0.36');
        $connection->setPdo($pdo);
        $originalGrammar = $connection->getSchemaGrammar();
        try {
            $base = ['name' => 'value', 'type' => 'varchar', 'length' => '', 'nullable' => true, 'unsigned' => false, 'auto_increment' => false, 'index' => '', 'default' => null];
            $expected = ['integer' => 'int', 'bigint' => 'bigint', 'smallint' => 'smallint', 'tinyint' => 'tinyint',
                'string' => 'varchar(255)', 'char' => 'char(255)', 'text' => 'text', 'mediumtext' => 'mediumtext', 'longtext' => 'longtext',
                'boolean' => 'tinyint(1)', 'date' => 'date', 'datetime' => 'datetime', 'timestamp' => 'timestamp', 'time' => 'time', 'json' => 'json',
                'mediumint' => 'mediumint', 'varchar' => 'varchar(255)', 'tinytext' => 'tinytext', 'year' => 'year',
                'binary' => 'binary(255)', 'varbinary' => 'varbinary(255)', 'bit' => 'bit(1)', 'tinyblob' => 'tinyblob', 'blob' => 'blob',
                'mediumblob' => 'mediumblob', 'longblob' => 'longblob', 'float' => 'float', 'double' => 'double', 'decimal' => 'decimal(10, 0)',
                'geometry' => 'geometry', 'point' => 'point', 'linestring' => 'linestring', 'polygon' => 'polygon',
                'multipoint' => 'multipoint', 'multilinestring' => 'multilinestring', 'multipolygon' => 'multipolygon', 'geometrycollection' => 'geometrycollection'];
            $service = app(\App\Services\Admin\DatabaseTableCreationService::class);
            $this->assertEqualsCanonicalizing(\App\Services\Admin\DatabaseTableCreationService::TYPES, array_keys($expected));
            foreach ($expected as $type => $declaration) {
                $result = $service->preview(['name' => 'types_fixture', 'columns' => [array_replace($base, ['type' => $type])]]);
                $this->assertStringContainsString('`value` '.$declaration.' null', implode("\n", $result['sql']), $type);
                $this->assertSame($originalGrammar, $connection->getSchemaGrammar());
            }
            foreach (['integer', 'float', 'double'] as $type) {
                $result = $service->preview(['name' => 'types_fixture', 'columns' => [array_replace($base, ['type' => $type, 'length' => '12,3'])]]);
                $this->assertSame('', $result['definition']['columns'][0]['length']);
                $this->assertStringNotContainsString('(12', implode("\n", $result['sql']));
            }
            foreach (['decimal', 'double'] as $type) {
                $result = $service->preview(['name' => 'types_fixture', 'columns' => [array_replace($base, ['type' => $type, 'unsigned' => true, 'default' => ' 1.5 '])]]);
                $this->assertStringContainsString('unsigned', implode("\n", $result['sql']));
                $this->assertSame('1.5', $result['definition']['columns'][0]['default']);
            }
            $result = $service->preview(['name' => 'types_fixture', 'columns' => [array_replace($base, ['type' => 'binary', 'length' => '16', 'index' => 'UNIQUE', 'default' => "a'b"])]]);
            $this->assertStringContainsString('binary(16)', implode("\n", $result['sql']));
            $this->assertStringContainsString("default 'a''b'", implode("\n", $result['sql']));
            foreach ([['type' => 'enum'], ['type' => 'set'], ['type' => 'bit', 'length' => '65'], ['type' => 'binary', 'length' => '256'], ['type' => 'tinyblob', 'default' => 'x'], ['type' => 'year', 'default' => '1900']] as $bad) {
                try { $service->preview(['name' => 'types_fixture', 'columns' => [array_replace($base, $bad)]]); $this->fail('Invalid original type options accepted'); }
                catch (\Illuminate\Validation\ValidationException) { $this->assertSame($originalGrammar, $connection->getSchemaGrammar()); }
            }
            $connection->beforeExecuting(fn () => throw new \RuntimeException('Fixture compilation failure'));
            try { $service->preview(['name' => 'types_fixture', 'columns' => [$base]]); $this->fail('Fixture failure was ignored'); }
            catch (\RuntimeException $exception) {
                $this->assertSame('Fixture compilation failure', $exception->getMessage());
                $this->assertSame($originalGrammar, $connection->getSchemaGrammar());
            }
        } finally {
            DB::setDefaultConnection($default);
            $this->app->forgetInstance('db.schema');
            Schema::clearResolvedInstance('db.schema');
            DB::purge('compile_fixture');
        }
        $this->assertFalse(Schema::hasTable('types_fixture'));
    }

    public function test_database_creation_extended_native_types_defaults_and_type_switch(): void
    {
        $this->admin(['browse_admin', 'browse_database']);
        $base = ['name' => 'value', 'type' => 'varchar', 'length' => '', 'nullable' => false, 'unsigned' => false, 'auto_increment' => false, 'index' => '', 'default' => null];
        $columns = [array_replace($base, ['name' => 'count', 'type' => 'mediumint', 'default' => '0']),
            array_replace($base, ['name' => 'year', 'type' => 'year', 'default' => '2026']),
            array_replace($base, ['name' => 'label', 'default' => '  label  ']),
            array_replace($base, ['name' => 'bytes', 'type' => 'varbinary', 'length' => '16', 'default' => 'bytes']),
            array_replace($base, ['name' => 'amount', 'type' => 'decimal', 'unsigned' => true, 'default' => '0'])];
        $service = app(\App\Services\Admin\DatabaseTableCreationService::class);
        $service->create(['name' => 'native_types_fixture', 'columns' => $columns]);
        DB::table('native_types_fixture')->insert(['year' => 2026]);
        $this->assertDatabaseHas('native_types_fixture', ['count' => 0, 'year' => 2026, 'label' => 'label', 'bytes' => 'bytes', 'amount' => 0]);
        foreach (['point', 'tinyblob'] as $type) {
            try { $service->create(['name' => 'unsupported_fixture', 'columns' => [array_replace($base, ['type' => $type])]]); $this->fail('MySQL-only type accepted on SQLite'); }
            catch (\Illuminate\Validation\ValidationException) { $this->assertFalse(Schema::hasTable('unsupported_fixture')); }
        }
        $page = Livewire::test(\App\Filament\Pages\DatabaseCreate::class)->set('data.name', 'switch_fixture');
        $key = array_key_first($page->get('data.columns'));
        $page->set('data.columns.'.$key.'.default', 'old')->set('data.columns.'.$key.'.type', 'text');
        $this->assertNull($page->get('data.columns.'.$key.'.default'));
        $this->assertNull($page->get('data.columns.'.$key.'.index'));
        $page->call('preview')->assertHasNoErrors();
        $this->assertFalse(Schema::hasTable('switch_fixture'));
    }

    public function test_database_creation_previews_then_creates_columns_indexes_and_defaults_only_after_submit(): void
    {
        $this->admin(['browse_admin', 'browse_database']);
        $columns = [
            ['name' => 'id', 'type' => 'integer', 'length' => '', 'nullable' => false, 'unsigned' => true, 'auto_increment' => true, 'index' => 'PRIMARY', 'default' => null],
            ['name' => 'code', 'type' => 'string', 'length' => '80', 'nullable' => false, 'unsigned' => false, 'auto_increment' => false, 'index' => 'UNIQUE', 'default' => 'initial'],
            ['name' => 'amount', 'type' => 'decimal', 'length' => '10,2', 'nullable' => false, 'unsigned' => false, 'auto_increment' => false, 'index' => '', 'default' => '1.25'],
            ['name' => 'note', 'type' => 'text', 'length' => '', 'nullable' => true, 'unsigned' => false, 'auto_increment' => false, 'index' => '', 'default' => null],
        ];
        $this->get('/filament/database-tables/create')->assertOk()->assertSee('Создание таблицы');
        $page = Livewire::test(\App\Filament\Pages\DatabaseCreate::class)
            ->set('data.name', 'creation_fixture')->set('data.columns', $columns)
            ->call('createTable')->assertHasErrors(['data.name']);
        $this->assertFalse(Schema::hasTable('creation_fixture'));
        $page->call('preview')->assertHasNoErrors()->assertSee('create table');
        $this->assertFalse(Schema::hasTable('creation_fixture'));
        $page->set('data.name', 'changed_fixture')->call('createTable')->assertHasErrors(['data.name']);
        $this->assertFalse(Schema::hasTable('changed_fixture'));
        $page->set('data.name', 'creation_fixture')->call('preview')->assertHasNoErrors()
            ->call('createTable')->assertHasNoErrors()->assertRedirect(\App\Filament\Pages\DatabaseTables::getUrl().'?table=creation_fixture');
        $this->assertSame(['id', 'code', 'amount', 'note'], Schema::getColumnListing('creation_fixture'));
        DB::table('creation_fixture')->insert(['note' => null]);
        $this->assertDatabaseHas('creation_fixture', ['id' => 1, 'code' => 'initial', 'amount' => '1.25', 'note' => null]);
        $this->assertTrue(collect(Schema::getIndexes('creation_fixture'))->contains(fn ($index) => $index['unique'] && $index['columns'] === ['code']));
        $this->assertDatabaseCount('data_types', 1);
        $this->assertDatabaseCount('pages', 0);
        $page->call('preview')->assertHasErrors(['data.name']);
    }

    public function test_database_creation_rejects_invalid_schema_and_rechecks_permission(): void
    {
        $this->admin(['browse_admin', 'browse_database']);
        $column = ['name' => 'value', 'type' => 'string', 'length' => '80', 'nullable' => false, 'unsigned' => false, 'auto_increment' => false, 'index' => '', 'default' => null];
        $service = app(\App\Services\Admin\DatabaseTableCreationService::class);
        foreach ([['name' => 'bad; DROP TABLE pages', 'columns' => [$column]],
            ['name' => 'pages', 'columns' => [$column]],
            ['name' => 'invalid_fixture', 'columns' => [$column, $column]],
            ...array_map(fn ($changes) => ['name' => 'invalid_fixture', 'columns' => [array_replace($column, $changes)]], [
                ['type' => 'unknown'], ['auto_increment' => true], ['unsigned' => true], ['length' => '0'],
                ['type' => 'decimal', 'length' => '2,4'], ['type' => 'text', 'length' => '', 'index' => 'UNIQUE'],
                ['index' => 'PRIMARY', 'nullable' => true], ['type' => 'boolean', 'length' => '', 'default' => '2'],
                ['type' => 'integer', 'length' => '', 'default' => 'SQL()'], ['raw_sql' => 'drop table pages'],
                ['type' => 'date', 'length' => '', 'default' => 'CURRENT_TIMESTAMP'], ['length' => '2', 'default' => 'long'],
            ])] as $invalid) {
            try { $service->create($invalid); $this->fail('Invalid schema accepted'); }
            catch (\Illuminate\Validation\ValidationException) { $this->assertFalse(Schema::hasTable('invalid_fixture')); }
        }
        $page = Livewire::test(\App\Filament\Pages\DatabaseCreate::class)->set('data.name', 'revoked_fixture')->set('data.columns', [$column])->call('preview')->assertHasNoErrors();
        auth('filament')->user()->role->permissions()->detach(Permission::where('key', 'browse_database')->value('id'));
        auth('filament')->user()->unsetRelation('role');
        $page->call('createTable')->assertStatus(403);
        $this->get('/filament/database-tables/create')->assertForbidden();
        $this->assertFalse(Schema::hasTable('revoked_fixture'));
        $this->assertTrue(Schema::hasTable('pages'));
    }

    public function test_database_creation_failure_clears_preview_and_keeps_partial_schema_for_review(): void
    {
        $this->admin(['browse_admin', 'browse_database']);
        $page = Livewire::test(\App\Filament\Pages\DatabaseCreate::class)->set('data.name', 'partial_fixture')
            ->call('preview')->assertHasNoErrors();
        $this->app->instance(\App\Services\Admin\DatabaseTableCreationService::class, new class extends \App\Services\Admin\DatabaseTableCreationService {
            public function create(array $input): void
            {
                Schema::create($input['name'], fn (Blueprint $table) => $table->id());
                throw new \Illuminate\Database\QueryException('sqlite', 'Fixture index failure', [], new \PDOException('Fixture failure'));
            }
        });
        $page->call('createTable')->assertHasErrors(['data.name'])->assertSet('previewHash', null)->assertSet('sql', []);
        $this->assertTrue(Schema::hasTable('partial_fixture'));
        $this->assertDatabaseCount('data_types', 1);
        $page->call('preview')->assertHasErrors(['data.name']);
        $this->assertTrue(Schema::hasTable('pages'));
    }

    public function test_database_catalog_inspects_schema_without_modifying_records_or_bread(): void
    {
        $this->admin(['browse_admin', 'browse_database']);
        Schema::create('database_fixture', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->default('initial')->unique();
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();
        });
        $pageId = DB::table('pages')->insertGetId(['title' => 'Keep']);
        DB::table('database_fixture')->insert(['page_id' => $pageId]);
        $before = Schema::getColumns('database_fixture');
        $this->get('/filament/database-tables')->assertOk()->assertSee('База данных');
        $page = Livewire::test(\App\Filament\Pages\DatabaseTables::class)
            ->assertDontSee('Редактировать BREAD')->call('inspect', 'database_fixture')
            ->assertSee('Структура:')->assertSee('PRIMARY')->assertSee('UNIQUE')->assertSee('set null');
        $this->assertStringContainsString('?table=database_fixture', $page->html());
        $this->get('/filament/database-tables?table=database_fixture')->assertOk()->assertSee('Структура: database_fixture');
        $structure = $page->instance()->structure();
        $this->assertSame(['id', 'code', 'page_id'], array_column($structure['columns'], 'name'));
        $this->assertSame('pages', $structure['foreign_keys'][0]['foreign_table']);
        $page->call('closeStructure')->assertSet('selectedTable', null)->set('search', 'database_fixture');
        $this->assertSame(['database_fixture'], array_column($page->instance()->tables(), 'name'));
        $this->assertSame($before, Schema::getColumns('database_fixture'));
        $this->assertDatabaseHas('database_fixture', ['code' => 'initial', 'page_id' => $pageId]);
        $this->assertDatabaseCount('data_types', 1);
        config(['voyager.database.tables.hidden' => ['database_fixture']]);
        $this->assertSame([], app(\App\Services\Admin\DatabaseInspectionService::class)->tables('database_fixture'));
        $this->withoutExceptionHandling();
        foreach (['database_fixture', 'pages; DROP TABLE pages', 'missing_table'] as $invalidTable) {
            try {
                Livewire::test(\App\Filament\Pages\DatabaseTables::class)->call('inspect', $invalidTable);
                $this->fail('Unavailable table inspected');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
                $this->assertSame(404, $exception->getStatusCode());
            }
        }
        $this->assertTrue(Schema::hasTable('pages'));
    }

    public function test_database_catalog_requires_its_own_permission_on_page_and_livewire_requests(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $this->get('/filament/database-tables')->assertForbidden();
        $role = auth('filament')->user()->role;
        $permission = Permission::create(['key' => 'browse_database']);
        $role->permissions()->attach($permission);
        auth('filament')->user()->unsetRelation('role');
        $page = Livewire::test(\App\Filament\Pages\DatabaseTables::class)->assertSee('Редактировать BREAD');
        $role->permissions()->detach($permission);
        auth('filament')->user()->unsetRelation('role');
        $page->call('inspect', 'pages')->assertStatus(403);
    }

    public function test_database_catalog_handles_connection_prefix_without_double_prefixing(): void
    {
        $connection = DB::connection();
        $connection->setTablePrefix('fixture_');
        try {
            Schema::create('sample', function (Blueprint $table): void { $table->id(); });
            $service = app(\App\Services\Admin\DatabaseInspectionService::class);
            $this->assertSame(['sample'], array_column($service->tables(), 'name'));
            $this->assertSame(['id'], array_column($service->describe('sample')['columns'], 'name'));
        } finally {
            $connection->setTablePrefix('');
        }
    }

    public function test_jpeg_exif_orientations_visible_corners_thumbnails_and_webp(): void
    {
        Storage::fake('public');
        $image = imagecreatetruecolor(80, 40);
        $colors = ['A' => [255, 0, 0], 'B' => [0, 255, 0], 'C' => [0, 0, 255], 'D' => [255, 255, 0]];
        foreach (['A' => [0, 0, 39, 19], 'B' => [40, 0, 79, 19], 'C' => [0, 20, 39, 39], 'D' => [40, 20, 79, 39]] as $name => $rectangle) {
            imagefilledrectangle($image, ...[...$rectangle, imagecolorallocate($image, ...$colors[$name])]);
        }
        ob_start(); imagejpeg($image, null, 100); $jpeg = ob_get_clean(); imagedestroy($image);
        // Independent EXIF contract: TL, TR, BL, BR after display orientation.
        $expected = [1 => ['A', 'B', 'C', 'D'], 2 => ['B', 'A', 'D', 'C'], 3 => ['D', 'C', 'B', 'A'], 4 => ['C', 'D', 'A', 'B'],
            5 => ['A', 'C', 'B', 'D'], 6 => ['C', 'A', 'D', 'B'], 7 => ['D', 'B', 'C', 'A'], 8 => ['B', 'D', 'A', 'C'], 9 => ['A', 'B', 'C', 'D']];
        $corners = static function (string $bytes) use ($colors): array {
            $decoded = imagecreatefromstring($bytes); $width = imagesx($decoded); $height = imagesy($decoded); $labels = [];
            foreach ([[.25, .25], [.75, .25], [.25, .75], [.75, .75]] as [$x, $y]) {
                $pixel = imagecolorat($decoded, (int) ($width * $x), (int) ($height * $y));
                $rgb = [($pixel >> 16) & 255, ($pixel >> 8) & 255, $pixel & 255]; $distances = [];
                foreach ($colors as $name => $color) { $distances[$name] = array_sum(array_map(fn ($a, $b) => ($a - $b) ** 2, $rgb, $color)); }
                asort($distances); $labels[] = array_key_first($distances);
            }
            imagedestroy($decoded); return $labels;
        };
        $service = app(\App\Filament\Bread\BreadImageUpload::class);
        foreach ($expected as $orientation => $labels) {
            // Minimal little-endian TIFF IFD with an Orientation SHORT tag.
            $exif = "Exif\0\0II".pack('vVv', 42, 8, 1).pack('vvV', 0x0112, 3, 1).pack('v', $orientation)."\0\0".pack('V', 0);
            $fixture = substr($jpeg, 0, 2)."\xFF\xE1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2);
            $file = UploadedFile::fake()->createWithContent('orientation'.$orientation.'.jpg', $fixture);
            $this->assertSame($orientation, exif_read_data($file->getRealPath())['Orientation']);
            $path = $service->store($file, 'public', 'exif', ['quality' => 100, 'preserveFileUploadName' => true,
                'thumbnails' => [['name' => 'half', 'scale' => 50], ['name' => 'square', 'crop' => ['width' => 20, 'height' => 20]]]], 'image');
            $size = in_array($orientation, [5, 6, 7, 8], true) ? [40, 80] : [80, 40];
            foreach ([$path => $size, image_webp_path($path) => $size, str_replace('.jpg', '-half.jpg', $path) => [$size[0] / 2, $size[1] / 2], str_replace('.jpg', '-square.jpg', $path) => [20, 20]] as $output => $dimensions) {
                $bytes = Storage::disk('public')->get($output);
                $this->assertEquals($dimensions, array_slice(getimagesizefromstring($bytes), 0, 2), 'Dimensions '.$output);
                $this->assertSame($labels, $corners($bytes), 'Corners '.$output);
            }
            $this->assertSame($fixture, file_get_contents($file->getRealPath()));
            $outputExif = @exif_read_data(Storage::disk('public')->path($path));
            $this->assertFalse(isset($outputExif['Orientation']));
        }
        $plain = $service->store(UploadedFile::fake()->createWithContent('plain.jpg', $jpeg), 'public', 'exif', [], 'image');
        $this->assertSame($expected[1], $corners(Storage::disk('public')->get($plain)));
    }

    public function test_image_webp_companions_collisions_transparency_and_nonfatal_failures(): void
    {
        Storage::fake('public');
        $service = app(\App\Filament\Bread\BreadImageUpload::class);
        $details = ['resize' => ['width' => 30], 'preserveFileUploadName' => true];
        $path = $service->store(UploadedFile::fake()->image('new.jpg', 60, 40), 'public', 'webp-test', $details, 'image');
        $webp = image_webp_path($path);
        $info = getimagesizefromstring(Storage::disk('public')->get($webp));
        $this->assertSame('image/webp', $info['mime']); $this->assertSame([30, 20], array_slice($info, 0, 2));
        $original = Storage::disk('public')->get($path); $companion = Storage::disk('public')->get($webp);
        Storage::disk('public')->put('webp-test/reserved.webp', 'Existing companion');
        $collision = $service->store(UploadedFile::fake()->image('reserved.png', 10, 10), 'public', 'webp-test', $details, 'image');
        $this->assertSame('webp-test/reserved1.png', $collision);
        $this->assertSame('Existing companion', Storage::disk('public')->get('webp-test/reserved.webp'));
        $this->assertTrue(Storage::disk('public')->exists('webp-test/reserved1.webp'));
        $this->assertSame($original, Storage::disk('public')->get($path)); $this->assertSame($companion, Storage::disk('public')->get($webp));
        $image = imagecreatetruecolor(10, 10); imagealphablending($image, false); imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127)); ob_start(); imagepng($image); $png = ob_get_clean(); imagedestroy($image);
        $alpha = $service->store(UploadedFile::fake()->createWithContent('alpha.png', $png), 'public', 'webp-test', ['preserveFileUploadName' => true], 'image');
        $decoded = imagecreatefromstring(Storage::disk('public')->get(image_webp_path($alpha)));
        $this->assertSame(127, (imagecolorat($decoded, 0, 0) >> 24) & 127); imagedestroy($decoded);
        $native = $service->store(UploadedFile::fake()->image('native.webp', 10, 10), 'public', 'native', ['preserveFileUploadName' => true], 'image');
        $this->assertSame('native/native.webp', $native); $this->assertCount(1, Storage::disk('public')->files('native'));
        $gif = UploadedFile::fake()->image('anim.gif', 60, 40); $raw = file_get_contents($gif->getRealPath());
        $gifPath = $service->store($gif, 'public', 'webp-test', $details, 'image');
        $this->assertSame($raw, Storage::disk('public')->get($gifPath));
        $this->assertSame([60, 40], array_slice(getimagesizefromstring(Storage::disk('public')->get(image_webp_path($gifPath))), 0, 2));
        $failingCodec = new class extends \App\Filament\Bread\BreadImageUpload {
            protected function webpBytes(string $bytes, string $path): string { throw new \RuntimeException('Test codec failure'); }
        };
        $kept = $failingCodec->store(UploadedFile::fake()->image('codec.jpg', 10, 10), 'public', 'webp-test', $details, 'image');
        $this->assertTrue(Storage::disk('public')->exists($kept)); $this->assertFalse(Storage::disk('public')->exists(image_webp_path($kept)));
        $brokenDisk = \Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
        $brokenDisk->shouldReceive('exists')->andReturn(false);
        $brokenDisk->shouldReceive('put')->once()->ordered()->andReturn(true);
        $brokenDisk->shouldReceive('put')->once()->ordered()->andReturn(false);
        $brokenDisk->shouldReceive('delete')->once()->with('optional/failed.webp')->andReturn(true);
        Storage::shouldReceive('disk')->with('optional-failure')->andReturn($brokenDisk);
        $this->assertSame('optional/failed.jpg', $service->store(UploadedFile::fake()->image('failed.jpg', 10, 10), 'optional-failure', 'optional', ['preserveFileUploadName' => true], 'image'));
    }

    public function test_blog_rich_editor_tools_preserve_breaks_and_justify_in_saved_translation(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')
            ->update(['type' => 'rich_text_box', 'details' => json_encode(['tinymceOptions' => ['toolbar' => 'removeformat alignjustify insertBrButton']])]);
        $id = DB::table('pages')->insertGetId(['title' => 'Editor', 'meta_description' => '<p>Original</p>']);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $container = $editor->instance()->form;
        $service = app(\App\Filament\Bread\BreadRichEditor::class);
        $blog = $service->component('body', [], 'blog_posts')->container($container);
        $this->assertContains('alignJustify', $blog->getToolbarButtons()[2]);
        $this->assertContains(['clearFormatting', 'breadBreak'], $blog->getToolbarButtons());
        $plain = $service->component('body', [], 'pages')->container($container);
        $this->assertNotContains(['clearFormatting', 'breadBreak'], $plain->getToolbarButtons());
        $service->validate(['tinymceOptions' => ['toolbar' => 'removeformat alignjustify insertBrButton']]);
        $configured = $service->component('body', ['tinymceOptions' => ['toolbar' => 'removeformat alignjustify insertBrButton']], 'blog_posts')->container($container);
        $this->assertSame([['clearFormatting', 'alignJustify', 'breadBreak']], $configured->getToolbarButtons());
        $this->assertSame('$getEditor()?.chain().focus().setHardBreak().run()', $blog->getTools()['breadBreak']->getJsHandler());
        $this->assertSame([], $service->component('body', ['tinymceOptions' => ['toolbar' => false]], 'blog_posts')->container($container)->getToolbarButtons());

        $html = '<p style="text-align: justify;">Первая строка<br>Вторая строка</p>';
        $editor->call('changeLocale', 'ru')->set('data.meta_description', $html)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'column_name' => 'meta_description', 'locale' => 'ru', 'value' => $html]);
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => '<p>Original</p>']);
    }

    public function test_blog_text_styles_roundtrip_and_keep_unsupported_html_in_source_mode(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')
            ->update(['type' => 'rich_text_box', 'details' => json_encode(['tinymceOptions' => ['toolbar' => 'fontsizeselect backcolor']])]);
        $id = DB::table('pages')->insertGetId(['title' => 'Styled draft', 'meta_description' => '<p>Original</p>']);
        $page = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $component = collect($page->instance()->form->getFlatComponents())->first(fn ($field) => $field instanceof \Filament\Forms\Components\RichEditor);
        $this->assertSame([['breadFontSize', 'breadBackgroundColor']], $component->getToolbarButtons());
        $html = '<p style="text-align: justify;"><span style="font-size: 18pt; background-color: rgb(255, 204, 0)">Текст</span><br>End</p>';
        $editor = $component->getTipTapEditor()->setContent($html);
        $rendered = $editor->getHTML();
        $this->assertStringContainsString('font-size: 18pt', $rendered);
        $this->assertStringContainsString('background-color: #ffcc00', $rendered);
        $this->assertStringContainsString('<br', $rendered);
        $this->assertStringContainsString('text-align: justify', $rendered);
        $this->assertSame($rendered, $component->getTipTapEditor()->setContent($rendered)->getHTML());
        $service = app(\App\Filament\Bread\BreadRichEditor::class);
        $this->assertFalse($service->requiresSource($html, 'blog_posts'));
        $this->assertTrue($service->requiresSource($html, 'pages'));
        foreach (['<span style="font-size: 999pt">Keep</span>', '<span style="background-color: url(https://example.test/x)">Keep</span>',
            '<span style="font-size:18pt; position:fixed">Keep</span>', '<p class="image-row custom"><img src="/old.jpg"></p>'] as $legacy) {
            $this->assertTrue($service->requiresSource($legacy, 'blog_posts'));
        }
        $this->assertNull(\App\Filament\Bread\BreadTextStyle::color('rgb(999,0,0)'));
        $this->assertNull(\App\Filament\Bread\BreadTextStyle::color('#ffcc00; position:fixed'));
        $this->assertSame('#ffcc00', \App\Filament\Bread\BreadTextStyle::color('rgb(255,204,0)'));
        $page->call('changeLocale', 'ru')->set('data.meta_description', $rendered)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'column_name' => 'meta_description', 'locale' => 'ru', 'value' => $rendered]);
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => '<p>Original</p>']);
    }

    public function test_blog_block_formats_and_indents_roundtrip_without_losing_inline_styles(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')
            ->update(['type' => 'rich_text_box', 'details' => json_encode(['tinymceOptions' => ['toolbar' => 'formatselect outdent indent']])]);
        $id = DB::table('pages')->insertGetId(['title' => 'Formats', 'meta_description' => '<p>Original</p>']);
        $page = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $component = collect($page->instance()->form->getFlatComponents())->first(fn ($field) => $field instanceof \Filament\Forms\Components\RichEditor);
        $this->assertSame([['breadFormat', 'breadOutdent', 'breadIndent']], $component->getToolbarButtons());
        $this->assertCount(4, $component->getTipTapJsExtensions());
        $service = app(\App\Filament\Bread\BreadRichEditor::class);
        foreach (['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $tag) {
            $html = '<'.$tag.' style="padding-left: 80px; text-align: justify;"><span style="font-size:18pt;background-color:#ffcc00">Текст</span><br>End</'.$tag.'>';
            $output = $component->getTipTapEditor()->setContent($html)->getHTML();
            $this->assertStringContainsString('<'.$tag.' ', $output);
            $this->assertStringContainsString('padding-left: 80px', $output);
            $this->assertStringContainsString('text-align: justify', $output);
            $this->assertStringContainsString('font-size: 18pt', $output);
            $this->assertStringContainsString('background-color: #ffcc00', $output);
            $this->assertSame($output, $component->getTipTapEditor()->setContent($output)->getHTML());
            $this->assertFalse($service->requiresSource($output, 'blog_posts'));
        }
        foreach (['-40px', '41px', 'calc(40px)', '40px;position:fixed', '1000000px'] as $value) {
            $this->assertNull(\App\Filament\Bread\BreadBlockStyle::indent($value));
            $this->assertTrue($service->requiresSource('<p style="padding-left:'.$value.'">Keep</p>', 'blog_posts'));
        }
        $this->assertSame(40, \App\Filament\Bread\BreadBlockStyle::indent('40px'));
        $this->assertSame(0, \App\Filament\Bread\BreadBlockStyle::indent('0px'));
        $html = '<h4 style="padding-left:40px">Заголовок</h4><pre style="padding-left:80px"><code>plain &lt;b&gt;text&lt;/b&gt;</code></pre><ul><li><p>One</p><ul><li><p>Two</p></li></ul></li></ul>';
        $output = $component->getTipTapEditor()->setContent($html)->getHTML();
        $this->assertStringContainsString('<h4', $output);
        $this->assertStringContainsString('<pre', $output);
        $this->assertStringContainsString('padding-left: 80px', $output);
        $this->assertFalse($service->requiresSource($output, 'blog_posts'));
        $this->assertStringContainsString('plain &lt;b&gt;text&lt;/b&gt;', $output);
        $this->assertSame(2, substr_count($output, '<ul'));
        $page->call('changeLocale', 'ru')->set('data.meta_description', $output)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'column_name' => 'meta_description', 'locale' => 'ru', 'value' => $output]);
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => '<p>Original</p>']);
    }

    public function test_blog_image_row_roundtrip_preserves_images_and_rejects_lossy_legacy_rows(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')
            ->update(['type' => 'rich_text_box', 'details' => '{"tinymceOptions":{"toolbar":"mergeImageRowButton"}}']);
        $id = DB::table('pages')->insertGetId(['title' => 'Rows', 'meta_description' => '<p>Original</p>']);
        $page = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $component = collect($page->instance()->form->getFlatComponents())->first(fn ($field) => $field instanceof \Filament\Forms\Components\RichEditor);
        $this->assertSame([['breadImageRow']], $component->getToolbarButtons());
        $html = '<p>Before</p><p class="image-row"><img src="/one.jpg" alt="One" title="First" width="200" height="100"><img src="https://example.test/two.jpg?x=1" alt="Two"></p><p>After</p>';
        $editor = $component->getTipTapEditor()->setContent($html);
        $output = $editor->getHTML();
        $this->assertStringContainsString('<p class="image-row">', $output);
        $this->assertSame(2, substr_count($output, '<img'));
        $this->assertStringContainsString('src="/one.jpg"', $output);
        $this->assertStringContainsString('title="First"', $output);
        $this->assertStringContainsString('width="200"', $output);
        $this->assertStringContainsString('height="100"', $output);
        $this->assertStringContainsString('src="https://example.test/two.jpg?x=1"', $output);
        $this->assertStringContainsString('Before', $output); $this->assertStringContainsString('After', $output);
        $this->assertSame($output, $component->getTipTapEditor()->setContent($output)->getHTML());
        $this->assertStringContainsString('breadImageRow', $editor->getJSON());
        $figure = '<figure class="image" contenteditable="false"><img src="/caption.jpg" width="200" height="100" title="Title"><figcaption contenteditable="true">Подпись <strong>картинки</strong></figcaption></figure>';
        $figureOutput = $component->getTipTapEditor()->setContent($figure)->getHTML();
        $this->assertStringContainsString('<figure class="image">', $figureOutput);
        $this->assertStringContainsString('<figcaption>Подпись <strong>картинки</strong></figcaption>', $figureOutput);
        $this->assertSame($figureOutput, $component->getTipTapEditor()->setContent($figureOutput)->getHTML());
        $service = app(\App\Filament\Bread\BreadRichEditor::class);
        $this->assertFalse($service->requiresSource($figure, 'blog_posts'));
        $this->assertFalse($service->requiresSource($figureOutput, 'blog_posts'));
        $this->assertTrue($service->requiresSource('<figure class="image"><img src="/one.jpg"><p>Unsupported caption</p></figure>', 'blog_posts'));
        $this->assertTrue($service->requiresSource('<figure class="image"><img src="/one.jpg"><figcaption><p>Block caption</p></figcaption></figure>', 'blog_posts'));
        $this->assertFalse($service->requiresSource('<p class="image-row"><img src="/one.jpg"><img src="/two.jpg"></p>', 'blog_posts'));
        $this->assertFalse($service->requiresSource($output, 'blog_posts'));
        $this->assertFalse($service->requiresSource('<p class="image-row"><img data-id="owned-id" src="/one.jpg" loading="lazy" style="width:200px;height:100px"></p>', 'blog_posts'));
        $this->assertTrue($service->requiresSource('<img src="/one.jpg" style="width:200px;position:fixed">', 'blog_posts'));
        $linked = '<figure class="image"><a href="/caption-link" title="Linked"><img src="/caption.jpg"></a><figcaption>Caption</figcaption></figure><p class="image-row"><a href="/row-link"><img src="/row.jpg"></a></p>';
        $linkedOutput = $component->getTipTapEditor()->setContent($linked)->getHTML();
        $this->assertFalse($service->requiresSource($linkedOutput, 'blog_posts'));
        $this->assertStringContainsString('href="/caption-link"', $linkedOutput);
        $this->assertStringContainsString('href="/row-link"', $linkedOutput);
        $this->assertSame($linkedOutput, $component->getTipTapEditor()->setContent($linkedOutput)->getHTML());
        foreach (['<p class="image-row"></p>', '<p class="image-row">Caption<img src="/one.jpg"></p>', '<p class="image-row"><a href="/link">Text<img src="/one.jpg"></a></p>',
            '<p class="image-row extra"><img src="/one.jpg"></p>'] as $legacy) {
            $this->assertTrue($service->requiresSource($legacy, 'blog_posts'));
        }
        $page->call('changeLocale', 'ru')->set('data.meta_description', $output)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'column_name' => 'meta_description', 'locale' => 'ru', 'value' => $output]);
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => '<p>Original</p>']);
    }

    public function test_blog_image_properties_validate_and_only_dispatch_selected_image_attributes(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')
            ->update(['type' => 'rich_text_box', 'details' => '{"tinymceOptions":{"toolbar":"mergeImageRowButton"}}']);
        $id = DB::table('pages')->insertGetId(['title' => 'Image draft', 'meta_description' => '<p>Original</p>']);
        $page = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $selection = ['anchor' => 1, 'head' => 2, 'type' => 'node'];
        $action = \Filament\Actions\Testing\TestAction::make('breadImageProperties')->schemaComponent('meta_description', 'form')
            ->arguments(['editorSelection' => $selection, 'attributes' => ['src' => '/keep.jpg', 'id' => 'keep-id', 'alt' => 'Old', 'width' => '200']]);
        $page->callAction($action, ['alt' => 'Draft', 'title' => 'Title', 'width' => '180', 'height' => '100px;position:fixed'])
            ->assertHasActionErrors(['height']);
        $page->callAction($action, ['src' => 'javascript:alert(1)', 'alt' => 'Draft'])->assertHasActionErrors(['src']);
        $page->callAction($action, ['src' => '/changed.jpg', 'alt' => 'Draft', 'title' => 'Title', 'width' => '180', 'height' => '100', 'caption' => true, 'caption_text' => 'Подпись', 'constrain' => false])
            ->assertHasNoActionErrors()->assertDispatched('run-rich-editor-commands', function ($name, $parameters) use ($selection): bool {
                return $parameters['editorSelection'] === $selection
                    && $parameters['commands'] === [['name' => 'applyBreadImageProperties', 'arguments' => [[
                        'src' => '/changed.jpg', 'alt' => 'Draft', 'title' => 'Title', 'width' => '180', 'height' => '100',
                    ], true, 'Подпись', false]]];
            });
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => '<p>Original</p>']);
        $this->assertDatabaseCount('translations', 0);
        $page->set('componentFileAttachments.data.meta_description.keep-id', 'pending-upload');
        $page->callAction($action, ['src' => '/changed.jpg', 'alt' => 'Draft'])->assertHasActionErrors(['src']);
    }

    public function test_rich_editor_safe_tinymce_options_and_content_translations(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $rowId = DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')->value('id');
        $details = ['tinymceOptions' => ['toolbar' => 'bold italic | bullist numlist link', 'height' => 300, 'placeholder' => 'Текст']];
        DB::table('data_rows')->where('id', $rowId)->update(['type' => 'rich_text_box', 'details' => json_encode($details)]);
        $id = DB::table('pages')->insertGetId(['title' => 'Editor', 'meta_description' => '<p>Original <strong>HTML</strong></p>']);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $field = collect($editor->instance()->form->getFlatComponents())->first(fn ($field) => $field instanceof \Filament\Forms\Components\RichEditor);
        $this->assertSame([['bold', 'italic'], ['bulletList', 'orderedList', 'link']], $field->getToolbarButtons());
        $this->assertSame('Текст', $field->getPlaceholder());
        $this->assertSame('min-height:300px', $field->getExtraInputAttributes()['style']);
        $editor->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => '<p>Original <strong>HTML</strong></p>']);
        $editor->call('changeLocale', 'ru')->set('data.meta_description', '<p>Перевод</p>')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'column_name' => 'meta_description', 'locale' => 'ru', 'value' => '<p>Перевод</p>']);
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId);
        foreach ([['height' => 2], ['toolbar' => 'unknown'], ['plugins' => 'custom']] as $bad) { $metadata->set('data.details', json_encode(['tinymceOptions' => $bad]))->call('save')->assertHasErrors(['details']); }
        $metadata->set('data.details', '{"tinymceOptions":{"toolbar":false}}')->call('save')->assertHasNoErrors();
        $noToolbar = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $field = collect($noToolbar->instance()->form->getFlatComponents())->first(fn ($field) => $field instanceof \Filament\Forms\Components\RichEditor);
        $this->assertSame([], $field->getToolbarButtons());
        DB::table('data_rows')->where('id', $rowId)->update(['details' => '{"tinymceOptions":{"plugins":"old-plugin"}}']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('Часть настроек TinyMCE')->set('data.title', 'Kept')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => '<p>Original <strong>HTML</strong></p>']);
    }

    public function test_rich_editor_html_source_is_a_draft_and_saves_current_translation(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $rowId = DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')->value('id');
        DB::table('data_rows')->where('id', $rowId)->update(['type' => 'rich_text_box', 'details' => '{}']);
        $id = DB::table('pages')->insertGetId(['title' => 'HTML editor', 'meta_description' => '<p>Original</p>']);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $field = collect($editor->instance()->form->getFlatComponents())->first(fn ($field) => $field instanceof \Filament\Forms\Components\RichEditor);
        $this->assertContains('breadSource', array_merge(...$field->getToolbarButtons()));
        $this->assertSame('public', $field->getFileAttachmentsDiskName());
        $this->assertSame('pages/'.date('FY'), $field->getFileAttachmentsDirectory());
        $this->assertSame('public', $field->getFileAttachmentsVisibility());
        $this->assertSame(10240, $field->getFileAttachmentsMaxSize());
        $this->assertNotContains('application/pdf', $field->getFileAttachmentsAcceptedFileTypes());
        $action = \Filament\Actions\Testing\TestAction::make('breadSource')->schemaComponent('meta_description', 'form');
        $html = '<p>Changed <strong>HTML</strong></p>';
        $editor->callAction($action, ['html' => $html])->assertHasNoActionErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => '<p>Original</p>']);
        $editor->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => $html]);
        $editor->call('changeLocale', 'ru')->callAction($action, ['html' => '<p>Перевод HTML</p>'])->assertHasNoActionErrors()
            ->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'column_name' => 'meta_description', 'locale' => 'ru', 'value' => '<p>Перевод HTML</p>']);
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => $html]);
        $editor->set('data.meta_description', ['invalid'])->call('save')->assertHasErrors();
        DB::table('data_rows')->where('id', $rowId)->update(['details' => '{"tinymceOptions":{"toolbar":"code image forecolor table"}}']);
        $custom = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $field = collect($custom->instance()->form->getFlatComponents())->first(fn ($field) => $field instanceof \Filament\Forms\Components\RichEditor);
        $this->assertSame([['breadSource', 'attachFiles', 'textColor', 'table']], $field->getToolbarButtons());
    }

    public function test_rich_legacy_html_and_source_changes_preserve_embedded_elements_and_attributes(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')->update(['type' => 'rich_text_box', 'details' => '{}']);
        $html = '<div class="legacy"><p style="background-color: #ff0000; margin-left: 40px">Old</p><form><input name="legacy" value="x"></form><iframe src="https://example.com/embed"></iframe></div>';
        $id = DB::table('pages')->insertGetId(['title' => 'Legacy HTML', 'meta_description' => $html]);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $field = collect($editor->instance()->form->getFlatComponents())->first(fn ($field) => method_exists($field, 'getName') && $field->getName() === 'meta_description');
        $this->assertInstanceOf(\Filament\Forms\Components\CodeEditor::class, $field);
        $editor->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => $html]);
        $editor->call('changeLocale', 'ru')->set('data.meta_description', '<p>Перевод</p>')->call('save')->assertHasNoErrors();
        $editor->call('changeLocale', 'en')->assertSet('data.meta_description', $html);
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => $html]);
        $fresh = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->call('changeLocale', 'ru');
        $action = \Filament\Actions\Testing\TestAction::make('breadSource')->schemaComponent('meta_description', 'form');
        $fresh->callAction($action, ['html' => $html])->assertHasNoActionErrors()->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'column_name' => 'meta_description', 'locale' => 'ru', 'value' => $html]);
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => $html]);
        $fresh->call('changeLocale', 'en')->set('data.meta_description', ['invalid'])->call('save')->assertHasErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => $html]);

        // Shared HTML drafts keep their editor mode across language switches.
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['type' => 'rich_text_box', 'details' => '{}']);
        $shared = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $sharedSource = \Filament\Actions\Testing\TestAction::make('breadSource')->schemaComponent('title', 'form');
        $shared->callAction($sharedSource, ['html' => $html])->assertHasNoActionErrors()
            ->call('changeLocale', 'ru')->assertSet('data.title', $html)
            ->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => $html]);
        $this->assertSame(0, DB::table('translations')->where('column_name', 'title')->count());
    }

    public function test_rich_source_mode_does_not_bypass_pending_image_attachment_save(): void
    {
        Storage::fake('public');
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')->update(['type' => 'rich_text_box', 'details' => '{}']);
        $id = DB::table('pages')->insertGetId(['title' => 'Pending HTML image', 'meta_description' => '<p>Original</p>']);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $attach = \Filament\Actions\Testing\TestAction::make('attachFiles')->schemaComponent('meta_description', 'form')
            ->arguments(['editorSelection' => ['anchor' => 1, 'head' => 1, 'type' => 'text']]);
        $editor->callAction($attach, ['file' => UploadedFile::fake()->image('inline.png'), 'alt' => 'Inline'])->assertHasNoActionErrors();
        $uploads = $editor->get('componentFileAttachments.data.meta_description');
        $uuid = array_key_first($uploads);
        $this->assertNotNull($uuid);
        $editor->set('data.meta_description', ['type' => 'doc', 'content' => [
            ['type' => 'image', 'attrs' => ['id' => $uuid, 'src' => $uploads[$uuid]->temporaryUrl()]],
        ]]);
        $source = \Filament\Actions\Testing\TestAction::make('breadSource')->schemaComponent('meta_description', 'form');
        $editor->callAction($source, ['html' => '<p>Source</p>'])->assertHasActionErrors(['html']);
        $this->assertFalse((bool) ($editor->get('richSourceFields')['en']['meta_description'] ?? false));
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => '<p>Original</p>']);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_image_upload_cleanup_on_validation_and_sql_failure_and_retry(): void
    {
        Storage::fake('public');
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadImageRecoveryFixture::class]);
        $details = ['preserveFileUploadName' => true, 'thumbnails' => [['name' => 'small', 'scale' => 50]]];
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'image', 'type' => 'image', 'display_name' => 'Image', 'details' => json_encode($details)]);
        $disk = Storage::disk('public');
        $oldFile = UploadedFile::fake()->image('old.jpg');
        $disk->put('old/original.jpg', file_get_contents($oldFile->getRealPath()));
        $disk->put('old/original-small.jpg', 'Existing thumbnail');
        $disk->put('old/original.webp', 'Existing companion');
        $old = collect($disk->allFiles())->mapWithKeys(fn ($path) => [$path => $disk->get($path)])->all();
        $creator = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')
            ->set('data.title', 'Reject upload')->set('data.image', UploadedFile::fake()->image('rejected.jpg', 20, 10));
        $creator->call('save')->assertHasErrors(['data.title']);
        $this->assertDatabaseMissing('pages', ['title' => 'Reject upload']);
        $this->assertNull($creator->get('recordId'));
        $this->assertSame([], $creator->get('data.image'));
        $this->assertEquals(array_keys($old), $disk->allFiles());
        // A fresh selection can be saved after fixing the error.
        $creator->set('data.title', 'Accepted upload')->set('data.image', UploadedFile::fake()->image('accepted.jpg', 20, 10))
            ->call('save')->assertHasNoErrors();
        $id = DB::table('pages')->where('title', 'Accepted upload')->value('id');
        $accepted = DB::table('pages')->where('id', $id)->value('image');
        foreach ([$accepted, str_replace('.jpg', '-small.jpg', $accepted), image_webp_path($accepted)] as $path) { $this->assertTrue($disk->exists($path)); }
        DB::statement("CREATE TRIGGER reject_image_update BEFORE UPDATE ON pages WHEN NEW.title = 'SQL failure' BEGIN SELECT RAISE(ABORT, 'Fixture SQL failure'); END");
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->set('data.title', 'SQL failure')->set('data.image', []);
        $editor->set('data.image', [UploadedFile::fake()->image('sql-failed.jpg', 20, 10)]);
        $before = $disk->allFiles();
        try { $editor->call('save'); $this->fail('Expected SQL rollback'); }
        catch (\Illuminate\Database\QueryException $error) { $this->assertStringContainsString('Fixture SQL failure', $error->getMessage()); }
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => 'Accepted upload', 'image' => $accepted]);
        $this->assertEquals($before, $disk->allFiles());
        foreach ($old as $path => $bytes) { $this->assertSame($bytes, $disk->get($path)); }
        DB::statement('DROP TRIGGER reject_image_update');
        // Multiple uploads retain the old selection and remove every new derivative.
        DB::table('data_rows')->where('id', $rowId)->update(['type' => 'multiple_images']);
        DB::table('pages')->where('id', $id)->update(['image' => json_encode(['old/original.jpg'])]);
        $multiple = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->set('data.title', 'Reject upload');
        $multiple->set('data.image', [UploadedFile::fake()->image('one.jpg', 20, 10), UploadedFile::fake()->image('two.jpg', 20, 10)])
            ->call('save')->assertHasErrors(['data.title']);
        $this->assertSame(['old/original.jpg'], array_values($multiple->get('data.image')));
        $this->assertEquals($before, $disk->allFiles());
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => 'Accepted upload', 'image' => json_encode(['old/original.jpg'])]);
    }

    public function test_image_upload_cleanup_when_a_later_file_fails(): void
    {
        Storage::fake('public');
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => 'image', 'type' => 'multiple_images', 'display_name' => 'Image',
            'details' => json_encode(['preserveFileUploadName' => true, 'thumbnails' => [['name' => 'small', 'scale' => 50]]])]);
        $disk = Storage::disk('public');
        $oldFile = UploadedFile::fake()->image('old.jpg');
        $disk->put('old.jpg', file_get_contents($oldFile->getRealPath()));
        $oldBytes = $disk->get('old.jpg');
        $id = DB::table('pages')->insertGetId(['title' => 'Original', 'image' => json_encode(['old.jpg'])]);
        $service = new class extends \App\Filament\Bread\BreadImageUpload {
            private int $calls = 0;
            public function store(\Illuminate\Http\UploadedFile $file, string $disk, string $directory, array $details, string $path, ?\Closure $onCreated = null): string
            {
                if (++$this->calls === 2) { throw \Illuminate\Validation\ValidationException::withMessages([$path => 'Fixture second upload failure']); }
                return parent::store($file, $disk, $directory, $details, $path, $onCreated);
            }
        };
        $this->app->instance(\App\Filament\Bread\BreadImageUpload::class, $service);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->set('data.title', 'Draft title');
        $editor->set('data.image', [UploadedFile::fake()->image('first.jpg', 20, 10), UploadedFile::fake()->image('second.jpg', 20, 10)])
            ->call('save')->assertHasErrors(['data.image']);
        $this->assertSame(['old.jpg'], array_values($editor->get('data.image')));
        $this->assertSame('Draft title', $editor->get('data.title'));
        $this->assertSame(['old.jpg'], $disk->allFiles());
        $this->assertSame($oldBytes, $disk->get('old.jpg'));
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => 'Original', 'image' => json_encode(['old.jpg'])]);
    }

    public function test_generic_image_upload_transforms_thumbnails_names_and_existing_paths(): void
    {
        Storage::fake('public');
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $details = ['resize' => ['width' => 100], 'quality' => '80%', 'upsize' => false, 'preserveFileUploadName' => true,
            'thumbnails' => [['name' => 'small', 'scale' => 50], ['name' => 'square', 'crop' => ['width' => 30, 'height' => 30]]]];
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'image', 'type' => 'image', 'display_name' => 'Image', 'details' => json_encode($details)]);
        $creator = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->set('data.title', 'Upload')->set('data.image', UploadedFile::fake()->image('photo.jpg', 200, 100))->call('save')->assertHasNoErrors();
        $path = DB::table('pages')->where('title', 'Upload')->value('image');
        $this->assertStringEndsWith('/photo.jpg', $path);
        $this->assertSame([100, 50], array_slice(getimagesizefromstring(Storage::disk('public')->get($path)), 0, 2));
        $small = str_replace('.jpg', '-small.jpg', $path); $square = str_replace('.jpg', '-square.jpg', $path);
        $this->assertSame([50, 25], array_slice(getimagesizefromstring(Storage::disk('public')->get($small)), 0, 2));
        $this->assertSame([30, 30], array_slice(getimagesizefromstring(Storage::disk('public')->get($square)), 0, 2));
        $id = DB::table('pages')->where('title', 'Upload')->value('id'); $bytes = Storage::disk('public')->get($path);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->set('data.title', 'No image edit')->call('save')->assertHasNoErrors();
        $this->assertSame($path, DB::table('pages')->where('id', $id)->value('image')); $this->assertSame($bytes, Storage::disk('public')->get($path));
        $replacement = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->set('data.image', []);
        $replacement->set('data.image', [UploadedFile::fake()->image('photo.jpg', 20, 10)])->call('save')->assertHasNoErrors();
        $next = DB::table('pages')->where('id', $id)->value('image'); $this->assertStringEndsWith('/photo1.jpg', $next);
        $this->assertSame([20, 10], array_slice(getimagesizefromstring(Storage::disk('public')->get($next)), 0, 2));
        $this->assertSame($bytes, Storage::disk('public')->get($path));
        DB::table('data_rows')->where('id', $rowId)->update(['type' => 'multiple_images']);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->set('data.title', 'Multiple')->set('data.image', [UploadedFile::fake()->image('a.png', 120, 60), UploadedFile::fake()->image('b.png', 120, 60)])->call('save')->assertHasNoErrors();
        $files = json_decode(DB::table('pages')->where('title', 'Multiple')->value('image'), true); $this->assertCount(2, $files);
        foreach ($files as $file) { $this->assertSame([100, 50], array_slice(getimagesizefromstring(Storage::disk('public')->get($file)), 0, 2)); }
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId);
        foreach ([['resize' => ['width' => 0]], ['quality' => 101], ['thumbnails' => [['name' => '../bad', 'scale' => 50]]], ['thumbnails' => [['name' => 'bad', 'crop' => ['width' => 0, 'height' => 5]]]]] as $bad) { $metadata->set('data.details', json_encode($bad))->call('save')->assertHasErrors(['details']); }
        $metadata->set('data.details', json_encode($details))->call('save')->assertHasNoErrors();
        $service = app(\App\Filament\Bread\BreadImageUpload::class);
        $allowed = $service->store(UploadedFile::fake()->image('up.jpg', 20, 10), 'public', 'tests', ['resize' => ['width' => 100]], 'image');
        $this->assertSame([100, 50], array_slice(getimagesizefromstring(Storage::disk('public')->get($allowed)), 0, 2));
        $gif = UploadedFile::fake()->image('animated.gif', 20, 10);
        $gifBytes = file_get_contents($gif->getRealPath());
        $gifPath = $service->store($gif, 'public', 'tests', ['resize' => ['width' => 10]], 'image');
        $this->assertSame($gifBytes, Storage::disk('public')->get($gifPath));
        $this->assertSame([10, 5], array_slice(getimagesizefromstring(Storage::disk('public')->get(str_replace('.gif', '-static.gif', $gifPath))), 0, 2));
        $brokenDisk = \Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
        $brokenDisk->shouldReceive('exists')->andReturn(false);
        $brokenDisk->shouldReceive('put')->once()->ordered()->andReturn(true);
        $brokenDisk->shouldReceive('put')->once()->ordered()->andReturn(false);
        $brokenDisk->shouldReceive('delete')->once()->with(\Mockery::on(fn ($paths) => count($paths) === 2))->andReturn(true);
        Storage::shouldReceive('disk')->with('broken')->andReturn($brokenDisk);
        try { $service->store(UploadedFile::fake()->image('fail.jpg', 100, 50), 'broken', 'test', $details, 'image'); $this->fail('Expected storage error'); }
        catch (\Illuminate\Validation\ValidationException $error) { $this->assertArrayHasKey('image', $error->errors()); }
    }

    public function test_multiple_checkbox_defaults_lists_maps_labels_translations_and_preservation(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $details = ['options' => ['main' => 'Основной', 'other' => 'Другой'], 'checked' => true];
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'multiple_checkbox', 'display_name' => 'Варианты', 'details' => json_encode($details)]);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->assertSet('data.flags', ['main', 'other'])->set('data.title', 'Checked all')->call('save')->assertHasNoErrors();
        $this->assertSame(['main' => 'main', 'other' => 'other'], json_decode(DB::table('pages')->where('title', 'Checked all')->value('flags'), true));
        $id = DB::table('pages')->insertGetId(['title' => 'List', 'flags' => '[ "other" ]']);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSet('data.flags', ['other'])->call('save')->assertHasNoErrors();
        $this->assertSame('[ "other" ]', DB::table('pages')->where('id', $id)->value('flags'));
        $editor->set('data.flags', ['main'])->call('changeLocale', 'ru')->assertSet('data.flags', ['main'])->call('save')->assertHasNoErrors();
        $this->assertSame(['main' => 'main'], json_decode(DB::table('pages')->where('id', $id)->value('flags'), true));
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openView', $id)->assertSee('Основной');
        $this->assertSame('Основной', Livewire::test(VoyagerBread::class, ['type' => 'pages'])->instance()->records()['rows']->firstWhere('id', $id)->flags);
        $editor->set('data.flags', ['unknown'])->call('save')->assertHasErrors();
        $editor->set('data.flags', ['main', 'main'])->call('save')->assertHasErrors();
        $editor->set('data.flags', [])->call('save')->assertHasNoErrors();
        $this->assertSame('[]', DB::table('pages')->where('id', $id)->value('flags'));
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSet('data.flags', []);
        foreach (['broken', '{"main":{"nested":"bad"}}', '["retired"]'] as $raw) {
            DB::table('pages')->where('id', $id)->update(['flags' => $raw]);
            Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('Значение сохраняется без изменений')->set('data.title', 'Kept')->call('save')->assertHasNoErrors();
            $this->assertSame($raw, DB::table('pages')->where('id', $id)->value('flags'));
        }
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId);
        $metadata->set('data.details', '{"checked":[],"options":{"a":"A"}}')->call('save')->assertHasErrors(['details']);
        $metadata->set('data.details', '{"checked":false,"options":{"main":"Основной","other":"Другой"}}')->call('save')->assertHasNoErrors();
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->assertSet('data.flags', []);
        DB::table('pages')->where('id', $id)->update(['flags' => '{"main":"main"}']);
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadTranslatedCheckboxFixture::class]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->call('changeLocale', 'ru')->assertSet('data.flags', [])->set('data.flags', ['other'])->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'column_name' => 'flags', 'locale' => 'ru', 'value' => '{"other":"other"}']);
        $this->assertSame('{"main":"main"}', DB::table('pages')->where('id', $id)->value('flags'));
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->call('changeLocale', 'ru')->assertSet('data.flags', ['other']);
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadCastCheckboxFixture::class]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSet('data.flags', ['main'])->set('data.flags', ['other'])->call('save')->assertHasNoErrors();
        $this->assertSame(['other' => 'other'], json_decode(DB::table('pages')->where('id', $id)->value('flags'), true));
        $codec = app(\App\Filament\Bread\BreadMultiCheckbox::class);
        $this->assertSame(['main', 'other'], $codec->state(null, [...$details, 'checked' => 'false'])); // Original uses PHP truthiness.
        $this->assertSame(['other'], $codec->state('{"unrelated_key":"other"}', $details));
    }

    public function test_checkbox_checked_captions_existing_false_and_shared_locale(): void
    {
        Schema::table('pages', fn (Blueprint $table) => $table->boolean('enabled')->nullable());
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $details = ['checked' => 'yes', 'on' => 'Показывать', 'off' => 'Скрывать'];
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'enabled', 'type' => 'checkbox', 'display_name' => 'Видимость', 'details' => json_encode($details)]);
        $creator = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->assertSet('data.enabled', true)->assertSee('Показывать');
        $creator->set('data.title', 'Checked default')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['title' => 'Checked default', 'enabled' => 1]);
        $id = DB::table('pages')->insertGetId(['title' => 'Existing false', 'enabled' => false]);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSet('data.enabled', false)->assertSee('Скрывать');
        $editor->set('data.enabled', true)->call('changeLocale', 'ru')->assertSet('data.enabled', true)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'enabled' => 1]);
        $editor->set('data.enabled', false)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'enabled' => 0]);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openView', $id)->assertSee('Скрывать');
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->assertSee('Показывать')->assertSee('Скрывать');
        $nullId = DB::table('pages')->insertGetId(['title' => 'Null default', 'enabled' => null]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $nullId])->assertSet('data.enabled', true);
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId);
        foreach ([['checked' => []], ['checked' => 'invalid'], ['checked' => 2], ['on' => []], ['off' => 1]] as $bad) {
            $metadata->set('data.details', json_encode($bad))->call('save')->assertHasErrors(['details']);
        }
        $metadata->set('data.details', '{"checked":"off","on":"<b>On</b>","off":"Off"}')->call('save')->assertHasNoErrors();
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->assertSet('data.enabled', false)->set('data.enabled', true)->assertSee('&lt;b&gt;On&lt;/b&gt;', escape: false);
        $this->assertSame('Да', app(\App\Filament\Bread\BreadCheckbox::class)->caption(1, []));
    }

    public function test_metadata_database_regex_and_dependent_validation_on_create_edit_and_translations(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $titleId = DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->value('id');
        DB::table('data_rows')->where('id', $titleId)->update(['details' => '{"validation":{"rule":["unique:pages,title","regex:/^(First|Second|Third)$/"],"messages":{"unique":"Название уже занято"}}}']);
        $descId = DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')->value('id');
        DB::table('data_rows')->where('id', $descId)->update(['details' => '{"validation":{"rule":"unique:pages,meta_description"}}']);
        $flagsId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'text', 'display_name' => 'Dependent', 'details' => '{"validation":{"rule":["required_if:title,Second","same:meta_description"]}}']);
        $authorRow = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'author_id', 'type' => 'number', 'display_name' => 'Reference', 'details' => '{"validation":{"rule":"exists:pages,id"}}']);
        $one = DB::table('pages')->insertGetId(['title' => 'First', 'meta_description' => 'Description']);
        $two = DB::table('pages')->insertGetId(['title' => 'Third', 'meta_description' => 'Other']);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $one]);
        $editor->call('save')->assertHasNoErrors();
        $editor->set('data.title', 'Third')->call('save')->assertHasErrors(['data.title'])->assertSee('Название уже занято');
        $editor->set('data.title', 'Invalid')->call('save')->assertHasErrors(['data.title']);
        $editor->set('data.title', 'Second')->set('data.flags', null)->call('save')->assertHasErrors(['data.flags']);
        $editor->set('data.flags', 'Wrong')->call('save')->assertHasErrors(['data.flags']);
        $editor->set('data.flags', 'Description')->set('data.author_id', 999)->call('save')->assertHasErrors(['data.author_id']);
        $editor->set('data.author_id', $two)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $one, 'title' => 'Second', 'author_id' => $two]);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->set('data.title', 'Second')->call('save')->assertHasErrors(['data.title']);
        DB::table('translations')->insert(['table_name' => 'pages', 'column_name' => 'meta_description', 'foreign_key' => $two, 'locale' => 'ru', 'value' => 'Занято']);
        $editor->call('changeLocale', 'ru')->set('data.meta_description', 'Занято')->set('data.flags', 'Занято')->call('save')->assertHasErrors(['data.meta_description']);
        $editor->set('data.meta_description', 'Свободно')->set('data.flags', 'Свободно')->call('save')->assertHasNoErrors();
        $editor->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('translations', ['foreign_key' => $one, 'locale' => 'ru', 'column_name' => 'meta_description', 'value' => 'Свободно']);
        $this->assertDatabaseHas('pages', ['id' => $one, 'meta_description' => 'Description']);
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $titleId);
        foreach (['unique:pages,title,1', 'unique:users,email', 'exists:pages,missing', 'exists:users,password', 'same:missing', 'same:password', 'regex:/[/', 'custom_rule', 'unique:other.pages,title'] as $rule) {
            $metadata->set('data.details', json_encode(['validation' => ['rule' => [$rule]]]))->call('save')->assertHasErrors(['details']);
        }
        $metadata->set('data.details', '{"validation":{"rule":["unique:pages,title","regex:/^(First|Second|Third)$/"]}}')->call('save')->assertHasNoErrors();
        $service = app(\App\Filament\Bread\BreadValidation::class);
        $model = new \App\Models\Page;
        foreach (['different:meta_description', 'required_with:meta_description', 'required_without:meta_description', 'required_unless:title,First', 'required_with_all:title,meta_description', 'required_without_all:title,meta_description', 'not_regex:/^Bad$/'] as $rule) {
            $this->assertTrue($service->accepts($rule));
            $this->assertNotEmpty($service->compile([$rule], $model, 'flags'));
        }
        DB::table('data_rows')->where('id', $titleId)->update(['details' => '{"validation":{"rule":"unique:missing,title"}}']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $one])->set('data.author_id', $one)->set('data.flags', 'Description')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $one, 'title' => 'Second']);
    }

    public function test_slug_generator_tracking_force_manual_values_and_locale_isolation(): void
    {
        Schema::table('pages', fn (Blueprint $table) => $table->string('slug')->nullable());
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'slug', 'type' => 'text', 'display_name' => 'Slug', 'details' => '{"slugify":{"origin":"title"}}']);
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadSlugPageFixture::class]);
        $creator = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate');
        $creator->set('data.title', 'Привет & Café')->assertSet('data.slug', 'privet-and-cafe')
            ->set('data.title', 'Новый заголовок')->assertSet('data.slug', 'novyj-zagolovok')
            ->set('data.slug', 'custom')->call('save')->assertHasNoErrors();
        $id = DB::table('pages')->where('slug', 'custom')->value('id');
        $this->assertNotNull($id);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $editor->set('data.title', 'Changed title')->assertSet('data.slug', 'custom')->call('save')->assertHasNoErrors();
        $editor->set('data.slug', '')->set('data.title', 'Auto again')->assertSet('data.slug', 'auto-again');
        $editor->set('data.slug', 'Manual while tracking')->set('data.title', 'Tracking remains')->assertSet('data.slug', 'tracking-remains');
        $editor->call('changeLocale', 'ru')->set('data.title', 'Перевод заголовка')->assertSet('data.slug', 'tracking-remains')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'slug' => 'tracking-remains']);
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'column_name' => 'title', 'locale' => 'ru', 'value' => 'Перевод заголовка']);
        // Original helper treats presence of forceUpdate=false as enabled.
        DB::table('data_rows')->where('id', $rowId)->update(['details' => '{"slugify":{"origin":"title","forceUpdate":false}}']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->set('data.title', 'Forced update')->assertSet('data.slug', 'forced-update')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'slug' => 'forced-update']);
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId);
        foreach ([['origin' => 'missing'], ['origin' => 'slug'], ['origin' => 'password'], ['origin' => 'title', 'forceUpdate' => 'yes'], ['origin' => 'title', 'map' => []]] as $config) {
            $metadata->set('data.details', json_encode(['slugify' => $config]))->call('save')->assertHasErrors(['details']);
        }
        $metadata->set('data.details', '{"slugify":{"origin":"title"}}')->call('save')->assertHasNoErrors();
        DB::table('data_rows')->where('id', $rowId)->update(['details' => '{"slugify":{"origin":"missing"}}']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->set('data.title', 'No generator')->set('data.slug', 'manual-safe')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'slug' => 'manual-safe']);
        DB::table('data_rows')->where('id', $rowId)->update(['details' => '{"slugify":{"origin":"title","forceUpdate":true}}']);
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadTranslatedSlugFixture::class]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->call('changeLocale', 'ru')->set('data.title', 'Мой перевод')->assertSet('data.slug', 'moj-perevod')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'slug' => 'manual-safe']);
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'column_name' => 'slug', 'locale' => 'ru', 'value' => 'moj-perevod']);
        $creator = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->set('data.slug', 'Early manual')->set('data.title', 'New source');
        $creator->assertSet('data.slug', 'new-source');
        // Browser forms can submit the root array as one Livewire update.
        $bulk = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')
            ->set('data', ['title' => 'Bulk browser form', 'slug' => 'bulk-browser-form'])
            ->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['title' => 'Bulk browser form', 'slug' => 'bulk-browser-form']);
        $slugger = app(\App\Filament\Bread\BreadSlug::class);
        $this->assertSame('shuka-yozh', $slugger->generate('Щука Ёж'));
        $this->assertSame('love-and-euro', $slugger->generate('♥ & €'));
    }

    public function test_bread_display_width_responsive_spans_tabs_languages_and_validation(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $titleRow = DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->value('id');
        DB::table('data_rows')->where('id', $titleRow)->update(['details' => '{"display":{"width":"6"}}']);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')->update(['details' => '{"display":{"width":3}}']);
        DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'text', 'display_name' => 'Full width', 'details' => '{"display":{"width":12}}']);
        $id = DB::table('pages')->insertGetId(['title' => 'Widths', 'meta_description' => 'Description', 'flags' => 'Keep']);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $form = $editor->instance()->form;
        $this->assertSame(1, $form->getColumns('default'));
        $this->assertSame(12, $form->getColumns('lg'));
        $find = fn ($componentName, $form) => collect($form->getFlatComponents())->first(fn ($field) => $field instanceof \Filament\Forms\Components\Field && $field->getName() === $componentName);
        foreach (['title' => 6, 'meta_description' => 3, 'flags' => 12] as $name => $width) {
            $field = $find($name, $form);
            $this->assertSame(1, $field->getColumnSpan('default'));
            $this->assertSame($width, $field->getColumnSpan('lg'));
        }
        $editor->set('data.title', 'Saved width')->call('changeLocale', 'ru')->set('data.meta_description', 'Перевод')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => 'Saved width', 'flags' => 'Keep']);
        $this->assertSame(6, $find('title', $editor->instance()->form)->getColumnSpan('lg'));
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'column_name' => 'meta_description', 'locale' => 'ru', 'value' => 'Перевод']);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'flags')->update(['details' => '{"tab_title":"Extra","display":{"width":12}}']);
        $tabEditor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('Extra');
        $tabs = collect($tabEditor->instance()->form->getFlatComponents())->filter(fn ($component) => $component instanceof \Filament\Schemas\Components\Tabs\Tab);
        $this->assertCount(2, $tabs);
        foreach ($tabs as $tab) { $this->assertSame(12, $tab->getChildSchema()->getColumns('lg')); $this->assertSame(1, $tab->getChildSchema()->getColumns('default')); }
        $this->assertSame(3, $find('meta_description', $tabEditor->instance()->form)->getColumnSpan('lg'));
        $tabEditor->set('data.flags', 'Extra saved')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => 'Extra saved']);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->set('data.title', 'New width')->set('data.flags', 'Created')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['title' => 'New width', 'flags' => 'Created']);
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $titleRow);
        foreach ([0, 13, -1, 3.5, true, [], 'full', '6;bad'] as $bad) {
            $metadata->set('data.details', json_encode(['display' => ['width' => $bad]]))->call('save')->assertHasErrors(['details']);
        }
        $metadata->set('data.details', '{"display":{"width":"6"}}')->call('save')->assertHasNoErrors();
        DB::table('data_rows')->where('id', $titleRow)->update(['details' => '{"display":{"width":"bad"}}']);
        $fallback = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->set('data.title', 'Fallback')->call('save')->assertHasNoErrors();
        $this->assertSame(12, $find('title', $fallback->instance()->form)->getColumnSpan('lg'));
        $this->assertDatabaseHas('data_rows', ['id' => $titleRow, 'details' => '{"display":{"width":"bad"}}']);
    }

    public function test_scalar_field_options_validation_bounds_step_rows_and_translations(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $number = ['min' => 0.1, 'max' => 2.1, 'step' => 0.5, 'default' => 0.1, 'placeholder' => 'Число'];
        $numId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'number', 'display_name' => 'Число', 'details' => json_encode($number)]);
        $text = ['placeholder' => 'Введите название', 'validation' => ['rule' => 'max:20', 'add' => ['rule' => 'min:5'], 'edit' => ['rule' => 'min:2'], 'messages' => ['max' => 'Слишком длинное название']]];
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['details' => json_encode($text)]);
        $area = ['display' => ['rows' => 8], 'placeholder' => 'Описание', 'validation' => ['rule' => ['required', 'max:10']]];
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')->update(['type' => 'text_area', 'details' => json_encode($area)]);
        $id = DB::table('pages')->insertGetId(['title' => 'Title', 'flags' => '0.6', 'meta_description' => 'Description']);
        // Use a valid existing description for default-language saves.
        DB::table('pages')->where('id', $id)->update(['meta_description' => 'Desc']);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('Введите название')->assertSee('Описание')->assertSee('rows="8"', false);
        foreach (['-1', '2.6', '0.2'] as $invalid) {
            $editor->set('data.flags', $invalid)->call('save')->assertHasErrors(['data.flags']);
            $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => '0.6']);
        }
        $editor->set('data.flags', '1.1')->set('data.title', str_repeat('X', 21))->call('save')->assertHasErrors(['data.title'])->assertSee('Слишком длинное название');
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => 'Title']);
        $editor->set('data.title', 'OK')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => '1.1', 'title' => 'OK']);
        $editor->call('changeLocale', 'ru')->set('data.flags', '1.6')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => '1.6', 'meta_description' => 'Desc']);
        $editor->set('data.meta_description', str_repeat('Я', 11))->call('save')->assertHasErrors(['data.meta_description']);
        $this->assertDatabaseMissing('translations', ['foreign_key' => $id, 'column_name' => 'meta_description', 'locale' => 'ru']);
        $editor->set('data.meta_description', 'Перевод')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'column_name' => 'meta_description', 'locale' => 'ru', 'value' => 'Перевод']);
        $create = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->assertSet('data.flags', 0.1);
        $create->set('data.title', 'abc')->set('data.meta_description', 'New')->call('save')->assertHasErrors(['data.title']);
        $create->set('data.title', 'Created')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['title' => 'Created', 'flags' => '0.1']);
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $numId);
        foreach ([['min' => 5, 'max' => 1], ['step' => 0], ['step' => -1], ['min' => []], ['validation' => ['rule' => 'unknown_rule']], ['validation' => ['rule' => ['max:3'], 'edit' => ['rule' => 'unique:missing_table']]], ['validation' => ['messages' => ['max' => []]]]] as $bad) {
            $metadata->set('data.details', json_encode($bad))->call('save')->assertHasErrors(['details']);
        }
        $metadata->set('data.details', json_encode($number))->call('save')->assertHasNoErrors();
        DB::table('data_rows')->where('id', $numId)->update(['details' => '{"validation":{"rule":"custom_unported_rule"}}']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('Значение сохраняется без изменений')
            ->set('data.title', 'Kept')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => '1.6']);
    }

    public function test_select_multiple_relation_list_json_pivot_values_and_scopes(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        Schema::create('select_nodes', function (Blueprint $table) { $table->id(); $table->string('slug'); $table->string('name'); $table->boolean('active'); $table->softDeletes(); });
        Schema::create('select_links', function (Blueprint $table) { $table->unsignedBigInteger('page_id'); $table->unsignedBigInteger('node_id'); $table->string('note')->nullable(); $table->integer('weight')->nullable(); });
        DB::table('select_nodes')->insert([
            ['id' => 1, 'slug' => 'one', 'name' => 'Первый', 'active' => 1, 'deleted_at' => null],
            ['id' => 2, 'slug' => 'two', 'name' => 'Второй', 'active' => 1, 'deleted_at' => null],
            ['id' => 3, 'slug' => 'hidden', 'name' => 'Hidden', 'active' => 0, 'deleted_at' => null],
            ['id' => 4, 'slug' => 'deleted', 'name' => 'Deleted', 'active' => 1, 'deleted_at' => now()],
        ]);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadSelectPageFixture::class]);
        $details = ['relationship' => ['key' => 'slug', 'label' => 'name', 'editablePivotFields' => ['note', 'weight']]];
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'select_multiple', 'display_name' => 'Варианты связи', 'details' => json_encode($details)]);
        $id = DB::table('pages')->insertGetId(['title' => 'Select relation']);
        DB::table('select_links')->insert(['page_id' => $id, 'node_id' => 1, 'note' => 'original pivot', 'weight' => 7]);
        $codec = app(\App\Filament\Bread\BreadSelectRelation::class);
        $this->assertSame(['two' => 'Второй', 'one' => 'Первый'], $codec->options(BreadSelectPageFixture::find($id), 'flags', $details));
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('Добавить вариант');
        $rows = array_values($editor->get('data.flags.rows'));
        $this->assertSame([['key' => 'one', 'attributes' => ['note' => 'original pivot', 'weight' => 7]]], $rows);
        $editor->set('data.flags.rows', [['key' => 'two', 'attributes' => ['note' => 'new', 'weight' => '9']], ['key' => 'one', 'attributes' => ['note' => 'kept', 'weight' => '8']]])
            ->call('changeLocale', 'ru')->call('save')->assertHasNoErrors();
        $saved = json_decode(DB::table('pages')->where('id', $id)->value('flags'), true);
        $this->assertSame(['two' => ['note' => 'new', 'weight' => '9'], 'one' => ['note' => 'kept', 'weight' => '8']], $saved);
        $this->assertDatabaseHas('select_links', ['page_id' => $id, 'node_id' => 1, 'note' => 'original pivot', 'weight' => 7]);
        $this->assertDatabaseMissing('select_links', ['page_id' => $id, 'node_id' => 2]);
        $this->assertDatabaseMissing('translations', ['foreign_key' => $id, 'column_name' => 'flags']);
        $reopened = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $this->assertSame(['two', 'one'], array_column(array_values($reopened->get('data.flags.rows')), 'key'));
        $raw = DB::table('pages')->where('id', $id)->value('flags');
        $reopened->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => $raw]);
        foreach (['hidden', 'deleted', 'missing'] as $bad) {
            $reopened->set('data.flags.rows', [['key' => $bad, 'attributes' => ['note' => 'x']]])->call('save')->assertHasErrors();
            $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => $raw]);
        }
        foreach ([['rows' => [['key' => 'one', 'attributes' => ['page_id' => 99]]]], ['rows' => [['key' => 'one', 'attributes' => ['note' => []]]]], ['rows' => [['key' => 'one', 'attributes' => []], ['key' => 'one', 'attributes' => []]]]] as $bad) {
            try { $codec->encode(BreadSelectPageFixture::find($id), 'flags', $details, $bad, 'data.flags', false); $this->fail('Forged relation accepted'); }
            catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('data.flags', $e->errors()); }
        }
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openView', $id)->assertSee('Второй, Первый');
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->assertSee('Второй, Первый');
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->set('data.title', 'Created relation')
            ->set('data.flags.rows', [['key' => 'two', 'attributes' => ['note' => 'Created', 'weight' => '1']]])->call('save')->assertHasNoErrors();
        $created = json_decode(DB::table('pages')->where('title', 'Created relation')->value('flags'), true);
        $this->assertSame('Created', $created['two']['note']);
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId);
        foreach ([['key' => 'password', 'label' => 'name'], ['key' => 'slug', 'label' => 'remember_token'], ['key' => 'slug', 'label' => 'name', 'editablePivotFields' => ['page_id']], ['key' => 'slug', 'label' => 'name', 'editablePivotFields' => ['absent']]] as $bad) {
            $metadata->set('data.details', json_encode(['relationship' => $bad]))->call('save')->assertHasErrors(['details']);
        }
        $metadata->set('data.details', json_encode($details))->call('save')->assertHasNoErrors();
        DB::table('pages')->where('id', $id)->update(['flags' => '{broken']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('Значение сохранено без изменений')
            ->set('data.title', 'Preserve broken')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => '{broken']);
        $plain = ['relationship' => ['key' => 'slug', 'label' => 'name']];
        DB::table('data_rows')->where('id', $rowId)->update(['details' => json_encode($plain)]);
        DB::table('pages')->where('id', $id)->update(['flags' => null]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSet('data.flags.ids', ['one'])
            ->set('data.flags.ids', ['two'])->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => '["two"]']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSet('data.flags.ids', ['two'])
            ->set('data.flags.ids', [])->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => '[]']);
        // HasMany without *List: source comes from the related scoped query.
        Schema::table('select_nodes', function (Blueprint $table) { $table->unsignedBigInteger('page_id')->nullable(); });
        DB::table('select_nodes')->where('id', 1)->update(['page_id' => $id]);
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadSelectChildrenFixture::class]);
        $childrenDetails = ['relationship' => ['key' => 'id', 'label' => 'name']];
        DB::table('data_rows')->where('id', $rowId)->update(['details' => json_encode($childrenDetails)]);
        DB::table('pages')->where('id', $id)->update(['flags' => null]);
        $this->assertSame([1 => 'Первый', 2 => 'Второй', 3 => 'Hidden'], $codec->options(new BreadSelectChildrenFixture, 'flags', $childrenDetails));
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSet('data.flags.ids', ['1'])
            ->set('data.flags.ids', ['2'])->call('save')->assertHasNoErrors();
        $this->assertSame([2], BreadSelectChildrenFixture::find($id)->flags);
        $this->assertDatabaseHas('select_nodes', ['id' => 1, 'page_id' => $id]);
        $this->assertDatabaseHas('select_nodes', ['id' => 2, 'page_id' => null]);
        try { $codec->configuration(new BreadSelectChildrenFixture, 'flags', ['relationship' => ['key' => 'id', 'label' => 'name', 'editablePivotFields' => ['note']]]); $this->fail('HasMany accepted pivot fields'); }
        catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('details', $e->errors()); }
        try { $codec->encode(BreadSelectChildrenFixture::find($id), 'flags', $childrenDetails, ['ids' => []], 'data.flags', true); $this->fail('Required empty selection accepted'); }
        catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('data.flags', $e->errors()); }
    }

    public function test_code_editor_languages_raw_text_defaults_translations_and_json_cast(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $details = ['language' => 'html', 'theme' => 'github', 'default' => "  <div>Default</div>\n"];
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'code_editor', 'display_name' => 'Код', 'details' => json_encode($details)]);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')->update(['type' => 'code_editor', 'details' => json_encode($details)]);
        $original = "  <script>\r\n\twindow.sample = '</script>';\r\n</script>  \r\n";
        $id = DB::table('pages')->insertGetId(['title' => 'Code', 'flags' => $original, 'meta_description' => $original]);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->assertSee('codeEditorFormComponent')->assertSee('Цветовая тема следует')->assertSet('data.flags', $original)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => $original]);
        $updated = "\t<div>{{title}} & <span>Привет</span></div>  \n";
        $editor->set('data.flags', $updated)->call('changeLocale', 'ru')->call('changeLocale', 'en')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => $updated]);
        $editor->call('changeLocale', 'ru')->set('data.meta_description', "  <b>Перевод</b>  \n")->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => $original]);
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'column_name' => 'meta_description', 'locale' => 'ru', 'value' => "  <b>Перевод</b>  \n"]);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->assertSet('data.flags', $details['default'])
            ->set('data.title', 'Created code')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['title' => 'Created code', 'flags' => $details['default']]);
        $codec = app(\App\Filament\Bread\BreadCodeEditor::class);
        foreach (\Filament\Forms\Components\CodeEditor\Enums\Language::cases() as $language) {
            $this->assertSame($language, $codec->language(['language' => $language->value]));
        }
        $this->assertSame(\Filament\Forms\Components\CodeEditor\Enums\Language::JavaScript, $codec->language(['language' => 'js']));
        $this->assertNull($codec->language(['language' => 'text']));
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId);
        foreach (['{"language":"unknown"}', '{"language":[]}', '{"theme":[]}', '{"default":[]}'] as $bad) {
            $metadata->set('data.details', $bad)->call('save')->assertHasErrors(['details']);
        }
        $metadata->set('data.details', json_encode($details))->call('save')->assertHasNoErrors();
        DB::table('data_rows')->where('id', $rowId)->update(['details' => '{"language":"legacy-mode"}']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('используется текстовый режим')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => $updated]);
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadCodeCastFixture::class]);
        DB::table('data_rows')->where('id', $rowId)->update(['details' => '{}']);
        $json = '{ "items": [{"name":"Привет"}], "enabled":true }';
        DB::table('pages')->where('id', $id)->update(['flags' => $json]);
        $castEditor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSet('data.flags', $json)
            ->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => $json]);
        $castEditor->set('data.flags', '{bad')->call('save')->assertHasErrors(['data.flags']);
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => $json]);
        $castEditor->set('data.flags', '{"items":[1,2]}')->call('save')->assertHasNoErrors();
        $this->assertSame(['items' => [1, 2]], BreadCodeCastFixture::find($id)->flags);
        $castEditor->set('data.flags', null)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => null]);
    }

    public function test_coordinates_manual_point_roundtrip_validation_and_readonly_geometry(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        // SQLite has no spatial engine: these functions are explicit WKT fixtures,
        // not evidence of a real MySQL geometry roundtrip.
        DB::statement('ALTER TABLE pages ADD COLUMN location POINT NULL');
        DB::connection()->getPdo()->sqliteCreateFunction('ST_GeomFromText', fn ($wkt) => $wkt, 1);
        DB::connection()->getPdo()->sqliteCreateFunction('ST_AsText', fn ($value) => $value, 1);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'location', 'type' => 'coordinates', 'display_name' => 'Местоположение', 'details' => '{}']);
        $id = DB::table('pages')->insertGetId(['title' => 'Coordinates', 'location' => 'POINT(24.1052 56.9496)']);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('Широта')->assertSee('Карта и поиск адреса ещё не подключены')
            ->assertSet('data.location.lat', 56.9496)->assertSet('data.location.lng', 24.1052)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'location' => 'POINT(24.1052 56.9496)']);
        $editor->set('data.location.lat', '91')->call('save')->assertHasErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'location' => 'POINT(24.1052 56.9496)']);
        $editor->set('data.location.lat', '-90')->set('data.location.lng', '180')->call('changeLocale', 'ru')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'location' => 'POINT(180 -90)']);
        $this->assertDatabaseMissing('translations', ['foreign_key' => $id, 'column_name' => 'location']);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openView', $id)->assertSee('-90, 180');
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->assertSee('-90, 180');
        $editor->call('changeLocale', 'en')->set('data.location.lat', null)->set('data.location.lng', null)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'location' => null]);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->set('data.title', 'New point')
            ->set('data.location.lat', 0)->set('data.location.lng', 0)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['title' => 'New point', 'location' => 'POINT(0 0)']);
        $codec = app(\App\Filament\Bread\BreadCoordinates::class);
        $model = \App\Models\Page::find($id);
        $this->assertTrue($codec->supported($model, 'location'));
        $this->assertFalse($codec->supported($model, 'title'));
        foreach ([['lat' => 1], ['lng' => 2], ['lat' => '1); DROP TABLE pages', 'lng' => 2], ['lat' => 2, 'lng' => 181], ['lat' => [], 'lng' => 2], ['lat' => true, 'lng' => 2], ['lat' => INF, 'lng' => 2], ['lat' => 2, 'lng' => 3, 'extra' => 7]] as $bad) {
            try { $codec->encode($model, 'location', $bad, 'data.location', false); $this->fail('Bad point accepted'); }
            catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('data.location', $e->errors()); }
        }
        try { $codec->encode($model, 'location', ['lat' => null, 'lng' => null], 'data.location', true); $this->fail('Required blank point accepted'); }
        catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('data.location', $e->errors()); }
        $this->assertSame(['lat' => 25.0, 'lng' => -1.2], $codec->state('POINT(-1.2e0 2.5e1)'));
        $this->assertNull($codec->state('LINESTRING(1 2,3 4)'));
        $this->assertNull($codec->state('POINT(1 100)'));
        DB::table('pages')->where('id', $id)->update(['location' => 'LINESTRING(1 2,3 4)']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('Геометрия отличается от POINT')
            ->set('data.title', 'Preserved')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'location' => 'LINESTRING(1 2,3 4)']);
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId);
        foreach (['{"onChange":"unsafeCallback"}', '{"default":{"lat":1,"lng":2}}'] as $bad) {
            $metadata->set('data.details', $bad)->call('save')->assertHasErrors(['details']);
        }
        $metadata->set('data.details', '{}')->call('save')->assertHasNoErrors();
        $textId = DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->value('id');
        Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $textId)
            ->set('data.type', 'coordinates')->call('save')->assertHasErrors(['details']);
    }

    public function test_adv_tree_relation_order_scopes_where_validation_and_scalar_save(): void
    {
        Schema::create('tree_nodes', function (Blueprint $table) { $table->id(); $table->unsignedBigInteger('parent_id')->nullable(); $table->string('name'); $table->integer('order')->default(0); $table->boolean('active')->default(true); $table->softDeletes(); });
        DB::table('tree_nodes')->insert([
            ['id' => 1, 'parent_id' => null, 'name' => 'Later root', 'order' => 20, 'active' => true, 'deleted_at' => null],
            ['id' => 2, 'parent_id' => null, 'name' => 'First root', 'order' => 10, 'active' => true, 'deleted_at' => null],
            ['id' => 3, 'parent_id' => 2, 'name' => 'Child', 'order' => 10, 'active' => true, 'deleted_at' => null],
            ['id' => 4, 'parent_id' => 3, 'name' => 'Grandchild', 'order' => 10, 'active' => true, 'deleted_at' => null],
            ['id' => 5, 'parent_id' => null, 'name' => 'Inactive', 'order' => 0, 'active' => false, 'deleted_at' => null],
            ['id' => 6, 'parent_id' => null, 'name' => 'Deleted', 'order' => 0, 'active' => true, 'deleted_at' => now()],
            ['id' => 7, 'parent_id' => 8, 'name' => 'Cycle A', 'order' => 0, 'active' => true, 'deleted_at' => null],
            ['id' => 8, 'parent_id' => 7, 'name' => 'Cycle B', 'order' => 0, 'active' => true, 'deleted_at' => null],
            ['id' => 9, 'parent_id' => 999, 'name' => 'Orphan', 'order' => 0, 'active' => true, 'deleted_at' => null],
        ]);
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadTreePageFixture::class]);
        $details = ['relationship' => ['label' => 'name', 'where' => ['active', 1]], 'options' => ['_empty_' => 'None', -1 => 'Custom']];
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'author_id', 'type' => 'adv_select_dropdown_tree', 'display_name' => 'Дерево', 'details' => json_encode($details)]);
        $id = DB::table('pages')->insertGetId(['title' => 'Tree page', 'author_id' => 3]);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $field = collect($editor->instance()->form->getFlatComponents())->first(fn ($field) => $field instanceof \Filament\Forms\Components\Select && $field->getName() === 'author_id');
        $this->assertSame(['' => 'None', -1 => 'Custom', 0 => '-----', 2 => 'First root', 3 => '— Child', 4 => '— — Grandchild', 1 => 'Later root'], $field->getOptions());
        $editor->assertSet('data.author_id', 3)->set('data.author_id', 4)->call('changeLocale', 'ru')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'author_id' => 4]);
        foreach ([5, 6, 7, 9, 999] as $invalid) { $editor->set('data.author_id', $invalid)->call('save')->assertHasErrors(); }
        $this->assertDatabaseHas('pages', ['id' => $id, 'author_id' => 4]);
        $editor->set('data.author_id', 0)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'author_id' => 0]);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->set('data.title', 'Created tree')->set('data.author_id', 3)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['title' => 'Created tree', 'author_id' => 3]);
        $editor->set('data.author_id', 3)->call('save')->assertHasNoErrors();
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openView', $id)->assertSee('— Child');
        $listing = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        $this->assertSame('— Child', $listing->instance()->records()['rows']->firstWhere('id', $id)->author_id);
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId);
        $metadata->set('data.details', '{"relationship":{"label":"password"}}')->call('save')->assertHasErrors(['details']);
        $metadata->set('data.details', '{"relationship":{"label":"name","where":["missing",1]}}')->call('save')->assertHasErrors(['details']);
        $metadata->set('data.details', '{"options":{"x":"X"},"default":"App@method"}')->call('save')->assertHasErrors(['details']);
        $metadata->set('data.details', '{"options":{"x":"X"}}')->call('save')->assertHasNoErrors();
        $this->assertSame(['x' => 'X'], app(\App\Filament\Bread\BreadTreeOptions::class)->options(new BreadTreePageFixture, 'author_id', ['options' => ['x' => 'X']]));
        $this->assertSame([0 => '-----', 2 => 'First root', 3 => '— Child'], app(\App\Filament\Bread\BreadTreeOptions::class)->options(new BreadTreeListFixture, 'author_id', ['relationship' => ['label' => 'name']]));
    }

    public function test_dropdown_relation_list_where_defaults_languages_and_guards(): void
    {
        Schema::create('tree_nodes', function (Blueprint $table) { $table->id(); $table->string('name'); $table->boolean('active'); $table->softDeletes(); });
        DB::table('tree_nodes')->insert([
            ['id' => 1, 'name' => 'One', 'active' => true, 'deleted_at' => null],
            ['id' => 2, 'name' => 'Two', 'active' => true, 'deleted_at' => null],
            ['id' => 3, 'name' => 'Hidden', 'active' => false, 'deleted_at' => null],
            ['id' => 4, 'name' => 'Deleted', 'active' => true, 'deleted_at' => now()],
        ]);
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadTreePageFixture::class]);
        $details = ['relationship' => ['key' => 'id', 'label' => 'name', 'where' => ['active', 1]], 'options' => ['_empty_' => 'None', -1 => 'Custom'], 'default' => 2];
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'author_id', 'type' => 'select_dropdown', 'display_name' => 'Source', 'details' => json_encode($details)]);
        $id = DB::table('pages')->insertGetId(['title' => 'Dropdown page', 'author_id' => 1]);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $field = collect($editor->instance()->form->getFlatComponents())->first(fn ($field) => $field instanceof \Filament\Forms\Components\Select && $field->getName() === 'author_id');
        $this->assertSame(['' => 'None', -1 => 'Custom', 1 => 'One', 2 => 'Two'], $field->getOptions());
        $editor->assertSet('data.author_id', 1)->set('data.author_id', 2)->call('changeLocale', 'ru')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'author_id' => 2]);
        foreach ([3, 4, 999] as $invalid) { $editor->set('data.author_id', $invalid)->call('save')->assertHasErrors(); }
        $this->assertDatabaseHas('pages', ['id' => $id, 'author_id' => 2]);
        $editor->set('data.author_id', -1)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'author_id' => -1]);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->assertSet('data.author_id', 2)->set('data.title', 'Default dropdown')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['title' => 'Default dropdown', 'author_id' => 2]);
        $editor->set('data.author_id', 2)->call('save')->assertHasNoErrors();
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openView', $id)->assertSee('Two');
        $this->assertSame('Two', Livewire::test(VoyagerBread::class, ['type' => 'pages'])->instance()->records()['rows']->firstWhere('id', $id)->author_id);
        $source = app(\App\Filament\Bread\BreadDropdownOptions::class);
        // List takes precedence over where in the original; SoftDeletes still applies.
        $this->assertSame([2 => 'Two', 3 => 'Hidden'], $source->options(new BreadTreeListFixture, 'author_id', ['relationship' => ['key' => 'id', 'label' => 'name', 'where' => ['active', 1]]]));
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId);
        foreach ([['relationship' => ['key' => 'id', 'label' => 'password']], ['relationship' => ['key' => 'name', 'label' => 'name']], ['relationship' => ['key' => 'id', 'label' => 'name'], 'default' => 'App@method'], ['relationship' => ['key' => 'id', 'label' => 'name', 'where' => ['missing', 1]]]] as $invalid) {
            $metadata->set('data.details', json_encode($invalid))->call('save')->assertHasErrors(['details']);
        }
        $metadata->set('data.details', json_encode($details))->call('save')->assertHasNoErrors();
        DB::table('data_rows')->where('id', $rowId)->update(['details' => '{"relationship":{"key":"id","label":"missing"}}']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->set('data.title', 'Preserved')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => 'Preserved', 'author_id' => 2]);
    }

    public function test_adv_image_keeps_old_files_on_later_sql_failure_and_retries_same_upload(): void
    {
        Storage::fake('public');
        require_once database_path('migrations/2021_11_19_144609_create_media_table.php');
        (new \CreateMediaTable)->up();
        (require database_path('migrations/2026_09_25_120000_add_generated_conversions_to_media_table.php'))->up();
        Schema::create('canvas_new', function (Blueprint $table) { $table->id(); $table->timestamps(); });
        $this->admin(['browse_admin', 'browse_canvas_new', 'read_canvas_new', 'edit_canvas_new', 'add_canvas_new', 'browse_bread']);
        $typeId = DB::table('data_types')->insertGetId(['name' => 'canvas_new', 'slug' => 'canvas-new', 'model_name' => BreadRecoveryMediaFixture::class]);
        foreach (['cover', 'second'] as $field) {
            DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => $field, 'type' => 'adv_image', 'display_name' => $field, 'details' => '{}']);
        }
        $record = BreadRecoveryMediaFixture::create();
        $oldMedia = [];
        foreach (['cover', 'second'] as $field) {
            $oldMedia[] = $record->addMedia(UploadedFile::fake()->image('old-'.$field.'.jpg', 40, 20))->toMediaCollection($field, 'public');
            $record->unsetRelation('media');
        }
        $disk = Storage::disk('public');
        $oldFiles = collect($disk->allFiles())->mapWithKeys(fn ($path) => [$path => $disk->get($path)])->all();
        $this->assertCount(4, $oldFiles); // Two originals and actual GD conversions.
        DB::statement("CREATE TRIGGER reject_second_media BEFORE INSERT ON media WHEN NEW.file_name = 'failed.jpg' BEGIN SELECT RAISE(ABORT, 'Fixture second media SQL failure'); END");
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'canvas-new', 'record' => $record->id])
            ->set('singleImageUploads.cover', UploadedFile::fake()->image('replacement.jpg', 40, 20))
            ->set('singleImageUploads.second', UploadedFile::fake()->image('failed.jpg', 40, 20))
            ->set('singleImageProperties.cover.alt', 'Draft alt');
        $temporary = $editor->instance()->singleImageUploads['cover']->getRealPath();
        try { $editor->call('save'); $this->fail('Expected SQL rejection'); }
        catch (\Illuminate\Database\QueryException $error) { $this->assertStringContainsString('Fixture second media SQL failure', $error->getMessage()); }
        $this->assertSame(array_keys($oldFiles), $disk->allFiles());
        foreach ($oldFiles as $path => $bytes) { $this->assertSame($bytes, $disk->get($path)); }
        $this->assertSame($oldMedia[0]->id, $record->fresh()->getFirstMedia('cover')->id);
        $this->assertDatabaseCount('media', 2);
        $this->assertFileExists($temporary);
        $creator = Livewire::test(VoyagerBread::class, ['type' => 'canvas-new'])->call('openCreate')
            ->set('singleImageUploads.cover', UploadedFile::fake()->image('created.jpg', 40, 20))
            ->set('singleImageUploads.second', UploadedFile::fake()->image('failed.jpg', 40, 20));
        try { $creator->call('save'); $this->fail('Expected create rollback'); }
        catch (\Illuminate\Database\QueryException $error) { $this->assertStringContainsString('Fixture second media SQL failure', $error->getMessage()); }
        $this->assertNull($creator->instance()->recordId);
        $this->assertDatabaseCount('canvas_new', 1);
        $this->assertDatabaseCount('media', 2);
        $this->assertSame(array_keys($oldFiles), $disk->allFiles());
        DB::statement('DROP TRIGGER reject_second_media');
        $editor->call('save')->assertHasNoErrors();
        $this->assertDatabaseCount('media', 2);
        $new = $record->fresh()->getFirstMedia('cover');
        $this->assertSame('replacement.jpg', $new->file_name);
        $this->assertSame('Draft alt', $new->getCustomProperty('alt'));
        $this->assertSame([], $editor->get('singleImageUploads'));
        foreach (array_keys($oldFiles) as $path) { $disk->assertMissing($path); }
        $disk->assertExists($new->getPathRelativeToRoot());
        $disk->assertExists($new->getPathRelativeToRoot('small'));
    }

    public function test_adv_image_cleanup_after_conversion_failure_preserves_source_and_old_media(): void
    {
        Storage::fake('public');
        require_once database_path('migrations/2021_11_19_144609_create_media_table.php');
        (new \CreateMediaTable)->up();
        (require database_path('migrations/2026_09_25_120000_add_generated_conversions_to_media_table.php'))->up();
        Schema::create('canvas_new', function (Blueprint $table) { $table->id(); $table->timestamps(); });
        $this->admin(['browse_admin', 'browse_canvas_new', 'read_canvas_new', 'edit_canvas_new', 'add_canvas_new', 'browse_bread']);
        $typeId = DB::table('data_types')->insertGetId(['name' => 'canvas_new', 'slug' => 'canvas-new', 'model_name' => BreadRecoveryMediaFixture::class]);
        DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => 'cover', 'type' => 'adv_image', 'display_name' => 'Cover', 'details' => '{}']);
        $record = BreadRecoveryMediaFixture::create();
        $old = $record->addMedia(UploadedFile::fake()->image('old.jpg', 40, 20))->toMediaCollection('cover', 'public');
        $disk = Storage::disk('public');
        $before = collect($disk->allFiles())->mapWithKeys(fn ($path) => [$path => $disk->get($path)])->all();
        $this->app->instance(\Spatie\MediaLibrary\Conversions\FileManipulator::class, new class extends \Spatie\MediaLibrary\Conversions\FileManipulator {
            public function createDerivedFiles(\Spatie\MediaLibrary\MediaCollections\Models\Media $media, array $onlyConversionNames = [], bool $onlyMissing = false, bool $withResponsiveImages = false, bool $queueAll = false): void
            {
                parent::createDerivedFiles($media, $onlyConversionNames, $onlyMissing, $withResponsiveImages, $queueAll);
                Storage::disk($media->disk)->put($media->id.'/conversions/unfinished.tmp', 'Partial conversion');
                throw new \RuntimeException('Fixture conversion failure');
            }
        });
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'canvas-new', 'record' => $record->id])
            ->set('singleImageUploads.cover', UploadedFile::fake()->image('replacement.jpg', 40, 20));
        $temporary = $editor->instance()->singleImageUploads['cover']->getRealPath();
        try { $editor->call('save'); $this->fail('Expected conversion rejection'); }
        catch (\RuntimeException $error) { $this->assertSame('Fixture conversion failure', $error->getMessage()); }
        $this->assertSame(array_keys($before), $disk->allFiles());
        foreach ($before as $path => $bytes) { $this->assertSame($bytes, $disk->get($path)); }
        $this->assertDatabaseCount('media', 1);
        $this->assertSame($old->id, $record->fresh()->getFirstMedia('cover')->id);
        $this->assertFileExists($temporary);
        $this->app->forgetInstance(\Spatie\MediaLibrary\Conversions\FileManipulator::class);
        $editor->call('save')->assertHasNoErrors();
        $this->assertDatabaseCount('media', 1);
        foreach (array_keys($before) as $path) { $disk->assertMissing($path); }
    }

    public function test_adv_image_single_collection_create_replace_properties_and_guards(): void
    {
        Storage::fake('public');
        require_once database_path('migrations/2021_11_19_144609_create_media_table.php');
        (new \CreateMediaTable)->up();
        (require database_path('migrations/2026_09_25_120000_add_generated_conversions_to_media_table.php'))->up();
        Schema::create('canvas_new', function (Blueprint $table) { $table->id(); $table->timestamps(); });
        $this->admin(['browse_admin', 'browse_canvas_new', 'read_canvas_new', 'edit_canvas_new', 'add_canvas_new', 'browse_bread']);
        $typeId = DB::table('data_types')->insertGetId(['name' => 'canvas_new', 'slug' => 'canvas-new', 'model_name' => \App\Models\CanvasNew::class]);
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'cover', 'type' => 'adv_image', 'required' => true, 'display_name' => 'Обложка', 'details' => '{}']);
        $creator = Livewire::test(VoyagerBread::class, ['type' => 'canvas-new'])->call('openCreate')->assertSee('Обложка')
            ->call('save')->assertHasErrors(['singleImageUploads.cover']);
        $this->assertDatabaseCount('canvas_new', 0);
        $creator->set('singleImageUploads.cover', UploadedFile::fake()->image('first.jpg'))
            ->set('singleImageProperties.cover.title', 'First title')->set('singleImageProperties.cover.alt', 'First alt')
            ->call('save')->assertHasNoErrors();
        $id = DB::table('canvas_new')->value('id');
        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::first();
        $this->assertSame('cover', $media->collection_name);
        $this->assertSame('First alt', $media->getCustomProperty('alt'));
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
        Livewire::test(VoyagerBread::class, ['type' => 'canvas-new'])->assertSeeHtml('src="'.$media->getUrl().'"')
            ->call('openView', $id)->assertSeeHtml('src="'.$media->getUrl().'"');
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'canvas-new', 'record' => $id])
            ->assertSet('singleImageProperties.cover.alt', 'First alt')->call('changeLocale', 'ru')
            ->set('singleImageProperties.cover.alt', 'Shared alt')->call('save')->assertHasNoErrors();
        $this->assertSame('Shared alt', $media->fresh()->getCustomProperty('alt'));
        $oldPath = $media->getPathRelativeToRoot();
        $editor->set('singleImageUploads.cover', UploadedFile::fake()->create('bad.txt'))->call('save')->assertHasErrors(['singleImageUploads.cover']);
        Storage::disk('public')->assertExists($oldPath);
        $editor->set('singleImageUploads.cover', UploadedFile::fake()->image('replacement.png'))->call('save')->assertHasNoErrors();
        $this->assertDatabaseCount('media', 1);
        Storage::disk('public')->assertMissing($oldPath);
        $replacement = \Spatie\MediaLibrary\MediaCollections\Models\Media::first();
        $this->assertSame('replacement.png', $replacement->file_name);
        $this->assertSame('Shared alt', $replacement->getCustomProperty('alt'));
        $this->assertDatabaseCount('translations', 0);
        $otherId = DB::table('canvas_new')->insertGetId([]);
        $other = \App\Models\CanvasNew::findOrFail($otherId)->addMedia(UploadedFile::fake()->image('other.jpg'))->toMediaCollection('cover', 'public');
        try { $editor->instance()->deleteSingleImage('cover', $other->id); $this->fail('Foreign media accepted'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { $this->assertSame(404, $exception->getStatusCode()); }
        $this->assertDatabaseHas('media', ['id' => $other->id]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'canvas-new', 'record' => $id])->call('deleteSingleImage', 'cover', $replacement->id)->assertHasNoErrors();
        $this->assertDatabaseMissing('media', ['id' => $replacement->id]);
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId)->call('save')->assertHasNoErrors();
        DB::table('data_rows')->where('id', $rowId)->update(['edit' => false]);
        try { $editor->instance()->deleteSingleImage('cover', $other->id); $this->fail('Hidden field accepted'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { $this->assertSame(404, $exception->getStatusCode()); }
    }

    public function test_adv_page_layout_catalog_reorder_preservation_translations_and_validation(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        Schema::create('layout_sources', function (Blueprint $table) {
            $table->id(); $table->string('key'); $table->string('title'); $table->integer('status'); $table->integer('order'); $table->softDeletes();
        });
        DB::table('layout_sources')->insert([
            ['key' => 'second', 'title' => 'Second', 'status' => 1, 'order' => 2, 'deleted_at' => null],
            ['key' => 'first', 'title' => 'First', 'status' => 1, 'order' => 1, 'deleted_at' => null],
            ['key' => 'inactive', 'title' => 'Inactive', 'status' => 0, 'order' => 0, 'deleted_at' => null],
            ['key' => 'deleted', 'title' => 'Deleted', 'status' => 1, 'order' => 0, 'deleted_at' => now()],
        ]);
        $details = ['layout_fields' => ['body' => 'Содержимое'], 'block_model' => BreadLayoutSourceFixture::class, 'form_model' => BreadLayoutSourceFixture::class];
        $codec = app(\App\Filament\Bread\BreadPageLayout::class);
        $catalog = $codec->catalog($details);
        $this->assertSame(['Field', 'Block', 'Block', 'Form', 'Form'], array_column(array_values($catalog), 'type'));
        $this->assertSame(['first', 'second'], array_column(array_values(array_filter($catalog, fn ($item) => $item['type'] === 'Block')), 'key'));
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'adv_page_layout', 'display_name' => 'Секции', 'details' => json_encode($details)]);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')->update(['type' => 'adv_page_layout', 'details' => json_encode($details)]);
        $original = '[ {"type":"Field","key":"body","title":"Legacy body","icon":"voyager-receipt","custom":7}, {"type":"Block","key":"retired","title":"Old block","icon":null} ]';
        $id = DB::table('pages')->insertGetId(['title' => 'Layout', 'flags' => $original, 'meta_description' => $original]);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('Добавить секцию')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => $original]);
        $keys = array_keys($editor->get('data.flags.rows'));
        $action = fn ($name) => \Filament\Actions\Testing\TestAction::make($name)->schemaComponent('flags.rows', 'form');
        $editor->callAction($action('add'))->assertHasNoActionErrors();
        $new = array_key_last($editor->get('data.flags.rows'));
        $editor->set('data.flags.rows.'.$new.'.choice', $codec->token('Form', 'first'))
            ->callAction($action('reorder'), arguments: ['items' => [$new, $keys[1], $keys[0]]])->assertHasNoActionErrors()
            ->call('changeLocale', 'ru')->call('changeLocale', 'en')->call('save')->assertHasNoErrors();
        $saved = json_decode(DB::table('pages')->where('id', $id)->value('flags'), true);
        $this->assertSame(['first', 'retired', 'body'], array_column($saved, 'key'));
        $this->assertSame('layout-icon voyager-window-list', $saved[0]['icon']);
        $this->assertSame(7, $saved[2]['custom']);
        $this->assertNull($saved[1]['icon']);
        $editor->call('changeLocale', 'ru')->set('data.meta_description.rows', [['choice' => $codec->token('Field', 'body')]])->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => $original]);
        $translated = json_decode(DB::table('translations')->where('foreign_key', $id)->where('column_name', 'meta_description')->where('locale', 'ru')->value('value'), true);
        $this->assertSame('Field', $translated[0]['type']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->call('changeLocale', 'ru')->assertSet('data.meta_description.rows', fn ($rows) => array_values($rows) === [['choice' => $codec->token('Field', 'body')]]);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openView', $id)->assertSee('Legacy body');
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->set('data.title', 'New layout')
            ->set('data.flags.rows', [['choice' => $codec->token('Block', 'second')]])->call('save')->assertHasNoErrors();
        $created = json_decode(DB::table('pages')->where('title', 'New layout')->value('flags'), true);
        $this->assertSame('second', $created[0]['key']);
        foreach ([['choice' => $codec->token('Block', 'inactive')], ['choice' => $codec->token('Block', 'deleted')], ['choice' => 'unknown'], ['choice' => []], ['choice' => $codec->token('Field', 'body'), 'title' => 'forged']] as $bad) {
            try { $codec->encode($details, $original, ['rows' => [$bad]], 'data.flags'); $this->fail('Invalid section accepted'); }
            catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('data.flags', $e->errors()); }
        }
        $this->assertSame('[]', $codec->encode($details, $original, ['rows' => []], 'data.flags'));
        $this->assertNull($codec->document('{}'));
        $this->assertNull($codec->document('[{"type":"Bad"}]'));
        DB::table('pages')->where('id', $id)->update(['flags' => '{broken']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('Значение сохранено без изменений')->set('data.title', 'Updated')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => '{broken']);
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId);
        foreach ([['layout_fields' => ['a' => ['bad']]], ['block_model' => \App\Models\Page::class], ['form_model' => 'Invalid'], ['default' => '{}']] as $badDetails) {
            $metadata->set('data.details', json_encode($badDetails))->call('save')->assertHasErrors(['details']);
        }
        $metadata->set('data.details', json_encode($details))->call('save')->assertHasNoErrors();
    }

    public function test_custom_browse_view_receives_original_context_models_raw_content_and_page_only(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        app('view')->addNamespace('bread-audit', base_path('tests/Fixtures/views'));
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadListTranslatedFixture::class]);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update([
            'type' => 'select_dropdown', 'details' => json_encode(['view' => 'bread-audit::bread-custom-browse', 'nested' => ['label' => 'Context'], 'options' => ['key' => 'Standard caption']]),
        ]);
        for ($id = 1; $id <= 12; $id++) { DB::table('pages')->insert(['id' => $id, 'title' => 'key']); }
        DB::table('translations')->insert(['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => 2, 'locale' => 'ru', 'value' => 'Translated raw']);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('perPage', 10)->call('gotoPage', 2)
            ->assertSee('browse|browse|pages|bread-audit::bread-custom-browse|Context|2|2|2', false)->assertDontSee('Standard caption');
        $this->assertSame([2, 1], array_keys($page->instance()->records()['customCells']));
        $this->assertStringContainsString('>key</strong>', (string) $page->instance()->records()['customCells'][2]['title']);
        $page->call('changeLocale', 'ru')->assertSee('Translated raw');
        $page->assertSee('Custom component');
        $this->assertDatabaseHas('pages', ['id' => 2, 'title' => 'key']);
        $this->assertDatabaseCount('pages', 12);
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadCustomBrowseAccessorFixture::class]);
        $accessor = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('perPage', 10)->call('gotoPage', 2)->assertSee('Accessor: key');
        $this->assertStringContainsString('Accessor: key', (string) $accessor->instance()->records()['customCells'][2]['title']);
        $this->assertStringContainsString('data-custom-current>Accessor: key</small>', (string) $accessor->instance()->records()['customCells'][2]['title']);
        $page->set('search', 'no match');
        $this->assertSame([], $page->instance()->records()['customCells']);
    }

    public function test_custom_browse_view_unavailable_names_errors_and_hidden_rows_do_not_leak_content(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        app('view')->addNamespace('bread-audit', base_path('tests/Fixtures/views'));
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $id = DB::table('pages')->insertGetId(['title' => 'Secret raw value']);
        foreach (['missing.view', '../outside', '/absolute/path', ['not-a-name'], 'bread-audit::bread-custom-broken', 'voyager::unregistered'] as $name) {
            DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['details' => json_encode(['view' => $name])]);
            Livewire::test(VoyagerBread::class, ['type' => 'pages'])->assertSee('Шаблон колонки недоступен.')->assertDontSee('Secret raw value')->assertDontSee('Template failure');
        }
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['browse' => false, 'details' => json_encode(['view' => 'bread-audit::bread-custom-browse'])]);
        $this->assertSame([], Livewire::test(VoyagerBread::class, ['type' => 'pages'])->instance()->records()['customCells']);
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => 'Secret raw value']);
    }

    public function test_inline_text_number_checkbox_save_base_values_and_validate_without_full_form_writes(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadListTranslatedFixture::class]);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['details' => json_encode(['browse_inline_editor' => false, 'validation' => ['edit' => ['rule' => 'max:20']]])]);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')->update(['type' => 'number', 'details' => json_encode(['browse_inline_editor' => true, 'min' => 0, 'max' => 10, 'step' => 0.5])]);
        DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'checkbox', 'browse' => true, 'details' => json_encode(['on' => 'On', 'off' => 'Off', 'browse_inline_checkbox' => false])]);
        $id = DB::table('pages')->insertGetId(['title' => 'Base', 'meta_description' => '1', 'flags' => '0', 'image' => 'keep.jpg']);
        DB::table('translations')->insert(['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => $id, 'locale' => 'ru', 'value' => 'Перевод']);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('changeLocale', 'ru');
        $this->assertSame('Перевод', $page->instance()->records()['rows']->first()->title);
        $this->assertSame('Base', $page->instance()->records()['inlineValues'][$id]['title']);
        $page->assertSee('Редактировать поле')->assertSee('x-on:keydown.enter.prevent', false)->assertSee('Отменить изменение поля');
        $this->assertStringNotContainsString('@js(', $page->html());
        $document = new \DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">'.$page->html());
        $cancelExpressions = [];
        foreach ($document->getElementsByTagName('button') as $button) {
            if ($button->getAttribute('aria-label') === 'Отменить изменение поля') {
                $cancelExpressions[] = $button->getAttribute('x-on:click');
            }
        }
        $this->assertContains("value = 'Base'; editing = false", $cancelExpressions);
        $page->call('saveInlineField', $id, 'title', 'Updated')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => 'Updated', 'image' => 'keep.jpg', 'meta_description' => '1', 'flags' => '0']);
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'value' => 'Перевод']);
        $page->call('saveInlineField', $id, 'title', str_repeat('x', 21))->assertHasErrors(['inline.'.$id.'.title']);
        foreach (['not a number', '-1', '11', '1.25', ['invalid']] as $invalid) {
            $page->call('saveInlineField', $id, 'meta_description', $invalid)->assertHasErrors(['inline.'.$id.'.meta_description']);
        }
        $page->call('saveInlineField', $id, 'meta_description', '2.5')->assertHasNoErrors(['inline.'.$id.'.meta_description']);
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => '2.5']);
        $page->call('saveInlineField', $id, 'flags', 1)->assertHasNoErrors(['inline.'.$id.'.flags']);
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => '1']);
        $page->call('saveInlineField', $id, 'flags', 'yes')->assertHasErrors(['inline.'.$id.'.flags']);
        $page->call('saveInlineField', $id, 'flags', 0)->assertHasNoErrors(['inline.'.$id.'.flags']);
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => '0', 'title' => 'Updated', 'image' => 'keep.jpg']);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['details' => json_encode(['browse_inline_editor' => true, 'validation' => ['edit' => ['rule' => 'different:meta_description']]])]);
        $page->call('saveInlineField', $id, 'title', '2.5')->assertHasErrors(['inline.'.$id.'.title']);
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => 'Updated']);
        $this->assertFalse($page->instance()->editing);
    }

    public function test_inline_field_is_hidden_and_direct_save_forbidden_without_edit_permission(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['details' => '{"browse_inline_editor":true}']);
        $id = DB::table('pages')->insertGetId(['title' => 'Keep']);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->assertDontSee('Редактировать поле');
        $this->assertSame([], $page->instance()->records()['inlineValues']);
        $page->call('saveInlineField', $id, 'title', 'Forged')->assertForbidden();
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => 'Keep']);
    }

    public function test_inline_field_rejects_disabled_metadata_wrong_types_and_model_veto(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['details' => '{"browse_inline_editor":true}']);
        DB::table('pages')->insert(['id' => 1, 'title' => 'Keep']);
        foreach ([['details' => '{}'], ['edit' => false], ['browse' => false], ['type' => 'password'], ['details' => '{"browse_inline_editor":true,"view":"custom"}']] as $disabled) {
            DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(array_merge(['details' => '{"browse_inline_editor":true}', 'edit' => true, 'browse' => true, 'type' => 'text'], $disabled));
            Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('saveInlineField', 1, 'title', 'Forged')->assertForbidden();
        }
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['type' => 'text', 'details' => '{"browse_inline_editor":true}']);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('saveInlineField', 1, 'title', ['invalid'])->assertHasErrors(['inline.1.title']);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['details' => '{"browse_inline_editor":true,"validation":{"rule":"unsupported_rule"}}']);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('saveInlineField', 1, 'title', 'Rejected')->assertHasErrors(['inline.1.title']);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['details' => '{"browse_inline_editor":true}']);
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadOrderVetoFixture::class]);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('saveInlineField', 1, 'title', 'Rejected')->assertHasErrors(['inline.1.title']);
        $this->assertDatabaseHas('pages', ['id' => 1, 'title' => 'Keep']);
    }

    public function test_inline_group_dialog_matches_original_text_only_base_save_and_cancel(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $details = ['browse_inline_editor' => false, 'fields' => [
            'caption' => ['type' => 'text', 'label' => 'Подпись', 'value' => 'Base', 'custom' => 'keep'],
            'count' => ['type' => 'number', 'label' => 'Число', 'value' => 3],
            'body' => ['type' => 'textarea', 'label' => 'Описание', 'value' => 'Unchanged'],
        ], 'version' => 7];
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')->update([
            'type' => 'adv_fields_group', 'browse' => true, 'details' => json_encode($details),
        ]);
        $original = json_encode($details);
        $id = DB::table('pages')->insertGetId(['title' => 'Keep title', 'meta_description' => $original, 'image' => 'keep.jpg']);
        $translated = json_encode(array_replace($details, ['version' => 99]));
        DB::table('translations')->insert(['table_name' => 'pages', 'column_name' => 'meta_description', 'foreign_key' => $id, 'locale' => 'ru', 'value' => $translated]);
        $action = \Filament\Actions\Testing\TestAction::make('editInlineGroup')->arguments(['id' => $id, 'field' => 'meta_description']);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->assertSee('Редактировать группу');
        $this->assertStringNotContainsString('@js(', $page->html());
        $page->call('changeLocale', 'ru')->mountAction($action)->assertActionMounted('editInlineGroup');
        $schema = $page->instance()->getSchema($page->instance()->getMountedActionSchemaName());
        $this->assertSame(['values.caption'], collect($schema->getFlatComponents())->filter(fn ($field) => $field instanceof \Filament\Forms\Components\TextInput)->map(fn ($field) => $field->getName())->values()->all());
        $page->call('unmountAction');
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => $original]);
        $page->callAction($action, ['values' => ['caption' => 'Updated']])->assertHasNoActionErrors();
        $saved = json_decode(DB::table('pages')->where('id', $id)->value('meta_description'), true);
        $this->assertSame('Updated', $saved['fields']['caption']['value']);
        $this->assertSame('keep', $saved['fields']['caption']['custom']);
        $this->assertSame(3, $saved['fields']['count']['value']);
        $this->assertSame('Unchanged', $saved['fields']['body']['value']);
        $this->assertSame(7, $saved['version']);
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => 'Keep title', 'image' => 'keep.jpg']);
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'value' => $translated]);
        $before = DB::table('pages')->where('id', $id)->value('meta_description');
        $page->callAction($action, ['values' => ['caption' => str_repeat('x', 50001)]])->assertHasActionErrors(['values.caption']);
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => $before]);
        $page->call('unmountAction');
        DB::table('pages')->where('id', $id)->update(['meta_description' => null]);
        $page->callAction($action, ['values' => ['caption' => 'From defaults']])->assertHasNoActionErrors();
        $this->assertSame('From defaults', json_decode(DB::table('pages')->where('id', $id)->value('meta_description'), true)['fields']['caption']['value']);
    }

    public function test_inline_group_dialog_requires_edit_and_enabled_metadata(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $details = ['browse_inline_editor' => true, 'fields' => ['caption' => ['type' => 'text', 'label' => 'Caption', 'value' => 'Keep']]];
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'adv_fields_group', 'browse' => true, 'details' => json_encode($details)]);
        $id = DB::table('pages')->insertGetId(['title' => 'Restricted', 'flags' => json_encode($details)]);
        $arguments = ['id' => $id, 'field' => 'flags'];
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->assertDontSee('Редактировать группу')
            ->call('mountAction', 'editInlineGroup', $arguments)->assertForbidden();
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        DB::table('data_rows')->where('id', $rowId)->update(['details' => json_encode(['fields' => $details['fields']])]);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('mountAction', 'editInlineGroup', $arguments)->assertForbidden();
        DB::table('data_rows')->where('id', $rowId)->update(['details' => json_encode($details), 'edit' => false]);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->assertDontSee('Редактировать группу')->call('mountAction', 'editInlineGroup', $arguments)->assertForbidden();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => json_encode($details)]);
    }

    public function test_inline_group_rechecks_metadata_scope_type_and_model_veto(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        Schema::table('pages', fn (Blueprint $table) => $table->integer('id_type')->nullable());
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $details = ['browse_inline_editor' => true, 'fields' => ['caption' => ['type' => 'text', 'label' => 'Caption', 'value' => 'Keep']]];
        $original = json_encode($details);
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'adv_fields_group', 'browse' => true, 'details' => $original]);
        DB::table('pages')->insert([
            ['id' => 1, 'title' => 'Visible', 'flags' => $original, 'meta_description' => 'Visible', 'id_type' => 2],
            ['id' => 2, 'title' => 'Hidden', 'flags' => $original, 'meta_description' => 'Hidden', 'id_type' => 2],
        ]);
        $args = ['id' => 1, 'field' => 'flags'];
        $action = \Filament\Actions\Testing\TestAction::make('editInlineGroup')->arguments($args);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->mountAction($action)->assertActionMounted('editInlineGroup');
        DB::table('data_rows')->where('id', $rowId)->update(['edit' => false]);
        $page->callMountedAction(['values' => ['caption' => 'Forged']])->assertForbidden();
        DB::table('data_rows')->where('id', $rowId)->update(['edit' => true]);
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadScopedPageFixture::class, 'details' => json_encode(['scope' => 'visibleForBread'])]);
        $assertMissing = function (array $arguments, ?int $type = null): void {
            try {
                $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
                if ($type !== null) { $page->set('filterType', $type); }
                $page->call('mountAction', 'editInlineGroup', $arguments);
                $this->fail('Excluded record opened in the inline editor');
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
                $this->assertNotEmpty($exception->getIds());
            }
        };
        $assertMissing(['id' => 2, 'field' => 'flags']);
        $assertMissing($args, 3);
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadGlobalScopeFixture::class, 'details' => '{}']);
        $assertMissing(['id' => 2, 'field' => 'flags']);
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadOrderVetoFixture::class]);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->callAction($action, ['values' => ['caption' => 'Rejected']])->assertHasActionErrors(['values']);
        $this->assertDatabaseHas('pages', ['id' => 1, 'flags' => $original]);
        $this->assertDatabaseHas('pages', ['id' => 2, 'flags' => $original]);
    }

    public function test_adv_fields_group_roundtrip_defaults_validation_and_translations(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $details = ['fields' => ['title' => ['type' => 'text', 'label' => 'Заголовок', 'value' => 'Default'],
            'count' => ['type' => 'number', 'label' => 'Количество', 'value' => 3], 'body' => ['type' => 'textarea', 'label' => 'Описание']]];
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'adv_fields_group', 'display_name' => 'Группа', 'details' => json_encode($details)]);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')->update(['type' => 'adv_fields_group', 'details' => json_encode($details)]);
        $originalDoc = $details;
        $originalDoc['version'] = 7;
        $originalDoc['fields']['old'] = ['type' => 'text', 'label' => 'Старое', 'value' => 'Keep', 'custom' => true];
        $original = json_encode($originalDoc, JSON_UNESCAPED_UNICODE);
        $id = DB::table('pages')->insertGetId(['title' => 'Page', 'flags' => $original, 'meta_description' => $original]);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('Старое')
            ->assertSet('data.flags.values.old', 'Keep')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => $original]);
        $editor->set('data.flags.values.count', 'not numeric')->call('save')->assertHasErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => $original]);
        $editor->set('data.flags.values.count', '5')->set('data.flags.values.body', 'Text area')
            ->call('changeLocale', 'ru')->call('changeLocale', 'en')->call('save')->assertHasNoErrors();
        $saved = json_decode(DB::table('pages')->where('id', $id)->value('flags'), true);
        $this->assertSame('5', (string) $saved['fields']['count']['value']);
        $this->assertSame('Text area', $saved['fields']['body']['value']);
        $this->assertTrue($saved['fields']['old']['custom']);
        $this->assertSame(7, $saved['version']);
        $editor->call('changeLocale', 'ru')->set('data.meta_description.values.title', 'Перевод')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => $original]);
        $translated = json_decode(DB::table('translations')->where('foreign_key', $id)->where('column_name', 'meta_description')->where('locale', 'ru')->value('value'), true);
        $this->assertSame('Перевод', $translated['fields']['title']['value']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->call('changeLocale', 'ru')->assertSet('data.meta_description.values.title', 'Перевод');
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->set('data.title', 'Created group')->call('save')->assertHasNoErrors();
        $created = json_decode(DB::table('pages')->where('title', 'Created group')->value('flags'), true);
        $this->assertSame('Default', $created['fields']['title']['value']);
        $this->assertSame(3, $created['fields']['count']['value']);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openView', $id)->assertSee('Количество: 5');
        $codec = app(\App\Filament\Bread\BreadFieldsGroup::class);
        foreach ([['unknown' => 'bad'], ['count' => 'bad'], ['title' => ['nested']]] as $values) {
            try { $codec->encode($details, $original, ['values' => $values], 'data.flags'); $this->fail('Forged group accepted'); }
            catch (\Illuminate\Validation\ValidationException $exception) { $this->assertArrayHasKey('data.flags', $exception->errors()); }
        }
        DB::table('pages')->where('id', $id)->update(['flags' => '{broken']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('Значение сохранено без изменений')
            ->set('data.title', 'Another')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => '{broken']);
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId);
        $metadata->set('data.details', '{"fields":{"bad":{"type":"image","label":"Bad"}}}')->call('save')->assertHasErrors(['details']);
        $metadata->set('data.details', json_encode($details))->call('save')->assertHasNoErrors();
    }

    public function test_adv_json_rows_add_delete_reorder_preserve_legacy_fields_and_translations(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $details = ['json_fields' => ['key' => 'Ключ', 'value' => 'Значение']];
        DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'adv_json', 'display_name' => 'JSON строки', 'details' => json_encode($details)]);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')->update(['type' => 'adv_json', 'details' => json_encode($details)]);
        $original = '{"fields":{"key":"Legacy key","value":"Legacy value","extra":"Legacy extra"},"rows":[{"key":"one","value":"Первый","extra":"keep"},{"key":"two","value":"Второй"}],"version":7}';
        $id = DB::table('pages')->insertGetId(['title' => 'Page', 'flags' => $original, 'meta_description' => $original]);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('Legacy extra');
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => $original]);
        $keys = array_keys($editor->get('data.flags.rows'));
        $action = fn (string $name) => \Filament\Actions\Testing\TestAction::make($name)->schemaComponent('flags.rows', 'form');
        $editor->callAction($action('add'))->assertHasNoActionErrors();
        $newKey = array_key_last($editor->get('data.flags.rows'));
        $editor->set('data.flags.rows.'.$newKey.'.key', 'new')->set('data.flags.rows.'.$newKey.'.value', 'Новый')
            ->callAction($action('reorder'), arguments: ['items' => [$newKey, $keys[0], $keys[1]]])->assertHasNoActionErrors()
            ->callAction($action('delete'), arguments: ['item' => $keys[1]])->assertHasNoActionErrors()
            ->call('changeLocale', 'ru')->call('changeLocale', 'en')->call('save')->assertHasNoErrors();
        $saved = json_decode(DB::table('pages')->where('id', $id)->value('flags'), true);
        $this->assertSame(['new', 'one'], array_column($saved['rows'], 'key'));
        $this->assertSame('keep', $saved['rows'][1]['extra']);
        $this->assertSame('Legacy extra', $saved['fields']['extra']);
        $this->assertSame(7, $saved['version']);
        $editor->call('changeLocale', 'ru')->set('data.meta_description.rows', [['key' => 'ru', 'value' => 'Перевод']])->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => $original]);
        $translation = json_decode(DB::table('translations')->where('foreign_key', $id)->where('column_name', 'meta_description')->where('locale', 'ru')->value('value'), true);
        $this->assertSame([['key' => 'ru', 'value' => 'Перевод']], $translation['rows']);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openView', $id)->assertSee('Legacy extra: keep');
        $creator = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->set('data.title', 'Created')
            ->set('data.flags.rows', [['key' => 'created', 'value' => 'Value']])->call('save')->assertHasNoErrors();
        $created = json_decode(DB::table('pages')->where('title', 'Created')->value('flags'), true);
        $this->assertSame([['key' => 'created', 'value' => 'Value']], $created['rows']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->set('data.flags.rows', [])->call('save')->assertHasNoErrors();
        $empty = json_decode(DB::table('pages')->where('id', $id)->value('flags'), true);
        $this->assertSame([], $empty['rows']);
        $this->assertSame(7, $empty['version']);
    }

    public function test_adv_json_malformed_document_is_preserved_and_forged_cells_are_rejected(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'adv_json', 'display_name' => 'JSON', 'details' => '{}']);
        $id = DB::table('pages')->insertGetId(['title' => 'Old', 'flags' => '{broken']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('Значение сохранено без изменений')
            ->set('data.title', 'Updated')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => 'Updated', 'flags' => '{broken']);
        $json = app(\App\Filament\Bread\BreadJsonRows::class);
        foreach ([['rows' => [['unknown' => 'bad']]], ['rows' => [['key' => ['nested']]]], ['rows' => 'bad']] as $bad) {
            try { $json->encode([], null, $bad, 'data.flags'); $this->fail('Bad rows were accepted'); }
            catch (\Illuminate\Validation\ValidationException $exception) { $this->assertArrayHasKey('data.flags', $exception->errors()); }
        }
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId);
        $metadata->set('data.details', '{"json_fields":{"bad.key":"Title"}}')->call('save')->assertHasErrors(['details']);
        $metadata->set('data.details', '{"json_fields":[]}')->call('save')->assertHasErrors(['details']);
        $metadata->set('data.details', '{"default":"bad"}')->call('save')->assertHasErrors(['details']);
        $metadata->set('data.details', '{"json_fields":{"key":"Ключ","value":"Значение"}}')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => '{broken']);
    }

    public function test_metadata_editor_accepts_extra_types_and_rejects_invalid_options(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $rowId = DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->value('id');
        DB::table('pages')->insert(['title' => 'Untouched']);
        $editor = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId);
        foreach (['radio_btn', 'select_multiple'] as $type) {
            $editor->set('data.type', $type)->set('data.details', '{}')->call('save')->assertHasErrors(['details']);
            $editor->set('data.details', '{"options":{"a":{"bad":"nested"}}}')->call('save')->assertHasErrors(['details']);
            $editor->set('data.details', '{"options":{"a":"Alpha"}}')->call('save')->assertHasNoErrors();
            $this->assertDatabaseHas('data_rows', ['id' => $rowId, 'type' => $type]);
            $editor->call('openEdit', $rowId);
        }
        $editor->set('data.details', '{"options":{"a":"Alpha"},"relationship":{"key":"id"}}')->call('save')->assertHasErrors(['details']);
        foreach (['time', 'markdown_editor', 'hidden'] as $type) {
            $editor->set('data.type', $type)->set('data.details', '{}')->call('save')->assertHasNoErrors();
            $this->assertDatabaseHas('data_rows', ['id' => $rowId, 'type' => $type]);
            $editor->call('openEdit', $rowId);
        }
        $this->assertDatabaseHas('pages', ['title' => 'Untouched']);
    }

    public function test_extra_voyager_field_types_roundtrip_options_time_and_markdown(): void
    {
        Schema::table('pages', function (Blueprint $table): void { $table->string('choice')->nullable(); $table->string('slot')->nullable(); $table->string('hidden_note')->nullable(); });
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->whereIn('field', ['title', 'meta_description'])->update(['type' => 'markdown_editor']);
        foreach (['flags' => 'select_multiple', 'choice' => 'radio_btn', 'slot' => 'time', 'hidden_note' => 'hidden'] as $field => $type) {
            DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => $field, 'type' => $type,
                'display_name' => $field, 'details' => json_encode(['options' => ['a' => 'Alpha', 'b' => 'Beta'], 'default' => $type === 'hidden' ? 'Hidden default' : null]), 'order' => 10]);
        }
        $id = DB::table('pages')->insertGetId(['title' => '# Original', 'flags' => '["b"]', 'choice' => 'a', 'slot' => '10:30:00']);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $components = collect($editor->instance()->form->getFlatComponents())->filter(fn ($field) => $field instanceof \Filament\Forms\Components\Field)->keyBy(fn ($field) => $field->getName());
        $this->assertInstanceOf(\Filament\Forms\Components\MarkdownEditor::class, $components['title']);
        $this->assertInstanceOf(\Filament\Forms\Components\Radio::class, $components['choice']);
        $this->assertInstanceOf(\Filament\Forms\Components\TimePicker::class, $components['slot']);
        $this->assertTrue($components['flags']->isMultiple());
        $this->assertInstanceOf(\Filament\Forms\Components\Hidden::class, $components['hidden_note']);
        $editor->assertSet('data.flags', ['b'])->set('data.flags', ['a', 'b'])->set('data.choice', 'b')->set('data.slot', '14:15:30')
            ->set('data.title', '## Updated **markdown**')->set('data.meta_description', '# Base description')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => '["a","b"]', 'choice' => 'b', 'slot' => '14:15:30', 'title' => '## Updated **markdown**']);
        $browser = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        $record = $browser->instance()->records()['rows']->first();
        $this->assertSame('Alpha, Beta', $record->flags);
        $this->assertSame('Beta', $record->choice);
        $browser->call('openView', $id)->assertSee('Alpha, Beta')->assertSee('Beta');
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $editor->set('data.flags', ['forged'])->call('save')->assertHasErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => '["a","b"]']);
        $editor->set('data.flags', [])->set('data.choice', 'forged')->call('save')->assertHasErrors();
        $editor->set('data.choice', 'a')->set('data.slot', 'bad time')->call('save')->assertHasErrors();
        $editor->set('data.slot', '09:10:11')->call('changeLocale', 'ru')->set('data.meta_description', '# Перевод')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'flags' => '[]', 'title' => '## Updated **markdown**']);
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_description' => '# Base description']);
        $this->assertDatabaseHas('translations', ['table_name' => 'pages', 'foreign_key' => $id, 'column_name' => 'meta_description', 'locale' => 'ru', 'value' => '# Перевод']);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate')->set('data.title', '# New')
            ->set('data.flags', ['a'])->set('data.choice', 'b')->set('data.slot', '08:00:00')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['title' => '# New', 'flags' => '["a"]', 'choice' => 'b', 'slot' => '08:00:00']);
        $this->assertDatabaseHas('pages', ['title' => '# New', 'hidden_note' => 'Hidden default']);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')
            ->update(['type' => 'select_multiple', 'details' => '{"options":{"a":"Alpha","b":"Beta"}}']);
        $translatedSelect = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $translatedSelect->call('changeLocale', 'ru')->set('data.meta_description', ['a', 'b'])->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'column_name' => 'meta_description', 'locale' => 'ru', 'value' => '["a","b"]']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->call('changeLocale', 'ru')->assertSet('data.meta_description', ['a', 'b']);
    }

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
            $table->string('display_name_singular')->nullable(); $table->string('icon')->nullable();
            $table->text('description')->nullable(); $table->text('details')->nullable();
            $table->string('controller')->nullable(); $table->string('policy_name')->nullable();
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

    public function test_temporal_fields_match_legacy_display_defaults_and_keep_shared_state(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'add_pages', 'edit_pages']);
        Schema::table('pages', function (Blueprint $table): void {
            $table->string('slot')->nullable(); $table->date('event_date')->nullable(); $table->dateTime('event_at')->nullable();
        });
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        foreach (['slot' => 'time', 'event_date' => 'date', 'event_at' => 'timestamp'] as $field => $type) {
            DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => $field, 'type' => $type, 'display_name' => $field,
                'details' => json_encode($type === 'time' ? ['placeholder' => 'Время встречи', 'default' => '09:15'] : []), 'order' => 10]);
        }
        $creator = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate');
        $fields = collect($creator->instance()->form->getFlatComponents())
            ->filter(fn ($field) => $field instanceof \Filament\Forms\Components\Field)->keyBy(fn ($field) => $field->getName());
        $this->assertSame('Время встречи', $fields['slot']->getPlaceholder());
        $this->assertSame('event_date', $fields['event_date']->getPlaceholder());
        $this->assertSame('Y-m-d', $fields['event_date']->getFormat());
        $this->assertSame('m/d/Y g:i A', $fields['event_at']->getDisplayFormat());
        $this->assertFalse($fields['event_at']->isNative());
        $this->assertSame(config('app.timezone'), $fields['event_at']->getTimezone());
        $creator->set('data.title', 'Temporal record')->set('data.event_date', '2026-10-09')
            ->set('data.event_at', '2026-10-09 15:45:00')->call('save')->assertHasNoErrors();
        $id = DB::table('pages')->where('title', 'Temporal record')->value('id');
        $this->assertDatabaseHas('pages', ['id' => $id, 'slot' => '09:15:00', 'event_date' => '2026-10-09', 'event_at' => '2026-10-09 15:45:00']);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $editor->set('data.slot', 'bad time')->call('save')->assertHasErrors();
        $editor->set('data.slot', '10:20:30')->set('data.event_date', 'not a date')->call('save')->assertHasErrors();
        $editor->set('data.event_date', '2026-11-10')->set('data.event_at', 'bad timestamp')->call('save')->assertHasErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'slot' => '09:15:00', 'event_date' => '2026-10-09', 'event_at' => '2026-10-09 15:45:00']);
        $editor->set('data.event_at', '2026-11-10 16:30:00')->call('changeLocale', 'ru')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'slot' => '10:20:30', 'event_date' => '2026-11-10', 'event_at' => '2026-11-10 16:30:00']);
        $this->assertSame(0, DB::table('translations')->whereIn('column_name', ['slot', 'event_date', 'event_at'])->count());
        foreach ([['placeholder' => ['bad']], ['default' => '25:00'], ['default' => false]] as $details) {
            try { app(\App\Filament\Bread\BreadTemporal::class)->validate('time', $details); $this->fail('Invalid time metadata accepted'); }
            catch (\Illuminate\Validation\ValidationException $error) { $this->assertArrayHasKey('details', $error->errors()); }
        }
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

    public function test_read_renders_cast_timestamp_without_changing_stored_value(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => 'created_at', 'type' => 'timestamp',
            'display_name' => 'Created At', 'read' => true, 'details' => '{}']);
        $id = DB::table('pages')->insertGetId(['title' => 'Timestamp record', 'created_at' => '2026-10-12 12:58:25']);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openView', $id)->assertSee('2026-10-12 12:58:25');
        $this->assertDatabaseHas('pages', ['id' => $id, 'created_at' => '2026-10-12 12:58:25']);
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

    public function test_image_fields_render_external_urls_without_rewriting_stored_values(): void
    {
        Storage::fake('public');
        $this->admin(['browse_admin', 'browse_pages', 'read_pages']);
        $rowId = DB::table('data_rows')->insertGetId([
            'data_type_id' => DB::table('data_types')->where('slug', 'pages')->value('id'),
            'field' => 'image', 'type' => 'image', 'display_name' => 'Картинка',
            'order' => 3, 'details' => '{}',
        ]);
        $remote = 'https://images.example.test/render?id=7&size=small#preview';
        $id = DB::table('pages')->insertGetId(['title' => 'External image', 'image' => $remote]);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->assertSeeHtml('src="'.e($remote).'"')
            ->call('openView', $id)->assertSeeHtml('src="'.e($remote).'"');
        $this->assertSame($remote, DB::table('pages')->where('id', $id)->value('image'));

        $local = 'pages/example.jpg';
        $http = 'http://images.example.test/photo.jpg';
        $values = json_encode([$remote, $local, $http, 'javascript:alert(1)', 'data:image/svg+xml,test', 'ftp://images.example.test/photo.jpg', '//images.example.test/photo.jpg', '../private.jpg', $remote]);
        DB::table('data_rows')->where('id', $rowId)->update(['type' => 'multiple_images']);
        DB::table('pages')->where('id', $id)->update(['image' => $values]);
        $this->assertSame([$remote, Storage::disk('public')->url($local), $http], \App\Filament\Bread\BreadImage::urls($values));
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->assertSeeHtml('src="'.e($remote).'"')->assertSeeHtml('src="'.$http.'"')
            ->call('openView', $id)->assertSeeHtml('src="'.e($remote).'"');
        $this->assertSame($values, DB::table('pages')->where('id', $id)->value('image'));
        $this->assertSame([], \App\Filament\Bread\BreadImage::urls('[invalid'));
        $this->assertSame([], Storage::disk('public')->allFiles());
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
            ->call('changeLocale', 'ru')
            ->assertSee('Автор')
            ->set('data.author_id', $author->id)
            ->call('changeLocale', 'en')->assertSet('data.author_id', $author->id)
            ->call('changeLocale', 'ru')->assertSet('data.author_id', $author->id)
            ->call('save')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'author_id' => $author->id]);
        $browse = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->instance()->records();
        $this->assertSame('author@example.test', $browse['rows']->first()->page_belongsto_user_relationship);
        $view = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openView', $id)->instance()->viewedFields();
        $this->assertSame('author@example.test', collect($view)->pluck('value', 'label')['Автор']);

        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->call('openEdit', $id)
            ->call('changeLocale', 'ru')
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

    public function test_relation_dropdown_loads_options_and_searches_beyond_initial_limit(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        for ($index = 0; $index < 55; $index++) {
            DB::table('users')->insert(['email' => 'author-' . $index . '@example.test', 'password' => 'unused']);
        }
        $lastId = DB::table('users')->where('email', 'author-54@example.test')->value('id');
        $id = DB::table('pages')->insertGetId(['title' => 'Page', 'author_id' => $lastId]);
        DB::table('data_rows')->insert([
            'data_type_id' => 1, 'field' => 'page_author_relationship', 'type' => 'relationship', 'order' => 3,
            'details' => json_encode(['type' => 'belongsTo', 'table' => 'users', 'column' => 'author_id', 'key' => 'id', 'label' => 'email']),
        ]);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $field = collect($editor->instance()->form->getFlatComponents())
            ->first(fn ($field): bool => $field instanceof \Filament\Forms\Components\Select && $field->getName() === 'author_id');
        $this->assertCount(50, $field->getOptions());
        $this->assertArrayNotHasKey($lastId, $field->getOptions());
        $this->assertSame([$lastId => 'author-54@example.test'], $field->getSearchResults('author-54'));
        $this->assertSame('author-54@example.test', $field->getOptionLabel());
        $this->assertSame([], $field->getSearchResults('no-such-author'));
        foreach (['ru', 'lv', 'ee', 'lt', 'de', 'pl', 'en'] as $locale) {
            $editor->call('changeLocale', $locale)->assertSet('data.author_id', $lastId);
            $field = collect($editor->instance()->form->getFlatComponents())
                ->first(fn ($field): bool => $field instanceof \Filament\Forms\Components\Select && $field->getName() === 'author_id');
            $this->assertNotNull($field);
            $this->assertCount(50, $field->getOptions());
            $this->assertSame('author-54@example.test', $field->getOptionLabel());
        }
    }

    public function test_validated_many_to_many_relation_syncs_pivot(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id(); $table->string('name');
        });
        Schema::create('category_page', function (Blueprint $table): void {
            $table->id(); $table->foreignId('page_id'); $table->foreignId('category_id');
        });
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages', 'add_pages', 'add_categories']);
        $categoryType = DB::table('data_types')->insertGetId(['name' => 'categories', 'slug' => 'categories', 'model_name' => BreadTagFixture::class]);
        DB::table('data_rows')->insert(['data_type_id' => $categoryType, 'field' => 'name', 'type' => 'text', 'required' => true, 'details' => '{"validation":{"rule":"min:3","add":{"rule":"max:255"}}}']);
        $id = DB::table('pages')->insertGetId(['title' => 'Page']);
        $categoryId = DB::table('categories')->insertGetId(['name' => 'Art']);
        $rowId = DB::table('data_rows')->insertGetId([
            'data_type_id' => DB::table('data_types')->where('slug', 'pages')->value('id'),
            'field' => 'page_belongstomany_category_relationship', 'type' => 'relationship',
            'display_name' => 'Категории', 'order' => 3,
            'details' => json_encode([
                'type' => 'belongsToMany', 'table' => 'categories', 'pivot_table' => 'category_page',
                'label' => 'name', 'tab_title' => 'Фильтры', 'taggable' => 'on',
            ]),
        ]);
        $type = app(BreadRegistry::class)->type('pages');
        $this->assertCount(1, app(BreadRegistry::class)->manyToManyRows($type, 'edit'));

        $otherCategoryId = DB::table('categories')->insertGetId(['name' => 'Other']);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id]);
        $field = collect($editor->instance()->form->getFlatComponents())
            ->first(fn ($field): bool => $field instanceof \Filament\Forms\Components\Select && $field->getName() === '__pivot_' . $rowId);
        $this->assertSame([$categoryId => 'Art', $otherCategoryId => 'Other'], $field->getOptions());
        $this->assertSame([$otherCategoryId => 'Other'], $field->getSearchResults('Other'));
        $this->assertNotNull($field->getCreateOptionAction());
        $createAction = \Filament\Actions\Testing\TestAction::make('createOption')->schemaComponent('__pivot_'.$rowId, 'form');
        $editor->callAction($createAction, ['label' => 'Through modal'])->assertHasNoActionErrors();
        $modalId = DB::table('categories')->where('name', 'Through modal')->value('id');
        $this->assertNotNull($modalId);
        $this->assertContains((int) $modalId, array_map('intval', $editor->get('data.__pivot_'.$rowId)));
        $this->assertDatabaseMissing('category_page', ['category_id' => $modalId]);
        $editor->callAction($createAction, ['label' => 'ab'])->assertHasActionErrors(['label']);
        $newId = $field->evaluate($field->getCreateOptionUsing(), ['data' => ['label' => ' New tag ', 'id' => 999]]);
        $this->assertDatabaseHas('categories', ['id' => $newId, 'name' => 'New tag']);
        $this->assertDatabaseMissing('category_page', ['category_id' => $newId]);
        foreach (['TCG\\Voyager\\Http\\Controllers\\VoyagerBaseController', '\\TCG\\Voyager\\Http\\Controllers\\VoyagerBaseController'] as $index => $controller) {
            DB::table('data_types')->where('id', $categoryType)->update(['controller' => $controller]);
            $baseId = app(\App\Services\Admin\BreadTagCreationService::class)->create('pages', $rowId, $id, ['label' => 'Base controller '.$index]);
            $this->assertDatabaseHas('categories', ['id' => $baseId, 'name' => 'Base controller '.$index]);
            $this->assertDatabaseMissing('category_page', ['category_id' => $baseId]);
        }
        DB::table('data_types')->where('id', $categoryType)->update(['controller' => 'App\\Http\\Controllers\\CustomTagController']);
        try {
            $field->evaluate($field->getCreateOptionUsing(), ['data' => ['label' => 'Unsupported controller']]);
            $this->fail('Custom tag handler was bypassed');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { $this->assertSame(403, $exception->getStatusCode()); }
        $this->assertDatabaseMissing('categories', ['name' => 'Unsupported controller']);
        DB::table('data_types')->where('id', $categoryType)->update(['controller' => 'TCG\\Voyager\\Http\\Controllers\\VoyagerBaseController']);
        foreach (['', 'ab'] as $invalidLabel) {
            try {
                app(\App\Services\Admin\BreadTagCreationService::class)->create('pages', $rowId, $id, ['label' => $invalidLabel]);
                $this->fail('Invalid label was accepted');
            } catch (\Illuminate\Validation\ValidationException $exception) { $this->assertArrayHasKey('label', $exception->errors()); }
        }
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->set('data.__pivot_'.$rowId, [$newId])->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('category_page', ['page_id' => $id, 'category_id' => $newId]);
        $originalDetails = DB::table('data_rows')->where('id', $rowId)->value('details');
        DB::table('data_rows')->where('id', $rowId)->update(['details' => str_replace('"taggable":"on"', '"taggable":false', $originalDetails)]);
        try {
            $field->evaluate($field->getCreateOptionUsing(), ['data' => ['label' => 'Forbidden']]);
            $this->fail('Stale action was accepted');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { $this->assertSame(403, $exception->getStatusCode()); }
        $this->assertDatabaseMissing('categories', ['name' => 'Forbidden']);
        DB::table('data_rows')->where('id', $rowId)->update(['details' => $originalDetails]);

        Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->call('openEdit', $id)
            ->call('changeLocale', 'ru')->assertSee('Категории')
            ->set('data.__pivot_' . $rowId, [$categoryId])
            ->call('changeLocale', 'en')->assertSet('data.__pivot_' . $rowId, [$categoryId])
            ->call('changeLocale', 'ru')->assertSet('data.__pivot_' . $rowId, [$categoryId])
            ->call('save')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('category_page', ['page_id' => $id, 'category_id' => $categoryId]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->call('changeLocale', 'ru')->set('data.meta_description', 'Must roll back')
            ->set('data.__pivot_' . $rowId, [$otherCategoryId + 1000])
            ->call('save')->assertHasErrors();
        $this->assertDatabaseHas('category_page', ['page_id' => $id, 'category_id' => $categoryId]);
        $this->assertDatabaseMissing('translations', ['foreign_key' => $id, 'column_name' => 'meta_description', 'value' => 'Must roll back']);
        DB::table('data_rows')->insert(['data_type_id' => $categoryType, 'field' => 'id', 'type' => 'number', 'required' => true, 'details' => '{}']);
        // Non-editable primary key is ignored; required editable extra fields are validated.
        Schema::table('categories', fn (Blueprint $table) => $table->string('code')->nullable());
        $extraRow = DB::table('data_rows')->insertGetId(['data_type_id' => $categoryType, 'field' => 'code', 'type' => 'text', 'required' => true, 'details' => '{}']);
        try {
            app(\App\Services\Admin\BreadTagCreationService::class)->create('pages', $rowId, $id, ['label' => 'Needs code']);
            $this->fail('Required target field was ignored');
        } catch (\Illuminate\Validation\ValidationException $exception) { $this->assertArrayHasKey('label', $exception->errors()); }
        $this->assertDatabaseMissing('categories', ['name' => 'Needs code']);
        DB::table('data_rows')->where('id', $extraRow)->update(['details' => '{"default":"DEFAULT"}']);
        $defaultId = app(\App\Services\Admin\BreadTagCreationService::class)->create('pages', $rowId, $id, ['label' => 'With default']);
        $this->assertDatabaseHas('categories', ['id' => $defaultId, 'code' => 'DEFAULT']);
        $creator = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openCreate');
        $creator->callAction($createAction, ['label' => 'New parent tag'])->assertHasNoActionErrors();
        $createdTag = DB::table('categories')->where('name', 'New parent tag')->value('id');
        $this->assertNotNull($createdTag);
        $this->assertDatabaseMissing('category_page', ['category_id' => $createdTag]);
        $creator->set('data.title', 'New parent')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('category_page', ['page_id' => DB::table('pages')->where('title', 'New parent')->value('id'), 'category_id' => $createdTag]);
        DB::table('permission_role')->where('permission_id', DB::table('permissions')->where('key', 'add_categories')->value('id'))->delete();
        try {
            $field->evaluate($field->getCreateOptionUsing(), ['data' => ['label' => 'No permission']]);
            $this->fail('Revoked permission was ignored');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { $this->assertSame(403, $exception->getStatusCode()); }
        $this->assertDatabaseMissing('categories', ['name' => 'No permission']);
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

    private function recoveryMediaCollection(): array
    {
        Storage::fake('public');
        require_once database_path('migrations/2021_11_19_144609_create_media_table.php');
        (new \CreateMediaTable)->up();
        (require database_path('migrations/2026_09_25_120000_add_generated_conversions_to_media_table.php'))->up();
        Schema::create('canvas_new', function (Blueprint $table) { $table->id(); $table->timestamps(); });
        $this->admin(['browse_admin', 'browse_canvas_new', 'read_canvas_new', 'edit_canvas_new', 'add_canvas_new', 'browse_bread']);
        $typeId = DB::table('data_types')->insertGetId(['name' => 'canvas_new', 'slug' => 'canvas-new', 'model_name' => BreadRecoveryCollectionFixture::class]);
        DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => 'tab_examples', 'type' => 'adv_media_files', 'display_name' => 'Examples', 'details' => '{}']);
        $record = BreadRecoveryCollectionFixture::create();
        $old = $record->addMedia(UploadedFile::fake()->image('old.jpg', 40, 20))->withCustomProperties(['alt' => 'Original ALT', 'image_alt_ru' => 'Описание'])->toMediaCollection('tab_examples', 'public');
        $record->unsetRelation('media');
        $other = $record->addMedia(UploadedFile::fake()->image('other.jpg', 40, 20))->toMediaCollection('tab_examples', 'public');
        $disk = Storage::disk('public');

        return [$record, $old, $other, collect($disk->allFiles())->mapWithKeys(fn ($path) => [$path => $disk->get($path)])->all()];
    }

    public function test_media_bulk_delete_rolls_back_second_sql_failure_and_preserves_drafts(): void
    {
        [$record, $old, $other, $before] = $this->recoveryMediaCollection();
        DB::statement("CREATE TRIGGER reject_second_delete BEFORE DELETE ON media WHEN OLD.id = {$other->id} BEGIN SELECT RAISE(ABORT, 'Fixture second deletion failure'); END");
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'canvas-new', 'record' => $record->id])
            ->set('selectedMedia.tab_examples', [(string) $old->id, (string) $other->id])
            ->set('mediaProperties.'.$old->id.'.alt', 'Draft ALT')
            ->set('mediaReplacements.'.$old->id, UploadedFile::fake()->image('pending.jpg', 40, 20));
        $draftSource = $editor->instance()->mediaReplacements[$old->id]->getRealPath();
        try { $editor->call('deleteSelectedMedia', 'tab_examples'); $this->fail('Expected deletion rejection'); }
        catch (\Illuminate\Database\QueryException $error) { $this->assertStringContainsString('Fixture second deletion failure', $error->getMessage()); }
        $this->assertSame([$old->id, $other->id], DB::table('media')->orderBy('id')->pluck('id')->all());
        $disk = Storage::disk('public');
        $this->assertSame(array_keys($before), $disk->allFiles());
        foreach ($before as $path => $bytes) { $this->assertSame($bytes, $disk->get($path)); }
        $this->assertSame([(string) $old->id, (string) $other->id], $editor->get('selectedMedia.tab_examples'));
        $this->assertSame('Draft ALT', $editor->get('mediaProperties.'.$old->id.'.alt'));
        $this->assertFileExists($draftSource);
        DB::statement('DROP TRIGGER reject_second_delete');
        $editor->call('deleteSelectedMedia', 'tab_examples')->assertHasNoErrors();
        $this->assertDatabaseCount('media', 0);
        $this->assertSame([], $disk->allFiles());
        $this->assertSame([], $editor->get('selectedMedia.tab_examples'));
        $this->assertArrayNotHasKey($old->id, $editor->get('mediaProperties'));
        $this->assertArrayNotHasKey($old->id, $editor->get('mediaReplacements'));
    }

    public function test_single_media_deletions_handle_sql_failure_and_preserve_other_collections(): void
    {
        [$record, $old, $other, $before] = $this->recoveryMediaCollection();
        DB::statement("CREATE TRIGGER reject_single_delete BEFORE DELETE ON media WHEN OLD.id = {$old->id} BEGIN SELECT RAISE(ABORT, 'Fixture single deletion failure'); END");
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'canvas-new', 'record' => $record->id])
            ->set('selectedMedia.tab_examples', [(string) $old->id, (string) $other->id]);
        try { $editor->call('deleteMedia', 'tab_examples', $old->id); $this->fail('Expected deletion rejection'); }
        catch (\Illuminate\Database\QueryException $error) { $this->assertStringContainsString('Fixture single deletion failure', $error->getMessage()); }
        $this->assertDatabaseCount('media', 2);
        $disk = Storage::disk('public');
        foreach ($before as $path => $bytes) { $this->assertSame($bytes, $disk->get($path)); }
        DB::statement('DROP TRIGGER reject_single_delete');
        DB::beginTransaction();
        try {
            $editor->instance()->deleteMedia('tab_examples', $old->id);
            $this->assertDatabaseMissing('media', ['id' => $old->id]);
            $disk->assertExists($old->getPathRelativeToRoot());
            $disk->assertExists($old->getPathRelativeToRoot('small'));
            $this->assertSame([(string) $old->id, (string) $other->id], $editor->instance()->selectedMedia['tab_examples']);
        } finally { DB::rollBack(); }
        $this->assertDatabaseHas('media', ['id' => $old->id]);
        $editor->call('deleteMedia', 'tab_examples', $old->id)->assertHasNoErrors();
        $this->assertSame([(string) $other->id], $editor->get('selectedMedia.tab_examples'));
        $disk->assertMissing($old->getPathRelativeToRoot());
        $disk->assertMissing($old->getPathRelativeToRoot('small'));
        $disk->assertExists($other->getPathRelativeToRoot());
        $typeId = DB::table('data_types')->where('slug', 'canvas-new')->value('id');
        DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => 'cover', 'type' => 'adv_image', 'display_name' => 'Cover', 'details' => '{}']);
        $cover = $record->fresh()->addMedia(UploadedFile::fake()->image('cover.jpg', 40, 20))
            ->withCustomProperties(['title' => 'Original title', 'alt' => 'Original alt'])->toMediaCollection('cover', 'public');
        $single = Livewire::test(VoyagerBreadEdit::class, ['type' => 'canvas-new', 'record' => $record->id]);
        DB::statement("CREATE TRIGGER reject_cover_delete BEFORE DELETE ON media WHEN OLD.id = {$cover->id} BEGIN SELECT RAISE(ABORT, 'Fixture cover deletion failure'); END");
        $coverBytes = $disk->get($cover->getPathRelativeToRoot());
        try { $single->call('deleteSingleImage', 'cover', $cover->id); $this->fail('Expected cover rejection'); }
        catch (\Illuminate\Database\QueryException $error) { $this->assertStringContainsString('Fixture cover deletion failure', $error->getMessage()); }
        $this->assertDatabaseHas('media', ['id' => $cover->id]);
        $this->assertSame($coverBytes, $disk->get($cover->getPathRelativeToRoot()));
        $this->assertSame('Original alt', $single->get('singleImageProperties.cover.alt'));
        $disk->assertExists($cover->getPathRelativeToRoot('small'));
        DB::statement('DROP TRIGGER reject_cover_delete');
        DB::beginTransaction();
        try {
            $single->instance()->deleteSingleImage('cover', $cover->id);
            $this->assertDatabaseMissing('media', ['id' => $cover->id]);
            $disk->assertExists($cover->getPathRelativeToRoot());
            $disk->assertExists($cover->getPathRelativeToRoot('small'));
            $this->assertSame('Original alt', $single->instance()->singleImageProperties['cover']['alt']);
        } finally { DB::rollBack(); }
        $this->assertDatabaseHas('media', ['id' => $cover->id]);
        $single->call('deleteSingleImage', 'cover', $cover->id)->assertHasNoErrors();
        $this->assertDatabaseMissing('media', ['id' => $cover->id]);
        $disk->assertMissing($cover->getPathRelativeToRoot());
        $disk->assertMissing($cover->getPathRelativeToRoot('small'));
        $disk->assertExists($other->getPathRelativeToRoot());
        $this->assertSame(['title' => null, 'alt' => null], $single->get('singleImageProperties.cover'));
    }

    public function test_media_collection_batch_failure_cleans_partial_media_and_retries_sources(): void
    {
        [$record, $old, $other, $before] = $this->recoveryMediaCollection();
        $this->app->instance(\Spatie\MediaLibrary\Conversions\FileManipulator::class, new class extends \Spatie\MediaLibrary\Conversions\FileManipulator {
            public function createDerivedFiles(\Spatie\MediaLibrary\MediaCollections\Models\Media $media, array $onlyConversionNames = [], bool $onlyMissing = false, bool $withResponsiveImages = false, bool $queueAll = false): void
            {
                parent::createDerivedFiles($media, $onlyConversionNames, $onlyMissing, $withResponsiveImages, $queueAll);
                if ($media->file_name === 'failed.jpg') {
                    Storage::disk($media->disk)->put($media->id.'/conversions/incomplete.tmp', 'Partial');
                    throw new \RuntimeException('Fixture batch conversion failure');
                }
            }
        });
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'canvas-new', 'record' => $record->id])
            ->set('mediaUploads.tab_examples', [UploadedFile::fake()->image('first.jpg', 40, 20), UploadedFile::fake()->image('failed.jpg', 40, 20)]);
        $sources = array_map(fn ($file) => $file->getRealPath(), $editor->instance()->mediaUploads['tab_examples']);
        try { $editor->call('uploadMedia', 'tab_examples'); $this->fail('Expected batch rejection'); }
        catch (\RuntimeException $error) { $this->assertSame('Fixture batch conversion failure', $error->getMessage()); }
        $this->assertSame([$old->id, $other->id], DB::table('media')->orderBy('id')->pluck('id')->all());
        $disk = Storage::disk('public');
        $this->assertSame(array_keys($before), $disk->allFiles());
        foreach ($before as $path => $bytes) { $this->assertSame($bytes, $disk->get($path)); }
        foreach ($sources as $path) { $this->assertFileExists($path); }
        $this->app->forgetInstance(\Spatie\MediaLibrary\Conversions\FileManipulator::class);
        $editor->call('uploadMedia', 'tab_examples')->assertHasNoErrors();
        $this->assertDatabaseCount('media', 2); // Configured onlyKeepLatest(2) retained.
        $this->assertSame(['first.jpg', 'failed.jpg'], DB::table('media')->orderBy('id')->pluck('file_name')->all());
        foreach (array_keys($before) as $path) { $disk->assertMissing($path); }
        $this->assertArrayNotHasKey('tab_examples', $editor->get('mediaUploads'));
    }

    public function test_media_collection_replacement_sql_failure_keeps_old_selection_properties_and_order(): void
    {
        [$record, $old, $other, $before] = $this->recoveryMediaCollection();
        DB::table('media')->where('id', $old->id)->update(['order_column' => 7]);
        DB::table('media')->where('id', $other->id)->update(['order_column' => 8]);
        DB::statement("CREATE TRIGGER reject_media_order BEFORE UPDATE ON media WHEN NEW.file_name = 'replacement.jpg' AND NEW.order_column != OLD.order_column BEGIN SELECT RAISE(ABORT, 'Fixture order SQL failure'); END");
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'canvas-new', 'record' => $record->id])
            ->set('selectedMedia.tab_examples', [(string) $old->id, (string) $other->id])
            ->set('mediaReplacements.'.$old->id, UploadedFile::fake()->image('replacement.jpg', 40, 20));
        $source = $editor->instance()->mediaReplacements[$old->id]->getRealPath();
        try { $editor->call('replaceMedia', 'tab_examples', $old->id); $this->fail('Expected order rejection'); }
        catch (\Illuminate\Database\QueryException $error) { $this->assertStringContainsString('Fixture order SQL failure', $error->getMessage()); }
        $this->assertDatabaseCount('media', 2);
        $this->assertDatabaseHas('media', ['id' => $old->id, 'order_column' => 7]);
        $this->assertSame('Original ALT', $old->fresh()->getCustomProperty('alt'));
        $disk = Storage::disk('public');
        $this->assertSame(array_keys($before), $disk->allFiles());
        foreach ($before as $path => $bytes) { $this->assertSame($bytes, $disk->get($path)); }
        $this->assertSame([(string) $old->id, (string) $other->id], $editor->get('selectedMedia.tab_examples'));
        $this->assertFileExists($source);
        DB::statement('DROP TRIGGER reject_media_order');
        $editor->call('replaceMedia', 'tab_examples', $old->id)->assertHasNoErrors();
        $this->assertDatabaseCount('media', 2);
        $new = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('file_name', 'replacement.jpg')->firstOrFail();
        $this->assertSame(7, $new->order_column);
        $this->assertSame('Original ALT', $new->getCustomProperty('alt'));
        $this->assertSame('Описание', $new->getCustomProperty('image_alt_ru'));
        $this->assertSame([(string) $new->id, (string) $other->id], $editor->get('selectedMedia.tab_examples'));
        $disk->assertMissing($old->getPathRelativeToRoot());
        $disk->assertMissing($old->getPathRelativeToRoot('small'));
        $disk->assertExists($new->getPathRelativeToRoot());
        $disk->assertExists($new->getPathRelativeToRoot('small'));
        $disk->assertExists($other->getPathRelativeToRoot());
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

    private function createMenuManagementTables(): void
    {
        Schema::create('menus', function (Blueprint $table): void {
            $table->id(); $table->string('name')->unique(); $table->timestamps();
        });
        Schema::create('menu_items', function (Blueprint $table): void {
            $table->id(); $table->foreignId('menu_id'); $table->boolean('status')->default(true);
            $table->string('title'); $table->text('url')->nullable(); $table->string('target')->nullable();
            $table->string('icon_class')->nullable(); $table->string('color')->nullable();
            $table->foreignId('parent_id')->nullable(); $table->integer('order');
            $table->string('route')->nullable(); $table->text('parameters')->nullable(); $table->timestamps();
        });
    }

    public function test_menu_tree_reorder_matches_voyager_sibling_order_and_rejects_incomplete_or_foreign_trees(): void
    {
        $this->createMenuManagementTables();
        $this->admin(['browse_admin', 'browse_menus', 'edit_menus']);
        $menu = DB::table('menus')->insertGetId(['name' => 'admin']);
        $other = DB::table('menus')->insertGetId(['name' => 'other']);
        $first = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'First', 'order' => 1, 'url' => '/first']);
        $child = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'Child', 'parent_id' => $first, 'order' => 1]);
        $second = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'Second', 'order' => 2]);
        $foreign = DB::table('menu_items')->insertGetId(['menu_id' => $other, 'title' => 'Foreign', 'order' => 1]);
        DB::table('translations')->insert(['table_name' => 'menu_items', 'column_name' => 'title', 'foreign_key' => $child, 'locale' => 'ru', 'value' => 'Child RU']);
        $component = Livewire::test(VoyagerMenuItems::class);
        $this->assertSame($child, $component->instance()->menuTree()[0]['children'][0]['item']->id);
        \Illuminate\Support\Facades\Cache::put('voyager_menu_admin', ['old'], 60);
        $tree = [['id' => $second, 'children' => [['id' => $child]]], ['id' => $first]];
        $component->call('reorderItems', $menu, $tree)->assertHasNoErrors()->assertReturned(true);
        $this->assertDatabaseHas('menu_items', ['id' => $second, 'parent_id' => null, 'order' => 1]);
        $this->assertDatabaseHas('menu_items', ['id' => $first, 'parent_id' => null, 'order' => 2, 'url' => '/first']);
        $this->assertDatabaseHas('menu_items', ['id' => $child, 'parent_id' => $second, 'order' => 1]);
        $this->assertDatabaseHas('translations', ['foreign_key' => $child, 'value' => 'Child RU']);
        $this->assertFalse(\Illuminate\Support\Facades\Cache::has('voyager_menu_admin'));
        $before = DB::table('menu_items')->orderBy('id')->get()->toJson();
        $deep = []; for ($id = 6; $id >= 1; $id--) { $deep = [['id' => $id, 'children' => $deep]]; }
        foreach ([[], [['id' => $first], ['id' => $child]],
            [['id' => $first], ['id' => $child], ['id' => $foreign]],
            [['id' => $first, 'children' => [['id' => $first]]], ['id' => $child], ['id' => $second]],
            [['id' => $first, 'children' => 'bad']], $deep] as $invalid) {
            $component->call('reorderItems', $menu, $invalid)->assertHasErrors('menuOrder');
            $this->assertSame($before, DB::table('menu_items')->orderBy('id')->get()->toJson());
        }
        Livewire::test(VoyagerMenuItems::class)->call('reorderItems', $other, [])->assertStatus(422);
    }

    public function test_menu_tree_accepts_five_levels_and_keeps_legacy_orphans_and_cycles_visible(): void
    {
        $this->createMenuManagementTables();
        $this->admin(['browse_admin', 'browse_menus', 'edit_menus']);
        $menu = DB::table('menus')->insertGetId(['name' => 'admin']);
        $ids = [];
        for ($depth = 1; $depth <= 5; $depth++) {
            $ids[] = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'Level '.$depth,
                'parent_id' => $ids === [] ? null : end($ids), 'order' => 1]);
        }
        $tree = [];
        foreach (array_reverse($ids) as $id) { $tree = [['id' => $id, 'children' => $tree]]; }
        $component = Livewire::test(VoyagerMenuItems::class)->call('reorderItems', $menu, $tree)->assertHasNoErrors()->assertReturned(true);
        DB::table('menu_items')->where('id', $ids[0])->update(['parent_id' => $ids[1]]);
        DB::table('menu_items')->where('id', $ids[2])->update(['parent_id' => 99999]);
        $before = DB::table('menu_items')->get()->toJson();
        $visible = [];
        $walk = function (array $nodes) use (&$walk, &$visible): void {
            foreach ($nodes as $node) { $visible[] = $node['item']->id; $walk($node['children']); }
        };
        $walk($component->instance()->menuTree());
        sort($visible);
        $this->assertSame($ids, $visible);
        $this->assertSame($before, DB::table('menu_items')->get()->toJson());
    }

    public function test_menu_tree_branch_keeps_all_descendants_after_move_edit_and_translation(): void
    {
        $this->createMenuManagementTables();
        $this->admin(['browse_admin', 'browse_menus', 'edit_menus']);
        $menu = DB::table('menus')->insertGetId(['name' => 'admin']);
        $ids = [];
        for ($depth = 1; $depth <= 5; $depth++) {
            $ids[] = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'Level '.$depth,
                'parent_id' => $ids === [] ? null : end($ids), 'order' => 1, 'url' => '/level-'.$depth, 'target' => '_self']);
        }
        $other = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'Other root', 'order' => 2]);
        $branch = [];
        foreach (array_reverse(array_slice($ids, 1)) as $id) { $branch = [['id' => $id, 'children' => $branch]]; }
        Livewire::test(VoyagerMenuItems::class)->call('reorderItems', $menu,
            [['id' => $other, 'children' => $branch], ['id' => $ids[0]]])->assertHasNoErrors()->assertReturned(true);
        Livewire::test(VoyagerMenuItems::class)->call('openEdit', $ids[1])->assertSet('data.parent_id', $other)
            ->set('data.title', 'Edited branch')->call('changeLocale', 'ru')->set('data.title', 'Ветка')
            ->call('save')->assertHasNoErrors();
        $reopened = Livewire::test(VoyagerMenuItems::class)->call('openEdit', $ids[1])
            ->assertSet('data.parent_id', $other)->assertSet('data.title', 'Edited branch');
        $this->assertDatabaseHas('menu_items', ['id' => $ids[1], 'parent_id' => $other, 'order' => 1, 'url' => '/level-2']);
        for ($index = 2; $index < 5; $index++) {
            $this->assertDatabaseHas('menu_items', ['id' => $ids[$index], 'parent_id' => $ids[$index - 1], 'order' => 1]);
        }
        $this->assertDatabaseHas('translations', ['table_name' => 'menu_items', 'foreign_key' => $ids[1], 'locale' => 'ru', 'value' => 'Ветка']);
        $nodes = $reopened->instance()->menuTree()[0]['children'];
        foreach (array_slice($ids, 1) as $id) {
            $this->assertCount(1, $nodes);
            $this->assertSame($id, $nodes[0]['item']->id);
            $nodes = $nodes[0]['children'];
        }
        $this->assertSame([], $nodes);
    }

    public function test_menu_tree_sorting_preserves_missing_parent_until_explicit_reparent(): void
    {
        $this->createMenuManagementTables();
        $this->admin(['browse_admin', 'browse_menus', 'edit_menus']);
        $menu = DB::table('menus')->insertGetId(['name' => 'admin']);
        $root = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'Root', 'order' => 1]);
        $orphan = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'Orphan', 'parent_id' => 99999, 'order' => 2]);
        $child = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'Child', 'parent_id' => $orphan, 'order' => 1]);
        $component = Livewire::test(VoyagerMenuItems::class)->assertSee('Родитель #99999 отсутствует');
        $component->call('reorderItems', $menu, [['id' => $orphan, 'children' => [['id' => $child]]], ['id' => $root]])
            ->assertHasNoErrors()->assertReturned(true);
        $this->assertDatabaseHas('menu_items', ['id' => $orphan, 'parent_id' => 99999]);
        $this->assertDatabaseHas('menu_items', ['id' => $child, 'parent_id' => $orphan]);
        Livewire::test(VoyagerMenuItems::class)->call('openEdit', $orphan)->assertSet('data.parent_id', 99999)
            ->assertSee('Отсутствующий родитель #99999 (текущая связь)')->set('data.title', 'Renamed orphan')
            ->call('changeLocale', 'ru')->set('data.title', 'Перевод')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('menu_items', ['id' => $orphan, 'parent_id' => 99999, 'title' => 'Renamed orphan']);
        // Repeating an unrelated sort still must not flatten the recovery branch.
        $component->call('reorderItems', $menu, [['id' => $root], ['id' => $orphan, 'children' => [['id' => $child]]]])
            ->assertHasNoErrors()->assertReturned(true);
        $this->assertDatabaseHas('menu_items', ['id' => $orphan, 'parent_id' => 99999]);
        $component->call('reorderItems', $menu, [['id' => $root, 'children' => [['id' => $orphan, 'children' => [['id' => $child]]]]]])
            ->assertHasNoErrors()->assertReturned(true);
        $this->assertDatabaseHas('menu_items', ['id' => $orphan, 'parent_id' => $root]);
        $this->assertDatabaseHas('menu_items', ['id' => $child, 'parent_id' => $orphan]);
        // Making a repaired branch a genuine root remains possible.
        $component->call('reorderItems', $menu, [['id' => $orphan, 'children' => [['id' => $child]]], ['id' => $root]])
            ->assertHasNoErrors()->assertReturned(true);
        $this->assertDatabaseHas('menu_items', ['id' => $orphan, 'parent_id' => null]);
    }

    public function test_menu_tree_reorder_rolls_back_all_changes_when_a_later_update_fails(): void
    {
        $this->createMenuManagementTables();
        $this->admin(['browse_admin', 'browse_menus', 'edit_menus']);
        $menu = DB::table('menus')->insertGetId(['name' => 'admin']);
        $first = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'First', 'order' => 1]);
        $second = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'Second', 'order' => 2]);
        \Illuminate\Support\Facades\Cache::put('voyager_menu_admin', ['cached'], 60);
        DB::statement("CREATE TRIGGER reject_menu_order BEFORE UPDATE ON menu_items WHEN OLD.id = $first BEGIN SELECT RAISE(ABORT, 'Synthetic order failure'); END");
        $component = Livewire::test(VoyagerMenuItems::class);
        try {
            $component->instance()->reorderItems($menu, [['id' => $second], ['id' => $first]]);
            $this->fail('The first update must be rolled back.');
        } catch (\Illuminate\Database\QueryException $error) {
            $this->assertStringContainsString('Synthetic order failure', $error->getMessage());
        }
        $this->assertDatabaseHas('menu_items', ['id' => $first, 'order' => 1]);
        $this->assertDatabaseHas('menu_items', ['id' => $second, 'order' => 2]);
        $this->assertTrue(\Illuminate\Support\Facades\Cache::has('voyager_menu_admin'));
    }

    public function test_menu_creation_and_rename_support_empty_list_validate_names_and_clear_original_cache(): void
    {
        $this->createMenuManagementTables();
        $this->admin(['browse_admin', 'browse_menus', 'add_menus', 'edit_menus']);
        $component = Livewire::test(VoyagerMenuItems::class)->assertSet('menuId', 0)
            ->assertSee('Меню ещё не созданы.')->assertDontSee('Добавить пункт')
            ->call('openCreateMenu')->set('menuName', '   ')->call('saveMenuName')->assertHasErrors('menuName')
            ->set('menuName', str_repeat('a', 256))->call('saveMenuName')->assertHasErrors('menuName')
            ->set('menuName', ' footer ')->call('saveMenuName')->assertHasNoErrors()->assertSet('menuEditing', false);
        $id = DB::table('menus')->where('name', 'footer')->value('id');
        $this->assertNotNull($id);
        $component->assertSet('menuId', $id)->assertSee('Добавить пункт')
            ->call('openCreateMenu')->set('menuName', 'footer')->call('saveMenuName')->assertHasErrors('menuName')
            ->call('cancelMenu')->assertSet('menuEditing', false);
        $this->assertSame(1, DB::table('menus')->count());
        $item = DB::table('menu_items')->insertGetId(['menu_id' => $id, 'title' => 'Existing item', 'order' => 1]);
        \Illuminate\Support\Facades\Cache::put('voyager_menu_footer', ['old'], 60);
        \Illuminate\Support\Facades\Cache::put('voyager_menu_footer-new', ['stale'], 60);
        $component->call('openRenameMenu')->assertSet('menuName', 'footer')
            ->set('menuName', 'footer-new')->call('saveMenuName')->assertHasNoErrors();
        $this->assertDatabaseHas('menus', ['id' => $id, 'name' => 'footer-new']);
        $this->assertDatabaseHas('menu_items', ['id' => $item, 'menu_id' => $id, 'title' => 'Existing item']);
        $this->assertFalse(\Illuminate\Support\Facades\Cache::has('voyager_menu_footer'));
        $this->assertFalse(\Illuminate\Support\Facades\Cache::has('voyager_menu_footer-new'));
    }

    public function test_menu_deletion_removes_its_tree_and_translations_without_touching_other_menus(): void
    {
        $this->createMenuManagementTables();
        $this->admin(['browse_admin', 'browse_menus', 'delete_menus', 'edit_menus']);
        $id = DB::table('menus')->insertGetId(['name' => 'admin']);
        $other = DB::table('menus')->insertGetId(['name' => 'footer']);
        $parent = DB::table('menu_items')->insertGetId(['menu_id' => $id, 'title' => 'Parent', 'order' => 1]);
        $child = DB::table('menu_items')->insertGetId(['menu_id' => $id, 'parent_id' => $parent, 'title' => 'Child', 'order' => 1]);
        $unrelated = DB::table('menu_items')->insertGetId(['menu_id' => $other, 'title' => 'Other', 'order' => 1]);
        foreach ([$parent, $child, $unrelated] as $item) {
            DB::table('translations')->insert(['table_name' => 'menu_items', 'column_name' => 'title', 'foreign_key' => $item, 'locale' => 'ru', 'value' => 'Translation']);
        }
        DB::table('translations')->insert(['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => $parent, 'locale' => 'ru', 'value' => 'Page']);
        \Illuminate\Support\Facades\Cache::put('voyager_menu_admin', ['cached'], 60);
        \Illuminate\Support\Facades\Cache::put('voyager_menu_footer', ['untouched'], 60);
        $component = Livewire::test(VoyagerMenuItems::class)->call('openRenameMenu')
            ->call('deleteMenu', $id)->assertHasNoErrors()->assertSet('menuId', $other)
            ->assertSet('menuEditing', false)->assertSet('itemId', null);
        $this->assertDatabaseMissing('menus', ['id' => $id]);
        $this->assertDatabaseMissing('menu_items', ['id' => $parent]);
        $this->assertDatabaseMissing('menu_items', ['id' => $child]);
        $this->assertDatabaseHas('menu_items', ['id' => $unrelated, 'menu_id' => $other]);
        $this->assertSame(2, DB::table('translations')->count());
        $this->assertFalse(\Illuminate\Support\Facades\Cache::has('voyager_menu_admin'));
        $this->assertTrue(\Illuminate\Support\Facades\Cache::has('voyager_menu_footer'));
        $component->call('deleteMenu', $other)->assertSet('menuId', 0)->assertSee('Меню ещё не созданы.')->assertDontSee('Удалить меню');
        $this->assertSame(0, DB::table('menu_items')->count());
        $this->assertSame(1, DB::table('translations')->count());
        $this->assertDatabaseHas('translations', ['table_name' => 'pages', 'value' => 'Page']);
    }

    public function test_menu_deletion_rolls_back_items_translations_and_keeps_cache_if_menu_delete_fails(): void
    {
        $this->createMenuManagementTables();
        $this->admin(['browse_admin', 'browse_menus', 'delete_menus']);
        $id = DB::table('menus')->insertGetId(['name' => 'admin']);
        $item = DB::table('menu_items')->insertGetId(['menu_id' => $id, 'title' => 'Item', 'order' => 1]);
        DB::table('translations')->insert(['table_name' => 'menu_items', 'column_name' => 'title', 'foreign_key' => $item, 'locale' => 'ru', 'value' => 'Translation']);
        \Illuminate\Support\Facades\Cache::put('voyager_menu_admin', ['cached'], 60);
        DB::statement("CREATE TRIGGER reject_menu_delete BEFORE DELETE ON menus BEGIN SELECT RAISE(ABORT, 'Synthetic failure'); END");
        $component = Livewire::test(VoyagerMenuItems::class);
        try {
            $component->instance()->deleteMenu($id);
            $this->fail('A failure must roll back the full deletion.');
        } catch (\Illuminate\Database\QueryException $error) {
            $this->assertStringContainsString('Synthetic failure', $error->getMessage());
        }
        $this->assertDatabaseHas('menus', ['id' => $id]);
        $this->assertDatabaseHas('menu_items', ['id' => $item]);
        $this->assertDatabaseHas('translations', ['table_name' => 'menu_items', 'foreign_key' => $item]);
        $this->assertTrue(\Illuminate\Support\Facades\Cache::has('voyager_menu_admin'));
        $this->assertSame($id, $component->get('menuId'));
    }

    public function test_menu_management_actions_require_their_own_permissions(): void
    {
        $this->createMenuManagementTables();
        DB::table('menus')->insert(['name' => 'admin']);
        $this->admin(['browse_admin', 'browse_menus']);
        Livewire::test(VoyagerMenuItems::class)->assertDontSee('Создать меню')->assertDontSee('Переименовать меню')
            ->call('openCreateMenu')->assertForbidden();
        Livewire::test(VoyagerMenuItems::class)->call('openRenameMenu')->assertForbidden();
        Livewire::test(VoyagerMenuItems::class)->call('reorderItems', 1, [])->assertForbidden();
        Livewire::test(VoyagerMenuItems::class)->assertDontSee('Удалить меню')->call('deleteMenu', 1)->assertForbidden();
        Livewire::test(VoyagerMenuItems::class)->set('menuEditing', true)->set('menuName', 'Forged')
            ->call('saveMenuName')->assertForbidden();
        $this->assertSame(['admin'], DB::table('menus')->pluck('name')->all());
    }

    public function test_menu_item_fields_match_voyager_link_modes_color_and_root_order(): void
    {
        $this->createMenuManagementTables();
        $this->admin(['browse_admin', 'browse_menus', 'add_menus', 'edit_menus', 'delete_menus']);
        $menu = DB::table('menus')->insertGetId(['name' => 'admin']);
        $other = DB::table('menus')->insertGetId(['name' => 'footer']);
        DB::table('menu_items')->insert(['menu_id' => $other, 'title' => 'Original root', 'order' => 12]);
        $component = Livewire::test(VoyagerMenuItems::class)->call('openCreate')
            ->assertSet('data.order', 13)->assertSet('data.link_type', 'url')->assertSet('data.color', '#000000');
        $component->set('data.title', 'New URL')->set('data.url', '/about?type=2')->set('data.color', '#12aBcF')
            ->set('data.target', '_blank')->set('data.icon_class', 'voyager-photo')->call('save')->assertHasNoErrors();
        $id = DB::table('menu_items')->where('title', 'New URL')->value('id');
        $this->assertDatabaseHas('menu_items', ['id' => $id, 'menu_id' => $menu, 'url' => '/about?type=2', 'route' => null,
            'parameters' => '', 'color' => '#12aBcF', 'target' => '_blank', 'icon_class' => 'voyager-photo', 'order' => 13]);
        Cache::put('voyager_menu_admin', 'Stale');
        Livewire::test(VoyagerMenuItems::class)->call('openEdit', $id)->set('data.link_type', 'route')
            ->set('data.route', 'catalog.show')->set('data.parameters', '{"id":974,"type":2}')
            ->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('menu_items', ['id' => $id, 'url' => '', 'route' => 'catalog.show', 'parameters' => '{"id":974,"type":2}', 'color' => '#12aBcF']);
        $this->assertFalse(Cache::has('voyager_menu_admin'));
        $component = Livewire::test(VoyagerMenuItems::class)->call('openEdit', $id)->assertSet('data.link_type', 'route');
        $this->assertInstanceOf(\Filament\Forms\Components\Textarea::class, $component->instance()->form->getFlatFields()['parameters']);
        $component->set('data.link_type', 'url')->set('data.url', '#section')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('menu_items', ['id' => $id, 'url' => '#section', 'route' => null, 'parameters' => '']);
        Cache::put('voyager_menu_admin', 'Stale');
        Livewire::test(VoyagerMenuItems::class)->call('openEdit', $id)->call('changeLocale', 'ru')->set('data.title', 'Перевод')->call('save')->assertHasNoErrors();
        $this->assertFalse(Cache::has('voyager_menu_admin'));
        $this->assertDatabaseHas('menu_items', ['id' => $id, 'url' => '#section', 'color' => '#12aBcF', 'title' => 'New URL']);
        Cache::put('voyager_menu_admin', 'Stale');
        Livewire::test(VoyagerMenuItems::class)->call('deleteItem', $id)->assertHasNoErrors();
        $this->assertFalse(Cache::has('voyager_menu_admin'));
    }

    public function test_menu_item_rejects_invalid_color_parameters_mode_target_and_foreign_item(): void
    {
        $this->createMenuManagementTables();
        $this->admin(['browse_admin', 'browse_menus', 'edit_menus']);
        $menu = DB::table('menus')->insertGetId(['name' => 'admin']);
        $other = DB::table('menus')->insertGetId(['name' => 'footer']);
        $id = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'Original', 'order' => 1, 'route' => 'catalog.show', 'url' => '/legacy', 'parameters' => '{"id":1}', 'color' => '#123456']);
        $foreign = DB::table('menu_items')->insertGetId(['menu_id' => $other, 'title' => 'Foreign', 'order' => 1]);
        foreach (['color' => 'red;display:none', 'parameters' => '{broken', 'link_type' => 'unsafe', 'target' => '_top'] as $field => $value) {
            Livewire::test(VoyagerMenuItems::class)->call('openEdit', $id)->set('data.'.$field, $value)->call('save')->assertHasErrors('data.'.$field);
        }
        $this->assertDatabaseHas('menu_items', ['id' => $id, 'url' => '/legacy', 'color' => '#123456', 'parameters' => '{"id":1}']);
        $component = Livewire::test(VoyagerMenuItems::class);
        try {
            $component->instance()->openEdit($foreign);
            $this->fail('Foreign menu item must be rejected.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) { $this->assertSame(404, $error->getStatusCode()); }
        Livewire::test(VoyagerMenuItems::class)->call('openEdit', $id)->set('data.link_type', 'url')
            ->set('data.parameters', '{ignored')->set('data.url', '/new')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('menu_items', ['id' => $id, 'url' => '/new', 'route' => null, 'parameters' => '']);
    }

    public function test_menu_item_editor_schema_cannot_be_forged_without_action_permission(): void
    {
        $this->createMenuManagementTables();
        $menu = DB::table('menus')->insertGetId(['name' => 'admin']);
        $id = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'Original', 'order' => 1]);
        $this->admin(['browse_admin', 'browse_menus']);
        Livewire::test(VoyagerMenuItems::class)->call('openCreate')->assertForbidden();
        Livewire::test(VoyagerMenuItems::class)->call('openEdit', $id)->assertForbidden();
        $component = Livewire::test(VoyagerMenuItems::class)->set('editing', true)->set('itemId', $id);
        $this->assertSame([], $component->instance()->form->getFlatFields());
        $component->call('save')->assertForbidden();
        $this->assertDatabaseHas('menu_items', ['id' => $id, 'title' => 'Original']);
    }

    public function test_menu_item_translation_drafts_keep_common_fields_and_create_all_languages_together(): void
    {
        $this->createMenuManagementTables();
        $this->admin(['browse_admin', 'browse_menus', 'add_menus', 'edit_menus']);
        DB::table('menus')->insert(['name' => 'admin']);
        $component = Livewire::test(VoyagerMenuItems::class)->call('openCreate')->set('data.title', 'English draft')
            ->set('data.link_type', 'route')->set('data.route', 'catalog.show')->set('data.parameters', '{"type":2}')
            ->set('data.color', '#123456')->set('data.icon_class', 'voyager-photo')->set('data.target', '_blank')
            ->call('changeLocale', 'ru')->assertSet('data.route', 'catalog.show')->assertSet('data.color', '#123456')
            ->set('data.title', 'Русский черновик')->call('changeLocale', 'lv')->set('data.title', 'Latviski')
            ->call('changeLocale', 'en')->assertSet('data.title', 'English draft')
            ->call('changeLocale', 'ru')->assertSet('data.title', 'Русский черновик');
        $fields = $component->instance()->form->getFlatFields();
        $this->assertSame('Заголовок RU', $fields['title']->getLabel());
        $this->assertFalse($fields['title']->isRequired());
        foreach (['route', 'parameters', 'color', 'icon_class', 'target', 'parent_id', 'order', 'status'] as $field) {
            $this->assertArrayHasKey($field, $fields);
        }
        $component->call('save')->assertHasNoErrors();
        $id = DB::table('menu_items')->value('id');
        $this->assertDatabaseHas('menu_items', ['id' => $id, 'title' => 'English draft', 'route' => 'catalog.show', 'parameters' => '{"type":2}',
            'url' => '', 'color' => '#123456', 'icon_class' => 'voyager-photo', 'target' => '_blank']);
        $this->assertDatabaseHas('translations', ['table_name' => 'menu_items', 'foreign_key' => $id, 'column_name' => 'title', 'locale' => 'ru', 'value' => 'Русский черновик']);
        $this->assertDatabaseHas('translations', ['table_name' => 'menu_items', 'foreign_key' => $id, 'locale' => 'lv', 'value' => 'Latviski']);
        $this->assertDatabaseCount('menu_items', 1);
        Livewire::test(VoyagerMenuItems::class)->call('openEdit', $id)->call('changeLocale', 'ru')->assertSet('data.title', 'Русский черновик')
            ->set('data.title', 'Изменённый черновик')->set('data.color', '#abcdef')->call('changeLocale', 'en')
            ->set('data.title', 'Changed EN')->call('changeLocale', 'ru')->assertSet('data.title', 'Изменённый черновик')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('menu_items', ['id' => $id, 'title' => 'Changed EN', 'color' => '#abcdef']);
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'locale' => 'ru', 'value' => 'Изменённый черновик']);
    }

    public function test_menu_item_translation_drafts_validate_hidden_languages_and_cancel_without_writes(): void
    {
        $this->createMenuManagementTables();
        $this->admin(['browse_admin', 'browse_menus', 'add_menus', 'edit_menus']);
        DB::table('menus')->insert(['name' => 'admin']);
        Livewire::test(VoyagerMenuItems::class)->call('openCreate')->call('changeLocale', 'ru')->set('data.title', 'Only RU')
            ->call('save')->assertHasErrors('titleTranslations.en')->assertSee('Заголовок EN');
        Livewire::test(VoyagerMenuItems::class)->call('openCreate')->set('data.title', 'English')->call('changeLocale', 'ru')
            ->set('data.title', str_repeat('a', 256))->call('changeLocale', 'en')->call('save')->assertHasErrors('titleTranslations.ru');
        Livewire::test(VoyagerMenuItems::class)->call('openCreate')->set('data.title', 'English')
            ->set('titleTranslations.zz', 'Forged')->call('save')->assertHasErrors('titleTranslations');
        Livewire::test(VoyagerMenuItems::class)->call('openCreate')->set('data.title', 'Draft')->call('changeLocale', 'ru')->set('data.title', 'Черновик')
            ->call('cancel')->assertSet('titleTranslations', [])->assertSet('data', [])->assertSet('editing', false);
        $this->assertDatabaseCount('menu_items', 0);
        $this->assertDatabaseCount('translations', 0);
    }

    public function test_menu_item_translation_failure_rolls_back_creation_and_can_retry_same_draft(): void
    {
        $this->createMenuManagementTables();
        $this->admin(['browse_admin', 'browse_menus', 'add_menus']);
        DB::table('menus')->insert(['name' => 'admin']);
        $component = Livewire::test(VoyagerMenuItems::class)->call('openCreate')->set('data.title', 'English')
            ->call('changeLocale', 'ru')->set('data.title', 'Русский');
        Cache::put('voyager_menu_admin', 'Retain until commit');
        DB::statement("CREATE TRIGGER reject_menu_translation BEFORE INSERT ON translations BEGIN SELECT RAISE(ABORT, 'Synthetic translation failure'); END");
        try {
            $component->instance()->save();
            $this->fail('Translation failure must roll back creation.');
        } catch (\Illuminate\Database\QueryException $error) { $this->assertStringContainsString('Synthetic translation failure', $error->getMessage()); }
        $this->assertDatabaseCount('menu_items', 0);
        $this->assertDatabaseCount('translations', 0);
        $this->assertNull($component->instance()->itemId);
        $this->assertTrue($component->instance()->editing);
        $this->assertSame('Retain until commit', Cache::get('voyager_menu_admin'));
        DB::statement('DROP TRIGGER reject_menu_translation');
        $component->instance()->save();
        $this->assertDatabaseCount('menu_items', 1);
        $this->assertDatabaseHas('menu_items', ['title' => 'English']);
        $this->assertDatabaseHas('translations', ['locale' => 'ru', 'value' => 'Русский']);
        $this->assertFalse(Cache::has('voyager_menu_admin'));
    }

    public function test_menu_item_empty_translation_matches_original_skip_and_preserves_unconfigured_languages(): void
    {
        $this->createMenuManagementTables();
        $this->admin(['browse_admin', 'browse_menus', 'edit_menus']);
        $menu = DB::table('menus')->insertGetId(['name' => 'admin']);
        $id = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'English', 'order' => 1]);
        foreach (['ru' => 'Original RU', 'zz' => 'Legacy outside configured locales'] as $locale => $title) {
            DB::table('translations')->insert(['table_name' => 'menu_items', 'column_name' => 'title', 'foreign_key' => $id, 'locale' => $locale, 'value' => $title]);
        }
        Livewire::test(VoyagerMenuItems::class)->call('openEdit', $id)->call('changeLocale', 'ru')->set('data.title', '')
            ->set('data.url', '/changed')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'locale' => 'ru', 'value' => 'Original RU']);
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'locale' => 'zz', 'value' => 'Legacy outside configured locales']);
        $this->assertDatabaseHas('menu_items', ['id' => $id, 'title' => 'English', 'url' => '/changed']);
        $component = Livewire::test(VoyagerMenuItems::class)->call('openEdit', $id)->set('data.title', 'New EN')
            ->set('data.color', '#abcdef')->call('changeLocale', 'ru')->set('data.title', 'New RU');
        DB::statement("CREATE TRIGGER reject_menu_translation_edit BEFORE UPDATE ON translations BEGIN SELECT RAISE(ABORT, 'Synthetic translation edit failure'); END");
        try {
            $component->instance()->save();
            $this->fail('Translation failure must roll back common field changes.');
        } catch (\Illuminate\Database\QueryException $error) { $this->assertStringContainsString('Synthetic translation edit failure', $error->getMessage()); }
        $this->assertDatabaseHas('menu_items', ['id' => $id, 'title' => 'English', 'color' => null]);
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'locale' => 'ru', 'value' => 'Original RU']);
        DB::statement('DROP TRIGGER reject_menu_translation_edit');
        $component->instance()->save();
        $this->assertDatabaseHas('menu_items', ['id' => $id, 'title' => 'New EN', 'color' => '#abcdef']);
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'locale' => 'ru', 'value' => 'New RU']);
    }

    public function test_menu_item_parent_deletion_matches_voyager_and_retains_children_and_their_translations(): void
    {
        $this->createMenuManagementTables();
        $this->admin(['browse_admin', 'browse_menus', 'delete_menus']);
        $menu = DB::table('menus')->insertGetId(['name' => 'admin']);
        $parent = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'Parent', 'order' => 1]);
        $child = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'Child', 'parent_id' => $parent, 'order' => 1]);
        $grandchild = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'Grandchild', 'parent_id' => $child, 'order' => 1]);
        foreach ([$parent, $child, $grandchild] as $id) {
            DB::table('translations')->insert(['table_name' => 'menu_items', 'column_name' => 'title', 'foreign_key' => $id, 'locale' => 'ru', 'value' => 'RU '.$id]);
        }
        $component = Livewire::test(VoyagerMenuItems::class)->call('requestDeleteItem', $parent)
            ->assertSet('pendingItemDeletion', $parent)->assertSee('Дочерние пункты сохранятся');
        $this->assertDatabaseCount('menu_items', 3);
        $component->call('cancelDeleteItem')->assertSet('pendingItemDeletion', null);
        $this->assertDatabaseCount('menu_items', 3);
        Cache::put('voyager_menu_admin', 'Stale');
        $component->call('requestDeleteItem', $parent)->call('confirmDeleteItem')->assertHasNoErrors()->assertSet('pendingItemDeletion', null);
        $this->assertDatabaseMissing('menu_items', ['id' => $parent]);
        $this->assertDatabaseMissing('translations', ['table_name' => 'menu_items', 'foreign_key' => $parent]);
        $this->assertDatabaseHas('menu_items', ['id' => $child, 'parent_id' => $parent]);
        $this->assertDatabaseHas('menu_items', ['id' => $grandchild, 'parent_id' => $child]);
        $this->assertDatabaseHas('translations', ['foreign_key' => $child, 'value' => 'RU '.$child]);
        $this->assertDatabaseHas('translations', ['foreign_key' => $grandchild, 'value' => 'RU '.$grandchild]);
        $tree = $component->instance()->menuTree();
        $this->assertSame($child, $tree[0]['item']->id);
        $this->assertSame($grandchild, $tree[0]['children'][0]['item']->id);
        $this->assertFalse(Cache::has('voyager_menu_admin'));
    }

    public function test_menu_item_delete_rolls_back_translations_and_rechecks_permissions_and_menu(): void
    {
        $this->createMenuManagementTables();
        $this->admin(['browse_admin', 'browse_menus', 'delete_menus']);
        $menu = DB::table('menus')->insertGetId(['name' => 'admin']);
        $other = DB::table('menus')->insertGetId(['name' => 'footer']);
        $id = DB::table('menu_items')->insertGetId(['menu_id' => $menu, 'title' => 'Parent', 'order' => 1]);
        $foreign = DB::table('menu_items')->insertGetId(['menu_id' => $other, 'title' => 'Foreign', 'order' => 1]);
        DB::table('translations')->insert(['table_name' => 'menu_items', 'column_name' => 'title', 'foreign_key' => $id, 'locale' => 'ru', 'value' => 'Original']);
        $component = Livewire::test(VoyagerMenuItems::class)->call('requestDeleteItem', $id);
        Cache::put('voyager_menu_admin', 'Retain');
        DB::statement("CREATE TRIGGER reject_menu_item_delete BEFORE DELETE ON menu_items BEGIN SELECT RAISE(ABORT, 'Synthetic item delete failure'); END");
        try {
            $component->instance()->confirmDeleteItem();
            $this->fail('Deletion must roll back.');
        } catch (\Illuminate\Database\QueryException $error) { $this->assertStringContainsString('Synthetic item delete failure', $error->getMessage()); }
        $this->assertDatabaseHas('menu_items', ['id' => $id]);
        $this->assertDatabaseHas('translations', ['foreign_key' => $id, 'value' => 'Original']);
        $this->assertSame('Retain', Cache::get('voyager_menu_admin'));
        DB::statement('DROP TRIGGER reject_menu_item_delete');
        try {
            $component->instance()->requestDeleteItem($foreign);
            $this->fail('Foreign item must be rejected.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) { $this->assertSame(404, $error->getStatusCode()); }
        $component->call('selectMenu', $other)->assertSet('pendingItemDeletion', null)->call('confirmDeleteItem')->assertStatus(409);
        $this->admin(['browse_admin', 'browse_menus']);
        Livewire::test(VoyagerMenuItems::class)->call('requestDeleteItem', $id)->assertForbidden();
        Livewire::test(VoyagerMenuItems::class)->call('confirmDeleteItem')->assertForbidden();
        $this->assertDatabaseCount('menu_items', 2);
    }

    public function test_menu_item_and_its_translation_are_editable(): void
    {
        Schema::table('users', function (Blueprint $table): void { $table->text('settings')->nullable(); });
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

    public function test_standalone_translation_manager_edits_imports_and_publishes_with_filament_auth(): void
    {
        Schema::create('ltm_translations', function (Blueprint $table): void {
            $table->id(); $table->integer('status')->default(0);
            $table->string('locale'); $table->string('group'); $table->text('key');
            $table->text('value')->nullable(); $table->timestamps();
        });
        $this->admin(['browse_admin']);
        DB::table('ltm_translations')->insert(['locale' => 'ru', 'group' => 'homepage_new', 'key' => 'title', 'value' => 'Old']);
        $oldPath = app()->langPath();
        $directory = storage_path('framework/testing/standalone-translations-' . uniqid());
        \Illuminate\Support\Facades\File::ensureDirectoryExists($directory . '/ru');
        file_put_contents($directory . '/ru/homepage_new.php', "<?php return ['title' => 'From file', 'other' => 'Imported'];");
        app()->useLangPath($directory);
        app()->forgetInstance('translation.loader');
        app()->forgetInstance('translator');
        \Illuminate\Support\Facades\Lang::clearResolvedInstance('translator');
        try {
            $this->get('/filament/translations')->assertOk()->assertSee('Translation Manager')->assertSee('Import groups');
            $this->get('/filament/ui-translations')->assertRedirect('/filament/translations');
            $this->get('/filament/ui-translations?group=homepage_new')->assertRedirect('/filament/translations/view/homepage_new');
            $this->post('/filament/translations/edit/homepage_new', ['name' => 'ru|title', 'value' => 'New'])
                ->assertOk()->assertJson(['status' => 'ok']);
            $this->assertDatabaseHas('ltm_translations', ['key' => 'title', 'value' => 'New', 'status' => 1]);
            $this->post('/filament/translations/import', ['replace' => false])->assertOk()->assertJson(['status' => 'ok']);
            $this->assertDatabaseHas('ltm_translations', ['key' => 'title', 'value' => 'New']);
            $this->assertDatabaseHas('ltm_translations', ['key' => 'other', 'value' => 'Imported']);
            $this->post('/filament/translations/publish/homepage_new')->assertOk()->assertJson(['status' => 'ok']);
            $this->assertSame(['title' => 'New', 'other' => 'Imported'], require $directory . '/ru/homepage_new.php');
            $this->assertDatabaseHas('ltm_translations', ['key' => 'title', 'status' => 0]);
            $this->get('/filament/translations/view/homepage_new')->assertOk()->assertSee('New');
            $this->post('/filament/translations/add/homepage_new', ['keys' => "new_key\nsecond_key"])->assertRedirect();
            $this->assertDatabaseHas('ltm_translations', ['group' => 'homepage_new', 'key' => 'new_key']);
            auth('filament')->user()->role->permissions()->detach();
            auth('filament')->user()->unsetRelations();
            $this->get('/filament/translations')->assertForbidden();
            $this->post('/filament/translations/edit/homepage_new', ['name' => 'ru|title', 'value' => 'Forbidden'])->assertForbidden();
            auth('filament')->logout();
            $this->get('/filament/translations')->assertRedirect(route('filament.admin.auth.login'));
            $this->postJson('/filament/translations/edit/homepage_new', ['name' => 'ru|title', 'value' => 'Guest'])->assertUnauthorized();
            $this->assertDatabaseHas('ltm_translations', ['key' => 'title', 'value' => 'New']);
        } finally {
            app()->useLangPath($oldPath);
            \Illuminate\Support\Facades\File::deleteDirectory($directory);
        }
    }
    private function createSettingsManagementTables(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id(); $table->string('key')->unique(); $table->string('display_name');
            $table->text('value')->nullable(); $table->text('details')->nullable();
            $table->string('type'); $table->integer('order')->default(0); $table->string('group')->nullable();
        });
    }

    public function test_setting_creation_and_group_changes_match_original_key_and_global_order(): void
    {
        $this->createSettingsManagementTables();
        $this->admin(['browse_admin', 'browse_settings', 'add_settings', 'edit_settings']);
        $component = Livewire::test(VoyagerSettings::class)->assertSee('Настройки ещё не созданы.')
            ->call('openCreate')->set('data', ['display_name' => 'Title', 'key' => 'title', 'type' => 'text', 'group' => 'Site', 'details' => '{bad'])
            ->call('createSetting')->assertHasErrors('data.details')
            ->set('data.details', '{"placeholder":"Title"}')->call('createSetting')->assertHasNoErrors()->assertSet('activeGroup', 'Site');
        $this->assertDatabaseHas('settings', ['key' => 'site.title', 'value' => '', 'order' => 0, 'group' => 'Site']);
        $id = DB::table('settings')->where('key', 'site.title')->value('id');
        $component->call('openCreate')->set('data', ['display_name' => 'Caption', 'key' => 'caption', 'type' => 'text_area', 'group' => 'My Group', 'details' => null])
            ->call('createSetting')->assertHasNoErrors()->assertSet('activeGroup', 'My Group');
        $this->assertDatabaseHas('settings', ['key' => 'my-group.caption', 'order' => 1]);
        $component->call('openCreate')->set('data', ['display_name' => 'Duplicate', 'key' => 'title', 'type' => 'text', 'group' => 'Site'])
            ->call('createSetting')->assertHasErrors('data.key')->call('cancel');
        $this->assertSame(2, DB::table('settings')->count());
        $component->call('openEdit', $id)->set('data.value', 'New')->set('data.group', 'Admin')
            ->call('save')->assertHasNoErrors()->assertSet('activeGroup', 'Admin');
        $this->assertDatabaseHas('settings', ['id' => $id, 'key' => 'admin.title', 'group' => 'Admin', 'value' => 'New', 'order' => 0]);
        DB::table('settings')->insert(['key' => 'other.title', 'display_name' => 'Other title', 'type' => 'text', 'group' => 'Other']);
        $component->call('openEdit', $id)->set('data.group', 'Other')->call('save')->assertHasErrors('data.group');
        $this->assertDatabaseHas('settings', ['id' => $id, 'key' => 'admin.title', 'group' => 'Admin']);
    }

    public function test_setting_order_swaps_only_neighbors_in_same_group_and_stops_at_boundaries(): void
    {
        $this->createSettingsManagementTables();
        $this->admin(['browse_admin', 'browse_settings', 'edit_settings']);
        $first = DB::table('settings')->insertGetId(['key' => 'site.first', 'display_name' => 'First', 'type' => 'text', 'group' => 'Site', 'order' => 0]);
        $second = DB::table('settings')->insertGetId(['key' => 'site.second', 'display_name' => 'Second', 'type' => 'text', 'group' => 'Site', 'order' => 10]);
        $other = DB::table('settings')->insertGetId(['key' => 'other.title', 'display_name' => 'Other', 'type' => 'text', 'group' => 'Other', 'order' => 5]);
        $component = Livewire::test(VoyagerSettings::class)->call('moveSetting', $second, 'up')->assertHasNoErrors()->assertSet('activeGroup', 'Site');
        $this->assertDatabaseHas('settings', ['id' => $first, 'order' => 10]);
        $this->assertDatabaseHas('settings', ['id' => $second, 'order' => 0]);
        $this->assertDatabaseHas('settings', ['id' => $other, 'order' => 5]);
        $component->call('moveSetting', $second, 'up')->assertHasNoErrors()
            ->call('moveSetting', $second, 'down')->assertHasNoErrors();
        $this->assertDatabaseHas('settings', ['id' => $first, 'order' => 0]);
        $this->assertDatabaseHas('settings', ['id' => $second, 'order' => 10]);
        DB::statement("CREATE TRIGGER reject_setting_order BEFORE UPDATE ON settings WHEN OLD.id = $second BEGIN SELECT RAISE(ABORT, 'Synthetic order failure'); END");
        try {
            $component->instance()->moveSetting($first, 'down');
            $this->fail('Both order updates must roll back.');
        } catch (\Illuminate\Database\QueryException $error) {
            $this->assertStringContainsString('Synthetic order failure', $error->getMessage());
        }
        $this->assertDatabaseHas('settings', ['id' => $first, 'order' => 0]);
        $this->assertDatabaseHas('settings', ['id' => $second, 'order' => 10]);
    }

    public function test_setting_delete_and_clear_follow_original_image_and_file_rules(): void
    {
        $this->createSettingsManagementTables();
        Storage::fake('public');
        Storage::disk('public')->put('settings/image.png', 'Image bytes');
        Storage::disk('public')->put('settings/file.pdf', 'Document bytes');
        $this->admin(['browse_admin', 'browse_settings', 'delete_settings']);
        $image = DB::table('settings')->insertGetId(['key' => 'site.image', 'display_name' => 'Image', 'type' => 'image', 'group' => 'Site', 'value' => 'settings/image.png']);
        $file = DB::table('settings')->insertGetId(['key' => 'site.file', 'display_name' => 'File', 'type' => 'file', 'group' => 'Site', 'value' => 'settings/file.pdf']);
        $component = Livewire::test(VoyagerSettings::class)->call('requestRemove', $image, true)
            ->assertDispatched('open-modal', id: 'remove-setting')->assertSee('Файл изображения также будет удалён.');
        $this->assertSame('Image bytes', Storage::disk('public')->get('settings/image.png'));
        $component->call('confirmRemove')->assertHasNoErrors()->assertSet('pendingId', null);
        Storage::disk('public')->assertMissing('settings/image.png');
        $this->assertDatabaseHas('settings', ['id' => $image, 'value' => '']);
        $component->call('requestRemove', $file, true)->call('confirmRemove')->assertHasNoErrors();
        $this->assertSame('Document bytes', Storage::disk('public')->get('settings/file.pdf'));
        Storage::disk('public')->put('settings/image.png', 'Keep on row delete');
        DB::table('settings')->where('id', $image)->update(['value' => 'settings/image.png']);
        $component->call('requestRemove', $image)->call('confirmRemove')->assertHasNoErrors();
        $this->assertDatabaseMissing('settings', ['id' => $image]);
        $this->assertSame('Keep on row delete', Storage::disk('public')->get('settings/image.png'));
        $component->call('requestRemove', $file)->call('confirmRemove')->assertSee('Настройки ещё не созданы.');
    }

    public function test_setting_actions_check_permissions_and_restore_image_after_database_failure(): void
    {
        $this->createSettingsManagementTables();
        Storage::fake('public');
        Storage::disk('public')->put('settings/image.png', 'Original bytes');
        $image = DB::table('settings')->insertGetId(['key' => 'site.image', 'display_name' => 'Image', 'type' => 'image', 'group' => 'Site', 'value' => 'settings/image.png']);
        $this->admin(['browse_admin', 'browse_settings']);
        $forged = Livewire::test(VoyagerSettings::class)->set('settingId', $image);
        $this->assertSame([], $forged->instance()->form->getFlatFields());
        Livewire::test(VoyagerSettings::class)->assertDontSee('Добавить настройку')->call('openCreate')->assertForbidden();
        Livewire::test(VoyagerSettings::class)->set('creating', true)->call('createSetting')->assertForbidden();
        Livewire::test(VoyagerSettings::class)->call('moveSetting', $image, 'up')->assertForbidden();
        Livewire::test(VoyagerSettings::class)->call('requestRemove', $image)->assertForbidden();
        Livewire::test(VoyagerSettings::class)->set('pendingId', $image)->set('clearingValue', true)->call('confirmRemove')->assertForbidden();
        $permission = Permission::create(['key' => 'delete_settings']);
        auth('filament')->user()->role->permissions()->attach($permission);
        auth('filament')->user()->unsetRelations();
        DB::statement("CREATE TRIGGER reject_setting_clear BEFORE UPDATE ON settings BEGIN SELECT RAISE(ABORT, 'Synthetic setting failure'); END");
        $component = Livewire::test(VoyagerSettings::class)->call('requestRemove', $image, true);
        try {
            $component->instance()->confirmRemove();
            $this->fail('The image must be restored after failure.');
        } catch (\Illuminate\Database\QueryException $error) {
            $this->assertStringContainsString('Synthetic setting failure', $error->getMessage());
        }
        $this->assertSame('Original bytes', Storage::disk('public')->get('settings/image.png'));
        $this->assertDatabaseHas('settings', ['id' => $image, 'value' => 'settings/image.png']);
    }

    public function test_setting_text_code_choice_and_checkbox_types_preserve_original_values_and_defaults(): void
    {
        $this->createSettingsManagementTables();
        $this->admin(['browse_admin', 'browse_settings', 'edit_settings']);
        $cases = [
            ['text', 'Old', [], str_repeat('x', 6000), \Filament\Forms\Components\TextInput::class],
            ['text_area', "Line1\nLine2", [], "New1\nNew2", \Filament\Forms\Components\Textarea::class],
            ['code_editor', 'old()', ['language' => 'javascript', 'theme' => 'monokai'], "const x = '<>&';\n", \Filament\Forms\Components\CodeEditor::class],
            ['rich_text_box', '<section data-custom="1"><p>Old</p></section>', [], '<section data-custom="2">New</section>', \Filament\Forms\Components\CodeEditor::class],
            ['checkbox', '2', ['on' => 'Yes', 'off' => 'No'], true, \Filament\Forms\Components\Toggle::class],
            ['select_dropdown', '', ['options' => ['a' => 'A', 'b' => 'B'], 'default' => 'b'], 'a', \Filament\Forms\Components\Select::class],
            ['radio_btn', 'right', ['options' => ['left' => 'Left', 'right' => 'Right'], 'default' => 'left'], 'left', \Filament\Forms\Components\Radio::class],
        ];
        foreach ($cases as [$type, $old, $details, $value, $class]) {
            $id = DB::table('settings')->insertGetId(['key' => 'site.'.$type, 'display_name' => $type, 'type' => $type,
                'group' => 'Site', 'value' => $old, 'details' => json_encode($details)]);
            $component = Livewire::test(VoyagerSettings::class)->call('openEdit', $id);
            $this->assertInstanceOf($class, $component->instance()->form->getFlatFields()['value']);
            if ($type === 'checkbox') { $component->assertSet('data.value', false)->assertSee('Yes / No'); }
            if ($type === 'select_dropdown') { $component->assertSet('data.value', 'b'); }
            if ($type === 'rich_text_box') { $component->assertSet('richSource', true); }
            $component->set('data.value', $value)->call('save')->assertHasNoErrors();
            $this->assertDatabaseHas('settings', ['id' => $id, 'value' => $type === 'checkbox' ? '1' : $value]);
        }
        $visual = DB::table('settings')->insertGetId(['key' => 'site.visual', 'display_name' => 'Visual', 'type' => 'rich_text_box', 'group' => 'Site', 'value' => '<p>Hello</p>']);
        $component = Livewire::test(VoyagerSettings::class)->call('openEdit', $visual)->assertSet('richSource', false);
        $this->assertInstanceOf(\Filament\Forms\Components\RichEditor::class, $component->instance()->form->getFlatFields()['value']);
        $select = DB::table('settings')->where('type', 'select_dropdown')->value('id');
        Livewire::test(VoyagerSettings::class)->call('openEdit', $select)->set('data.value', 'outside')->call('save')->assertHasErrors('data.value');
        $this->assertDatabaseHas('settings', ['id' => $select, 'value' => 'a']);
    }

    public function test_setting_file_and_image_uploads_keep_legacy_shape_and_retain_unchanged_sources(): void
    {
        $this->createSettingsManagementTables();
        Storage::fake('public');
        Storage::disk('public')->put('settings/manual.txt', 'Existing');
        $this->admin(['browse_admin', 'browse_settings', 'edit_settings']);
        $legacy = json_encode([['download_link' => 'settings/manual.txt', 'original_name' => 'Manual.txt']]);
        $file = DB::table('settings')->insertGetId(['key' => 'site.file', 'display_name' => 'File', 'type' => 'file', 'group' => 'Site',
            'value' => $legacy, 'details' => json_encode(['preserveFileUploadName' => true])]);
        Livewire::test(VoyagerSettings::class)->call('openEdit', $file)->assertSee('Manual.txt')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('settings', ['id' => $file, 'value' => $legacy]);
        Livewire::test(VoyagerSettings::class)->call('openEdit', $file)
            ->set('data.replacement', [UploadedFile::fake()->createWithContent('manual.txt', 'Replacement')])->call('save')->assertHasNoErrors();
        $stored = json_decode(DB::table('settings')->where('id', $file)->value('value'), true);
        $this->assertSame('manual.txt', $stored[0]['original_name']);
        $this->assertSame('Replacement', Storage::disk('public')->get($stored[0]['download_link']));
        $this->assertSame('Existing', Storage::disk('public')->get('settings/manual.txt'));
        Livewire::test(VoyagerSettings::class)->call('openEdit', $file)
            ->set('data.replacement', [UploadedFile::fake()->createWithContent('manual.txt', 'Second replacement')])->call('save')->assertHasNoErrors();
        $second = json_decode(DB::table('settings')->where('id', $file)->value('value'), true);
        $this->assertSame('manual1.txt', basename($second[0]['download_link']));
        $this->assertSame('Replacement', Storage::disk('public')->get($stored[0]['download_link']));
        $beforeRejected = Storage::disk('public')->allFiles();
        Livewire::test(VoyagerSettings::class)->call('openEdit', $file)
            ->set('data.replacement', [UploadedFile::fake()->createWithContent('blocked.php', '<?php echo 1;')])->call('save')->assertHasErrors('data.replacement');
        $this->assertSame($beforeRejected, Storage::disk('public')->allFiles());
        $this->assertSame($second, json_decode(DB::table('settings')->where('id', $file)->value('value'), true));
        $image = DB::table('settings')->insertGetId(['key' => 'site.image', 'display_name' => 'Image', 'type' => 'image', 'group' => 'Site',
            'value' => 'settings/old.png', 'details' => json_encode(['resize' => ['width' => 10], 'upsize' => false])]);
        Storage::disk('public')->put('settings/old.png', 'Old bytes');
        Livewire::test(VoyagerSettings::class)->call('openEdit', $image)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('settings', ['id' => $image, 'value' => 'settings/old.png']);
        Livewire::test(VoyagerSettings::class)->call('openEdit', $image)->set('data.replacement', [UploadedFile::fake()->image('photo.png', 40, 20)])
            ->call('save')->assertHasNoErrors();
        $path = DB::table('settings')->where('id', $image)->value('value');
        $size = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame([10, 5], [$size[0], $size[1]]);
        $this->assertSame('Old bytes', Storage::disk('public')->get('settings/old.png'));
    }

    public function test_setting_failed_image_save_cleans_copies_and_can_retry_the_same_upload(): void
    {
        $this->createSettingsManagementTables();
        Storage::fake('public');
        Storage::disk('public')->put('settings/original.png', 'Original');
        $this->admin(['browse_admin', 'browse_settings', 'edit_settings']);
        $id = DB::table('settings')->insertGetId(['key' => 'site.image', 'display_name' => 'Image', 'type' => 'image', 'group' => 'Site', 'value' => 'settings/original.png']);
        $component = Livewire::test(VoyagerSettings::class)->call('openEdit', $id)->set('data.replacement', [UploadedFile::fake()->image('new.png', 40, 20)]);
        DB::statement("CREATE TRIGGER reject_setting_upload BEFORE UPDATE ON settings BEGIN SELECT RAISE(ABORT, 'Synthetic upload failure'); END");
        try {
            $component->instance()->save();
            $this->fail('The failed upload must be cleaned.');
        } catch (\Illuminate\Database\QueryException $error) {
            $this->assertStringContainsString('Synthetic upload failure', $error->getMessage());
        }
        $this->assertSame(['settings/original.png'], Storage::disk('public')->allFiles());
        $this->assertDatabaseHas('settings', ['id' => $id, 'value' => 'settings/original.png']);
        DB::statement('DROP TRIGGER reject_setting_upload');
        $component->instance()->save();
        $path = DB::table('settings')->where('id', $id)->value('value');
        $this->assertNotSame('settings/original.png', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertSame('Original', Storage::disk('public')->get('settings/original.png'));
    }

    public function test_settings_joint_editor_saves_all_groups_and_preserves_media_without_upload(): void
    {
        $this->createSettingsManagementTables();
        Storage::fake('public');
        $this->admin(['browse_admin', 'browse_settings', 'edit_settings']);
        $ids = [];
        foreach (['text', 'text_area', 'rich_text_box', 'code_editor', 'checkbox', 'radio_btn', 'select_dropdown', 'image', 'file'] as $index => $type) {
            $group = $index % 2 ? 'Admin' : 'Site';
            $ids[$type] = DB::table('settings')->insertGetId(['key' => strtolower($group).'.'.$type, 'display_name' => $type, 'type' => $type,
                'group' => $group, 'value' => $type === 'rich_text_box' ? '<section>Old</section>' : ($type === 'image' ? 'settings/old.png' : 'old'),
                'details' => json_encode(['options' => ['old' => 'Old', 'new' => 'New']])]);
        }
        Storage::disk('public')->put('settings/old.png', 'Original');
        $component = Livewire::test(VoyagerSettings::class)->call('openEditAll')->assertSet('editingAll', true)->assertSee('Сохранить настройки');
        foreach (['text', 'text_area', 'rich_text_box', 'code_editor', 'checkbox', 'radio_btn', 'select_dropdown'] as $type) {
            $component->set('data.value_'.$ids[$type], $type === 'checkbox' ? true : ($type === 'rich_text_box' ? '<section>New</section>' : 'new'));
        }
        $component->set('data.group_'.$ids['text'], 'Other')->call('saveAll')->assertHasNoErrors()->assertSet('editingAll', false);
        foreach (['text', 'text_area', 'rich_text_box', 'code_editor', 'checkbox', 'radio_btn', 'select_dropdown'] as $type) {
            $this->assertDatabaseHas('settings', ['id' => $ids[$type], 'value' => $type === 'checkbox' ? '1' : ($type === 'rich_text_box' ? '<section>New</section>' : 'new')]);
        }
        $this->assertDatabaseHas('settings', ['id' => $ids['text'], 'key' => 'other.text', 'group' => 'Other']);
        $this->assertDatabaseHas('settings', ['id' => $ids['image'], 'value' => 'settings/old.png']);
        $this->assertDatabaseHas('settings', ['id' => $ids['file'], 'value' => 'old']);
        $this->assertSame('Original', Storage::disk('public')->get('settings/old.png'));
    }

    public function test_settings_joint_editor_rolls_back_rows_and_uploads_then_retries(): void
    {
        $this->createSettingsManagementTables();
        Storage::fake('public');
        $this->admin(['browse_admin', 'browse_settings', 'edit_settings']);
        $file = DB::table('settings')->insertGetId(['key' => 'site.file', 'display_name' => 'File', 'type' => 'file', 'group' => 'Site', 'value' => 'original']);
        $text = DB::table('settings')->insertGetId(['key' => 'admin.text', 'display_name' => 'Text', 'type' => 'text', 'group' => 'Admin', 'value' => 'old']);
        $component = Livewire::test(VoyagerSettings::class)->call('openEditAll')
            ->set('data.replacement_'.$file, [UploadedFile::fake()->createWithContent('manual.txt', 'Uploaded bytes')])->set('data.value_'.$text, 'new');
        DB::statement("CREATE TRIGGER reject_joint_save BEFORE UPDATE ON settings WHEN NEW.id = $text BEGIN SELECT RAISE(ABORT, 'Synthetic joint failure'); END");
        try {
            $component->instance()->saveAll();
            $this->fail('Joint save must fail.');
        } catch (\Illuminate\Database\QueryException $error) { $this->assertStringContainsString('Synthetic joint failure', $error->getMessage()); }
        $this->assertDatabaseHas('settings', ['id' => $file, 'value' => 'original']);
        $this->assertDatabaseHas('settings', ['id' => $text, 'value' => 'old']);
        $this->assertSame([], Storage::disk('public')->allFiles());
        DB::statement('DROP TRIGGER reject_joint_save');
        $component->instance()->saveAll();
        $stored = json_decode(DB::table('settings')->where('id', $file)->value('value'), true);
        $this->assertSame('Uploaded bytes', Storage::disk('public')->get($stored[0]['download_link']));
        $this->assertDatabaseHas('settings', ['id' => $text, 'value' => 'new']);
    }

    public function test_settings_joint_editor_validates_choices_keys_snapshot_and_permissions(): void
    {
        $this->createSettingsManagementTables();
        Storage::fake('public');
        $this->admin(['browse_admin', 'browse_settings', 'edit_settings']);
        $id = DB::table('settings')->insertGetId(['key' => 'site.choice', 'display_name' => 'Choice', 'type' => 'select_dropdown', 'group' => 'Site', 'value' => 'a', 'details' => '{"options":{"a":"A","b":"B"}}']);
        DB::table('settings')->insert(['key' => 'other.choice', 'display_name' => 'Other', 'type' => 'text', 'group' => 'Other', 'value' => 'original']);
        Livewire::test(VoyagerSettings::class)->call('openEditAll')->set('data.value_'.$id, 'invalid')->call('saveAll')->assertHasErrors('data.value_'.$id);
        Livewire::test(VoyagerSettings::class)->call('openEditAll')->set('data.group_'.$id, 'Other')->call('saveAll')->assertHasErrors('data.group_'.$id);
        $this->assertDatabaseHas('settings', ['id' => $id, 'value' => 'a', 'key' => 'site.choice']);
        $component = Livewire::test(VoyagerSettings::class)->call('openEditAll');
        $new = DB::table('settings')->insertGetId(['key' => 'site.new', 'display_name' => 'New', 'type' => 'text', 'group' => 'Site', 'value' => 'Untouched']);
        $component->set('data.value_'.$new, 'Forged')->set('data.value_'.$id, 'b')->call('saveAll')->assertHasNoErrors();
        $this->assertDatabaseHas('settings', ['id' => $new, 'value' => 'Untouched']);
        $component = Livewire::test(VoyagerSettings::class)->call('openEditAll');
        $component->set('data.group_'.$id, 'Fresh')->set('data.group_'.DB::table('settings')->where('key', 'other.choice')->value('id'), 'Fresh')
            ->call('saveAll')->assertHasErrors();
        $this->assertDatabaseHas('settings', ['id' => $id, 'key' => 'site.choice']);
        $component = Livewire::test(VoyagerSettings::class)->call('openEditAll');
        DB::table('settings')->where('id', $id)->delete();
        $component->call('saveAll')->assertStatus(409);
        $this->admin(['browse_admin', 'browse_settings']);
        Livewire::test(VoyagerSettings::class)->call('openEditAll')->assertForbidden();
        Livewire::test(VoyagerSettings::class)->set('editingAll', true)->call('saveAll')->assertForbidden();
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_setting_rich_attachment_is_cleaned_on_sql_failure_and_same_draft_can_retry(): void
    {
        $this->createSettingsManagementTables();
        Storage::fake('public');
        $this->admin(['browse_admin', 'browse_settings', 'edit_settings']);
        $id = DB::table('settings')->insertGetId(['key' => 'site.rich', 'display_name' => 'Rich', 'type' => 'rich_text_box', 'group' => 'Site', 'value' => '<p>Original</p>']);
        foreach ([false, true] as $bulk) {
            DB::table('settings')->where('id', $id)->update(['value' => '<p>Original</p>']);
            $field = $bulk ? 'value_'.$id : 'value';
            $editor = Livewire::test(VoyagerSettings::class)->call($bulk ? 'openEditAll' : 'openEdit', ...($bulk ? [] : [$id]));
            $attach = \Filament\Actions\Testing\TestAction::make('attachFiles')->schemaComponent($field, 'form')
                ->arguments(['editorSelection' => ['anchor' => 1, 'head' => 1, 'type' => 'text']]);
            $editor->callAction($attach, ['file' => UploadedFile::fake()->image('inline.png'), 'alt' => 'Inline'])->assertHasNoActionErrors();
            $uploads = $editor->get('componentFileAttachments.data.'.$field);
            $uuid = array_key_first($uploads);
            $document = ['type' => 'doc', 'content' => [['type' => 'image', 'attrs' => ['id' => $uuid, 'src' => $uploads[$uuid]->temporaryUrl()]]]];
            $editor->set('data.'.$field, $document);
            $beforeFiles = Storage::disk('public')->allFiles();
            $beforeValue = DB::table('settings')->where('id', $id)->value('value');
            DB::statement("CREATE TRIGGER reject_rich_setting BEFORE UPDATE ON settings BEGIN SELECT RAISE(ABORT, 'Synthetic rich setting failure'); END");
            try {
                $bulk ? $editor->instance()->saveAll() : $editor->instance()->save();
                $this->fail('Rich save must fail.');
            } catch (\Illuminate\Database\QueryException $error) { $this->assertStringContainsString('Synthetic rich setting failure', $error->getMessage()); }
            $this->assertSame($beforeFiles, Storage::disk('public')->allFiles());
            $this->assertDatabaseHas('settings', ['id' => $id, 'value' => $beforeValue]);
            $this->assertSame($document, $editor->instance()->data[$field]);
            DB::statement('DROP TRIGGER reject_rich_setting');
            $bulk ? $editor->instance()->saveAll() : $editor->instance()->save();
            $stored = DB::table('settings')->where('id', $id)->value('value');
            $this->assertStringContainsString('<img', $stored);
            $this->assertCount(count($beforeFiles) + 1, Storage::disk('public')->allFiles());
        }
    }

    public function test_setting_groups_use_voyager_global_order_instead_of_alphabetical_names(): void
    {
        $this->createSettingsManagementTables();
        $this->admin(['browse_admin', 'browse_settings', 'edit_settings']);
        foreach (['Site' => 1, 'Admin' => 2, 'Соц. сети' => 3, 'Модульные картины' => 4, '' => 99] as $group => $order) {
            DB::table('settings')->insert(['key' => 'key'.$order, 'display_name' => 'Name'.$order, 'type' => 'text', 'group' => $group, 'order' => $order, 'value' => 'Value']);
        }
        $component = Livewire::test(VoyagerSettings::class);
        $this->assertSame(['', 'Site', 'Admin', 'Соц. сети', 'Модульные картины'], array_keys($component->instance()->groups()));
        $component->call('openEditAll');
        $tabs = $component->instance()->form->getComponents()[0]->getChildSchema()->getComponents();
        $this->assertSame(['Общие', 'Site', 'Admin', 'Соц. сети', 'Модульные картины'], array_map(fn ($tab) => $tab->getLabel(), $tabs));
        foreach ($tabs as $tab) { $this->assertSame($tab->getId(), $tab->getKey(isAbsolute: false)); }
        $expectedEffect = $component->instance()->form->getComponents()[0]->getExtraAlpineAttributes()['x-effect'];
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$component->html());
        $nodes = (new \DOMXPath($document))->query('//*[@x-effect]');
        $effects = [];
        foreach ($nodes as $node) { $effects[] = $node->getAttribute('x-effect'); }
        $this->assertContains($expectedEffect, $effects, 'HTML parsing must retain the complete tab synchronization expression.');
    }

    public function test_joint_settings_html_action_is_scoped_to_one_rich_field_and_preserves_other_drafts(): void
    {
        $this->createSettingsManagementTables();
        $this->admin(['browse_admin', 'browse_settings', 'edit_settings']);
        $ids = [];
        foreach (['first' => 'rich_text_box', 'second' => 'rich_text_box', 'text' => 'text'] as $key => $type) {
            $ids[$key] = DB::table('settings')->insertGetId(['key' => 'site.'.$key, 'display_name' => $key,
                'type' => $type, 'group' => 'Site', 'value' => '<p>Original</p>']);
        }
        $editor = Livewire::test(VoyagerSettings::class)->call('openEditAll')->set('data.value_'.$ids['second'], '<p>Second draft</p>')
            ->set('data.value_'.$ids['text'], 'Text draft');
        $html = '<section data-custom="retained"><p>Exact HTML &amp; Unicode: тест</p></section>';
        $action = \Filament\Actions\Testing\TestAction::make('breadSource')->schemaComponent('value_'.$ids['first'], 'form');
        $editor->callAction($action, ['html' => $html])->assertHasNoActionErrors()
            ->assertSet('richSources.'.$ids['first'], true)->assertSet('richSources.'.$ids['second'], false)
            ->assertSet('data.value_'.$ids['first'], $html)
            ->assertSet('data.value_'.$ids['text'], 'Text draft');
        $fields = $editor->instance()->form->getFlatFields();
        $this->assertInstanceOf(\Filament\Forms\Components\CodeEditor::class, $fields['value_'.$ids['first']]);
        $this->assertInstanceOf(\Filament\Forms\Components\RichEditor::class, $fields['value_'.$ids['second']]);
        $this->assertSame('<p>Second draft</p>', $fields['value_'.$ids['second']]->getState());
        foreach (['value_'.$ids['text'], 'value_999999'] as $field) {
            try {
                $editor->instance()->enableRichSource($field);
                $this->fail('Only an open rich setting may enter HTML mode.');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) { $this->assertSame(403, $error->getStatusCode()); }
        }
        $editor->call('saveAll')->assertHasNoErrors();
        $this->assertDatabaseHas('settings', ['id' => $ids['first'], 'value' => $html]);
        $this->assertDatabaseHas('settings', ['id' => $ids['second'], 'value' => '<p>Second draft</p>']);
        $this->assertDatabaseHas('settings', ['id' => $ids['text'], 'value' => 'Text draft']);
        $single = Livewire::test(VoyagerSettings::class)->call('openEdit', $ids['second']);
        $singleAction = \Filament\Actions\Testing\TestAction::make('breadSource')->schemaComponent('value', 'form');
        $single->callAction($singleAction, ['html' => $html])->assertHasNoActionErrors()->assertSet('data.value', $html);
        $this->assertInstanceOf(\Filament\Forms\Components\CodeEditor::class, $single->instance()->form->getFlatFields()['value']);
        $single->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('settings', ['id' => $ids['second'], 'value' => $html]);
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
            ->assertRedirect(VoyagerBreadEdit::getUrl(['type' => 'gallery-items', 'record' => $id + 1]) . '?type=2&list_lang=en');
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

    public function test_role_permissions_save_with_role_and_survive_language_switch(): void
    {
        $this->admin(['browse_admin', 'browse_roles', 'edit_roles']);
        $this->roleMetadata();
        $target = Role::create(['name' => 'seo_manager', 'display_name' => 'SEO']);
        $old = Permission::create(['key' => 'browse_pages', 'table_name' => 'pages']);
        $new = Permission::create(['key' => 'edit_pages', 'table_name' => 'pages']);
        $target->permissions()->attach($old);

        Livewire::test(VoyagerBreadEdit::class, ['type' => 'roles', 'record' => $target->id])
            ->assertSee('Разрешения роли')->assertSee('Просмотр списка')->assertSee('Редактирование')
            ->assertSet('rolePermissionIds', [(string) $old->id])
            ->set('data.display_name', 'Updated SEO')
            ->set('rolePermissionIds', [(string) $new->id, (string) $new->id])
            ->call('changeLocale', 'ru')
            ->assertSet('rolePermissionIds', [(string) $new->id, (string) $new->id])
            ->call('save')->assertHasNoErrors();

        $this->assertSame('Updated SEO', $target->fresh()->display_name);
        $this->assertSame([$new->id], $target->permissions()->pluck('permissions.id')->all());
    }

    public function test_unknown_permission_rejects_role_and_permission_changes(): void
    {
        $this->admin(['browse_admin', 'browse_roles', 'edit_roles']);
        $this->roleMetadata();
        $target = Role::create(['name' => 'manager', 'display_name' => 'Before']);
        $permission = Permission::create(['key' => 'browse_pages', 'table_name' => 'pages']);
        $target->permissions()->attach($permission);

        Livewire::test(VoyagerBreadEdit::class, ['type' => 'roles', 'record' => $target->id])
            ->set('data.display_name', 'Must not save')->set('rolePermissionIds', ['999999'])
            ->call('save')->assertHasErrors(['rolePermissionIds.0']);

        $this->assertSame('Before', $target->fresh()->display_name);
        $this->assertSame([$permission->id], $target->permissions()->pluck('permissions.id')->all());
    }

    public function test_role_permission_group_selection_and_create_then_clear(): void
    {
        $this->admin(['browse_admin', 'browse_roles', 'add_roles', 'edit_roles']);
        $this->roleMetadata();
        $browse = Permission::create(['key' => 'browse_pages', 'table_name' => 'pages']);
        $edit = Permission::create(['key' => 'edit_pages', 'table_name' => 'pages']);
        $other = Permission::create(['key' => 'browse_orders', 'table_name' => 'orders']);
        $component = Livewire::test(VoyagerBread::class, ['type' => 'roles'])
            ->call('openCreate')->assertSet('rolePermissionIds', [])
            ->assertDontSee('@js(', escape: false)
            ->set('data.name', 'manager')->set('data.display_name', 'Manager')
            ->call('selectRolePermissions', 'pages', true)
            ->assertSet('rolePermissionIds', [(string) $browse->id, (string) $edit->id])
            ->call('selectRolePermissions', 'pages', false)->assertSet('rolePermissionIds', [])
            ->call('selectRolePermissions', 'pages', true)
            ->call('save')->assertHasNoErrors();
        $target = Role::where('name', 'manager')->firstOrFail();
        $this->assertSame([$browse->id, $edit->id], $target->permissions()->orderBy('permissions.id')->pluck('permissions.id')->all());
        $this->assertDatabaseMissing('permission_role', ['role_id' => $target->id, 'permission_id' => $other->id]);

        Livewire::test(VoyagerBreadEdit::class, ['type' => 'roles', 'record' => $target->id])
            ->call('selectRolePermissions', null, true)
            ->assertSet('rolePermissionIds', fn ($ids) => in_array((string) $other->id, $ids, true))
            ->call('selectRolePermissions', null, false)->assertSet('rolePermissionIds', [])
            ->call('save')->assertHasNoErrors();
        $this->assertSame(0, $target->permissions()->count());
    }

    public function test_role_editor_requires_permission_regardless_of_role_name(): void
    {
        $this->admin(['browse_admin', 'browse_roles']);
        auth('filament')->user()->role->update(['name' => 'manager']);
        $this->roleMetadata();
        $target = Role::create(['name' => 'seo_manager']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'roles', 'record' => $target->id])->assertForbidden();
        Livewire::test(VoyagerBread::class, ['type' => 'roles'])
            ->set('editing', true)->set('recordId', $target->id)
            ->call('selectRolePermissions', null, true)->assertForbidden();
        Livewire::test(VoyagerBread::class, ['type' => 'roles'])
            ->set('editing', true)->set('recordId', $target->id)
            ->set('rolePermissionIds', [1])->call('save')->assertForbidden();
        $this->assertSame(0, $target->permissions()->count());
    }

    public function test_role_permission_controls_cannot_change_other_bread(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        $id = DB::table('pages')->insertGetId(['title' => 'Page']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->assertDontSee('Разрешения роли')->call('selectRolePermissions', null, true)->assertStatus(409);
    }

    public function test_seo_resource_filters_and_approval_apply_localized_values(): void
    {
        $this->seoFixture();
        $id = DB::table('pages')->insertGetId(['title' => 'Source', 'meta_title' => 'Base', 'meta_description' => 'Base description']);
        $record = \App\Models\SeoMetaSuggestion::create(['metaable_type' => \App\Models\Page::class, 'metaable_id' => $id,
            'locale' => 'ru', 'entity_title' => 'Source', 'status' => 'pending', 'current_meta_title' => null,
            'suggested_meta_title' => 'Предложение', 'suggested_meta_description' => 'Описание']);
        $other = \App\Models\SeoMetaSuggestion::create(['locale' => 'en', 'status' => 'failed', 'error' => 'Example']);
        $component = Livewire::test(\App\Filament\Resources\SeoMetaSuggestions\Pages\ListSeoMetaSuggestions::class)
            ->assertCanSeeTableRecords([$record, $other])
            ->searchTable('Предложение')->assertCanSeeTableRecords([$record])->assertCanNotSeeTableRecords([$other])
            ->searchTable('Example')->assertCanSeeTableRecords([$other])->assertCanNotSeeTableRecords([$record])
            ->searchTable('')
            ->filterTable('locale', 'ru')->assertCanSeeTableRecords([$record])->assertCanNotSeeTableRecords([$other])
            ->resetTableFilters()
            ->callAction(\Filament\Actions\Testing\TestAction::make('approve')->table($record), data: [
                'meta_title' => 'Одобрено', 'meta_description' => 'Проверенное описание', 'seo_keywords' => 'холст',
            ])->assertHasNoErrors();
        $this->assertSame('approved', $record->fresh()->status);
        $this->assertSame(auth('filament')->id(), $record->fresh()->reviewed_by);
        $component->callAction(\Filament\Actions\Testing\TestAction::make('apply')->table($record))->assertHasNoErrors();
        $this->assertSame('applied', $record->fresh()->status);
        $this->assertSame('Base', DB::table('pages')->where('id', $id)->value('meta_title'));
        $this->assertDatabaseHas('translations', ['table_name' => 'pages', 'foreign_key' => $id, 'locale' => 'ru', 'column_name' => 'meta_title', 'value' => 'Одобрено']);
    }

    public function test_seo_inline_fields_preserve_draft_and_approve_without_applying(): void
    {
        $this->seoFixture();
        $record = \App\Models\SeoMetaSuggestion::create(['status' => 'pending', 'locale' => 'ru', 'suggested_meta_title' => 'Generated',
            'suggested_meta_description' => 'Description', 'title_field' => 'meta_title', 'description_field' => 'meta_description',
            'generated_at' => '2026-10-08 09:30:00', 'error' => 'Visible error']);
        $page = Livewire::test(\App\Filament\Resources\SeoMetaSuggestions\Pages\ListSeoMetaSuggestions::class)
            ->assertSee('Ключевые слова')->assertSee('Будут учтены при следующей генерации.')->assertSee('meta_title / meta_description')->assertSee('Visible error')->assertSee('08.10.2026 09:30')
            ->set('seoDrafts.'.$record->id.'.meta_title', 'Edited title')->set('seoDrafts.'.$record->id.'.seo_keywords', 'canvas')
            ->call('$refresh')->assertSet('seoDrafts.'.$record->id.'.meta_title', 'Edited title')
            ->call('submitSeoDraft', $record->id, 'approve')->assertHasNoErrors();
        $this->assertDatabaseHas('seo_meta_suggestions', ['id' => $record->id, 'approved_meta_title' => 'Edited title',
            'approved_meta_description' => 'Description', 'seo_keywords' => 'canvas', 'status' => 'approved', 'error' => null]);
        $this->assertNull($record->fresh()->applied_at);
    }

    public function test_seo_inline_generation_uses_keywords_and_refreshes_untouched_draft(): void
    {
        $this->seoFixture();
        \Illuminate\Support\Facades\Queue::fake();
        $record = \App\Models\SeoMetaSuggestion::create(['status' => 'new', 'locale' => 'ru']);
        $page = Livewire::test(\App\Filament\Resources\SeoMetaSuggestions\Pages\ListSeoMetaSuggestions::class)
            ->set('seoDrafts.'.$record->id.'.seo_keywords', 'canvas print')->call('submitSeoDraft', $record->id, 'generate')->assertHasNoErrors();
        $this->assertSame('canvas print', $record->fresh()->seo_keywords);
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\GenerateSeoMetaSuggestion::class);
        $record->update(['suggested_meta_title' => 'Async result', 'status' => 'pending']);
        $page->call('$refresh')->assertSet('seoDrafts.'.$record->id.'.meta_title', 'Async result')
            ->set('seoDrafts.'.$record->id.'.meta_title', str_repeat('X', 61))->call('submitSeoDraft', $record->id, 'approve')
            ->assertHasErrors(['seoDrafts.'.$record->id.'.meta_title']);
        $this->assertSame('pending', $record->fresh()->status);
    }

    public function test_seo_inline_write_requires_edit_permission(): void
    {
        $this->seoFixture(false);
        $record = \App\Models\SeoMetaSuggestion::create(['status' => 'pending', 'suggested_meta_title' => 'Generated']);
        Livewire::test(\App\Filament\Resources\SeoMetaSuggestions\Pages\ListSeoMetaSuggestions::class)
            ->set('seoDrafts.'.$record->id.'.meta_title', 'Forbidden')->call('submitSeoDraft', $record->id, 'approve')->assertForbidden();
        $this->assertNull($record->fresh()->approved_meta_title);
    }

    public function test_seo_bulk_approval_skips_invalid_and_apply_requires_approval(): void
    {
        $this->seoFixture();
        $good = \App\Models\SeoMetaSuggestion::create(['status' => 'pending', 'locale' => 'ru', 'suggested_meta_title' => 'Good']);
        $bad = \App\Models\SeoMetaSuggestion::create(['status' => 'new', 'locale' => 'en']);
        $resource = \App\Filament\Resources\SeoMetaSuggestions\SeoMetaSuggestionResource::class;
        $this->assertFalse($resource::perform($bad, 'apply', false));
        Livewire::test(\App\Filament\Resources\SeoMetaSuggestions\Pages\ListSeoMetaSuggestions::class)
            ->selectTableRecords([$good->id, $bad->id])
            ->callAction(\Filament\Actions\Testing\TestAction::make('approve')->table()->bulk())->assertHasNoErrors();
        $this->assertSame('approved', $good->fresh()->status);
        $this->assertSame('new', $bad->fresh()->status);
        $this->assertTrue($resource::perform($good->fresh(), 'reject', false));
        $this->assertSame('rejected', $good->fresh()->status);
    }

    public function test_seo_approval_limits_and_missing_source_do_not_change_entity(): void
    {
        $this->seoFixture();
        $record = \App\Models\SeoMetaSuggestion::create(['status' => 'pending', 'locale' => 'ru', 'suggested_meta_title' => str_repeat('x', 61)]);
        $resource = \App\Filament\Resources\SeoMetaSuggestions\SeoMetaSuggestionResource::class;
        $this->assertFalse($resource::perform($record, 'approve', false));
        $this->assertSame('pending', $record->fresh()->status);
        $record->update(['status' => 'approved', 'approved_meta_title' => 'Approved', 'metaable_type' => \App\Models\Page::class, 'metaable_id' => 999]);
        $this->assertFalse($resource::perform($record, 'apply', false));
        $this->assertSame('approved', $record->fresh()->status);
        $this->assertNotNull($record->fresh()->error);
        $this->assertDatabaseCount('translations', 0);
    }

    public function test_seo_scan_and_generate_queue_preserve_source_fields(): void
    {
        $this->seoFixture();
        \Illuminate\Support\Facades\Queue::fake();
        $id = DB::table('pages')->insertGetId(['title' => 'Scan source', 'meta_title' => 'Original']);
        Livewire::test(\App\Filament\Resources\SeoMetaSuggestions\Pages\ListSeoMetaSuggestions::class)
            ->callAction('scan', data: ['model' => \App\Models\Page::class, 'locale' => 'ru', 'limit' => 10, 'only_empty' => true, 'force' => false])
            ->assertHasNoErrors();
        $record = \App\Models\SeoMetaSuggestion::firstOrFail();
        $this->assertSame('new', $record->status);
        Livewire::test(\App\Filament\Resources\SeoMetaSuggestions\Pages\ListSeoMetaSuggestions::class)
            ->callAction(\Filament\Actions\Testing\TestAction::make('generate')->table($record), data: ['seo_keywords' => 'Canvas'])
            ->assertHasNoErrors();
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\GenerateSeoMetaSuggestion::class, fn ($job) => $job->suggestionId === $record->id);
        $this->mock(\App\Services\SeoMetaGeneration\SeoMetaGenerator::class, function ($mock): void {
            $mock->shouldReceive('generate')->once()->andReturn(['locales' => ['ru' => ['meta_title' => 'Generated', 'meta_description' => 'Description']], 'model' => 'fake']);
        });
        (new \App\Jobs\GenerateSeoMetaSuggestion($record->id))->handle(app(\App\Services\SeoMetaGeneration\SeoMetaModerationService::class));
        $this->assertSame('pending', $record->fresh()->status);
        $this->assertSame('Generated', $record->fresh()->suggested_meta_title);
        $this->assertSame('Original', DB::table('pages')->where('id', $id)->value('meta_title'));
    }

    public function test_seo_read_only_user_cannot_scan_generate_or_apply(): void
    {
        $this->seoFixture(false);
        \Illuminate\Support\Facades\Queue::fake();
        $record = \App\Models\SeoMetaSuggestion::create(['status' => 'approved', 'approved_meta_title' => 'Protected']);
        $this->get('/filament/seo-meta-suggestions')->assertOk();
        Livewire::test(\App\Filament\Resources\SeoMetaSuggestions\Pages\ListSeoMetaSuggestions::class)
            ->assertActionHidden('scan')
            ->assertActionHidden(\Filament\Actions\Testing\TestAction::make('generate')->table($record))
            ->assertActionHidden(\Filament\Actions\Testing\TestAction::make('apply')->table($record));
        try {
            \App\Filament\Resources\SeoMetaSuggestions\SeoMetaSuggestionResource::perform($record, 'apply');
            $this->fail('Missing permission must reject direct calls');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        \Illuminate\Support\Facades\Queue::assertNothingPushed();
        $this->assertSame('approved', $record->fresh()->status);
    }

    public function test_seo_legacy_url_redirects_and_invalid_scan_is_rejected(): void
    {
        $this->seoFixture();
        $this->get('/filament/bread/seo-meta-suggestions')->assertRedirect(
            \App\Filament\Resources\SeoMetaSuggestions\SeoMetaSuggestionResource::getUrl());
        Livewire::test(\App\Filament\Resources\SeoMetaSuggestions\Pages\ListSeoMetaSuggestions::class)
            ->callAction('scan', data: ['model' => 'InvalidModel', 'locale' => 'xx', 'limit' => 0])
            ->assertHasErrors();
        $this->assertDatabaseCount('seo_meta_suggestions', 0);
    }

    public function test_seo_generation_failure_preserves_existing_meta(): void
    {
        $this->seoFixture();
        $id = DB::table('pages')->insertGetId(['title' => 'Source', 'meta_title' => 'Unchanged']);
        $record = \App\Models\SeoMetaSuggestion::create(['metaable_type' => \App\Models\Page::class,
            'metaable_id' => $id, 'locale' => 'ru', 'status' => 'new']);
        $this->mock(\App\Services\SeoMetaGeneration\SeoMetaGenerator::class, function ($mock): void {
            $mock->shouldReceive('generate')->once()->andThrow(new \RuntimeException('Provider unavailable'));
        });
        (new \App\Jobs\GenerateSeoMetaSuggestion($record->id))->handle(app(\App\Services\SeoMetaGeneration\SeoMetaModerationService::class));
        $this->assertSame('failed', $record->fresh()->status);
        $this->assertSame('Provider unavailable', $record->fresh()->error);
        $this->assertSame('Unchanged', DB::table('pages')->where('id', $id)->value('meta_title'));
        $this->assertDatabaseCount('translations', 0);
    }

    public function test_seo_empty_approved_field_stays_empty_and_apply_snapshot_matches_source(): void
    {
        $this->seoFixture();
        $id = DB::table('pages')->insertGetId(['title' => 'Source', 'meta_title' => 'Keep source title', 'meta_description' => 'Old description']);
        $record = \App\Models\SeoMetaSuggestion::create(['metaable_type' => \App\Models\Page::class, 'metaable_id' => $id,
            'locale' => config('voyager.multilingual.default', 'en'), 'status' => 'pending',
            'suggested_meta_title' => 'Generated title', 'suggested_meta_description' => 'Generated description']);
        $page = Livewire::test(\App\Filament\Resources\SeoMetaSuggestions\Pages\ListSeoMetaSuggestions::class)
            ->set('seoDrafts.'.$record->id.'.meta_title', null)
            ->set('seoDrafts.'.$record->id.'.meta_description', 'Only description')
            ->call('submitSeoDraft', $record->id, 'approve')->assertHasNoErrors()
            ->call('$refresh')->assertSet('seoDrafts.'.$record->id.'.meta_title', '');
        $this->assertSame('', $record->fresh()->approved_meta_title);
        $page->callAction(\Filament\Actions\Testing\TestAction::make('apply')->table($record))->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_title' => 'Keep source title', 'meta_description' => 'Only description']);
        $this->assertSame('Keep source title', $record->fresh()->current_meta_title);
        $this->assertSame('Only description', $record->fresh()->current_meta_description);
    }

    public function test_seo_bulk_approval_keeps_already_reviewed_edits(): void
    {
        $this->seoFixture();
        $reviewed = \App\Models\SeoMetaSuggestion::create(['status' => 'approved', 'suggested_meta_title' => 'Generated', 'approved_meta_title' => 'Manual review']);
        $pending = \App\Models\SeoMetaSuggestion::create(['status' => 'pending', 'suggested_meta_title' => 'Ready']);
        Livewire::test(\App\Filament\Resources\SeoMetaSuggestions\Pages\ListSeoMetaSuggestions::class)
            ->selectTableRecords([$reviewed->id, $pending->id])
            ->callAction(\Filament\Actions\Testing\TestAction::make('approve')->table()->bulk())->assertHasNoErrors();
        $this->assertSame('Manual review', $reviewed->fresh()->approved_meta_title);
        $this->assertSame('Ready', $pending->fresh()->approved_meta_title);
    }

    public function test_seo_apply_and_reject_save_inline_keywords_and_validate_them(): void
    {
        $this->seoFixture();
        $id = DB::table('pages')->insertGetId(['title' => 'Source']);
        $record = \App\Models\SeoMetaSuggestion::create(['metaable_type' => \App\Models\Page::class, 'metaable_id' => $id,
            'locale' => 'ru', 'status' => 'approved', 'approved_meta_title' => 'Approved']);
        $page = Livewire::test(\App\Filament\Resources\SeoMetaSuggestions\Pages\ListSeoMetaSuggestions::class)
            ->set('seoDrafts.'.$record->id.'.seo_keywords', '  apply keywords  ')
            ->callAction(\Filament\Actions\Testing\TestAction::make('apply')->table($record))->assertHasNoErrors();
        $this->assertSame('apply keywords', $record->fresh()->seo_keywords);
        $rejected = \App\Models\SeoMetaSuggestion::create(['status' => 'pending', 'seo_keywords' => 'Saved']);
        $page->call('$refresh')->set('seoDrafts.'.$rejected->id.'.seo_keywords', str_repeat('X', 1001))
            ->callAction(\Filament\Actions\Testing\TestAction::make('reject')->table($rejected))
            ->assertHasErrors(['seoDrafts.'.$rejected->id.'.seo_keywords']);
        $this->assertSame('pending', $rejected->fresh()->status);
        $this->assertSame('Saved', $rejected->fresh()->seo_keywords);
        $page->set('seoDrafts.'.$rejected->id.'.seo_keywords', ' reject keywords ')
            ->call('callMountedAction')->assertHasNoErrors();
        $this->assertSame('rejected', $rejected->fresh()->status);
        $this->assertSame('reject keywords', $rejected->fresh()->seo_keywords);
    }

    public function test_seo_search_includes_model_label_and_scan_defaults_include_filled_entities(): void
    {
        $this->seoFixture();
        $record = \App\Models\SeoMetaSuggestion::create(['entity_label' => 'Unique model label']);
        $other = \App\Models\SeoMetaSuggestion::create(['entity_title' => 'Other']);
        Livewire::test(\App\Filament\Resources\SeoMetaSuggestions\Pages\ListSeoMetaSuggestions::class)
            ->searchTable('Unique model label')->assertCanSeeTableRecords([$record])->assertCanNotSeeTableRecords([$other])
            ->mountAction('scan')->assertSchemaStateSet(['only_empty' => false]);
    }

    public function test_seo_scan_repeat_force_and_locale_preserve_moderation_and_source(): void
    {
        $this->seoFixture();
        $id = DB::table('pages')->insertGetId(['title' => 'Filled source', 'meta_title' => 'Original title', 'meta_description' => 'Original description']);
        $page = Livewire::test(\App\Filament\Resources\SeoMetaSuggestions\Pages\ListSeoMetaSuggestions::class);
        $options = ['model' => \App\Models\Page::class, 'locale' => 'en', 'limit' => 10, 'only_empty' => false, 'force' => false];
        $page->callAction('scan', data: $options)->assertHasNoErrors();
        $record = \App\Models\SeoMetaSuggestion::firstOrFail();
        $record->update(['status' => 'approved', 'approved_meta_title' => 'Reviewed', 'seo_keywords' => 'Keep keywords']);
        DB::table('pages')->where('id', $id)->update(['meta_title' => 'Changed source']);
        $page->callAction('scan', data: $options)->assertHasNoErrors();
        $this->assertDatabaseCount('seo_meta_suggestions', 1);
        $this->assertSame('Original title', $record->fresh()->current_meta_title);
        $page->callAction('scan', data: array_replace($options, ['force' => true]))->assertHasNoErrors();
        $this->assertSame('Changed source', $record->fresh()->current_meta_title);
        $this->assertSame('approved', $record->fresh()->status);
        $this->assertSame('Reviewed', $record->fresh()->approved_meta_title);
        $this->assertSame('Keep keywords', $record->fresh()->seo_keywords);
        $page->callAction('scan', data: array_replace($options, ['locale' => 'ru']))->assertHasNoErrors();
        $this->assertDatabaseCount('seo_meta_suggestions', 2);
        $this->assertDatabaseHas('seo_meta_suggestions', ['metaable_id' => $id, 'locale' => 'ru', 'status' => 'new']);
        $this->assertDatabaseHas('pages', ['id' => $id, 'meta_title' => 'Changed source', 'meta_description' => 'Original description']);
        $this->assertDatabaseCount('translations', 0);
    }

    private function seoFixture(bool $edit = true): void
    {
        $this->admin(array_merge(['browse_admin', 'browse_seo_meta_suggestions'], $edit ? ['edit_seo_meta_suggestions'] : []));
        require_once database_path('migrations/2026_07_08_120000_create_seo_meta_suggestions_table.php');
        (new \CreateSeoMetaSuggestionsTable)->up();
        DB::table('data_types')->insert(['name' => 'seo_meta_suggestions', 'slug' => 'seo-meta-suggestions',
            'model_name' => \App\Models\SeoMetaSuggestion::class, 'display_name_plural' => 'SEO Meta']);
        Schema::table('pages', fn (Blueprint $table) => $table->string('meta_title')->nullable());
        config(['seo_meta_generation.targets' => [\App\Models\Page::class => ['label' => 'Page', 'voyager_slug' => 'pages',
            'title_field' => 'meta_title', 'description_field' => 'meta_description', 'context_fields' => ['title' => ['title']]]]]);
    }

    public function test_user_language_is_a_preference_and_preserves_other_settings(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->json('settings')->nullable());
        $typeId = DB::table('data_types')->insertGetId(['name' => 'users', 'slug' => 'users',
            'model_name' => User::class, 'display_name_plural' => 'Пользователи']);
        DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => 'email', 'type' => 'text',
            'display_name' => 'Email', 'required' => true]);
        $this->admin(['browse_admin', 'browse_users', 'edit_users']);
        $client = User::forceCreate(['email' => 'client@example.invalid', 'password' => Hash::make('unchanged'),
            'settings' => ['locale' => 'lv', 'other' => 'keep']]);
        $password = $client->password;
        $page = Livewire::test(\App\Filament\Pages\VoyagerBreadEdit::class, ['type' => 'users', 'record' => $client->id])
            ->assertSet('data.__user_locale', 'lv')->assertSee('Язык пользователя');
        $this->assertStringNotContainsString('wire:click="changeLocale(', $page->html());
        $page->set('data.__user_locale', 'ru')->call('save')->assertHasNoErrors();
        $this->assertSame(['locale' => 'ru', 'other' => 'keep'], $client->fresh()->settings);
        $this->assertSame($password, $client->fresh()->password);
        $page->set('data.__user_locale', 'invalid')->call('save')->assertHasErrors(['data.__user_locale']);
        $this->assertSame('ru', $client->fresh()->locale);
    }

    public function test_client_editor_saves_fields_and_rejects_invalid_choices(): void
    {
        $this->clientEditorMetadata();
        $client = User::forceCreate(['email' => 'client@example.invalid', 'password' => Hash::make('keep'),
            'settings' => ['locale' => 'lv'], 'news' => 'NO']);
        $password = $client->password;
        $page = Livewire::test(\App\Filament\Pages\VoyagerBreadEdit::class, ['type' => 'users', 'record' => $client->id]);
        foreach (['client_status' => 2, 'type_id' => 1, 'first_name' => 'Имя', 'last_name' => 'Фамилия',
            'bonuses' => 125, 'is_facebook_sale' => 0, 'active_coupon' => 'GIFT', 'inv_sale_code' => 'regenerate',
            'date1' => '2026-11-10', 'date2' => '2026-12-20', 'torj1' => 'День рождения', 'torj2' => 'Юбилей'] as $field => $value) {
            $page->set('data.'.$field, $value);
        }
        $page->set('data.news', 'YES')->call('save')->assertHasNoErrors();
        $saved = $client->fresh();
        $this->assertSame('NO', $saved->news);
        $this->assertSame($password, $saved->password);
        $this->assertDatabaseHas('users', ['id' => $client->id, 'client_status' => 2, 'type_id' => 1,
            'bonuses' => 125, 'is_facebook_sale' => 0, 'active_coupon' => 'GIFT', 'inv_sale_code' => 'regenerate',
            'date1' => '2026-11-10', 'date2' => '2026-12-20', 'torj1' => 'День рождения', 'torj2' => 'Юбилей']);
        $page->call('openEdit', $client->id)->set('data.client_status', 999)->call('save')->assertHasErrors(['data.client_status']);
        $page->set('data.client_status', 2)->set('data.is_facebook_sale', 'invalid')->call('save')->assertHasErrors(['data.is_facebook_sale']);
        $page->set('data.is_facebook_sale', 0)->set('data.email', 'invalid')->call('save')->assertHasErrors(['data.email']);
        $page->set('data.email', auth('filament')->user()->email)->call('save')->assertHasErrors(['data.email']);
        $page->set('data.email', 'client@example.invalid')->set('data.type_id', 999)->call('save')->assertHasErrors(['data.type_id']);
        $this->assertSame('client@example.invalid', $client->fresh()->email);
    }

    public function test_bread_policy_choices_affect_self_profile_access_without_granting_foreign_access(): void
    {
        $this->clientEditorMetadata();
        $user = auth('filament')->user();
        $typeId = DB::table('data_types')->where('slug', 'users')->value('id');
        DB::table('data_types')->where('id', $typeId)->update(['display_name_singular' => 'User', 'display_name_plural' => 'Users']);
        $permission = \App\Models\Permission::firstOrCreate(['key' => 'browse_bread'], ['table_name' => null]);
        DB::table('permission_role')->insert(['role_id' => $user->role_id, 'permission_id' => $permission->id]);
        DB::table('permission_role')->whereIn('permission_id', DB::table('permissions')->where('key', 'edit_users')->pluck('id'))->delete();
        $this->app->forgetInstance(BreadRegistry::class);
        $other = User::forceCreate(['email' => 'policy-foreign@example.invalid', 'password' => 'unused', 'role_id' => $user->role_id]);
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openSettings');
        $policy = \App\Filament\Bread\BreadPolicyOptions::USER;
        $base = \App\Filament\Bread\BreadPolicyOptions::BASE;
        $metadata->set('data.policy_name', $policy)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('data_types', ['id' => $typeId, 'policy_name' => $policy]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'users', 'record' => $user->id])->call('save')->assertHasNoErrors();
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'users', 'record' => $other->id])->assertForbidden();
        $metadata->call('openSettings')->set('data.policy_name', $base)->call('save')->assertHasNoErrors();
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'users', 'record' => $user->id])->assertForbidden();
        $metadata->call('openSettings')->set('data.policy_name', null)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('data_types', ['id' => $typeId, 'policy_name' => null]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'users', 'record' => $user->id])->assertForbidden();
        DB::table('data_types')->where('id', $typeId)->update(['policy_name' => '\\'.$policy]);
        $metadata->call('openSettings')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('data_types', ['id' => $typeId, 'policy_name' => $policy]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'users', 'record' => $user->id])->call('save')->assertHasNoErrors();
        $this->assertFalse(app(BreadRegistry::class)->permitted(app(BreadRegistry::class)->type('users'), 'delete', $user->id));
    }

    public function test_bread_policy_choices_reject_incompatible_classes_and_preserve_unknown_policy(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $id = DB::table('data_types')->where('slug', 'pages')->value('id');
        DB::table('data_types')->where('id', $id)->update(['display_name_singular' => 'Page']);
        $type = DB::table('data_types')->find($id);
        $service = app(\App\Services\Admin\BreadTypeSettingsService::class);
        foreach ([\App\Filament\Bread\BreadPolicyOptions::USER, 'App\\Policies\\UnknownPolicy', ['not-a-class']] as $invalid) {
            try {
                $service->save(auth('filament')->user(), $id, array_merge((array) $type, ['order_direction' => 'desc', 'policy_name' => $invalid]), app(\App\Services\Admin\BreadMetadataService::class)->fingerprint($type));
                $this->fail('Unsupported policy accepted');
            } catch (\Illuminate\Validation\ValidationException $error) { $this->assertArrayHasKey('policy_name', $error->errors()); }
        }
        $this->assertDatabaseHas('data_types', ['id' => $id, 'policy_name' => null]);
        DB::table('data_types')->where('id', $id)->update(['policy_name' => 'App\\Policies\\UnknownPolicy']);
        $metadata = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $id)->call('openSettings');
        $field = collect($metadata->instance()->form->getFlatComponents())->first(fn ($field) => method_exists($field, 'getName') && $field->getName() === 'policy_name');
        $this->assertTrue($field->isDisabled());
        $metadata->set('data.display_name_plural', 'Keep policy')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('data_types', ['id' => $id, 'policy_name' => 'App\\Policies\\UnknownPolicy', 'display_name_plural' => 'Keep policy']);
        $type = DB::table('data_types')->find($id);
        foreach ([null, \App\Filament\Bread\BreadPolicyOptions::BASE] as $invalid) {
            try {
                $service->save(auth('filament')->user(), $id, array_merge((array) $type, ['order_direction' => 'desc', 'policy_name' => $invalid]), app(\App\Services\Admin\BreadMetadataService::class)->fingerprint($type));
                $this->fail('Unknown policy overwritten');
            } catch (\Illuminate\Validation\ValidationException $error) { $this->assertArrayHasKey('policy_name', $error->errors()); }
        }
        $options = app(\App\Filament\Bread\BreadPolicyOptions::class);
        $this->assertFalse($options->supports((object) ['name' => 'orders'], \App\Filament\Bread\BreadPolicyOptions::BASE));
        $this->assertTrue($options->supports((object) ['name' => 'pages'], '\\'.\App\Filament\Bread\BreadPolicyOptions::BASE));
    }

    public function test_bread_controller_choices_save_compatible_classes_and_preserve_unsupported_settings(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $base = 'TCG\\Voyager\\Http\\Controllers\\VoyagerBaseController';
        DB::table('data_types')->where('id', $typeId)->update(['display_name_singular' => 'Page']);
        $page = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openSettings');
        $page->set('data.controller', $base)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('data_types', ['id' => $typeId, 'controller' => $base]);
        $page->call('openSettings')->set('data.controller', null)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('data_types', ['id' => $typeId, 'controller' => null]);
        $options = app(\App\Filament\Bread\BreadControllerOptions::class);
        $type = app(BreadRegistry::class)->type('pages');
        $this->assertFalse($options->supports($type, 'TCG\\Voyager\\Http\\Controllers\\VoyagerUserController'));
        $this->assertFalse($options->supports($type, 'App\\Http\\Controllers\\UnknownController'));
        $service = app(\App\Services\Admin\BreadTypeSettingsService::class);
        $invalid = array_merge((array) $type, ['order_direction' => 'desc']);
        $invalid['controller'] = 'TCG\\Voyager\\Http\\Controllers\\VoyagerUserController';
        try { $service->save(auth('filament')->user(), $typeId, $invalid, app(\App\Services\Admin\BreadMetadataService::class)->fingerprint($type)); $this->fail('Cross-table controller accepted'); }
        catch (\Illuminate\Validation\ValidationException $error) { $this->assertArrayHasKey('controller', $error->errors()); }
        DB::table('data_types')->where('id', $typeId)->update(['controller' => 'App\\Http\\Controllers\\UnknownController']);
        $unknown = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openSettings');
        $field = collect($unknown->instance()->form->getFlatComponents())->first(fn ($field) => method_exists($field, 'getName') && $field->getName() === 'controller');
        $this->assertTrue($field->isDisabled());
        $unknown->set('data.display_name_plural', 'Changed label')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('data_types', ['id' => $typeId, 'controller' => 'App\\Http\\Controllers\\UnknownController', 'display_name_plural' => 'Changed label']);
        $unknownType = DB::table('data_types')->find($typeId);
        $invalid = array_merge((array) $unknownType, ['order_direction' => 'desc']);
        $invalid['controller'] = null;
        try { $service->save(auth('filament')->user(), $typeId, $invalid, app(\App\Services\Admin\BreadMetadataService::class)->fingerprint($unknownType)); $this->fail('Unsupported controller cleared'); }
        catch (\Illuminate\Validation\ValidationException $error) { $this->assertArrayHasKey('controller', $error->errors()); }
        $locale = (object) ['name' => 'locales', 'controller' => '\\App\\Http\\Controllers\\Admin\\AdminLocaleController'];
        $this->assertTrue($options->supports($locale, $locale->controller));
        $this->assertArrayHasKey($locale->controller, $options->options($locale));
        $this->assertFalse($options->supports((object) ['name' => 'orders'], $base));
        $this->assertFalse($options->supports((object) ['name' => 'pages'], '\\App\\Http\\Controllers\\Admin\\AdminLocaleController'));
    }

    public function test_locale_controller_creates_language_folder_without_overwriting_files_and_preserves_updates(): void
    {
        Storage::fake('public');
        $root = Storage::disk('public')->path('fixture-languages');
        mkdir($root);
        $this->app->useLangPath($root);
        Schema::create('locales', function (Blueprint $table): void { $table->id(); $table->string('prefix'); $table->string('name'); $table->timestamps(); });
        $this->admin(['browse_admin', 'browse_locales', 'read_locales', 'add_locales', 'edit_locales']);
        $typeId = DB::table('data_types')->insertGetId(['name' => 'locales', 'slug' => 'locales', 'model_name' => \App\Models\Locale::class,
            'controller' => '\\App\\Http\\Controllers\\Admin\\AdminLocaleController']);
        foreach (['prefix', 'name'] as $field) { DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => $field, 'type' => 'text', 'required' => true, 'details' => '{}']); }
        $this->admin(['browse_admin', 'browse_bread', 'browse_locales', 'read_locales', 'add_locales', 'edit_locales']);
        Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openSettings')
            ->set('data.display_name_singular', 'Language')->set('data.display_name_plural', 'Languages')
            ->set('data.controller', '\\App\\Http\\Controllers\\Admin\\AdminLocaleController')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('data_types', ['id' => $typeId, 'controller' => 'App\\Http\\Controllers\\Admin\\AdminLocaleController']);
        $creator = Livewire::test(VoyagerBread::class, ['type' => 'locales'])->call('openCreate')
            ->set('data.prefix', 'pt-BR')->set('data.name', 'Portuguese')->call('save')->assertHasNoErrors();
        $this->assertDirectoryExists($root.'/pt-BR');
        $id = DB::table('locales')->where('prefix', 'pt-BR')->value('id');
        file_put_contents($root.'/pt-BR/messages.php', '<?php return ["key" => "Keep"];');
        Livewire::test(VoyagerBread::class, ['type' => 'locales'])->call('openCreate')
            ->set('data.prefix', 'pt-BR')->set('data.name', 'Existing folder')->call('save')->assertHasNoErrors();
        $this->assertSame('<?php return ["key" => "Keep"];', file_get_contents($root.'/pt-BR/messages.php'));
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'locales', 'record' => $id])
            ->set('data.prefix', 'pt')->call('save')->assertHasNoErrors();
        $this->assertDirectoryDoesNotExist($root.'/pt');
        $this->assertFileExists($root.'/pt-BR/messages.php');
        Livewire::test(VoyagerBread::class, ['type' => 'locales'])->call('openCreate')
            ->set('data.prefix', '../unsafe')->set('data.name', 'Unsafe')->call('save')->assertHasErrors(['data.prefix']);
        $this->assertDatabaseMissing('locales', ['prefix' => '../unsafe']);
        $this->assertDirectoryDoesNotExist(dirname($root).'/unsafe');
        file_put_contents($root.'/blocked', 'Existing file');
        Livewire::test(VoyagerBread::class, ['type' => 'locales'])->call('openCreate')
            ->set('data.prefix', 'blocked')->set('data.name', 'Blocked')->call('save')->assertHasErrors(['data.prefix']);
        $this->assertDatabaseMissing('locales', ['prefix' => 'blocked']);
        $this->assertSame('Existing file', file_get_contents($root.'/blocked'));
        $nested = Livewire::test(VoyagerBread::class, ['type' => 'locales'])->call('openCreate')->set('data.prefix', 'zz')->set('data.name', 'Rollback');
        DB::beginTransaction();
        try {
            $nested->instance()->save();
            $this->assertDirectoryExists($root.'/zz');
        } finally { DB::rollBack(); }
        $this->assertDirectoryDoesNotExist($root.'/zz');
        $this->assertDatabaseMissing('locales', ['prefix' => 'zz']);
        $populated = Livewire::test(VoyagerBread::class, ['type' => 'locales'])->call('openCreate')->set('data.prefix', 'keep')->set('data.name', 'Rollback populated');
        DB::beginTransaction();
        try {
            $populated->instance()->save();
            file_put_contents($root.'/keep/new.php', 'Keep concurrent file');
        } finally { DB::rollBack(); }
        $this->assertSame('Keep concurrent file', file_get_contents($root.'/keep/new.php'));
        $this->assertDatabaseMissing('locales', ['prefix' => 'keep']);
        // A generic section without this controller gets no extra filesystem behavior.
        DB::table('data_types')->where('id', $typeId)->update(['controller' => null]);
        Livewire::test(VoyagerBread::class, ['type' => 'locales'])->call('openCreate')
            ->set('data.prefix', 'generic')->set('data.name', 'Generic')->call('save')->assertHasNoErrors();
        $this->assertDirectoryDoesNotExist($root.'/generic');
    }

    public function test_configured_voyager_user_policy_allows_self_profile_but_denies_foreign_and_tampered_save(): void
    {
        $this->clientEditorMetadata();
        DB::table('data_types')->where('slug', 'users')->update(['policy_name' => '\\TCG\\Voyager\\Policies\\UserPolicy']);
        DB::table('permission_role')->whereIn('permission_id', DB::table('permissions')->where('key', 'edit_users')->pluck('id'))->delete();
        $user = auth('filament')->user();
        $other = User::forceCreate(['email' => 'foreign@example.invalid', 'password' => 'unused', 'role_id' => $user->role_id]);
        $type = app(BreadRegistry::class)->type('users');
        $registry = app(BreadRegistry::class);
        $this->assertTrue($registry->permitted($type, 'read', $user->id));
        $this->assertTrue($registry->permitted($type, 'edit', $user->id));
        $this->assertFalse($registry->permitted($type, 'edit', $other->id));
        $this->assertFalse($registry->permitted($type, 'edit'));
        $this->assertFalse($registry->permitted($type, 'add', $user->id));
        $this->assertFalse($registry->permitted($type, 'delete', $user->id));
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'users', 'record' => $user->id]);
        $editor->set('data.first_name', 'Own profile')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'first_name' => 'Own profile']);
        $editor->set('recordId', $other->id)->set('data.first_name', 'Forbidden')->call('save')->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $other->id, 'first_name' => null]);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'users', 'record' => $other->id])->assertForbidden();
        $viewer = Livewire::test(VoyagerBread::class, ['type' => 'users'])->call('openView', $user->id)->assertHasNoErrors();
        $viewer->call('changeLocale', 'ru')->assertHasNoErrors();
        Livewire::test(VoyagerBread::class, ['type' => 'users'])->call('openView', $other->id)->assertForbidden();
        DB::table('permission_role')->whereIn('permission_id', DB::table('permissions')->where('key', 'browse_users')->pluck('id'))->delete();
        $this->app->forgetInstance(BreadRegistry::class);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'users', 'record' => $user->id])
            ->set('data.first_name', 'Direct own profile')->call('save')->assertHasNoErrors();
        Livewire::test(VoyagerBread::class, ['type' => 'users'])->assertForbidden();
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'users', 'record' => $other->id])->assertForbidden();
        DB::table('data_types')->where('slug', 'users')->update(['policy_name' => null]);
        $this->assertFalse(app(BreadRegistry::class)->permitted(app(BreadRegistry::class)->type('users'), 'edit', $user->id));
    }

    public function test_self_edit_preserves_role_even_with_tampered_form_state(): void
    {
        $this->clientEditorMetadata();
        $user = auth('filament')->user();
        $originalRole = $user->role_id;
        $otherRole = Role::create(['name' => 'other', 'display_name' => 'Other']);
        Livewire::test(\App\Filament\Pages\VoyagerBreadEdit::class, ['type' => 'users', 'record' => $user->id])
            ->set('data.role_id', $otherRole->id)->call('save')->assertHasNoErrors();
        $this->assertSame($originalRole, $user->fresh()->role_id);
    }

    public function test_client_review_action_requires_confirmation_and_suppresses_mail(): void
    {
        $this->clientEditorMetadata();
        \Illuminate\Support\Facades\Mail::fake();
        config(['admin_migration.review_request_enabled' => false]);
        $client = User::forceCreate(['email' => 'review@example.invalid', 'password' => 'unused', 'settings' => ['locale' => 'ru']]);
        $page = Livewire::test(\App\Filament\Pages\VoyagerBreadEdit::class, ['type' => 'users', 'record' => $client->id]);
        $page->mountAction('requestClientReview')->assertActionMounted('requestClientReview');
        \Illuminate\Support\Facades\Mail::assertNothingSent();
        $page->callMountedAction()->assertHasNoErrors();
        \Illuminate\Support\Facades\Mail::assertNothingSent();
    }

    public function test_painter_assignments_sync_is_scoped_and_preserves_existing_metadata(): void
    {
        $this->clientEditorMetadata();
        auth('filament')->user()->role->permissions()->attach(Permission::create(['key' => 'edit_orders']));
        Schema::create('orders', function (Blueprint $table): void { $table->id(); $table->string('status'); });
        Schema::create('painter_orders', function (Blueprint $table): void {
            $table->id(); $table->integer('order_id'); $table->integer('user_id'); $table->integer('in_work')->nullable(); $table->timestamps();
        });
        $role = Role::create(['name' => 'painter', 'display_name' => 'Художник']);
        $painter = User::forceCreate(['email' => 'artist@example.invalid', 'password' => 'unused', 'role_id' => $role->id]);
        DB::table('orders')->insert([['id' => 1, 'status' => 'watching'], ['id' => 2, 'status' => 'watching'], ['id' => 3, 'status' => 'completed']]);
        DB::table('painter_orders')->insert([['order_id' => 1, 'user_id' => $painter->id, 'in_work' => 1], ['order_id' => 2, 'user_id' => 999, 'in_work' => 1]]);
        $page = Livewire::test(\App\Filament\Pages\VoyagerBreadEdit::class, ['type' => 'users', 'record' => $painter->id]);
        $page->callAction('painterAssignments', data: ['orders' => [1,2]])->assertHasNoErrors();
        $this->assertDatabaseHas('painter_orders', ['order_id' => 1, 'user_id' => $painter->id, 'in_work' => 1]);
        $this->assertDatabaseCount('painter_orders', 3);
        $page->callAction('painterAssignments', data: ['orders' => [1,2]])->assertHasNoErrors();
        $this->assertDatabaseCount('painter_orders', 3);
        $page->callAction('painterAssignments', data: ['orders' => [3]])->assertHasErrors(['orders']);
        $this->assertDatabaseCount('painter_orders', 3);
        $page->call('unmountAction');
        $page->callAction('painterAssignments', data: ['orders' => []])->assertHasNoErrors();
        $this->assertDatabaseCount('painter_orders', 1);
        $this->assertDatabaseHas('painter_orders', ['order_id' => 2, 'user_id' => 999]);
        $service = app(\App\Services\Admin\UserPainterAssignments::class);
        DB::table('painter_orders')->insert(['order_id' => 3, 'user_id' => $painter->id, 'in_work' => 1]);
        $service->sync(auth('filament')->user(), $painter, [3]);
        $this->assertDatabaseHas('painter_orders', ['order_id' => 3, 'user_id' => $painter->id, 'in_work' => 1]);
        try {
            $service->sync(auth('filament')->user(), $painter, [999]);
            $this->fail('Unknown order was accepted.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('orders.0', $exception->errors());
        }
        $this->assertDatabaseCount('painter_orders', 2);
        auth('filament')->user()->role->permissions()->detach();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(\App\Services\Admin\UserPainterAssignments::class)->sync(auth('filament')->user(), $painter, [1]);
    }

    private function clientEditorMetadata(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->json('settings')->nullable();
            foreach (['first_name', 'last_name', 'news', 'active_coupon', 'inv_sale_code', 'date1', 'date2', 'torj1', 'torj2'] as $field) {
                $table->string($field)->nullable();
            }
            foreach (['bonuses', 'client_status', 'type_id', 'is_facebook_sale'] as $field) { $table->integer($field)->nullable(); }
        });
        Schema::create('user_types', function (Blueprint $table): void { $table->id(); $table->string('name'); });
        DB::table('user_types')->insert([['id' => 1, 'name' => 'Новый'], ['id' => 2, 'name' => 'Постоянный']]);
        $typeId = DB::table('data_types')->insertGetId(['name' => 'users', 'slug' => 'users', 'model_name' => User::class]);
        foreach (['email', 'first_name', 'last_name', 'client_status', 'type_id', 'role_id', 'is_facebook_sale',
            'bonuses', 'active_coupon', 'inv_sale_code', 'date1', 'date2', 'torj1', 'torj2', 'news', 'password'] as $field) {
            DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => $field,
                'type' => match ($field) { 'password' => 'password', 'bonuses' => 'number', 'date1', 'date2' => 'date', default => 'text' },
                'edit' => $field !== 'news', 'display_name' => $field]);
        }
        foreach (['role_id' => ['roles', 'display_name'], 'type_id' => ['user_types', 'name']] as $column => [$table, $label]) {
            DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => 'user_'.$column.'_relationship', 'type' => 'relationship',
                'details' => json_encode(['type' => 'belongsTo', 'table' => $table, 'column' => $column, 'key' => 'id', 'label' => $label])]);
        }
        $this->admin(['browse_admin', 'browse_users', 'edit_users']);
        DB::table('roles')->update(['display_name' => 'Администратор']);
    }

    public function test_metadata_editor_updates_form_and_adds_existing_column_without_business_writes(): void
    {
        $this->admin(['browse_admin', 'browse_bread', 'browse_pages', 'edit_pages']);
        $typeId = DB::table('data_types')->where('slug', 'pages')->value('id');
        $rowId = DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->value('id');
        $id = DB::table('pages')->insertGetId(['title' => 'Keep data']);
        $page = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId)
            ->set('data.display_name', 'Новая подпись')->set('data.order', 10)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('data_rows', ['id' => $rowId, 'display_name' => 'Новая подпись', 'order' => 10]);
        Livewire::test(\App\Filament\Pages\VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])->assertSee('Новая подпись');
        $page->call('openCreate')->set('data.field', 'image')->set('data.display_name', 'Изображение')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('data_rows', ['data_type_id' => $typeId, 'field' => 'image']);
        $this->assertDatabaseHas('pages', ['id' => $id, 'title' => 'Keep data']);
        $this->assertFalse(Schema::hasColumn('pages', 'missing_column'));
        $page->call('openCreate')->set('data.field', 'missing_column')->set('data.display_name', 'Missing')->call('save')->assertHasErrors(['field']);
    }

    public function test_metadata_editor_rejects_stale_invalid_json_and_foreign_rows(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $typeId = DB::table('data_types')->insertGetId(['name' => 'pages', 'slug' => 'pages', 'model_name' => \App\Models\Page::class]);
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'title', 'type' => 'text', 'display_name' => 'Old']);
        $page = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openEdit', $rowId);
        $page->set('data.details', '{broken')->call('save')->assertHasErrors(['details']);
        DB::table('data_rows')->where('id', $rowId)->update(['display_name' => 'Other editor']);
        $page->set('data.details', '{}')->call('save')->assertHasErrors(['details']);
        $this->assertDatabaseHas('data_rows', ['id' => $rowId, 'display_name' => 'Other editor']);
        $page->call('openEdit', $rowId)->set('data.details', '{"type":"belongsTo","table":"users","column":"missing","key":"id","label":"email"}')
            ->set('data.type', 'relationship')->call('save')->assertHasErrors(['details']);
        $page->call('openCreate')->set('data.field', 'title')->set('data.display_name', 'Duplicate')->call('save')->assertHasErrors(['field']);
        auth('filament')->user()->role->permissions()->detach();
        $page->call('save')->assertForbidden();
    }

    public function test_metadata_relationships_and_secret_fields_are_validated(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $typeId = DB::table('data_types')->insertGetId(['name' => 'pages', 'slug' => 'pages', 'model_name' => \App\Models\Page::class]);
        $page = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $typeId)->call('openCreate')
            ->set('data.field', 'page_author_relationship')->set('data.display_name', 'Автор')->set('data.type', 'relationship')
            ->set('data.details', '{"type":"belongsTo","table":"users","column":"author_id","key":"id","label":"email"}')
            ->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('data_rows', ['data_type_id' => $typeId, 'field' => 'page_author_relationship']);
        $usersType = DB::table('data_types')->insertGetId(['name' => 'users', 'slug' => 'users', 'model_name' => User::class]);
        $row = DB::table('data_rows')->insertGetId(['data_type_id' => $usersType, 'field' => 'password', 'type' => 'password', 'display_name' => 'Пароль', 'browse' => false, 'read' => false]);
        $page->call('selectType', $usersType)->call('openEdit', $row)->set('data.type', 'text')->call('save')->assertHasErrors(['type']);
        $this->assertDatabaseHas('data_rows', ['id' => $row, 'type' => 'password']);
    }

    public function test_metadata_editor_validates_many_to_many_and_preserves_options(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $type = app(BreadRegistry::class)->type('pages');
        Schema::create('page_user', function (Blueprint $table) {
            $table->unsignedInteger('page_id');
            $table->unsignedInteger('user_id');
        });
        $page = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $type->id)->call('openCreate')
            ->set('data.field', 'page_users_relationship')->set('data.display_name', 'Авторы')->set('data.type', 'relationship')
            ->set('data.details', '{"type":"belongsToMany","table":"users","pivot_table":"missing_pivot","label":"email"}')
            ->call('save')->assertHasErrors(['details']);
        $page->set('data.details', '{"type":"belongsToMany","table":"users","pivot_table":"page_user","label":"email"}')
            ->call('save')->assertHasNoErrors();
        $this->assertDatabaseCount('page_user', 0);
        $row = DB::table('data_rows')->where('data_type_id', $type->id)->where('field', 'title')->first();
        $page->call('openEdit', $row->id)->set('data.type', 'select_dropdown')->set('data.details', '{"options":{"a":{"bad":"label"}}}')
            ->call('save')->assertHasErrors(['details']);
        $page->set('data.details', '{"options":{"a":"Alpha","b":"Beta"},"tab_title":"Extra","custom":{"keep":true}}')
            ->set('data.edit', false)->set('data.order', 99)->call('save')->assertHasNoErrors();
        $saved = DB::table('data_rows')->find($row->id);
        $this->assertSame('select_dropdown', $saved->type);
        $this->assertTrue(json_decode($saved->details, true)['custom']['keep']);
        $this->assertNotContains('title', array_map(fn ($r) => $r->field, app(BreadRegistry::class)->editableRows($type, 'edit')));
        $this->assertSame('title', DB::table('data_rows')->where('data_type_id', $type->id)->orderByDesc('order')->value('field'));
        $foreignType = DB::table('data_types')->insertGetId(['name' => 'users', 'slug' => 'users', 'model_name' => User::class]);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        app(\App\Services\Admin\BreadMetadataService::class)->save(auth('filament')->user(), $foreignType, $row->id, [], null);
    }

    public function test_bread_section_settings_apply_to_listing_and_keep_other_metadata(): void
    {
        $this->admin(['browse_admin', 'browse_bread', 'browse_pages', 'edit_pages']);
        $type = app(BreadRegistry::class)->type('pages');
        DB::table('data_types')->where('id', $type->id)->update(['model_name' => BreadScopedPageFixture::class,
            'details' => '{"custom":{"keep":true},"order_display_column":"title"}']);
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id(); $table->string('route')->nullable(); $table->string('url')->nullable();
        });
        DB::table('menu_items')->insert([
            ['route' => 'voyager.pages.index', 'url' => '/admin/pages?type=2'],
            ['route' => 'voyager.pages-other.index', 'url' => '/admin/pages-other'],
        ]);
        $z = DB::table('pages')->insertGetId(['title' => 'Zulu', 'meta_description' => 'Visible']);
        $a = DB::table('pages')->insertGetId(['title' => 'Alpha', 'meta_description' => 'Visible']);
        DB::table('pages')->insert(['title' => 'Hidden', 'meta_description' => 'Other']);
        $page = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $type->id)->call('openSettings')
            ->set('data.display_name_singular', 'Страница')->set('data.display_name_plural', 'Наши страницы')
            ->set('data.slug', 'our-pages')->set('data.icon', 'heroicon-o-document')->set('data.description', 'Описание раздела')
            ->set('data.order_column', 'title')->set('data.order_direction', 'asc')->set('data.default_search_key', 'meta_description')
            ->set('data.scope', 'visibleForBread')->call('save')->assertHasNoErrors();
        $saved = DB::table('data_types')->find($type->id);
        $details = json_decode($saved->details, true);
        $this->assertTrue($details['custom']['keep']);
        $this->assertSame('title', $details['order_display_column']);
        $this->assertSame(BreadScopedPageFixture::class, $saved->model_name);
        $this->assertDatabaseHas('menu_items', ['route' => 'voyager.our-pages.index', 'url' => '/admin/our-pages?type=2']);
        $this->assertDatabaseHas('menu_items', ['route' => 'voyager.pages-other.index', 'url' => '/admin/pages-other']);
        $this->assertDatabaseCount('pages', 3);
        $this->assertDatabaseCount('data_rows', 2);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'our-pages', 'record' => $a])->assertSee('Редактирование: Страница #'.$a);
        $this->assertArrayNotHasKey('withTranslations', app(\App\Services\Admin\BreadTypeSettingsService::class)->scopes($saved));
        $list = Livewire::test(VoyagerBread::class, ['type' => 'our-pages'])->assertSee('Наши страницы')->assertSee('Описание раздела');
        $this->assertSame([$a, $z], $list->instance()->records()['rows']->pluck('id')->all());
        $list->set('search', 'Visible');
        $this->assertSame(2, $list->instance()->records()['rows']->total());
        $list->set('search', 'Alpha');
        $this->assertSame(0, $list->instance()->records()['rows']->total());
    }

    public function test_bread_section_settings_reject_invalid_stale_and_unauthorized_changes(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $type = app(BreadRegistry::class)->type('pages');
        $page = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('selectType', $type->id)->call('openSettings')
            ->set('data.display_name_singular', 'Page')->set('data.slug', '../bad')->call('save')->assertHasErrors(['slug']);
        $page->set('data.slug', 'pages')->set('data.order_column', 'missing')->call('save')->assertHasErrors();
        $page->set('data.order_column', 'title')->set('data.scope', 'delete')->call('save')->assertHasErrors();
        $page->set('data.scope', null)->set('data.default_search_key', 'password')->call('save')->assertHasErrors();
        DB::table('data_types')->insert(['name' => 'users', 'slug' => 'other', 'model_name' => User::class]);
        $page->set('data.default_search_key', null)->set('data.slug', 'other')->call('save')->assertHasErrors(['slug']);
        DB::table('data_types')->where('id', $type->id)->update(['description' => 'Other editor']);
        $page->set('data.slug', 'pages')->call('save')->assertHasErrors(['slug']);
        $this->assertDatabaseHas('data_types', ['id' => $type->id, 'description' => 'Other editor', 'slug' => 'pages']);
        auth('filament')->user()->role->permissions()->detach();
        $page->call('save')->assertForbidden();
    }

    public function test_bread_creation_prepares_schema_fields_menu_and_grants_creator_admin_access(): void
    {
        $this->admin(['browse_admin', 'browse_bread', 'browse_stocks', 'edit_stocks']);
        $this->creationTables();
        $id = DB::table('stocks')->insertGetId(['title' => 'Unchanged']);
        $grants = DB::table('permission_role')->count();
        $page = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('openSectionCreation', 'stocks');
        $rows = $page->get('data.rows');
        foreach ($rows as $key => $row) {
            if ($row['field'] === 'title') {
                $page->set('data.rows.'.$key.'.display_name', 'Заголовок акции')->set('data.rows.'.$key.'.type', 'text_area');
            }
        }
        $page->set('data.display_name_plural', 'Акции')
            ->set('data.controller', 'TCG\\Voyager\\Http\\Controllers\\VoyagerBaseController')
            ->set('data.policy_name', \App\Filament\Bread\BreadPolicyOptions::BASE)
            ->call('save')->assertHasNoErrors();
        $type = app(BreadRegistry::class)->type('stocks');
        $page->assertRedirect(\App\Filament\Pages\BreadMetadata::getUrl().'?section='.$type->id);
        $this->assertSame('TCG\\Voyager\\Http\\Controllers\\VoyagerBaseController', $type->controller);
        $this->assertSame(\App\Filament\Bread\BreadPolicyOptions::BASE, $type->policy_name);
        $this->assertSame(\App\Models\Stock::class, $type->model_name);
        $this->assertDatabaseCount('stocks', 1);
        $this->assertDatabaseHas('stocks', ['id' => $id, 'title' => 'Unchanged']);
        $this->assertSame($grants + 3, DB::table('permission_role')->count());
        foreach (['browse', 'read', 'add', 'edit', 'delete'] as $action) {
            $this->assertSame(1, DB::table('permissions')->where('key', $action.'_stocks')->count());
            $this->assertDatabaseHas('permission_role', ['role_id' => auth('filament')->user()->role_id,
                'permission_id' => DB::table('permissions')->where('key', $action.'_stocks')->value('id')]);
        }
        $this->assertDatabaseHas('menu_items', ['title' => 'Акции', 'route' => 'voyager.stocks.index', 'order' => 5]);
        $this->assertDatabaseHas('data_rows', ['data_type_id' => $type->id, 'field' => 'title', 'type' => 'text_area', 'display_name' => 'Заголовок акции']);
        $this->assertDatabaseHas('data_rows', ['data_type_id' => $type->id, 'field' => 'id', 'add' => false, 'edit' => false]);
        $this->assertDatabaseHas('data_rows', ['data_type_id' => $type->id, 'field' => 'remember_token', 'browse' => false, 'read' => false, 'add' => false, 'edit' => false]);
        $this->assertDatabaseCount('data_rows', 6);
        Livewire::test(VoyagerBread::class, ['type' => 'stocks'])->assertSee('Unchanged');
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'stocks', 'record' => $id])->assertSee('Заголовок акции');
        $this->assertContains('Акции', array_map(fn ($item) => $item->getLabel(), VoyagerBread::getNavigationItems()));
    }

    public function test_bread_creation_does_not_grant_other_roles_or_non_admin_creator(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $actor = auth('filament')->user();
        $other = Role::create(['name' => 'editor']);
        $actor->roles()->attach($other);
        $actor->role()->update(['name' => 'metadata_manager']);
        $this->creationTables();
        $service = app(\App\Services\Admin\BreadCreationService::class);
        $values = ['name' => 'stocks', 'model_name' => \App\Models\Stock::class, 'slug' => 'stocks',
            'display_name_singular' => 'Stock', 'display_name_plural' => 'Stocks',
            'generate_permissions' => true, 'add_menu' => false, 'rows' => $service->defaults('stocks')];
        $before = DB::table('permission_role')->count();
        $service->create($actor, $values, $service->snapshot('stocks'));
        $this->assertSame($before, DB::table('permission_role')->count());
        $this->assertDatabaseHas('permissions', ['key' => 'add_stocks']);
        $this->assertDatabaseMissing('permission_role', ['role_id' => $other->id]);
    }

    public function test_bread_creation_rejects_invalid_model_stale_schema_and_rolls_back_field_errors(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $this->creationTables();
        $service = app(\App\Services\Admin\BreadCreationService::class);
        $values = ['name' => 'stocks', 'model_name' => \App\Models\Stock::class, 'slug' => 'stocks',
            'display_name_singular' => 'Stock', 'display_name_plural' => 'Stocks', 'icon' => null, 'description' => null,
            'generate_permissions' => true, 'add_menu' => true, 'rows' => $service->defaults('stocks')];
        $snapshot = $service->snapshot('stocks');
        foreach ([['model_name' => User::class], ['slug' => 'pages'], ['rows' => []]] as $invalid) {
            try { $service->create(auth('filament')->user(), array_replace($values, $invalid), $snapshot); $this->fail('Invalid creation accepted'); }
            catch (\Illuminate\Validation\ValidationException) { $this->assertDatabaseMissing('data_types', ['name' => 'stocks']); }
        }
        $badRows = $values['rows'];
        $badRows[1]['details'] = '{broken';
        try { $service->create(auth('filament')->user(), array_replace($values, ['rows' => $badRows]), $snapshot); $this->fail('Invalid field accepted'); }
        catch (\Illuminate\Validation\ValidationException) {
            $this->assertDatabaseMissing('data_types', ['name' => 'stocks']);
            $this->assertDatabaseCount('data_rows', 2);
            $this->assertDatabaseMissing('permissions', ['key' => 'browse_stocks']);
            $this->assertDatabaseCount('menu_items', 1);
        }
        Schema::table('stocks', fn (Blueprint $table) => $table->string('new_column')->nullable());
        try { $service->create(auth('filament')->user(), $values, $snapshot); $this->fail('Stale schema accepted'); }
        catch (\Illuminate\Validation\ValidationException) { $this->assertDatabaseMissing('data_types', ['name' => 'stocks']); }
        $values['rows'] = $service->defaults('stocks');
        $values['generate_permissions'] = $values['add_menu'] = false;
        $service->create(auth('filament')->user(), $values, $service->snapshot('stocks'));
        $this->assertDatabaseMissing('permissions', ['key' => 'browse_stocks']);
        $this->assertDatabaseCount('menu_items', 1);
        try { $service->create(auth('filament')->user(), $values, $service->snapshot('stocks')); $this->fail('Duplicate creation accepted'); }
        catch (\Illuminate\Validation\ValidationException) { $this->assertSame(1, DB::table('data_types')->where('name', 'stocks')->count()); }
        auth('filament')->user()->role->permissions()->detach();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $service->create(auth('filament')->user(), $values, $service->snapshot('stocks'));
    }

    public function test_bread_creation_rejects_incompatible_classes_and_rolls_back_valid_choices_with_bad_fields(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $this->creationTables();
        $service = app(\App\Services\Admin\BreadCreationService::class);
        $values = ['name' => 'stocks', 'model_name' => \App\Models\Stock::class, 'slug' => 'stocks',
            'display_name_singular' => 'Stock', 'display_name_plural' => 'Stocks',
            'generate_permissions' => true, 'add_menu' => true, 'rows' => $service->defaults('stocks')];
        $snapshot = $service->snapshot('stocks');
        $grants = DB::table('permission_role')->count();
        foreach (['controller' => ['TCG\\Voyager\\Http\\Controllers\\VoyagerUserController', 'App\\UnknownController', ['invalid']],
            'policy_name' => [\App\Filament\Bread\BreadPolicyOptions::USER, 'App\\UnknownPolicy', ['invalid']]] as $field => $invalids) {
            foreach ($invalids as $invalid) {
                try { $service->create(auth('filament')->user(), [...$values, $field => $invalid], $snapshot); $this->fail('Incompatible class accepted'); }
                catch (\Illuminate\Validation\ValidationException $error) {
                    $this->assertArrayHasKey($field, $error->errors());
                    $this->assertDatabaseMissing('data_types', ['name' => 'stocks']);
                }
            }
        }
        $values['controller'] = '\\TCG\\Voyager\\Http\\Controllers\\VoyagerBaseController';
        $values['policy_name'] = '\\'.\App\Filament\Bread\BreadPolicyOptions::BASE;
        $badRows = $values['rows'];
        $badRows[1]['details'] = '{broken';
        try { $service->create(auth('filament')->user(), [...$values, 'rows' => $badRows], $snapshot); $this->fail('Invalid fields accepted'); }
        catch (\Illuminate\Validation\ValidationException $error) {
            $this->assertArrayHasKey('rows', $error->errors());
            $this->assertDatabaseMissing('data_types', ['name' => 'stocks']);
            $this->assertDatabaseCount('data_rows', 2);
            $this->assertDatabaseMissing('permissions', ['key' => 'browse_stocks']);
            $this->assertDatabaseCount('menu_items', 1);
        }
        $id = $service->create(auth('filament')->user(), $values, $snapshot);
        $this->assertDatabaseHas('data_types', ['id' => $id, 'controller' => ltrim($values['controller'], '\\'),
            'policy_name' => \App\Filament\Bread\BreadPolicyOptions::BASE]);
        $this->assertSame($grants + 5, DB::table('permission_role')->count());
    }

    public function test_bread_creation_offers_user_controller_and_policy_in_wizard(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $grants = DB::table('permission_role')->count();
        $page = Livewire::test(\App\Filament\Pages\BreadMetadata::class)->call('openSectionCreation', 'users');
        $fields = collect($page->instance()->form->getFlatComponents())
            ->filter(fn ($field) => $field instanceof \Filament\Forms\Components\Select)->keyBy(fn ($field) => $field->getName());
        $controller = $fields['controller'];
        $policy = $fields['policy_name'];
        $this->assertArrayHasKey('TCG\\Voyager\\Http\\Controllers\\VoyagerUserController', $controller->getOptions());
        $this->assertArrayHasKey(\App\Filament\Bread\BreadPolicyOptions::USER, $policy->getOptions());
        $page->set('data.controller', 'TCG\\Voyager\\Http\\Controllers\\VoyagerUserController')
            ->set('data.policy_name', \App\Filament\Bread\BreadPolicyOptions::USER)
            ->set('data.add_menu', false)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('data_types', ['name' => 'users',
            'controller' => 'TCG\\Voyager\\Http\\Controllers\\VoyagerUserController',
            'policy_name' => \App\Filament\Bread\BreadPolicyOptions::USER]);
        $this->assertSame($grants + 5, DB::table('permission_role')->count());
    }

    public function test_bread_creation_requires_primary_id_and_available_admin_menu(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $this->creationTables();
        $service = app(\App\Services\Admin\BreadCreationService::class);
        $this->assertContains('stocks', $service->tables());
        $this->assertNotContains('pages', $service->tables());
        $values = ['name' => 'stocks', 'model_name' => \App\Models\Stock::class, 'slug' => 'stocks',
            'display_name_singular' => 'Stock', 'display_name_plural' => 'Stocks',
            'generate_permissions' => true, 'add_menu' => true, 'rows' => $service->defaults('stocks')];
        DB::table('menus')->delete();
        try { $service->create(auth('filament')->user(), $values, $service->snapshot('stocks')); $this->fail('Missing menu accepted'); }
        catch (\Illuminate\Validation\ValidationException $error) {
            $this->assertArrayHasKey('add_menu', $error->errors());
            $this->assertDatabaseMissing('data_types', ['name' => 'stocks']);
        }
        Schema::drop('stocks');
        Schema::create('stocks', function (Blueprint $table) { $table->integer('id'); $table->string('title')->nullable(); });
        $values['rows'] = $service->defaults('stocks');
        $values['add_menu'] = false;
        try { $service->create(auth('filament')->user(), $values, $service->snapshot('stocks')); $this->fail('Missing primary accepted'); }
        catch (\Illuminate\Validation\ValidationException $error) {
            $this->assertArrayHasKey('name', $error->errors());
            $this->assertDatabaseMissing('data_types', ['name' => 'stocks']);
        }
    }

    public function test_bread_table_catalog_lists_configured_and_unconfigured_tables_and_checks_browse_access(): void
    {
        $this->admin(['browse_admin', 'browse_bread', 'browse_pages']);
        $this->creationTables();
        $page = Livewire::test(\App\Filament\Pages\BreadTables::class)->assertSee('Все таблицы BREAD')->assertSee('stocks');
        $tables = collect($page->instance()->tables())->keyBy('name');
        $this->assertTrue($tables['pages']['configured']);
        $this->assertTrue($tables['pages']['available']);
        $this->assertSame(VoyagerBread::getUrl(['type' => 'pages']), $tables['pages']['browse_url']);
        $this->assertStringContainsString('?section=', $tables['pages']['edit_url']);
        $this->assertFalse($tables['stocks']['configured']);
        $this->assertStringEndsWith('?create=stocks', $tables['stocks']['create_url']);
        $this->assertNull($tables['stocks']['edit_url']);
        $page->set('search', 'PAGES');
        $this->assertSame(['pages'], array_column($page->instance()->tables(), 'name'));
        $page->set('search', 'nonexistent')->assertSee('Таблицы не найдены.');
        auth('filament')->user()->role->permissions()->where('key', 'browse_pages')->first()->pivot->delete();
        auth('filament')->user()->role->unsetRelation('permissions');
        app()->forgetInstance(BreadRegistry::class);
        $page->set('search', 'pages');
        $this->assertNull($page->instance()->tables()[0]['browse_url']);
        auth('filament')->user()->role->permissions()->detach();
        $page->call('tables')->assertForbidden();
    }

    public function test_bread_catalog_links_select_existing_section_and_prepare_new_section(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $this->creationTables();
        $this->roleMetadata();
        $roleType = DB::table('data_types')->where('name', 'roles')->value('id');
        Livewire::withQueryParams(['section' => $roleType])->test(\App\Filament\Pages\BreadMetadata::class)
            ->assertSet('typeId', $roleType)->assertSee('display_name');
        Livewire::withQueryParams(['create' => 'stocks'])->test(\App\Filament\Pages\BreadMetadata::class)
            ->assertSet('creatingSection', true)->assertSet('creationTable', 'stocks');
        $this->assertDatabaseMissing('data_types', ['name' => 'stocks']);
    }

    public function test_full_bread_editor_saves_settings_and_fields_atomically_and_rejects_stale_rows(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $type = DB::table('data_types')->where('name', 'pages')->first();
        $timestampId = DB::table('data_rows')->insertGetId(['data_type_id' => $type->id, 'field' => 'created_at', 'type' => 'timestamp', 'display_name' => 'Created At', 'order' => 3, 'add' => false, 'edit' => true]);
        $row = DB::table('data_rows')->where('data_type_id', $type->id)->where('field', 'title')->first();
        $page = Livewire::withQueryParams(['section' => $type->id])->test(\App\Filament\Pages\BreadMetadata::class)
            ->assertSet('fullEditor', true)->assertSee('Редактирование полей BREAD')
            ->set('data.display_name_singular', 'Page')->set('data.display_name_plural', 'Updated pages')
            ->set('data.order_display_column', 'title')
            ->set('fieldRows.'.$row->id.'.display_name', 'Updated title')
            ->set('fieldRows.'.$timestampId.'.display_name', 'Legacy timestamp label')
            ->set('fieldRows.'.$row->id.'.details', '{bad')->call('save')->assertHasErrors();
        $this->assertDatabaseHas('data_types', ['id' => $type->id, 'display_name_plural' => 'Pages']);
        $this->assertDatabaseHas('data_rows', ['id' => $row->id, 'display_name' => $row->display_name]);
        $page->set('fieldRows.'.$row->id.'.details', '{}')->call('save')->assertHasNoErrors()->assertSet('fullEditor', true);
        $this->assertDatabaseHas('data_types', ['id' => $type->id, 'display_name_plural' => 'Updated pages']);
        $this->assertSame('title', json_decode(DB::table('data_types')->where('id', $type->id)->value('details'), true)['order_display_column']);
        $this->assertDatabaseHas('data_rows', ['id' => $row->id, 'display_name' => 'Updated title']);
        $this->assertDatabaseHas('data_rows', ['id' => $timestampId, 'display_name' => 'Legacy timestamp label', 'edit' => true]);
        DB::table('data_rows')->where('id', $row->id)->update(['display_name' => 'Other editor']);
        $page->set('data.display_name_plural', 'Must not save')->call('save')->assertHasErrors(['fieldRows']);
        $this->assertDatabaseHas('data_types', ['id' => $type->id, 'display_name_plural' => 'Updated pages']);
        $page->call('openFullEditor')->set('fieldRows', [])->call('save')->assertHasErrors(['fieldRows']);
        auth('filament')->user()->role->permissions()->detach();
        $page->call('save')->assertForbidden();
    }

    public function test_full_bread_editor_preserves_language_drafts_reorders_and_adds_unconfigured_columns(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $rowId = DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->value('id');
        DB::table('translations')->insert(['table_name' => 'data_rows', 'column_name' => 'display_name', 'foreign_key' => $rowId, 'locale' => 'ru', 'value' => 'Старый заголовок']);
        $page = Livewire::withQueryParams(['section' => $typeId])->test(\App\Filament\Pages\BreadMetadata::class)
            ->assertSee('Ещё не настроено в BREAD')->set('data.display_name_singular', 'Page')
            ->set('fieldRows.'.$rowId.'.display_name', 'English title')
            ->call('switchMetadataLanguage', 'ru')->assertSet('fieldRows.'.$rowId.'.display_name', 'Старый заголовок')
            ->set('data.display_name_singular', 'Страница')->set('data.display_name_plural', 'Страницы')
            ->set('fieldRows.'.$rowId.'.display_name', 'Заголовок')
            ->set('fieldRows.new_image.display_name', 'Изображение')
            ->call('moveField', 'new_image', (string) $rowId)->assertSet('fieldRows.new_image.order', 1)
            ->call('switchMetadataLanguage', 'en')->assertSet('fieldRows.'.$rowId.'.display_name', 'English title')
            ->assertSet('fieldRows.new_image.display_name', 'Image')->call('save')->assertHasNoErrors();
        $imageId = DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'image')->value('id');
        $this->assertDatabaseHas('data_rows', ['id' => $imageId, 'order' => 1, 'display_name' => 'Image']);
        $this->assertDatabaseHas('data_types', ['id' => $typeId, 'display_name_singular' => 'Page', 'display_name_plural' => 'Pages']);
        $this->assertDatabaseHas('translations', ['table_name' => 'data_types', 'foreign_key' => $typeId, 'column_name' => 'display_name_plural', 'locale' => 'ru', 'value' => 'Страницы']);
        $this->assertDatabaseHas('translations', ['table_name' => 'data_rows', 'foreign_key' => $rowId, 'column_name' => 'display_name', 'locale' => 'ru', 'value' => 'Заголовок']);
        $this->assertDatabaseHas('translations', ['table_name' => 'data_rows', 'foreign_key' => $imageId, 'column_name' => 'display_name', 'locale' => 'ru', 'value' => 'Изображение']);
        $this->assertCount(count(Schema::getColumnListing('pages')), DB::table('data_rows')->where('data_type_id', $typeId)->get());
        $page->call('switchMetadataLanguage', 'ru')->set('data.display_name_plural', 'Сохранение из RU')
            ->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('data_types', ['id' => $typeId, 'display_name_plural' => 'Pages']);
        $this->assertDatabaseHas('translations', ['table_name' => 'data_types', 'foreign_key' => $typeId, 'column_name' => 'display_name_plural', 'locale' => 'ru', 'value' => 'Сохранение из RU']);
        app()->setLocale('ru');
        $translatedType = app(BreadRegistry::class)->type('pages');
        $this->assertSame('Сохранение из RU', $translatedType->display_name_plural);
        $this->assertSame('Заголовок', collect(app(BreadRegistry::class)->rows($translatedType))->firstWhere('field', 'title')->display_name);
    }

    public function test_full_bread_editor_rejects_stale_translations_and_rolls_back_new_columns(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $page = Livewire::withQueryParams(['section' => $typeId])->test(\App\Filament\Pages\BreadMetadata::class)
            ->set('data.display_name_singular', 'Page')->set('data.display_name_plural', 'Must roll back');
        DB::table('translations')->insert(['table_name' => 'data_types', 'column_name' => 'display_name_plural', 'foreign_key' => $typeId, 'locale' => 'ru', 'value' => 'Concurrent translation']);
        $page->call('save')->assertHasErrors(['metadataTranslations']);
        $this->assertDatabaseCount('data_rows', 2);
        $this->assertDatabaseHas('data_types', ['id' => $typeId, 'display_name_plural' => 'Pages']);
        $this->assertDatabaseHas('translations', ['value' => 'Concurrent translation']);
    }

    public function test_bread_relationship_constructor_stages_supported_relation_and_validates_columns(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $page = Livewire::withQueryParams(['section' => $typeId])->test(\App\Filament\Pages\BreadMetadata::class)
            ->set('data.display_name_singular', 'Page')->call('openRelationship')
            ->set('relationship', ['type' => 'belongsTo', 'table' => 'users', 'model' => User::class,
                'column' => 'missing', 'key' => 'id', 'label' => 'email', 'pivot_table' => '', 'taggable' => false])
            ->call('stageRelationship')->assertHasErrors();
        $page->set('relationship.column', 'author_id')->call('stageRelationship')->assertHasNoErrors()->assertSet('relationshipEditor', false);
        $this->assertDatabaseCount('data_rows', 2);
        $page->call('save')->assertHasNoErrors();
        $relation = DB::table('data_rows')->where('data_type_id', $typeId)->where('type', 'relationship')->first();
        $details = json_decode($relation->details, true);
        $this->assertSame('users', $details['table']);
        $this->assertSame('author_id', $details['column']);
        $this->assertSame(User::class, $details['model']);
        $page->call('openRelationship', (string) $relation->id)->assertSet('relationship.label', 'email')
            ->set('relationship.type', 'hasMany')->call('stageRelationship')->assertHasErrors();
        $page->set('relationship.type', 'belongsTo')->set('relationship.label', 'password')->call('stageRelationship')->assertHasErrors();
        $page->call('removeRelationship', (string) $relation->id);
        $this->assertDatabaseHas('data_rows', ['id' => $relation->id]);
        $page->call('save')->assertHasNoErrors();
        $this->assertDatabaseMissing('data_rows', ['id' => $relation->id]);
    }

    public function test_bread_settings_can_generate_permission_definitions_without_changing_role_grants(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('generate_permissions')->default(false));
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $grants = DB::table('permission_role')->count();
        $page = Livewire::withQueryParams(['section' => $typeId])->test(\App\Filament\Pages\BreadMetadata::class)
            ->set('data.display_name_singular', 'Page')->set('data.generate_permissions', true)->call('save')->assertHasNoErrors();
        foreach (['browse', 'read', 'add', 'edit', 'delete'] as $action) { $this->assertDatabaseHas('permissions', ['key' => $action.'_pages', 'table_name' => 'pages']); }
        $this->assertSame($grants, DB::table('permission_role')->count());
        $this->assertDatabaseHas('data_types', ['id' => $typeId, 'generate_permissions' => true]);
        $page->set('data.generate_permissions', false)->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('data_types', ['id' => $typeId, 'generate_permissions' => false]);
        $this->assertSame(5, DB::table('permissions')->where('table_name', 'pages')->count());
        $page->set('data.model_name', User::class)->call('save')->assertHasErrors(['data.model_name']);
    }

    public function test_custom_pivot_keys_preserve_string_values_across_forms_sync_and_display(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id(); $table->string('code')->unique(); $table->string('name');
        });
        Schema::create('custom_links', function (Blueprint $table): void {
            $table->string('owner_ref'); $table->string('tag_ref');
        });
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages', 'add_categories', 'browse_bread']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $id = DB::table('pages')->insertGetId(['title' => 'owner-A']);
        DB::table('pages')->insert(['title' => 'owner-B']);
        DB::table('categories')->insert([
            ['code' => '001', 'name' => 'First custom category'],
            ['code' => 'blue', 'name' => 'Second custom category'],
        ]);
        DB::table('custom_links')->insert([
            ['owner_ref' => 'owner-A', 'tag_ref' => '001'],
            ['owner_ref' => 'owner-B', 'tag_ref' => 'blue'],
        ]);
        $details = ['type' => 'belongsToMany', 'table' => 'categories', 'model' => BreadTagFixture::class,
            'label' => 'name', 'key' => 'code', 'parent_key' => 'title', 'pivot_table' => 'custom_links',
            'foreign_pivot_key' => 'owner_ref', 'related_pivot_key' => 'tag_ref', 'taggable' => 'on'];
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'custom_categories',
            'display_name' => 'Custom categories', 'type' => 'relationship', 'details' => json_encode($details),
            'browse' => true, 'read' => true, 'add' => true, 'edit' => true]);
        // Parent key must be selected even if its physical column is hidden in browse.
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['browse' => false]);
        $registry = app(BreadRegistry::class);
        app(\App\Services\Admin\BreadMetadataService::class)->validateRelationship($registry->type('pages'), $details);
        $editor = Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->assertSet('data.__pivot_'.$rowId, ['001']);
        $field = collect($editor->instance()->form->getFlatComponents())->first(fn ($field) => $field->getName() === '__pivot_'.$rowId);
        $this->assertSame('First custom category', $field->getOptions()['001']);
        $this->assertSame('Second custom category', $field->getOptions()['blue']);
        $editor->set('data.__pivot_'.$rowId, ['001', 'blue'])->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('custom_links', ['owner_ref' => 'owner-A', 'tag_ref' => '001']);
        $this->assertDatabaseHas('custom_links', ['owner_ref' => 'owner-A', 'tag_ref' => 'blue']);
        $this->assertDatabaseHas('custom_links', ['owner_ref' => 'owner-B', 'tag_ref' => 'blue']);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        $record = $page->instance()->records()['rows']->firstWhere('id', $id);
        $this->assertSame('First custom category, Second custom category', $record->custom_categories);
        $page->call('openView', $id)->assertSee('First custom category')->assertSee('Second custom category');
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->set('data.__pivot_'.$rowId, ['missing'])->call('save')->assertHasErrors();
        $this->assertDatabaseHas('custom_links', ['owner_ref' => 'owner-A', 'tag_ref' => '001']);
        $categoryType = DB::table('data_types')->insertGetId(['name' => 'categories', 'slug' => 'categories', 'model_name' => BreadTagFixture::class]);
        DB::table('data_rows')->insert([
            ['data_type_id' => $categoryType, 'field' => 'name', 'type' => 'text', 'required' => true, 'details' => '{}'],
            ['data_type_id' => $categoryType, 'field' => 'code', 'type' => 'text', 'required' => true, 'details' => '{"default":"03"}'],
        ]);
        $createdKey = app(\App\Services\Admin\BreadTagCreationService::class)->create('pages', $rowId, $id, ['label' => 'Tagged custom category']);
        $this->assertSame('03', $createdKey);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->set('data.__pivot_'.$rowId, [$createdKey])->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('custom_links', ['owner_ref' => 'owner-A', 'tag_ref' => '03']);
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => $id])
            ->set('data.__pivot_'.$rowId, [])->call('save')->assertHasNoErrors();
        $this->assertDatabaseMissing('custom_links', ['owner_ref' => 'owner-A']);
        $this->assertDatabaseHas('custom_links', ['owner_ref' => 'owner-B', 'tag_ref' => 'blue']);
        foreach (['parent_key', 'key', 'foreign_pivot_key', 'related_pivot_key'] as $key) {
            try {
                app(\App\Services\Admin\BreadMetadataService::class)->validateRelationship($registry->type('pages'), array_replace($details, [$key => 'missing_column']));
                $this->fail('Missing custom column accepted');
            } catch (\Illuminate\Validation\ValidationException) { $this->assertTrue(true); }
        }
        Livewire::withQueryParams(['section' => $typeId])->test(\App\Filament\Pages\BreadMetadata::class)
            ->set('data.display_name_singular', 'Page')->call('openRelationship', (string) $rowId)
            ->assertSet('relationship.parent_key', 'title')->assertSet('relationship.related_pivot_key', 'tag_ref')
            ->set('relationship.table', 'users')->set('relationship.model', User::class)
            ->set('relationship.key', 'id')->set('relationship.label', 'email')
            ->call('stageRelationship')->assertHasNoErrors()->call('save')->assertHasNoErrors();
        $this->assertSame('owner_ref', json_decode(DB::table('data_rows')->where('id', $rowId)->value('details'), true)['foreign_pivot_key']);
    }

    public function test_generic_client_search_finds_visible_related_labels_without_duplicate_parents(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        Schema::create('categories', function (Blueprint $table): void { $table->id(); $table->string('name'); });
        Schema::create('category_page', function (Blueprint $table): void { $table->id(); $table->unsignedBigInteger('page_id'); $table->unsignedBigInteger('category_id'); });
        Schema::create('page_notes', function (Blueprint $table): void { $table->id(); $table->string('page_code')->nullable(); $table->string('label'); $table->softDeletes(); });
        DB::table('pages')->insert([['id' => 1, 'title' => 'alpha'], ['id' => 2, 'title' => 'beta']]);
        DB::table('categories')->insert([['id' => 11, 'name' => 'Ocean art'], ['id' => 12, 'name' => 'Ocean blue'], ['id' => 13, 'name' => 'Unlinked tag']]);
        DB::table('category_page')->insert([['page_id' => 1, 'category_id' => 11], ['page_id' => 1, 'category_id' => 12]]);
        DB::table('page_notes')->insert([
            ['page_code' => 'alpha', 'label' => 'First note', 'deleted_at' => null],
            ['page_code' => 'alpha', 'label' => 'Second note', 'deleted_at' => null],
            ['page_code' => 'beta', 'label' => 'Beta note', 'deleted_at' => null],
            ['page_code' => 'alpha', 'label' => 'Deleted note', 'deleted_at' => now()],
            ['page_code' => null, 'label' => 'Unassigned note', 'deleted_at' => null],
        ]);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $tagRow = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'tags', 'type' => 'relationship', 'browse' => true,
            'details' => json_encode(['type' => 'belongsToMany', 'table' => 'categories', 'pivot_table' => 'category_page', 'label' => 'name'])]);
        $childRow = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'notes', 'type' => 'relationship', 'browse' => true,
            'details' => json_encode(['type' => 'hasMany', 'table' => 'page_notes', 'model' => BreadChildFixture::class, 'column' => 'page_code', 'key' => 'title', 'label' => 'label'])]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        foreach (['Ocean', 'Second note'] as $needle) {
            $page->set('search', $needle);
            $rows = $page->instance()->records()['rows'];
            $this->assertSame([1], $rows->pluck('id')->all());
            $this->assertSame(1, $rows->total());
        }
        foreach (['Unlinked tag', 'Deleted note', 'Unassigned note'] as $needle) {
            $page->set('search', $needle);
            $this->assertSame(0, $page->instance()->records()['rows']->total());
        }
        $page->set('search', 'Beta note');
        $this->assertSame([2], $page->instance()->records()['rows']->pluck('id')->all());
        $details = json_decode(DB::table('data_rows')->where('id', $childRow)->value('details'), true);
        $details['type'] = 'hasOne';
        DB::table('data_rows')->where('id', $childRow)->update(['details' => json_encode($details)]);
        $page->set('search', 'Second note');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        $page->set('search', 'First note');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        DB::table('data_rows')->where('id', $tagRow)->update(['browse' => false]);
        $page->set('search', 'Ocean');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        DB::table('data_rows')->where('id', $tagRow)->update(['browse' => true]);
        DB::table('data_types')->where('id', $typeId)->update(['server_side' => true]);
        $page->set('search', 'Ocean blue');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        DB::table('data_types')->where('id', $typeId)->update(['server_side' => false]);
        Schema::table('pages', fn (Blueprint $table) => $table->unsignedBigInteger('parent_id')->nullable());
        DB::table('pages')->where('id', 1)->update(['parent_id' => 2]);
        DB::table('data_rows')->where('data_type_id', $typeId)->update(['browse' => false]);
        $selfRow = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'self_children', 'type' => 'relationship', 'browse' => true,
            'details' => json_encode(['type' => 'hasMany', 'table' => 'pages', 'model' => \App\Models\Page::class, 'column' => 'parent_id', 'key' => 'id', 'label' => 'title'])]);
        $page->set('search', 'alpha');
        $this->assertSame([2], $page->instance()->records()['rows']->pluck('id')->all());
        $selfDetails = json_decode(DB::table('data_rows')->where('id', $selfRow)->value('details'), true);
        $selfDetails['type'] = 'hasOne';
        DB::table('data_rows')->where('id', $selfRow)->update(['details' => json_encode($selfDetails)]);
        $this->assertSame([2], $page->instance()->records()['rows']->pluck('id')->all());
    }

    public function test_child_relationships_match_voyager_readonly_forms_and_model_scopes(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'read_pages', 'edit_pages', 'add_pages']);
        Schema::create('page_notes', function (Blueprint $table) {
            $table->id(); $table->string('page_code')->nullable(); $table->string('label'); $table->softDeletes();
        });
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $id = DB::table('pages')->insertGetId(['title' => 'alpha']);
        DB::table('page_notes')->insert([
            ['page_code' => 'alpha', 'label' => 'First child', 'deleted_at' => null],
            ['page_code' => 'alpha', 'label' => 'Second child', 'deleted_at' => null],
            ['page_code' => 'beta', 'label' => 'Other parent', 'deleted_at' => null],
            ['page_code' => 'alpha', 'label' => 'Deleted child', 'deleted_at' => now()],
            ['page_code' => null, 'label' => 'Unassigned child', 'deleted_at' => null],
        ]);
        $rows = [];
        foreach (['hasOne', 'hasMany'] as $index => $kind) {
            $rows[$kind] = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'child_'.$kind,
                'type' => 'relationship', 'display_name' => $kind.' notes', 'order' => 10 + $index,
                'details' => json_encode(['type' => $kind, 'table' => 'page_notes', 'model' => BreadChildFixture::class,
                    'column' => 'page_code', 'key' => 'title', 'label' => 'label'])]);
        }
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        $record = $page->instance()->records()['rows']->first();
        $this->assertSame('First child', $record->child_hasOne);
        $this->assertSame('First child, Second child', $record->child_hasMany);
        $page->call('openView', $id)->assertSee('First child')->assertSee('Second child')->assertDontSee('Deleted child')->assertDontSee('Other parent');
        $fields = collect($page->instance()->viewedFields())->keyBy('label');
        $this->assertSame('First child', $fields['hasOne notes']['value']);
        $page->call('openEdit', $id)->assertSee('First child')->assertSee('Second child')->assertDontSee('Unassigned child');
        $before = DB::table('page_notes')->orderBy('id')->get()->toJson();
        $page->set('data.child_hasMany', [3])->set('data.__child_'.$rows['hasMany'], [3])->call('save')->assertHasNoErrors();
        $this->assertSame($before, DB::table('page_notes')->orderBy('id')->get()->toJson());
        $page->call('openCreate')->assertSee('Связанные записи будут доступны после создания записи.')->assertDontSee('Unassigned child');
        DB::table('data_rows')->whereIn('id', array_values($rows))->update(['edit' => false, 'browse' => false, 'read' => false]);
        $this->assertSame([], app(BreadRegistry::class)->childrenRows(app(BreadRegistry::class)->type('pages'), 'edit'));
    }

    public function test_child_relationship_constructor_maps_child_column_and_parent_key_and_rejects_invalid_models(): void
    {
        $this->admin(['browse_admin', 'browse_bread']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $page = Livewire::withQueryParams(['section' => $typeId])->test(\App\Filament\Pages\BreadMetadata::class)
            ->set('data.display_name_singular', 'Page')->call('openRelationship')
            ->set('relationship', ['type' => 'hasOne', 'table' => 'users', 'model' => User::class,
                'column' => 'role_id', 'key' => 'id', 'label' => 'email', 'pivot_table' => '', 'taggable' => false])
            ->call('stageRelationship')->assertHasNoErrors()->call('save')->assertHasNoErrors();
        $row = DB::table('data_rows')->where('data_type_id', $typeId)->where('type', 'relationship')->first();
        $this->assertSame('hasOne', json_decode($row->details, true)['type']);
        $page->call('openRelationship', (string) $row->id)->set('relationship.type', 'hasMany')
            ->call('stageRelationship')->call('save')->assertHasNoErrors();
        $details = json_decode(DB::table('data_rows')->where('id', $row->id)->value('details'), true);
        $this->assertSame('hasMany', $details['type']);
        $this->assertSame('role_id', $details['column']);
        $this->assertSame('id', $details['key']);
        $service = app(\App\Services\Admin\BreadMetadataService::class);
        foreach ([['column'=>'title'], ['key'=>'role_id'], ['model'=>\App\Models\Page::class], ['label'=>'password']] as $bad) {
            try { $service->validateRelationship(app(BreadRegistry::class)->type('pages'), array_replace($details, $bad)); $this->fail('Invalid child mapping accepted'); }
            catch (\Illuminate\Validation\ValidationException) { $this->assertTrue(true); }
        }
    }

    private function creationTables(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id(); $table->string('title')->nullable(); $table->rememberToken(); $table->timestamp('created_at')->nullable();
        });
        Schema::create('menus', function (Blueprint $table) { $table->id(); $table->string('name'); });
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id(); $table->unsignedInteger('menu_id'); $table->string('title'); $table->string('route')->nullable();
            $table->string('url')->nullable(); $table->string('target')->nullable(); $table->string('icon_class')->nullable();
            $table->unsignedInteger('parent_id')->nullable(); $table->boolean('status')->default(true); $table->integer('order');
        });
        $menu = DB::table('menus')->insertGetId(['name' => 'admin']);
        DB::table('menu_items')->insert(['menu_id' => $menu, 'title' => 'Existing', 'order' => 4]);
    }

    private function roleMetadata(): void
    {
        $typeId = DB::table('data_types')->insertGetId([
            'name' => 'roles', 'slug' => 'roles', 'model_name' => Role::class, 'display_name_plural' => 'Роли',
        ]);
        foreach (['name', 'display_name'] as $field) {
            DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => $field,
                'type' => 'text', 'display_name' => $field, 'required' => true]);
        }
    }

    public function test_generic_list_search_sort_and_page_size_keep_scope_and_type_filters(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('pages', fn (Blueprint $table) => $table->unsignedInteger('id_type')->nullable());
        DB::table('data_types')->where('name', 'pages')->update([
            'model_name' => BreadScopedPageFixture::class,
            'details' => json_encode(['scope' => 'visibleForBread', 'order_column' => 'title', 'order_direction' => 'asc', 'default_search_key' => 'title']),
        ]);
        for ($number = 1; $number <= 26; $number++) {
            DB::table('pages')->insert(['title' => sprintf('Item %02d', $number), 'meta_description' => 'Visible', 'id_type' => 2]);
        }
        DB::table('pages')->insert([
            ['title' => 'Item 01 hidden', 'meta_description' => 'Hidden', 'id_type' => 2],
            ['title' => 'Item 01 other type', 'meta_description' => 'Visible', 'id_type' => 3],
        ]);
        $list = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('filterType', 2);
        $this->assertSame('title', $list->get('searchKey'));
        $this->assertSame(26, $list->instance()->records()['rows']->total());
        $this->assertSame('Item 01', $list->instance()->records()['rows']->first()->title);
        $list->call('gotoPage', 2)->set('perPage', 10);
        $this->assertSame(1, $list->instance()->records()['rows']->currentPage());
        $this->assertCount(10, $list->instance()->records()['rows']);
        $list->call('sortBy', 'title')->call('sortBy', 'title');
        $this->assertSame('Item 26', $list->instance()->records()['rows']->first()->title);
        $list->call('gotoPage', 2)->set('search', 'Item 01');
        $this->assertSame(1, $list->instance()->records()['rows']->currentPage());
        $this->assertSame(1, $list->instance()->records()['rows']->total());
        $list->set('search', 'Item 0');
        $this->assertSame(9, $list->instance()->records()['rows']->total());
        $list->set('searchFilter', 'equals')->assertSee('Записи не найдены');
        $this->assertSame(0, $list->instance()->records()['rows']->total());
        $list->set('searchKey', 'id')->set('search', '1');
        $this->assertSame([1], $list->instance()->records()['rows']->pluck('id')->all());
        $this->assertDatabaseCount('pages', 28);
    }

    public function test_generic_list_controls_cannot_query_hidden_or_sensitive_fields(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('pages', function (Blueprint $table): void {
            $table->string('private_note')->nullable(); $table->string('password')->nullable();
        });
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_rows')->insert([
            ['data_type_id' => $typeId, 'field' => 'private_note', 'type' => 'text', 'browse' => false, 'details' => '{}'],
            ['data_type_id' => $typeId, 'field' => 'password', 'type' => 'text', 'browse' => true, 'details' => '{}'],
        ]);
        DB::table('pages')->insert(['title' => 'Public title', 'private_note' => 'Secret needle', 'password' => 'Secret needle']);
        $list = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->assertDontSee('Secret needle');
        $this->assertArrayNotHasKey('private_note', $list->instance()->browseFields());
        $this->assertArrayNotHasKey('password', $list->instance()->browseFields());
        $list->set('searchKey', 'password')->set('search', 'Secret needle');
        $this->assertSame(0, $list->instance()->records()['rows']->total());
        $list->set('search', '')->set('sortField', 'id desc; DROP TABLE pages')->set('perPage', 1000000);
        $this->assertSame(25, $list->instance()->records()['rows']->perPage());
        $this->assertDatabaseCount('pages', 1);
        $list->call('sortBy', 'private_note')->assertStatus(422);
    }

    public function test_generic_list_searches_and_sorts_belongs_to_labels_with_custom_keys(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::create('authors', function (Blueprint $table): void {
            $table->id(); $table->unsignedInteger('code')->unique(); $table->string('name');
        });
        Schema::table('pages', fn (Blueprint $table) => $table->unsignedInteger('id_type')->nullable());
        DB::table('authors')->insert([['code' => 100, 'name' => 'Zulu'], ['code' => 200, 'name' => 'Alpha']]);
        DB::table('data_types')->where('name', 'pages')->update([
            'model_name' => BreadScopedPageFixture::class, 'details' => '{"scope":"visibleForBread"}',
        ]);
        DB::table('data_rows')->insert([
            'data_type_id' => 1, 'field' => 'page_author_relationship', 'type' => 'relationship', 'display_name' => 'Автор',
            'details' => json_encode(['type' => 'belongsTo', 'table' => 'authors', 'column' => 'author_id', 'key' => 'code', 'label' => 'name']),
        ]);
        $z = DB::table('pages')->insertGetId(['title' => 'Z', 'author_id' => 100, 'meta_description' => 'Visible', 'id_type' => 2]);
        $alpha = [];
        for ($number = 1; $number <= 11; $number++) {
            $alpha[] = DB::table('pages')->insertGetId(['title' => 'A'.$number, 'author_id' => 200, 'meta_description' => 'Visible', 'id_type' => 2]);
        }
        $empty = DB::table('pages')->insertGetId(['title' => 'No author', 'meta_description' => 'Visible', 'id_type' => 2]);
        DB::table('pages')->insert([
            ['title' => 'Wrong type', 'author_id' => 200, 'meta_description' => 'Visible', 'id_type' => 3],
            ['title' => 'Outside scope', 'author_id' => 200, 'meta_description' => 'Hidden', 'id_type' => 2],
        ]);
        $list = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('filterType', 2)->set('perPage', 10);
        $this->assertSame('Автор', $list->instance()->browseFields()['page_author_relationship']);
        $list->set('searchKey', 'page_author_relationship')->set('search', 'Al');
        $this->assertSame(11, $list->instance()->records()['rows']->total());
        $list->set('searchFilter', 'equals');
        $this->assertSame(0, $list->instance()->records()['rows']->total());
        $list->set('search', 'Alpha');
        $this->assertSame(11, $list->instance()->records()['rows']->total());
        $this->assertSame('Alpha', $list->instance()->records()['rows']->first()->page_author_relationship);
        DB::table('data_rows')->insert(['data_type_id' => 1, 'field' => 'author_id', 'type' => 'text', 'details' => '{}']);
        $list->set('searchKey', 'author_id');
        $this->assertSame(0, $list->instance()->records()['rows']->total());
        $list->set('search', '200');
        $this->assertSame(11, $list->instance()->records()['rows']->total());
        $list->set('searchKey', 'page_author_relationship');
        $list->set('search', '200');
        $this->assertSame(0, $list->instance()->records()['rows']->total());
        $list->set('search', '')->call('sortBy', 'page_author_relationship');
        $this->assertSame([$empty, ...array_slice(array_reverse($alpha), 0, 9)], $list->instance()->records()['rows']->pluck('id')->all());
        $this->assertSame(13, $list->instance()->records()['rows']->total());
        $list->call('gotoPage', 2);
        $this->assertSame([$alpha[1], $alpha[0], $z], $list->instance()->records()['rows']->pluck('id')->all());
        $list->call('sortBy', 'page_author_relationship');
        $this->assertSame($z, $list->instance()->records()['rows']->first()->id);
        $this->assertSame(1, $list->instance()->records()['rows']->currentPage());
        $this->assertDatabaseCount('pages', 15);
    }

    public function test_generic_list_belongs_to_controls_reject_sensitive_stale_and_hidden_metadata(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        foreach ([['secret_author', 'password', true], ['hidden_author', 'email', false], ['stale_author', 'missing_column', true]] as [$field, $label, $browse]) {
            DB::table('data_rows')->insert([
                'data_type_id' => 1, 'field' => $field, 'type' => 'relationship', 'browse' => $browse,
                'details' => json_encode(['type' => 'belongsTo', 'table' => 'users', 'column' => 'author_id', 'key' => 'id', 'label' => $label]),
            ]);
        }
        $list = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        foreach (['secret_author', 'hidden_author', 'stale_author'] as $field) {
            $this->assertArrayNotHasKey($field, $list->instance()->browseFields());
        }
        $list->call('sortBy', 'secret_author')->assertStatus(422);
    }

    public function test_generic_bulk_selection_is_page_local_and_resets_with_list_controls(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'delete_pages']);
        for ($number = 1; $number <= 12; $number++) { DB::table('pages')->insert(['title' => 'Item '.$number]); }
        $list = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->assertDontSee('Удалить выбранные')
            ->set('perPage', 10)->call('togglePageSelection')->assertSee('Удалить выбранные');
        $this->assertCount(10, $list->get('selectedRecords'));
        $list->call('togglePageSelection')->assertSet('selectedRecords', [])->assertDontSee('Удалить выбранные');
        $list->call('togglePageSelection')->call('gotoPage', 2)->assertSet('selectedRecords', []);
        $list->call('togglePageSelection');
        $this->assertSame(['2', '1'], $list->get('selectedRecords'));
        $list->set('search', 'Item')->assertSet('selectedRecords', []);
        $list->call('togglePageSelection')->call('sortBy', 'title')->assertSet('selectedRecords', []);
        $list->call('togglePageSelection')->call('changeLocale', 'ru')->assertSet('selectedRecords', []);
        $list->set('selectedRecords', ['1', '2'])->call('deleteSelectedRecords')->assertHasNoErrors()->assertSet('selectedRecords', []);
        $this->assertDatabaseMissing('pages', ['id' => 1]);
        $this->assertDatabaseMissing('pages', ['id' => 2]);
        $this->assertDatabaseCount('pages', 10);
        $list->call('deleteSelectedRecords')->assertHasErrors(['selectedRecords']);
    }

    public function test_generic_bulk_delete_rejects_selection_outside_scope_type_search_or_current_page(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'delete_pages']);
        Schema::table('pages', fn (Blueprint $table) => $table->unsignedInteger('id_type')->nullable());
        DB::table('data_types')->where('name', 'pages')->update(['model_name' => BreadScopedPageFixture::class, 'details' => '{"scope":"visibleForBread"}']);
        for ($number = 1; $number <= 12; $number++) {
            DB::table('pages')->insert(['title' => 'Match '.$number, 'meta_description' => 'Visible', 'id_type' => 2]);
        }
        DB::table('pages')->insert([
            ['title' => 'Match wrong type', 'meta_description' => 'Visible', 'id_type' => 3],
            ['title' => 'Match hidden', 'meta_description' => 'Hidden', 'id_type' => 2],
            ['title' => 'Excluded search', 'meta_description' => 'Visible', 'id_type' => 2],
        ]);
        $list = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('filterType', 2)->set('perPage', 10)->set('searchKey', 'title')->set('search', 'Match');
        foreach ([13, 14, 15, 1, 9999] as $invalid) {
            $list->set('selectedRecords', ['12', (string) $invalid])->call('deleteSelectedRecords')->assertHasErrors(['selectedRecords']);
            $this->assertDatabaseCount('pages', 15);
        }
        $list->set('selectedRecords', ['12', '12'])->call('deleteSelectedRecords')->assertHasErrors(['selectedRecords.0']);
        $this->assertDatabaseCount('pages', 15);
    }

    public function test_generic_bulk_delete_and_selection_require_delete_permission(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        DB::table('pages')->insert(['title' => 'Retain']);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->assertDontSee('Удалить выбранные')->call('togglePageSelection')->assertForbidden();
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('selectedRecords', ['1'])->call('deleteSelectedRecords')->assertForbidden();
        $this->assertDatabaseCount('pages', 1);
    }

    public function test_generic_bulk_delete_rolls_back_sql_failure_and_defers_media_files_until_commit(): void
    {
        [$record, $old, $other, $before] = $this->recoveryMediaCollection();
        Role::first()->permissions()->attach(Permission::create(['key' => 'delete_canvas_new']));
        $second = BreadRecoveryCollectionFixture::create();
        DB::statement('CREATE TRIGGER reject_bulk_record BEFORE DELETE ON canvas_new WHEN OLD.id = '.$second->id." BEGIN SELECT RAISE(ABORT, 'Fixture bulk record failure'); END");
        $list = Livewire::test(VoyagerBread::class, ['type' => 'canvas-new'])->set('selectedRecords', [(string) $record->id, (string) $second->id]);
        try { $list->call('deleteSelectedRecords'); $this->fail('Expected SQL failure'); }
        catch (\Illuminate\Database\QueryException $error) { $this->assertStringContainsString('Fixture bulk record failure', $error->getMessage()); }
        $this->assertDatabaseCount('canvas_new', 2);
        $this->assertDatabaseCount('media', 2);
        $disk = Storage::disk('public');
        $this->assertSame(array_keys($before), $disk->allFiles());
        foreach ($before as $path => $bytes) { $this->assertSame($bytes, $disk->get($path)); }
        DB::statement('DROP TRIGGER reject_bulk_record');
        $list->call('deleteSelectedRecords')->assertHasNoErrors()->assertSet('selectedRecords', []);
        $this->assertDatabaseCount('canvas_new', 0);
        $this->assertDatabaseCount('media', 0);
        $this->assertSame([], $disk->allFiles());
    }

    public function test_generic_bulk_delete_rolls_back_when_a_model_vetoes_deletion(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'delete_pages']);
        DB::table('data_types')->where('name', 'pages')->update(['model_name' => BreadDeleteVetoFixture::class]);
        DB::table('pages')->insert([['title' => 'Allowed'], ['title' => 'Veto']]);
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('selectedRecords', ['1', '2'])
            ->call('deleteSelectedRecords')->assertHasErrors(['selectedRecords'])->assertSet('selectedRecords', ['1', '2']);
        $this->assertDatabaseCount('pages', 2);
    }

    public function test_generic_list_url_restores_filters_page_and_edit_return_context(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        Schema::table('pages', fn (Blueprint $table) => $table->unsignedInteger('id_type')->nullable());
        for ($number = 1; $number <= 12; $number++) {
            DB::table('pages')->insert(['title' => 'Item '.$number, 'id_type' => 2]);
        }
        $state = ['s' => 'Item', 'key' => 'title', 'filter' => 'contains', 'order_by' => 'id', 'sort_order' => 'desc',
            'per_page' => 10, 'lang' => 'ru', 'type' => 2, 'page' => 2];
        $list = Livewire::withQueryParams($state)->test(VoyagerBread::class, ['type' => 'pages'])
            ->assertSet('search', 'Item')->assertSet('searchKey', 'title')->assertSet('sortDirection', 'desc')
            ->assertSet('locale', 'ru')->assertSet('perPage', 10)->assertSet('filterType', 2);
        $this->assertSame([2, 1], $list->instance()->records()['rows']->pluck('id')->all());
        $editUrl = $list->instance()->editUrl(2);
        parse_str(parse_url($editUrl, PHP_URL_QUERY), $editState);
        $this->assertSame('2', $editState['page']);
        $this->assertSame('ru', $editState['lang']);
        $this->assertSame('Item', $editState['s']);
        $editor = Livewire::withQueryParams($editState)->test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => 2])->assertSet('locale', 'ru');
        $returnUrl = $editor->instance()->listUrl();
        $editor->call('changeLocale', 'en');
        $this->assertSame($returnUrl, $editor->instance()->listUrl());
        $reloadedEditor = Livewire::withQueryParams(array_merge($editState, ['lang' => 'en']))
            ->test(VoyagerBreadEdit::class, ['type' => 'pages', 'record' => 2])->assertSet('locale', 'en');
        $this->assertSame($returnUrl, $reloadedEditor->instance()->listUrl());
        $editor->call('save')->assertHasNoErrors();
        $this->assertSame($returnUrl, $editor->instance()->listUrl());
        $editor->call('cancel')->assertRedirect($returnUrl);
        parse_str(parse_url($returnUrl, PHP_URL_QUERY), $returnState);
        $returned = Livewire::withQueryParams($returnState)->test(VoyagerBread::class, ['type' => 'pages'])->assertSet('locale', 'ru');
        $this->assertSame(2, $returned->instance()->records()['rows']->currentPage());
        $this->assertSame([2, 1], $returned->instance()->records()['rows']->pluck('id')->all());
        $this->assertSame([], $returned->get('selectedRecords'));
    }

    public function test_generic_list_normalizes_malformed_url_and_never_accepts_external_return_urls(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        DB::table('pages')->insert(['title' => 'Keep']);
        $list = Livewire::withQueryParams(['s' => ['bad'], 'key' => 'password', 'filter' => 'bad', 'order_by' => 'password',
            'sort_order' => 'DROP TABLE', 'per_page' => 1000000, 'lang' => ['ru'], 'type' => 'bad', 'page' => -5,
            'return_url' => 'https://outside.example/'])
            ->test(VoyagerBread::class, ['type' => 'pages'])->assertSet('search', '')->assertSet('searchKey', '')
            ->assertSet('sortField', '')->assertSet('sortDirection', 'asc')->assertSet('searchFilter', 'contains')
            ->assertSet('perPage', 25)->assertSet('locale', 'en')->assertSet('filterType', null);
        $this->assertSame(1, $list->instance()->records()['rows']->currentPage());
        $this->assertStringNotContainsString('outside.example', $list->instance()->editUrl(1));
        $list->set('search', 'A & B # C')->set('searchKey', 'title');
        parse_str(parse_url($list->instance()->editUrl(1), PHP_URL_QUERY), $query);
        $this->assertSame('A & B # C', $query['s']);
        $this->assertArrayNotHasKey('return_url', $query);
        $this->assertDatabaseCount('pages', 1);
    }

    public function test_generic_order_moves_records_and_preserves_scope_type_and_return_state(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        Schema::table('pages', function (Blueprint $table): void { $table->integer('sort')->nullable(); $table->integer('id_type')->nullable(); });
        DB::table('data_types')->where('name', 'pages')->update(['model_name' => BreadScopedPageFixture::class, 'details' => json_encode([
            'order_column' => 'sort', 'order_display_column' => 'title', 'order_direction' => 'asc', 'scope' => 'visibleForBread'])]);
        foreach ([1, 2, 3, 4, 5] as $id) { DB::table('pages')->insert(['id' => $id, 'title' => 'Order '.$id, 'sort' => $id * 10,
            'id_type' => $id === 4 ? 3 : 2, 'meta_description' => $id === 5 ? 'Hidden' : 'Visible']); }
        $list = Livewire::withQueryParams(['type' => 2, 's' => 'Order', 'lang' => 'ru'])->test(VoyagerBread::class, ['type' => 'pages'])->assertSee('Порядок записей');
        $this->assertStringContainsString('/bread/pages/order?', $list->instance()->orderUrl());
        $page = Livewire::withQueryParams(['type' => 2, 's' => 'Order', 'lang' => 'ru'])->test(\App\Filament\Pages\VoyagerBreadOrder::class, ['type' => 'pages'])
            ->assertSee('Order 1')->assertDontSee('Order 4')->assertDontSee('Order 5');
        $this->assertSame([1, 2, 3], $page->instance()->orderedRecords()->modelKeys());
        $page->call('moveRecord', 3, 0)->assertHasNoErrors();
        $this->assertSame([3, 1, 2], $page->instance()->orderedRecords()->modelKeys());
        $this->assertDatabaseHas('pages', ['id' => 3, 'sort' => 1]);
        $this->assertDatabaseHas('pages', ['id' => 4, 'sort' => 40]);
        $this->assertDatabaseHas('pages', ['id' => 5, 'sort' => 50]);
        $page->call('moveRecord', 3, 1)->assertHasNoErrors();
        $this->assertSame([1, 3, 2], $page->instance()->orderedRecords()->modelKeys());
        parse_str(parse_url($page->instance()->listUrl(), PHP_URL_QUERY), $return);
        $this->assertSame(['lang' => 'ru', 's' => 'Order', 'type' => '2'], $return);
    }

    public function test_generic_order_rejects_invalid_stale_and_outside_selection_without_partial_writes(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        Schema::table('pages', fn (Blueprint $table) => $table->integer('sort')->nullable());
        DB::table('data_types')->where('name', 'pages')->update(['details' => json_encode(['order_column' => 'sort', 'order_display_column' => 'title'])]);
        DB::table('pages')->insert([['id' => 1, 'title' => 'One', 'sort' => 10], ['id' => 2, 'title' => 'Two', 'sort' => 20]]);
        $page = Livewire::test(\App\Filament\Pages\VoyagerBreadOrder::class, ['type' => 'pages']);
        $page->call('moveRecord', 999, 0)->assertHasErrors('ordering');
        $page->call('moveRecord', 1, -1)->assertHasErrors('ordering');
        $page->call('moveRecord', 1, 2)->assertHasErrors('ordering');
        $this->assertDatabaseHas('pages', ['id' => 1, 'sort' => 10]);
        DB::table('pages')->where('id', 2)->update(['sort' => 99]);
        $page->call('moveRecord', 2, 0)->assertHasErrors('ordering');
        $this->assertDatabaseHas('pages', ['id' => 1, 'sort' => 10]);
        $this->assertDatabaseHas('pages', ['id' => 2, 'sort' => 99]);
        DB::table('data_types')->where('name', 'pages')->update(['details' => json_encode(['order_column' => 'title', 'order_display_column' => 'title'])]);
        $this->withoutExceptionHandling();
        try { Livewire::test(\App\Filament\Pages\VoyagerBreadOrder::class, ['type' => 'pages']); $this->fail('Invalid ordering was accepted'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { $this->assertSame(404, $exception->getStatusCode()); }
        $this->withExceptionHandling();
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->assertDontSee('Порядок записей');
    }

    public function test_generic_order_requires_edit_permission_on_mount_and_each_move(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        Schema::table('pages', fn (Blueprint $table) => $table->integer('sort')->nullable());
        DB::table('data_types')->where('name', 'pages')->update(['details' => json_encode(['order_column' => 'sort', 'order_display_column' => 'title'])]);
        DB::table('pages')->insert(['title' => 'Unchanged', 'sort' => 20]);
        $page = Livewire::test(\App\Filament\Pages\VoyagerBreadOrder::class, ['type' => 'pages']);
        auth('filament')->user()->role->permissions()->where('key', 'edit_pages')->first()->pivot->delete();
        app()->forgetInstance(BreadRegistry::class);
        $page->call('moveRecord', 1, 0)->assertForbidden();
        Livewire::test(\App\Filament\Pages\VoyagerBreadOrder::class, ['type' => 'pages'])->assertForbidden();
        Livewire::test(VoyagerBread::class, ['type' => 'pages'])->assertDontSee('Порядок записей');
        $this->assertDatabaseHas('pages', ['title' => 'Unchanged', 'sort' => 20]);
    }

    public function test_generic_order_rolls_back_model_veto_and_renders_compact_image_labels(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        Schema::table('pages', fn (Blueprint $table) => $table->integer('sort')->nullable());
        $type = DB::table('data_types')->where('name', 'pages')->first();
        DB::table('data_types')->where('id', $type->id)->update(['model_name' => BreadOrderVetoFixture::class,
            'details' => json_encode(['order_column' => 'sort', 'order_display_column' => 'image'])]);
        DB::table('data_rows')->insert(['data_type_id' => $type->id, 'field' => 'image', 'type' => 'image', 'display_name' => 'Image', 'browse' => true]);
        DB::table('pages')->insert([['id' => 1, 'title' => 'One', 'image' => 'fixture/one.jpg', 'sort' => 10], ['id' => 2, 'title' => 'Two', 'image' => 'fixture/two.jpg', 'sort' => 20]]);
        $page = Livewire::test(\App\Filament\Pages\VoyagerBreadOrder::class, ['type' => 'pages'])->assertSee('viar-order-image', false)->assertSee('fixture/one.jpg');
        $page->call('moveRecord', 2, 0)->assertHasErrors('ordering');
        $this->assertDatabaseHas('pages', ['id' => 1, 'sort' => 10]);
        $this->assertDatabaseHas('pages', ['id' => 2, 'sort' => 20]);
    }

    public function test_generic_order_keeps_original_descending_numbering_and_handles_empty_lists(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        Schema::table('pages', fn (Blueprint $table) => $table->integer('sort')->nullable());
        DB::table('data_types')->where('name', 'pages')->update(['details' => json_encode(['order_column' => 'sort', 'order_display_column' => 'title', 'order_direction' => 'desc'])]);
        Livewire::test(\App\Filament\Pages\VoyagerBreadOrder::class, ['type' => 'pages'])->assertSee('Нет записей для упорядочивания.')
            ->call('moveRecord', 1, 0)->assertHasErrors('ordering');
        DB::table('pages')->insert([['id' => 1, 'title' => 'One', 'sort' => 10], ['id' => 2, 'title' => 'Two', 'sort' => 20]]);
        $page = Livewire::test(\App\Filament\Pages\VoyagerBreadOrder::class, ['type' => 'pages']);
        $this->assertSame([2, 1], $page->instance()->orderedRecords()->modelKeys());
        $page->call('moveRecord', 1, 0)->assertHasNoErrors();
        $this->assertDatabaseHas('pages', ['id' => 1, 'sort' => 1]);
        $this->assertDatabaseHas('pages', ['id' => 2, 'sort' => 2]);
        $this->assertSame([2, 1], $page->instance()->orderedRecords()->modelKeys());
        DB::table('pages')->insert(['id' => 3, 'title' => 'New record', 'sort' => 3]);
        $page->call('moveRecord', 2, 1)->assertHasErrors('ordering');
        $this->assertDatabaseHas('pages', ['id' => 2, 'sort' => 2]);
    }

    public function test_generic_server_search_includes_declared_non_browse_fields_but_keeps_sort_and_secrets_restricted(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(true));
        Schema::table('pages', function (Blueprint $table): void { $table->string('secret')->nullable(); $table->string('unconfigured')->nullable(); });
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->whereIn('field', ['meta_description', 'created_at'])->delete();
        DB::table('data_rows')->insert([
            ['data_type_id' => $typeId, 'field' => 'meta_description', 'type' => 'text_area', 'display_name' => 'Description', 'browse' => false, 'read' => true, 'edit' => true],
            ['data_type_id' => $typeId, 'field' => 'secret', 'type' => 'hidden', 'display_name' => 'Secret', 'browse' => false, 'read' => true, 'edit' => true],
            ['data_type_id' => $typeId, 'field' => 'created_at', 'type' => 'timestamp', 'display_name' => 'Created At', 'browse' => false, 'read' => false, 'edit' => false],
        ]);
        DB::table('pages')->insert(['title' => 'Public', 'meta_description' => 'Metadata needle', 'secret' => 'Secret needle', 'unconfigured' => 'Secret needle', 'created_at' => '2026-10-12 09:00:00']);
        $page = Livewire::withQueryParams(['key' => 'meta_description', 's' => 'Metadata needle', 'filter' => 'equals'])->test(VoyagerBread::class, ['type' => 'pages'])
            ->assertSet('searchKey', 'meta_description');
        $this->assertSame(1, $page->instance()->records()['rows']->total());
        $this->assertArrayHasKey('meta_description', $page->instance()->searchFields());
        $this->assertArrayNotHasKey('meta_description', $page->instance()->browseFields());
        $this->assertArrayNotHasKey('secret', $page->instance()->searchFields());
        $this->assertArrayNotHasKey('unconfigured', $page->instance()->searchFields());
        $page->set('searchKey', 'created_at')->set('search', '2026-10-12 09:00:00');
        $this->assertSame(1, $page->instance()->records()['rows']->total());
        $page->call('sortBy', 'meta_description')->assertStatus(422);
        Livewire::withQueryParams(['key' => 'secret', 's' => 'Secret needle'])->test(VoyagerBread::class, ['type' => 'pages'])->assertSet('searchKey', '')->assertSee('Записи не найдены');
        DB::table('data_types')->where('name', 'pages')->update(['controller' => 'TCG\\Voyager\\Http\\Controllers\\VoyagerBaseController']);
        $standard = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        $this->assertArrayNotHasKey('meta_description', $standard->instance()->searchFields());
    }

    public function test_generic_roles_browse_only_hides_actions_and_blocks_direct_mutation_calls(): void
    {
        $this->admin(['browse_admin', 'browse_roles']);
        $this->roleMetadata();
        $target = Role::create(['name' => 'restricted_target', 'display_name' => 'Restricted target']);
        Livewire::test(VoyagerBread::class, ['type' => 'roles'])->assertSee('Restricted target')
            ->assertDontSee('Создать запись')->assertDontSee('aria-label="Просмотр"', false)
            ->assertDontSee('aria-label="Изменить"', false)->assertDontSee('aria-label="Удалить"', false)
            ->assertDontSee('Выбрать все записи текущей страницы');
        foreach (['openCreate' => [], 'openEdit' => [$target->id], 'openView' => [$target->id], 'deleteRecord' => [$target->id],
            'togglePageSelection' => [], 'deleteSelectedRecords' => []] as $action => $arguments) {
            Livewire::test(VoyagerBread::class, ['type' => 'roles'])->call($action, ...$arguments)->assertForbidden();
        }
        Livewire::test(VoyagerBread::class, ['type' => 'roles'])->set('editing', true)->set('recordId', $target->id)
            ->set('data.name', 'forged')->call('save')->assertForbidden();
        $this->assertDatabaseHas('roles', ['id' => $target->id, 'name' => 'restricted_target']);
        $this->assertSame(0, $target->permissions()->count());
    }

    public function test_generic_roles_read_permission_only_shows_view_action(): void
    {
        $this->admin(['browse_admin', 'browse_roles', 'read_roles']);
        $this->roleMetadata();
        $target = Role::create(['name' => 'reader_target', 'display_name' => 'Reader target']);
        Livewire::test(VoyagerBread::class, ['type' => 'roles'])->assertSee('aria-label="Просмотр"', false)
            ->assertDontSee('Создать запись')->assertDontSee('aria-label="Изменить"', false)->assertDontSee('aria-label="Удалить"', false)
            ->call('openView', $target->id)->assertSee('Reader target');
        Livewire::test(VoyagerBreadEdit::class, ['type' => 'roles', 'record' => $target->id])->assertForbidden();
    }

    public function test_generic_edge_pages_recover_after_filters_single_bulk_delete_and_stale_urls(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'delete_pages']);
        for ($id = 1; $id <= 11; $id++) { DB::table('pages')->insert(['id' => $id, 'title' => 'Item '.$id]); }
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('perPage', 10)->call('sortBy', 'id')->call('gotoPage', 2);
        $this->assertSame([11], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('selectedRecords', ['11'])->call('deleteRecord', 11)->assertHasNoErrors()->assertSet('selectedRecords', []);
        $rows = $page->instance()->records()['rows'];
        $this->assertSame(1, $rows->currentPage());
        $this->assertSame(10, $rows->total());
        $this->assertDatabaseMissing('pages', ['id' => 11]);
        $page->call('gotoPage', 999);
        $this->assertSame(1, $page->instance()->records()['rows']->currentPage());
        DB::table('pages')->insert(['id' => 12, 'title' => 'New item']);
        $page->call('gotoPage', 2)->set('searchKey', 'title')->set('searchFilter', 'equals')->set('search', 'Item 1');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', 'No match')->assertSee('Записи не найдены');
        $this->assertSame(1, $page->instance()->records()['rows']->currentPage());
        $page->set('search', '')->call('gotoPage', 2)->call('togglePageSelection')->call('deleteSelectedRecords')->assertHasNoErrors();
        $this->assertDatabaseMissing('pages', ['id' => 12]);
        $this->assertSame(1, $page->instance()->records()['rows']->currentPage());
        $page->call('togglePageSelection')->call('deleteSelectedRecords')->assertHasNoErrors()->assertSee('Записи не найдены');
        $this->assertDatabaseCount('pages', 0);
        $this->assertSame(1, $page->instance()->records()['rows']->currentPage());
    }

    public function test_generic_single_delete_veto_keeps_record_selection_and_page(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'delete_pages']);
        DB::table('data_types')->where('name', 'pages')->update(['model_name' => BreadDeleteVetoFixture::class]);
        for ($id = 1; $id <= 11; $id++) { DB::table('pages')->insert(['id' => $id, 'title' => $id === 1 ? 'Veto' : 'Item '.$id]); }
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('perPage', 10)->call('gotoPage', 2)
            ->set('selectedRecords', ['1'])->call('deleteRecord', 1)->assertHasNoErrors()->assertSet('selectedRecords', ['1']);
        $this->assertSame(2, $page->instance()->records()['rows']->currentPage());
        $this->assertDatabaseCount('pages', 11);
        $page->assertNotified(\Filament\Notifications\Notification::make()->title('Удаление отклонено моделью')->danger());
    }

    public function test_generic_global_literal_search_applies_to_all_related_label_branches(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        Schema::create('authors', function (Blueprint $table): void { $table->id(); $table->string('name'); });
        Schema::create('categories', function (Blueprint $table): void { $table->id(); $table->string('name'); });
        Schema::create('category_page', function (Blueprint $table): void { $table->id(); $table->unsignedBigInteger('page_id'); $table->unsignedBigInteger('category_id'); });
        Schema::create('page_notes', function (Blueprint $table): void { $table->id(); $table->string('page_code'); $table->string('label'); $table->softDeletes(); });
        DB::table('authors')->insert([['id' => 1, 'name' => 'Literal %_! label'], ['id' => 2, 'name' => 'Neutral label']]);
        DB::table('categories')->insert([['id' => 1, 'name' => 'Literal %_! label'], ['id' => 2, 'name' => 'Neutral label']]);
        DB::table('pages')->insert([['id' => 1, 'title' => 'alpha', 'author_id' => 1], ['id' => 2, 'title' => 'beta', 'author_id' => 2]]);
        DB::table('category_page')->insert([['page_id' => 1, 'category_id' => 1], ['page_id' => 2, 'category_id' => 2]]);
        DB::table('page_notes')->insert([
            ['page_code' => 'alpha', 'label' => 'Literal %_! label', 'deleted_at' => null], ['page_code' => 'beta', 'label' => 'Neutral label', 'deleted_at' => null],
        ]);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $configs = [
            'author_relation' => ['type' => 'belongsTo', 'table' => 'authors', 'column' => 'author_id', 'key' => 'id', 'label' => 'name'],
            'tags' => ['type' => 'belongsToMany', 'table' => 'categories', 'pivot_table' => 'category_page', 'label' => 'name'],
            'notes' => ['type' => 'hasMany', 'table' => 'page_notes', 'model' => BreadChildFixture::class, 'column' => 'page_code', 'key' => 'title', 'label' => 'label'],
            'first_note' => ['type' => 'hasOne', 'table' => 'page_notes', 'model' => BreadChildFixture::class, 'column' => 'page_code', 'key' => 'title', 'label' => 'label'],
        ];
        foreach ($configs as $field => $details) { DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => $field, 'type' => 'relationship', 'browse' => false, 'details' => json_encode($details)]); }
        foreach (array_keys($configs) as $field) {
            DB::table('data_rows')->where('data_type_id', $typeId)->update(['browse' => false]);
            DB::table('data_rows')->where('data_type_id', $typeId)->where('field', $field)->update(['browse' => true]);
            $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
            foreach (['%', '_', '!', 'label %_!'] as $search) {
                $page->set('search', $search);
                $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all(), $field.': '.$search);
            }
            $page->set('search', 'Literal %_?');
            $this->assertSame(0, $page->instance()->records()['rows']->total());
        }
        $this->assertDatabaseCount('pages', 2);
    }

    public function test_generic_global_search_treats_sql_wildcards_as_literal_text(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadListTranslatedFixture::class]);
        DB::table('pages')->insert([
            ['id' => 1, 'title' => '10% Sale'], ['id' => 2, 'title' => 'file_name'], ['id' => 3, 'title' => 'Wow!'],
            ['id' => 4, 'title' => 'path\\name'], ['id' => 5, 'title' => 'Neutral'], ['id' => 6, 'title' => '10X Sale'],
        ]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        foreach (['%' => [1], '_' => [2], '!' => [3], '\\' => [4], 'Sale %' => [1], '10_' => [], 'file%name' => []] as $search => $expected) {
            $page->set('search', $search);
            $this->assertSame($expected, $page->instance()->records()['rows']->pluck('id')->all());
        }
        DB::table('translations')->insert([
            ['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => 5, 'locale' => 'ru', 'value' => 'old%'],
            ['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => 5, 'locale' => 'ru', 'value' => 'new_!'],
            ['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => 1, 'locale' => 'ru', 'value' => null],
        ]);
        $page->call('changeLocale', 'ru')->set('search', '%');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        $page->set('search', '_');
        $this->assertSame([5, 2], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', '!');
        $this->assertSame([5, 3], $page->instance()->records()['rows']->pluck('id')->all());
        // Explicit field and legacy server-side LIKE retain their original wildcard contract.
        $page->call('changeLocale', 'en')->set('searchKey', 'title')->set('search', '%');
        $this->assertSame(6, $page->instance()->records()['rows']->total());
        DB::table('data_types')->where('id', $typeId)->update(['server_side' => true]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('search', '%');
        $this->assertSame(6, $page->instance()->records()['rows']->total());
        $this->assertDatabaseHas('pages', ['id' => 1, 'title' => '10% Sale']);
    }

    public function test_generic_smart_search_matches_words_across_columns_and_selected_captions(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')->update(['browse' => false]);
        DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'select_multiple', 'browse' => true,
            'details' => json_encode(['options' => ['o' => 'Ocean blue', 'm' => 'Mountain green']])]);
        DB::table('pages')->insert([
            ['id' => 1, 'title' => 'Red portrait', 'flags' => '["o","m"]', 'meta_description' => 'Hidden secret'],
            ['id' => 2, 'title' => 'Red portrait', 'flags' => '["o"]', 'meta_description' => 'Hidden secret'],
            ['id' => 3, 'title' => 'Green landscape', 'flags' => '["m","o"]', 'meta_description' => 'Hidden secret'],
        ]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        foreach (['Mountain Red', 'Red Mountain', '  Red   Mountain  ', 'Red "Mountain green"'] as $search) {
            $page->set('search', $search);
            $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        }
        $page->set('search', 'Mountain Ocean');
        $this->assertSame([3, 1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', '"Ocean blue"');
        $this->assertSame([3, 2, 1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', '"blue Ocean"');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        $page->set('search', 'Red secret');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        $page->set('search', '   ');
        $this->assertSame(3, $page->instance()->records()['rows']->total());
        $page->set('searchKey', 'title')->set('search', 'portrait Red');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        $page->set('searchFilter', 'equals')->set('search', 'Red portrait');
        $this->assertSame([2, 1], $page->instance()->records()['rows']->pluck('id')->all());
        DB::table('data_types')->where('id', $typeId)->update(['server_side' => true]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('search', 'portrait Red');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        $this->assertSame(['Off', 'Gloss Hint'], app(\App\Filament\Bread\BreadSmartSearch::class)->terms('Off "Gloss Hint"'));
        $this->assertDatabaseCount('pages', 3);
    }

    public function test_generic_inline_checkbox_has_no_search_text_and_is_disabled_without_edit_rights(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        Schema::table('pages', fn (Blueprint $table) => $table->string('enabled')->nullable());
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        // Use values that cannot match the id column (which browse always includes).
        DB::table('data_rows')->where('data_type_id', $typeId)->update(['browse' => false]);
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'enabled', 'type' => 'checkbox', 'browse' => true, 'edit' => true,
            'details' => json_encode(['on' => 'Active', 'off' => 'Inactive', 'browse_inline_checkbox' => false])]);
        DB::table('pages')->insert([['id' => 11, 'title' => 'Neutral', 'enabled' => '731'], ['id' => 12, 'title' => 'Neutral', 'enabled' => '0']]);
        foreach (['browse_inline_checkbox' => false, 'browse_inline_editor' => null] as $flag => $value) {
            DB::table('data_rows')->where('id', $rowId)->update(['details' => json_encode(['on' => 'Active', 'off' => 'Inactive', $flag => $value])]);
            $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
            $this->assertSame([], $page->instance()->records()['inlineValues']);
            $this->assertMatchesRegularExpression('/role="switch" disabled aria-checked="true"/', $page->html());
            $this->assertMatchesRegularExpression('/role="switch" disabled aria-checked="false"/', $page->html());
            $page->assertDontSee('wire:click="saveInlineField', false);
            foreach (['Active', 'Inactive', '731', '0'] as $needle) {
                $page->set('search', $needle);
                $this->assertSame(0, $page->instance()->records()['rows']->total());
            }
            $page->set('searchKey', 'enabled')->set('search', '731');
            $this->assertSame([11], $page->instance()->records()['rows']->pluck('id')->all());
            Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('saveInlineField', 11, 'enabled', 0)->assertForbidden();
        }
        $this->assertSame(['731', '0'], DB::table('pages')->orderBy('id')->pluck('enabled')->all());
    }

    public function test_generic_multiple_captions_searches_escaped_tags_and_entity_text_literally(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->update(['browse' => false]);
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'multiple_checkbox', 'browse' => true,
            'details' => json_encode(['options' => ['tag' => '<b>Active</b>', 'entity' => 'Rock &amp; Roll', 'plain' => 'Neutral', 'zero' => '0']])]);
        DB::table('pages')->insert([
            ['id' => 1, 'title' => 'One', 'flags' => '["tag"]'], ['id' => 2, 'title' => 'Two', 'flags' => '["entity"]'],
            ['id' => 3, 'title' => 'Three', 'flags' => '["plain"]'], ['id' => 4, 'title' => 'Four', 'flags' => '["unknown"]'],
            ['id' => 5, 'title' => 'Five', 'flags' => '["zero"]'],
        ]);
        foreach (['multiple_checkbox', 'select_multiple'] as $type) {
            DB::table('data_rows')->where('id', $rowId)->update(['type' => $type]);
            $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
            $page->assertSee('&lt;b&gt;Active&lt;/b&gt;', false)->assertSee('Rock &amp;amp; Roll', false)->assertDontSee('unknown');
            foreach (['<b>' => [1], '</b>' => [1], 'amp;' => [2], '"Rock & Roll"' => [], 'unknown' => [], '0' => []] as $needle => $expected) {
                $page->set('search', $needle);
                $this->assertSame($expected, $page->instance()->records()['rows']->pluck('id')->all());
            }
            $page->set('searchFilter', 'equals')->set('search', '<b>Active</b>');
            $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
            $page->set('search', 'Active');
            $this->assertSame(0, $page->instance()->records()['rows']->total());
        }
        $this->assertSame(['["tag"]', '["entity"]', '["plain"]', '["unknown"]', '["zero"]'], DB::table('pages')->orderBy('id')->pluck('flags')->all());
    }

    public function test_generic_file_media_sort_uses_visible_captions_and_preserves_raw_server_order(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        app()->setLocale('en');
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'file', 'browse' => true, 'display_name' => 'Attachment', 'details' => '{}']);
        $values = [1 => '[{"download_link":"a.pdf","original_name":"Zulu.pdf"}]',
            2 => '[{"download_link":"z.pdf","original_name":"Alpha"},{"download_link":"b.pdf","original_name":"z.pdf"}]',
            3 => '[{"download_link":"y.pdf","original_name":"alpha"},{"download_link":"c.pdf","original_name":"a.pdf"}]',
            4 => 'single.pdf', 5 => '[]', 6 => null,
            7 => '[{"download_link":"e.pdf","original_name":"ё.pdf"}]', 8 => '[{"download_link":"f.pdf","original_name":"Ё.pdf"}]'];
        foreach ($values as $id => $value) { DB::table('pages')->insert(['id' => $id, 'title' => 'Neutral', 'flags' => $value]); }
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('sortBy', 'flags');
        $this->assertSame([6, 5, 3, 2, 4, 1, 8, 7], $page->instance()->records()['rows']->pluck('id')->all());
        $page->call('sortBy', 'flags');
        $this->assertSame([8, 7, 1, 4, 2, 3, 6, 5], $page->instance()->records()['rows']->pluck('id')->all());
        $this->assertSame($values, DB::table('pages')->orderBy('id')->pluck('flags', 'id')->all());
        DB::table('data_types')->where('id', $typeId)->update(['server_side' => true]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('sortBy', 'flags');
        $this->assertSame(DB::table('pages')->orderBy('flags')->orderByDesc('id')->pluck('id')->all(), $page->instance()->records()['rows']->pluck('id')->all());
        DB::table('data_types')->where('id', $typeId)->update(['server_side' => false]);
        DB::table('data_rows')->where('id', $rowId)->update(['type' => 'media_picker']);
        DB::table('pages')->whereIn('id', [7,8])->delete();
        $values = [1 => '["z.jpg"]', 2 => '[]', 3 => '["a.jpg","b.jpg","c.jpg","z-hidden.jpg"]',
            4 => '["a.jpg","b.jpg","c.jpg","a-hidden.jpg"]', 5 => 'a single.jpg', 6 => null];
        foreach ($values as $id => $value) { DB::table('pages')->where('id', $id)->update(['flags' => $value]); }
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('sortBy', 'flags');
        $this->assertSame([6, 2, 5, 4, 3, 1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->call('sortBy', 'flags');
        $this->assertSame([1, 4, 3, 5, 6, 2], $page->instance()->records()['rows']->pluck('id')->all());
        DB::table('data_rows')->where('id', $rowId)->update(['details' => '{"show_as_images":true}']);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('sortBy', 'flags');
        $this->assertSame([5, 1, 6, 2, 4, 3], $page->instance()->records()['rows']->pluck('id')->all());
        $page->call('sortBy', 'flags');
        $this->assertSame([4, 3, 6, 2, 5, 1], $page->instance()->records()['rows']->pluck('id')->all());
        $this->assertSame($values, DB::table('pages')->orderBy('id')->pluck('flags', 'id')->all());
        $query = DB::table('pages');
        $this->assertFalse(app(\App\Filament\Bread\BreadBrowseFileSort::class)->apply($query, 'flags', (object) ['type'=>'file', 'details'=>'{"view":"custom"}'], 'asc'));
    }

    public function test_generic_file_media_global_search_uses_visible_captions(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        app()->setLocale('en');
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'file', 'browse' => true, 'display_name' => 'Attachment', 'details' => '{}']);
        $values = [1 => '[{"download_link":"docs/hidden-storage.pdf","original_name":"Report 100%_<b>literal</b>.pdf"}]',
            2 => 'docs/single.pdf', 3 => '[]', 4 => '[{"download_link":"docs/second.pdf","original_name":"Another.pdf"}]'];
        foreach ($values as $id => $value) { DB::table('pages')->insert(['id' => $id, 'title' => 'Neutral', 'flags' => $value]); }
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        foreach (['Report' => [1], '100%_' => [1], '<b>literal</b>' => [1], 'hidden-storage' => [], 'original_name' => [], 'Download' => [2], 'Report Neutral' => [1]] as $search => $ids) {
            $page->set('search', $search);
            $this->assertSame($ids, $page->instance()->records()['rows']->pluck('id')->all(), $search);
        }
        $page->set('searchKey', 'flags')->set('search', 'hidden-storage');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        $this->assertSame($values, DB::table('pages')->orderBy('id')->pluck('flags', 'id')->all());
        DB::table('data_rows')->where('id', $rowId)->update(['type' => 'media_picker']);
        $values = [1 => '["images/first.jpg","images/second.jpg","images/third.jpg","images/hidden.jpg"]', 2 => '[]', 3 => '', 4 => 'images/single.jpg'];
        foreach ($values as $id => $value) { DB::table('pages')->where('id', $id)->update(['flags' => $value]); }
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        foreach (['first.jpg' => [1], 'hidden.jpg' => [], 'single.jpg' => [4], 'And 1 more' => [1], '0 files' => [3, 2], 'first.jpg third.jpg' => [1]] as $search => $ids) {
            $page->set('search', $search);
            $this->assertSame($ids, $page->instance()->records()['rows']->pluck('id')->all(), $search);
        }
        DB::table('data_rows')->where('id', $rowId)->update(['details' => '{"show_as_images":true}']);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        foreach (['first.jpg' => [], 'single.jpg' => [], 'And 1 more' => [1], '0 files' => [3, 2]] as $search => $ids) {
            $page->set('search', $search);
            $this->assertSame($ids, $page->instance()->records()['rows']->pluck('id')->all(), $search);
        }
        $this->assertSame($values, DB::table('pages')->orderBy('id')->pluck('flags', 'id')->all());
    }

    public function test_generic_file_browse_renders_named_downloads_and_encoded_single_paths(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'file', 'browse' => true, 'display_name' => 'Attachment']);
        $files = [['download_link' => 'docs/Файл #1?.pdf', 'original_name' => '<b>Файл.pdf</b>'], ['download_link' => 'audio/track.mp3', 'original_name' => 'Audio']];
        $values = [json_encode($files), 'docs/single file.pdf', '[]', '[{"download_link":"javascript:alert(1)","original_name":"Unsafe"}]'];
        foreach ($values as $index => $value) { DB::table('pages')->insert(['id' => $index + 1, 'title' => 'Neutral', 'flags' => $value]); }
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        $page->assertSee('&lt;b&gt;Файл.pdf&lt;/b&gt;', false)->assertSee('Audio')->assertSee('Download');
        $page->assertSee('href="'.e(Storage::disk(config('voyager.storage.disk', 'public'))->url('docs/'.rawurlencode('Файл #1?.pdf'))).'"', false);
        $page->assertSee('href="'.e(Storage::disk(config('voyager.storage.disk', 'public'))->url('docs/single%20file.pdf')).'"', false);
        $page->assertDontSee('href="javascript:', false)->assertDontSee('<b>Файл.pdf</b>', false);
        foreach (['../private.pdf', '/absolute.pdf', 'folder\\bad.pdf', 'data:text/html,test', "bad\nfile.pdf"] as $path) {
            $this->assertNull(\App\Filament\Bread\BreadBrowseFiles::url($path));
        }
        $this->assertSame([], \App\Filament\Bread\BreadBrowseFiles::links('[]'));
        $this->assertSame($values, DB::table('pages')->orderBy('id')->pluck('flags')->all());
    }

    public function test_generic_media_picker_browse_respects_list_image_empty_and_more_modes(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        app()->setLocale('en');
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'media_picker', 'browse' => true, 'display_name' => 'Media', 'details' => '{}']);
        $values = [json_encode(['images/one.jpg', 'images/two.png', 'https://example.test/three.webp', 'images/four.jpg']), '[]', '', 'images/single #.jpg'];
        foreach ($values as $index => $value) { DB::table('pages')->insert(['id' => $index + 1, 'title' => 'Neutral', 'flags' => $value]); }
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        foreach (['images/one.jpg', 'images/two.png', 'https://example.test/three.webp', 'images/single #.jpg'] as $path) {
            $page->assertSee('<li>'.$path.'</li>', false);
        }
        $page->assertDontSee('<li>images/four.jpg</li>', false)->assertSee('And 1 more')->assertSee('0 files');
        $page->assertDontSee('alt="Media"', false);
        DB::table('data_rows')->where('id', $rowId)->update(['details' => '{"show_as_images":true}']);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        $page->assertSee('src="https://example.test/three.webp"', false)->assertSee('alt="Media"', false)->assertSee('And 1 more')->assertSee('0 files');
        $page->assertDontSee('<li>images/one.jpg</li>', false)->assertDontSee('src="'.e(Storage::disk(config('voyager.storage.disk', 'public'))->url('images/four.jpg')).'"', false);
        $page->assertSee('src="'.e(Storage::disk(config('voyager.storage.disk', 'public'))->url('images/single%20%23.jpg')).'"', false);
        $this->assertSame(['items' => ['a', 'b', 'c'], 'more' => 1, 'empty' => false, 'images' => false], \App\Filament\Bread\BreadBrowseFiles::media(['a', 'b', 'c', 'd'], ['show_as_images' => false]));
        $this->assertSame($values, DB::table('pages')->orderBy('id')->pluck('flags')->all());
    }

    public function test_generic_text_previews_preserve_literal_text_and_original_unicode_limit(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadPlainPreviewFixture::class]);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')->update(['browse' => false]);
        $values = [1 => str_repeat('Я', 199), 2 => str_repeat('Ж', 200), 3 => str_repeat('Ю', 201),
            4 => '<b>Literal</b> &amp; <script>marker</script>', 5 => '-1234.50', 6 => null];
        foreach ($values as $id => $value) { DB::table('pages')->insert(['id' => $id, 'title' => $value]); }
        foreach (['text', 'text_area', 'number'] as $type) {
            DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['type' => $type, 'details' => '{}']);
            $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
            $html = preg_replace('/<!--.*?-->/s', '', $page->html());
            foreach ([str_repeat('Я', 199), str_repeat('Ж', 200), str_repeat('Ю', 200).' ...', '-1234.50'] as $expected) {
                $this->assertStringContainsString('<div>'.$expected.'</div>', $html);
            }
            $this->assertStringContainsString('<div>&lt;b&gt;Literal&lt;/b&gt; &amp;amp; &lt;script&gt;marker&lt;/script&gt;</div>', $html);
            $this->assertStringNotContainsString('<script>marker</script>', $html);
            $this->assertSame(array_reverse($values, true), $page->instance()->records()['rows']->pluck('title', 'id')->all());
        }
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['type' => 'text', 'edit' => true, 'details' => '{"browse_inline_editor":true}']);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        $this->assertStringContainsString('<span>'.str_repeat('Ю', 200).' ...</span>', preg_replace('/<!--.*?-->/s', '', $page->html()));
        $this->assertSame(str_repeat('Ю', 201), $page->instance()->records()['inlineValues'][3]['title']);
        $this->assertSame($values, DB::table('pages')->orderBy('id')->pluck('title', 'id')->all());
    }

    public function test_generic_plain_text_search_uses_unicode_preview_and_keeps_raw_modes(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadPlainPreviewFixture::class]);
        DB::table('data_rows')->where('data_type_id', $typeId)->update(['browse' => false]);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['browse' => true]);
        $values = [1 => str_repeat('Я', 200).'HiddenTail', 2 => '<b>Visible</b> 100%_ '.str_repeat('Ю', 200), 3 => 'short'];
        foreach ($values as $id => $title) { DB::table('pages')->insert(['id' => $id, 'title' => $title]); }
        foreach (['text', 'text_area', 'number'] as $type) {
            DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['type' => $type]);
            $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
            $page->set('search', 'HiddenTail');
            $this->assertSame([], $page->instance()->records()['rows']->pluck('id')->all());
            $page->set('search', str_repeat('Я', 200).' ...');
            $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
            $page->set('search', '<b>Visible</b> 100%_');
            $this->assertSame([2], $page->instance()->records()['rows']->pluck('id')->all());
            $page->set('search', 'HiddenTail')->set('searchKey', 'title');
            $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        }
        DB::table('data_types')->where('id', $typeId)->update(['server_side' => true]);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['type' => 'text']);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('search', 'HiddenTail');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        DB::table('data_types')->where('id', $typeId)->update(['server_side' => false, 'model_name' => BreadListTranslatedFixture::class]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('search', 'HiddenTail');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        DB::table('translations')->insert(['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => 1, 'locale' => 'ru', 'value' => str_repeat('Ж', 210).'TranslatedTail']);
        $page->call('changeLocale', 'ru')->set('search', 'TranslatedTail');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        $this->assertSame($values, DB::table('pages')->orderBy('id')->pluck('title', 'id')->all());
    }

    public function test_generic_rich_text_search_uses_stripped_preview_and_full_translations(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadPlainPreviewFixture::class]);
        DB::table('data_rows')->where('data_type_id', $typeId)->update(['browse' => false]);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['browse' => true, 'type' => 'rich_text_box']);
        $values = [
            1 => '<p data-secret="HiddenAttribute">'.str_repeat('Я', 200).'HiddenTail</p>',
            2 => '<p><!--HiddenComment--><strong>Visible</strong><br><b class="KeepAttribute">Bold</b><i>Italic</i><u>Under</u>&amp; 100%_</p>',
            3 => '<div title="a > b">Quotes</div>',
        ];
        foreach ($values as $id => $title) { DB::table('pages')->insert(['id' => $id, 'title' => $title]); }
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        foreach (['HiddenTail', 'HiddenAttribute', 'HiddenComment', '<strong>', '<p>', '<br>'] as $term) {
            $page->set('search', $term);
            $this->assertSame([], $page->instance()->records()['rows']->pluck('id')->all());
        }
        foreach (['Visible', 'KeepAttribute', '<b', '&amp;', '100%_'] as $term) {
            $page->set('search', $term);
            $this->assertSame([2], $page->instance()->records()['rows']->pluck('id')->all());
        }
        $page->set('search', str_repeat('Я', 200).' ...');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', 'Quotes');
        $this->assertSame([3], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', 'HiddenAttribute')->set('searchKey', 'title');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadListTranslatedFixture::class]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('search', 'HiddenTail');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        DB::table('translations')->insert(['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => 1, 'locale' => 'ru', 'value' => '<p>'.str_repeat('Ж', 210).'TranslatedTail</p>']);
        $page->call('changeLocale', 'ru')->set('search', 'TranslatedTail');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        $this->assertSame($values, DB::table('pages')->orderBy('id')->pluck('title', 'id')->all());
    }

    public function test_generic_rich_text_preview_keeps_original_allowed_tags_as_escaped_text(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadPlainPreviewFixture::class]);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['type' => 'rich_text_box']);
        $value = '<p><b>Bold</b><i>Italic</i><u>Underline</u><strong>Strong</strong><br>&amp;</p>';
        DB::table('pages')->insert([['id' => 1, 'title' => $value], ['id' => 2, 'title' => '<p>'.str_repeat('Я', 201).'</p>']]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        $html = preg_replace('/<!--.*?-->/s', '', $page->html());
        $this->assertStringContainsString('<div>&lt;b&gt;Bold&lt;/b&gt;&lt;i&gt;Italic&lt;/i&gt;&lt;u&gt;Underline&lt;/u&gt;Strong&amp;amp;</div>', $html);
        $this->assertStringContainsString('<div>'.str_repeat('Я', 200).' ...</div>', $html);
        $this->assertStringNotContainsString('<b>Bold</b>', $html);
        $this->assertDatabaseHas('pages', ['id' => 1, 'title' => $value]);
    }

    public function test_generic_translated_text_preview_is_full_literal_text_in_each_language(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadListTranslatedFixture::class]);
        $base = '<p>'.str_repeat('Я', 220).'</p>';
        $translation = '<strong>'.str_repeat('Ю', 230).'</strong>';
        DB::table('pages')->insert(['id' => 1, 'title' => $base]);
        DB::table('translations')->insert(['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => 1, 'locale' => 'ru', 'value' => $translation]);
        foreach (['text', 'text_area', 'rich_text_box', 'number'] as $type) {
            DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['type' => $type]);
            $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
            $this->assertStringContainsString('<div>&lt;p&gt;'.str_repeat('Я', 220).'&lt;/p&gt;</div>', preg_replace('/<!--.*?-->/s', '', $page->html()));
            $page->call('changeLocale', 'ru');
            $this->assertStringContainsString('<div>&lt;strong&gt;'.str_repeat('Ю', 230).'&lt;/strong&gt;</div>', preg_replace('/<!--.*?-->/s', '', $page->html()));
        }
        $this->assertDatabaseHas('pages', ['id' => 1, 'title' => $base]);
        $this->assertDatabaseHas('translations', ['foreign_key' => 1, 'value' => $translation]);
    }

    public function test_generic_empty_multiple_caption_render_and_search_match_original(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        app()->setLocale('en');
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->update(['browse' => false]);
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'multiple_checkbox', 'browse' => true,
            'details' => json_encode(['options' => ['known' => 'Selected']])]);
        $values = [null, '', 'null', '[]', '{}', '["unknown"]', '["known"]', '{bad json', 'false', '0', '0.0', '""', '"0"'];
        foreach ($values as $index => $value) { DB::table('pages')->insert(['id' => $index + 1, 'title' => 'Neutral', 'flags' => $value]); }
        foreach (['multiple_checkbox' => [8, 5, 4, 3, 2, 1], 'select_multiple' => [13, 12, 11, 10, 9, 8, 4, 3, 2, 1]] as $type => $expected) {
            DB::table('data_rows')->where('id', $rowId)->update(['type' => $type]);
            $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('searchFilter', 'equals')->set('search', 'None');
            $this->assertSame($expected, $page->instance()->records()['rows']->pluck('id')->all());
            $this->assertSame(array_fill(0, count($expected), 'None'), $page->instance()->records()['rows']->pluck('flags')->all());
            $page->assertSee('None');
            $page->set('searchFilter', 'contains')->set('search', 'one');
            $this->assertSame($expected, $page->instance()->records()['rows']->pluck('id')->all());
            $page->set('search', 'Selected');
            $this->assertSame([7], $page->instance()->records()['rows']->pluck('id')->all());
            app()->setLocale('ru');
            $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('search', 'Отсутствует');
            $this->assertSame($expected, $page->instance()->records()['rows']->pluck('id')->all());
            $page->assertSee('Отсутствует');
            app()->setLocale('en');
        }
        $this->assertSame($values, DB::table('pages')->orderBy('id')->pluck('flags')->all());
    }

    public function test_generic_static_html_caption_entities_match_visible_text_in_render_and_search(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $options = ['a' => '<b>Rock &amp; Roll</b>', 'b' => '&lt;literal&gt; &quot;quoted&quot; &#169;'];
        DB::table('pages')->insert([['id' => 1, 'title' => 'a'], ['id' => 2, 'title' => 'b']]);
        foreach (['select_dropdown', 'radio_btn'] as $type) {
            DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['type' => $type, 'details' => json_encode(['options' => $options])]);
            $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
            $this->assertSame(['<literal> "quoted" ©', 'Rock & Roll'], $page->instance()->records()['rows']->pluck('title')->all());
            $page->assertSee('Rock &amp; Roll', false)->assertSee('&lt;literal&gt;', false)->assertDontSee('Rock &amp;amp; Roll', false);
            foreach (['"Rock & Roll"' => [1], '<literal>' => [2], '©' => [2], 'amp;' => [], '<b>' => []] as $needle => $expected) {
                $page->set('search', $needle);
                $this->assertSame($expected, $page->instance()->records()['rows']->pluck('id')->all());
            }
        }
        $this->assertSame(['a', 'b'], DB::table('pages')->orderBy('id')->pluck('title')->all());
    }

    public function test_generic_checkbox_caption_search_matches_php_truthiness_without_raw_false_positives(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        Schema::table('pages', fn (Blueprint $table) => $table->string('enabled')->nullable());
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'enabled', 'type' => 'checkbox', 'browse' => true,
            'details' => json_encode(['on' => 'Включено', 'off' => 'Выключено'])]);
        $values = ['0', '1', null, '2', 'false', '', '00', ' ', '0.0', '-1'];
        foreach ($values as $index => $value) { DB::table('pages')->insert(['id' => $index + 1, 'title' => 'Neutral', 'enabled' => $value]); }
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('searchFilter', 'equals')->set('search', 'Включено');
        $this->assertSame([10, 9, 8, 7, 5, 4, 2], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', '')->call('sortBy', 'enabled');
        $this->assertSame([10, 9, 8, 7, 5, 4, 2, 6, 3, 1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', 'Выключено');
        $this->assertSame([6, 3, 1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', 'false');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        $page->set('searchFilter', 'contains')->set('search', 'ключено');
        $this->assertSame(10, $page->instance()->records()['rows']->total());
        $page->set('search', 'missing caption');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        // Checkbox labels are escaped text in Voyager, not HTML like static dropdown captions.
        DB::table('data_rows')->where('id', $rowId)->update(['details' => json_encode(['on' => '<b>Active</b>', 'off' => 'Inactive'])]);
        $page->set('search', '<b>');
        $this->assertSame([10, 9, 8, 7, 5, 4, 2], $page->instance()->records()['rows']->pluck('id')->all());
        // An inline toggle carries no textual label catalog, including false-valued metadata flags.
        DB::table('data_rows')->where('id', $rowId)->update(['details' => json_encode(['on' => 'Включено', 'off' => 'Выключено', 'browse_inline_checkbox' => false])]);
        $page->set('search', 'Включено');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        DB::table('data_rows')->where('id', $rowId)->update(['details' => '{}']);
        $page->set('search', 'false');
        $this->assertSame([5], $page->instance()->records()['rows']->pluck('id')->all());
        $this->assertMatchesRegularExpression('/<td[^>]*>\s*false\s*<\/td>/', preg_replace('/<!--.*?-->/s', '', $page->html()));
        DB::table('data_rows')->where('id', $rowId)->update(['details' => json_encode(['on' => 'Включено', 'off' => 'Выключено'])]);
        $page->set('searchKey', 'enabled')->set('search', 'false');
        $this->assertSame([5], $page->instance()->records()['rows']->pluck('id')->all());
        DB::table('data_types')->where('id', $typeId)->update(['server_side' => true]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('searchKey', 'enabled')->set('search', 'false');
        $this->assertSame([5], $page->instance()->records()['rows']->pluck('id')->all());
        $this->assertSame($values, DB::table('pages')->orderBy('id')->pluck('enabled')->all());
    }

    public function test_generic_static_option_search_uses_visible_captions_and_active_translation(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $options = ['raw-ocean' => '<b>Ocean caption</b>', 'raw-mountain' => 'Mountain caption'];
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadListTranslatedFixture::class]);
        DB::table('pages')->insert([
            ['id' => 1, 'title' => 'raw-ocean'], ['id' => 2, 'title' => 'raw-mountain'], ['id' => 3, 'title' => 'unknown-key'],
        ]);
        foreach (['select_dropdown', 'radio_btn'] as $type) {
            DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update(['type' => $type, 'details' => json_encode(['options' => $options])]);
            $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
            $page->set('search', 'ocean caption');
            $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
            foreach (['raw-ocean', 'unknown-key', '<b>'] as $search) {
                $page->set('search', $search);
                $this->assertSame(0, $page->instance()->records()['rows']->total());
            }
            $page->set('searchFilter', 'equals')->set('search', 'Ocean caption');
            $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
            $page->set('search', 'Ocean');
            $this->assertSame(0, $page->instance()->records()['rows']->total());
            $page->set('searchKey', 'title')->set('search', 'raw-ocean');
            $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        }
        DB::table('translations')->insert([
            ['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => 1, 'locale' => 'ru', 'value' => 'raw-ocean'],
            ['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => 1, 'locale' => 'ru', 'value' => 'raw-mountain'],
            ['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => 3, 'locale' => 'ru', 'value' => 'raw-ocean'],
        ]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('changeLocale', 'ru')->set('search', 'Mountain caption');
        $this->assertSame([2, 1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', 'Ocean caption');
        $this->assertSame([3], $page->instance()->records()['rows']->pluck('id')->all());
        DB::table('translations')->where('foreign_key', 3)->update(['value' => null]);
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        DB::table('data_types')->where('id', $typeId)->update(['server_side' => true]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('searchKey', 'title')->set('search', 'unknown-key');
        $this->assertSame([3], $page->instance()->records()['rows']->pluck('id')->all());
        $this->assertDatabaseHas('pages', ['id' => 1, 'title' => 'raw-ocean']);
    }

    public function test_generic_static_option_sort_uses_captions_across_pages_but_server_side_uses_values(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update([
            'type' => 'select_dropdown', 'details' => json_encode(['options' => ['a' => 'Zulu', 'z' => 'alpha', 'q' => 'ALPHA']]),
        ]);
        for ($id = 1; $id <= 12; $id++) { DB::table('pages')->insert(['id' => $id, 'title' => $id <= 6 ? 'a' : 'z']); }
        DB::table('pages')->insert(['id' => 13, 'title' => 'q']);
        DB::table('pages')->insert(['id' => 14, 'title' => 'unknown']);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('perPage', 10)->call('sortBy', 'title');
        $this->assertSame([14, 13, 12, 11, 10, 9, 8, 7, 6, 5], $page->instance()->records()['rows']->pluck('id')->all());
        $this->assertSame('', $page->instance()->records()['rows']->first()->title);
        $page->call('gotoPage', 2);
        $this->assertSame([4, 3, 2, 1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->call('sortBy', 'title');
        $this->assertSame([6, 5, 4, 3, 2, 1, 13, 12, 11, 10], $page->instance()->records()['rows']->pluck('id')->all());
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadListTranslatedFixture::class]);
        DB::table('translations')->insert(['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => 4, 'locale' => 'ru', 'value' => 'z']);
        $translated = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('perPage', 10)->call('sortBy', 'title')->call('gotoPage', 2);
        $translated->call('changeLocale', 'ru');
        $this->assertSame([4, 3, 2, 1], $translated->instance()->records()['rows']->pluck('id')->all());
        $this->assertSame('alpha', $translated->instance()->records()['rows']->first()->title);
        DB::table('data_types')->where('id', $typeId)->update(['server_side' => true]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('perPage', 10)->call('sortBy', 'title');
        $this->assertSame([6, 5, 4, 3, 2, 1, 13, 14, 12, 11], $page->instance()->records()['rows']->pluck('id')->all());
    }

    public function test_generic_numeric_radio_and_checkbox_caption_sort_preserves_raw_values(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        Schema::table('pages', fn (Blueprint $table) => $table->string('enabled')->nullable());
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'title')->update([
            'type' => 'radio_btn', 'details' => json_encode(['options' => ['a' => '10', 'b' => '2', 'c' => '2.0']]),
        ]);
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'enabled', 'type' => 'checkbox', 'browse' => true,
            'details' => json_encode(['on' => 'Да', 'off' => 'Нет'])]);
        foreach (['0', '1', null, '2', 'false', ''] as $index => $value) {
            DB::table('pages')->insert(['id' => $index + 1, 'title' => ['a', 'b', 'c'][$index % 3], 'enabled' => $value]);
        }
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('sortBy', 'title');
        $this->assertSame([6, 5, 3, 2, 4, 1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->call('sortBy', 'enabled');
        $this->assertSame([5, 4, 2, 6, 3, 1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->call('sortBy', 'enabled');
        $this->assertSame([6, 3, 1, 5, 4, 2], $page->instance()->records()['rows']->pluck('id')->all());
        $this->assertDatabaseHas('pages', ['id' => 5, 'enabled' => 'false', 'title' => 'b']);
        // Inline checkbox has no on/off text in the original; it must bypass caption ordering.
        DB::table('data_rows')->where('id', $rowId)->update(['details' => json_encode(['on' => 'Да', 'off' => 'Нет', 'browse_inline_checkbox' => false])]);
        $query = (new \App\Models\Page)->newQuery()->toBase();
        $this->assertFalse(app(\App\Filament\Bread\BreadOptionSort::class)->apply($query, 'pages.enabled', DB::table('data_rows')->find($rowId), 'asc'));
        foreach ([['view' => 'custom'], ['relationship' => ['key' => 'id']], ['options' => ['a' => ['invalid']]], ['options' => []]] as $details) {
            $this->assertFalse(app(\App\Filament\Bread\BreadOptionSort::class)->apply($query, 'pages.title',
                (object) ['type' => 'select_dropdown', 'details' => json_encode($details)], 'asc'));
        }
    }

    public function test_generic_sort_keeps_original_base_order_when_locale_changes_across_pages(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        DB::table('data_types')->where('name', 'pages')->update(['model_name' => BreadListTranslatedFixture::class]);
        for ($id = 1; $id <= 12; $id++) {
            DB::table('pages')->insert(['id' => $id, 'title' => sprintf('Base %02d', $id)]);
            DB::table('translations')->insert(['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => $id, 'locale' => 'ru', 'value' => sprintf('Перевод %02d', 13 - $id)]);
        }
        // A blank translation stays blank; absence of a translation falls back to base.
        DB::table('translations')->where('foreign_key', 2)->update(['value' => null]);
        DB::table('translations')->where('foreign_key', 3)->delete();
        DB::table('translations')->insert(['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => 1, 'locale' => 'ru', 'value' => 'Последний перевод']);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('perPage', 10)->call('sortBy', 'title');
        $this->assertSame(range(1, 10), $page->instance()->records()['rows']->pluck('id')->all());
        $page->call('gotoPage', 2);
        $this->assertSame([11, 12], $page->instance()->records()['rows']->pluck('id')->all());
        $page->call('changeLocale', 'ru');
        $this->assertSame([11, 12], $page->instance()->records()['rows']->pluck('id')->all());
        $page->call('gotoPage', 1);
        $rows = $page->instance()->records()['rows'];
        $this->assertSame(range(1, 10), $rows->pluck('id')->all());
        $this->assertSame('Последний перевод', $rows->firstWhere('id', 1)->title);
        $this->assertNull($rows->firstWhere('id', 2)->title);
        $this->assertSame('Base 03', $rows->firstWhere('id', 3)->title);
        $page->call('gotoPage', 2);
        $this->assertSame([11, 12], $page->instance()->records()['rows']->pluck('id')->all());
        $page->call('sortBy', 'title');
        $this->assertSame(range(12, 3), $page->instance()->records()['rows']->pluck('id')->all());
        $page->call('gotoPage', 2);
        $this->assertSame([2, 1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->call('changeLocale', 'en');
        $this->assertSame([2, 1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->call('gotoPage', 1);
        $this->assertSame(range(12, 3), $page->instance()->records()['rows']->pluck('id')->all());
        $this->assertSame('Base 12', $page->instance()->records()['rows']->first()->title);
    }

    public function test_generic_advanced_browse_matches_original_raw_json_and_group_indicators(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        Schema::table('pages', fn (Blueprint $table) => $table->text('json_payload')->nullable());
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        $groupDetails = ['fields' => ['fallback' => ['type' => 'text', 'label' => 'Secret default label', 'value' => 'Secret default value']]];
        DB::table('data_rows')->insert([
            ['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'adv_fields_group', 'browse' => true, 'details' => json_encode($groupDetails)],
            ['data_type_id' => $typeId, 'field' => 'json_payload', 'type' => 'adv_json', 'browse' => true, 'details' => '{}'],
        ]);
        DB::table('data_rows')->where('data_type_id', $typeId)->where('field', 'meta_description')->update(['type' => 'adv_page_layout', 'browse' => true]);
        $group = json_encode(['fields' => [
            'filled' => ['type' => 'text', 'label' => 'Secret group label', 'value' => 'Secret group value'],
            'zero' => ['type' => 'number', 'label' => 'Zero', 'value' => '0'],
            'empty' => ['type' => 'textarea', 'label' => 'Empty', 'value' => ''],
        ]]);
        $json = '{"fields":{"x":"Title"},"rows":[{"x":"Content"}],"internal_nonce":"Legacy"}';
        $layout = '[{"type":"Field","key":"body","title":"Original section"}]';
        DB::table('pages')->insert([
            ['id' => 1, 'title' => 'Alpha', 'flags' => $group, 'json_payload' => $json, 'meta_description' => $layout],
            ['id' => 2, 'title' => 'Beta', 'flags' => null, 'json_payload' => '{broken JSON', 'meta_description' => '[]'],
        ]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])
            ->assertSee('viar-bread-group-indicators', false)->assertSee('data-filled="1"', false)->assertSee('data-filled="0"', false)
            ->assertDontSee('Secret group value')->assertDontSee('Secret group label')->assertDontSee('Secret default label')->assertDontSee('Secret default value');
        $record = $page->instance()->records()['rows']->firstWhere('id', 1);
        $this->assertSame($json, $record->json_payload);
        $this->assertSame($layout, $record->meta_description);
        $this->assertSame($group, $record->flags);
        foreach (['internal_nonce', 'Original section'] as $needle) {
            $page->set('search', $needle);
            $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        }
        $page->set('search', 'Secret group value');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        $page->set('search', 'broken JSON');
        $this->assertSame([2], $page->instance()->records()['rows']->pluck('id')->all());
        DB::table('translations')->insert(['table_name' => 'pages', 'column_name' => 'meta_description', 'foreign_key' => 1, 'locale' => 'ru', 'value' => '[{"type":"Field","key":"body","title":"Translated section"}]']);
        $page->call('changeLocale', 'ru')->set('search', 'Translated section');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', 'Original section');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        $codec = app(\App\Filament\Bread\BreadFieldsGroup::class);
        $this->assertSame([true, false, false], $codec->browseIndicators($group, $groupDetails));
        $this->assertSame([true], $codec->browseIndicators(null, $groupDetails));
        $this->assertSame([true], $codec->browseIndicators('{broken', $groupDetails));
        $this->assertSame([false, false, false], $codec->browseIndicators(null, []));
        $this->assertSame([], $codec->browseIndicators('{"fields":[]}', $groupDetails));
    }

    public function test_generic_dynamic_catalog_search_respects_labels_filters_and_pivot_json_keys(): void
    {
        $this->admin(['browse_admin', 'browse_pages', 'edit_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        Schema::create('select_nodes', function (Blueprint $table): void { $table->id(); $table->string('slug'); $table->string('name'); $table->boolean('active'); $table->softDeletes(); });
        Schema::create('select_links', function (Blueprint $table): void { $table->unsignedBigInteger('page_id'); $table->unsignedBigInteger('node_id'); $table->string('note')->nullable(); });
        DB::table('select_nodes')->insert([
            ['id' => 11, 'slug' => 'one', 'name' => 'Ocean catalog', 'active' => 1, 'deleted_at' => null],
            ['id' => 12, 'slug' => 'two', 'name' => 'Mountain catalog', 'active' => 1, 'deleted_at' => null],
            ['id' => 13, 'slug' => 'deleted', 'name' => 'Deleted catalog', 'active' => 1, 'deleted_at' => now()],
            ['id' => 14, 'slug' => 'inactive', 'name' => 'Inactive catalog', 'active' => 0, 'deleted_at' => null],
        ]);
        DB::table('pages')->insert([
            ['id' => 1, 'title' => 'Alpha', 'author_id' => 11, 'flags' => '["two"]'],
            ['id' => 2, 'title' => 'Beta', 'author_id' => 14, 'flags' => '["deleted"]'],
            ['id' => 3, 'title' => 'Gamma', 'author_id' => 13, 'flags' => '[]'],
        ]);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadDynamicCatalogFixture::class]);
        DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => 'author_id', 'type' => 'select_dropdown', 'browse' => true,
            'details' => json_encode(['options' => [], 'relationship' => ['key' => 'id', 'label' => 'name', 'where' => ['active', 1]]])]);
        $details = ['relationship' => ['key' => 'slug', 'label' => 'name']];
        $rowId = DB::table('data_rows')->insertGetId(['data_type_id' => $typeId, 'field' => 'flags', 'type' => 'select_multiple', 'browse' => true, 'details' => json_encode($details)]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        foreach (['Ocean catalog', 'Mountain catalog'] as $needle) {
            $page->set('search', $needle);
            $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        }
        foreach (['Deleted catalog', 'Inactive catalog'] as $needle) {
            $page->set('search', $needle);
            $this->assertSame(0, $page->instance()->records()['rows']->total());
        }
        $details['relationship']['editablePivotFields'] = ['note'];
        DB::table('data_rows')->where('id', $rowId)->update(['details' => json_encode($details)]);
        DB::table('pages')->where('id', 1)->update(['flags' => '{"two":{"note":"private attribute"}}']);
        DB::table('pages')->where('id', 2)->update(['flags' => '{"one":{"note":"two"}}']);
        $page->set('search', 'Mountain catalog');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', 'Ocean catalog');
        $this->assertSame([2, 1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', 'private attribute');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        DB::table('data_rows')->where('id', $rowId)->update(['details' => json_encode(['relationship' => ['key' => 'slug', 'label' => 'password', 'editablePivotFields' => ['note']]])]);
        $page->set('search', 'Mountain catalog');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        DB::table('data_rows')->where('id', $rowId)->update(['details' => json_encode($details)]);
        DB::table('data_types')->where('id', $typeId)->update(['model_name' => BreadRecordListCatalogFixture::class]);
        $page->set('search', '');
        $this->assertSame(3, $page->instance()->records()['rows']->total());
        // These methods throw on an empty model: search must not invent a shared List catalog.
        $page->set('search', 'Mountain catalog')->assertHasNoErrors();
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        foreach ([1 => [11 => 'Ocean catalog'], 2 => [12 => 'Mountain catalog']] as $id => $expected) {
            $editor = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->call('openEdit', $id)->assertHasNoErrors();
            $field = collect($editor->instance()->form->getFlatComponents())->first(fn ($field) => $field instanceof \Filament\Forms\Components\Select && $field->getName() === 'author_id');
            $this->assertSame($expected, $field->getOptions());
        }
    }

    public function test_generic_multiple_option_search_matches_selected_values_and_translations(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        Schema::table('pages', function (Blueprint $table): void { $table->text('choices')->nullable(); });
        DB::table('data_types')->where('name', 'pages')->update(['model_name' => BreadMultipleTranslatedFixture::class]);
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        foreach (['choices' => 'select_multiple', 'flags' => 'multiple_checkbox'] as $field => $type) {
            DB::table('data_rows')->insert(['data_type_id' => $typeId, 'field' => $field, 'type' => $type, 'browse' => true,
                'details' => json_encode(['options' => ['art' => 'Canvas art', 2 => 'Metal print', 22 => 'Large print', 'a"b' => 'Quoted option']])]);
        }
        DB::table('pages')->insert([
            ['id' => 1, 'title' => 'First', 'choices' => '["art",2]', 'flags' => '{"2":"2"}'],
            ['id' => 2, 'title' => 'Second', 'choices' => '[22]', 'flags' => '{"art":"22"}'],
            ['id' => 3, 'title' => 'Third', 'choices' => '{bad json', 'flags' => 'null'],
            ['id' => 4, 'title' => 'Fourth', 'choices' => '"art"', 'flags' => '{"nested":["art"]}'],
            ['id' => 5, 'title' => 'Fifth', 'choices' => '["a\\"b"]', 'flags' => '[]'],
        ]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('search', 'Canvas art');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('searchFilter', 'equals')->set('search', 'Metal print');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', 'Large print');
        $this->assertSame([2], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', 'Quoted option');
        $this->assertSame([5], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('searchFilter', 'contains')->set('search', 'print');
        $this->assertSame([2, 1], $page->instance()->records()['rows']->pluck('id')->all());
        $this->assertSame(2, $page->instance()->records()['rows']->total());
        DB::table('translations')->insert(['table_name' => 'pages', 'column_name' => 'choices', 'foreign_key' => 1, 'locale' => 'ru', 'value' => '[22]']);
        $page->call('changeLocale', 'ru')->set('search', 'Canvas art');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        $page->set('search', 'Large print');
        $this->assertSame([2, 1], $page->instance()->records()['rows']->pluck('id')->all());
        DB::table('translations')->where('foreign_key', 1)->where('column_name', 'choices')->update(['value' => null]);
        $page->set('search', 'Canvas art');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        DB::table('translations')->where('foreign_key', 1)->where('column_name', 'choices')->delete();
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        DB::table('data_rows')->where('data_type_id', $typeId)->whereIn('field', ['choices', 'flags'])->update(['browse' => false]);
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        DB::table('data_rows')->where('data_type_id', $typeId)->whereIn('field', ['choices', 'flags'])->update(['browse' => true]);
        DB::table('data_types')->where('id', $typeId)->update(['server_side' => true]);
        $this->assertSame(0, $page->instance()->records()['rows']->total());
    }

    public function test_generic_client_style_search_uses_visible_numbers_option_labels_and_current_translation(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        Schema::table('data_types', fn (Blueprint $table) => $table->boolean('server_side')->default(false));
        DB::table('data_types')->where('name', 'pages')->update(['model_name' => BreadListTranslatedFixture::class]);
        Schema::table('pages', function (Blueprint $table): void { $table->integer('quantity')->nullable(); $table->boolean('active')->nullable(); $table->string('kind')->nullable(); });
        $typeId = DB::table('data_types')->where('name', 'pages')->value('id');
        DB::table('data_rows')->insert([
            ['data_type_id' => $typeId, 'field' => 'quantity', 'type' => 'number', 'browse' => true, 'details' => '{}'],
            ['data_type_id' => $typeId, 'field' => 'active', 'type' => 'checkbox', 'browse' => true, 'details' => json_encode(['on' => 'Enabled', 'off' => 'Disabled'])],
            ['data_type_id' => $typeId, 'field' => 'kind', 'type' => 'select_dropdown', 'browse' => true, 'details' => json_encode(['options' => ['canvas' => 'Canvas Print', 'poster' => 'Poster']])],
        ]);
        DB::table('pages')->insert([
            ['id' => 1, 'title' => 'English title', 'quantity' => 73, 'active' => 1, 'kind' => 'canvas'],
            ['id' => 2, 'title' => 'Fallback title', 'quantity' => 84, 'active' => 0, 'kind' => 'poster'],
        ]);
        DB::table('translations')->insert(['table_name' => 'pages', 'column_name' => 'title', 'foreign_key' => 1, 'locale' => 'ru', 'value' => 'Русское название']);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages'])->set('search', '73');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('searchFilter', 'equals')->set('search', 'Enabled');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', 'Poster');
        $this->assertSame([2], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', '1');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->call('changeLocale', 'ru')->set('search', 'Русское название');
        $this->assertSame([1], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', 'Fallback title');
        $this->assertSame([2], $page->instance()->records()['rows']->pluck('id')->all());
        $page->set('search', 'English title');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        DB::table('translations')->where('foreign_key', 1)->update(['value' => null]);
        $this->assertSame(0, $page->instance()->records()['rows']->total());
    }

    public function test_generic_browse_and_search_apply_model_global_scopes_with_and_without_local_scope(): void
    {
        $this->admin(['browse_admin', 'browse_pages']);
        DB::table('data_types')->where('name', 'pages')->update(['model_name' => BreadGlobalScopeFixture::class]);
        DB::table('pages')->insert([['title' => 'Allowed', 'meta_description' => 'Visible'], ['title' => 'Excluded', 'meta_description' => 'Hidden']]);
        $page = Livewire::test(VoyagerBread::class, ['type' => 'pages']);
        $this->assertSame(['Allowed'], $page->instance()->records()['rows']->pluck('title')->all());
        $page->set('search', 'Excluded');
        $this->assertSame(0, $page->instance()->records()['rows']->total());
        DB::table('data_types')->where('name', 'pages')->update(['details' => json_encode(['scope' => 'visibleForBread'])]);
        $page->set('search', '');
        $this->assertSame(['Allowed'], $page->instance()->records()['rows']->pluck('title')->all());
        $this->assertDatabaseCount('pages', 2);
    }

    public function test_cache_tools_get_does_not_run_commands_and_permission_is_required(): void
    {
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->never();
        $this->admin(['browse_admin']);
        $this->get('/filament/cache-tools')->assertForbidden();
        Livewire::test(\App\Filament\Pages\CacheTools::class)->assertForbidden();
        $this->admin(['browse_admin', 'browse_compass']);
        Livewire::test(\App\Filament\Pages\CacheTools::class)->assertSee('Очистить кеш приложения')->assertSee('Собрать кеш конфигурации и маршрутов')
            ->call('run', 'migrate:fresh')->assertHasErrors('operation');
    }

    public function test_cache_tools_authorized_http_get_is_read_only(): void
    {
        $this->admin(['browse_admin', 'browse_compass']);
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->never();
        $this->get('/filament/cache-tools')->assertOk()->assertSee('Очистить кеш приложения')->assertSee('Собрать кеш конфигурации и маршрутов');
    }

    public function test_cache_tools_execute_only_the_original_commands_in_order(): void
    {
        $this->admin(['browse_admin', 'browse_compass']);
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->with('cache:clear')->once()->ordered()->andReturn(0);
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->with('config:cache')->once()->ordered()->andReturn(0);
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->with('route:cache')->once()->ordered()->andReturn(0);
        Livewire::test(\App\Filament\Pages\CacheTools::class)->call('run', 'clear')->assertHasNoErrors()
            ->assertNotified(\Filament\Notifications\Notification::make()->title('Кеш приложения очищен')->success());
        Livewire::test(\App\Filament\Pages\CacheTools::class)->call('run', 'build')->assertHasNoErrors()
            ->assertNotified(\Filament\Notifications\Notification::make()->title('Кеш конфигурации и маршрутов собран')->success());
    }

    public function test_cache_tools_reports_partial_failure_and_does_not_expose_exception_secrets(): void
    {
        $this->admin(['browse_admin', 'browse_compass']);
        \Illuminate\Support\Facades\Log::spy();
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->with('config:cache')->once()->ordered()->andReturn(0);
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->with('route:cache')->once()->ordered()->andReturn(2);
        Livewire::test(\App\Filament\Pages\CacheTools::class)->call('run', 'build')
            ->assertNotified(\Filament\Notifications\Notification::make()->title('Не удалось выполнить route:cache')->danger()->body('Уже выполнено: config:cache.'));
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->with('cache:clear')->once()->andThrow(new \RuntimeException('SECRET_SYNTHETIC_VALUE'));
        Livewire::test(\App\Filament\Pages\CacheTools::class)->call('run', 'clear')->assertDontSee('SECRET_SYNTHETIC_VALUE')
            ->assertNotified(\Filament\Notifications\Notification::make()->title('Не удалось выполнить cache:clear')->danger()->body('Операция не завершена.'));
        \Illuminate\Support\Facades\Log::shouldHaveReceived('warning')->with('Admin cache command failed', ['command' => 'cache:clear', 'exit_code' => -1, 'exception' => \RuntimeException::class])->once();
    }

    public function test_cache_tools_permission_is_rechecked_before_execution(): void
    {
        $this->admin(['browse_admin', 'browse_compass']);
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->never();
        $page = Livewire::test(\App\Filament\Pages\CacheTools::class);
        $this->admin(['browse_admin']);
        $page->call('run', 'clear')->assertForbidden();
    }

    public function test_compass_resources_preserve_original_icon_catalogue_and_filter_without_writes(): void
    {
        config(['voyager.compass_in_production' => true]);
        $this->admin(['browse_admin', 'browse_compass']);
        $this->get('/filament/compass')->assertOk()->assertSee('Иконки Voyager')->assertSee('Документация Voyager');
        $page = Livewire::test(\App\Filament\Pages\Compass::class);
        $this->assertCount(206, $page->instance()->icons());
        $this->assertContains('voyager-bread', $page->instance()->icons());
        $this->assertContains('voyager-home', $page->instance()->icons());
        $this->assertSame('a', $page->instance()->iconCharacter('voyager-bread'));
        $this->assertSame('', $page->instance()->iconCharacter('untrusted-class'));
        $page->set('iconSearch', 'VOYAGER-HOME');
        foreach ($page->instance()->icons() as $name) { $this->assertStringContainsString('voyager-home', $name); }
        $page->set('iconSearch', 'No-icon-matches-123')->assertSee('Иконки не найдены.');
        $this->assertSame([], $page->instance()->icons());
        $css = file_get_contents(public_path('vendor/voyager-icons/icons.css'));
        $this->assertStringNotContainsString('voyager-assets?', $css);
        foreach (['woff', 'ttf', 'svg', 'eot'] as $extension) { $this->assertFileExists(public_path('vendor/voyager-icons/fonts/voyager.'.$extension)); }
        $this->assertFileExists(public_path('vendor/voyager-icons/LICENSE'));
    }

    public function test_compass_resources_apply_original_permission_and_environment_gate(): void
    {
        config(['voyager.compass_in_production' => true]);
        $this->admin(['browse_admin']);
        $this->get('/filament/compass')->assertForbidden();
        $this->admin(['browse_admin', 'browse_compass']);
        $originalEnvironment = $this->app['env'];
        try {
            $this->app['env'] = 'production';
            config(['voyager.compass_in_production' => false]);
            $this->assertFalse(\App\Filament\Pages\Compass::canAccess());
            Livewire::test(\App\Filament\Pages\Compass::class)->assertForbidden();
            $this->app['env'] = 'local';
            $this->assertTrue(\App\Filament\Pages\Compass::canAccess());
            $this->app['env'] = 'production';
            config(['voyager.compass_in_production' => true]);
            $this->assertTrue(\App\Filament\Pages\Compass::canAccess());
        } finally { $this->app['env'] = $originalEnvironment; }
    }

    public function test_menu_icon_catalogue_hint_requires_compass_access(): void
    {
        $this->createMenuManagementTables();
        DB::table('menus')->insert(['name' => 'admin']);
        config(['voyager.compass_in_production' => true]);
        $this->admin(['browse_admin', 'browse_menus', 'add_menus']);
        Livewire::test(VoyagerMenuItems::class)->call('openCreate')->assertDontSee('Каталог иконок');
        $this->admin(['browse_admin', 'browse_menus', 'add_menus', 'browse_compass']);
        Livewire::test(VoyagerMenuItems::class)->call('openCreate')->assertSee('Каталог иконок')->assertSee('/filament/compass#fonts');
    }

    public function test_compass_log_tab_reads_filters_and_pages_artificial_logs_without_writes(): void
    {
        Storage::fake('compass-logs-ui');
        $disk = Storage::disk('compass-logs-ui');
        $content = '';
        for ($index = 1; $index <= 24; $index++) { $content .= '[2026-10-16 10:00:00] local.INFO: Synthetic '.$index."\n"; }
        $content .= "[2026-10-16 10:00:01] local.ERROR: <script>synthetic</script>\n#0 Synthetic stack trace\n";
        $disk->put('laravel-test.log', $content);
        $this->app->instance(\App\Services\Admin\CompassLogReader::class, new \App\Services\Admin\CompassLogReader($disk->path('')));
        config(['voyager.compass_in_production' => true]);
        $this->admin(['browse_admin', 'browse_compass']);
        $page = Livewire::test(\App\Filament\Pages\Compass::class)->call('selectTab', 'logs')
            ->assertSet('logFile', 'laravel-test.log')->assertSee('Synthetic stack trace')->assertSee('<script>synthetic</script>')
            ->assertDontSee('<script>synthetic</script>', false);
        $this->assertSame(25, $page->instance()->logData()['total']);
        $this->assertCount(10, $page->instance()->logData()['entries']);
        $page->call('changeLogPage', 2)->assertSet('logPage', 2);
        $this->assertSame('Synthetic 15', $page->instance()->logData()['entries'][0]['text']);
        $page->set('logLevel', 'error')->assertSet('logPage', 1);
        $this->assertSame(1, $page->instance()->logData()['total']);
        $page->set('logSearch', 'No-match')->assertSee('Записи не найдены.');
        $page->set('logSearch', '')->set('logLevel', '')->set('logPerPage', 25);
        $this->assertCount(25, $page->instance()->logData()['entries']);
        $page->set('logFile', '../outside.log')->assertSee('Файл журнала не найден.');
        $this->assertSame($content, $disk->get('laravel-test.log'));
        $this->assertSame(['laravel-test.log'], $disk->allFiles());
    }

    public function test_compass_log_tab_handles_empty_directory_and_denies_revoked_access(): void
    {
        Storage::fake('compass-logs-ui');
        $this->app->instance(\App\Services\Admin\CompassLogReader::class,
            new \App\Services\Admin\CompassLogReader(Storage::disk('compass-logs-ui')->path('')));
        config(['voyager.compass_in_production' => true]);
        $this->admin(['browse_admin', 'browse_compass']);
        $page = Livewire::test(\App\Filament\Pages\Compass::class)->call('selectTab', 'logs')->assertSee('Журналов нет')->assertSee('Записи не найдены.');
        $this->admin(['browse_admin']);
        $page->call('selectTab', 'logs')->assertForbidden();
    }

    public function test_compass_log_download_streams_exact_file_and_rejects_foreign_paths(): void
    {
        Storage::fake('compass-logs-ui');
        $disk = Storage::disk('compass-logs-ui');
        $disk->put('download.log', "Synthetic bytes\r\n");
        $this->app->instance(\App\Services\Admin\CompassLogReader::class, new \App\Services\Admin\CompassLogReader($disk->path('')));
        config(['voyager.compass_in_production' => true]);
        $this->admin(['browse_admin', 'browse_compass']);
        $response = $this->get('/filament/compass/download?file=download.log')->assertOk()->assertDownload('download.log');
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\BinaryFileResponse::class, $response->baseResponse);
        $this->assertTrue($response->baseResponse->headers->hasCacheControlDirective('no-store'));
        $this->assertSame("Synthetic bytes\r\n", file_get_contents($response->baseResponse->getFile()->getPathname()));
        $this->getJson('/filament/compass/download?file=..%2Foutside.log')->assertUnprocessable();
        $this->getJson('/filament/compass/download?file=missing.log')->assertUnprocessable();
        $this->getJson('/filament/compass/download?file[]=download.log')->assertUnprocessable();
        $this->assertSame("Synthetic bytes\r\n", $disk->get('download.log'));
    }

    public function test_compass_log_download_does_not_apply_the_preview_size_limit(): void
    {
        Storage::fake('compass-logs-ui');
        $disk = Storage::disk('compass-logs-ui');
        $stream = fopen($disk->path('large.log'), 'wb');
        try { ftruncate($stream, \App\Services\Admin\CompassLogReader::MAX_FILE_SIZE + 1); } finally { fclose($stream); }
        $this->app->instance(\App\Services\Admin\CompassLogReader::class, new \App\Services\Admin\CompassLogReader($disk->path('')));
        config(['voyager.compass_in_production' => true]);
        $this->admin(['browse_admin', 'browse_compass']);
        $response = $this->get('/filament/compass/download?file=large.log')->assertOk()->assertDownload('large.log');
        $this->assertSame(\App\Services\Admin\CompassLogReader::MAX_FILE_SIZE + 1, $response->baseResponse->getFile()->getSize());
    }

    public function test_compass_log_download_requires_permission(): void
    {
        config(['voyager.compass_in_production' => true]);
        $this->admin(['browse_admin']);
        $this->get('/filament/compass/download?file=laravel.log')->assertForbidden();
    }

    public function test_compass_log_delete_confirmation_cancels_and_deletes_only_its_snapshot(): void
    {
        Storage::fake('compass-logs-ui');
        $disk = Storage::disk('compass-logs-ui');
        $disk->put('first.log', 'First');
        $disk->put('second.log', 'Second');
        $disk->put('notes.txt', 'Keep');
        $this->app->instance(\App\Services\Admin\CompassLogReader::class, new \App\Services\Admin\CompassLogReader($disk->path('')));
        config(['voyager.compass_in_production' => true]);
        $this->admin(['browse_admin', 'browse_compass']);
        $page = Livewire::test(\App\Filament\Pages\Compass::class)->call('selectTab', 'logs')
            ->call('requestLogDeletion')->assertSet('logsToDelete', ['second.log']);
        $this->assertSame('Second', $disk->get('second.log'));
        $page->call('cancelLogDeletion')->assertSet('logsToDelete', []);
        $this->assertSame('Second', $disk->get('second.log'));
        $page->call('requestLogDeletion', true)->assertSet('logsToDelete', ['second.log', 'first.log']);
        $disk->put('new-after-dialog.log', 'Keep new');
        $page->call('confirmLogDeletion')->assertHasNoErrors()->assertSet('logsToDelete', [])
            ->assertSet('logFile', 'new-after-dialog.log');
        $this->assertSame(['new-after-dialog.log', 'notes.txt'], $disk->allFiles());
        $this->assertSame('Keep new', $disk->get('new-after-dialog.log'));
        $page->call('requestLogDeletion')->call('confirmLogDeletion')->assertSet('logFile', null);
        $disk->assertExists('notes.txt');
    }

    public function test_compass_log_delete_handles_stale_selection_and_revoked_permission(): void
    {
        Storage::fake('compass-logs-ui');
        $disk = Storage::disk('compass-logs-ui');
        $disk->put('first.log', 'First');
        $disk->put('second.log', 'Second');
        $this->app->instance(\App\Services\Admin\CompassLogReader::class, new \App\Services\Admin\CompassLogReader($disk->path('')));
        config(['voyager.compass_in_production' => true]);
        $this->admin(['browse_admin', 'browse_compass']);
        $page = Livewire::test(\App\Filament\Pages\Compass::class)->call('selectTab', 'logs')->call('requestLogDeletion', true);
        $disk->delete('first.log');
        $page->call('confirmLogDeletion')->assertHasErrors('logDelete');
        $this->assertSame('Second', $disk->get('second.log'));
        $page->call('cancelLogDeletion')->call('requestLogDeletion');
        $this->admin(['browse_admin']);
        $page->call('confirmLogDeletion')->assertForbidden();
        $this->assertSame('Second', $disk->get('second.log'));
    }

    public function test_compass_log_download_requires_filament_login(): void
    {
        $this->get('/filament/compass/download?file=laravel.log')->assertRedirect('/filament/login');
    }

    public function test_compass_log_delete_partial_failure_retains_failed_names_for_retry(): void
    {
        Storage::fake('compass-logs-ui');
        $disk = Storage::disk('compass-logs-ui');
        $disk->put('remove.log', 'Remove');
        $disk->put('locked.log', 'Keep');
        $reader = new class($disk->path('')) extends \App\Services\Admin\CompassLogReader {
            protected function removeFile(string $path): bool { return basename($path) === 'locked.log' ? false : parent::removeFile($path); }
        };
        $this->app->instance(\App\Services\Admin\CompassLogReader::class, $reader);
        config(['voyager.compass_in_production' => true]);
        $this->admin(['browse_admin', 'browse_compass']);
        $page = Livewire::test(\App\Filament\Pages\Compass::class)->call('selectTab', 'logs')->call('requestLogDeletion', true)
            ->call('confirmLogDeletion')->assertHasErrors('logDelete')->assertSet('logsToDelete', ['locked.log']);
        $this->assertSame('Keep', $disk->get('locked.log'));
        $disk->assertMissing('remove.log');
        $page->call('cancelLogDeletion')->assertSet('logsToDelete', []);
    }

    public function test_compass_command_catalogue_filters_deduplicates_aliases_and_keeps_argument_drafts(): void
    {
        config(['voyager.compass_in_production' => true]);
        $this->admin(['browse_admin', 'browse_compass']);
        $first = (new \Symfony\Component\Console\Command\Command('fixture:first'))->setDescription('Первая команда');
        $second = (new \Symfony\Component\Console\Command\Command('fixture:second'))->setDescription('Вторая команда');
        $hidden = (new \Symfony\Component\Console\Command\Command('fixture:hidden'))->setHidden(true);
        \Illuminate\Support\Facades\Artisan::shouldReceive('all')->andReturn(['fixture:first' => $first, 'fixture:alias' => $first, 'fixture:second' => $second, 'fixture:hidden' => $hidden]);
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->never();
        $page = Livewire::test(\App\Filament\Pages\Compass::class)->call('selectTab', 'commands');
        $this->assertSame(['fixture:first' => 'Первая команда', 'fixture:second' => 'Вторая команда'], $page->instance()->commands());
        $html = $page->html();
        $this->assertStringNotContainsString('@js(', $html);
        $document = new \DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $actions = [];
        foreach ($document->getElementsByTagName('button') as $button) {
            $actions[] = $button->getAttribute('wire:click');
        }
        $this->assertContains("selectCommand('fixture:first')", $actions);
        $this->assertContains("selectCommand('fixture:second')", $actions);
        $page->set('commandSearch', 'ПЕРВАЯ');
        $this->assertSame(['fixture:first' => 'Первая команда'], $page->instance()->commands());
        $page->call('selectCommand', 'fixture:first')->set('commandArgs', '--name="First draft"')
            ->call('selectCommand', 'fixture:second')->assertSet('commandArgs', '')
            ->set('commandArgs', '--name="Second draft"')->call('selectCommand', 'fixture:first')->assertSet('commandArgs', '--name="First draft"');
        $page->call('clearCommandOutput')->assertSet('lastCommandName', null);
    }

    public function test_compass_commands_validate_registered_name_before_execution(): void
    {
        config(['voyager.compass_in_production' => true]);
        $this->admin(['browse_admin', 'browse_compass']);
        $command = new \Symfony\Component\Console\Command\Command('fixture:first');
        \Illuminate\Support\Facades\Artisan::shouldReceive('all')->andReturn(['fixture:first' => $command]);
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->never();
        $page = Livewire::test(\App\Filament\Pages\Compass::class)->call('selectTab', 'commands');
        $page->call('runCommand')->assertHasErrors('commandName');
        $page->set('commandName', 'fixture:first; injected')->call('runCommand')->assertHasErrors('commandName');
        $page->set('commandName', 'fixture:missing')->call('runCommand')->assertHasErrors('commandName');
        $page->set('commandName', 'fixture:first')->set('commandArgs', str_repeat('a', 65536))->call('runCommand')->assertHasErrors('commandArgs');
    }

    public function test_compass_command_execution_preserves_quoted_arguments_and_escapes_output_and_errors(): void
    {
        config(['voyager.compass_in_production' => true]);
        $this->admin(['browse_admin', 'browse_compass']);
        $command = new \Symfony\Component\Console\Command\Command('fixture:first');
        \Illuminate\Support\Facades\Artisan::shouldReceive('all')->andReturn(['fixture:first' => $command]);
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->with('fixture:first --name="quoted value" --flag')->once()->andReturn(0);
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->with('fixture:first')->once()->andReturn(7);
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->with('fixture:first --throw')->once()->andThrow(new \RuntimeException('<script>synthetic-error</script>'));
        \Illuminate\Support\Facades\Artisan::shouldReceive('output')->twice()->andReturn('<b>Synthetic output</b>', 'Synthetic failure output');
        $page = Livewire::test(\App\Filament\Pages\Compass::class)->call('selectTab', 'commands')->call('selectCommand', 'fixture:first')
            ->set('commandArgs', '--name="quoted value" --flag')->call('runCommand')->assertHasNoErrors()->assertSet('commandExitCode', 0)
            ->assertSet('commandOutput', '<b>Synthetic output</b>')->assertSee('<b>Synthetic output</b>')->assertDontSee('<b>Synthetic output</b>', false)
            ->assertNotified(\Filament\Notifications\Notification::make()->title('Команда выполнена')->success());
        $page->set('commandArgs', '')->call('runCommand')->assertSet('commandExitCode', 7)->assertSet('commandOutput', 'Synthetic failure output')
            ->assertNotified(\Filament\Notifications\Notification::make()->title('Команда завершилась ошибкой')->danger());
        $page->set('commandArgs', '--throw')->call('runCommand')->assertSet('commandExitCode', 1)
            ->assertSet('commandOutput', '<script>synthetic-error</script>')->assertDontSee('<script>synthetic-error</script>', false);
        $page->call('clearCommandOutput')->assertSet('commandOutput', '')->assertSet('commandExitCode', null)->assertSet('lastCommandName', null);
    }

    public function test_compass_command_execution_rechecks_permission_and_environment(): void
    {
        config(['voyager.compass_in_production' => true]);
        $this->admin(['browse_admin', 'browse_compass']);
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->never();
        $page = Livewire::test(\App\Filament\Pages\Compass::class);
        config(['voyager.compass_in_production' => false]);
        try {
            $page->instance()->runCommand();
            $this->fail('Environment gate must prevent execution.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) { $this->assertSame(403, $error->getStatusCode()); }
        config(['voyager.compass_in_production' => true]);
        $this->admin(['browse_admin']);
        $page->call('runCommand')->assertForbidden();
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

class BreadDynamicCatalogFixture extends \App\Models\Page
{
    protected $table = 'pages';
    public function authorId(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BreadSelectNodeFixture::class, 'author_id');
    }
    public function flags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(BreadSelectNodeFixture::class, 'select_links', 'page_id', 'node_id');
    }
}

class BreadRecordListCatalogFixture extends BreadDynamicCatalogFixture
{
    public function authorIdList(): \Illuminate\Support\Collection
    {
        if (! $this->exists) { throw new \LogicException('List requires a persisted record'); }
        return BreadSelectNodeFixture::query()->where('id', $this->title === 'Alpha' ? 11 : 12)->get();
    }
    public function flagsList(): \Illuminate\Support\Collection
    {
        if (! $this->exists) { throw new \LogicException('List requires a persisted record'); }
        return BreadSelectNodeFixture::query()->where('slug', $this->title === 'Alpha' ? 'two' : 'one')->get();
    }
}

class BreadPlainPreviewFixture extends \App\Models\Page
{
    protected $table = 'pages';
    protected $translatable = [];
}

class BreadMultipleTranslatedFixture extends \App\Models\Page
{
    protected $table = 'pages';
    protected $translatable = ['choices'];
}

class BreadCustomBrowseAccessorFixture extends \App\Models\Page
{
    protected $table = 'pages';
    public function getTitleBrowseAttribute(): string { return 'Accessor: '.$this->getRawOriginal('title'); }
}

class BreadListTranslatedFixture extends \App\Models\Page
{
    protected $table = 'pages';
    protected $translatable = ['title'];
}

class BreadGlobalScopeFixture extends \App\Models\Page
{
    protected $table = 'pages';
    protected static function booted(): void { static::addGlobalScope('visible', fn ($query) => $query->where('pages.meta_description', 'Visible')); }
    public function scopeVisibleForBread($query) { return $query->where('pages.meta_description', 'Visible'); }
}

class BreadOrderVetoFixture extends \App\Models\Page
{
    protected $table = 'pages';
    protected static function booted(): void { static::saving(fn ($model) => (int) $model->getKey() !== 1); }
}

class BreadDeleteVetoFixture extends \App\Models\Page
{
    protected $table = 'pages';

    protected static function booted(): void
    {
        static::deleting(fn ($record) => $record->title === 'Veto' ? false : null);
    }
}

class BreadRecoveryMediaFixture extends \App\Models\CanvasNew
{
    protected $table = 'canvas_new';

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('second')->singleFile();
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('small')->width(10)->nonQueued();
    }
}

class BreadRecoveryCollectionFixture extends BreadRecoveryMediaFixture
{
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('tab_examples')->onlyKeepLatest(2);
    }
}

class BreadImageRecoveryFixture extends \App\Models\Page
{
    protected $table = 'pages';

    protected static function booted(): void
    {
        static::saved(function ($page): void {
            if ($page->title === 'Reject upload') {
                throw \Illuminate\Validation\ValidationException::withMessages(['data.title' => 'Fixture validation after SQL write']);
            }
        });
    }
}

class BreadScopedPageFixture extends \App\Models\Page
{
    protected $table = 'pages';

    public function scopeVisibleForBread($query)
    {
        return $query->where('pages.meta_description', 'Visible');
    }
}

class BreadChildFixture extends \Illuminate\Database\Eloquent\Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    protected $table = 'page_notes';
    public $timestamps = false;
}

class BreadTagFixture extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'categories';
    public $timestamps = false;
}

class BreadTreeNodeFixture extends \Illuminate\Database\Eloquent\Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    protected $table = 'tree_nodes';
    public $timestamps = false;
}

class BreadSlugPageFixture extends \App\Models\Page
{
    protected $table = 'pages';
    protected $translatable = ['title', 'meta_description'];
}

class BreadTranslatedCheckboxFixture extends \App\Models\Page
{
    protected $table = 'pages';
    protected $translatable = ['meta_description', 'flags'];
}

class BreadCastCheckboxFixture extends \App\Models\Page
{
    protected $table = 'pages';
    protected $casts = ['flags' => 'array'];
}

class BreadTranslatedSlugFixture extends BreadSlugPageFixture
{
    protected $translatable = ['title', 'slug', 'meta_description'];
}

class BreadTreePageFixture extends \App\Models\Page
{
    protected $table = 'pages';
    public function authorId(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BreadTreeNodeFixture::class, 'author_id');
    }
}

class BreadTreeListFixture extends BreadTreePageFixture
{
    public function authorIdList()
    {
        return BreadTreeNodeFixture::query()->whereIn('id', [2, 3])->get();
    }
}

class BreadLayoutSourceFixture extends \Illuminate\Database\Eloquent\Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    protected $table = 'layout_sources';
    public $timestamps = false;
}

class BreadCodeCastFixture extends \App\Models\Page
{
    protected $table = 'pages';
    protected $casts = ['flags' => 'array'];
}

class BreadSelectNodeFixture extends \Illuminate\Database\Eloquent\Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    protected $table = 'select_nodes';
    public $timestamps = false;
}
class BreadSelectPageFixture extends \App\Models\Page
{
    protected $table = 'pages';
    public function flags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(BreadSelectNodeFixture::class, 'select_links', 'page_id', 'node_id');
    }
    public function flagsList(): \Illuminate\Support\Collection
    {
        return BreadSelectNodeFixture::withTrashed()->where('active', 1)->orderByDesc('id')->get();
    }
}

class BreadSelectChildrenFixture extends \App\Models\Page
{
    protected $table = 'pages';
    protected $casts = ['flags' => 'array'];
    public function flags(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BreadSelectNodeFixture::class, 'page_id');
    }
}
