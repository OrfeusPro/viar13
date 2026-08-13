<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use GuzzleHttp\Client;

class RegisterStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
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
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'surname' => 'max:255',
            'email' => 'required|unique:users',
            'phone' => 'required|min:7|regex:/^\+?[0-9\s()-]+$/',
            'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:6',
            'g-recaptcha-response' => ['required', function ($attribute, $value, $fail) {
                $gResponseToken = (string) $value;

                $client = new Client();
                $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                    'form_params' => [
                        'secret' => env('RECAPTCHA_SECRET_KEY'),
                        'response' => $gResponseToken,
                    ]
                ]);

                if (!json_decode($response->getBody(), true)['success']) {
                    $fail('Invalid recaptcha');
                }
            }],
        ];
    }
}
