<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateReviewRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'text' => ['required', 'string', 'max:10000'],
            'file' => ['nullable', 'array', 'max:2'],
            'file.*' => [
                'nullable',
                'file',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif',
                'max:10240',
            ],
            'audioData' => [
                'nullable',
                'string',
                'max:20971520',
                static function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! preg_match('#^data:audio/(webm|ogg|mpeg|mp4|wav);base64,[A-Za-z0-9+/=\r\n]+$#', $value)) {
                        $fail('The audio recording format is invalid.');
                    }
                },
            ],
        ];
    }
}
