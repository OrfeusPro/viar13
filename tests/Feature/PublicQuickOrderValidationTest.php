<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
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

    public function test_quick_order_has_no_application_file_size_limit(): void
    {
        $rules = (new \App\Http\Requests\QuickOrderRequest())->rules();
        $validator = Validator::make([
            'email' => 'customer@example.test',
            'phone' => '+37120123456',
            'file' => [UploadedFile::fake()->create('print-source.jpg', 102400, 'image/jpeg')],
        ], $rules);

        $this->assertTrue($validator->passes(), $validator->errors()->toJson());
    }

    public function test_legacy_numbered_upload_fields_are_normalized_to_the_file_array(): void
    {
        $this->from('/')
            ->post('/send_photo_portrait_form', [
                'email' => 'customer@example.test',
                'phone' => '123',
                'file' => UploadedFile::fake()->create('portrait.jpg', 100, 'image/jpeg'),
                'file2' => UploadedFile::fake()->create('reference.jpg', 102400, 'image/jpeg'),
            ])
            ->assertRedirect('/')
            ->assertSessionHasErrors('phone')
            ->assertSessionDoesntHaveErrors(['file', 'file.0', 'file.1']);
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
