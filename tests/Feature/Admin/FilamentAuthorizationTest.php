<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\ImageAltSuggestions\ImageAltSuggestionResource;
use App\Filament\Resources\ImageAltSuggestions\Pages\ListImageAltSuggestions;
use App\Filament\Resources\CanvasSliders\CanvasSliderResource;
use App\Filament\Resources\CanvasSliders\Pages\ListCanvasSliders;
use App\Models\CanvasSlider;
use App\Models\ImageAltSuggestion;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('key');
            $table->string('table_name')->nullable();
            $table->timestamps();
        });
        Schema::create('permission_role', function (Blueprint $table): void {
            $table->foreignId('permission_id');
            $table->foreignId('role_id');
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('role_id')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('last_ip')->nullable();
            $table->text('registration_page')->nullable();
            $table->text('referrer_url')->nullable();
            $table->json('utm_parameters')->nullable();
            $table->text('user_agent')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('user_roles', function (Blueprint $table): void {
            $table->foreignId('user_id');
            $table->foreignId('role_id');
        });
    }

    public function test_primary_voyager_role_with_browse_admin_can_access_panel(): void
    {
        [$role, $permission] = $this->adminRoleAndPermission();
        $role->permissions()->attach($permission);
        $user = $this->user(['role_id' => $role->id]);

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
        $this->actingAs($user, 'filament')->get('/filament')->assertOk();
    }

    public function test_additional_voyager_role_can_grant_panel_access(): void
    {
        [$role, $permission] = $this->adminRoleAndPermission();
        $role->permissions()->attach($permission);
        $user = $this->user();
        $user->roles()->attach($role);

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_user_without_browse_admin_cannot_access_panel(): void
    {
        $role = Role::create(['name' => 'user', 'display_name' => 'User']);
        $user = $this->user(['role_id' => $role->id]);

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
        $this->actingAs($user, 'filament')->get('/filament')->assertForbidden();
    }

    public function test_alt_suggestions_require_their_own_browse_permission(): void
    {
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach($panelPermission);
        $user = $this->user(['role_id' => $role->id]);

        $this->actingAs($user, 'filament');
        $this->assertFalse(ImageAltSuggestionResource::canViewAny());

        $role->permissions()->attach(Permission::create(['key' => 'browse_alt_suggestions']));
        $user->unsetRelation('role');
        $this->assertTrue(ImageAltSuggestionResource::canViewAny());
    }

    public function test_alt_suggestions_page_renders_for_authorized_admin(): void
    {
        Schema::create('image_alt_suggestions', function (Blueprint $table): void {
            $table->id();
            $table->string('image_path');
            $table->string('status');
            $table->string('locale')->nullable();
            $table->timestamps();
        });
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([$panelPermission->id, Permission::create(['key' => 'browse_alt_suggestions'])->id]);
        $user = $this->user(['role_id' => $role->id]);

        $this->actingAs($user, 'filament')
            ->get('/filament/image-alt-suggestions')
            ->assertOk()
            ->assertSee('ALT');
    }

    public function test_canvas_slider_subtitle_page_requires_legacy_permission(): void
    {
        Schema::create('canvas_slider', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->timestamps();
        });
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach($panelPermission);
        $user = $this->user(['role_id' => $role->id]);
        $this->actingAs($user, 'filament');

        $this->assertFalse(CanvasSliderResource::canViewAny());
        $role->permissions()->attach(Permission::create(['key' => 'browse_canvas_slider']));
        $user->unsetRelation('role');

        $this->assertTrue(CanvasSliderResource::canViewAny());
        $this->get('/filament/canvas-sliders')->assertOk();
    }

    public function test_canvas_slider_subtitle_action_saves_default_and_translation(): void
    {
        Schema::create('canvas_slider', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->timestamps();
        });
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name');
            $table->string('column_name');
            $table->unsignedBigInteger('foreign_key');
            $table->string('locale');
            $table->text('value')->nullable();
            $table->timestamps();
        });
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([
            $panelPermission->id,
            Permission::create(['key' => 'browse_canvas_slider'])->id,
            Permission::create(['key' => 'edit_canvas_slider'])->id,
        ]);
        $user = $this->user(['role_id' => $role->id]);
        $slide = CanvasSlider::query()->forceCreate(['title' => 'Canvas']);
        $this->actingAs($user, 'filament');

        Livewire::test(ListCanvasSliders::class)
            ->call('mountTableAction', 'editHeroSubtitle', (string) $slide->id)
            ->fillForm(['en' => 'English subtitle', 'ru' => 'Русский подзаголовок'])
            ->callMountedAction()
            ->assertHasNoErrors();

        $this->assertSame('English subtitle', $slide->fresh()->hero_subtitle);
        $this->assertDatabaseHas('translations', [
            'table_name' => 'canvas_slider',
            'column_name' => 'hero_subtitle',
            'foreign_key' => $slide->id,
            'locale' => 'ru',
            'value' => 'Русский подзаголовок',
        ]);
    }

    public function test_alt_moderation_action_approves_edited_text(): void
    {
        require_once database_path('migrations/2026_07_01_150000_create_image_alt_apply_log_table.php');
        (new \CreateImageAltApplyLogTable)->up();
        Schema::create('image_alt_suggestions', function (Blueprint $table): void {
            $table->id();
            $table->string('imageable_type')->nullable();
            $table->unsignedBigInteger('imageable_id')->nullable();
            $table->string('page_url')->nullable();
            $table->text('error')->nullable();
            $table->text('current_alt')->nullable();
            $table->text('current_title')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->string('image_path');
            $table->string('status');
            $table->string('locale')->nullable();
            $table->text('suggested_alt')->nullable();
            $table->text('suggested_title')->nullable();
            $table->text('approved_alt')->nullable();
            $table->text('approved_title')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([
            $panelPermission->id,
            Permission::create(['key' => 'browse_alt_suggestions'])->id,
            Permission::create(['key' => 'edit_alt_suggestions'])->id,
        ]);
        $user = $this->user(['role_id' => $role->id]);
        $suggestion = ImageAltSuggestion::query()->create([
            'image_path' => '/images/logo.svg',
            'status' => ImageAltSuggestion::STATUS_PENDING,
            'locale' => 'ru',
            'suggested_alt' => 'Черновик',
        ]);
        $this->actingAs($user, 'filament');

        Livewire::test(ListImageAltSuggestions::class)
            ->call('mountTableAction', 'approve', (string) $suggestion->id)
            ->fillForm(['alt' => 'Проверенный ALT', 'title' => 'Заголовок'])
            ->callMountedAction()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('image_alt_suggestions', [
            'id' => $suggestion->id,
            'status' => ImageAltSuggestion::STATUS_APPROVED,
            'approved_alt' => 'Проверенный ALT',
            'approved_title' => 'Заголовок',
            'reviewed_by' => $user->id,
        ]);
        $page = Livewire::test(ListImageAltSuggestions::class);
        $page->call('mountTableAction', 'details', (string) $suggestion->id)
            ->assertHasNoErrors();
        $details = view('filament.tables.alt-details', ['record' => $suggestion])->render();
        $this->assertStringContainsString('Оригинал', $details);
        $this->assertStringContainsString('Предложенное', $details);
        $this->assertStringContainsString('Одобренное', $details);
        $this->assertStringContainsString('TITLE', $details);
        $this->assertStringContainsString('Превью после применения', $details);
        $this->assertStringContainsString('Блок/позиция', $details);
        $page = Livewire::test(ListImageAltSuggestions::class);
        $suggestion->update(['imageable_type' => User::class, 'page_url' => 'https://example.test/one', 'error' => 'Failure']);
        $other = ImageAltSuggestion::create(['image_path' => '/two.png', 'status' => 'pending', 'imageable_type' => Role::class]);
        $other->update(['current_title' => 'Original phrase']);
        Livewire::test(ListImageAltSuggestions::class)->searchTable('Original phrase')
            ->assertCanSeeTableRecords([$other])->assertCanNotSeeTableRecords([$suggestion]);
        $other->update(['status' => 'applied']);
        Livewire::test(ListImageAltSuggestions::class)->assertTableActionHidden('approve', $other);
        $other->update(['status' => 'pending']);
        $refreshing = Livewire::test(ListImageAltSuggestions::class);
        $other->update(['suggested_alt' => 'Generated result', 'suggested_title' => 'Generated title']);
        $refreshing->call('$refresh')->assertSet('altDrafts.'.$other->id.'.alt', 'Generated result');
        $refreshing->set('altDrafts.'.$other->id.'.alt', 'Keep my edit');
        $other->update(['suggested_alt' => 'Next result', 'suggested_title' => 'Next title']);
        $refreshing->call('$refresh')->assertSet('altDrafts.'.$other->id.'.alt', 'Keep my edit')
            ->assertSet('altDrafts.'.$other->id.'.title', 'Generated title');
        $inline = Livewire::test(ListImageAltSuggestions::class)
            ->set('altDrafts.'.$other->id.'.alt', 'Edited inline')
            ->set('altDrafts.'.$other->id.'.title', 'Inline title');
        $this->assertNull($other->fresh()->approved_alt);
        $inline->call('approveDraft', $other->id)->assertHasNoErrors();
        $this->assertSame('Edited inline', $other->fresh()->approved_alt);
        $this->assertSame('Inline title', $other->fresh()->approved_title);
        $inline->set('altDrafts.'.$other->id.'.alt', str_repeat('x', 126))
            ->call('approveDraft', $other->id)->assertHasErrors(['altDrafts.'.$other->id.'.alt']);
        $this->assertSame('Edited inline', $other->fresh()->approved_alt);
        $other->refresh()->update(['status' => 'pending']);
        $page->filterTable('imageable_type', User::class)->assertCanSeeTableRecords([$suggestion])->assertCanNotSeeTableRecords([$other]);
        Livewire::test(ListImageAltSuggestions::class)->filterTable('has_error', '0')->assertCanSeeTableRecords([$other])->assertCanNotSeeTableRecords([$suggestion]);
        Livewire::test(ListImageAltSuggestions::class)->filterTable('page_url', ['value' => '/one'])->assertCanSeeTableRecords([$suggestion])->assertCanNotSeeTableRecords([$other]);
        Livewire::test(ListImageAltSuggestions::class)->call('selectStatus', 'approved')
            ->assertCanSeeTableRecords([$suggestion])->assertCanNotSeeTableRecords([$other])
            ->call('selectStatus')->assertCanSeeTableRecords([$suggestion, $other]);
        $page->assertTableActionHidden('revert', $suggestion);
        \App\Models\ImageAltApplyLog::create(['suggestion_id' => $suggestion->id,
            'column_written' => 'image_alt', 'old_value' => 'Before']);
        $reverter = \Mockery::mock(\App\Services\AltGeneration\AltSuggestionReverter::class);
        $reverter->shouldReceive('revert')->once()->withArgs(fn ($record) => $record->id === $suggestion->id);
        $this->app->instance(\App\Services\AltGeneration\AltSuggestionReverter::class, $reverter);
        Livewire::test(ListImageAltSuggestions::class)
            ->assertTableActionVisible('revert', $suggestion)
            ->call('mountTableAction', 'revert', (string) $suggestion->id)
            ->callMountedAction()->assertHasNoErrors();
        $mounted = Livewire::test(ListImageAltSuggestions::class)
            ->call('mountTableAction', 'revert', (string) $suggestion->id);
        $editPermission = Permission::where('key', 'edit_alt_suggestions')->first();
        $role->permissions()->detach($editPermission->id);
        $this->actingAs($user->fresh(), 'filament');
        $mounted->callMountedAction()->assertHasNoErrors();
        $this->assertSame('approved', $suggestion->fresh()->status);
        Livewire::test(ListImageAltSuggestions::class)->assertTableActionHidden('revert', $suggestion);
        $readOnly = Livewire::test(ListImageAltSuggestions::class);
        $this->assertStringNotContainsString('<textarea', $readOnly->html());
        Livewire::test(ListImageAltSuggestions::class)->call('approveDraft', $other->id)->assertForbidden();
    }

    public function test_alt_bulk_actions_use_original_values_and_apply_contract(): void
    {
        require_once database_path('migrations/2026_07_01_120000_create_image_alt_suggestions_table.php');
        (new \CreateImageAltSuggestionsTable)->up();
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([
            $panelPermission->id,
            Permission::create(['key' => 'browse_alt_suggestions'])->id,
            Permission::create(['key' => 'edit_alt_suggestions'])->id,
        ]);
        $user = $this->user(['role_id' => $role->id]);
        $this->actingAs($user, 'filament');
        $first = ImageAltSuggestion::create(['image_path' => '/one.png', 'status' => 'pending',
            'suggested_alt' => 'Suggested', 'suggested_title' => 'Title', 'approved_alt' => 'Previous']);
        $second = ImageAltSuggestion::create(['image_path' => '/two.png', 'status' => 'failed',
            'suggested_alt' => 'Second']);
        $records = ImageAltSuggestion::query()->get();
        $this->assertSame(2, ImageAltSuggestionResource::performBulk($records, 'approve'));
        $this->assertSame('Suggested', $first->fresh()->approved_alt);
        $this->assertSame('Title', $first->fresh()->approved_title);
        $this->assertSame($user->id, $second->fresh()->reviewed_by);
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->once()->with('alt:apply', [
            '--ids' => $first->id . ',' . $second->id, '--status' => 'approved',
            '--source' => 'admin', '--applied-by' => $user->id, '--limit' => 2,
        ])->andReturnUsing(function () use ($first): int {
            $first->update(['status' => 'applied']);
            return 0;
        });
        $this->assertSame(1, ImageAltSuggestionResource::performBulk($records, 'apply'));
        $this->assertSame(1, ImageAltSuggestionResource::performBulk($records, 'reject'));
        $this->assertSame('applied', $first->fresh()->status);
        $this->assertSame('rejected', $second->fresh()->status);
        \Illuminate\Support\Facades\Bus::fake();
        $this->assertSame(0, ImageAltSuggestionResource::performBulk($records, 'regenerate'));
        \Illuminate\Support\Facades\Bus::assertNothingDispatched();
        $second->update(['imageable_type' => User::class, 'imageable_id' => $user->id, 'error' => 'Old error']);
        $this->assertSame(1, ImageAltSuggestionResource::performBulk($records, 'regenerate'));
        $this->assertSame('new', $second->fresh()->status);
        $this->assertNull($second->fresh()->error);
        \Illuminate\Support\Facades\Bus::assertDispatched(\App\Jobs\GenerateImageAltJob::class, 1);
        $role->permissions()->detach();
        $this->actingAs($user->fresh(), 'filament');
        try {
            ImageAltSuggestionResource::performBulk($records, 'approve');
            $this->fail('Bulk must check permission again at execution.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertSame('new', $second->fresh()->status);
    }

    private function adminRoleAndPermission(): array
    {
        return [
            Role::create(['name' => 'admin', 'display_name' => 'Admin']),
            Permission::create(['key' => 'browse_admin']),
        ];
    }

    public function test_standalone_media_library_browses_all_file_types_without_bread_record(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $disk->put('photos/document.txt', 'Example');
        $disk->put('photos/.hidden', 'Hidden');
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([$panelPermission->id, Permission::create(['key' => 'browse_media'])->id]);
        $user = $this->user(['role_id' => $role->id]);
        $this->actingAs($user, 'filament');
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->assertSee('photos')
            ->call('browse', 'photos')->assertSee('document.txt')->assertDontSee('.hidden')
            ->assertSet('folder', 'photos')->call('browse', '')->assertSet('folder', '');
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', '../')->assertStatus(422);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)
            ->set('newFolder', '../outside')->call('createFolder')->assertHasErrors(['newFolder'])
            ->set('newFolder', 'NUL')->call('createFolder')->assertHasErrors(['newFolder'])
            ->set('newFolder', 'Новая папка')->call('createFolder')->assertHasNoErrors()
            ->assertSee('Новая папка')->assertSet('newFolder', '')
            ->set('newFolder', 'Новая папка')->call('createFolder')->assertHasErrors(['newFolder']);
        $this->assertTrue($disk->directoryExists('Новая папка'));
        $originalUpload = \Illuminate\Http\UploadedFile::fake()->createWithContent('document.txt', 'New upload');
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos')
            ->set('uploads', [$originalUpload])->call('uploadFiles')->assertHasNoErrors()->assertSet('uploads', []);
        $this->assertSame('Example', $disk->get('photos/document.txt'));
        $this->assertSame('New upload', $disk->get('photos/document_1.txt'));
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)
            ->set('uploads', [\Illuminate\Http\UploadedFile::fake()->createWithContent('script.php', '<?php echo 1;')])
            ->call('uploadFiles')->assertHasErrors(['uploads.0']);
        $this->assertFalse($disk->exists('script.php'));
        config(['voyager.media.allowed_mimetypes' => ['image/png']]);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)
            ->set('uploads', [\Illuminate\Http\UploadedFile::fake()->createWithContent('denied.txt', 'Text')])
            ->call('uploadFiles')->assertHasErrors(['uploads.0']);
        $this->assertFalse($disk->exists('denied.txt'));
        config(['voyager.media.allowed_mimetypes' => '*']);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)
            ->set('uploads', [\Illuminate\Http\UploadedFile::fake()->create('large.pdf', 10241, 'application/pdf')])
            ->call('uploadFiles')->assertHasErrors(['uploads.0']);
        $this->assertFalse($disk->exists('large.pdf'));
        $selection = Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos');
        $selection->assertDontSee('Удалить выбранные')->call('selectPageFiles')
            ->assertSee('Удалить выбранные')->assertSet('selectedFiles', ['photos/document.txt', 'photos/document_1.txt'])
            ->call('clearFileSelection')->assertSet('selectedFiles', []);
        $disk->put('photos/disposable.txt', 'Delete fixture');
        $selection->set('selectedFiles', ['photos/disposable.txt', '../outside.txt'])
            ->call('deleteSelectedFiles')->assertStatus(422);
        $this->assertTrue($disk->exists('photos/disposable.txt'));
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos')
            ->set('selectedFiles', ['photos/disposable.txt'])->call('deleteSelectedFiles')->assertHasNoErrors()
            ->assertSet('selectedFiles', []);
        $this->assertFalse($disk->exists('photos/disposable.txt'));
        config(['voyager.media.allow_delete' => false]);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->set('selectedFiles', ['photos/document.txt'])
            ->call('deleteSelectedFiles')->assertForbidden();
        config(['voyager.media.allow_delete' => true]);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)
            ->call('deleteFolder', 'photos')->assertHasErrors(['folderName'])
            ->call('deleteFolder', 'Новая папка')->assertHasNoErrors();
        $this->assertFalse($disk->directoryExists('Новая папка'));
        $this->assertTrue($disk->exists('photos/document.txt'));
        \Illuminate\Support\Facades\Schema::create('data_types', function (Blueprint $table): void {
            $table->id(); $table->string('name'); $table->string('model_name')->nullable();
        });
        \Illuminate\Support\Facades\Schema::create('data_rows', function (Blueprint $table): void {
            $table->id(); $table->foreignId('data_type_id'); $table->string('field'); $table->string('type');
        });
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)
            ->call('editFolder', 'photos')->set('folderName', 'renamed')->call('renameDirectory')->assertHasNoErrors();
        $this->assertSame('Example', $disk->get('renamed/document.txt'));
        $this->assertSame('Example', $disk->get('photos/document.txt'));
        $disk->makeDirectory('destination');
        $disk->put('renamed/second.txt', 'Second');
        $disk->put('destination/second.txt', 'Conflict');
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'renamed')
            ->set('selectedFiles', ['renamed/document.txt', 'renamed/second.txt'])->set('bulkFolder', 'destination')
            ->call('moveSelectedFiles')->assertHasErrors(['bulkFolder']);
        $this->assertFalse($disk->exists('destination/document.txt'));
        $this->assertSame('Conflict', $disk->get('destination/second.txt'));
        $disk->delete('destination/second.txt');
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'renamed')
            ->set('selectedFiles', ['renamed/document.txt', 'renamed/second.txt'])->set('bulkFolder', 'destination')
            ->call('moveSelectedFiles')->assertHasNoErrors()->assertSet('selectedFiles', []);
        $this->assertSame('Example', $disk->get('destination/document.txt'));
        $this->assertSame('Second', $disk->get('destination/second.txt'));
        $this->assertSame('Second', $disk->get('renamed/second.txt'));
        $disk->makeDirectory('rollback-target');
        $referencesMock = \Mockery::mock(\App\Filament\Bread\BreadLibraryReferences::class);
        $referencesMock->shouldReceive('replace')->once()->with('renamed/document.txt', 'rollback-target/document.txt', \Mockery::any())->andReturn(1)->ordered();
        $referencesMock->shouldReceive('replace')->once()->with('renamed/second.txt', 'rollback-target/second.txt', \Mockery::any())
            ->andThrow(new \RuntimeException('Synthetic reference failure'))->ordered();
        $this->app->instance(\App\Filament\Bread\BreadLibraryReferences::class, $referencesMock);
        try {
            app(\App\Filament\Bread\BreadLibraryFiles::class)->relocateMany(['renamed/document.txt', 'renamed/second.txt'], 'rollback-target', app(\App\Filament\Bread\BreadRegistry::class));
            $this->fail('Batch must fail on the second reference update.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Synthetic reference failure', $exception->getMessage());
            $this->assertSame([], $disk->files('rollback-target'));
            $this->assertTrue($disk->exists('renamed/document.txt'));
            $this->assertTrue($disk->exists('renamed/second.txt'));
        }
        $this->app->forgetInstance(\App\Filament\Bread\BreadLibraryReferences::class);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)
            ->call('editFile', 'renamed/document.txt')->set('fileName', 'report.txt')->set('targetFolder', '')
            ->call('relocateFile')->assertHasNoErrors()->assertSet('sourceFile', null);
        $this->assertSame('Example', $disk->get('report.txt'));
        $this->assertSame('Example', $disk->get('renamed/document.txt'));
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)
            ->call('editFile', 'report.txt')->set('fileName', 'report.php')->call('relocateFile')->assertHasErrors(['fileName'])
            ->set('fileName', 'document.txt')->set('targetFolder', 'photos')->call('relocateFile')->assertHasErrors(['fileName']);
        $role->permissions()->detach();
        $this->actingAs($user->fresh(), 'filament');
        $this->assertFalse(\App\Filament\Pages\MediaLibrary::canAccess());
        $this->assertSame('Example', $disk->get('photos/document.txt'));
    }

    public function test_media_upload_rejects_a_folder_changed_during_transfer_before_writing(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $disk->makeDirectory('photos');
        $disk->makeDirectory('other');
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([$panelPermission->id, Permission::create(['key' => 'browse_media'])->id]);
        $this->actingAs($this->user(['role_id' => $role->id]), 'filament');
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'other')
            ->set('uploads', [\Illuminate\Http\UploadedFile::fake()->createWithContent('a.txt', 'Fixture')])
            ->call('uploadFiles', 'photos')->assertStatus(409);
        $this->assertSame([], $disk->allFiles('photos'));
        $this->assertSame([], $disk->allFiles('other'));
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos')
            ->set('uploads', [\Illuminate\Http\UploadedFile::fake()->createWithContent('a.txt', 'Fixture')])
            ->call('uploadFiles', 'photos')->assertHasNoErrors()->assertReturned(true);
        $this->assertSame('Fixture', $disk->get('photos/a.txt'));
    }

    public function test_media_upload_orients_jpeg_and_preserves_gif_document_bytes(): void
    {
        $image = imagecreatetruecolor(40, 20);
        ob_start(); imagejpeg($image, null, 100); $jpeg = ob_get_clean(); imagedestroy($image);
        $exif = "Exif\0\0II".pack('vVv', 42, 8, 1).pack('vvV', 0x0112, 3, 1).pack('v', 6)."\0\0".pack('V', 0);
        $fixture = substr($jpeg, 0, 2)."\xff\xe1".pack('n', strlen($exif)+2).$exif.substr($jpeg, 2);
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('original.jpeg', $fixture);
        $processed = app(\App\Filament\Bread\BreadImageUpload::class)->mediaBytes($file, 'uploads');
        $info = getimagesizefromstring($processed);
        $this->assertSame([20, 40], [$info[0], $info[1]]);
        $this->assertSame('image/jpeg', $info['mime']);
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        $this->assertSame($gif, app(\App\Filament\Bread\BreadImageUpload::class)->mediaBytes(
            \Illuminate\Http\UploadedFile::fake()->createWithContent('animation.gif', $gif), 'uploads'));
        $this->assertSame('Document', app(\App\Filament\Bread\BreadImageUpload::class)->mediaBytes(
            \Illuminate\Http\UploadedFile::fake()->createWithContent('document.txt', 'Document'), 'uploads'));
    }

    public function test_media_configuration_limits_root_and_disabled_actions(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $disk->put('allowed/sub/file.txt', 'Inside');
        $disk->put('outside/file.txt', 'Outside');
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([$panelPermission->id, Permission::create(['key' => 'browse_media'])->id]);
        $this->actingAs($this->user(['role_id' => $role->id]), 'filament');
        config(['voyager.media.path' => '/allowed/']);
        $scopedMedia = Livewire::test(\App\Filament\Pages\MediaLibrary::class)->assertSet('folder', 'allowed')->assertSet('bulkFolder', 'allowed')
            ->assertSee('sub')->call('browse', 'allowed/sub')->assertSee('file.txt');
        $this->assertSame([['label' => 'Медиатека', 'path' => 'allowed'], ['label' => 'sub', 'path' => 'allowed/sub']], $scopedMedia->instance()->folderTrail());
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'outside')->assertStatus(422);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('editFile', 'outside/file.txt')->assertStatus(422);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('deleteFolder', 'allowed')->assertStatus(422);
        config(['voyager.media.show_folders' => false, 'voyager.media.allow_create_folder' => false,
            'voyager.media.allow_rename' => false, 'voyager.media.allow_move' => false]);
        $hiddenFolders = Livewire::test(\App\Filament\Pages\MediaLibrary::class)->assertDontSee('Создать папку');
        $this->assertSame([], $hiddenFolders->instance()->listing()['folders']);
        $hiddenFolders->call('browse', 'allowed/sub')->assertForbidden();
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->set('newFolder', 'blocked')->call('createFolder')->assertForbidden();
        $this->assertFalse($disk->exists('allowed/blocked'));
        config(['voyager.media.show_folders' => true]);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('editFolder', 'allowed/sub')->assertForbidden();
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('editFile', 'allowed/sub/file.txt')->assertForbidden();
        $this->assertSame('Inside', $disk->get('allowed/sub/file.txt'));
        $this->assertSame('Outside', $disk->get('outside/file.txt'));
    }

    public function test_standalone_media_crop_copy_and_overwrite_match_original_modes(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $image = imagecreatetruecolor(40, 30);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 0, 0));
        imagefilledrectangle($image, 20, 0, 39, 29, imagecolorallocate($image, 0, 0, 255));
        ob_start(); imagepng($image); $bytes = ob_get_clean(); imagedestroy($image);
        $disk->put('photos/original.png', $bytes);
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([$panelPermission->id, Permission::create(['key' => 'browse_media'])->id]);
        $this->actingAs($this->user(['role_id' => $role->id]), 'filament');
        $component = Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos')
            ->call('openCrop', 'photos/original.png')->assertSet('libraryCrop.width', 32)->assertSet('libraryCrop.x', 4)
            ->set('libraryCrop', ['x' => 20, 'y' => 2, 'width' => 10, 'height' => 12])
            ->call('cropImage', true)->assertHasNoErrors()->assertSet('cropFile', null);
        $copies = array_values(array_diff($disk->files('photos'), ['photos/original.png']));
        $this->assertCount(1, $copies);
        $this->assertMatchesRegularExpression('/original\.cropped_\d+\.png$/', $copies[0]);
        $this->assertSame($bytes, $disk->get('photos/original.png'));
        $cropped = imagecreatefromstring($disk->get($copies[0]));
        $this->assertSame([10, 12, 0x0000ff], [imagesx($cropped), imagesy($cropped), imagecolorat($cropped, 0, 0)]);
        imagedestroy($cropped);
        $component->call('openCrop', 'photos/original.png')
            ->set('libraryCrop', ['x' => 1, 'y' => 1, 'width' => 8, 'height' => 9])
            ->call('cropImage', false)->assertHasNoErrors();
        $overwritten = imagecreatefromstring($disk->get('photos/original.png'));
        $this->assertSame([8, 9, 0xff0000], [imagesx($overwritten), imagesy($overwritten), imagecolorat($overwritten, 0, 0)]);
        imagedestroy($overwritten);
        $this->assertCount(2, $disk->files('photos'));
        $gifImage = imagecreatetruecolor(40, 30);
        ob_start(); imagegif($gifImage); $gif = ob_get_clean(); imagedestroy($gifImage);
        $disk->put('photos/animation.gif', $gif);
        $component->call('openCrop', 'photos/animation.gif')->assertSee('без анимации')
            ->set('libraryCrop', ['x' => 1, 'y' => 2, 'width' => 8, 'height' => 9])
            ->call('cropImage', true)->assertHasNoErrors();
        $gifCopies = array_values(array_filter($disk->files('photos'), fn ($path) => str_contains($path, 'animation.cropped_')));
        $this->assertCount(1, $gifCopies);
        $gifInfo = getimagesizefromstring($disk->get($gifCopies[0]));
        $this->assertSame([8, 9, 'image/gif'], [$gifInfo[0], $gifInfo[1], $gifInfo['mime']]);
        $this->assertSame($gif, $disk->get('photos/animation.gif'));
    }

    public function test_standalone_media_crop_outside_source_matches_voyager_padding(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $image = imagecreatetruecolor(40, 30);
        imagefill($image, 0, 0, 0xff0000);
        ob_start(); imagepng($image); $bytes = ob_get_clean(); imagedestroy($image);
        $disk->put('photos/padding.png', $bytes);
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([$panelPermission->id, Permission::create(['key' => 'browse_media'])->id]);
        $this->actingAs($this->user(['role_id' => $role->id]), 'filament');
        $component = Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos');
        foreach ([[35, 25], [45, 35], [-1, 0]] as [$x, $y]) {
            $before = $disk->files('photos');
            $component->call('openCrop', 'photos/padding.png')
                ->set('libraryCrop', ['x' => $x, 'y' => $y, 'width' => 10, 'height' => 10])
                ->call('cropImage', true)->assertHasNoErrors()->assertSet('cropFile', null);
            $created = array_values(array_diff($disk->files('photos'), $before));
            $this->assertCount(1, $created);
            $result = imagecreatefromstring($disk->get($created[0]));
            $this->assertSame([10, 10], [imagesx($result), imagesy($result)]);
            for ($dy = 0; $dy < 10; $dy++) {
                for ($dx = 0; $dx < 10; $dx++) {
                    $inside = $x + $dx >= 0 && $x + $dx < 40 && $y + $dy >= 0 && $y + $dy < 30;
                    $this->assertSame($inside ? 0xff0000 : 0, imagecolorat($result, $dx, $dy));
                }
            }
            imagedestroy($result);
            $this->assertSame($bytes, $disk->get('photos/padding.png'));
        }
        try {
            app(\App\Filament\Bread\BreadImageCrop::class)->crop($bytes, 35, 25, 10, 10);
            $this->fail('BREAD must retain its existing bounds validation.');
        } catch (\Illuminate\Validation\ValidationException $error) {
            $this->assertArrayHasKey('libraryCrop', $error->errors());
        }
    }

    public function test_standalone_media_crop_rejects_invalid_region_path_and_disabled_action(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $file = \Illuminate\Http\UploadedFile::fake()->image('original.png', 40, 30);
        $bytes = file_get_contents($file->getRealPath());
        $disk->put('photos/original.png', $bytes);
        $disk->put('outside/original.png', $bytes);
        $disk->put('photos/document.txt', 'Document');
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([$panelPermission->id, Permission::create(['key' => 'browse_media'])->id]);
        $this->actingAs($this->user(['role_id' => $role->id]), 'filament');
        config(['voyager.media.path' => 'photos']);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('openCrop', 'outside/original.png')->assertStatus(422);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('openCrop', 'photos/document.txt')->assertHasErrors('libraryCrop');
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('openCrop', 'photos/original.png')
            ->set('libraryCrop.width', 20000001)->call('cropImage', false)->assertHasErrors('libraryCrop')
            ->set('libraryCrop.width', 0)->call('cropImage', true)->assertHasErrors('libraryCrop.width');
        config(['voyager.media.allow_crop' => false]);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->assertDontSee('Обрезать')->call('openCrop', 'photos/original.png')->assertForbidden();
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('cropImage', false)->assertForbidden();
        $this->assertSame($bytes, $disk->get('photos/original.png'));
        $this->assertCount(2, $disk->files('photos'));
    }

    public function test_media_upload_reserves_thumbnail_paths_and_rolls_back_failed_batch(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $disk->put('photos/original-small.png', 'Existing thumbnail');
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([$panelPermission->id, Permission::create(['key' => 'browse_media'])->id]);
        $this->actingAs($this->user(['role_id' => $role->id]), 'filament');
        config(['voyager.media.thumbnails' => [['name' => 'small', 'type' => 'resize', 'width' => 20]]]);
        $thumbnailUpload = Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos')
            ->set('uploads', [\Illuminate\Http\UploadedFile::fake()->image('original.png', 60, 30)])
            ->call('uploadFiles')->assertHasNoErrors()->assertSet('uploads', []);
        $this->assertFalse($disk->exists('photos/original.png'));
        $this->assertTrue($disk->exists('photos/original_1.png'));
        $this->assertTrue($disk->exists('photos/original_1-small.png'));
        $this->assertSame('Existing thumbnail', $disk->get('photos/original-small.png'));
        $info = getimagesizefromstring($disk->get('photos/original_1-small.png'));
        $this->assertSame([20, 10], [$info[0], $info[1]]);
        $listed = $thumbnailUpload->instance()->listing()['files'];
        $this->assertCount(1, $listed);
        $this->assertSame('original_1.png', $listed[0]['name']);
        $this->assertSame('original_1-small.png', $listed[0]['thumbnails'][0]['name']);
        $before = $disk->allFiles();
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos')
            ->set('uploads', [\Illuminate\Http\UploadedFile::fake()->image('new.png', 60, 30),
                \Illuminate\Http\UploadedFile::fake()->createWithContent('unsupported.svg', '<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"></svg>')])
            ->call('uploadFiles')->assertHasErrors('uploads');
        $this->assertSame($before, $disk->allFiles());
        $this->assertSame('Existing thumbnail', $disk->get('photos/original-small.png'));
    }

    public function test_media_hidden_files_setting_matches_voyager_filename_rule(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $disk->put('photos/.hidden', 'Hidden');
        $disk->put('photos/.named.txt', 'Named');
        $disk->put('photos/normal.txt', 'Normal');
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([$panelPermission->id, Permission::create(['key' => 'browse_media'])->id]);
        $this->actingAs($this->user(['role_id' => $role->id]), 'filament');
        config(['voyager.hidden_files' => false]);
        $component = Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos');
        $names = array_column($component->instance()->listing()['files'], 'name');
        $this->assertSame(['.named.txt', 'normal.txt'], $names);
        config(['voyager.hidden_files' => true]);
        $component->call('refreshFiles');
        $this->assertSame(['.hidden', '.named.txt', 'normal.txt'], array_column($component->instance()->listing()['files'], 'name'));
        $this->assertSame('Hidden', $disk->get('photos/.hidden'));
        $this->assertCount(3, $disk->allFiles('photos'));
    }

    public function test_media_details_rename_uses_shared_references_and_retains_original(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $disk->put('photos/original.txt', 'Original');
        $disk->put('photos/occupied.txt', 'Occupied');
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([$panelPermission->id, Permission::create(['key' => 'browse_media'])->id]);
        $this->actingAs($this->user(['role_id' => $role->id]), 'filament');
        config(['voyager.media.allow_move' => false]);
        $references = \Mockery::mock(\App\Filament\Bread\BreadLibraryReferences::class);
        $references->shouldReceive('replace')->once()->with('photos/original.txt', 'photos/renamed.txt',
            \Mockery::type(\App\Filament\Bread\BreadRegistry::class))->andReturn(2);
        $this->app->instance(\App\Filament\Bread\BreadLibraryReferences::class, $references);
        $component = Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos')
            ->call('selectCard', 'photos/original.txt')->assertSee('Enter — переименовать.')
            ->call('renameInspectedFile', 'original.txt')->assertHasNoErrors()->assertSet('infoFile', 'photos/original.txt')
            ->call('renameInspectedFile', 'occupied.txt')->assertHasErrors('fileName');
        $this->assertSame('Occupied', $disk->get('photos/occupied.txt'));
        $component->call('renameInspectedFile', '../outside.txt')->assertHasErrors('fileName')
            ->call('renameInspectedFile', 'renamed.txt')->assertHasNoErrors()
            ->assertSet('infoFile', null)->assertSet('selectedFiles', []);
        $this->assertSame('Original', $disk->get('photos/original.txt'));
        $this->assertSame('Original', $disk->get('photos/renamed.txt'));
        $this->assertFalse($disk->exists('outside.txt'));
        config(['voyager.media.allow_rename' => false]);
        $component->call('selectCard', 'photos/original.txt')->assertDontSee('Enter — переименовать.')
            ->call('renameInspectedFile', 'blocked.txt')->assertForbidden();
        $this->assertFalse($disk->exists('photos/blocked.txt'));
    }

    public function test_media_image_preview_opens_current_image_without_crop_or_storage_writes(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $fixture = \Illuminate\Http\UploadedFile::fake()->image('image.png', 40, 30);
        $bytes = file_get_contents($fixture->getRealPath());
        $disk->put('photos/image with spaces.png', $bytes);
        $disk->put('photos/second.png', $bytes);
        $disk->put('photos/document.txt', 'Document');
        $disk->put('outside/image.png', $bytes);
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $permission = Permission::create(['key' => 'browse_media']);
        $role->permissions()->attach([$panelPermission->id, $permission->id]);
        $this->actingAs($this->user(['role_id' => $role->id]), 'filament');
        config(['voyager.media.allow_crop' => false]);
        $component = Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos')
            ->call('openImage', 'photos/image with spaces.png')->assertSet('previewFile', 'photos/image with spaces.png')
            ->assertSet('selectedFiles', ['photos/image with spaces.png'])
            ->assertDispatched('open-modal', id: 'media-image-preview')->assertSee('image%20with%20spaces.png', false)
            ->set('previewFile', null)->call('openImage', 'photos/second.png')->assertSet('previewFile', 'photos/second.png')
            ->call('refreshFiles')->assertSet('previewFile', null);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos')->call('openImage', 'photos/document.txt')->assertStatus(422);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos')->call('openImage', 'outside/image.png')->assertStatus(422);
        $this->assertSame($bytes, $disk->get('photos/image with spaces.png'));
        $this->assertCount(3, $disk->files('photos'));
        $role->permissions()->detach($permission->id);
        $component->call('openImage', 'photos/second.png')->assertForbidden();
    }

    public function test_media_card_selection_matches_voyager_click_modifiers_and_information(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        foreach (['a', 'b', 'c', 'd'] as $name) { $disk->put('photos/'.$name.'.txt', $name); }
        $disk->makeDirectory('photos/folder');
        $disk->put('outside/private.txt', 'Private');
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $permission = Permission::create(['key' => 'browse_media']);
        $role->permissions()->attach([$panelPermission->id, $permission->id]);
        $this->actingAs($this->user(['role_id' => $role->id]), 'filament');
        $component = Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos')
            ->call('selectCard', 'photos/b.txt')->assertSet('selectedFiles', ['photos/b.txt'])->assertSet('infoFile', 'photos/b.txt')
            ->call('selectCard', 'photos/c.txt', true)->assertSet('selectedFiles', ['photos/b.txt', 'photos/c.txt'])->assertSet('infoFile', null)
            ->call('selectCard', 'photos/c.txt', true)->assertSet('selectedFiles', ['photos/b.txt', 'photos/c.txt'])
            ->call('selectCard', 'photos/a.txt')->assertSet('selectedFiles', ['photos/a.txt'])
            ->call('selectCard', 'photos/c.txt', false, true)->assertSet('selectedFiles', ['photos/a.txt', 'photos/b.txt', 'photos/c.txt'])
            ->call('selectCard', 'photos/c.txt')->call('selectCard', 'photos/a.txt', false, true)
            ->assertSet('selectedFiles', ['photos/c.txt', 'photos/a.txt'])
            ->call('selectCard', 'photos/folder')->assertSet('selectedFiles', ['photos/folder'])->assertSet('infoFile', null)
            ->set('selectedFiles', ['photos/d.txt'])->assertSet('infoFile', 'photos/d.txt')
            ->call('selectPageFiles')->assertSet('infoFile', null)
            ->call('clearFileSelection')->assertSet('selectedFiles', [])->assertSet('infoFile', null);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos')
            ->call('selectCard', 'outside/private.txt')->assertStatus(422);
        $this->assertSame('a', $disk->get('photos/a.txt'));
        $role->permissions()->detach($permission->id);
        $component->call('selectCard', 'photos/a.txt')->assertForbidden();
    }

    public function test_media_information_contains_audio_and_video_players_like_voyager(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $wave = 'RIFF'.pack('V', 38).'WAVEfmt '.pack('VvvVVvv', 16, 1, 1, 8000, 16000, 2, 16)
            .'data'.pack('V', 2)."\0\0";
        $video = pack('N', 24).'ftypisom'.pack('N', 0).'isommp42';
        $disk->put('photos/sound.wav', $wave);
        $disk->put('photos/clip.mp4', $video);
        $disk->put('photos/document.txt', 'Document');
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([$panelPermission->id, Permission::create(['key' => 'browse_media'])->id]);
        $this->actingAs($this->user(['role_id' => $role->id]), 'filament');
        $component = Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos')
            ->call('inspectFile', 'photos/sound.wav')->assertSee('Предпросмотр аудио')->assertDontSee('Предпросмотр видео');
        $this->assertStringContainsString('<audio ', $component->html());
        $this->assertStringContainsString('controls preload="metadata"', $component->html());
        $this->assertStringNotContainsString('autoplay', $component->html());
        $component->call('inspectFile', 'photos/clip.mp4')->assertSee('Предпросмотр видео')->assertDontSee('Предпросмотр аудио');
        $this->assertStringContainsString('<video ', $component->html());
        $this->assertStringNotContainsString('<audio ', $component->html());
        $component->call('inspectFile', 'photos/document.txt')->assertDontSee('Предпросмотр видео')->assertDontSee('Предпросмотр аудио');
        $this->assertSame($wave, $disk->get('photos/sound.wav'));
        $this->assertSame($video, $disk->get('photos/clip.mp4'));
    }

    public function test_media_file_information_and_refresh_follow_current_folder_and_permission(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $disk->put('photos/file with spaces.txt', 'Document');
        $disk->put('outside/private.txt', 'Outside');
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([$panelPermission->id, Permission::create(['key' => 'browse_media'])->id]);
        $user = $this->user(['role_id' => $role->id]);
        $this->actingAs($user, 'filament');
        $component = Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos')
            ->call('inspectFile', 'photos/file with spaces.txt')->assertSee('Дата изменения')
            ->assertSee('text/plain')->assertSee('file%20with%20spaces.txt');
        $listed = $component->instance()->listing()['files'][0];
        $this->assertSame(8, $listed['size']);
        $this->assertSame($disk->lastModified('photos/file with spaces.txt'), $listed['modified']);
        $disk->put('photos/new.txt', 'New');
        $component->set('selectedFiles', ['photos/file with spaces.txt'])->call('refreshFiles')
            ->assertSet('infoFile', null)->assertSet('selectedFiles', [])->assertSee('new.txt');
        $component->call('inspectFile', 'photos/file with spaces.txt')->call('browse', 'outside')->assertSet('infoFile', null);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos')
            ->call('inspectFile', 'outside/private.txt')->assertStatus(422);
        $role->permissions()->detach();
        $this->actingAs($user->fresh(), 'filament');
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->assertForbidden();
        $this->assertSame('Document', $disk->get('photos/file with spaces.txt'));
    }

    public function test_media_folder_move_preserves_sources_and_updates_references_with_rollback(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $disk->put('photos/source/nested/image.txt', 'Image');
        $disk->makeDirectory('photos/source/empty');
        $disk->makeDirectory('destination');
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([$panelPermission->id, Permission::create(['key' => 'browse_media'])->id]);
        $this->actingAs($this->user(['role_id' => $role->id]), 'filament');
        $references = \Mockery::mock(\App\Filament\Bread\BreadLibraryReferences::class);
        $references->shouldReceive('replace')->once()->with('photos/source', 'destination/source', \Mockery::type(\App\Filament\Bread\BreadRegistry::class), true)->andReturn(2);
        $this->app->instance(\App\Filament\Bread\BreadLibraryReferences::class, $references);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'photos')
            ->call('openFolderMove', 'photos/source')->assertSet('folderDestination', 'photos')
            ->set('folderDestination', 'photos/source/nested')->call('moveFolder')->assertHasErrors('folderDestination')
            ->set('folderDestination', 'destination')->call('moveFolder')->assertHasNoErrors()->assertSet('movingFolder', null);
        $this->assertSame('Image', $disk->get('destination/source/nested/image.txt'));
        $this->assertSame('Image', $disk->get('photos/source/nested/image.txt'));
        $this->assertTrue($disk->directoryExists('destination/source/empty'));
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('openFolderMove', 'photos/source')
            ->set('folderDestination', 'destination')->call('moveFolder')->assertHasErrors('folderDestination');
        $disk->makeDirectory('rollback');
        $failure = \Mockery::mock(\App\Filament\Bread\BreadLibraryReferences::class);
        $failure->shouldReceive('replace')->once()->andThrow(new \RuntimeException('Synthetic reference failure'));
        $this->app->instance(\App\Filament\Bread\BreadLibraryReferences::class, $failure);
        try {
            app(\App\Filament\Bread\BreadLibraryFolders::class)->rename('photos/source', 'source', 'folderDestination', app(\App\Filament\Bread\BreadRegistry::class), 'rollback');
            $this->fail('Reference failure should abort folder move.');
        } catch (\RuntimeException $error) { $this->assertSame('Synthetic reference failure', $error->getMessage()); }
        $this->assertFalse($disk->directoryExists('rollback/source'));
        $this->assertSame('Image', $disk->get('photos/source/nested/image.txt'));
        config(['voyager.media.allow_move' => false]);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('openFolderMove', 'photos/source')->assertForbidden();
        config(['voyager.media.allow_move' => true, 'voyager.media.path' => 'photos']);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('openFolderMove', 'photos')->assertStatus(422);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('openFolderMove', 'photos/source')
            ->set('folderDestination', 'destination')->call('moveFolder')->assertStatus(422);
    }

    public function test_media_mixed_selection_moves_atomically_and_deletes_only_empty_folders(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $disk->put('mixed/album/nested.txt', 'Nested');
        $disk->put('mixed/document.txt', 'Document');
        $disk->makeDirectory('destination');
        [$role, $panelPermission] = $this->adminRoleAndPermission();
        $role->permissions()->attach([$panelPermission->id, Permission::create(['key' => 'browse_media'])->id]);
        $this->actingAs($this->user(['role_id' => $role->id]), 'filament');
        $references = \Mockery::mock(\App\Filament\Bread\BreadLibraryReferences::class);
        $references->shouldReceive('replace')->once()->with('mixed/album', 'destination/album', \Mockery::type(\App\Filament\Bread\BreadRegistry::class), true)->andReturn(1);
        $references->shouldReceive('replace')->once()->with('mixed/document.txt', 'destination/document.txt', \Mockery::type(\App\Filament\Bread\BreadRegistry::class))->andReturn(1);
        $this->app->instance(\App\Filament\Bread\BreadLibraryReferences::class, $references);
        Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'mixed')->call('selectPageFiles')
            ->assertSet('selectedFiles', ['mixed/album', 'mixed/document.txt'])->assertSee('Выбрано: 2')
            ->set('bulkFolder', 'destination')->call('moveSelectedFiles')->assertHasNoErrors()->assertSet('selectedFiles', []);
        foreach (['album/nested.txt' => 'Nested', 'document.txt' => 'Document'] as $relative => $bytes) {
            $this->assertSame($bytes, $disk->get('mixed/'.$relative));
            $this->assertSame($bytes, $disk->get('destination/'.$relative));
        }
        $disk->makeDirectory('rollback');
        $failure = \Mockery::mock(\App\Filament\Bread\BreadLibraryReferences::class);
        $failure->shouldReceive('replace')->once()->with('mixed/album', 'rollback/album', \Mockery::any(), true)->andReturn(1);
        $failure->shouldReceive('replace')->once()->with('mixed/document.txt', 'rollback/document.txt', \Mockery::any())->andThrow(new \RuntimeException('Synthetic mixed failure'));
        $this->app->instance(\App\Filament\Bread\BreadLibraryReferences::class, $failure);
        try {
            app(\App\Filament\Bread\BreadLibraryFiles::class)->relocateMany(['mixed/album', 'mixed/document.txt'], 'rollback', app(\App\Filament\Bread\BreadRegistry::class));
            $this->fail('Mixed batch should roll back.');
        } catch (\RuntimeException $error) { $this->assertSame('Synthetic mixed failure', $error->getMessage()); }
        $this->assertSame([], $disk->allFiles('rollback'));
        $this->assertFalse($disk->directoryExists('rollback/album'));
        $component = Livewire::test(\App\Filament\Pages\MediaLibrary::class)->call('browse', 'mixed')
            ->set('selectedFiles', ['mixed/document.txt', 'mixed/album'])->call('deleteSelectedFiles')->assertHasErrors('selectedFiles');
        $this->assertSame('Document', $disk->get('mixed/document.txt'));
        $this->assertSame('Nested', $disk->get('mixed/album/nested.txt'));
        $disk->makeDirectory('mixed/empty');
        $component->set('selectedFiles', ['mixed/document.txt', 'mixed/empty'])->call('deleteSelectedFiles')->assertHasNoErrors()->assertSet('selectedFiles', []);
        $this->assertFalse($disk->exists('mixed/document.txt'));
        $this->assertFalse($disk->directoryExists('mixed/empty'));
        $this->assertSame('Nested', $disk->get('mixed/album/nested.txt'));
    }

    private function user(array $attributes = []): User
    {
        return User::forceCreate(array_merge([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
        ], $attributes));
    }
}
