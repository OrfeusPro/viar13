<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RecommendedBasketItemRequest extends FormRequest
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
            'item_id' => ['required', 'integer', 'min:1'],
            // Compatibility only: controllers calculate price from server data.
            'price' => ['nullable', 'numeric', 'min:0'],
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
