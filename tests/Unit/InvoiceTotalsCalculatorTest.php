<?php

namespace Tests\Unit;

use App\Services\Invoice\InvoiceTotalsCalculator;
use App\Services\Invoice\InvoiceVatResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class InvoiceTotalsCalculatorTest extends TestCase
{
    public function test_vat_totals_preserve_the_legacy_vat_amount_in_cents()
    {
        $totals = (new InvoiceTotalsCalculator())->calculateUsingVatAmount('243.00 €', '42.17', 21, true);

        $this->assertSame(24300, $totals['gross_cents']);
        $this->assertSame(20083, $totals['net_cents']);
        $this->assertSame(4217, $totals['vat_cents']);
        $this->assertSame($totals['gross_cents'], $totals['net_cents'] + $totals['vat_cents']);
    }

    public function test_non_vat_invoice_keeps_the_gross_total_and_has_no_vat()
    {
        $totals = (new InvoiceTotalsCalculator())->calculateUsingVatAmount(243.05, 99.99, 21, false);

        $this->assertFalse($totals['has_vat']);
        $this->assertSame(24305, $totals['net_cents']);
        $this->assertSame(0, $totals['vat_cents']);
    }

    #[DataProvider('invoiceSeries')]
    public function test_seller_vat_status_uses_the_existing_series_flags($number, $expected)
    {
        $strings = [
            'is_pvn' => 0,
            'is_pvn_vrr' => 1,
            'is_pvn_vrv' => 0,
            'is_pvn_vra' => 1,
        ];

        $this->assertSame($expected, (new InvoiceVatResolver())->sellerHasVat($number, $strings));
    }

    public static function invoiceSeries()
    {
        return [
            ['VR00123', false],
            ['VRR123', true],
            ['BAW123', false],
            ['DS020123', true],
            ['UNKNOWN123', false],
        ];
    }

    public function test_custom_firm_with_vat_number_is_treated_as_vat_registered()
    {
        $strings = ['is_pvn' => 0];

        $this->assertTrue(
            (new InvoiceVatResolver())->sellerHasVat('VR0048146', $strings, 'LV40203723856')
        );
    }

    public function test_custom_firm_without_vat_number_is_treated_as_non_vat()
    {
        $strings = ['is_pvn' => 1];

        $this->assertFalse(
            (new InvoiceVatResolver())->sellerHasVat('VR0048146', $strings, '')
        );
    }
}
