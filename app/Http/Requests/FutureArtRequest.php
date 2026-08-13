<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FutureArtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'tel' => preg_replace('/[\s\-()]/', '', (string) $this->input('tel')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tel' => ['required', 'regex:/^\+?\d{7,15}$/'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'images' => ['required', 'array', 'min:1', 'max:15'],
            'images.*' => [
                'file',
                'mimes:jpeg,jpg,png,bmp,heic,heif',
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Invalid file count or format.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
