<?php

namespace App\Services\Admin;

use App\Mail\SendUserReview;
use App\Models\Orders;
use App\Models\UserMessage;
use App\Services\BestEffortMailService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderReviewRequestService
{
    public function __construct(private readonly BestEffortMailService $mail) {}

    public function send(Orders $order): array
    {
        $user = $order->user;
        if (! $user || ! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages(['user' => 'У заказа нет пользователя с корректным email.']);
        }

        if (! config('admin_migration.review_request_enabled', false)) {
            Log::info('Admin review request suppressed during Filament UAT.', [
                'order_id' => $order->id,
                'user_id' => $user->id,
            ]);

            return ['sent' => false, 'suppressed' => true];
        }

        $locale = $user->preferredLocale() ?: 'ru';

        try {
            $messages = UserMessage::query()->get()->translate($locale, 'ru');
            $data = $messages[0] ?? null;
        } catch (Throwable $exception) {
            Log::warning('Review request texts could not be loaded.', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'exception' => $exception,
            ]);
            $data = null;
        }

        if (! $data) {
            throw ValidationException::withMessages(['message' => 'Не найдены тексты письма запроса отзыва.']);
        }

        $reviewUrl = route('send_rev');
        $data['rev_lang_url'] = str_replace('/user/', '/'.$locale.'/user/', $reviewUrl);
        $sent = $this->mail->send(
            $user->email,
            new SendUserReview($data, strtolower((string) ($user->country ?: $locale))),
            'admin_order_review_request',
            ['order_id' => $order->id, 'user_id' => $user->id],
        );

        Log::info('Admin review request processed.', [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'sent' => $sent,
        ]);

        return ['sent' => $sent, 'suppressed' => false];
    }
}
