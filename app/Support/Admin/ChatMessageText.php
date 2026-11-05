<?php

namespace App\Support\Admin;

use Illuminate\Support\HtmlString;

class ChatMessageText
{
    public static function render(?string $text): HtmlString
    {
        // Split raw text first: escape text and URL independently, never re-parse escaped HTML.
        $parts = preg_split('~(https?://[^\s<>"\x27\x00-\x1f]+)~iu', $text ?? '', -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return new HtmlString(e($text ?? ''));
        }

        $html = '';
        foreach ($parts as $index => $part) {
            if ($index % 2 === 0) {
                $html .= e($part);

                continue;
            }
            $url = rtrim($part, '.,;:!?');
            // Keep balanced parentheses in URLs, but exclude surrounding prose punctuation.
            foreach ([')' => '(', ']' => '[', '}' => '{'] as $close => $open) {
                while (str_ends_with($url, $close) && substr_count($url, $close) > substr_count($url, $open)) {
                    $url = substr($url, 0, -1);
                }
            }
            $host = parse_url($url, PHP_URL_HOST);
            if (! is_string($host) || $host === '' || str_contains($url, '\\')) {
                $html .= e($part);

                continue;
            }
            $html .= '<a href="'.e($url).'" target="_blank" rel="noopener noreferrer" style="color: #2563eb; text-decoration: underline;">'
                .e($url).'</a>'.e(substr($part, strlen($url)));
        }

        return new HtmlString($html);
    }
}
