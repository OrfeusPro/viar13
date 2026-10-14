<?php

namespace App\Filament\Bread;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\TextInput;

class BreadLinkPlugin implements RichContentPlugin
{
    public function getTipTapPhpExtensions(): array { return [new BreadLink]; }
    public function getTipTapJsExtensions(): array { return [asset('js/bread-link.js').'?v=5']; }
    public function getEditorTools(): array
    {
        return [RichEditorTool::make('breadLink')->label('Ссылка')->icon('heroicon-o-link')->activeKey('link')
            ->action(arguments: '$getEditor().storage.breadLink.getArguments($getEditor())')];
    }
    public function getEditorActions(): array
    {
        return [Action::make('breadLink')->label('Ссылка')->modalHeading('Ссылка')->modalSubmitActionLabel('Применить')->modalCancelActionLabel('Отмена')
            ->fillForm(fn (array $arguments): array => ['url' => $arguments['href'] ?? null, 'text' => $arguments['text'] ?? '', 'title' => $arguments['title'] ?? '', 'newTab' => ($arguments['target'] ?? null) === '_blank'])
            ->schema(fn (array $arguments): array => [
                TextInput::make('url')->label('Адрес')->maxLength(2048)->helperText('Очистите адрес, чтобы удалить ссылку.')->rules([fn () => function ($attribute, $value, $fail) {
                    if (filled($value) && (preg_match('/[\x00-\x20]/', $value) || (preg_match('/^([a-z][a-z0-9+.-]*):/i', $value, $parts) && ! in_array(strtolower($parts[1]), ['http', 'https', 'ftp', 'ftps', 'mailto', 'tel', 'callto', 'sms', 'cid', 'xmpp'], true)))) { $fail('Недопустимый адрес ссылки.'); }
                }]),
                TextInput::make('text')->label('Текст')->maxLength(10000)->visible($arguments['canEditText'] ?? true),
                TextInput::make('title')->label('TITLE')->maxLength(1000),
                Checkbox::make('newTab')->label('Открывать в новой вкладке'),
            ])->action(function (array $arguments, array $data, RichEditor $component): void {
                $rel = array_values(array_filter(preg_split('/\s+/', trim($arguments['rel'] ?? ''), -1, PREG_SPLIT_NO_EMPTY), fn ($token) => $token !== 'noopener'));
                if ($data['newTab'] ?? false) { $rel[] = 'noopener'; }
                $rel = array_unique($rel); sort($rel);
                $component->runCommands([
                    EditorCommand::make('applyBreadLink', [['href' => $data['url'] ?? null, 'title' => $data['title'] ?? null, 'target' => ($data['newTab'] ?? false) ? '_blank' : null, 'rel' => $rel ? implode(' ', $rel) : null], $data['text'] ?? '', $arguments['text'] ?? '', (bool) ($arguments['canEditText'] ?? true)]),
                ], editorSelection: $arguments['editorSelection']);
            })];
    }
}
