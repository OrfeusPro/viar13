<?php

namespace App\Filament\Bread;

use Filament\Forms\Components\RichEditor;
use Illuminate\Validation\ValidationException;

class BreadRichEditor
{
    private const TOOLS = ['bold' => 'bold', 'italic' => 'italic', 'underline' => 'underline', 'strikethrough' => 'strike', 'subscript' => 'subscript', 'superscript' => 'superscript', 'link' => 'link', 'h2' => 'h2', 'h3' => 'h3', 'alignleft' => 'alignStart', 'aligncenter' => 'alignCenter', 'alignright' => 'alignEnd', 'blockquote' => 'blockquote', 'bullist' => 'bulletList', 'numlist' => 'orderedList', 'table' => 'table', 'undo' => 'undo', 'redo' => 'redo'];

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

    public function component(string $field, array $details): RichEditor
    {
        $editor = RichEditor::make($field);
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
