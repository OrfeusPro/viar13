<?php

namespace App\Filament\Bread;

use Tiptap\Core\Extension;

class BreadLink extends Extension
{
    public static $name = 'breadLink';

    public function addGlobalAttributes(): array
    {
        return [['types' => ['link'], 'attributes' => ['title' => [
            'default' => null,
            'parseHTML' => fn ($node) => $node->getAttribute('title') ?: null,
            'renderHTML' => fn ($attributes) => filled($attributes->title ?? null) ? ['title' => $attributes->title] : [],
        ]]]];
    }
}
