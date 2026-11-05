<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name' => trans('homepage_new_login_reg.name_error'),
            'name.max' => trans('homepage_new_login_reg.name_error'),
            'surname' => trans('homepage_new_login_reg.surname_error'),
            'surname.max' => trans('homepage_new_login_reg.surname_error'),
            'email' => trans('homepage_new_login_reg.enter_email'),
            'email.email' => trans('homepage_new_login_reg.email_error'),
            'email.unique' => trans('homepage_new_login_reg.email_unique_error'),
            'phone' => trans('homepage_new_login_reg.phone_error'),
            'phone.unique' => trans('homepage_new_login_reg.phone_error'),
            'password' => trans('homepage_new_login_reg.password_error'),
            'password.min' => trans('homepage_new_login_reg.password_error'),
            'password.required_with' => trans('homepage_new_login_reg.password_error'),
            'password.same' => trans('homepage_new_login_reg.password_error'),
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email', ''))),
            'phone' => preg_replace('/[()\s-]+/', '', (string) $this->input('phone', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'regex:/^\+?\d{7,15}$/'],
            'password' => ['required', 'string', 'min:6', 'max:255', 'same:password_confirmation'],
            'password_confirmation' => ['required', 'string', 'min:6', 'max:255'],
        ];
    }
}
