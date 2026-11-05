<?php

namespace App\Filament\Bread;

use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\Select;

class BreadTextStylePlugin implements RichContentPlugin
{
    public function getTipTapPhpExtensions(): array { return [new BreadTextStyle]; }
    public function getTipTapJsExtensions(): array { return [asset('js/bread-text-style.js').'?v=1']; }

    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('breadFontSize')->label('Размер шрифта')->icon('heroicon-o-arrows-up-down')
                ->action(arguments: '{ value: $getEditor().getAttributes(\'breadTextStyle\').fontSize ?? null }'),
            RichEditorTool::make('breadBackgroundColor')->label('Цвет фона текста')->icon('heroicon-o-paint-brush')
                ->action(arguments: '{ value: $getEditor().getAttributes(\'breadTextStyle\').backgroundColor ?? null }'),
        ];
    }

    public function getEditorActions(): array
    {
        return [
            Action::make('breadFontSize')->label('Размер шрифта')->modalHeading('Размер шрифта')
                ->modalSubmitActionLabel('Применить')->modalCancelActionLabel('Отмена')
                ->fillForm(fn (array $arguments): array => ['value' => $arguments['value'] ?? null])
                ->schema([Select::make('value')->label('Размер')->placeholder('По умолчанию')
                    ->options(array_combine(BreadTextStyle::SIZES, BreadTextStyle::SIZES))->rules(['nullable', 'in:'.implode(',', BreadTextStyle::SIZES)])])
                ->action(fn (array $arguments, array $data, RichEditor $component) => $component->runCommands(
                    [EditorCommand::make('setBreadFontSize', [$data['value'] ?? null])], editorSelection: $arguments['editorSelection'])),
            Action::make('breadBackgroundColor')->label('Цвет фона текста')->modalHeading('Цвет фона текста')
                ->modalDescription('Очистите цвет, чтобы убрать фон с выделенного текста.')
                ->modalSubmitActionLabel('Применить')->modalCancelActionLabel('Отмена')
                ->fillForm(fn (array $arguments): array => ['value' => BreadTextStyle::color($arguments['value'] ?? null)])
                ->schema([ColorPicker::make('value')->label('Цвет')->hex()->rules(['nullable', 'regex:/^#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?$/D'])])
                ->action(fn (array $arguments, array $data, RichEditor $component) => $component->runCommands(
                    [EditorCommand::make('setBreadBackgroundColor', [BreadTextStyle::color($data['value'] ?? null)])], editorSelection: $arguments['editorSelection'])),
        ];
    }
}
