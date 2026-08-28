<?php

namespace App\Services\Admin;

use App\Models\Orders;
use App\Notifications\AdminMailNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderRecipientEmailService
{
    public function send(Orders $order, array $data): array
    {
        $data = validator($data, [
            'subject' => ['required', 'string', 'max:255'],
            'greetings' => ['required', 'string', 'max:255'],
            'line' => ['required', 'string', 'max:10000'],
            'salutation' => ['required', 'string', 'max:255'],
        ])->validate();

        $user = $order->user;
        if (! $user || ! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages(['user' => 'У заказа нет пользователя с корректным email.']);
        }

        if (! config('admin_migration.recipient_email_enabled', false)) {
            Log::info('Admin recipient email suppressed during Filament UAT.', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'subject_length' => mb_strlen($data['subject']),
                'message_length' => mb_strlen($data['line']),
            ]);

            return ['sent' => false, 'suppressed' => true];
        }

        $user->notify(new AdminMailNotification(
            $data['subject'],
            $data['greetings'],
            $data['line'],
            $data['salutation'],
        ));

        Log::info('Admin recipient email queued.', ['order_id' => $order->id, 'user_id' => $user->id]);

        return ['sent' => true, 'suppressed' => false];
    }
}
