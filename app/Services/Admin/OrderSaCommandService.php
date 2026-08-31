<?php

namespace App\Services\Admin;

use App\Models\Orders;
use App\Models\SaBotControl;
use App\Models\SaConversation;
use App\Models\SaEvent;
use App\Models\SaMessage;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderSaCommandService
{
    public function __construct(private readonly OrderSaTransport $transport) {}

    public function token(Orders $order, User $author, int $conversationId): string
    {
        Gate::forUser($author)->authorize('update', $order);
        $conversation = SaConversation::query()->findOrFail($conversationId);
        abort_unless((int) $conversation->orders_id === (int) $order->id, 403);

        return Crypt::encryptString(json_encode([
            'event_id' => (string) Str::uuid(), 'order_id' => (int) $order->id, 'author_id' => (int) $author->id,
            'conversation_id' => (int) $conversation->id, 'external_id' => $conversation->conversation_id,
            'phone' => $conversation->client_phone, 'mode' => $conversation->bot_mode,
            'expires' => now()->addHour()->timestamp,
        ], JSON_THROW_ON_ERROR));
    }

    public function execute(Orders $order, User $author, array $input): array
    {
        Gate::forUser($author)->authorize('update', $order);
        if (is_string($input['text'] ?? null)) {
            $input['text'] = trim($input['text']);
        }
        $data = validator($input, [
            'token' => ['required', 'string', 'max:4096'],
            'action' => ['required', Rule::in(['send', 'pause_bot', 'resume_bot', 'handoff_to_manager'])],
            'text' => ['required_if:action,send', 'nullable', 'string', 'max:10000'],
            'handoff' => ['sometimes', 'boolean'],
        ])->validate();
        try {
            $token = json_decode(Crypt::decryptString($data['token']), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw ValidationException::withMessages(['token' => 'Откройте форму заново: токен команды недействителен.']);
        }
        abort_unless($token['order_id'] === (int) $order->id && $token['author_id'] === (int) $author->id, 403);
        $command = ['action' => $data['action'], 'text' => $data['action'] === 'send' ? $data['text'] : null,
            'handoff' => $data['action'] === 'send' && (bool) ($data['handoff'] ?? false)];
        $fingerprint = hash('sha256', json_encode($command, JSON_THROW_ON_ERROR));
        $prefix = 'filament-sa:'.$order->id.':'.$token['conversation_id'].':';
        $key = $prefix.$token['event_id'];
        $suppressed = ! config('admin_migration.sa_commands_enabled', false)
            || str_starts_with((string) $token['external_id'], 'ADM-FIL-UAT-');

        $prepared = DB::transaction(function () use ($order, $author, $token, $command, $fingerprint, $prefix, $key, $suppressed): array {
            Orders::query()->lockForUpdate()->findOrFail($order->id);
            $conversation = SaConversation::query()->lockForUpdate()->findOrFail($token['conversation_id']);
            abort_unless((int) $conversation->orders_id === (int) $order->id, 403);
            $existing = SaEvent::query()->where('dedupe_key', $key)->first();
            if ($existing) {
                $stored = json_decode($existing->payload, true);
                if (($stored['fingerprint'] ?? '') !== $fingerprint) {
                    throw ValidationException::withMessages(['token' => 'Эта команда уже использована с другим содержимым. Откройте новую форму.']);
                }

                return ['duplicate' => true, 'status' => $existing->status, 'event_id' => $existing->event_id];
            }
            if ($token['expires'] < now()->timestamp || $conversation->conversation_id !== $token['external_id']
                || $conversation->client_phone !== $token['phone'] || $conversation->bot_mode !== $token['mode']) {
                throw ValidationException::withMessages(['token' => 'Диалог изменился или форма устарела. Откройте её заново.']);
            }
            if ($conversation->channel !== 'whatsapp' || trim((string) $conversation->conversation_id) === ''
                || ! preg_match('/^\+?[1-9]\d{6,14}$/', (string) $conversation->client_phone)) {
                throw ValidationException::withMessages(['token' => 'Нужен связанный WhatsApp-диалог с корректным телефоном.']);
            }
            if (SaEvent::query()->where('dedupe_key', 'like', $prefix.'%')->where('status', 'dispatching')->exists()) {
                throw ValidationException::withMessages(['token' => 'Предыдущая команда ещё обрабатывается. Проверьте её результат перед новой отправкой.']);
            }
            $mode = match ($command['action']) {
                'pause_bot' => 'paused', 'resume_bot' => 'active', 'handoff_to_manager' => 'handoff_to_manager',
                default => $command['handoff'] ? 'handoff_to_manager' : null,
            };
            $state = $suppressed ? 'uat_suppressed' : 'dispatching';
            $payload = ['fingerprint' => $fingerprint, 'command' => $command, 'conversation_row_id' => $conversation->id,
                'mode' => $mode, 'uat' => $suppressed];
            $event = SaEvent::query()->create(['dedupe_key' => $key, 'event_id' => $token['event_id'], 'idempotency_key' => $key,
                'event_type' => $command['action'] === 'send' ? 'crm.message.send' : 'crm.bot_control', 'source' => 'CRM',
                'status' => $state, 'payload' => json_encode($payload, JSON_THROW_ON_ERROR), 'processed_at' => $suppressed ? now() : null]);
            $messageId = 'OUT-'.substr(sha1($key), 0, 16);
            if ($command['action'] === 'send') {
                SaMessage::query()->create(['orders_id' => $order->id, 'conversation_id' => $conversation->conversation_id,
                    'message_id' => $messageId, 'event_id' => $event->event_id, 'direction' => 'outbound',
                    'status' => $suppressed ? 'uat_suppressed' : 'pending', 'text' => $command['text'],
                    'from_json' => json_encode(['type' => 'manager', 'id' => (string) $author->id]),
                    'to_json' => json_encode(['phone' => $conversation->client_phone]),
                    'provider_meta_json' => json_encode(['uat' => $suppressed, 'event_id' => $event->event_id])]);
            }
            $botControlId = null;
            if ($mode !== null) {
                $botControlId = SaBotControl::query()->create(['orders_id' => $order->id, 'conversation_id' => $conversation->conversation_id,
                    'action' => $command['action'] === 'send' ? 'handoff_to_manager' : $command['action'], 'mode_after' => $mode,
                    'changed_by_json' => json_encode(['type' => 'manager', 'id' => (string) $author->id]),
                    'payload' => json_encode(['event_id' => $event->event_id, 'status' => $state])])->id;
            }

            return ['duplicate' => false, 'status' => $state, 'event_id' => $event->event_id, 'message_id' => $messageId,
                'conversation' => $conversation, 'mode' => $mode, 'bot_control_id' => $botControlId];
        });
        if ($prepared['duplicate'] || $suppressed) {
            return $prepared;
        }

        $context = ['order_id' => (int) $order->id, 'lead_id' => (int) $order->id,
            'conversation_id' => $token['external_id'], 'manager_id' => (string) $author->id,
            'event_id' => $token['event_id'], 'idempotency_key' => $key, 'message_id' => $prepared['message_id'],
            'trigger' => 'filament_sa_command', 'channel' => 'whatsapp'];
        $messageAccepted = false;
        $botAccepted = false;
        try {
            if ($command['action'] === 'send') {
                $messageAccepted = $this->transport->dispatch('message', $token['phone'], $command['text'], $context);
            }
            if ($prepared['mode'] !== null && ($command['action'] !== 'send' || $messageAccepted)) {
                $context['idempotency_key'] .= ':bot';
                $botAccepted = $this->transport->dispatch('bot', $token['phone'], $prepared['mode'], $context);
            }
        } catch (Throwable $exception) {
            Log::warning('SA command outcome uncertain; no automatic resend.', ['event_id' => $token['event_id'], 'exception_type' => $exception::class]);
        }
        $status = $command['action'] === 'send'
            ? ($messageAccepted ? ($prepared['mode'] !== null && ! $botAccepted ? 'partial' : 'accepted') : 'uncertain')
            : ($botAccepted ? 'accepted' : 'uncertain');

        DB::transaction(function () use ($order, $author, $token, $key, $command, $prepared, $messageAccepted, $botAccepted, &$status): void {
            $lockedOrder = Orders::query()->lockForUpdate()->findOrFail($order->id);
            $conversation = SaConversation::query()->lockForUpdate()->findOrFail($token['conversation_id']);
            $stillLinked = (int) $conversation->orders_id === (int) $order->id
                && $conversation->conversation_id === $token['external_id'] && $conversation->client_phone === $token['phone'];
            if ($command['action'] === 'send') {
                // Do not overwrite a delivery callback that arrived during dispatch.
                SaMessage::query()->where('message_id', $prepared['message_id'])->where('status', 'pending')
                    ->update(['status' => $messageAccepted ? 'queued' : 'delivery_unknown']);
                if ($messageAccepted && $stillLinked && ! DB::table('order_user_comments')
                    ->where('order_id', $order->id)->where('sa_message_id', $prepared['message_id'])->exists()) {
                    DB::table('order_user_comments')->insert([
                        'order_id' => $order->id, 'user_id' => $author->id, 'comment' => $command['text'],
                        'is_admin' => 1, 'is_read' => 0, 'admin_is_read' => 1, 'sa_message_id' => $prepared['message_id'],
                        'sa_direction' => 'outbound', 'is_img_sketch' => 0, 'is_img_painter' => 0,
                        'order_painter_image_id' => 0, 'order_user_image_id' => 0, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
            if ($botAccepted) {
                if ($stillLinked && $conversation->bot_mode === $token['mode']) {
                    $conversation->update(['bot_mode' => $prepared['mode']]);
                    if (! $lockedOrder->sa_conversation_id || $lockedOrder->sa_conversation_id === $token['external_id']) {
                        $lockedOrder->forceFill(['sa_conversation_id' => $token['external_id'], 'sa_client_phone' => $token['phone'],
                            'sa_bot_mode' => $prepared['mode']])->save();
                    }
                } else {
                    $status = 'state_conflict';
                }
            }
            if (! $stillLinked) {
                $status = 'state_conflict';
            }
            SaEvent::query()->where('dedupe_key', $key)->update(['status' => $status, 'processed_at' => now()]);
            if ($prepared['bot_control_id']) {
                SaBotControl::query()->whereKey($prepared['bot_control_id'])->update([
                    'payload' => json_encode(['event_id' => $token['event_id'], 'status' => $status, 'bot_accepted' => $botAccepted]),
                ]);
            }
        });
        Log::info('SA command recorded.', ['event_id' => $token['event_id'], 'order_id' => $order->id, 'status' => $status]);

        return ['duplicate' => false, 'status' => $status, 'event_id' => $token['event_id']];
    }
}
