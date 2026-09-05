<?php

namespace App\Services\Admin;

use App\Mail\SendAdminOrder;
use App\Mail\SendUserRegister;
use App\Models\Orders;
use App\Models\User;
use App\Services\SynvolveWebhookService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdminOrderCreationService
{
    public const PAYMENT_METHODS = [
        'cash_in_office', 'on_delivery', 'transfer', 'online_paysera',
        'google_pay', 'apple_pay', 'paypalOnetimePayment', 'creditcart', 'prepayment',
    ];

    public const DELIVERY_METHODS = [
        'to_the_door', 'pickup_Riga', 'pickup_Daugavplis', 'pickup_Daugavpils',
        'venipak', 'pickup_at_viar_workshop', 'city_delivery',
    ];

    /** @return array<string, mixed> */
    public function defaults(?Orders $source = null): array
    {
        // Voyager starts a fresh order for this client; it does not clone the order.
        if ($source) {
            $user = $source->user;

            return array_replace($this->defaults(), [
                'source_order_id' => $source->id,
                'client_id' => $user?->id,
                'email' => $user?->email,
                'first_name' => $user?->first_name,
                'last_name' => $user?->last_name,
                'phone' => $user?->phone,
                'recipient_phone' => $user?->phone,
                'country' => $user?->country ?: 'LV',
                'city' => $user?->city,
                'address' => $user?->address,
                'postal_index' => $user?->postal_index,
            ]);
        }

        return [
            'source_order_id' => null, 'client_id' => null, 'manager_id' => null,
            'admin_comment' => null, 'email' => null, 'first_name' => null,
            'last_name' => null, 'phone' => null, 'recipient_phone' => null,
            'comment' => null, 'a_order_from' => null, 'catid' => 0,
            'payment' => 'cash_in_office', 'payment_status' => 'not_payed',
            'delivery_method' => 'to_the_door', 'country' => 'LV',
            'pickup_workshop_id' => null, 'delivery_town_id' => null,
            'city' => null, 'address' => null, 'postal_index' => null, 'when_send' => null,
            'delivery_price' => 0.0, 'bonus' => 0.0, 'sale_eur' => 0.0, 'sale_percent' => 0.0,
            'is_manual_express' => false, 'is_legal_entity' => false,
            'legal_name' => null, 'legal_registration_number' => null, 'legal_address' => null,
            'legal_vat_number' => null, 'legal_bank_name' => null,
            'legal_bank_code' => null, 'legal_bank_account' => null,
            'items' => [$this->emptyItem()],
        ];
    }

    /** @param array<string, mixed> $input */
    public function create(array $input): Orders
    {
        $validator = validator($input, $this->rules());
        $validator->after(function ($validator) use ($input): void {
            if ((float) ($input['sale_eur'] ?? 0) > 0 && (float) ($input['sale_percent'] ?? 0) > 0) {
                $validator->errors()->add('sale_percent', 'Укажите скидку только в EUR или только в процентах.');
            }
        });
        $data = $validator->validate();
        $storedPaths = [];
        $randomPassword = '';
        $createdUser = false;

        try {
            /** @var Orders $order */
            $order = DB::transaction(function () use ($data, &$storedPaths, &$randomPassword, &$createdUser): Orders {
                $email = mb_strtolower(trim($data['email']));
                $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->lockForUpdate()->first();

                if (! $user) {
                    $createdUser = true;
                    $randomPassword = Str::random(16);
                    $user = new User;
                    $user->forceFill([
                        'first_name' => trim($data['first_name']),
                        'last_name' => trim((string) ($data['last_name'] ?? '')),
                        'email' => $email,
                        'phone' => $this->normalizePhone($data['recipient_phone'] ?? null)
                            ?: $this->normalizePhone($data['phone'] ?? null),
                        'address' => trim((string) ($data['address'] ?? '')),
                        'postal_index' => trim((string) ($data['postal_index'] ?? '')),
                        'country' => strtoupper($data['country']),
                        'client_data' => 'NO',
                        'news' => 'YES',
                        'role_id' => 2,
                        'avatar' => 'users/default.png',
                        'active_coupon' => null,
                        'password' => Hash::make($randomPassword),
                        'settings' => ['locale' => strtolower($data['country'])],
                    ])->save();
                }

                $bonus = round((float) ($data['bonus'] ?? 0), 2);
                if ($bonus > 0) {
                    if ($createdUser || (float) $user->bonuses < $bonus) {
                        throw ValidationException::withMessages([
                            'bonus' => 'Недостаточно бонусов у выбранного клиента.',
                        ]);
                    }
                    $user->forceFill(['bonuses' => round((float) $user->bonuses - $bonus, 2)])->save();
                }

                $delivery = $this->delivery($data);
                if ($bonus > 0) {
                    $delivery['bonus'] = $bonus;
                }

                $subtotal = round(collect($data['items'])->sum(fn (array $item): float => (float) $item['price']), 2);
                $price = max(0, round($subtotal - $bonus, 2));

                $order = new Orders;
                $order->forceFill([
                    'user_id' => $user->id,
                    'manager_id' => $data['manager_id'] ?? 0,
                    'is_admin_order' => 1,
                    'price' => $price,
                    'sale_price' => $price,
                    'sale_eur' => (float) ($data['sale_eur'] ?? 0),
                    'sale_percent' => (float) ($data['sale_percent'] ?? 0),
                    'items' => '[]',
                    'country' => strtoupper($data['country']),
                    'a_order_from' => $data['a_order_from'] ?? null,
                    'delivery' => json_encode($delivery, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'payment' => $data['payment'],
                    'payment_status' => $data['payment_status'],
                    'comment' => $this->nullableString($data['comment'] ?? null),
                    'admin_comment' => $this->nullableString($data['admin_comment'] ?? null),
                    'catid' => $data['catid'] ?? 0,
                    'status' => 'watching',
                    'order_image' => null,
                    'photo' => null,
                    'use_bonus' => $bonus > 0 ? 1 : 0,
                    'ur_name' => ! empty($data['is_legal_entity']) ? 'on' : '',
                    'ur_name_l' => $data['legal_name'] ?? '',
                    'ur_reg_num' => $data['legal_registration_number'] ?? '',
                    'ur_legal_addr' => $data['legal_address'] ?? '',
                    'ur_pnr_nr' => $data['legal_vat_number'] ?? '',
                    'ur_bank_name' => $data['legal_bank_name'] ?? '',
                    'ur_bank_code' => $data['legal_bank_code'] ?? '',
                    'ur_bank_acc_code' => $data['legal_bank_account'] ?? '',
                ])->save();

                $basket = [];
                $hasExpress = (bool) ($data['is_manual_express'] ?? false);
                foreach (array_values($data['items']) as $index => $item) {
                    $basket[$index] = $this->basketItem($order, $user, $item, $index, $storedPaths);
                    $basket[$index] = app(OrderItemPresentationService::class)->apply(
                        $basket[$index],
                        $user->preferredLocale() ?: 'ru',
                    );
                    $hasExpress = $hasExpress || (bool) $basket[$index]['is_manual_express'];
                }
                $basket['totalPrice'] = $price;
                $basket['saved_price'] = $price;
                $basket['formatedTotalPrice'] = $price.' €';
                $delivery['is_manual_express'] = $hasExpress ? 1 : 0;

                $order->forceFill([
                    'items' => json_encode($basket, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'delivery' => json_encode($delivery, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ])->save();

                return $order->refresh()->load('user');
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('uploads')->delete($path);
            }
            throw $exception;
        }

        $this->dispatchNotifications($order, $createdUser, $randomPassword);

        return $order;
    }

    /** @return array<string, mixed> */
    private function rules(): array
    {
        return [
            'source_order_id' => ['nullable', 'integer'],
            'client_id' => ['nullable', 'integer'],
            'manager_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role_id', 4)],
            'admin_comment' => ['nullable', 'string', 'max:10000'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'recipient_phone' => ['nullable', 'string', 'max:50'],
            'comment' => ['nullable', 'string', 'max:10000'],
            'a_order_from' => ['nullable', 'integer'],
            'catid' => ['nullable', 'integer', 'min:0'],
            'payment' => ['required', Rule::in(self::PAYMENT_METHODS)],
            'payment_status' => ['required', Rule::in(UpdateOrderService::PAYMENT_STATUSES)],
            'delivery_method' => ['required', Rule::in(self::DELIVERY_METHODS)],
            'country' => ['required', 'string', 'max:10'],
            'pickup_workshop_id' => ['nullable', 'integer'],
            'delivery_town_id' => ['nullable', 'integer'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'postal_index' => ['nullable', 'string', 'max:30'],
            'when_send' => ['nullable', 'date_format:Y-m-d'],
            'delivery_price' => ['nullable', 'numeric', 'min:0'],
            'bonus' => ['nullable', 'numeric', 'min:0'],
            'sale_eur' => ['nullable', 'numeric', 'min:0'],
            'sale_percent' => ['nullable', 'numeric', 'between:0,100'],
            'is_manual_express' => ['nullable', 'boolean'],
            'is_legal_entity' => ['nullable', 'boolean'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'legal_registration_number' => ['nullable', 'string', 'max:100'],
            'legal_address' => ['nullable', 'string', 'max:1000'],
            'legal_vat_number' => ['nullable', 'string', 'max:100'],
            'legal_bank_name' => ['nullable', 'string', 'max:255'],
            'legal_bank_code' => ['nullable', 'string', 'max:100'],
            'legal_bank_account' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:500'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.size' => ['required', 'string', 'max:255'],
            'items.*.terms' => ['nullable', 'string', 'max:255'],
            'items.*.comment' => ['nullable', 'string', 'max:10000'],
            'items.*.canvas_id' => ['required', 'integer', 'between:1,5'],
            'items.*.gift_code' => ['required', Rule::in(['G0', 'G1', 'G2'])],
            'items.*.decoration_id' => ['required', 'integer', Rule::in([1, 2, 3, 5])],
            'items.*.orientation_code' => ['required', Rule::in(['V0', 'V1', 'V2', 'V3', 'V4'])],
            'items.*.baget_code' => ['required', Rule::in(['B0', 'B1', 'B2'])],
            'items.*.express' => ['nullable', 'boolean'],
            'items.*.existing_images' => ['nullable', 'array'],
            'items.*.existing_images.*' => ['string', 'max:2048'],
            'items.*.legacy_payload' => ['nullable', 'array'],
            'items.*.images' => ['nullable', 'array'],
            'items.*.images.*' => ['file', 'max:102400', 'mimetypes:image/*,application/pdf,application/postscript'],
        ];
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function delivery(array $data): array
    {
        $method = $data['delivery_method'];
        $pickupId = (int) ($data['pickup_workshop_id'] ?? 0);
        $townId = (int) ($data['delivery_town_id'] ?? 0);
        if ($method === 'pickup_Riga' && $pickupId === 0) {
            $pickupId = 1;
        }
        if (in_array($method, ['pickup_Daugavplis', 'pickup_Daugavpils'], true) && $pickupId === 0) {
            $pickupId = 2;
        }

        $delivery = [
            'deliv_price' => (float) ($data['delivery_price'] ?? 0),
            'when_send' => $data['when_send'] ?? null,
            'email' => mb_strtolower(trim($data['email'])),
            'first_name' => trim($data['first_name']),
            'last_name' => trim((string) ($data['last_name'] ?? '')),
            'phone' => $this->normalizePhone($data['recipient_phone'] ?? null)
                ?: $this->normalizePhone($data['phone'] ?? null),
            'payer_phone' => $this->normalizePhone($data['phone'] ?? null),
            'address' => $this->nullableString($data['address'] ?? null),
            'postal_index' => $this->nullableString($data['postal_index'] ?? null),
            'country' => strtoupper($data['country']),
            'a_order_from' => $data['a_order_from'] ?? null,
            'city' => $this->nullableString($data['city'] ?? null),
            'sposob' => $method,
            'payment' => $data['payment'],
            'comment' => $this->nullableString($data['comment'] ?? null),
            'pickup_workshop_id' => null,
            'delivery_town_id' => null,
            'delivery_photo_short_code' => null,
            'is_manual_express' => ! empty($data['is_manual_express']) ? 1 : 0,
        ];

        if (in_array($method, ['pickup_at_viar_workshop', 'pickup_Riga', 'pickup_Daugavplis', 'pickup_Daugavpils'], true) && $pickupId > 0) {
            $pickup = DB::table('delivery_pickup_at_viar_workshop')->where('id', $pickupId)->first();
            if ($pickup) {
                $delivery['pickup_workshop_id'] = (int) $pickup->id;
                $delivery['address'] = $pickup->title;
                $delivery['city'] = $method === 'pickup_at_viar_workshop' ? null : $pickup->title;
                $delivery['delivery_photo_short_code'] = $this->nullableString($pickup->photo_short_code ?? null);
            }
        } elseif ($method === 'city_delivery' && $townId > 0) {
            $town = DB::table('a_delivery_towns')->where('id', $townId)->first();
            if ($town) {
                $delivery['delivery_town_id'] = (int) $town->id;
                $delivery['city'] = $town->city;
                $delivery['delivery_photo_short_code'] = $this->nullableString($town->photo_short_code ?? null);
            }
        }

        return $delivery;
    }

    /** @param array<string, mixed> $item
     * @param  array<int, string>  $storedPaths
     * @return array<string, mixed>
     */
    private function basketItem(Orders $order, User $user, array $item, int $index, array &$storedPaths): array
    {
        $images = array_values($item['existing_images'] ?? []);
        foreach ($item['images'] ?? [] as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $base = Orders::getOrderImageName('', '', false, $order->id, $item['size'], $user, $item['name']);
            $extension = mb_strtolower($file->getClientOriginalExtension() ?: $file->extension());
            // Number sources across the entire order, not item index + file index.
            $path = Storage::disk('uploads')->putFileAs('orders', $file, $base.'_'.count($storedPaths).'.'.$extension);
            if (! $path) {
                throw ValidationException::withMessages(['items.'.$index.'.images' => 'Не удалось сохранить файл позиции.']);
            }
            $storedPaths[] = $path;
            $images[] = url('/'.$path);
        }

        $decoration = (int) $item['decoration_id'];
        $codes = [1 => ['L2', 'P0'], 2 => ['L0', 'P1'], 3 => ['L1', 'P0'], 5 => ['L0', 'P0']][$decoration];
        $price = round((float) $item['price'], 2);

        return array_replace($item['legacy_payload'] ?? [], [
            'name' => trim($item['name']),
            'basketType' => '1',
            'is_def_product' => '1',
            'activeImage' => $item['legacy_payload']['activeImage'] ?? '',
            'count' => max(1, (int) ($item['legacy_payload']['count'] ?? 1)),
            'pack' => $item['legacy_payload']['pack'] ?? '',
            'orig_images' => $images,
            'size_name' => trim($item['size']),
            'userComment' => $this->nullableString($item['comment'] ?? null) ?? '',
            'terms' => $this->nullableString($item['terms'] ?? null) ?? '',
            'price' => $price,
            'sumPrice' => $price,
            'sumFormatedPrice' => $price.' €',
            'formatedPrice' => $price.' €',
            'manual_gift_code' => $item['gift_code'],
            'manual_decoration_id' => $decoration,
            'manual_lac_code' => $codes[0],
            'manual_brushstrokes_code' => $codes[1],
            'manual_orientation_code' => $item['orientation_code'],
            'manual_canvas_id' => (int) $item['canvas_id'],
            'canvasId' => (int) $item['canvas_id'],
            'manual_baget_code' => $item['baget_code'],
            'is_manual_baget' => $item['baget_code'] === 'B1' ? 1 : 0,
            'is_manual_express' => ! empty($item['express']) ? 1 : 0,
        ]);
    }

    /** @return array<string, mixed> */
    private function emptyItem(): array
    {
        return [
            'name' => '', 'price' => 0, 'size' => '', 'terms' => '', 'comment' => '',
            'canvas_id' => 2, 'gift_code' => 'G0', 'decoration_id' => 5,
            'orientation_code' => 'V0', 'baget_code' => 'B0', 'express' => false,
            'existing_images' => [], 'images' => [], 'legacy_payload' => [],
        ];
    }

    private function normalizePhone(mixed $phone): ?string
    {
        $phone = preg_replace('/[\s()\-]+/', '', trim((string) $phone));

        return $phone === '' ? null : $phone;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' || $value === 'null' ? null : $value;
    }

    private function dispatchNotifications(Orders $order, bool $createdUser, string $password): void
    {
        if (! config('admin_migration.order_creation_notifications_enabled', false)) {
            return;
        }

        try {
            $locale = $order->user?->preferredLocale() ?? strtolower((string) $order->country);
            if ($createdUser) {
                Mail::to($order->user->email)->send(new SendUserRegister($order->user, $password, $locale));
            }
            Mail::to($order->user->email)->send(new SendAdminOrder($locale, $order->id, $password));
            app(SynvolveWebhookService::class)->notifyOrderSnapshotById((int) $order->id, 'admin_order_created');
        } catch (Throwable $exception) {
            Log::error('Admin order creation notification failed.', [
                'order_id' => $order->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
