<?php

namespace App\Filament\Bread;

use Tiptap\Core\Node;

class BreadImageFigure extends Node
{
    public static $name = 'breadImageFigure';

    public function parseHTML(): array { return [['tag' => 'figure']]; }
    public function renderHTML($node, $HTMLAttributes = []): array { return ['figure', ['class' => 'image'], 0]; }
}
