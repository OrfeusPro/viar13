<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\SaConversation;
use App\Models\SaMessage;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\SynvolveWebhookService;

class AdminSaIntegrationController extends Controller
{
    public function conversationsIndex(Request $request)
    {
        abort_unless(Schema::hasTable('sa_conversations'), 404);

        $conversations = $this->buildConversationsListQuery($request)
            ->orderByRaw('COALESCE(sa_conversations.last_message_at, sa_conversations.updated_at, sa_conversations.created_at) desc')
            ->paginate(30)
            ->appends($request->query());

        if ($request->boolean('fragment')) {
            return view('admin.partials.sa_conversations_table', [
                'conversations' => $conversations,
            ]);
        }

        return view('admin.sa_conversations_index', [
            'conversations' => $conversations,
            'scope' => (string) $request->input('scope', ''),
            'q' => (string) $request->input('q', ''),
        ]);
    }

    public function conversationsUnreadState(): \Illuminate\Http\JsonResponse
    {
        abort_unless(Schema::hasTable('sa_conversations'), 404);

        $baseQuery = SaConversation::query();
        $unreadCount = 0;
        if (Schema::hasColumn('sa_conversations', 'unread_for_manager')) {
            $unreadCount = (int) (clone $baseQuery)
                ->where('unread_for_manager', 1)
                ->count();
        }

        $latestMessageAt = (clone $baseQuery)
            ->selectRaw('MAX(COALESCE(last_message_at, updated_at, created_at)) as last_seen_at')
            ->value('last_seen_at');

        $latestUnreadConversationId = null;
        if (Schema::hasColumn('sa_conversations', 'unread_for_manager')) {
            $latestUnreadConversationId = (clone $baseQuery)
                ->where('unread_for_manager', 1)
                ->orderByRaw('COALESCE(last_message_at, updated_at, created_at) desc')
                ->value('conversation_id');
        }

        return response()->json([
            'status' => 'ok',
            'data' => [
                'unread_count' => $unreadCount,
                'latest_message_at' => $latestMessageAt ? Carbon::parse($latestMessageAt)->toIso8601String() : null,
                'latest_unread_conversation_id' => $latestUnreadConversationId,
            ],
        ]);
    }

    public function conversationShow(string $conversationId)
    {
        abort_unless(Schema::hasTable('sa_conversations'), 404);

        $conversation = SaConversation::query()
            ->where('conversation_id', $conversationId)
            ->firstOrFail();

        if (Schema::hasColumn('sa_conversations', 'unread_for_manager') && (int) $conversation->unread_for_manager === 1) {
            $conversation->unread_for_manager = 0;
            $conversation->save();
        }

        $messages = Schema::hasTable('sa_messages')
            ? SaMessage::query()
                ->where('conversation_id', $conversationId)
                ->orderByRaw('COALESCE(sent_at, created_at) asc')
                ->orderBy('id')
                ->get()
            : collect();

        $order = null;
        if ($conversation->orders_id) {
            $order = Orders::query()->find($conversation->orders_id);
        }

        return view('admin.sa_conversation_show', [
            'conversation' => $conversation,
            'messages' => $messages,
            'order' => $order,
        ]);
    }

    public function conversationSendMessage(Request $request, string $conversationId)
    {
        $request->validate([
            'text' => 'required|string',
            'handoff' => 'nullable|boolean',
        ]);

        $conversation = SaConversation::query()
            ->where('conversation_id', $conversationId)
            ->firstOrFail();

        $payload = [
            'event_id' => (string) Str::uuid(),
            'event_type' => 'crm.message.send',
            'idempotency_key' => 'crm.message.send:conversation:' . $conversationId . ':' . now()->timestamp . rand(100, 999),
            'occurred_at' => now()->toIso8601String(),
            'source' => 'CRM',
            'data' => [
                'conversation_id' => $conversation->conversation_id,
                'channel' => $conversation->channel ?: 'whatsapp',
                'client' => [
                    'phone' => (string) ($conversation->client_phone ?: '+0000000'),
                    'name' => (string) ($conversation->client_name ?: 'Client'),
                ],
                'manager' => [
                    'id' => (string) (auth()->id() ?? 1),
                    'name' => auth()->check() ? auth()->user()->name : 'Admin',
                ],
                'message' => [
                    'client_visible_sender' => 'manager',
                    'text' => (string) $request->input('text'),
                ],
                'bot_control' => [
                    'mode_after_send' => $request->boolean('handoff') ? 'handoff_to_manager' : 'active',
                ],
            ],
        ];

        if (!empty($conversation->orders_id)) {
            $payload['data']['lead_id'] = (string) $conversation->orders_id;
        }

        $response = $this->dispatchInternalWebhook('/api/crm/webhooks/send-message', $payload, 'POST');
        if (($response['http_code'] ?? 500) >= 200 && ($response['http_code'] ?? 500) < 300) {
            $this->markConversationAsRead($conversationId);

            return redirect()
                ->route('admin.sa.conversations.show', ['conversation' => $conversationId])
                ->with('success', 'Сообщение отправлено.');
        }

        return redirect()
            ->route('admin.sa.conversations.show', ['conversation' => $conversationId])
            ->with('error', 'Не удалось отправить сообщение: ' . json_encode($response['api_response'] ?? $response));
    }

