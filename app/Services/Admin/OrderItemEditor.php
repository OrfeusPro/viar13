<?php

namespace App\Services\Admin;

use App\Models\CanvasRam;
use App\Models\Orders;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderItemEditor
{
    public function __construct(
        private readonly OrderItemPresentationService $presentation,
        private readonly OrderItemUploads $uploads,
    ) {}

    public function newState(): array
    {
        return ['name' => '', 'price' => 0, 'size' => '', 'terms' => '', 'userComment' => '',
            'manual_canvas_id' => 2, 'manual_gift_code' => 'G0', 'manual_decoration_id' => 5,
            'manual_orientation_code' => 'V0', 'manual_baget_code' => 'B0', 'manual_express' => false,
            'files' => []];
    }

    public function add(Orders $order, array $input): Orders
    {
        $data = validator($input, [
            'name' => ['required', 'string', 'max:1000'], 'price' => ['required', 'numeric', 'min:0'],
            'size' => ['nullable', 'string', 'max:255'], 'terms' => ['nullable', 'string', 'max:255'],
            'userComment' => ['nullable', 'string', 'max:10000'], 'files' => ['nullable', 'array'],
            ...$this->manualRules(),
        ])->validate();
        $files = $data['files'] ?? [];
        $this->uploads->validate($files, false);
        $createdPaths = [];
        try {
            return DB::transaction(function () use ($order, $data, $files, &$createdPaths): Orders {
                $locked = Orders::query()->lockForUpdate()->findOrFail($order->getKey());
                $items = $this->decode($locked->items, true);
                $delivery = $this->decode($locked->delivery, true);
                $price = round((float) $data['price'], 2);
                $item = [
                    'name' => $data['name'], 'price' => $price, 'sumPrice' => $price,
                    'sumFormatedPrice' => $price.' €', 'formatedPrice' => $price.' €', 'formatedTotalPrice' => $price.' €',
                    'size' => $data['size'] ?? null, 'terms' => $data['terms'] ?? null,
                    'userComment' => $data['userComment'] ?? null, 'orig_images' => [],
                    ...Arr::only($data, array_keys($this->manualRules())),
                ];
                [$item['manual_lac_code'], $item['manual_brushstrokes_code']] = [
                    1 => ['L2', 'P0'], 2 => ['L0', 'P1'], 3 => ['L1', 'P0'], 5 => ['L0', 'P0'],
                ][(int) $data['manual_decoration_id']];
                $item['manual_decoration_id'] = (int) $data['manual_decoration_id'];
                $item['is_manual_baget'] = $data['manual_baget_code'] === 'B1' ? 1 : 0;
                $item['is_manual_express'] = (int) $data['manual_express'];
                unset($item['manual_express']);
                $item = $this->presentation->apply($item, $this->locale($locked));
                $items[] = $item;
                $index = array_key_last($items);
                if ($item['is_manual_express']) {
                    $delivery['is_manual_express'] = 1;
                    $locked->delivery = json_encode($delivery, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
                }
                foreach (['saved_price', 'totalPrice'] as $key) {
                    $items[$key] = round((float) ($items[$key] ?? 0) + $price, 2);
                }
                $formatted = preg_replace('/[^0-9.,-]/u', '', (string) ($items['formatedTotalPrice'] ?? 0));
                $items['formatedTotalPrice'] = round((float) str_replace(',', '.', $formatted) + $price, 2).' €';
                $locked->items = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
                $items[$index]['orig_images'] = $this->uploads->store($locked, $item, $files, $createdPaths);
                $total = 0.0;
                foreach ($items as $key => $row) {
                    if ($this->isItem($key, $row) && isset($row['name'], $row['price'])) {
                        $total += round((float) $row['price'], 2);
                    }
                }
                if (! $locked->forceFill(['items' => json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                    'price' => round($total, 2), 'sale_price' => round($total, 2)])->save()) {
                    throw ValidationException::withMessages(['files' => 'Не удалось сохранить позицию заказа.']);
                }

                return $locked->refresh();
            });
        } catch (Throwable $exception) {
            $this->uploads->rollback($createdPaths);
            throw $exception;
        }
    }

    public function attach(Orders $order, array $input): Orders
    {
        $data = validator($input, ['index' => ['required', 'integer', 'min:0'],
            'fingerprint' => ['required', 'string', 'size:64'], 'files' => ['required', 'array', 'min:1']])->validate();
        $this->uploads->validate($data['files'], true);
        $createdPaths = [];
        try {
            return DB::transaction(function () use ($order, $data, &$createdPaths): Orders {
                $locked = Orders::query()->lockForUpdate()->findOrFail($order->getKey());
                $items = $this->decode($locked->items, true);
                $index = (int) $data['index'];
                $item = $items[$index] ?? null;
                if (! $this->isItem($index, $item) || ! hash_equals($this->fingerprint($item), $data['fingerprint'])) {
                    throw ValidationException::withMessages(['fingerprint' => 'Позиция заказа изменилась. Обновите страницу перед загрузкой.']);
                }
                $newImages = $this->uploads->store($locked, $item, $data['files'], $createdPaths);
                $items[$index]['orig_images'] = [...$this->images($locked, $index), ...$newImages];
                if (! $locked->forceFill(['items' => json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)])->save()) {
                    throw ValidationException::withMessages(['files' => 'Не удалось сохранить исходники позиции.']);
                }

                return $locked->refresh();
            });
        } catch (Throwable $exception) {
            $this->uploads->rollback($createdPaths);
            throw $exception;
        }
    }

    public function state(Orders $order): array
    {
        $state = [];
        foreach ($this->decode($order->items) as $index => $item) {
            if (! $this->isItem($index, $item)) {
                continue;
            }
            $gift = strtoupper((string) ($item['manual_gift_code'] ?? ''));
            if (! in_array($gift, ['G0', 'G1', 'G2'], true)) {
                $gift = 'G0';
                $boxes = $item['boxIds'] ?? [];
                if (is_string($boxes)) {
                    $boxes = json_decode($boxes, true) ?? $boxes;
                }
                foreach ([...(array) $boxes, $item['compl_id'] ?? null] as $box) {
                    if ((int) $box === 1) {
                        $gift = 'G2';
                        break;
                    }
                    if ((int) $box === 2) {
                        $gift = 'G1';
                    }
                }
            }
            $canvas = 2;
            foreach (['manual_canvas_id', 'canvasId', 'holst_id'] as $key) {
                if (in_array((int) ($item[$key] ?? 0), [1, 2, 3, 4, 5], true)) {
                    $canvas = (int) $item[$key];
                    break;
                }
            }
            $decoration = (int) ($item['manual_decoration_id'] ?? 0);
            if (! in_array($decoration, [1, 2, 3, 5], true)) {
                $decoration = (int) ($item['decorationId'] ?? $item['decor_id'] ?? $item['decorId'] ?? 0);
            }
            if (! in_array($decoration, [1, 2, 3, 5], true)) {
                $decoration = match (true) {
                    strtoupper((string) ($item['manual_lac_code'] ?? '')) === 'L2' => 1,
                    strtoupper((string) ($item['manual_brushstrokes_code'] ?? '')) === 'P1' => 2,
                    strtoupper((string) ($item['manual_lac_code'] ?? '')) === 'L1' => 3,
                    default => 5,
                };
            }
            $orientation = strtoupper((string) ($item['manual_orientation_code'] ?? ''));
            if (! in_array($orientation, ['V0', 'V1', 'V2', 'V3', 'V4'], true)) {
                $orientation = match ((int) ($item['formId'] ?? $item['forma_id'] ?? $item['form_id'] ?? 0)) {
                    1 => 'V1', 2 => 'V2', 3 => 'V3', 4 => 'V4', default => 'V0',
                };
            }
            $baget = strtoupper((string) ($item['manual_baget_code'] ?? ''));
            if (! in_array($baget, ['B0', 'B1', 'B2'], true)) {
                $baget = ! empty($item['ram_id']) ? CanvasRam::productionBagetCode($item['ram_id'])
                    : (! empty($item['is_manual_baget']) ? 'B1' : 'B0');
            }
            $fields = Arr::only($item, ['name', 'price', 'sizeId', 'size_name']);
            foreach (['name', 'sizeId', 'size_name'] as $field) {
                if (isset($fields[$field]) && is_scalar($fields[$field])) {
                    $fields[$field] = (string) $fields[$field];
                }
            }
            $state[] = $fields + [
                'price' => 0,
                'size_name' => $item['size'] ?? null,
                'index' => $index, 'fingerprint' => $this->fingerprint($item),
                'userComment' => $item['userComment'] ?? $item['user_comment'] ?? $item['comment'] ?? '',
                'manual_canvas_id' => $canvas, 'manual_gift_code' => $gift,
                'manual_decoration_id' => $decoration, 'manual_orientation_code' => $orientation,
                'manual_baget_code' => $baget,
                'manual_express' => ! empty($item['is_manual_express']) || (float) ($item['terms_price'] ?? 0) > 0 || $this->expressText((string) ($item['terms'] ?? '')),
            ];
        }

        return $state;
    }

    public function update(Orders $order, array $input): Orders
    {
        $data = validator($input, [
            'index' => ['required', 'integer', 'min:0'], 'fingerprint' => ['required', 'string', 'size:64'],
            'name' => ['nullable', 'string', 'max:1000'], 'price' => ['required', 'numeric', 'min:0'],
            'sizeId' => ['nullable', 'string', 'max:255'], 'size_name' => ['nullable', 'string', 'max:255'],
            'userComment' => ['nullable', 'string', 'max:10000'],
            ...$this->manualRules(),
        ])->validate();

        return DB::transaction(function () use ($order, $data): Orders {
            $locked = Orders::query()->lockForUpdate()->findOrFail($order->getKey());
            $items = $this->decode($locked->items, true);
            $index = (int) $data['index'];
            $item = $items[$index] ?? null;
            if (! $this->isItem($index, $item) || ! hash_equals($this->fingerprint($item), $data['fingerprint'])) {
                throw ValidationException::withMessages(['fingerprint' => 'Позиция заказа изменилась. Обновите страницу перед сохранением.']);
            }
            $price = round((float) $data['price'], 2);
            $item['name'] = $data['name'] ?? null;
            $item['price'] = $price;
            $item['sumPrice'] = $price;
            foreach (['sumFormatedPrice', 'formatedPrice', 'formatedTotalPrice'] as $field) {
                $item[$field] = $price.' €';
            }
            if (! empty($data['sizeId'])) {
                $item['sizeId'] = $data['sizeId'];
                $item['show'] = is_array($item['show'] ?? null) ? $item['show'] : [];
                $item['show']['size'] = $data['sizeId'];
            }
            if (! empty($data['size_name'])) {
                $item['size_name'] = $data['size_name'];
            }
            if (array_key_exists('userComment', $data) && $data['userComment'] !== null) {
                $comment = trim($data['userComment']);
                $item['userComment'] = $comment === '' ? null : $comment;
            }
            $item['manual_decoration_id'] = (int) $data['manual_decoration_id'];
            foreach (['manual_canvas_id', 'manual_gift_code', 'manual_decoration_id', 'manual_orientation_code', 'manual_baget_code'] as $field) {
                $item[$field] = $data[$field];
            }
            [$item['manual_lac_code'], $item['manual_brushstrokes_code']] = [
                1 => ['L2', 'P0'], 2 => ['L0', 'P1'], 3 => ['L1', 'P0'], 5 => ['L0', 'P0'],
            ][(int) $data['manual_decoration_id']];
            $item['canvasId'] = (int) $data['manual_canvas_id'];
            $item['is_manual_baget'] = $data['manual_baget_code'] === 'B1' ? 1 : 0;
            $item['is_manual_express'] = (int) $data['manual_express'];
            $items[$index] = $this->presentation->apply($item, $this->locale($locked));

            $total = 0.0;
            $terms = 0.0;
            $express = false;
            foreach ($items as $key => $row) {
                if (! $this->isItem($key, $row)) {
                    continue;
                }
                if (isset($row['name'], $row['price'])) {
                    $total += round((float) $row['price'], 2);
                }
                $terms += (float) ($row['terms_price'] ?? 0);
                $express = $express || $this->enabled($row['is_manual_express'] ?? null);
            }
            $items['total_terms_price'] = round($terms, 2);
            $delivery = $this->decode($locked->delivery, true);
            $delivery['is_manual_express'] = (int) $express;
            if (! $locked->forceFill([
                'items' => json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'delivery' => json_encode($delivery, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'price' => round($total, 2), 'sale_price' => round($total, 2),
            ])->save()) {
                throw ValidationException::withMessages(['fingerprint' => 'Не удалось сохранить позицию заказа.']);
            }

            return $locked->refresh();
        });
    }

    private function locale(Orders $order): string
    {
        $delivery = $this->decode($order->delivery);
        $mapped = ['LV' => 'lv', 'LT' => 'lt', 'EE' => 'ee', 'ET' => 'ee', 'PL' => 'pl', 'DE' => 'de',
            'EN' => 'en', 'GB' => 'en', 'RU' => 'ru'][strtoupper(trim((string) ($delivery['country'] ?? '')))] ?? null;

        return $mapped ?? ($order->user_id ? $order->user?->preferredLocale() : null) ?: 'ru';
    }

    private function manualRules(): array
    {
        return [
            'manual_canvas_id' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'manual_gift_code' => ['required', Rule::in(['G0', 'G1', 'G2'])],
            'manual_decoration_id' => ['required', 'integer', Rule::in([1, 2, 3, 5])],
            'manual_orientation_code' => ['required', Rule::in(['V0', 'V1', 'V2', 'V3', 'V4'])],
            'manual_baget_code' => ['required', Rule::in(['B0', 'B1', 'B2'])],
            'manual_express' => ['required', 'boolean'],
        ];
    }

    public function images(Orders $order, int $index): array
    {
        $images = $this->decode($order->items)[$index]['orig_images'] ?? [];
        if (is_string($images)) {
            $images = json_decode($images, true) ?? explode(',', $images);
        }

        return array_values(array_filter((array) $images, fn ($image): bool => is_string($image) && trim($image) !== ''));
    }

    private function decode(mixed $value, bool $strict = false): array
    {
        $decoded = is_array($value) ? $value : json_decode((string) $value, true);
        if ($strict && $value !== null && $value !== '' && ! is_array($decoded)) {
            throw ValidationException::withMessages(['fingerprint' => 'Не удалось прочитать данные заказа.']);
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function isItem(mixed $index, mixed $item): bool
    {
        return is_int($index) && $index >= 0 && is_array($item) && ! isset($item['totalPrice']);
    }

    private function fingerprint(array $item): string
    {
        return hash('sha256', json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    private function enabled(mixed $value): bool
    {
        return (is_bool($value) && $value)
            || (is_numeric($value) && (int) $value === 1)
            || (is_string($value) && in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true));
    }

    private function expressText(string $value): bool
    {
        foreach (['Express', 'Ekspress', 'Ekspresowy', 'Экспресс', 'Kiirsaadetis', 'Ekspres'] as $text) {
            if (mb_stripos($value, $text) !== false) {
                return true;
            }
        }

        return false;
    }
}
