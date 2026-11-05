<?php

namespace App\Services;

use App\Models\AProductionTime;
use App\Models\CanvasHeader;
use App\Models\CanvasPhotoImprove;
use App\Models\CanvasRam;
use App\Models\GalleryBox;
use App\Models\GalleryDecoration;
use App\Models\GalleryHolst;
use Illuminate\Support\Facades\View;

class CanvasOrderQuote
{
    public static function calculate(array $input, ?CanvasRam $frame): array
    {
        $invalid = fn () => abort(422, __('calculator.order_invalid'));
        $fields = [1 => 'sizes_30x40', 2 => 'sizes_38x38', 3 => 'sizes_40x30', 4 => 'sizes_60x30'];
        $field = $fields[$input['formId'] ?? 0] ?? null;
        $size = $input['sizeId'] ?? '';
        if ((! empty($input['ram_id']) && ! $frame) || ($frame?->price_per_meter !== null && ! $frame->calculator_enabled) || ! $field || ! preg_match('/^(\d+(?:\.\d+)?)x(\d+(?:\.\d+)?)$/D', $size, $dimensions)) {
            $invalid();
        }

        $header = CanvasHeader::first();
        $base = null;
        $label = '';
        foreach (explode(',', $header?->{$field} ?? '') as $entry) {
            if (preg_match('/^\s*([\d.]+x[\d.]+)\[([\d.]+)(?:-([\d.]+))?\]([hst])?/i', $entry, $match) && $match[1] === $size) {
                $base = (float) (($match[3] ?? '') !== '' ? $match[3] : $match[2]);
                $label = strtolower($match[4] ?? '');
                break;
            }
        }

        $boxes = json_decode($input['boxIds'] ?? '[]', true);
        $boxId = is_array($boxes) && count($boxes) === 1 ? reset($boxes) : null;
        $box = filter_var($boxId, FILTER_VALIDATE_INT) ? GalleryBox::find($boxId) : null;
        $material = GalleryHolst::find($input['canvasId'] ?? 0);
        $decoration = GalleryDecoration::find($input['decorationId'] ?? 0);
        $improvementId = filter_var($input['improvement_id'] ?? 0, FILTER_VALIDATE_INT);
        $improvement = $improvementId > 0 ? CanvasPhotoImprove::where('is_active', 1)->find($improvementId) : null;
        $execution = (int) ($input['executionId'] ?? 0);
        if ($base === null || ! $material || ! $decoration || ! $box || $improvementId === false ||
            $improvementId < 0 || ($improvementId && ! $improvement) || ! in_array($execution, [1, 2], true)) {
            $invalid();
        }

        $production = AProductionTime::where('category', 'canvas')->first();
        $terms = (float) ($input['terms_price'] ?? 0);
        if (! $production || ! in_array($terms, [(float) $production->standart_price, (float) $production->express_price], true)) {
            $invalid();
        }

        $width = (float) $dimensions[1];
        $height = (float) $dimensions[2];
        $meters = 2 * ($width + $height) / 100;
        $rate = $frame?->price_per_meter;
        $framePrice = $rate !== null
            ? round($meters * (float) $rate, 2)
            : (float) preg_replace('/\s+/', '', (string) $frame?->price);
        // The existing editor uses whole centimetres for decoration and exact dimensions for oil and frames.
        $decorArea = (int) $width * (int) $height / 10000;
        $coefficient = $decorArea <= .4 ? $decoration->coef_sm
            : ($decorArea <= 1 ? $decoration->coef_md : ($decorArea >= 1.01 ? $decoration->coef_lg : 0));
        $decorPrice = $decorArea * (float) $coefficient ?: (float) $decoration->price;
        $area = $width * $height / 10000;
        $oilPrice = $execution === 1 ? $area * ($area <= .4 ? 80 : ($area <= 1 ? 60 : ($area >= 1.01 ? 50 : 0))) : 0;
        $basePrice = round($base * (float) View::shared('contry_mult', 1) * (float) $material->price, 2);
        $total = round(round($basePrice + $terms + $oilPrice, 2) + $decorPrice + (float) $box->price + (float) ($improvement?->price ?? 0), 2) + $framePrice;
        $total = round($total, 2);

        abort_if(abs($total - (float) ($input['price'] ?? -1)) > .001, 422,
            __('calculator.order_price_changed').' '.number_format($total, 2, ',', ' ').' €');

        return [
            'price' => $total,
            'price_label' => $label,
            'terms_price' => $terms,
            'canvas_frame_quote' => ['meters' => $rate !== null ? $meters : null, 'rate' => $rate !== null ? (float) $rate : null, 'price' => $framePrice],
        ];
    }
}
