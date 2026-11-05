<?php

namespace App\Services;

use App\Models\SaEvent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaLeadUpdateService
{
    public function apply(string $leadId, array $payload): ?array
    {
        $key = $payload['idempotency_key'];
        // Preserve the original receipt namespace for requests processed before this fix.
        $dedupeKey = 'sa:leads:update:key:'.sha1($key);
        try {
            return DB::transaction(function () use ($leadId, $payload, $key, $dedupeKey): ?array {
                if (SaEvent::where('dedupe_key', $dedupeKey)->exists()) { return null; }
                $order = ctype_digit($leadId) ? DB::table('orders')->where('id', $leadId)->lockForUpdate()->first() : null;
                $order ??= DB::table('orders')->where('sa_conversation_id', $leadId)->lockForUpdate()->first();
                if (!$order) { throw (new ModelNotFoundException)->setModel(\App\Models\Orders::class, [$leadId]); }
                $update = $payload['update'];
                $fields = $update['fields'] ?? [];
                $tagsAdd = array_values(array_unique($update['tags_add'] ?? []));
                $tagsRemove = array_values(array_unique($update['tags_remove'] ?? []));
                $notes = $update['notes_append'] ?? [];
                $changes = [];
                $stage = data_get($update, 'stage.id');
                if ($stage !== null) { $changes['status'] = $stage; }
                if ($fields || $tagsAdd || $tagsRemove || $notes) {
                    $delivery = $order->delivery ? json_decode($order->delivery, true, 512, JSON_THROW_ON_ERROR) : [];
                    if (!is_array($delivery)) { throw new \RuntimeException('Order delivery is not a JSON object.'); }
                    $meta = $delivery['sa_lead'] ?? [];
                    if (!is_array($meta)) { throw new \RuntimeException('SA lead metadata is invalid.'); }
                    $meta['fields'] = array_replace($meta['fields'] ?? [], $fields);
                    // Contact/address changes belong to this order, not the global user profile.
                    foreach (['email', 'city', 'address', 'postal_index', 'first_name', 'last_name', 'comment'] as $name) {
                        if (array_key_exists($name, $fields)) { $delivery[$name] = $fields[$name]; }
                    }
                    if (array_key_exists('phone', $fields)) {
                        $delivery['payer_phone'] = $fields['phone'];
                        $changes['sa_client_phone'] = $fields['phone'];
                    }
                    $meta['tags'] = array_values(array_diff(array_unique(array_merge($meta['tags'] ?? [], $tagsAdd)), $tagsRemove));
                    $meta['notes'] = $meta['notes'] ?? [];
                    $adminComment = (string) $order->admin_comment;
                    foreach ($notes as $note) {
                        $note['created_at'] = Carbon::parse($note['created_at'] ?? now())->utc()->toIso8601String();
                        $meta['notes'][] = $note;
                        $adminComment .= ($adminComment === '' ? '' : "\n").'[SA '.$note['created_at'].'] '.$note['text'];
                    }
                    if ($notes) { $changes['admin_comment'] = $adminComment; }
                    $delivery['sa_lead'] = $meta;
                    $json = json_encode($delivery, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                    // orders.delivery is legacy MySQL TEXT, not LONGTEXT.
                    if (strlen($json) > 60000 || strlen($adminComment) > 60000) {
                        throw ValidationException::withMessages(['update' => 'Order integration metadata exceeds the storage limit.']);
                    }
                    $changes['delivery'] = $json;
                }
                $receipt = SaEvent::create(['dedupe_key' => $dedupeKey, 'idempotency_key' => $key,
                    'event_type' => 'sa.lead.update', 'source' => 'SA', 'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
                    'status' => 'received', 'processed_at' => null]);
                if ($changes) {
                    $changes['updated_at'] = now();
                    DB::table('orders')->where('id', $order->id)->update($changes);
                }
                if (array_key_exists('phone', $fields) && $order->sa_conversation_id) {
                    DB::table('sa_conversations')->where('conversation_id', $order->sa_conversation_id)
                        ->where('orders_id', $order->id)->update(['client_phone' => $fields['phone'], 'updated_at' => now()]);
                }
                $receipt->update(['status' => 'processed', 'processed_at' => now()]);
                return ['lead_id' => $leadId, 'updated' => true, 'stage' => ['id' => $stage],
                    'fields_updated' => array_keys($fields), 'tags' => ['added' => $tagsAdd, 'removed' => $tagsRemove],
                    'notes_appended' => count($notes), 'order_updated' => (bool) $changes, 'temporary_lead_created' => false];
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            if (SaEvent::where('dedupe_key', $dedupeKey)->exists()) { return null; }
            throw $exception;
        }
    }
}