    public function conversationCreateOrder(Request $request, string $conversationId)
    {
        $conversation = SaConversation::query()
            ->where('conversation_id', $conversationId)
            ->firstOrFail();

        if (!empty($conversation->orders_id)) {
            return redirect()
                ->route('edit_admin_order', ['id' => $conversation->orders_id])
                ->with('success', 'Диалог уже привязан к заказу.');
        }

        $payload = [
            'idempotency_key' => 'create_lead:conversation:' . $conversationId . ':' . now()->timestamp . rand(100, 999),
            'source' => 'SA',
            'lead' => [
                'external_ids' => [
                    'conversation_id' => $conversation->conversation_id,
                ],
                'client' => [
                    'phone' => (string) $conversation->client_phone,
                    'name' => (string) ($conversation->client_name ?: 'Client'),
                ],
                'channel' => $conversation->channel ?: 'whatsapp',
            ],
        ];

        $response = $this->dispatchInternalWebhook('/api/sa/leads', $payload, 'POST');
        $httpCode = (int) ($response['http_code'] ?? 500);
        $leadId = (string) data_get($response, 'api_response.data.lead_id', '');

        if ($httpCode >= 200 && $httpCode < 300 && $leadId !== '') {
            $this->markConversationAsRead($conversationId);

            return redirect()
                ->route('edit_admin_order', ['id' => $leadId])
                ->with('success', 'Заказ создан из диалога и сообщения привязаны.');
        }

        return redirect()
            ->route('admin.sa.conversations.show', ['conversation' => $conversationId])
            ->with('error', 'Не удалось создать заказ из диалога: ' . json_encode($response['api_response'] ?? $response));
    }

    public function conversationBindOrder(Request $request, string $conversationId)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $conversation = SaConversation::query()
            ->where('conversation_id', $conversationId)
            ->firstOrFail();

        $orderId = (int) $request->input('order_id');

        if (!empty($conversation->orders_id) && (int) $conversation->orders_id !== $orderId) {
            return redirect()
                ->route('admin.sa.conversations.show', ['conversation' => $conversationId])
                ->with('error', 'Диалог уже привязан к другому заказу. Сначала нужно решить конфликт привязки.');
        }

        $order = Orders::query()->findOrFail($orderId);

        $this->bindConversationToOrderAdmin($conversation, $order);
        $this->markConversationAsRead($conversationId);

