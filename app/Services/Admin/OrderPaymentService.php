<?php

namespace App\Services\Admin;

use App\Http\Controllers\GiftcardController;
use App\Mail\GiftCard;
use App\Mail\Payment_successful;
use App\Models\Coupon;
use App\Models\Orders;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\BestEffortMailService;
use App\Services\SynvolveWebhookService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderPaymentService
{
    public const STATUSES = ['not_payed', 'prepayment', 'payed'];

    public function __construct(
        private readonly BestEffortMailService $mail,
        private readonly SynvolveWebhookService $webhook,
        private readonly UserRepository $users,
        private readonly GiftcardController $giftcards,
    ) {}

    public function updateStatus(Orders $order, string $status): array
    {
        validator(['payment_status' => $status], [
            'payment_status' => ['required', Rule::in(self::STATUSES)],
        ])->validate();

        $result = DB::transaction(function () use ($order, $status): array {
            $lockedOrder = Orders::query()->lockForUpdate()->findOrFail($order->id);
            $customer = User::query()->lockForUpdate()->find($lockedOrder->user_id);

            if (in_array($status, ['prepayment', 'payed'], true) && ! $customer) {
                throw ValidationException::withMessages([
                    'payment_status' => 'У заказа не найден клиент.',
                ]);
            }

            $inviter = null;
            if ($status === 'payed') {
                if (filled($lockedOrder->used_coupon)) {
                    $inviter = User::query()
                        ->where('inv_sale_code', $lockedOrder->used_coupon)
                        ->lockForUpdate()
                        ->first();

                    if ($inviter) {
                        $inviter->forceFill(['bonuses' => (float) $inviter->bonuses + 5])->save();
                    }
                }

                if ((int) $lockedOrder->use_bonus === 0) {
                    $customer->forceFill([
                        'bonuses' => (float) $customer->bonuses + $this->calculateBonus((float) $lockedOrder->price),
                    ])->save();
                    $lockedOrder->use_bonus = 2;
                } else {
                    $lockedOrder->use_bonus = 1;
                }
            }

            $lockedOrder->payment_status = $status;
            $lockedOrder->save();

            return [
                'customer' => $customer,
                'inviter' => $inviter,
            ];
        });

        $notificationsEnabled = (bool) config('admin_migration.payment_notifications_enabled', false);
        $giftCardMailable = $status === 'payed' && $result['customer']
            ? $this->prepareGiftCards($order->fresh(), $result['customer'])
            : null;
        $mailProcessed = false;
        if ($result['customer'] && in_array($status, ['prepayment', 'payed'], true)) {
            $locale = strtolower((string) ($result['customer']->country ?: $result['customer']->preferredLocale() ?: 'ru'));
            $mailable = new Payment_successful($order->id, $locale);
            $mailProcessed = $notificationsEnabled
                ? $this->mail->send(
                    $result['customer']->email,
                    $mailable,
                    'admin_order_payment_status',
                    ['order_id' => $order->id, 'payment_status' => $status],
                )
                : $this->mail->attempt(
                    static fn () => Mail::mailer('log')->to($result['customer']->email)->send($mailable),
                    'admin_order_payment_status_preview',
                    ['order_id' => $order->id, 'payment_status' => $status],
                );
        }

        $giftCardMailProcessed = false;
        if ($giftCardMailable) {
            $giftCardMailProcessed = $notificationsEnabled
                ? $this->mail->send(
                    $result['customer']->email,
                    $giftCardMailable,
                    'admin_order_gift_cards',
                    ['order_id' => $order->id],
                )
                : $this->mail->attempt(
                    static fn () => Mail::mailer('log')->to($result['customer']->email)->send($giftCardMailable),
                    'admin_order_gift_cards_preview',
                    ['order_id' => $order->id],
                );
        }

        if ($result['inviter']) {
            $mailer = $notificationsEnabled ? config('mail.default') : 'log';
            $this->mail->attempt(
                static fn () => Mail::mailer($mailer)->raw(
                    'Вам пришел бонус на 5 евро',
                    static function ($message) use ($result): void {
                        $message->to($result['inviter']->email)
                            ->subject('Вам пришли бонусы на сайте viarcanvas.com');
                    },
                ),
                'admin_order_referral_bonus',
                ['order_id' => $order->id, 'inviter_id' => $result['inviter']->id],
            );
        }

        $webhookSent = $notificationsEnabled
            ? $this->webhook->notifyOrderSnapshotById($order->id, 'payment_status_changed_manual', [
                'payment_status' => $status,
            ])
            : false;

        Log::info('Admin order payment status updated.', [
            'order_id' => $order->id,
            'payment_status' => $status,
            'notifications_suppressed' => ! $notificationsEnabled,
            'mail_processed' => $mailProcessed,
            'gift_card_mail_processed' => $giftCardMailProcessed,
            'webhook_sent' => $webhookSent,
        ]);

        $order->refresh();

        return [
            'notifications_suppressed' => ! $notificationsEnabled,
            'mail_processed' => $mailProcessed,
            'gift_card_mail_processed' => $giftCardMailProcessed,
            'webhook_sent' => $webhookSent,
        ];
    }

    public function updatePrepayment(Orders $order, mixed $amount): Orders
    {
        $data = validator(['prepayment_price' => $amount], [
            'prepayment_price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ])->validate();

        DB::transaction(function () use ($order, $data): void {
            Orders::query()->lockForUpdate()->findOrFail($order->id)->forceFill([
                'prepayment_price' => $data['prepayment_price'],
            ])->save();
        });

        Log::info('Admin order prepayment amount updated.', [
            'order_id' => $order->id,
            'prepayment_price' => (float) $data['prepayment_price'],
        ]);

        return $order->refresh();
    }

    private function calculateBonus(float $total): int
    {
        return match (true) {
            $total < 30 => 1,
            $total < 60 => 2,
            $total < 90 => 3,
            $total < 140 => 4,
            $total < 180 => 5,
            $total < 250 => 6,
            $total < 300 => 7,
            default => 10,
        };
    }

    private function prepareGiftCards(Orders $order, User $customer): ?GiftCard
    {
        $items = json_decode((string) $order->items, true);
        if (! is_array($items)) {
            return null;
        }

        $giftItems = [];
        foreach ($items as $item) {
            if (! is_array($item) || (int) ($item['pid'] ?? 0) !== 5) {
                continue;
            }

            $count = isset($item['count']) ? max(0, (int) $item['count']) : 1;
            for ($index = 0; $index < $count; $index++) {
                $giftItems[] = [
                    'price' => (int) ($item['price'] ?? 0),
                    'is_offline' => ($item['card_type'] ?? null) === 'offline',
                ];
            }
        }

        if ($giftItems === []) {
            return null;
        }

        $settings = json_decode((string) $customer->settings, true);
        $locale = (string) (is_array($settings) ? ($settings['locale'] ?? null) : null)
            ?: $customer->preferredLocale()
            ?: strtolower((string) $customer->country)
            ?: 'ru';

        foreach ($giftItems as $giftItem) {
            $couponCode = $this->users->generateCouponUserGiftCard(
                $customer->email,
                $giftItem['price'],
                $order->id,
                $giftItem['is_offline'],
            );
            $coupon = $couponCode ? Coupon::query()->where('text', $couponCode)->first() : null;
            if (! $coupon) {
                Log::warning('Gift card coupon could not be created for paid admin order.', [
                    'order_id' => $order->id,
                    'user_id' => $customer->id,
                ]);
                continue;
            }

            $coupon->forceFill([
                'pdf' => $this->giftcards->generateGiftCardPDF(
                    $customer->id,
                    $order->id,
                    $coupon->id,
                    $giftItem['price'],
                    $locale,
                    $couponCode,
                ),
            ])->save();
        }

        return new GiftCard([
            'subject' => trans('pages.gift_card', [], $locale),
            'first_name' => $customer->first_name,
            'user' => $customer,
            'order_id' => $order->id,
        ], $locale);
    }
}
