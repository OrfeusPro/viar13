<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class StrTransHelperTest extends TestCase
{
    public function test_it_reads_curly_bracket_locale_fragments(): void
    {
        $value = '{{en}}English title{{ru}}Русский заголовок';

        $this->assertSame('English title', str_trans($value, 'en'));
        $this->assertSame('Русский заголовок', str_trans($value, 'ru'));
    }

    public function test_it_reads_square_bracket_locale_fragments(): void
    {
        $value = '[[en]]40x60[[de]]40x60 DE';

        $this->assertSame('40x60 DE', str_trans($value, 'de'));
    }

    public function test_it_preserves_plain_strings_and_handles_empty_values(): void
    {
        $this->assertSame('40x60', str_trans('40x60', 'en'));
        $this->assertSame('', str_trans(null, 'en'));
        $this->assertSame('', str_trans('', 'en'));
    }
}
