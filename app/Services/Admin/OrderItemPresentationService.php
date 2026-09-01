<?php

namespace App\Services\Admin;

use App\Models\GalleryBox;
use App\Models\GalleryDecoration;
use App\Models\GalleryHolst;
use App\Models\GalleryItem;

class OrderItemPresentationService
{
    /** @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    public function apply(array $item, string $locale): array
    {
        $canvasId = $this->canvasId($item);
        $item['manual_canvas_id'] = $canvasId;
        $item['canvasId'] = $canvasId;

        if ($canvas = GalleryHolst::find($canvasId)) {
            $item['show'] = is_array($item['show'] ?? null) ? $item['show'] : [];
            if ($name = $canvas->translate($locale, 'ru')->name ?? null) {
                $item['show']['canvas'] = $name;
            }
        }

        $giftCode = $this->giftCode($item);
        $boxId = ['G0' => 3, 'G1' => 2, 'G2' => 1][$giftCode] ?? 3;
        $item['show'] = is_array($item['show'] ?? null) ? $item['show'] : [];
        if (($box = GalleryBox::find($boxId)) && ($name = $box->translate($locale, 'ru')->name ?? null)) {
            $item['show']['box'] = [$name];
        }
        $item['compl_id'] = $boxId;

        $decorationId = $this->decorationId($item);
        if (($decoration = GalleryDecoration::find($decorationId)) && ($name = $decoration->translate($locale, 'ru')->name ?? null)) {
            $item['show']['decoration'] = $name;
        }

        $terms = trim((string) ($item['terms'] ?? ''));
        if (! empty($item['is_manual_express'])) {
            if ($terms === '' || is_numeric(str_replace(',', '.', $terms))) {
                $priceText = '';
                $termsValue = str_replace(',', '.', $terms);
                if ($terms !== '' && is_numeric($termsValue) && (float) $termsValue > 0) {
                    $priceText = ' '.$this->money((float) $termsValue).' €';
                } elseif (isset($item['terms_price']) && is_numeric($item['terms_price']) && (float) $item['terms_price'] > 0) {
                    $priceText = ' '.$this->money((float) $item['terms_price']).' €';
                }
                $item['terms'] = trim(GalleryItem::getTermsByPriceLocaled(1, $locale).$priceText);
            } elseif (! $this->isExpressText($terms)) {
                $express = GalleryItem::getTermsByPriceLocaled(1, $locale);
                $item['terms'] = $this->isStandardTermsText($terms, $locale) ? $express : $express.' | '.$terms;
            }
        } elseif (array_key_exists('is_manual_express', $item)) {
            if (isset($item['terms_price'])) {
                $item['terms_price'] = 0;
            }
            if ($terms === '' || $this->isExpressText($terms)) {
                $item['terms'] = GalleryItem::getTermsByPriceLocaled(0, $locale);
            }
        }

        return $item;
    }

    /** @param array<string, mixed> $item */
    private function canvasId(array $item): int
    {
        foreach (['manual_canvas_id', 'canvasId', 'holst_id'] as $key) {
            $id = (int) ($item[$key] ?? 0);
            if ($id >= 1 && $id <= 5) {
                return $id;
            }
        }

        return 2;
    }

    /** @param array<string, mixed> $item */
    private function giftCode(array $item): string
    {
        $code = strtoupper(trim((string) ($item['manual_gift_code'] ?? '')));
        if (in_array($code, ['G0', 'G1', 'G2'], true)) {
            return $code;
        }

        $boxIds = $item['boxIds'] ?? [];
        if (is_string($boxIds)) {
            $boxIds = json_decode($boxIds, true) ?: [$boxIds];
        }
        foreach ((array) $boxIds as $boxId) {
            if ((int) $boxId === 1) {
                return 'G2';
            }
            if ((int) $boxId === 2) {
                $code = 'G1';
            }
        }
        if ($code === 'G1') {
            return $code;
        }

        return match ((int) ($item['compl_id'] ?? 0)) {
            1 => 'G2', 2 => 'G1', default => 'G0',
        };
    }

    /** @param array<string, mixed> $item */
    private function decorationId(array $item): int
    {
        foreach (['manual_decoration_id', 'decorationId', 'decor_id', 'decorId'] as $key) {
            $id = (int) ($item[$key] ?? 0);
            if (in_array($id, [1, 2, 3, 5], true)) {
                return $id;
            }
        }

        return 5;
    }

    private function isExpressText(string $text): bool
    {
        foreach (['express', 'ekspress', 'ekspresowy', 'экспресс', 'kiirsaadetis', 'ekspres'] as $needle) {
            if (mb_stripos(mb_strtolower($text), $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function isStandardTermsText(string $text, string $locale): bool
    {
        $standard = trim((string) GalleryItem::getTermsByPriceLocaled(0, $locale));
        if ($standard !== '' && mb_stripos(mb_strtolower($text), mb_strtolower($standard)) !== false) {
            return true;
        }

        foreach (['standart', 'standard', 'стандарт'] as $needle) {
            if (mb_stripos(mb_strtolower($text), $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function money(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
