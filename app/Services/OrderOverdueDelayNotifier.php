<?php

namespace App\Services;

use App\Mail\OrderOverdueDelayMail;
use App\Models\Orders;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class OrderOverdueDelayNotifier
{
    private const FINAL_STATUSES = [
        'sended',
        'send_lubanas',
        'completed',
    ];

    /**
     * Send one delay notification for orders whose planned shipping moment has passed.
     *
     * @return array
     */
    public function sendDueNotifications(Carbon $now = null): array
    {
        $now = $now ? $now->copy() : Carbon::now();
        $result = [
            'checked' => 0,
            'sent' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        if (!Schema::hasColumn('orders', 'overdue_delay_email_sent_at')) {
            Log::warning('Order overdue delay notifier skipped: missing orders.overdue_delay_email_sent_at column');

            return $result;
        }

        Orders::query()
            ->whereNull('overdue_delay_email_sent_at')
            ->whereNotIn('status', self::FINAL_STATUSES)
            ->orderBy('id')
            ->chunk(100, function ($orders) use ($now, &$result) {
                foreach ($orders as $order) {
                    $result['checked']++;

                    if (!$this->shouldSend($order, $now)) {
                        $result['skipped']++;
                        continue;
                    }

                    $delivery = $this->decodeDelivery($order);
                    $email = trim((string) ($delivery['email'] ?? ''));
                    $locale = $this->resolveLocale($order, $delivery);

                    try {
                        Mail::to($email)->send(new OrderOverdueDelayMail($order, $delivery, $locale));

                        $order->overdue_delay_email_sent_at = $now;
                        $order->save();

                        $result['sent']++;

                        Log::info('Order overdue delay email sent', [
                            'order_id' => $order->id,
                            'email' => $email,
                            'locale' => $locale,
                            'due_at' => optional($this->resolveDueAt($order, $delivery))->toDateTimeString(),
                        ]);
                    } catch (\Throwable $e) {
                        $result['failed']++;

                        Log::error('Order overdue delay email failed', [
                            'order_id' => $order->id,
                            'email' => $email,
                            'locale' => $locale,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        return $result;
    }

    private function shouldSend(Orders $order, Carbon $now): bool
    {
        $delivery = $this->decodeDelivery($order);
        $email = trim((string) ($delivery['email'] ?? ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $dueAt = $this->resolveDueAt($order, $delivery);

        return $dueAt !== null && $dueAt->lte($now);
    }

    private function decodeDelivery(Orders $order): array
    {
        $delivery = is_string($order->delivery)
            ? json_decode($order->delivery, true)
            : $order->delivery;

        return is_array($delivery) ? $delivery : [];
    }

    private function resolveLocale(Orders $order, array $delivery): string
    {
        $locale = $this->normalizeLocale((string) ($delivery['lang'] ?? ''));

        if ($locale !== null) {
            return $locale;
        }

        if ($order->user_id) {
            $userLocale = DB::table('users')->where('id', $order->user_id)->value('locale');
            $locale = $this->normalizeLocale((string) $userLocale);

            if ($locale !== null) {
                return $locale;
            }
        }

        return 'ru';
    }

    private function normalizeLocale(string $locale): ?string
    {
        $locale = strtolower(trim($locale));
        $locale = str_replace('_', '-', $locale);

        if ($locale === '') {
            return null;
        }

        $locale = explode('-', $locale)[0];

        if ($locale === 'et') {
            $locale = 'ee';
        }

        $allowed = ['ru', 'en', 'lv', 'lt', 'ee', 'de', 'pl'];

        return in_array($locale, $allowed, true) ? $locale : null;
    }

    private function resolveDueAt(Orders $order, array $delivery): ?Carbon
    {
        $desiredDate = $this->parseDate($delivery['when_send'] ?? null, 'when_send');

        if ($desiredDate !== null) {
            return $desiredDate->setTime(18, 0, 0);
        }

        $createdAt = $order->created_at instanceof Carbon
            ? $order->created_at->copy()
            : $this->parseDate($order->created_at ?? null, 'created_at');

        if ($createdAt === null) {
            return null;
        }

        $businessDays = $this->isCanvasPrintOrder($order) ? 3 : 8;

        return $this->addBusinessDays($createdAt->startOfDay(), $businessDays)->setTime(18, 0, 0);
    }

    private function parseDate($value, string $field): ?Carbon
    {
        $rawDate = trim((string) $value);

        if ($rawDate === '') {
            return null;
        }

        try {
            return Carbon::parse($rawDate)->startOfDay();
        } catch (\Throwable $e) {
            Log::warning('Order overdue delay notifier skipped invalid date', [
                'field' => $field,
                'value' => $rawDate,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function addBusinessDays(Carbon $date, int $days): Carbon
    {
        $result = $date->copy();
        $added = 0;

        while ($added < $days) {
            $result->addDay();

            if (!$result->isWeekend()) {
                $added++;
            }
        }

        return $result;
    }

    private function isCanvasPrintOrder(Orders $order): bool
    {
        $items = $this->decodeItems($order);

        if ($items === []) {
            return false;
        }

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            if ($this->isPortraitLikeItem($item)) {
                continue;
            }

            if ((string) ($item['basketType'] ?? '') === '1') {
                return true;
            }

            $text = mb_strtolower($this->itemSearchText($item), 'UTF-8');
            foreach (['hm-2', '/new/canvas', 'foto kanvas', 'photo canvas', 'печать канвы', 'печать на холсте'] as $needle) {
                if (mb_strpos($text, $needle) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isPortraitLikeItem(array $item): bool
    {
        if (!empty($item['is_port_product'])) {
            return true;
        }

        $text = mb_strtolower($this->itemSearchText($item), 'UTF-8');

        foreach (['portrait', 'portret', 'портрет', 'sharj', 'шарж', 'simpson', 'collage', 'modular', 'hm-3', 'hm-27', 'hm-29', 'hm-33', 'hm-34', 'hm-43', 'hm-44', 'hm-45', 'hm-47', 'hm-48', 'hm-49'] as $needle) {
            if (mb_strpos($text, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function decodeItems(Orders $order): array
    {
        $items = is_string($order->items)
            ? json_decode($order->items, true)
            : $order->items;

        return is_array($items) ? $items : [];
    }

    private function itemSearchText(array $item): string
    {
        $values = Arr::only($item, [
            'service_id',
            'service_path',
            'name',
            'type',
            'execution',
            'basketType',
        ]);

        if (isset($item['show']) && is_array($item['show'])) {
            $values = array_merge($values, Arr::only($item['show'], [
                'basketType',
                'execution',
                'canvas',
                'type',
            ]));
        }

        return implode(' ', array_map(function ($value) {
            return is_scalar($value) ? (string) $value : '';
        }, $values));
    }
}
