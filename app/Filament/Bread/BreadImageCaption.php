<?php

namespace App\Filament\Bread;

use Tiptap\Core\Node;

class BreadImageCaption extends Node
{
    public static $name = 'breadImageCaption';

    public function parseHTML(): array { return [['tag' => 'figcaption']]; }
    public function renderHTML($node, $HTMLAttributes = []): array { return ['figcaption', [], 0]; }
}
