<?php

namespace App\Filament\Bread;

use Tiptap\Core\Mark;
use Tiptap\Utils\InlineStyle;

class BreadTextStyle extends Mark
{
    public static $name = 'breadTextStyle';

    public const SIZES = ['8pt', '10pt', '12pt', '14pt', '18pt', '24pt', '36pt'];

    public static function color(mixed $value): ?string
    {
        if (! is_string($value)) { return null; }
        $value = strtolower(trim($value));
        if (preg_match('/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/D', $value)) { return $value; }
        if (preg_match('/^rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)$/D', $value, $parts)
            && max((int) $parts[1], (int) $parts[2], (int) $parts[3]) <= 255) {
            return sprintf('#%02x%02x%02x', $parts[1], $parts[2], $parts[3]);
        }
        return null;
    }

    public function parseHTML(): array
    {
        return [['tag' => 'span', 'getAttrs' => fn ($node) => in_array(InlineStyle::getAttribute($node, 'font-size'), self::SIZES, true)
            || self::color(InlineStyle::getAttribute($node, 'background-color')) !== null ? null : false]];
    }

    public function addAttributes(): array
    {
        return [
            'fontSize' => ['default' => null, 'parseHTML' => fn ($node) => in_array($size = InlineStyle::getAttribute($node, 'font-size'), self::SIZES, true) ? $size : null,
                'renderHTML' => fn () => []],
            'backgroundColor' => ['default' => null, 'parseHTML' => fn ($node) => self::color(InlineStyle::getAttribute($node, 'background-color')),
                'renderHTML' => fn () => []],
        ];
    }

    public function renderHTML($mark, $HTMLAttributes = []): array
    {
        $style = [];
        if (in_array($mark->attrs->fontSize ?? null, self::SIZES, true)) { $style[] = 'font-size: '.$mark->attrs->fontSize; }
        if (($color = self::color($mark->attrs->backgroundColor ?? null)) !== null) { $style[] = 'background-color: '.$color; }
        return ['span', $style === [] ? [] : ['style' => implode('; ', $style)], 0];
    }
}
