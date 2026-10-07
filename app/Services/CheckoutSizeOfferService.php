<?php

namespace App\Services;

use App\Models\GalleryItem;
use Illuminate\Support\Facades\DB;

class CheckoutSizeOfferService
{
    public function multiplier(): float
    {
        $country = session('basket_country');

        return max(.01, (float) ($country ? DB::table('country_tels')->where('country_code', $country)
            ->value('price_country_mltpr') ?: 1 : 1));
    }

    public function offer(array $item, float $multiplier = 1): ?array
    {
        if (! empty($item['is_recommendation']) || ! empty($item['is_canvas_recommendation']) ||
            ! isset($item['price']) || ! is_numeric($item['price'])) {
            return null;
        }
        $canvas = ! empty($item['is_canvas_collage']) || ! empty($item['is_canvas_inter']) ||
            ((int) ($item['basketType'] ?? 0) === 1 && empty($item['is_def_product']));
        if ($canvas) {
            $header = DB::table('canvas_header')->first();
            $source = $header ? implode(',', array_map(function ($key) use ($header) {
                return $header->$key ?? '';
            }, ['sizes_30x40', 'sizes_40x30', 'sizes_60x30', 'sizes_38x38'])) : '';
        } else {
            $gallery = GalleryItem::find($item['pid'] ?? 0);
            $source = $gallery ? ($gallery->custom_size_prices ?: $gallery->sizes_cals) : '';
        }

        return $this->select($item, $source, $multiplier);
    }

    public function select(array $item, string $source, float $multiplier = 1): ?array
    {
        $currentSize = '';
        foreach (['sizeId', 'size_name', 'size'] as $field) {
            $candidate = $this->clean((string) ($item[$field] ?? ''));
            if (preg_match('/^\d+(?:\.\d+)?x\d+(?:\.\d+)?$/', $candidate)) {
                $currentSize = $candidate;
                break;
            }
        }
        $currentDimensions = explode('x', $currentSize);
        if (count($currentDimensions) !== 2 || ! is_numeric($currentDimensions[0]) || ! is_numeric($currentDimensions[1])) {
            return null;
        }
        $sizes = [];
        $currentBase = null;
        foreach (explode(',', $source) as $part) {
            if (! preg_match('/^(\d+(?:\.\d+)?x\d+(?:\.\d+)?)\[(\d+(?:\.\d+)?)(?:-(\d+(?:\.\d+)?))?\]([a-z]*)$/i', trim($part), $m)) {
                continue;
            }
            $price = isset($m[3]) && $m[3] !== '' ? (float) $m[3] : (float) $m[2];
            if ($m[1] === $currentSize) {
                $currentBase = $price;
            }
            $sizes[] = ['size' => $m[1].($m[4] ?? ''), 'price' => $price,
                'original_price' => (float) $m[2], 'dimensions' => explode('x', $m[1])];
        }
        if ($currentBase === null) {
            return null;
        }
        $area = $currentDimensions[0] * $currentDimensions[1];
        usort($sizes, function ($a, $b) {
            return ($a['dimensions'][0] * $a['dimensions'][1]) <=> ($b['dimensions'][0] * $b['dimensions'][1]);
        });
        foreach ($sizes as $size) {
            // Keep the exact aspect ratio and orientation of the customer's photo format.
            if (abs($size['dimensions'][0] * $currentDimensions[1] -
                $size['dimensions'][1] * $currentDimensions[0]) > 0.000001) {
                continue;
            }
            $newArea = $size['dimensions'][0] * $size['dimensions'][1];
            if (hasSpecialLabel($size['size'])) {
                continue;
            }
            // Keep extras separate so another upgrade never compounds the discount.
            $extras = isset($item['checkout_size_extras_price']) ? (float) $item['checkout_size_extras_price'] :
                max(0, (float) $item['price'] / $multiplier - $currentBase);
            $discountedBase = round($size['price'] * .85 * $multiplier, 2);
            $newPrice = round($discountedBase + $extras * $multiplier, 2);
            if ($newArea <= $area || $newPrice <= round((float) $item['price'], 2)) {
                continue;
            }

            return ['alternative_size' => $size['size'], 'alternative_price' => $newPrice,
                'current_size' => $currentSize, 'current_price' => (float) $item['price'],
                'extra' => round($newPrice - (float) $item['price'], 2),
                'unit_base_price' => round($newPrice / $multiplier, 4),
                'canvas_base_price' => $size['price'], 'extras_price' => $extras,
                'canvas_discounted_price' => $discountedBase,
                'before_discount_price' => round(($size['price'] + $extras) * $multiplier, 2)];
        }

        return null;
    }

    public function clean(string $size): string
    {
        return strtolower(preg_replace('/[htsr]$/i', '', trim($size)));
    }

    public function galleryRecommendation($item): ?array
    {
        $source = $item->custom_size_prices ?: $item->sizes_cals;
        foreach (explode(',', (string) $source) as $part) {
            if (preg_match('/^(\d+(?:\.\d+)?x\d+(?:\.\d+)?)\[(\d+(?:\.\d+)?)(?:-(\d+(?:\.\d+)?))?\]([a-z]*)$/i', trim($part), $m)) {
                $size = $m[1].($m[4] ?? '');
                if (! hasSpecialLabel($size)) {
                    return ['size' => $size, 'price' => (float) (($m[3] ?? '') !== '' ? $m[3] : $m[2])];
                }
            }
        }

        return trim((string) $source) === '' && (float) $item->price_from > 0 ?
            ['size' => '30x40', 'price' => (float) $item->price_from] : null;
    }
}
