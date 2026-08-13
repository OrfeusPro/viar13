<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PortraitBasketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'price' => ['required', 'numeric', 'min:0'],
            'terms_price' => ['nullable', 'numeric', 'min:0'],
            'userComment' => ['nullable', 'string', 'max:5000'],
            'orig_images' => ['nullable', 'array', 'max:10'],
            'orig_images.*' => [
                'file',
                'mimes:jpeg,jpg,png,gif,bmp,tiff,webp,pdf,psd,heic,heif',
            ],
            'photo_ex' => [
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,gif,bmp,tiff,webp,pdf,psd,heic,heif',
            ],
            'image' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $pid = $this->input('pid');
            $isOilPortrait = $pid === null || $pid === '' || $pid === 'undefined';
            $hasBase64Image = is_string($this->input('image'))
                && ! in_array($this->input('image'), ['', 'undefined'], true);
            $hasOriginalImage = count((array) $this->file('orig_images', [])) > 0;

            if ($isOilPortrait && ! $hasBase64Image && ! $hasOriginalImage) {
                $validator->errors()->add(
                    'orig_images',
                    'An original image or generated image is required.'
                );
            }

            if ((int) $this->input('is_gall_with_img') === 1 && ! $hasBase64Image) {
                $validator->errors()->add('image', 'The generated image is required.');
            }

            if ($hasBase64Image && ! $this->isValidImageData($this->input('image'))) {
                $validator->errors()->add('image', 'The generated image is invalid.');
            }
        });
    }

    private function isValidImageData(string $image): bool
    {
        if (! preg_match('#^data:image/(jpeg|png);base64,([A-Za-z0-9+/=\s]+)$#', $image, $matches)) {
            return false;
        }

        return base64_decode(preg_replace('/\s+/', '', $matches[2]), true) !== false;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422));
    }
}
