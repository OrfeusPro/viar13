<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CanvasRecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'size' => ['required', 'string', 'regex:/^\d+(?:\.\d+)?x\d+(?:\.\d+)?$/i'],
            'full_size' => [
                'required',
                'string',
                'regex:/^\d+(?:\.\d+)?x\d+(?:\.\d+)?[a-z]*$/i',
            ],
            // Compatibility only: the controller reads the catalog price.
            'price' => ['nullable', 'numeric', 'min:0'],
            'userImage' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,gif,bmp,tiff,webp,pdf,psd,heic,heif',
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422));
    }
}
