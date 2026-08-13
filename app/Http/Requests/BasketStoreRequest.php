<?php

namespace App\Http\Requests;

use App\Entity\BasketType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class BasketStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $activeImage = $this->files->get('activeImage');
        $frontendImage = $this->files->get('image');

        if (
            (int) $this->input('basketType') === BasketType::MODULAR_PICTURES_TYPE
            && ! $activeImage
            && $frontendImage
        ) {
            $this->files->set('activeImage', $frontendImage);
        }
    }

    public function rules(): array
    {
        $rules = [
            'basketType' => ['required', 'integer', Rule::in([
                BasketType::CANVAS_TYPE,
                BasketType::MODULAR_PICTURES_TYPE,
                BasketType::COLLAGE_TYPE,
                BasketType::GIFT_CARD,
            ])],
            'price' => ['required', 'numeric', 'min:0'],
        ];

        if (!($this->request->get('basketType'))) {
            return $rules;
        }

        switch ($this->request->get('basketType')) {

            case BasketType::CANVAS_TYPE:

                $rules = [

                    'basketType' => $rules['basketType'],

                    'price' => $rules['price'],

                    'userImage' => [
                        'required',
                        'file',
                        'mimes:jpeg,jpg,png,gif,bmp,tiff,webp,pdf,psd,heic,heif',
                    ],

                    'formId' => ['required', 'integer', 'min:1'],

                    'sizeId' => ['required', 'string', 'max:100'],

                    // 'effectId' => 'required',

                    'executionId' => ['required', 'integer', 'min:1'],

                    'canvasId' => ['required', 'integer', 'min:1'],

                    'decorationId' => ['nullable', 'integer', 'min:1'],

                    'ram_id' => ['nullable', 'integer', 'min:1'],

                    'terms_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],

                    'boxIds' => ['nullable', 'json'],

                    'Image3d' => ['nullable', 'string'],

                    'orig_images' => ['nullable', 'array'],

                    'orig_images.*' => [
                        'file',
                        'mimes:jpeg,jpg,png,gif,bmp,tiff,webp,pdf,psd,heic,heif',
                    ],

                    'photo_ex' => [
                        'nullable',
                        'file',
                        'mimes:jpeg,jpg,png,gif,bmp,tiff,webp,pdf,psd,heic,heif',
                    ],

                ];

                break;

            case BasketType::MODULAR_PICTURES_TYPE:

                $rules = [

                    'basketType' => $rules['basketType'],

                    'price' => $rules['price'],

                    'size' => 'required',

                    'activeImage' => [
                        'required',
                        'file',
                        'mimes:jpeg,jpg,png,gif,bmp,tiff,webp,pdf,psd,heic,heif',
                    ],

                    'executionId' => 'required',

                ];

                break;

            case BasketType::COLLAGE_TYPE:

                $rules = [

                    'basketType' => $rules['basketType'],

                    'price' => $rules['price'],

                    'allImages' => 'required',

                    'finalImg' => 'required',

                    'sizeId' => 'required',

                    'canvasId' => 'required',

                ];

                break;

            default:

                break;

        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ((int) $this->input('basketType') !== BasketType::CANVAS_TYPE) {
                return;
            }

            $preview = $this->input('Image3d');
            if ($preview !== null && $preview !== '' && ! $this->isValidImageData($preview)) {
                $validator->errors()->add('Image3d', 'The canvas preview is invalid.');
            }

            $boxIds = $this->input('boxIds');
            if ($boxIds !== null && $boxIds !== '') {
                $decoded = json_decode($boxIds, true);
                if (
                    ! is_array($decoded)
                    || ! array_is_list($decoded)
                    || array_filter($decoded, fn ($id) => ! is_int($id) || $id < 1)
                ) {
                    $validator->errors()->add('boxIds', 'The packaging selection is invalid.');
                }
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
