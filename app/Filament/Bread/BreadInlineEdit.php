<?php

namespace App\Filament\Bread;

class BreadInlineEdit
{
    public static function enabled(string $type, array $details): bool
    {
        if (isset($details['view'])) { return false; }
        return match ($type) {
            'text', 'number' => isset($details['browse_inline_editor']),
            'checkbox' => is_string($details['on'] ?? null) && is_string($details['off'] ?? null)
                && (array_key_exists('browse_inline_checkbox', $details) || array_key_exists('browse_inline_editor', $details)),
            'adv_fields_group' => array_key_exists('browse_inline_editor', $details),
            default => false,
        };
    }
}
