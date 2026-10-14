<?php

namespace App\Filament\Bread;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Validation\ValidationException;

class BreadImageRowPlugin implements RichContentPlugin
{
    public function getTipTapPhpExtensions(): array { return [new BreadImageRow, new BreadImageFigure, new BreadImageCaption]; }
    public function getTipTapJsExtensions(): array { return [asset('js/bread-image-row.js').'?v=8', asset('js/bread-image-figure.js').'?v=1', asset('js/bread-image-caption.js').'?v=2']; }
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('breadImageRow')->label('image-row: объединить картинки')->icon('heroicon-o-photo')
                ->jsHandler('$getEditor()?.chain().focus().mergeBreadImageRow().run()'),
            RichEditorTool::make('breadImageProperties')->label('Свойства картинки')->icon('heroicon-o-adjustments-horizontal')
                ->action(arguments: '(() => { const e = $getEditor(), p = e.state.selection.$from; for (let d = p.depth; d > 0; d--) { if (p.node(d).type.name === \'breadImageFigure\') return { attributes: p.node(d).firstChild.attrs, caption: true, caption_text: p.node(d).lastChild.textContent }; } return { attributes: e.getAttributes(\'image\'), caption: false, caption_text: \'\' }; })()')->activeKey('image'),
        ];
    }
    public function getEditorActions(): array
    {
        return [Action::make('breadImageProperties')->label('Свойства картинки')->modalHeading('Свойства картинки')
            ->modalSubmitActionLabel('Применить')->modalCancelActionLabel('Отмена')
            ->fillForm(fn (array $arguments): array => array_intersect_key($arguments['attributes'] ?? [], array_flip(['src', 'alt', 'title', 'width', 'height'])) + ['caption' => $arguments['caption'] ?? false, 'caption_text' => $arguments['caption_text'] ?? '', 'constrain' => true])
            ->schema([
                TextInput::make('src')->label('Адрес картинки')->required()->maxLength(2048)->rules([fn () => function ($attribute, $value, $fail) {
                    if (! is_string($value) || preg_match('/[\x00-\x20\\\\]/', $value) || ! preg_match('~^(?:https?://[^/]+(?:/|$)|/(?!/))~i', $value)) { $fail('Укажите HTTP(S) URL или локальный путь от /, без пробелов.'); }
                }]),
                TextInput::make('alt')->label('ALT')->maxLength(1000),
                TextInput::make('title')->label('TITLE')->maxLength(1000),
                ...array_map(fn (string $name) => TextInput::make($name)->label($name === 'width' ? 'Ширина' : 'Высота')
                    ->maxLength(32)->rules(['nullable', 'regex:/^\d+(?:\.\d+)?(?:%|px|em|rem|vw|vh|vmin|vmax|pt|pc|cm|mm|in|ch|ex)?$/iD']), ['width', 'height']),
                Toggle::make('constrain')->label('Сохранять пропорции')->default(true)->helperText('При изменении одного размера второй рассчитывается по прежним размерам в пикселях.'),
                Toggle::make('caption')->label('Подпись под картинкой'),
                TextInput::make('caption_text')->label('Текст подписи')->maxLength(1000),
            ])
            ->action(function (array $arguments, array $data, RichEditor $component): void {
                $attributes = array_intersect_key($data, array_flip(['src', 'alt', 'title', 'width', 'height']));
                $previous = $arguments['attributes'] ?? [];
                if (($previous['src'] ?? null) !== $attributes['src'] && filled($previous['id'] ?? null) && data_get($component->getLivewire(), "componentFileAttachments.{$component->getStatePath()}.{$previous['id']}") !== null) {
                    $livewire = $component->getLivewire();
                    $path = $livewire->getSchema($livewire->getMountedActionSchemaName())->getStatePath().'.src';
                    throw ValidationException::withMessages([$path => 'Сначала сохраните загруженную картинку, затем меняйте её URL.']);
                }
                foreach (['width', 'height'] as $name) { $attributes[$name] = filled($attributes[$name] ?? null) ? $attributes[$name] : null; }
                $component->runCommands([EditorCommand::make('applyBreadImageProperties', [$attributes, (bool) ($data['caption'] ?? false), $data['caption_text'] ?? '', (bool) ($data['constrain'] ?? true)])], editorSelection: $arguments['editorSelection']);
            })];
    }
}
