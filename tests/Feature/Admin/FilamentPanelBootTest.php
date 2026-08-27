<?php

namespace Tests\Feature\Admin;

use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Auth;
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
