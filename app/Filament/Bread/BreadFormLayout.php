<?php

namespace App\Filament\Bread;

use Illuminate\Validation\ValidationException;

class BreadFormLayout
{
    public const COLUMNS = ['default' => 1, 'lg' => 12];

    public function validate(array $details): void
    {
        if (isset($details['display']) && ! is_array($details['display'])) {
            throw ValidationException::withMessages(['details' => 'display должен быть объектом параметров.']);
        }
        if (! isset($details['display']['width'])) { return; }
        $width = $details['display']['width'];
        if ((! is_int($width) && ! is_string($width)) || ! preg_match('/^(?:[1-9]|1[0-2])$/', (string) $width)) {
            throw ValidationException::withMessages(['details' => 'display.width должен быть целым числом от 1 до 12.']);
        }
    }

    public function span(array $details): array
    {
        try { $this->validate($details); $width = (int) ($details['display']['width'] ?? 12); }
        catch (ValidationException) { $width = 12; }
        return ['default' => 1, 'lg' => $width];
    }
}
