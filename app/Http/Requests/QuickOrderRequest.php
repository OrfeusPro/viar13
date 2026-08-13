<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class QuickOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $uploadedFiles = $this->files->all();
        $files = $uploadedFiles['file'] ?? [];
        $files = $files instanceof UploadedFile ? [$files] : (array) $files;

        foreach (range(2, 10) as $index) {
            $file = $uploadedFiles['file'.$index] ?? null;

            if ($file instanceof UploadedFile) {
                $files[] = $file;
            }
        }

        if ($files !== []) {
            $this->files->set('file', $files);
        }

        $this->merge([
            'phone' => preg_replace('/[()\s-]+/', '', (string) $this->input('phone', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:150'],
            'phone' => ['required', 'regex:/^\+?\d{7,15}$/'],
            'file' => ['required', 'array', 'min:1', 'max:10'],
            'file.*' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,gif,bmp,tiff,webp,pdf,heic,heif',
                'max:15360',
            ],
        ];
    }
}
