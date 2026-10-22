<?php

namespace App\Services\Admin;

use App\Models\Orders;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderMediaRemovalService
{
    private const FIELDS = ['sketch' => 'painter_sketch_images', 'painting' => 'painter_images', 'client' => 'client_images'];

    public static function fingerprint(?string $value): string
    {
        return hash('sha256', (string) $value);
    }

    public function remove(Orders $order, User $author, array $input): void
    {
        Gate::forUser($author)->authorize('update', $order);
        $data = validator($input, [
            'type' => ['required', Rule::in(array_keys(self::FIELDS))],
            'row_id' => ['nullable', 'integer', 'min:1'],
            'index' => ['required_without:row_id', 'nullable', 'integer', 'min:0'],
            'fingerprint' => ['required', 'string', 'size:64'],
        ])->validate();
        DB::transaction(function () use ($order, $data): void {
            $locked = Orders::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $field = self::FIELDS[$data['type']];
            $paths = self::paths($locked->{$field});
            $row = null;
            if (! empty($data['row_id'])) {
                if ($data['type'] === 'client') {
                    throw ValidationException::withMessages(['image' => 'У изображения клиента нет записи художника.']);
                }
                $flag = $data['type'] === 'sketch' ? 'is_img_sketch' : 'is_img_painter';
                $row = $locked->order_painter_images()->whereKey($data['row_id'])->where($flag, 1)->lockForUpdate()->first();
                if (! $row || ! hash_equals(self::fingerprint($row->image), $data['fingerprint'])) {
                    throw ValidationException::withMessages(['image' => 'Изображение изменилось или не принадлежит этому заказу. Обновите галерею.']);
                }
                $basename = basename(parse_url($row->image, PHP_URL_PATH) ?: $row->image);
                $paths = array_values(array_filter($paths, fn ($path) => $path !== $row->image
                    && basename(parse_url($path, PHP_URL_PATH) ?: $path) !== $basename));
            } else {
                if (! hash_equals(self::fingerprint($locked->{$field}), $data['fingerprint']) || ! array_key_exists($data['index'], $paths)) {
                    throw ValidationException::withMessages(['image' => 'Список изображений изменился. Обновите галерею перед удалением.']);
                }
                unset($paths[$data['index']]);
            }
            if (! $locked->forceFill([$field => implode(',', $paths)])->save() || ($row && ! $row->delete())) {
                throw ValidationException::withMessages(['image' => 'Не удалось удалить изображение из заказа.']);
            }
        });
        $order->refresh()->unsetRelation('order_painter_images');
    }

    public static function paths(?string $paths): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $paths)), fn ($path) => $path !== ''));
    }
}
