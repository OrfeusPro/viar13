<?php

namespace App\Http\Requests;

use App\Support\CheckoutPaymentMethods;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paymentData' => [
                'required',
                'string',
                Rule::in(CheckoutPaymentMethods::all()),
            ],
            'paymentMethod' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $method = (string) $this->input('paymentData');
            $delivery = $this->session()->get('cart_delivery');

            if ($method !== '' && ! CheckoutPaymentMethods::isAllowedForDelivery(
                $method,
                is_array($delivery) ? $delivery : null
            )) {
                $validator->errors()->add(
                    'paymentData',
                    'The selected payment method is unavailable for this delivery.'
                );
            }
        });
    }
}
