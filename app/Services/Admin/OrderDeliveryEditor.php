<?php

namespace App\Services\Admin;

use App\Models\ADeliveryTown;
use App\Models\CountryTel;
use App\Models\DeliveryPickupAtViarWorkshop;
use App\Models\Orders;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderDeliveryEditor
{
    public const PICKUP_METHODS = ['pickup_at_viar_workshop', 'pickup_Riga', 'pickup_Daugavplis', 'pickup_Daugavpils'];

    public static function methods(): array
    {
        return [
            'to_the_door' => 'До дверей дома или работы',
            'pickup_Riga' => 'Самовывоз Рига',
            'pickup_Daugavplis' => 'Самовывоз Даугавпилс',
            'pickup_Daugavpils' => 'Самовывоз Даугавпилс',
            'venipak' => 'Venipak',
            'pickup_at_viar_workshop' => 'Мастерская VIAR',
            'city_delivery' => 'Доставка по городу',
        ];
    }

    public function state(Orders $order): array
    {
        $delivery = $this->decode($order->delivery);
        $phone = $delivery['phone'] ?? $this->portraitField($order, 'Phone');
        $state = Arr::only($delivery, [
            'first_name', 'last_name', 'email', 'city', 'address', 'postal_index',
            'country', 'sposob', 'deliv_price', 'when_send', 'pickup_workshop_id', 'delivery_town_id',
        ]);
        $state['email'] ??= $this->portraitField($order, 'Email');
        $state['phone'] = $delivery['payer_phone'] ?? $phone;
        $state['phone_rec'] = array_key_exists('payer_phone', $delivery) || ! empty($delivery['is_no_payer']) ? $phone : null;
        $state['comment'] = $delivery['comment'] ?? $order->comment;
        if ($state['comment'] === 'null') {
            $state['comment'] = null;
        }
        $state['is_manual_express'] = $this->manualExpress($order, $delivery);
        $method = $delivery['sposob'] ?? null;
        if (in_array($method, self::PICKUP_METHODS, true) && empty($state['pickup_workshop_id'])) {
            $state['pickup_workshop_id'] = match ($method) {
                'pickup_Riga' => 1,
                'pickup_Daugavplis', 'pickup_Daugavpils' => 2,
                default => $this->matchingLabel($this->pickups($delivery['country'] ?? null), $delivery['address'] ?? null),
            };
        }
        if ($method === 'city_delivery' && empty($state['delivery_town_id'])) {
            $state['delivery_town_id'] = $this->matchingLabel($this->towns($delivery['country'] ?? null), $delivery['city'] ?? null);
        }

        return $state;
    }

    public function countries(): array
    {
        return CountryTel::query()->orderBy('sort')->pluck('country_code', 'country_code')->all();
    }

    public function pickups(?string $country): array
    {
        return DeliveryPickupAtViarWorkshop::query()->with('translations')->where('is_show', 1)->orderBy('sort')->get()
            ->filter(fn ($row): bool => $this->pickupCountry($row->country_code, $country))
            ->mapWithKeys(fn ($row): array => [$row->id => $row->getTranslatedAttribute('title', 'ru')])->all();
    }

    public function towns(?string $country): array
    {
        return ADeliveryTown::query()->with('translations')->orderBy('id')->get()
            ->filter(fn ($row): bool => ! $country || ! trim((string) $row->country) || strtoupper(trim($row->country)) === strtoupper($country))
            ->mapWithKeys(fn ($row): array => [$row->id => $row->getTranslatedAttribute('city', 'ru')])->all();
    }

    /** Synchronize the visible address and country-filtered selectors, as the original edit page does. */
    public function synchronize(callable $get, callable $set): void
    {
        $method = $get('delivery_edit.sposob');
        $country = $get('delivery_edit.country');
        $pickup = null;
        $town = null;
        if (in_array($method, self::PICKUP_METHODS, true)) {
            $options = $this->pickups($country);
            $pickup = match ($method) {
                'pickup_Riga' => 1,
                'pickup_Daugavplis', 'pickup_Daugavpils' => 2,
                default => $get('delivery_edit.pickup_workshop_id'),
            };
            if (! isset($options[$pickup ?? ''])) {
                $pickup = array_key_first($options);
            }
            if ($pickup !== null) {
                $set('delivery_edit.address', $options[$pickup]);
                // Voyager clears the hidden city on method/country changes for every pickup variant.
                // Selecting a pickup explicitly has its separate callback and save still resolves its title.
                $set('delivery_edit.city', null);
            }
        } elseif ($method === 'city_delivery') {
            $options = $this->towns($country);
            $town = $get('delivery_edit.delivery_town_id');
            if (! isset($options[$town ?? ''])) {
                $town = array_key_first($options);
            }
            if ($town !== null) {
                $set('delivery_edit.city', $options[$town]);
            }
        }
        $set('delivery_edit.pickup_workshop_id', $pickup);
        $set('delivery_edit.delivery_town_id', $town);
    }

    /** Merge only editable keys into the latest persisted JSON; never touch items or files. */
    public function attributes(Orders $order, array $input): array
    {
        $rules = [
            'first_name' => 'nullable|string|max:255', 'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255', 'phone' => 'nullable|string|max:50',
            'phone_rec' => 'nullable|string|max:50', 'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000', 'postal_index' => 'nullable|string|max:30',
            'country' => 'nullable|string|max:10', 'sposob' => ['nullable', Rule::in(array_keys(self::methods()))],
            'when_send' => 'nullable|date_format:Y-m-d', 'deliv_price' => 'nullable|numeric|min:0',
            'pickup_workshop_id' => 'nullable|integer|min:1', 'delivery_town_id' => 'nullable|integer|min:1',
            'is_manual_express' => 'sometimes|boolean', 'comment' => 'nullable|string|max:10000',
        ];
        $data = validator(['delivery_edit' => $input], collect($rules)->mapWithKeys(
            fn ($rule, $key): array => ['delivery_edit.'.$key => $rule]
        )->all())->validate()['delivery_edit'] ?? [];
        $existing = $this->decode($order->delivery, true);
        $delivery = array_replace($existing, Arr::only($data, [
            'first_name', 'last_name', 'email', 'city', 'address', 'postal_index',
            'country', 'sposob', 'when_send', 'deliv_price', 'comment',
        ]));
        if (array_key_exists('phone', $data) || array_key_exists('phone_rec', $data)) {
            $payer = $this->phone($data['phone'] ?? $existing['payer_phone'] ?? $existing['phone'] ?? null);
            $recipient = array_key_exists('phone_rec', $data) ? $this->phone($data['phone_rec']) : ($existing['phone'] ?? null);
            $delivery['payer_phone'] = $payer;
            $delivery['phone'] = $recipient ?: $payer;
        }
        $delivery['is_manual_express'] = array_key_exists('is_manual_express', $data)
            ? (int) $data['is_manual_express'] : (int) $this->manualExpress($order, $existing);

        // Resolve address identity only when delivery location fields were submitted.
        if (array_intersect(array_keys($data), ['sposob', 'country', 'city', 'address', 'pickup_workshop_id', 'delivery_town_id'])) {
            $method = $delivery['sposob'] ?? null;
            $pickup = $data['pickup_workshop_id'] ?? null;
            $town = $data['delivery_town_id'] ?? null;
            $delivery['pickup_workshop_id'] = null;
            $delivery['delivery_town_id'] = null;
            $delivery['delivery_photo_short_code'] = null;
            if (in_array($method, self::PICKUP_METHODS, true)) {
                $pickup ??= match ($method) {
                    'pickup_Riga' => 1,
                    'pickup_Daugavplis', 'pickup_Daugavpils' => 2,
                    default => null,
                };
                if ($pickup) {
                    $row = DB::table('delivery_pickup_at_viar_workshop')->where('id', $pickup)->first();
                    if (! $row) {
                        throw ValidationException::withMessages(['delivery_edit.pickup_workshop_id' => 'Пункт выдачи не найден.']);
                    }
                    $delivery['pickup_workshop_id'] = (int) $row->id;
                    $delivery['address'] = $row->title;
                    $delivery['city'] = $method === 'pickup_at_viar_workshop' ? null : $row->title;
                    $delivery['delivery_photo_short_code'] = trim((string) $row->photo_short_code) ?: null;
                } elseif (in_array($existing['sposob'] ?? null, self::PICKUP_METHODS, true)) {
                    $delivery['pickup_workshop_id'] = $existing['pickup_workshop_id'] ?? null;
                    $delivery['delivery_photo_short_code'] = $existing['delivery_photo_short_code'] ?? null;
                }
            } elseif ($method === 'city_delivery') {
                if ($town) {
                    $row = DB::table('a_delivery_towns')->where('id', $town)->first();
                    if (! $row) {
                        throw ValidationException::withMessages(['delivery_edit.delivery_town_id' => 'Город доставки не найден.']);
                    }
                    $delivery['delivery_town_id'] = (int) $row->id;
                    $delivery['city'] = $row->city;
                    $delivery['delivery_photo_short_code'] = trim((string) $row->photo_short_code) ?: null;
                } elseif (($existing['sposob'] ?? null) === 'city_delivery') {
                    $delivery['delivery_town_id'] = $existing['delivery_town_id'] ?? null;
                    $delivery['delivery_photo_short_code'] = $existing['delivery_photo_short_code'] ?? null;
                }
            }
        }
        $attributes = ['delivery' => json_encode($delivery, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)];
        if (array_key_exists('comment', $data)) {
            $attributes['comment'] = $data['comment'];
        }

        return $attributes;
    }

    private function decode(mixed $value, bool $strict = false): array
    {
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode((string) $value, true);
        if ($strict && $value !== null && $value !== '' && ! is_array($decoded)) {
            throw ValidationException::withMessages(['delivery_edit' => 'Не удалось прочитать данные доставки.']);
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function phone(?string $value): ?string
    {
        return trim(str_replace([' ', ')', '(', '-'], '', $value ?? '')) ?: null;
    }

    private function manualExpress(Orders $order, array $delivery): bool
    {
        $enabled = fn ($value): bool => is_scalar($value) && in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
        if ($enabled($delivery['is_manual_express'] ?? null)) {
            return true;
        }
        foreach ($this->decode($order->items) as $key => $item) {
            if (is_int($key) && is_array($item) && $enabled($item['is_manual_express'] ?? null)) {
                return true;
            }
        }

        return false;
    }

    private function pickupCountry(?string $codes, ?string $country): bool
    {
        $allowed = array_filter(array_map('trim', explode(',', strtoupper($codes ?? 'ALL'))));

        return ! $country || ! $allowed || in_array('ALL', $allowed, true) || in_array(strtoupper($country), $allowed, true);
    }

    private function matchingLabel(array $options, ?string $label): int|string|null
    {
        if (trim($label ?? '') === '') {
            return null;
        }
        foreach ($options as $id => $title) {
            if (mb_strtolower(trim($title)) === mb_strtolower(trim($label))) {
                return $id;
            }
        }

        return null;
    }

    private function portraitField(Orders $order, string $name): ?string
    {
        foreach ($this->decode($order->items) as $item) {
            if (! is_array($item)) {
                continue;
            }
            foreach (explode('<br>', (string) ($item['content'] ?? '')) as $line) {
                $parts = explode(': ', trim($line), 2);
                if ($parts[0] === $name && isset($parts[1])) {
                    return $parts[1];
                }
            }
        }

        return null;
    }
}
