<?php

namespace Tests\Unit;

use App\Services\Invoice\InvoiceAmountInWords;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class InvoiceAmountInWordsTest extends TestCase
{
    #[DataProvider('localizedAmounts')]
    public function test_it_spells_the_complete_amount_in_supported_languages($locale, $expected)
    {
        $service = new InvoiceAmountInWords();

        $actual = $service->formatCents(24305, $locale);

        $this->assertSame($expected, $actual);
        $this->assertSame(0, preg_match('/\d/u', $actual));
    }

    public static function localizedAmounts()
    {
        return [
            'Latvian' => ['lv', 'Divi simti četrdesmit trīs euro un pieci centi'],
            'Lithuanian' => ['lt', 'Du šimtai keturiasdešimt trys eurai ir penki centai'],
            'Polish' => ['pl', 'Dwieście czterdzieści trzy euro i pięć centów'],
            'Russian' => ['ru', 'Двести сорок три евро и пять центов'],
            'German' => ['de', 'Zweihundertdreiundvierzig Euro und fünf Cent'],
            'English' => ['en', 'Two hundred forty-three euros and five cents'],
            'Estonian alias' => ['ee', 'Kakssada nelikümmend kolm eurot ja viis senti'],
        ];
    }

    #[DataProvider('boundaryAmounts')]
    public function test_it_handles_grammar_and_range_boundaries($cents, $locale, $expected)
    {
        $this->assertSame($expected, (new InvoiceAmountInWords())->formatCents($cents, $locale));
    }

    public static function boundaryAmounts()
    {
        return [
            [0, 'ru', 'Ноль евро и ноль центов'],
            [101, 'ru', 'Один евро и один цент'],
            [202, 'ru', 'Два евро и два цента'],
            [505, 'ru', 'Пять евро и пять центов'],
            [1100, 'ru', 'Одиннадцать евро и ноль центов'],
            [101, 'de', 'Ein Euro und ein Cent'],
            [2100, 'lv', 'Divdesmit viens euro un nulle centi'],
            [10100, 'lt', 'Vienas šimtas vienas euras ir nulis centų'],
            [114099, 'en', 'One thousand one hundred forty euros and ninety-nine cents'],
            [99999999999, 'de', 'Neunhundertneunundneunzig Millionen neunhundertneunundneunzigtausendneunhundertneunundneunzig Euro und neunundneunzig Cent'],
        ];
    }

    public function test_it_rejects_amounts_outside_the_invoice_range()
    {
        $this->expectException(InvalidArgumentException::class);

        (new InvoiceAmountInWords())->formatCents(100000000000, 'en');
    }
}
