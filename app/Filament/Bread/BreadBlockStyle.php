<?php

namespace App\Filament\Bread;

use Tiptap\Core\Extension;
use Tiptap\Utils\InlineStyle;

class BreadBlockStyle extends Extension
{
    public static $name = 'breadBlockStyle';

    public static function indent(mixed $value): ?int
    {
        if (! is_string($value) || ! preg_match('/^(\d{1,6})px$/D', trim($value), $parts)) { return null; }
        $pixels = (int) $parts[1];
        return $pixels % 40 === 0 ? $pixels : null;
    }

    public function addGlobalAttributes(): array
    {
        return [[
            'types' => ['paragraph', 'heading', 'codeBlock'],
            'attributes' => ['breadIndent' => [
                'default' => 0,
                'parseHTML' => fn ($node) => self::indent(InlineStyle::getAttribute($node, 'padding-left')) ?? 0,
                'renderHTML' => fn ($attributes) => is_int($attributes->breadIndent ?? null) && $attributes->breadIndent > 0
                    && self::indent($attributes->breadIndent.'px') !== null ? ['style' => 'padding-left: '.$attributes->breadIndent.'px'] : [],
            ]],
        ]];
    }
}
