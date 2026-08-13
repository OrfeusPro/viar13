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

                    'userImage' => 'required|image',

                    'formId' => 'required',

                    'sizeId' => 'required',

                    // 'effectId' => 'required',

                    'executionId' => 'required',

                    'canvasId' => 'required',

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

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422));
    }
}
