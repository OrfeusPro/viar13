<?php

namespace App\Services\Admin;

use App\Models\Orders;
use App\Models\SaConversation;
use App\Models\SaMessage;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderSaChatService
{
    public function readToken(Orders $order, SaConversation $conversation): string
    {
        return Crypt::encryptString(json_encode([
            'order_id' => (int) $order->id,
            'conversation_id' => (int) $conversation->id,
            'last_id' => (int) $order->saMessages->where('conversation_id', $conversation->conversation_id)->max('id'),
            'updated_at' => $conversation->getRawOriginal('updated_at'),
            'last_message_at' => $conversation->getRawOriginal('last_message_at'),
        ], JSON_THROW_ON_ERROR));
    }

    public function markAsRead(Orders $order, User $author, string $token): int
    {
        Gate::forUser($author)->authorize('update', $order);
        try {
            $snapshot = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException $exception) {
            throw ValidationException::withMessages(['snapshot' => 'Обновите историю чата перед отметкой прочтения.']);
        }
        abort_unless((int) ($snapshot['order_id'] ?? 0) === (int) $order->id, 403);

        return DB::transaction(function () use ($order, $author, $snapshot): int {
            $conversation = SaConversation::query()->lockForUpdate()->findOrFail($snapshot['conversation_id']);
            abort_unless((int) $conversation->orders_id === (int) $order->id, 403);
            if (! $conversation->unread_for_manager) {
                return 0;
            }
            $lastId = (int) SaMessage::query()->where('orders_id', $order->id)
                ->where('conversation_id', $conversation->conversation_id)->max('id');
            if ($lastId !== $snapshot['last_id']
                || $conversation->getRawOriginal('updated_at') !== $snapshot['updated_at']
                || $conversation->getRawOriginal('last_message_at') !== $snapshot['last_message_at']) {
                throw ValidationException::withMessages(['snapshot' => 'В диалоге появились изменения. Закройте и откройте историю, затем повторите прочтение.']);
            }

            // Manager acknowledgement is independent of WhatsApp delivery receipts and client chat flags.
            $changed = DB::table('sa_conversations')->where('id', $conversation->id)->update(['unread_for_manager' => 0]);
            Log::info('Admin acknowledged SA conversation.', [
                'order_id' => $order->id, 'conversation_row_id' => $conversation->id, 'author_id' => $author->id,
            ]);

            return $changed;
        });
    }
}
