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
            'name'        => ['required', 'string'],
            'email'       => ['required', 'email'],
            'text'        => ['required', 'string'],
            'photo_order' => ['nullable', 'string'],
            'photo'       => ['nullable', 'string'],
            'audio'       => ['nullable', 'string'],
        ];
    }
}