        return redirect()
            ->route('admin.sa.conversations.show', ['conversation' => $conversationId])
            ->with('success', 'Диалог привязан к заказу #' . $orderId . '. Сообщения синхронизированы.');
    }

    /**
     * Handle bot control actions from the admin panel
     */
    public function botControl(Request $request)
    {
        $request->validate([
            'order_id' => 'nullable|required_without:conversation_id|integer',
            'conversation_id' => 'nullable|required_without:order_id|string|max:255',
            'action' => 'required|string|in:pause_bot,resume_bot,handoff_to_manager',
        ]);

        $order = null;
        if ($request->filled('order_id')) {
            $order = Orders::find((int) $request->input('order_id'));
            if (!$order) {
                return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
            }
        }

        $conversationId = (string) $request->input('conversation_id', '');
        if ($conversationId === '' && $order) {
            $conversationId = (string) ($order->sa_conversation_id ?? ('CONV-' . $order->id));
        }

        if (!$order && $conversationId !== '' && Schema::hasTable('sa_conversations')) {
            $conversation = SaConversation::query()
                ->where('conversation_id', $conversationId)
                ->first();

            if (!$conversation) {
                return response()->json(['status' => 'error', 'message' => 'Conversation not found'], 404);
            }

            if (!empty($conversation->orders_id)) {
                $order = Orders::find((int) $conversation->orders_id);
            }
        }

        // We simulate sending a webhook to our own SA API since we know the secret key 
        // Or we can just build the request directly if they share the same backend.
        // It's cleaner to hit the webhook endpoint to ensure all logging and DB updates run exactly the same.
        
        $payload = [
            'event_id' => (string) \Illuminate\Support\Str::uuid(),
            'event_type' => 'crm.bot_control',
            'idempotency_key' => 'crm.bot_control:admin:' . time() . rand(100, 999),
            'occurred_at' => now()->toIso8601String(),
            'source' => 'CRM',
            'data' => [
                'action' => $request->action,
                'changed_by' => [
                    'type' => 'manager',
                    'id' => (string) (auth()->id() ?? 1),
                    'name' => auth()->check() ? auth()->user()->name : 'Admin'
                ]
            ]
        ];

        if ($order) {
            $payload['data']['lead_id'] = (string) $order->id;
        }
        if ($conversationId !== '') {
            $payload['data']['conversation_id'] = $conversationId;
        }

        try {
            $response = $this->dispatchInternalWebhook('/api/crm/webhooks/bot-control', $payload, 'POST');
            if (($response['http_code'] ?? 500) >= 200 && ($response['http_code'] ?? 500) < 300) {
                $botStatus = data_get($response, 'api_response.data.bot_mode');
                if ($botStatus && $order) {
                    app(SynvolveWebhookService::class)->notifyBotStatusForOrder($order, (string) $botStatus, [
                        'trigger' => 'admin_bot_control',
                        'action' => (string) $request->action,
                        'conversation_id' => (string) data_get($payload, 'data.conversation_id'),
                        'manager_id' => (string) data_get($payload, 'data.changed_by.id'),
                    ]);
                }

                return response()->json(['status' => 'ok', 'data' => $response['api_response']]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Webhook returned error: ' . json_encode($response['api_response'] ?? $response)], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Send WhatsApp message from the admin panel to SA
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'text' => 'required|string',
            'handoff' => 'boolean'
        ]);

        $order = Orders::find($request->order_id);
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        $payload = [
            'event_id' => (string) \Illuminate\Support\Str::uuid(),
            'event_type' => 'crm.message.send',
            'idempotency_key' => 'crm.message.send:admin:' . time() . rand(100, 999),
            'occurred_at' => now()->toIso8601String(),
            'source' => 'CRM',
            'data' => [
                'lead_id' => (string)$order->id,
                'conversation_id' => $order->sa_conversation_id ?? ('CONV-'.$order->id),
                'channel' => 'whatsapp',
                'client' => [
                    'phone' => (string) ($order->sa_client_phone ?? '+0000000')
                ],
                'manager' => [
                    'id' => (string) (auth()->id() ?? 1),
                    'name' => auth()->check() ? auth()->user()->name : 'Admin'
                ],
                'message' => [
                    'client_visible_sender' => 'manager',
                    'text' => $request->text,
                ],
                'bot_control' => [
                    'mode_after_send' => $request->handoff ? 'handoff_to_manager' : 'active'
                ]
            ]
        ];

        try {
            $response = $this->dispatchInternalWebhook('/api/crm/webhooks/send-message', $payload, 'POST');
            if (($response['http_code'] ?? 500) >= 200 && ($response['http_code'] ?? 500) < 300) {
                return response()->json(['status' => 'ok', 'data' => $response['api_response']]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Failed to send message: ' . json_encode($response['api_response'] ?? $response)], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Poll latest messages from the DB
     */
    public function getMessages(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'last_id'  => 'required|integer'
        ]);

        $order = Orders::find($request->order_id);
        if (!$order) {
            return response()->json(['status' => 'error'], 404);
        }

        // Fetch new messages that came after the last known id
        $messages = \App\Models\SaMessage::where('orders_id', $order->id)
            ->where('id', '>', $request->last_id)
            ->orderBy('sent_at', 'asc')
            ->get();

        return response()->json([
            'status'   => 'ok',
            'bot_mode' => $order->sa_bot_mode ?? 'n/a',
            'messages' => $messages
        ]);
    }

    /**
     * Simulator: Render the UI for SA Webhooks execution
     */
    public function simulator()
    {
        $defaults = $this->resolveSimulatorDefaults();
        $unreadConversationsCount = 0;

        if (Schema::hasTable('sa_conversations') && Schema::hasColumn('sa_conversations', 'unread_for_manager')) {
            $unreadConversationsCount = (int) SaConversation::query()
                ->where('unread_for_manager', 1)
                ->count();
        }

        return view('admin.sa_simulator', [
            'simulatorDefaults' => $defaults,
            'unreadConversationsCount' => $unreadConversationsCount,
        ]);
    }

    /**
     * Simulator: Execute internal webhook
     */
    public function simulatorTrigger(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
            'payload' => 'required|string'
        ]);

        try {
            $payloadArray = json_decode($request->payload, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid JSON payload: ' . json_last_error_msg(),
                ], 422);
            }
            $payloadArray = $payloadArray ?? [];
            
            // By default send POST, if lead_id is present and we're updating lead, use PATCH.
            // Services catalog is GET.
            $method = 'POST';
            if (
                strpos($request->endpoint, '/api/sa/services-catalog') !== false
                || strpos($request->endpoint, '/api/sa/catalog-full') !== false
                || strpos($request->endpoint, '/api/sa/orders/lookup') !== false
                || strpos($request->endpoint, '/api/blog/categories') !== false
                || strpos($request->endpoint, '/api/blog/authors?') === 0
                || strpos($request->endpoint, '/api/blog/posts?') === 0
            ) {
                $method = 'GET';
            }
            if (preg_match('#^/api/sa/services/(HM-\d+|GC-5|FC-1)/(sizes|price-by-size)#', $request->endpoint)) {
                $method = 'GET';
            }
            if (strpos($request->endpoint, '/api/sa/leads/') !== false && !Str::endsWith($request->endpoint, '/api/sa/leads')) {
                $method = 'PATCH';
            }
            if (preg_match('#^/api/blog/posts/\d+#', $request->endpoint)) {
                $method = 'PATCH';
            }

            $response = $this->dispatchInternalWebhook($request->endpoint, $payloadArray, $method);

            return response()->json([
                'status' => 'ok',
                'http_code' => $response['http_code'],
                'api_response' => $response['api_response']
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    protected function dispatchInternalWebhook(string $endpoint, array $payloadArray = [], string $method = 'POST'): array
    {
        $url = url($endpoint);
        $client = new Client(['verify' => false]);

        $requestOptions = [
            'headers' => [
                'X-Api-Key' => config('services.sa_integration.api_key', 'test-key')
            ],
            'http_errors' => false
        ];

        if (strtoupper($method) === 'GET') {
            if (!empty($payloadArray)) {
                $requestOptions['query'] = $payloadArray;
            }
        } else {
            $requestOptions['json'] = $payloadArray;
        }

        $response = $client->request(strtoupper($method), $url, $requestOptions);

        return [
            'http_code' => $response->getStatusCode(),
            'api_response' => json_decode((string) $response->getBody(), true) ?? (string) $response->getBody(),
        ];
    }

    private function bindConversationToOrderAdmin(SaConversation $conversation, Orders $order): void
    {
        $conversationId = (string) $conversation->conversation_id;
        $orderId = (int) $order->id;
        $clientPhone = (string) ($conversation->client_phone ?? '');
        $botMode = (string) ($conversation->bot_mode ?? '');

        $pendingMessages = collect();
        if (Schema::hasTable('sa_messages')) {
            $pendingMessages = SaMessage::query()
                ->where('conversation_id', $conversationId)
                ->whereNull('orders_id')
                ->orderByRaw('COALESCE(sent_at, created_at) asc')
                ->orderBy('id')
                ->get(['message_id', 'text', 'direction', 'sent_at']);

            SaMessage::query()
                ->where('conversation_id', $conversationId)
                ->whereNull('orders_id')
                ->update([
                    'orders_id' => $orderId,
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('sa_conversations')) {
            SaConversation::query()
                ->where('conversation_id', $conversationId)
                ->update([
                    'orders_id' => $orderId,
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('sa_escalations')) {
            DB::table('sa_escalations')
                ->where('conversation_id', $conversationId)
                ->whereNull('orders_id')
                ->update([
                    'orders_id' => $orderId,
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('sa_bot_controls')) {
            DB::table('sa_bot_controls')
                ->where('conversation_id', $conversationId)
                ->whereNull('orders_id')
                ->update([
                    'orders_id' => $orderId,
                    'updated_at' => now(),
                ]);
        }

        $orderUpdates = ['updated_at' => now()];
        if (Schema::hasColumn('orders', 'sa_conversation_id')) {
            $orderUpdates['sa_conversation_id'] = $conversationId;
        }
        if ($clientPhone !== '' && Schema::hasColumn('orders', 'sa_client_phone')) {
            $orderUpdates['sa_client_phone'] = $clientPhone;
        }
        if ($botMode !== '' && Schema::hasColumn('orders', 'sa_bot_mode')) {
            $orderUpdates['sa_bot_mode'] = $botMode;
        }
        DB::table('orders')->where('id', $orderId)->update($orderUpdates);

        foreach ($pendingMessages as $pendingMessage) {
            $this->syncConversationMessageToOrderChatAdmin(
                $orderId,
                (string) ($pendingMessage->text ?? ''),
                (string) ($pendingMessage->direction ?? '') === 'outbound',
                optional($pendingMessage->sent_at)->toIso8601String() ?: '',
                (string) ($pendingMessage->message_id ?? '')
            );
        }
    }

    private function markConversationAsRead(string $conversationId): void
    {
        if ($conversationId === '' || !Schema::hasTable('sa_conversations') || !Schema::hasColumn('sa_conversations', 'unread_for_manager')) {
            return;
        }

        SaConversation::query()
            ->where('conversation_id', $conversationId)
            ->update([
                'unread_for_manager' => 0,
                'updated_at' => now(),
            ]);
    }

    private function syncConversationMessageToOrderChatAdmin(
        int $orderId,
        string $text,
        bool $isAdmin,
        string $sentAt = '',
        string $saMessageId = ''
    ): void {
        if ($text === '' || !Schema::hasTable('order_user_comments')) {
            return;
        }

        $order = DB::table('orders')->where('id', $orderId)->first(['id', 'user_id']);
        if (!$order) {
            return;
        }

        $timestamp = $sentAt !== '' ? Carbon::parse($sentAt) : now();
        $senderUserId = $isAdmin ? $this->resolveAdminSenderUserId() : (int) $order->user_id;

        $insertData = [
            'order_id' => $orderId,
            'user_id' => $senderUserId > 0 ? $senderUserId : null,
            'comment' => $text,
            'is_admin' => $isAdmin ? 1 : 0,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'is_img_sketch' => 0,
            'is_img_painter' => 0,
            'order_painter_image_id' => 0,
            'order_user_image_id' => 0,
            'is_read' => $isAdmin ? 0 : 1,
            'admin_is_read' => $isAdmin ? 1 : 0,
        ];

        if ($saMessageId !== '' && Schema::hasColumn('order_user_comments', 'sa_message_id')) {
            $exists = DB::table('order_user_comments')
                ->where('order_id', $orderId)
                ->where('sa_message_id', $saMessageId)
                ->exists();

            if ($exists) {
                return;
            }

            $insertData['sa_message_id'] = $saMessageId;
        }

        if (Schema::hasColumn('order_user_comments', 'sa_direction')) {
            $insertData['sa_direction'] = $isAdmin ? 'outbound' : 'inbound';
        }

        DB::table('order_user_comments')->insert($insertData);
    }

    private function resolveAdminSenderUserId(): int
    {
        if (auth()->check()) {
            return (int) auth()->id();
        }

        if (!Schema::hasTable('users')) {
            return 0;
        }

        return (int) DB::table('users')
            ->whereIn('role_id', [1, 4])
            ->orderBy('id', 'asc')
            ->value('id');
    }

    private function resolveSimulatorDefaults(): array
    {
        $leadId = null;
        $conversationId = null;
        $messageId = null;
        $clientPhone = null;
        $clientName = null;
        $serviceId = null;
        $sampleSize = null;
        $couponCode = null;
        $bonusClientPhone = null;
        $bonusClientName = null;
        $bonusClientEmail = null;
        $giftCardNominal = null;
        $familyConstructorSize = null;
        $galleryItemId = null;
        $galleryItemSize = null;
        $blogPostId = null;

        if (Schema::hasTable('orders')) {
            $latestOrderId = Orders::query()->orderByDesc('id')->value('id');
            if ($latestOrderId) {
                $leadId = (string) $latestOrderId;
            }
        }

        if (Schema::hasTable('header_menu')) {
            $menuId = DB::table('header_menu')
                ->whereIn('menu_pos', [1, 2])
                ->where('is_show', 1)
                ->orderBy('menu_pos')
                ->orderBy('order')
                ->orderBy('id')
                ->value('id');

            if ($menuId) {
                $serviceId = 'HM-' . (int) $menuId;
            }
        }

        if (Schema::hasTable('sa_conversations')) {
            $conversationQuery = SaConversation::query();
            if ($leadId !== null) {
                $conversationQuery->where('orders_id', (int) $leadId);
            }

            $conversation = $conversationQuery
                ->orderByDesc('last_message_at')
                ->orderByDesc('id')
                ->first();

            if ($conversation) {
                if (!empty($conversation->conversation_id)) {
                    $conversationId = (string) $conversation->conversation_id;
                }
                if (!empty($conversation->client_phone)) {
                    $clientPhone = (string) $conversation->client_phone;
                }
                if (!empty($conversation->client_name)) {
                    $clientName = (string) $conversation->client_name;
                }
            }
        }

        if (Schema::hasTable('sa_messages')) {
            $messageQuery = SaMessage::query();
            if ($leadId !== null && is_numeric($leadId)) {
                $messageQuery->where('orders_id', (int) $leadId);
            }
            if ($conversationId !== null && $conversationId !== '') {
                $messageQuery->where('conversation_id', $conversationId);
            }

            $message = $messageQuery
                ->orderByDesc('sent_at')
                ->orderByDesc('id')
                ->first();

            if ($message && !empty($message->message_id)) {
                $messageId = (string) $message->message_id;
            }
        }

        if (Schema::hasTable('canvas_header')) {
            $sizesRaw = DB::table('canvas_header')->orderBy('id')->value('sizes_30x40');
            if (is_string($sizesRaw) && $sizesRaw !== '') {
                if (preg_match('/([0-9]+\s*x\s*[0-9]+)\s*\[/', $sizesRaw, $matches)) {
                    $sampleSize = strtolower(str_replace(' ', '', $matches[1]));
                }
            }
        }

        if (Schema::hasTable('coupons')) {
            $couponCode = DB::table('coupons')
                ->where('is_active', 1)
                ->where(function ($query) {
                    $query->where('is_universal', 1)
                        ->orWhere('free_delivery', 1)
                        ->orWhere('is_giftcard', 1);
                })
                ->orderBy('id')
                ->value('text');
        }

        if (Schema::hasTable('users')) {
            $bonusUser = DB::table('users')
                ->whereNotNull('bonuses')
                ->where('bonuses', '>', 0)
                ->orderByDesc('bonuses')
                ->orderBy('id')
                ->first(['email', 'phone', 'first_name', 'last_name']);

            if ($bonusUser) {
                $bonusClientPhone = !empty($bonusUser->phone) ? (string) $bonusUser->phone : null;
                $bonusClientEmail = !empty($bonusUser->email) ? (string) $bonusUser->email : null;

                $firstName = trim((string) ($bonusUser->first_name ?? ''));
                $lastName = trim((string) ($bonusUser->last_name ?? ''));
                $fullName = trim($firstName . ' ' . $lastName);
                $bonusClientName = $fullName !== ''
                    ? $fullName
                    : ($bonusClientEmail ?: $bonusClientPhone);
            }
        }

        if (Schema::hasTable('gift_card_noms')) {
            $giftCardNominal = DB::table('gift_card_noms')
                ->orderBy('id')
                ->value('text');
        }

        if (Schema::hasTable('family_constructor')) {
            $familySizesRaw = DB::table('family_constructor')->orderBy('id')->value('sizes');
            if (is_string($familySizesRaw) && preg_match('/([0-9]+\s*x\s*[0-9]+)\s*\[/', $familySizesRaw, $matches)) {
                $familyConstructorSize = strtolower(str_replace(' ', '', $matches[1]));
            }
        }

        if (Schema::hasTable('gallery_items')) {
            $galleryItem = DB::table('gallery_items')
                ->where('active', 1)
                ->whereIn('id_type', [2, 3, 4])
                ->whereNotNull('custom_size_prices')
                ->where('custom_size_prices', '<>', '')
                ->orderBy('id')
                ->first(['id', 'custom_size_prices']);

            if ($galleryItem) {
                $galleryItemId = (int) $galleryItem->id;
                if (preg_match('/([0-9]+\s*x\s*[0-9]+)\s*\[/', (string) $galleryItem->custom_size_prices, $matches)) {
                    $galleryItemSize = strtolower(str_replace(' ', '', $matches[1]));
                }
            }
        }

        if (Schema::hasTable('blog_posts')) {
            $blogQuery = DB::table('blog_posts')->orderByDesc('id');
            if (Schema::hasColumn('blog_posts', 'status')) {
                $blogQuery->where(function ($query) {
                    $query->where('status', 'published')
                        ->orWhereNull('status');
                });
            }
            $blogPostId = $blogQuery->value('id');
        }

        return [
            'lead_id' => $leadId,
            'conversation_id' => $conversationId,
            'message_id' => $messageId,
            'client_phone' => $clientPhone,
            'client_name' => $clientName,
            'service_id' => $serviceId,
            'sample_size' => $sampleSize,
            'coupon_code' => $couponCode,
            'bonus_client_phone' => $bonusClientPhone,
            'bonus_client_name' => $bonusClientName,
            'bonus_client_email' => $bonusClientEmail,
            'gift_card_nominal' => $giftCardNominal,
            'family_constructor_size' => $familyConstructorSize,
            'gallery_item_id' => $galleryItemId,
            'gallery_item_size' => $galleryItemSize,
            'blog_post_id' => $blogPostId,
        ];
    }

    private function buildConversationsListQuery(Request $request)
    {
        $query = SaConversation::query()
            ->leftJoin('orders', 'orders.id', '=', 'sa_conversations.orders_id')
            ->select([
                'sa_conversations.*',
                'orders.id as order_id',
                'orders.status as order_status',
            ])
            ->addSelect([
                'last_message_text' => SaMessage::query()
                    ->select('text')
                    ->whereColumn('conversation_id', 'sa_conversations.conversation_id')
                    ->orderByRaw('COALESCE(sent_at, created_at) desc')
                    ->limit(1),
                'last_message_direction' => SaMessage::query()
                    ->select('direction')
                    ->whereColumn('conversation_id', 'sa_conversations.conversation_id')
                    ->orderByRaw('COALESCE(sent_at, created_at) desc')
                    ->limit(1),
                'messages_count' => SaMessage::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('conversation_id', 'sa_conversations.conversation_id'),
            ]);

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($inner) use ($q) {
                $inner->where('sa_conversations.conversation_id', 'like', '%' . $q . '%')
                    ->orWhere('sa_conversations.client_phone', 'like', '%' . $q . '%')
                    ->orWhere('sa_conversations.client_name', 'like', '%' . $q . '%')
                    ->orWhere('orders.id', $q);
            });
        }

        if ($request->input('scope') === 'unlinked') {
            $query->whereNull('sa_conversations.orders_id');
        }

        if ($request->input('scope') === 'unread' && Schema::hasColumn('sa_conversations', 'unread_for_manager')) {
            $query->where('sa_conversations.unread_for_manager', 1);
        }

        if ($request->input('scope') === 'awaiting_reply') {
            $query->whereRaw(
                "(select sm.direction from sa_messages sm where sm.conversation_id = sa_conversations.conversation_id order by COALESCE(sm.sent_at, sm.created_at) desc, sm.id desc limit 1) = ?",
                ['inbound']
            );
        }

        if ($request->input('scope') === 'recent') {
            $query->whereRaw(
                'COALESCE(sa_conversations.last_message_at, sa_conversations.updated_at, sa_conversations.created_at) >= ?',
                [now()->subDay()->format('Y-m-d H:i:s')]
            );
        }

        return $query;
    }
}
