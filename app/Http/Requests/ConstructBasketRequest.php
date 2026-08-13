<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ConstructBasketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'size' => ['required', 'string', 'max:100'],
            'terms_price' => ['nullable', 'numeric', 'min:0'],
            'userComment' => ['nullable', 'string', 'max:5000'],
            'is_orig_file' => ['nullable', 'boolean'],
            'image_offset' => ['nullable', 'string'],
            'image' => [
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,gif,bmp,tiff,webp,pdf,psd,heic,heif',
            ],
            'collageSvgImage' => ['nullable', 'string'],
            'collageSvgImage_hash' => ['nullable', 'regex:/^[a-f0-9]{32}$/i'],
            'photo_ex' => [
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,gif,bmp,tiff,webp,pdf,psd,heic,heif',
            ],
            'fon' => ['nullable', 'array'],
            'fon.*' => [
                'file',
                'mimes:jpeg,jpg,png,gif,bmp,tiff,webp,pdf,psd,heic,heif',
            ],
            'orig_images' => ['nullable', 'array'],
            'orig_images.*' => [
                'file',
                'mimes:jpeg,jpg,png,gif,bmp,tiff,webp,pdf,psd,heic,heif',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->boolean('is_orig_file')) {
                if (! $this->hasFile('image')) {
                    $validator->errors()->add('image', 'The original image is required.');
                }

                if (! $this->filled('collageSvgImage_hash')) {
                    $validator->errors()->add(
                        'collageSvgImage_hash',
                        'The collage image hash is required.'
                    );
                }

                return;
            }

            $image = $this->input('image_offset');

            if (! is_string($image) || ! $this->isValidBase64Image($image)) {
                $validator->errors()->add('image_offset', 'The generated image is invalid.');
            }
        });
    }

    private function isValidBase64Image(string $image): bool
    {
        $payload = preg_replace('#^data:image/(?:jpeg|jpg|png);base64,#i', '', $image);
        $payload = preg_replace('/\s+/', '', str_replace(' ', '+', $payload));

        return $payload !== '' && base64_decode($payload, true) !== false;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422));
    }
}
