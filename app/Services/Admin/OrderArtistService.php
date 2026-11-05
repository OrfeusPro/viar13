<?php

namespace App\Services\Admin;

use App\Mail\SendPrainterToUserPicture;
use App\Models\Orders;
use App\Models\PainterOrder;
use App\Models\PrintingOrder;
use App\Models\User;
use App\Models\UserMessage;
use App\Services\BestEffortMailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderArtistService
{
    public function __construct(private readonly BestEffortMailService $mail) {}

    public function updatePaintingStatuses(Orders $order, array $input): void
    {
        $data = validator($input, [
            'images' => ['required', 'array', 'min:1'],
            'images.*.id' => ['required', 'integer', 'distinct'],
            'images.*.status' => ['nullable', 'integer', 'exists:a_painter_images_status,id'],
        ])->validate();

        DB::transaction(function () use ($order, $data): void {
            $lockedOrder = Orders::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            foreach ($data['images'] as $image) {
                $updated = $lockedOrder->order_painter_images()->whereKey($image['id'])
                    ->where('is_img_painter', 1)
                    ->update(['status' => $image['status'] ?? null, 'updated_at' => now()]);
                if ($updated !== 1) {
                    throw ValidationException::withMessages(['images' => 'Изображение не является готовой картиной этого заказа.']);
                }
            }
        });
        $order->unsetRelation('order_painter_images');
    }

    public function updateMediaStatus(Orders $order, int $imageId, string $type, mixed $status): void
    {
        $data = validator(compact('type', 'status'), [
            'type' => ['required', Rule::in(['sketch', 'painting'])],
            'status' => ['required', 'integer', 'exists:a_painter_images_status,id'],
        ])->validate();
        DB::transaction(function () use ($order, $imageId, $data): void {
            $locked = Orders::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $flag = $data['type'] === 'sketch' ? 'is_img_sketch' : 'is_img_painter';
            $updated = $locked->order_painter_images()->whereKey($imageId)->where($flag, 1)
                ->update(['status' => $data['status'], 'updated_at' => now()]);
            if ($updated !== 1) {
                throw ValidationException::withMessages(['status' => 'Изображение не принадлежит выбранной галерее этого заказа.']);
            }
        });
        $order->unsetRelation('order_painter_images');
    }

    public function update(Orders $order, array $input): array
    {
        return $this->persist($order, $input, false);
    }

    public function updateField(Orders $order, string $field, mixed $value): array
    {
        if (! in_array($field, ['painter_id', 'printing_id', 'painter_endtime', 'painter_payed', 'is_show_painter_images'], true)) {
            throw ValidationException::withMessages(['field' => 'Неизвестное поле художника.']);
        }

        return $this->persist($order, [$field => $value], true);
    }

    private function persist(Orders $order, array $input, bool $partial): array
    {
        $data = validator($input, [
            'painter_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role_id', 3)],
            'printing_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role_id', 6)],
            'painter_endtime' => ['nullable', 'date_format:Y-m-d'],
            'painter_payed' => [$partial ? 'sometimes' : 'required', 'boolean'],
            'is_show_painter_images' => [$partial ? 'sometimes' : 'required', 'boolean'],
            'images' => ['array'],
            'images.*.id' => ['required', 'integer'],
            'images.*.status' => ['nullable', 'integer', 'exists:a_painter_images_status,id'],
        ])->validate();

        $result = DB::transaction(function () use ($order, $data, $partial): array {
            $lockedOrder = Orders::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $oldPainterId = PainterOrder::query()->where('order_id', $lockedOrder->id)->lockForUpdate()->value('user_id');
            $oldShowing = (bool) $lockedOrder->is_show_painter_images;

            if (! $partial || array_key_exists('painter_id', $data)) {
                $this->syncAssignment(PainterOrder::class, $lockedOrder->id, $data['painter_id'] ?? null);
            }
            if (! $partial || array_key_exists('printing_id', $data)) {
                $this->syncAssignment(PrintingOrder::class, $lockedOrder->id, $data['printing_id'] ?? null);
            }

            $attributes = [];
            foreach (['painter_endtime', 'painter_payed', 'is_show_painter_images'] as $field) {
                if (! $partial || array_key_exists($field, $data)) {
                    $attributes[$field] = $field === 'painter_endtime' ? ($data[$field] ?? null) : ($data[$field] ? 1 : null);
                }
            }
            if ($attributes !== []) {
                $lockedOrder->forceFill($attributes)->save();
            }

            foreach ($data['images'] ?? [] as $image) {
                $updated = $lockedOrder->order_painter_images()
                    ->whereKey($image['id'])
                    ->update(['status' => $image['status'] ?? null, 'updated_at' => now()]);

                if ($updated !== 1) {
                    throw ValidationException::withMessages([
                        'images' => 'Одно из изображений не принадлежит этому заказу.',
                    ]);
                }
            }

            return [
                'order_id' => $lockedOrder->id,
                'painter_id' => array_key_exists('painter_id', $data) ? $data['painter_id'] : ($partial ? $oldPainterId : null),
                'painter_changed' => (! $partial || array_key_exists('painter_id', $data)) && (int) $oldPainterId !== (int) ($data['painter_id'] ?? 0),
                'show_enabled' => ! $oldShowing && (bool) ($data['is_show_painter_images'] ?? false),
            ];
        }, 3);

        $result['notifications_suppressed'] = ! config('admin_migration.artist_notifications_enabled', false);
        $this->notify($result);
        $order->refresh()->load(['painterAssignment.user', 'printingAssignment.user', 'order_painter_images.statusDefinition']);

        return $result;
    }

    private function syncAssignment(string $model, int $orderId, ?int $userId): void
    {
        $query = $model::query()->where('order_id', $orderId);
        if ($userId === null) {
            $query->delete();

            return;
        }

        $assignment = $query->where('user_id', $userId)->first();
        $model::query()->where('order_id', $orderId)
            ->when($assignment, fn ($assignments) => $assignments->whereKeyNot($assignment->getKey()))
            ->delete();

        if (! $assignment) {
            $model::query()->create(['order_id' => $orderId, 'user_id' => $userId]);
        }
    }

    private function notify(array $result): void
    {
        if ($result['notifications_suppressed']) {
            Log::info('Admin artist notifications suppressed during Filament UAT.', [
                'order_id' => $result['order_id'],
                'painter_changed' => $result['painter_changed'],
                'show_enabled' => $result['show_enabled'],
            ]);

            return;
        }

        if ($result['painter_changed'] && $result['painter_id']) {
            $this->notifyPainter((int) $result['order_id'], (int) $result['painter_id']);
        }

        if ($result['show_enabled']) {
            $this->notifyCustomer((int) $result['order_id']);
        }
    }

    private function notifyPainter(int $orderId, int $painterId): void
    {
        $painter = User::query()->find($painterId);
        $message = $this->messageFor($painter);
        if (! $painter || ! filter_var($painter->email, FILTER_VALIDATE_EMAIL) || ! $message) {
            Log::warning('Painter assignment email skipped: recipient or translated text is missing.', compact('orderId', 'painterId'));

            return;
        }

        $this->mail->attempt(
            fn () => Mail::html(trim((string) $message->new_painter_order_text).' '.$orderId, fn ($mail) => $mail
                ->to($painter->email)
                ->subject((string) $message->new_painter_order)),
            'admin_artist_assignment',
            ['order_id' => $orderId, 'painter_id' => $painterId],
        );
    }

    private function notifyCustomer(int $orderId): void
    {
        $order = Orders::query()->with('user')->find($orderId);
        $customer = $order?->user;
        $message = $this->messageFor($customer);
        if (! $customer || ! filter_var($customer->email, FILTER_VALIDATE_EMAIL) || ! $message) {
            Log::warning('Painter images email skipped: recipient or translated text is missing.', compact('orderId'));

            return;
        }

        $data = ['to' => $customer->email, 'subject' => $message->new_photo_subject, 'content' => $message->new_photo_text];
        $this->mail->send(
            $customer->email,
            new SendPrainterToUserPicture($data, $customer->preferredLocale() ?: 'ru'),
            'admin_artist_images_visible',
            ['order_id' => $orderId, 'user_id' => $customer->id],
        );
    }

    private function messageFor(?User $user): mixed
    {
        if (! $user) {
            return null;
        }

        try {
            return UserMessage::query()->get()->translate($user->preferredLocale() ?: 'ru', 'ru')->first();
        } catch (Throwable $exception) {
            Log::warning('Artist notification texts could not be loaded.', [
                'user_id' => $user->id,
                'exception' => $exception,
            ]);

            return null;
        }
    }
}
