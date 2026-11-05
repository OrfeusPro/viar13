<?php

namespace App\Services\Admin;

use App\Mail\pickup_at_workshop;
use App\Mail\SendUserYourOrderWasSend;
use App\Models\Orders;
use App\Models\User;
use App\Models\UserMessage;
use App\Services\BestEffortMailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class OrderLifecycleService
{
    public function __construct(private readonly BestEffortMailService $mail) {}

    public function updateStatus(Orders $order, string $status): array
    {
        $data = validator(['status' => $status], [
            'status' => ['required', Rule::in(UpdateOrderService::STATUSES)],
        ])->validate();

        $result = DB::transaction(function () use ($order, $data): array {
            $lockedOrder = Orders::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $changed = $lockedOrder->status !== $data['status'];
            if ($changed) {
                $now = now();
                $attributes = ['status' => $data['status'], 'status_date' => $now];
                $dateColumn = $this->statusDateColumn($data['status']);
                if ($dateColumn) {
                    $attributes[$dateColumn] = $now;
                }
                $lockedOrder->forceFill($attributes)->save();
            }

            return ['order_id' => $lockedOrder->id, 'status' => $data['status'], 'changed' => $changed];
        }, 3);

        $result['notifications_suppressed'] = ! config('admin_migration.order_status_notifications_enabled', false);
        if ($result['changed'] && in_array($result['status'], ['sended', 'send_lubanas'], true)) {
            $this->notifyStatus($result);
        }
        $order->refresh();

        return $result;
    }

    public function updateDeliveryDate(Orders $order, ?string $date): Orders
    {
        $data = validator(['delivery_date' => $date], [
            'delivery_date' => ['nullable', 'date_format:Y-m-d'],
        ])->validate();

        return DB::transaction(function () use ($order, $data): Orders {
            $lockedOrder = Orders::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $delivery = json_decode((string) $lockedOrder->delivery, true);
            if (! is_array($delivery)) {
                $delivery = [];
            }

            if ($data['delivery_date']) {
                $delivery['when_send'] = $data['delivery_date'];
            } else {
                unset($delivery['when_send']);
            }

            $lockedOrder->forceFill([
                'delivery' => json_encode($delivery, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])->save();

            return $lockedOrder->refresh();
        }, 3);
    }

    public function delete(Orders $order): array
    {
        return DB::transaction(function () use ($order): array {
            $lockedOrder = Orders::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $refunded = 0.0;

            if ((int) $lockedOrder->use_bonus === 1 && $lockedOrder->user_id) {
                $user = User::query()->whereKey($lockedOrder->user_id)->lockForUpdate()->first();
                if ($user) {
                    $refunded = (float) $lockedOrder->price - (float) $lockedOrder->sale_price;
                    $user->forceFill(['bonuses' => (float) $user->bonuses + $refunded])->save();
                }
            }

            DB::table('vr_numbers')->where('order_id', $lockedOrder->id)->delete();
            $orderId = $lockedOrder->id;
            $lockedOrder->delete();

            return ['order_id' => $orderId, 'refunded_bonus' => $refunded];
        }, 3);
    }

    private function statusDateColumn(string $status): ?string
    {
        return match ($status) {
            'watching' => 'watching_date',
            'pegging' => 'pegging_date',
            'in_production' => 'in_production_date',
            'sended' => 'send_date',
            'send_lubanas' => 'send_lubanas_date',
            'completed' => 'completed_date',
            default => null,
        };
    }

    private function notifyStatus(array $result): void
    {
        if ($result['notifications_suppressed']) {
            Log::info('Admin order status notification suppressed during Filament UAT.', $result);

            return;
        }

        $order = Orders::query()->with('user')->find($result['order_id']);
        $user = $order?->user;
        $delivery = json_decode((string) $order?->delivery, true) ?: [];
        $email = $delivery['email'] ?? $user?->email;
        $message = $this->messageFor($user);
        if (! $order || ! $user || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Order status email skipped: recipient is missing.', ['order_id' => $result['order_id']]);

            return;
        }

        if ($result['status'] === 'sended') {
            if (! $message) {
                Log::warning('Order status email skipped: message texts are missing.', ['order_id' => $order->id]);

                return;
            }
            $mailable = new SendUserYourOrderWasSend($order, $message, $user->preferredLocale() ?: 'ru');
        } else {
            $mailable = new pickup_at_workshop(['address' => '', 'order_id' => $order->id], $user->preferredLocale() ?: 'ru');
        }

        $this->mail->send($email, $mailable, 'admin_order_status_'.$result['status'], [
            'order_id' => $order->id,
            'user_id' => $user->id,
        ]);
    }

    private function messageFor(?User $user): mixed
    {
        if (! $user) {
            return null;
        }

        try {
            return UserMessage::query()->get()->translate($user->preferredLocale() ?: 'ru', 'ru')->first();
        } catch (Throwable $exception) {
            Log::warning('Order status notification texts could not be loaded.', [
                'user_id' => $user->id,
                'exception' => $exception,
            ]);

            return null;
        }
    }
}
