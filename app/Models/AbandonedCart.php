<?php

namespace App\Models;

use App\Models\Orders;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AbandonedCart extends Model
{

    protected $guarded = [];

    protected $casts = [
        'cart_data' => 'array',
        'token_expires_at' => 'datetime',
        'is_send_email_twelve_hours' => 'boolean',
        'is_coupon_sent' => 'boolean',
    ];

    /**
     * Генерує новий токен для відновлення кошика
     */
    public function generateRecoveryToken(): string
    {
        $this->recovery_token = Str::random(40);
        $this->token_expires_at = now()->addHours(24*2);
        $this->is_send_email_twelve_hours = true;
        $this->is_coupon_sent = false;
        $this->save();

        return $this->recovery_token;
    }

    /**
     * Очищає токен після використання
     */
    public function clearRecoveryToken(): void
    {
        $this->recovery_token = null;
        $this->token_expires_at = null;
        $this->is_send_email_twelve_hours = false;
        $this->save();
    }

    /**
     * Checks whether a paid order exists near or after the last cart update.
     * Default window: 7 days before the last change to now.
     */
    public function hasPaidOrderAfterUpdate(int $lookbackDays = 7): bool
    {
        $userId = User::where('email', $this->email)->value('id');

        if (!$userId) {
            return false;
        }

        $lastChange = $this->updated_at ?? $this->created_at ?? now();
        $fromDate = $lastChange->copy()->subDays($lookbackDays);

        return Orders::where('user_id', $userId)
            ->where('payment_status', 'payed')
            ->where('created_at', '>=', $fromDate)
            ->exists();
    }
}
