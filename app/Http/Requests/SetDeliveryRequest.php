<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SetDeliveryRequest extends FormRequest
{
    private const DELIVERY_TYPES = [
        'to_the_door',
        'venipak',
        'pickup_at_viar_workshop',
        'city_delivery',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'country' => strtoupper(trim((string) $this->input('country'))),
            'delivery_type' => $this->filled('delivery_type')
                ? trim((string) $this->input('delivery_type'))
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'country' => ['required', 'string', 'max:4', 'exists:country_tels,country_code'],
            'delivery_type' => ['nullable', 'string', Rule::in(self::DELIVERY_TYPES)],
            'cartDate' => ['nullable', 'date_format:d/m/Y'],
            'cartComment' => ['nullable', 'string', 'max:10000'],
            'cartCommentImage' => ['nullable', 'array'],
            'cartCommentImage.src' => ['nullable', 'string'],
            'city' => [
                Rule::requiredIf(fn (): bool => in_array($this->input('delivery_type'), [
                    'to_the_door',
                    'venipak',
                    'pickup_at_viar_workshop',
                ], true)),
                'nullable',
                'string',
                'max:255',
            ],
            'index' => ['required_if:delivery_type,to_the_door', 'nullable', 'string', 'max:32'],
            'address' => [
                Rule::requiredIf(fn (): bool => in_array($this->input('delivery_type'), [
                    'to_the_door',
                    'city_delivery',
                ], true)),
                'nullable',
                'string',
                'max:1000',
            ],
            'pickup' => ['required_if:delivery_type,venipak', 'nullable', 'string', 'max:1000'],
            'pickup_workshop_id' => [
                'required_if:delivery_type,pickup_at_viar_workshop',
                'nullable',
                'integer',
                Rule::exists('delivery_pickup_at_viar_workshop', 'id')->where('is_show', 1),
            ],
            'delivery_town_id' => [
                'required_if:delivery_type,city_delivery',
                'nullable',
                'integer',
                'exists:a_delivery_towns,id',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $deliveryType = $this->input('delivery_type');
            $country = $this->input('country');

            if ($deliveryType === 'pickup_at_viar_workshop' && $this->filled('pickup_workshop_id')) {
                $validWorkshop = DB::table('delivery_pickup_at_viar_workshop')
                    ->where('id', $this->integer('pickup_workshop_id'))
                    ->where('is_show', 1)
                    ->whereIn('country_code', ['ALL', $country])
                    ->exists();

                if (! $validWorkshop) {
                    $validator->errors()->add('pickup_workshop_id', 'The selected workshop is unavailable.');
                }
            }

            if ($deliveryType === 'city_delivery' && $this->filled('delivery_town_id')) {
                $validTown = DB::table('a_delivery_towns')
                    ->where('id', $this->integer('delivery_town_id'))
                    ->where('country', $country)
                    ->exists();

                if (! $validTown) {
                    $validator->errors()->add('delivery_town_id', 'The selected delivery town is unavailable.');
                }
            }

            $source = $this->input('cartCommentImage.src');

            if ($source === null || $source === '') {
                return;
            }

            if (! preg_match('/^data:image\/(png|jpeg);base64,([A-Za-z0-9+\/=\r\n]+)$/', $source, $matches)) {
                $validator->errors()->add('cartCommentImage.src', 'The comment image is invalid.');
                return;
            }

            if (base64_decode($matches[2], true) === false) {
                $validator->errors()->add('cartCommentImage.src', 'The comment image is invalid.');
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => 0,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422));
    }
}
