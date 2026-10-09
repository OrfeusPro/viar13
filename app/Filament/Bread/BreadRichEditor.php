<?php

namespace App\Filament\Bread;

use Filament\Forms\Components\RichEditor;
use Filament\Actions\Action;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Illuminate\Validation\ValidationException;

class BreadRichEditor
{
    public function requiresSource(?string $html): bool
    {
        if (! filled($html)) { return false; }
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $tags = ['p', 'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'sub', 'sup', 'a', 'h2', 'h3', 'blockquote', 'pre', 'code', 'ul', 'ol', 'li', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'img', 'br', 'hr'];
            foreach ($document->getElementsByTagName('*') as $element) {
                if (! in_array(strtolower($element->tagName), $tags, true)) { return true; }
                foreach ($element->attributes as $attribute) {
                    if (! in_array(strtolower($attribute->name), ['href', 'target', 'rel', 'title', 'src', 'alt', 'width', 'height'], true)) { return true; }
                }
            }
            return false;
        } finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
    }

    private const TOOLS = ['bold' => 'bold', 'italic' => 'italic', 'underline' => 'underline', 'strikethrough' => 'strike', 'subscript' => 'subscript', 'superscript' => 'superscript', 'link' => 'link', 'h2' => 'h2', 'h3' => 'h3', 'alignleft' => 'alignStart', 'aligncenter' => 'alignCenter', 'alignright' => 'alignEnd', 'blockquote' => 'blockquote', 'bullist' => 'bulletList', 'numlist' => 'orderedList', 'table' => 'table', 'undo' => 'undo', 'redo' => 'redo', 'code' => 'breadSource', 'image' => 'attachFiles', 'forecolor' => 'textColor'];

    public function validate(array $details): void
    {
        $options = $details['tinymceOptions'] ?? [];
        $bad = fn () => throw ValidationException::withMessages(['details' => 'tinymceOptions: поддерживаются height 100..2000, placeholder и toolbar из доступных команд Filament.']);
        if (! is_array($options) || array_diff(array_keys($options), ['height', 'placeholder', 'toolbar']) !== []) { $bad(); }
        if (isset($options['height']) && (filter_var($options['height'], FILTER_VALIDATE_INT) === false || $options['height'] < 100 || $options['height'] > 2000)) { $bad(); }
        if (isset($options['placeholder']) && (! is_string($options['placeholder']) || mb_strlen($options['placeholder']) > 1000)) { $bad(); }
        if (isset($options['toolbar']) && $options['toolbar'] !== false) {
            if (! is_string($options['toolbar']) || strlen($options['toolbar']) > 1000) { $bad(); }
            foreach (preg_split('/[\s|]+/', trim($options['toolbar']), -1, PREG_SPLIT_NO_EMPTY) as $tool) { if (! isset(self::TOOLS[$tool])) { $bad(); } }
        }
    }

    public function component(string $field, array $details, ?string $table = null, bool $sourceMode = false): RichEditor|CodeEditor
    {
        if ($sourceMode) {
            return CodeEditor::make($field)->language(Language::Html)->wrap()->rules(['nullable', 'string', 'max:2000000'])
                ->helperText('Режим HTML сохраняет разметку без преобразования визуальным редактором. Изменения записываются общим сохранением формы.');
        }
        $editor = RichEditor::make($field);
        $editor->fileAttachmentsDisk('public')->fileAttachmentsVisibility('public')
            ->fileAttachmentsDirectory(($table ?? 'bread-richtext').'/'.date('FY'))
            ->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp', 'image/avif'])
            ->fileAttachmentsMaxSize(10240);
        $editor->tools([
            RichEditorTool::make('breadSource')->label('HTML-код')->icon('heroicon-o-code-bracket')->action(),
        ])->registerActions([
            Action::make('breadSource')->label('HTML-код')->modalHeading('Редактирование HTML')
                ->modalDescription('Изменения применяются к черновику. Для записи нажмите «Сохранить» в основной форме.')
                ->modalSubmitActionLabel('Применить')->modalCancelActionLabel('Отмена')
                ->fillForm(fn (RichEditor $component): array => ['html' => is_string($component->getRawState()) ? $component->getRawState() : $component->getState()])
                ->schema([CodeEditor::make('html')->label('HTML')->language(Language::Html)->wrap()->rules(['nullable', 'string', 'max:2000000'])])
                ->action(function (array $data, RichEditor $component): void {
                    $component->getTipTapEditor()->setContent($component->getRawState() ?? [])
                        ->descendants(function (object &$node) use ($component): void {
                            if ($node->type === 'image' && filled($node->attrs->id ?? null)
                                && $component->getUploadedFileAttachment($node->attrs->id)) {
                                $livewire = $component->getLivewire();
                                $path = $livewire->getSchema($livewire->getMountedActionSchemaName())->getStatePath().'.html';
                                throw ValidationException::withMessages([$path => 'Сначала сохраните загруженные изображения в основной форме, затем откройте режим HTML.']);
                            }
                        });
                    $html = $data['html'] ?? '';
                    $livewire = $component->getLivewire();
                    $livewire->enableRichSource($component->getName());
                    $component->rawState($html);
                }),
        ]);
        $editor->toolbarButtons(fn (RichEditor $component): array => [...$component->getDefaultToolbarButtons(), ['textColor', 'breadSource']]);
        try { $this->validate($details); }
        catch (ValidationException) { return $editor->helperText('Часть настроек TinyMCE не поддерживается редактором Filament. Используются стандартные инструменты.'); }
        $options = $details['tinymceOptions'] ?? [];
        if (isset($options['placeholder'])) { $editor->placeholder($options['placeholder']); }
        if (isset($options['height'])) { $editor->extraInputAttributes(['style' => 'min-height:'.(int) $options['height'].'px']); }
        if (array_key_exists('toolbar', $options)) {
            $groups = [];
            foreach ($options['toolbar'] === false ? [] : explode('|', $options['toolbar']) as $group) {
                $buttons = array_map(fn ($tool) => self::TOOLS[$tool], preg_split('/\s+/', trim($group), -1, PREG_SPLIT_NO_EMPTY));
                if ($buttons !== []) { $groups[] = $buttons; }
            }
            $editor->toolbarButtons($groups);
        }
        return $editor;
    }
}
