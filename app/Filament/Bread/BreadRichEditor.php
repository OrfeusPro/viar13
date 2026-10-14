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
    public function requiresSource(?string $html, ?string $table = null): bool
    {
        if (! filled($html)) { return false; }
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $tags = ['p', 'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'sub', 'sup', 'a', 'h2', 'h3', 'blockquote', 'pre', 'code', 'ul', 'ol', 'li', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'img', 'br', 'hr'];
            if ($table === 'blog_posts') { $tags = [...$tags, 'span', 'h1', 'h4', 'h5', 'h6', 'figure', 'figcaption']; }
            foreach ($document->getElementsByTagName('*') as $element) {
                if (! in_array(strtolower($element->tagName), $tags, true)) { return true; }
                if ($table === 'blog_posts' && $element->tagName === 'figure') {
                    $children = array_values(array_filter(iterator_to_array($element->childNodes), fn ($child) => ! ($child instanceof \DOMText && trim($child->textContent) === '')));
                    if ($element->getAttribute('class') !== 'image' || count($children) !== 2 || ! ($children[0] instanceof \DOMElement) || ! $this->isImageOrImageLink($children[0]) || ! ($children[1] instanceof \DOMElement) || $children[1]->tagName !== 'figcaption') { return true; }
                }
                if ($table === 'blog_posts' && $element->tagName === 'figcaption' && $element->parentNode?->nodeName !== 'figure') { return true; }
                if ($table === 'blog_posts' && $element->tagName === 'figcaption') {
                    foreach ($element->getElementsByTagName('*') as $child) {
                        if (! in_array($child->tagName, ['strong', 'b', 'em', 'i', 'u', 's', 'strike', 'sub', 'sup', 'a', 'span', 'code', 'br'], true)) { return true; }
                    }
                }
                if ($table === 'blog_posts' && $element->tagName === 'p' && $element->getAttribute('class') === 'image-row') {
                    $images = 0;
                    foreach ($element->childNodes as $child) {
                        if ($child instanceof \DOMElement && $this->isImageOrImageLink($child)) { $images++; continue; }
                        if ($child instanceof \DOMText && trim($child->textContent) === '') { continue; }
                        return true;
                    }
                    if ($images === 0) { return true; }
                }
                foreach ($element->attributes as $attribute) {
                    if (in_array($element->tagName, ['td', 'th'], true)) {
                        if (in_array($attribute->name, ['rowspan', 'colspan'], true) && preg_match('/^[1-9]\d{0,3}$/D', $attribute->value)) { continue; }
                        if (in_array($attribute->name, ['data-colwidth', 'colwidth'], true) && preg_match('/^[1-9]\d{0,5}(?:,[1-9]\d{0,5})*$/D', $attribute->value)
                            && count(explode(',', $attribute->value)) === max(1, (int) $element->getAttribute('colspan'))) { continue; }
                    }
                    // Native table schema only retains spans and data-colwidth.
                    // Legacy table/cell dimensions must stay in HTML mode.
                    if (in_array($element->tagName, ['table', 'tr', 'td', 'th', 'thead', 'tbody'], true)) { return true; }
                    if ($table === 'blog_posts' && $element->tagName === 'figure' && (($attribute->name === 'class' && $attribute->value === 'image') || ($attribute->name === 'contenteditable' && $attribute->value === 'false'))) { continue; }
                    if ($table === 'blog_posts' && $element->tagName === 'figcaption' && $attribute->name === 'contenteditable' && $attribute->value === 'true') { continue; }
                    if ($table === 'blog_posts' && $element->tagName === 'p' && $attribute->name === 'class' && $attribute->value === 'image-row') { continue; }
                    if ($table === 'blog_posts' && $element->tagName === 'img' && in_array($attribute->name, ['data-id', 'loading'], true)) { continue; }
                    if ($table === 'blog_posts' && strtolower($attribute->name) === 'style' && $this->supportsBlogStyle($element)) { continue; }
                    if (! in_array(strtolower($attribute->name), ['href', 'target', 'rel', 'title', 'src', 'alt', 'width', 'height'], true)) { return true; }
                }
            }
            return false;
        } finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
    }

    private function supportsBlogStyle(\DOMElement $element): bool
    {
        foreach (explode(';', $element->getAttribute('style')) as $declaration) {
            if (trim($declaration) === '') { continue; }
            [$name, $value] = array_pad(explode(':', $declaration, 2), 2, '');
            $name = strtolower(trim($name)); $value = trim($value);
            if ($element->tagName === 'img') {
                if (! in_array($name, ['width', 'height'], true) || ! BreadImageRow::dimension($value)) { return false; }
                continue;
            }
            $supported = $element->tagName === 'span'
                ? match ($name) {
                    'font-size' => in_array($value, BreadTextStyle::SIZES, true),
                    'background-color' => BreadTextStyle::color($value) !== null,
                    default => false,
                }
                : in_array($element->tagName, ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre'], true) && match ($name) {
                    'text-align' => $element->tagName !== 'pre' && in_array($value, ['start', 'center', 'end', 'justify'], true),
                    'padding-left' => BreadBlockStyle::indent($value) !== null,
                    default => false,
                };
            if (! $supported) { return false; }
        }
        return true;
    }

    private function isImageOrImageLink(\DOMElement $element): bool
    {
        if ($element->tagName === 'img') { return true; }
        if ($element->tagName !== 'a' || ! $element->hasAttribute('href')) { return false; }
        $children = array_values(array_filter(iterator_to_array($element->childNodes), fn ($child) => ! ($child instanceof \DOMText && trim($child->textContent) === '')));
        return count($children) === 1 && $children[0] instanceof \DOMElement && $children[0]->tagName === 'img';
    }

    private const TOOLS = ['bold' => 'bold', 'italic' => 'italic', 'underline' => 'underline', 'strikethrough' => 'strike', 'subscript' => 'subscript', 'superscript' => 'superscript', 'link' => 'link', 'h2' => 'h2', 'h3' => 'h3', 'alignleft' => 'alignStart', 'aligncenter' => 'alignCenter', 'alignright' => 'alignEnd', 'alignjustify' => 'alignJustify', 'removeformat' => 'clearFormatting', 'insertBrButton' => 'breadBreak', 'blockquote' => 'blockquote', 'bullist' => 'bulletList', 'numlist' => 'orderedList', 'table' => 'table', 'undo' => 'undo', 'redo' => 'redo', 'code' => 'breadSource', 'image' => 'attachFiles', 'forecolor' => 'textColor', 'fontsizeselect' => 'breadFontSize', 'backcolor' => 'breadBackgroundColor', 'formatselect' => 'breadFormat', 'styleselect' => 'breadFormat', 'indent' => 'breadIndent', 'outdent' => 'breadOutdent', 'mergeImageRowButton' => 'breadImageRow'];

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
        $editor = RichEditor::make($field)->plugins([new BreadTextStylePlugin, new BreadTablePlugin]);
        $toolbar = $details['tinymceOptions']['toolbar'] ?? null;
        if ($table === 'blog_posts') { $editor->plugins([new BreadLinkPlugin]); }
        if ($table === 'blog_posts' || (is_string($toolbar) && preg_match('/\b(?:formatselect|styleselect|indent|outdent)\b/', $toolbar))) {
            $editor->plugins([new BreadBlockStylePlugin])->extraAttributes(['data-bread-blog-editor' => 'true']);
        }
        if ($table === 'blog_posts' || (is_string($toolbar) && str_contains($toolbar, 'mergeImageRowButton'))) {
            $editor->plugins([new BreadImageRowPlugin])->extraAttributes(['data-bread-blog-editor' => 'true']);
        }
        $editor->fileAttachmentsDisk('public')->fileAttachmentsVisibility('public')
            ->fileAttachmentsDirectory(($table ?? 'bread-richtext').'/'.date('FY'))
            ->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp', 'image/avif'])
            ->fileAttachmentsMaxSize(10240);
        $editor->tools([
            RichEditorTool::make('breadSource')->label('HTML-код')->icon('heroicon-o-code-bracket')->action(),
            RichEditorTool::make('breadBreak')->label('Перенос строки <br>')->icon('heroicon-o-arrow-turn-down-left')
                ->jsHandler('$getEditor()?.chain().focus().setHardBreak().run()'),
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
        $editor->toolbarButtons(function (RichEditor $component) use ($table): array {
            $groups = [...$component->getDefaultToolbarButtons(), ['textColor', 'breadSource']];
            if ($table === 'blog_posts') {
                $groups = array_map(fn ($group) => array_map(fn ($tool) => $tool === 'link' ? 'breadLink' : $tool, $group), $groups);
                $groups[2][] = 'alignJustify';
                $groups[] = ['clearFormatting', 'breadBreak'];
                $groups[] = ['breadFontSize', 'breadBackgroundColor'];
                $groups[] = ['breadFormat', 'breadOutdent', 'breadIndent'];
                $groups[] = ['breadImageRow', 'breadImageProperties'];
            }
            return $groups;
        });
        try { $this->validate($details); }
        catch (ValidationException) { return $editor->helperText('Часть настроек TinyMCE не поддерживается редактором Filament. Используются стандартные инструменты.'); }
        $options = $details['tinymceOptions'] ?? [];
        if (isset($options['placeholder'])) { $editor->placeholder($options['placeholder']); }
        if (isset($options['height'])) { $editor->extraInputAttributes(['style' => 'min-height:'.(int) $options['height'].'px']); }
        if (array_key_exists('toolbar', $options)) {
            $groups = [];
            foreach ($options['toolbar'] === false ? [] : explode('|', $options['toolbar']) as $group) {
                $buttons = array_map(fn ($tool) => $table === 'blog_posts' && $tool === 'link' ? 'breadLink' : self::TOOLS[$tool], preg_split('/\s+/', trim($group), -1, PREG_SPLIT_NO_EMPTY));
                if ($buttons !== []) { $groups[] = $buttons; }
            }
            $editor->toolbarButtons($groups);
        }
        return $editor;
    }
}
