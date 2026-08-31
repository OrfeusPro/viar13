<?php

namespace App\Services\Admin;

use App\Mail\AdminToPainterComment;
use App\Models\Orders;
use App\Models\OrdersChats;
use App\Models\User;
use App\Models\UserMessage;
use App\Services\BestEffortMailService;
use App\Services\SynvolveWebhookService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderPainterChatService
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
            'image_type' => ['prohibited'],
            'image_id' => ['prohibited'],
            'order_painter_image_id' => ['prohibited'],
        ])->validate();

        [$message, $painter] = DB::transaction(function () use ($order, $data): array {
            $lockedOrder = Orders::query()->lockForUpdate()->findOrFail($order->id);
            // Resolve the current assignment at save time, never from browser input or a stale relation.
            $assignment = $lockedOrder->painterAssignment()->lockForUpdate()->first();
            $painter = $assignment?->user;
            $message = (new OrdersChats)->forceFill([
                'orders_id' => $lockedOrder->id,
                'comment' => $data['comment'],
                'is_admin' => 1,
                'is_read' => 0,
                'admin_is_read' => 1,
                'is_img_sketch' => 0,
                'is_img_painter' => 0,
            ]);
            $message->save();

            return [$message, $painter];
        });

        $notificationsSuppressed = ! config('admin_migration.painter_chat_notifications_enabled', false);
        $mailSent = false;
        $webhookSent = false;
        if ($notificationsSuppressed) {
            Log::info('Admin painter chat notifications suppressed during Filament UAT.', [
                'order_id' => $order->id, 'message_id' => $message->id, 'author_id' => $author->id,
            ]);
        } else {
            if ($painter && filter_var($painter->email, FILTER_VALIDATE_EMAIL)) {
                try {
                    $locale = $painter->preferredLocale() ?: 'ru';
                    $texts = UserMessage::query()->get()->translate($locale, 'ru')[0] ?? [];
                    $template = trim((string) ($texts['admin_user_chat_title'] ?? ''));
                    $subject = $template !== '' ? str_replace('{order_id}', (string) $order->id, $template) : 'Сообщение по заказу #'.$order->id;
                    $mailSent = $this->mail->send($painter->email, (new AdminToPainterComment($message->comment, $subject))->locale($locale), 'admin_painter_chat', [
                        'order_id' => $order->id, 'message_id' => $message->id, 'painter_id' => $painter->id,
                    ]);
                } catch (Throwable $exception) {
                    Log::error('Admin painter chat email could not be prepared.', [
                        'order_id' => $order->id, 'message_id' => $message->id, 'exception_type' => $exception::class,
                    ]);
                }
            }
            try {
                $webhookSent = $this->webhook->notifyManagerMessageForOrder($order->id, (string) $message->comment, [
                    'trigger' => 'filament_admin_orders_chat', 'source' => 'orders_chats',
                    'message_id' => $message->id, 'image_type' => null,
                ]);
            } catch (Throwable $exception) {
                Log::warning('Admin painter chat webhook failed after message persistence.', [
                    'order_id' => $order->id, 'message_id' => $message->id, 'exception_type' => $exception::class,
                ]);
            }
        }

        return compact('message', 'notificationsSuppressed', 'mailSent', 'webhookSent');
    }

    public function markMessageAsRead(Orders $order, User $author, int $messageId): int
    {
        Gate::forUser($author)->authorize('update', $order);

        return DB::transaction(function () use ($order, $messageId): int {
            $message = OrdersChats::query()->lockForUpdate()->findOrFail($messageId);
            abort_unless((int) $message->orders_id === (int) $order->id, 403);
            if ($message->is_admin || $message->admin_is_read) {
                return 0;
            }

            // The painter's read state and historical message timestamps must not change.
            return DB::table('orders_chats')->where('id', $message->id)->update(['admin_is_read' => 1]);
        });
    }
}
