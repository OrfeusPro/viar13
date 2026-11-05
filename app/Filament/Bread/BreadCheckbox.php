<?php

namespace App\Filament\Bread;

use Filament\Forms\Components\Toggle;
use Illuminate\Validation\ValidationException;

class BreadCheckbox
{
    public function validate(array $details): void
    {
        $fail = fn () => throw ValidationException::withMessages(['details' => 'checked должен быть boolean (или 0/1, on/off, yes/no, true/false); on/off — текстовые подписи.']);
        if (array_key_exists('checked', $details) && (! is_scalar($details['checked']) || filter_var($details['checked'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null)) { $fail(); }
        foreach (['on', 'off'] as $key) {
            if (isset($details[$key]) && (! is_string($details[$key]) || mb_strlen($details[$key]) > 500)) { $fail(); }
        }
    }

    public function initial(array $details): bool
    {
        return filter_var($details['checked'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public function caption(mixed $value, array $details): string
    {
        if (isset($details['on'], $details['off']) && is_string($details['on']) && is_string($details['off'])) { return $value ? $details['on'] : $details['off']; }
        return $value ? 'Да' : 'Нет';
    }

    public function component(string $field, array $details): Toggle
    {
        $toggle = Toggle::make($field)->default($this->initial($details))->rules(['boolean']);
        try { $this->validate($details); }
        catch (ValidationException) { return $toggle->helperText('Параметры checkbox несовместимы; используйте переключатель вручную.'); }
        if (isset($details['on'], $details['off'])) { $toggle->live()->helperText(fn ($state) => $this->caption($state, $details)); }
        return $toggle;
    }
}
