<?php

namespace App\Services;

use App\Models\SaEvent;
use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class SaMessageIngressService
{
    public function isDuplicate(string $eventId, string $idempotencyKey): bool
    {
        return SaEvent::query()->where('event_id', $eventId)
            ->orWhere(function ($query) use ($idempotencyKey): void {
                $query->where('source', 'SA')->whereIn('event_type', ['message.created', 'message.status'])
                    ->where('idempotency_key', $idempotencyKey);
            })->exists();
    }

    /** The unique receipt and all business writes commit or roll back together. */
    public function process(array $payload, Closure $persist): bool
    {
        $eventId = $payload['event_id'];
        $key = $payload['idempotency_key'];

        try {
            return DB::transaction(function () use ($payload, $eventId, $key, $persist): bool {
                if ($this->isDuplicate($eventId, $key)) {
                    return false;
                }

                // Existing unique dedupe_key serializes equal keys, event_id serializes
                // equal events. No schema change or non-transactional cache reservation.
                $receipt = SaEvent::query()->create([
                    'dedupe_key' => 'sa:webhooks:messages:key:'.hash('sha256', $key),
                    'event_id' => $eventId, 'idempotency_key' => $key,
                    'event_type' => $payload['event_type'], 'source' => 'SA',
                    'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
                    'status' => 'received', 'processed_at' => null,
                ]);

                $persist();
                $receipt->update(['status' => 'processed', 'processed_at' => now()]);

                return true;
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            // A competing transaction may have committed while our INSERT waited.
            // Inspect only after rollback; do not hide unrelated constraint failures.
            if ($this->isDuplicate($eventId, $key)) {
                return false;
            }

            throw $exception;
        }
    }
}
