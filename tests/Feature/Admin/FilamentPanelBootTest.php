<?php

namespace Tests\Feature\Admin;

use Illuminate\Auth\GenericUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FilamentPanelBootTest extends TestCase
{
    public function test_guest_is_redirected_from_filament_panel_to_filament_login(): void
    {
        $response = $this->get('/filament');

        $response->assertRedirect('/filament/login');
    }

    public function test_filament_login_page_is_available(): void
    {
        $this->get('/filament/login')->assertOk();
    }

    public function test_login_uses_default_logo_when_settings_are_unavailable(): void
    {
        $this->get('/filament/login')->assertOk()
            ->assertSee('https://viarcanvas.com/storage/settings/March2021/Ey47Eo1q2bpmmxdRrHDv.png', false)
            ->assertSee('ViarCanvas');
    }

    public function test_login_and_shared_header_logo_follow_admin_settings(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });
        DB::table('settings')->insert([
            ['key' => 'admin.title', 'value' => 'ViarCanvas Studio'],
            ['key' => 'admin.icon_image', 'value' => 'settings\\March2021\\custom.png'],
        ]);
        config(['voyager.storage.disk' => 'public', 'filesystems.disks.public.url' => 'https://example.test/storage']);

        $this->get('/filament/login')->assertOk()
            ->assertSee('ViarCanvas Studio')
            ->assertSee('https://example.test/storage/settings/March2021/custom.png', false);

        DB::table('settings')->where('key', 'admin.title')->update(['value' => '<Studio>']);
        DB::table('settings')->where('key', 'admin.icon_image')->update(['value' => 'https://example.test/new.png']);
        $headerLogo = Blade::render('<x-filament-panels::logo />');

        $this->assertStringContainsString('https://example.test/new.png', $headerLogo);
        $this->assertStringContainsString('&lt;Studio&gt;', $headerLogo);
        $this->assertStringNotContainsString('<Studio>', $headerLogo);
    }

    public function test_password_is_masked_before_javascript_initializes(): void
    {
        $html = $this->get('/filament/login')->assertOk()->getContent();
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        $password = (new \DOMXPath($dom))->query('//input[@id="form.password"]')->item(0);

        $this->assertNotNull($password);
        $this->assertSame('password', $password->getAttribute('type'));
        $this->assertSame('current-password', $password->getAttribute('autocomplete'));
        $this->assertSame("isPasswordRevealed ? 'text' : 'password'", $password->getAttribute('x-bind:type'));
    }

    public function test_frontend_session_does_not_block_filament_login(): void
    {
        Auth::guard('web')->setUser(new GenericUser([
            'id' => 999,
            'email' => 'frontend@example.test',
            'password' => 'frontend-password-hash',
        ]));

        $this->get('/filament/login')->assertOk();
        $this->get('/filament')->assertRedirect('/filament/login');
    }

    public function test_legacy_voyager_routes_are_not_registered(): void
    {
        $this->assertFalse(app('router')->has('voyager.dashboard'));
        $this->assertFalse(app('router')->has('voyager.orders.index'));
    }
}
