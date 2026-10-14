<?php

namespace App\Filament\Bread;

use Tiptap\Core\Extension;

class BreadTableCompatibility extends Extension
{
    public static $name = 'breadTableCompatibility';

    public function addGlobalAttributes(): array
    {
        return [['types' => ['tableCell', 'tableHeader'], 'attributes' => ['breadColwidth' => [
            'default' => null,
            'parseHTML' => function ($node) {
                $value = $node->getAttribute('colwidth') ?: $node->getAttribute('data-colwidth');
                return preg_match('/^[1-9]\d{0,5}(?:,[1-9]\d{0,5})*$/D', $value) ? array_map('intval', explode(',', $value)) : null;
            },
            'renderHTML' => function ($attributes): array {
                $widths = $attributes->colwidth ?? $attributes->breadColwidth ?? null;
                return is_array($widths) && $widths !== [] ? ['colwidth' => implode(',', $widths)] : [];
            },
        ]]]];
    }
}
