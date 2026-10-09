<?php

namespace App\Filament\Bread;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TimePicker;
use Illuminate\Validation\ValidationException;

class BreadTemporal
{
    public function validate(string $type, array $details): void
    {
        if ($type !== 'time') { return; }
        if (isset($details['placeholder']) && (! is_string($details['placeholder']) || mb_strlen($details['placeholder']) > 5000)) {
            throw ValidationException::withMessages(['details' => 'placeholder времени должен быть текстом.']);
        }
        if (isset($details['default']) && $details['default'] !== '' && (! is_string($details['default'])
            || ! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $details['default']))) {
            throw ValidationException::withMessages(['details' => 'default времени: HH:MM или HH:MM:SS.']);
        }
    }

    public function component(string $field, string $type, array $details, string $label): DateTimePicker
    {
        $component = match ($type) {
            'date' => DatePicker::make($field)->format('Y-m-d')->displayFormat('Y-m-d')->placeholder($label)
                ->rules(['nullable', 'date_format:Y-m-d']),
            'timestamp' => DateTimePicker::make($field)->native(false)->seconds(false)
                ->format('Y-m-d H:i:s')->displayFormat('m/d/Y g:i A')->rules(['nullable', 'date_format:Y-m-d H:i:s']),
            default => TimePicker::make($field)->format('H:i:s')->rules(['nullable', 'date_format:H:i:s']),
        };
        $component->timezone(config('app.timezone'));
        try { $this->validate($type, $details); }
        catch (ValidationException) {
            return $component->disabled()->dehydrated(false)->helperText('Некорректные параметры времени. Значение сохранено без изменений.');
        }
        if ($type === 'time') { $component->placeholder($details['placeholder'] ?? $label); }
        return $component;
    }
}
