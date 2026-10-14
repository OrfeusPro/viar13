<?php

namespace App\Filament\Bread;

use Tiptap\Core\Node;

class BreadImageRow extends Node
{
    public static $name = 'breadImageRow';
    public static $priority = 1100;

    public static function dimension(mixed $value): bool
    {
        return is_string($value) && strlen($value) <= 32
            && preg_match('/^\d+(?:\.\d+)?(?:%|px|em|rem|vw|vh|vmin|vmax|pt|pc|cm|mm|in|ch|ex)?$/iD', trim($value));
    }

    public function parseHTML(): array
    {
        return [['tag' => 'p', 'getAttrs' => fn ($node) => $node->getAttribute('class') === 'image-row' ? null : false]];
    }

    public function renderHTML($node, $HTMLAttributes = []): array
    {
        return ['p', ['class' => 'image-row'], 0];
    }
}
