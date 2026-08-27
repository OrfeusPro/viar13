<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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
