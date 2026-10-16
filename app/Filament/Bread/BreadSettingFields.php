<?php

namespace App\Filament\Bread;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Field;

class BreadSettingFields
{
    public function details(object $setting): array
    {
        $details = json_decode($setting->details ?: '{}', true);
        return is_array($details) ? $details : [];
    }

    public function options(array $details): array
    {
        return is_array($details['options'] ?? null) ? array_map(fn ($label) => is_scalar($label) ? (string) $label : '', $details['options']) : [];
    }

    public function state(object $setting): mixed
    {
        if ($setting->type === 'checkbox') { return $setting->value == 1; }
        if (! in_array($setting->type, ['select_dropdown', 'radio_btn'], true)) { return $setting->value; }
        $details = $this->details($setting);
        $value = empty($setting->value) ? null : $setting->value;
        $selected = null;
        foreach ($this->options($details) as $key => $label) {
            if (($value === null && isset($details['default']) && $details['default'] == $key) || $value == $key) { $selected = (string) $key; }
        }
        return $selected ?? ($setting->type === 'select_dropdown' ? array_key_first($this->options($details)) : null);
    }

    public function component(object $setting, bool $sourceMode, string $valueName = 'value', string $uploadName = 'replacement'): Field
    {
        $details = $this->details($setting);
        return match ($setting->type) {
            'text_area' => Textarea::make($valueName)->rows(6)->rules(['nullable', 'string']),
            'rich_text_box' => app(BreadRichEditor::class)->component($valueName, $details, 'settings', $sourceMode),
            'code_editor' => app(BreadCodeEditor::class)->component($valueName, $details, new class extends \Illuminate\Database\Eloquent\Model { protected $table = 'settings'; }),
            'checkbox' => Toggle::make($valueName)->helperText(is_scalar($details['on'] ?? null) && is_scalar($details['off'] ?? null) ? (string) $details['on'].' / '.(string) $details['off'] : null),
            'radio_btn' => Radio::make($valueName)->options($this->options($details)),
            'select_dropdown' => Select::make($valueName)->options($this->options($details))->selectablePlaceholder(false),
            'image', 'file' => $this->upload($setting, $uploadName),
            default => TextInput::make($valueName)->rules(['nullable', 'string']),
        };
    }

    private function upload(object $setting, string $name): BreadFileUpload
    {
        $upload = BreadFileUpload::make($name)->disk('public')->directory('settings/'.date('FY'))
            ->storeFiles(false)->maxSize(10240)->helperText('Оставьте пустым, чтобы сохранить текущий файл.');
        return $setting->type === 'image' ? $upload->image()->compactImagePreviews() : $upload;
    }
}
