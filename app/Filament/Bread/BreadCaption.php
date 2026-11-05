<?php

namespace App\Filament\Bread;

class BreadCaption
{
    public static function preview(string $type, mixed $value, bool $translated = false): string
    {
        if (! is_scalar($value)) { return ''; }
        $text = (string) $value;
        // Voyager multilingual.loadLang replaces the preview with full literal text.
        if ($translated) { return $text; }
        if ($type === 'rich_text_box') { $text = strip_tags($text, '<b><i><u>'); }
        return mb_strlen($text) > 200 ? mb_substr($text, 0, 200).' ...' : $text;
    }

    public static function htmlText(string $caption): string
    {
        return html_entity_decode(strip_tags($caption), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function none(): string
    {
        return __('admin_bread.none');
    }
}
