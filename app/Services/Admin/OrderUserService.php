<?php

namespace App\Services\Admin;

use App\Models\Orders;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderUserService
{
    public function updatePdfLocale(Orders $order, string $locale): User
    {
        $locale = validator(['pdf_locale' => $locale], [
            'pdf_locale' => ['required', 'string', Rule::in(array_keys(config('laravellocalization.supportedLocales', [])))],
        ])->validate()['pdf_locale'];

        return $this->updateUser($order, ['pdf_locale' => $locale]);
    }

    public function updateClientStatus(Orders $order, int|string $statusId): User
    {
        $statusId = validator(['client_status' => $statusId], [
            'client_status' => ['required', 'integer', 'exists:user_types,id'],
        ])->validate()['client_status'];

        return $this->updateUser($order, ['client_status' => (int) $statusId]);
    }

    private function updateUser(Orders $order, array $attributes): User
    {
        if (! $order->user_id) {
            throw ValidationException::withMessages(['user' => 'У заказа отсутствует связанный пользователь.']);
        }

        return DB::transaction(function () use ($order, $attributes): User {
            $user = User::query()->lockForUpdate()->find($order->user_id);
            if (! $user) {
                throw ValidationException::withMessages(['user' => 'Связанный пользователь заказа не найден.']);
            }

            $user->forceFill($attributes)->save();

            return $user->refresh();
        });
    }
}
