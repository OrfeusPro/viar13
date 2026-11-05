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
        // Replays do not create another item and do not require another upload.
        foreach ((array) $this->session()->get('basket', []) as $item) {
            if (is_array($item) && (! empty($item['is_recommendation']) || ! empty($item['is_canvas_recommendation']))) {
                return [];
            }
        }

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
