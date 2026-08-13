<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PublicQuickOrderValidationTest extends TestCase
{
    public function test_portrait_and_all_styles_forms_reject_invalid_contacts_and_missing_files(): void
    {
        foreach (['/send_photo_portrait_form', '/all_styles_form'] as $endpoint) {
            $this->from('/')
                ->post($endpoint, [
                    'email' => 'not-an-email',
                    'phone' => '123',
                ])
                ->assertRedirect('/')
                ->assertSessionHasErrors(['email', 'phone', 'file']);
        }
    }

    public function test_quick_order_rejects_oversized_file(): void
    {
        $this->from('/')
            ->post('/send_photo_portrait_form', [
                'email' => 'customer@example.test',
                'phone' => '+371 20-123-456',
                'file' => [UploadedFile::fake()->create('portrait.jpg', 15361, 'image/jpeg')],
            ])
            ->assertRedirect('/')
            ->assertSessionHasErrors('file.0');
    }

    public function test_legacy_numbered_upload_fields_are_normalized_to_the_file_array(): void
    {
        $this->from('/')
            ->post('/send_photo_portrait_form', [
                'email' => 'customer@example.test',
                'phone' => '+371 20-123-456',
                'file' => UploadedFile::fake()->create('portrait.jpg', 100, 'image/jpeg'),
                'file2' => UploadedFile::fake()->create('reference.jpg', 15361, 'image/jpeg'),
            ])
            ->assertRedirect('/')
            ->assertSessionHasErrors('file.1');
    }

    public function test_photo_calculation_form_rejects_invalid_contact_data(): void
    {
        $this->from('/')
            ->post('/send_photo_form', [
                'email' => 'not-an-email',
                'phone' => '123',
                'website' => '',
                'form_ts' => now()->subSeconds(10)->timestamp,
            ])
            ->assertRedirect('/')
            ->assertSessionHasErrors(['email', 'phone']);
    }
}
