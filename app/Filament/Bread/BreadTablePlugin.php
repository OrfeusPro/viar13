<?php

namespace App\Filament\Bread;

use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;

class BreadTablePlugin implements RichContentPlugin
{
    public function getTipTapPhpExtensions(): array { return [new BreadTableCompatibility]; }
    public function getTipTapJsExtensions(): array { return [asset('js/bread-table.js').'?v=1']; }
    public function getEditorTools(): array { return []; }
    public function getEditorActions(): array { return []; }
}
