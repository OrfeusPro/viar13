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

    public function test_bread_creation_prepares_schema_fields_menu_and_permissions_without_granting_access(): void
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
        $this->assertSame('TCG\\Voyager\\Http\\Controllers\\VoyagerBaseController', $type->controller);
        $this->assertSame(\App\Filament\Bread\BreadPolicyOptions::BASE, $type->policy_name);
        $this->assertSame(\App\Models\Stock::class, $type->model_name);
        $this->assertDatabaseCount('stocks', 1);
        $this->assertDatabaseHas('stocks', ['id' => $id, 'title' => 'Unchanged']);
        $this->assertSame($grants, DB::table('permission_role')->count());
        foreach (['browse', 'read', 'add', 'edit', 'delete'] as $action) {
            $this->assertSame(1, DB::table('permissions')->where('key', $action.'_stocks')->count());
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
        $this->assertSame($grants, DB::table('permission_role')->count());
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
        $this->assertSame($grants, DB::table('permission_role')->count());
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
