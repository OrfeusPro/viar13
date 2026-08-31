<?php

namespace App\Services\Admin;

use App\Mail\AdminToUserComment;
use App\Models\Orders;
use App\Models\OrderUserComments;
use App\Models\User;
use App\Models\UserMessage;
use App\Services\BestEffortMailService;
use App\Services\SynvolveWebhookService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderClientChatService
{
    public function __construct(
        private readonly BestEffortMailService $mail,
        private readonly SynvolveWebhookService $webhook,
    ) {}

    public function send(Orders $order, User $author, array $input): array
    {
        Gate::forUser($author)->authorize('update', $order);
        if (is_string($input['comment'] ?? null)) {
            $input['comment'] = trim($input['comment']);
        }
        $data = validator($input, [
            'comment' => ['required', 'string', 'max:10000'],
            'thread_type' => ['required', Rule::in(['general', 'sketch', 'painter'])],
            'image_id' => ['nullable', 'integer', 'min:1'],
        ])->validate();

        $imageId = $data['image_id'] ?? null;
        if ($data['thread_type'] === 'general' && $imageId !== null) {
            throw ValidationException::withMessages(['image_id' => 'Общий ответ не должен содержать привязку к изображению.']);
        }

        $message = DB::transaction(function () use ($order, $author, $data, $imageId): OrderUserComments {
            $lockedOrder = Orders::query()->lockForUpdate()->findOrFail($order->id);
            if ($data['thread_type'] !== 'general') {
                if (in_array($lockedOrder->status, ['completed', 'sended', 'send_lubanas'], true)) {
                    throw ValidationException::withMessages(['thread_type' => 'Переписка по изображениям закрыта для отправленного или завершённого заказа.']);
                }
                $image = $lockedOrder->order_painter_images()->whereKey($imageId)->lockForUpdate()->first();
                $flag = $data['thread_type'] === 'sketch' ? 'is_img_sketch' : 'is_img_painter';
                if (! $image || ! $image->{$flag}) {
                    throw ValidationException::withMessages(['image_id' => 'Выберите изображение этого заказа и соответствующего типа переписки.']);
                }
            }

            $attributes = [
                'comment' => trim($data['comment']),
                'order_id' => $order->id,
                'user_id' => $author->id,
                'is_admin' => 1,
                'is_read' => 0,
                'admin_is_read' => 1,
                'is_img_sketch' => $data['thread_type'] === 'sketch' ? 1 : 0,
                'is_img_painter' => $data['thread_type'] === 'painter' ? 1 : 0,
                'order_painter_image_id' => $imageId ?: 0,
                'order_user_image_id' => 0,
            ];

            $message = (new OrderUserComments)->forceFill($attributes);
            $message->save();

            return $message;
        });

        $notificationsSuppressed = ! config('admin_migration.client_chat_notifications_enabled', false);
        $mailSent = false;
        $webhookSent = false;

        if ($notificationsSuppressed) {
            Log::info('Admin client chat notifications suppressed during Filament UAT.', [
                'order_id' => $order->id,
                'message_id' => $message->id,
                'author_id' => $author->id,
            ]);
        } else {
            [$mailSent, $webhookSent] = $this->notify($order, $message);
        }

        return compact('message', 'notificationsSuppressed', 'mailSent', 'webhookSent');
    }

    public function markMessageAsRead(Orders $order, User $author, int $messageId): int
    {
        Gate::forUser($author)->authorize('update', $order);

        return DB::transaction(function () use ($order, $messageId): int {
            $message = OrderUserComments::query()->lockForUpdate()->findOrFail($messageId);
            abort_unless((int) $message->order_id === (int) $order->id, 403);
            if ($message->is_admin || $message->admin_is_read) {
                return 0;
            }

            // Preserve the message timestamps and the client's independent is_read flag.
            return DB::table('order_user_comments')->where('id', $message->id)->update(['admin_is_read' => 1]);
        });
    }

    private function notify(Orders $order, OrderUserComments $message): array
    {
        $user = $order->user;
        $mailSent = false;

        if ($user && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            try {
                $locale = $user->preferredLocale() ?: 'ru';
                $messages = UserMessage::query()->get()->translate($locale, 'ru');
                $texts = $messages[0] ?? [];
                $subject = trim((string) ($texts['user_painter_mail_subject'] ?? 'Сообщение по заказу'));
                $mailSent = $this->mail->send($user->email, new AdminToUserComment([
                    'subject' => $subject.' '.$order->id,
                    'text' => nl2br(e($message->comment)),
                    'order_id' => $order->id,
                    'comment_images' => null,
                    'user_email' => $user->email,
                ], $locale), 'admin_client_chat', [
                    'order_id' => $order->id,
                    'message_id' => $message->id,
                    'user_id' => $user->id,
                ]);
            } catch (Throwable $exception) {
                Log::error('Admin client chat email could not be prepared.', [
                    'order_id' => $order->id,
                    'message_id' => $message->id,
                    'exception_type' => $exception::class,
                ]);
            }
        }

        try {
            $webhookSent = $this->webhook->notifyManagerMessageForOrder($order->id, (string) $message->comment, [
                'trigger' => 'filament_admin_order_chat',
                'source' => 'order_user_comments',
                'message_id' => $message->id,
                'has_attachments' => false,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Admin client chat webhook failed after message persistence.', [
                'order_id' => $order->id,
                'message_id' => $message->id,
                'exception_type' => $exception::class,
            ]);
            $webhookSent = false;
        }

        return [$mailSent, $webhookSent];
    }
}
