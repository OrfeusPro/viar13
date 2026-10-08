<?php

namespace App\Services\Admin;

use App\Http\Controllers\Api\SaIntegrationController;
use App\Models\Orders;
use App\Models\SaConversation;
use App\Models\SaMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class SaInboxService
{
    public static function authorize(User $user, string $ability = 'read'): void
    {
        abort_unless($user->hasPermission('browse_admin') && $user->hasPermission('browse_orders')
            && $user->hasPermission('read_orders')
            && $user->hasPermission($ability === 'edit' ? 'edit_orders' : 'read_orders'), 403);
    }

    public function readToken(SaConversation $conversation, ?int $displayedLastId = null): string
    {
        return Crypt::encryptString(json_encode(['id' => $conversation->id,
            'last_id' => $displayedLastId ?? (int) SaMessage::where('conversation_id', $conversation->conversation_id)->max('id'),
            'updated_at' => $conversation->getRawOriginal('updated_at'),
            'last_message_at' => $conversation->getRawOriginal('last_message_at')], JSON_THROW_ON_ERROR));
    }

    public function acknowledge(SaConversation $conversation, User $user, string $token): void
    {
        static::authorize($user, 'edit');
        try {
            $snapshot = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw ValidationException::withMessages(['snapshot' => 'Обновите историю перед прочтением.']);
        }
        abort_unless((int) ($snapshot['id'] ?? 0) === (int) $conversation->id, 403);
        DB::transaction(function () use ($conversation, $snapshot, $user): void {
            $locked = SaConversation::lockForUpdate()->findOrFail($conversation->id);
            if ((int) SaMessage::where('conversation_id', $locked->conversation_id)->max('id') !== $snapshot['last_id']
                || $locked->getRawOriginal('updated_at') !== $snapshot['updated_at']
                || $locked->getRawOriginal('last_message_at') !== $snapshot['last_message_at']) {
                throw ValidationException::withMessages(['snapshot' => 'В диалоге появились изменения. Обновите историю.']);
            }
            DB::table('sa_conversations')->where('id', $locked->id)->update(['unread_for_manager' => 0]);
            Log::info('SA inbox acknowledged.', ['conversation_row_id' => $locked->id, 'author_id' => $user->id]);
        });
    }

    public function bind(SaConversation $conversation, User $user, int $orderId): Orders
    {
        static::authorize($user, 'edit');
        validator(['order_id' => $orderId], ['order_id' => 'required|integer|min:1|exists:orders,id'])->validate();
        return DB::transaction(function () use ($conversation, $user, $orderId): Orders {
            // Same lock order as the existing order command service.
            $order = Orders::lockForUpdate()->findOrFail($orderId);
            Gate::forUser($user)->authorize('update', $order);
            $locked = SaConversation::lockForUpdate()->findOrFail($conversation->id);
            if (($locked->orders_id && (int) $locked->orders_id !== $orderId)
                || ($order->sa_conversation_id && $order->sa_conversation_id !== $locked->conversation_id)
                || SaMessage::where('conversation_id', $locked->conversation_id)->whereNotNull('orders_id')->where('orders_id', '<>', $orderId)->exists()) {
                throw ValidationException::withMessages(['order_id' => 'Диалог или заказ уже имеет другую привязку.']);
            }
            $messages = SaMessage::where('conversation_id', $locked->conversation_id)->orderBy('id')->get();
            SaMessage::where('conversation_id', $locked->conversation_id)->whereNull('orders_id')->update(['orders_id' => $orderId]);
            foreach (['sa_escalations', 'sa_bot_controls'] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->where('conversation_id', $locked->conversation_id)->whereNull('orders_id')
                        ->update(['orders_id' => $orderId, 'updated_at' => now()]);
                }
            }
            foreach ($messages as $message) {
                if (! filled($message->text) || ! filled($message->message_id)
                    || in_array($message->status, ['uat_suppressed', 'pending', 'delivery_unknown', 'failed'], true)
                    || DB::table('order_user_comments')->where('order_id', $orderId)->where('sa_message_id', $message->message_id)->exists()) {
                    continue;
                }
                $outbound = $message->direction === 'outbound';
                $sender = (int) data_get(json_decode((string) $message->from_json, true), 'id', $user->id);
                if ($outbound && ! User::whereKey($sender)->exists()) { $sender = $user->id; }
                DB::table('order_user_comments')->insert(['order_id' => $orderId,
                    'user_id' => $outbound ? $sender : $order->user_id,
                    'comment' => $message->text, 'is_admin' => (int) $outbound,
                    'is_read' => (int) ! $outbound, 'admin_is_read' => (int) $outbound,
                    'sa_message_id' => $message->message_id, 'sa_direction' => $message->direction,
                    'is_img_sketch' => 0, 'is_img_painter' => 0, 'order_painter_image_id' => 0, 'order_user_image_id' => 0,
                    'created_at' => $message->sent_at ?? $message->created_at ?? now(), 'updated_at' => now()]);
            }
            $order->forceFill(['sa_conversation_id' => $locked->conversation_id,
                'sa_client_phone' => $locked->client_phone, 'sa_bot_mode' => $locked->bot_mode])->save();
            // Binding does not acknowledge messages the manager has not explicitly read.
            $locked->update(['orders_id' => $orderId]);
            Log::info('SA inbox bound to order.', ['conversation_row_id' => $locked->id, 'order_id' => $orderId, 'author_id' => $user->id]);
            return $order;
        });
    }

    public function createOrder(SaConversation $conversation, User $user): Orders
    {
        static::authorize($user, 'edit');
        Gate::forUser($user)->authorize('create', Orders::class);
        return DB::transaction(function () use ($conversation, $user): Orders {
            $locked = SaConversation::lockForUpdate()->findOrFail($conversation->id);
            if ($locked->orders_id) { return Orders::findOrFail($locked->orders_id); }
            $request = Request::create('/api/sa/leads', 'POST', [
                'idempotency_key' => 'filament-inbox-create:'.$locked->id, 'source' => 'SA',
                'lead' => ['client' => ['phone' => $locked->client_phone, 'name' => $locked->client_name ?: 'Client'],
                    'channel' => $locked->channel ?: 'whatsapp'],
            ]);
            // Internal business call: no loopback HTTP, credentials, or new integration endpoint.
            $response = app(SaIntegrationController::class)->createLead($request);
            $id = data_get($response->getData(true), 'data.lead_id');
            if (! $response->isSuccessful() || ! is_numeric($id) || ! Orders::whereKey($id)->exists()) {
                throw ValidationException::withMessages(['conversation' => 'Не удалось создать заказ. Проверьте телефон и канал диалога.']);
            }
            $order = $this->bind($locked, $user, (int) $id);
            Log::info('SA inbox created order.', ['conversation_row_id' => $locked->id, 'order_id' => $order->id, 'author_id' => $user->id]);
            return $order;
        });
    }
}
