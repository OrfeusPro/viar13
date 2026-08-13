<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicReviewFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->timestamps();
        });
        Schema::dropIfExists('orders');
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('status')->nullable();
            $table->json('items')->nullable();
            $table->timestamps();
        });
        Schema::dropIfExists('reviews');
        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->string('img')->nullable();
            $table->string('avatar')->nullable();
            $table->string('name');
            $table->string('email');
            $table->text('text');
            $table->boolean('active')->default(false);
            $table->text('a_player')->nullable();
            $table->string('orig_locale')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('order_id')->nullable();
            $table->unsignedBigInteger('pid')->default(0);
            $table->timestamps();
        });
    }

    public function test_review_requires_identity_and_text(): void
    {
        $this->from('/review')
            ->post('/review')
            ->assertRedirect('/review')
            ->assertSessionHasErrors(['name', 'email', 'text']);

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_guest_can_submit_text_review_without_audio_or_files(): void
    {
        Storage::fake('public');

        $this->post('/review', [
            'name' => 'Frontend Customer',
            'email' => 'reviewer@example.test',
            'text' => 'Laravel 13 review form works.',
        ])->assertOk()->assertSeeText('success');

        $this->assertDatabaseHas('reviews', [
            'email' => 'reviewer@example.test',
            'text' => 'Laravel 13 review form works.',
            'a_player' => '',
            'active' => 0,
        ]);
    }

    public function test_review_rejects_invalid_audio_data_url(): void
    {
        $this->from('/review')
            ->post('/review', [
                'name' => 'Frontend Customer',
                'email' => 'reviewer@example.test',
                'text' => 'Invalid audio must not be stored.',
                'audioData' => 'data:text/plain;base64,dGVzdA==',
            ])
            ->assertRedirect('/review')
            ->assertSessionHasErrors('audioData');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_review_stores_valid_image_and_audio_on_public_disk(): void
    {
        Storage::fake('public');

        $this->post('/review', [
            'name' => 'Media Customer',
            'email' => 'media@example.test',
            'text' => 'Review with validated media.',
            'file' => [1 => UploadedFile::fake()->image('order.jpg')->size(100)],
            'audioData' => 'data:audio/webm;base64,dGVzdA==',
        ])->assertOk()->assertSeeText('success');

        $review = DB::table('reviews')->where('email', 'media@example.test')->first();
        $this->assertNotNull($review);
        Storage::disk('public')->assertExists($review->img);

        $audio = json_decode($review->a_player, true, flags: JSON_THROW_ON_ERROR);
        Storage::disk('public')->assertExists($audio[0]['download_link']);
    }
}
