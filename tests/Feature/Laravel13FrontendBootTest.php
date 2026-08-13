<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Laravel13FrontendBootTest extends TestCase
{
    public function test_laravel_13_frontend_runtime_boots_without_admin_routes(): void
    {
        $this->assertStringStartsWith('13.', app()->version());
        $this->assertTrue(Route::has('home'));
        $this->assertTrue(Route::has('login'));
        $this->assertFalse((bool) config('app.admin_enabled'));
        $this->assertFalse(Route::has('voyager.dashboard'));
    }

    public function test_voyager_frontend_media_adapter_preserves_public_storage_url(): void
    {
        $this->assertStringEndsWith('/storage/example/image.jpg', \Voyager::image('example/image.jpg'));
        $this->assertSame('https://cdn.example.test/image.jpg', \Voyager::image('https://cdn.example.test/image.jpg'));
    }
}
