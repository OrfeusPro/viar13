<?php

namespace Tests\Unit;

use App\Http\Controllers\OrdersController;
use PHPUnit\Framework\TestCase;

class AdminOrderRotationSortTest extends TestCase
{
    public function test_admin_orders_rotation_prioritizes_express_and_nearest_delivery_or_production_deadline(): void
    {
        $controller = new class extends OrdersController {
            public function __construct()
            {
            }

            public function sortForTest($orders, ?\DateTimeImmutable $now = null, ?string $orderIdSort = null): array
            {
                return $this->sortAdminRotationOrders($orders, $now, $orderIdSort);
            }
        };

        $orders = [
            $this->order(101, '2026-05-25 10:00:00', ['when_send' => '2026-05-27'], [
                ['terms' => 'Экспресс - 1 рабочие сутки 5 €', 'terms_price' => 5],
                'total_terms_price' => 5,
            ]),
            $this->order(102, '2026-05-26 10:00:00', ['is_manual_express' => 1], [
                ['terms' => 'Стандарт - 3 рабочих дня 0 €', 'terms_price' => 0],
            ]),
            $this->order(103, '2026-05-20 10:00:00', [], [
                ['terms' => 'Стандарт - 3 рабочих дня 0 €', 'terms_price' => 0],
            ]),
            $this->order(104, '2026-05-24 10:00:00', ['when_send' => '2026-05-28'], [
                ['terms' => 'Стандарт - 3 рабочих дня 0 €', 'terms_price' => 0],
            ]),
            $this->order(105, '2026-05-25 10:00:00', ['when_send' => '2026-06-05'], [
                ['terms' => 'Стандарт - 3 рабочих дня 0 €', 'terms_price' => 0],
            ]),
            $this->order(106, '2026-05-22 10:00:00', [], [
                ['terms' => 'Стандарт - 8 рабочих дней 0 €', 'terms_price' => 0],
            ], '2026-05-26'),
            $this->order(107, '2026-05-26 10:00:00', [], [
                ['terms' => 'Стандарт - 3-5 рабочих дней 0 €', 'terms_price' => 0],
            ]),
            $this->order(108, '2026-05-26 10:00:00', [], []),
        ];

        $sortedIds = array_map(function ($order) {
            return $order->id;
        }, $controller->sortForTest($orders));

        $this->assertSame([101, 102, 103, 106, 104, 108, 107, 105], $sortedIds);
    }

    public function test_new_orders_stay_on_top_for_working_time_window_and_after_hours_grace(): void
    {
        $controller = new class extends OrdersController {
            public function __construct()
            {
            }

            public function sortForTest($orders, ?\DateTimeImmutable $now = null, ?string $orderIdSort = null): array
            {
                return $this->sortAdminRotationOrders($orders, $now, $orderIdSort);
            }
        };

        $orders = [
            $this->order(201, '2026-06-04 10:30:00', [], [
                ['terms' => 'Стандарт - 3 рабочих дня 0 €', 'terms_price' => 0],
            ]),
            $this->order(202, '2026-06-03 21:30:00', [], [
                ['terms' => 'Стандарт - 3 рабочих дня 0 €', 'terms_price' => 0],
            ]),
            $this->order(203, '2026-06-04 07:30:00', [], [
                ['terms' => 'Стандарт - 3 рабочих дня 0 €', 'terms_price' => 0],
            ]),
            $this->order(204, '2026-06-01 10:00:00', ['when_send' => '2026-06-02'], [
                ['terms' => 'Экспресс - 1 рабочие сутки 5 €', 'terms_price' => 5],
                'total_terms_price' => 5,
            ]),
        ];

        $sortedIdsAtMorning = array_map(function ($order) {
            return $order->id;
        }, $controller->sortForTest($orders, new \DateTimeImmutable('2026-06-04 10:45:00')));

        $this->assertSame([201, 203, 202, 204], $sortedIdsAtMorning);

        $sortedIdsAfterGrace = array_map(function ($order) {
            return $order->id;
        }, $controller->sortForTest($orders, new \DateTimeImmutable('2026-06-04 11:30:00')));

        $this->assertSame([201, 204, 202, 203], $sortedIdsAfterGrace);
    }

    public function test_order_id_sort_overrides_rotation_in_both_directions(): void
    {
        $controller = new class extends OrdersController {
            public function __construct()
            {
            }

            public function sortForTest($orders, ?\DateTimeImmutable $now = null, ?string $orderIdSort = null): array
            {
                return $this->sortAdminRotationOrders($orders, $now, $orderIdSort);
            }
        };

        $orders = [
            $this->order(301, '2026-06-04 10:30:00', [], [
                ['terms' => 'Стандарт - 3 рабочих дня 0 €', 'terms_price' => 0],
            ]),
            $this->order(303, '2026-06-01 10:00:00', ['when_send' => '2026-06-02'], [
                ['terms' => 'Экспресс - 1 рабочие сутки 5 €', 'terms_price' => 5],
                'total_terms_price' => 5,
            ]),
            $this->order(302, '2026-06-02 10:00:00', [], [
                ['terms' => 'Стандарт - 3 рабочих дня 0 €', 'terms_price' => 0],
            ]),
        ];

        $sortedDesc = array_map(function ($order) {
            return $order->id;
        }, $controller->sortForTest($orders, new \DateTimeImmutable('2026-06-04 11:00:00'), 'desc'));

        $this->assertSame([303, 302, 301], $sortedDesc);

        $sortedAsc = array_map(function ($order) {
            return $order->id;
        }, $controller->sortForTest($orders, new \DateTimeImmutable('2026-06-04 11:00:00'), 'asc'));

        $this->assertSame([301, 302, 303], $sortedAsc);
    }

    private function order(
        int $id,
        string $createdAt,
        array $delivery,
        array $items,
        ?string $painterEndtime = null
    ): \stdClass
    {
        $order = new \stdClass();
        $order->id = $id;
        $order->created_at = $createdAt;
        $order->painter_endtime = $painterEndtime;
        $order->delivery = json_encode($delivery);
        $order->items = json_encode($items);

        return $order;
    }
}
