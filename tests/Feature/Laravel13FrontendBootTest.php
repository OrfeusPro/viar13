<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use TCG\Voyager\Traits\Translatable;

class Laravel13FrontendBootTest extends TestCase
{
    public function test_voyager_translatable_adapter_ignores_invalid_attribute_names(): void
    {
        $model = new class extends Model
        {
            use Translatable;

            protected $translatable = ['title', '', null];
        };

        $this->assertSame(['title'], $model->getTranslatableAttributes());
    }

    public function test_laravel_13_frontend_runtime_boots_without_admin_routes(): void
    {
        $this->assertStringStartsWith('13.', app()->version());
        $this->assertTrue(Route::has('home'));
        $this->assertTrue(Route::has('login'));
        $this->assertFalse((bool) config('app.admin_enabled'));
        $this->assertFalse(Route::has('voyager.dashboard'));
        $this->assertFalse(Route::has('verification.notice'));
    }

    public function test_voyager_frontend_media_adapter_preserves_public_storage_url(): void
    {
        $this->assertStringEndsWith('/storage/example/image.jpg', \Voyager::image('example/image.jpg'));
        $this->assertSame('https://cdn.example.test/image.jpg', \Voyager::image('https://cdn.example.test/image.jpg'));
    }
}
