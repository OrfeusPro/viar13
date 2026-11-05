<?php

namespace Tests\Unit;

use App\Support\Admin\ChatMessageText;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ChatMessageTextTest extends TestCase
{
    #[DataProvider('messages')]
    public function test_links_are_safe_and_visible_text_is_preserved(?string $text, array $urls): void
    {
        $html = ChatMessageText::render($text)->toHtml();
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8"><div id="message">'.$html.'</div>');
        $xpath = new \DOMXPath($dom);
        $links = $xpath->query('//a');
        $this->assertCount(count($urls), $links);
        foreach ($links as $index => $link) {
            $this->assertSame($urls[$index], $link->getAttribute('href'));
            $this->assertSame('_blank', $link->getAttribute('target'));
            $this->assertSame('noopener noreferrer', $link->getAttribute('rel'));
            $this->assertSame($urls[$index], $link->textContent);
            $this->assertFalse($link->hasAttribute('onclick'));
            $this->assertFalse($link->hasAttribute('onmouseover'));
        }
        $this->assertCount(0, $xpath->query('//script|//img|//iframe'));
        $this->assertSame($text ?? '', $xpath->query('//*[@id="message"]')->item(0)->textContent);
    }

    public static function messages(): array
    {
        return [
            'empty' => [null, []],
            'plain and newline' => ["Первая строка\nВторая & текст", []],
            'query and fragment' => ['Ссылка https://example.test/a?x=1&y=2#part', ['https://example.test/a?x=1&y=2#part']],
            'two protocols' => ['http://example.test/a и HTTPS://example.test/b', ['http://example.test/a', 'HTTPS://example.test/b']],
            'punctuation' => ['Смотрите (https://example.test/a). Ещё https://example.test/wiki/A_(B)!', ['https://example.test/a', 'https://example.test/wiki/A_(B)']],
            'markup and quotes' => ['<img src=x onerror=alert(1)> https://example.test/" onclick="alert(1) <script>alert(2)</script>', ['https://example.test/']],
            'encoded quotes' => ['https://example.test/?x=&quot;onclick=alert(1)', ['https://example.test/?x=&quot;onclick=alert(1)']],
            'unsafe protocols' => ['javascript:alert(1) data:text/html,bad //example.test ftp://example.test', []],
            'invalid host' => ['https:///bad http://', []],
            'backslash' => ['https://example.test\\bad', []],
            'unicode path' => ['https://example.test/путь?имя=тест', ['https://example.test/путь?имя=тест']],
        ];
    }
}
