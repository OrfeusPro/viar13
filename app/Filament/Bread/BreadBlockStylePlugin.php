<?php

namespace App\Filament\Bread;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\Select;

class BreadBlockStylePlugin implements RichContentPlugin
{
    public const FORMATS = ['p' => 'Абзац', 'h1' => 'Заголовок 1', 'h2' => 'Заголовок 2', 'h3' => 'Заголовок 3',
        'h4' => 'Заголовок 4', 'h5' => 'Заголовок 5', 'h6' => 'Заголовок 6', 'pre' => 'Предварительно форматированный текст'];

    public function getTipTapPhpExtensions(): array { return [new BreadBlockStyle]; }
    public function getTipTapJsExtensions(): array { return [asset('js/bread-block-style.js').'?v=2', asset('js/bread-blog-heading.js').'?v=1']; }
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('breadFormat')->label('Формат текста')->icon('heroicon-o-bars-3-bottom-left')
                ->action(arguments: '{ value: $getEditor().isActive(\'codeBlock\') ? \'pre\' : ($getEditor().isActive(\'heading\') ? \'h\' + $getEditor().getAttributes(\'heading\').level : \'p\') }'),
            RichEditorTool::make('breadIndent')->label('Увеличить отступ')->icon('heroicon-o-arrow-right')
                ->jsHandler('$getEditor()?.chain().focus().increaseBreadIndent().run()'),
            RichEditorTool::make('breadOutdent')->label('Уменьшить отступ')->icon('heroicon-o-arrow-left')
                ->jsHandler('$getEditor()?.chain().focus().decreaseBreadIndent().run()'),
        ];
    }
    public function getEditorActions(): array
    {
        return [Action::make('breadFormat')->label('Формат текста')->modalHeading('Формат текста')
            ->modalSubmitActionLabel('Применить')->modalCancelActionLabel('Отмена')
            ->fillForm(fn (array $arguments): array => ['value' => $arguments['value'] ?? 'p'])
            ->schema([Select::make('value')->label('Формат')->options(self::FORMATS)->required()->rules(['in:'.implode(',', array_keys(self::FORMATS))])])
            ->action(fn (array $arguments, array $data, RichEditor $component) => $component->runCommands(
                [EditorCommand::make('setBreadFormat', [$data['value']])], editorSelection: $arguments['editorSelection']))];
    }
}
