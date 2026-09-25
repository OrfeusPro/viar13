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
        Schema::create('image_alt_suggestions', function (Blueprint $table): void {
            $table->id();
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
    }

    private function adminRoleAndPermission(): array
    {
        return [
            Role::create(['name' => 'admin', 'display_name' => 'Admin']),
            Permission::create(['key' => 'browse_admin']),
        ];
    }

    private function user(array $attributes = []): User
    {
        return User::forceCreate(array_merge([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
        ], $attributes));
    }
}
