<?php

namespace Tests\Feature\Invoice;

use App\Http\Controllers\DynamicPDFController;
use App\Services\Invoice\InvoiceSummaryRenderer;
use App\Services\Invoice\InvoiceTotalsCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InvoiceSummaryRendererTest extends TestCase
{
    #[DataProvider('locales')]
    public function test_vat_summary_contains_all_required_localized_rows($locale, $netLabel, $wordsLabel, $notice)
    {
        $totals = (new InvoiceTotalsCalculator())->calculateUsingVatAmount(243, 42.17, 21, true);
        $html = $this->app->make(InvoiceSummaryRenderer::class)->render($totals, $locale);

        $this->assertStringContainsString($netLabel, $html);
        $this->assertStringContainsString($wordsLabel, $html);
        $this->assertStringContainsString($notice, $html);
        $this->assertStringContainsString('200.83&euro;', $html);
        $this->assertStringContainsString('42.17&euro;', $html);
        $this->assertStringContainsString('243.00&euro;', $html);
        $this->assertSame(0, preg_match('/invoice\.[a-z_]+/', $html));
    }

    public function test_non_vat_summary_omits_only_net_and_vat_rows()
    {
        $totals = (new InvoiceTotalsCalculator())->calculateUsingVatAmount(243.05, 0, 21, false);
        $html = $this->app->make(InvoiceSummaryRenderer::class)->render($totals, 'ru');

        $this->assertStringNotContainsString('Общая сумма без НДС', $html);
        $this->assertStringNotContainsString('НДС 21%', $html);
        $this->assertStringContainsString('Сумма к оплате', $html);
        $this->assertStringContainsString('Двести сорок три евро и пять центов', $html);
        $this->assertStringContainsString('Счёт подготовлен', $html);
        $this->assertStringNotContainsString('Dvesti', $html);
        $this->assertStringNotContainsString('border:1px solid #000', $html);
    }

    public function test_dynamic_pdf_controller_dependencies_resolve_from_the_container()
    {
        $this->assertInstanceOf(DynamicPDFController::class, $this->app->make(DynamicPDFController::class));
    }

    public static function locales()
    {
        return [
            ['lv', 'Kopējā summa bez PVN', 'Summa vārdiem:', 'Rēķins sagatavots un apstiprināts elektroniski'],
            ['lt', 'Bendra suma be PVM', 'Suma žodžiais:', 'Sąskaita parengta ir patvirtinta elektroniniu būdu'],
            ['pl', 'Suma netto', 'Kwota słownie:', 'Faktura została sporządzona i zatwierdzona elektronicznie'],
            ['ru', 'Общая сумма без НДС', 'Сумма прописью:', 'Счёт подготовлен и утверждён в электронном виде'],
            ['de', 'Gesamtsumme ohne MwSt.', 'Betrag in Worten:', 'Die Rechnung wurde gemäß'],
            ['en', 'Total excluding VAT', 'Amount in words:', 'The invoice has been prepared and approved electronically'],
            ['ee', 'Kogusumma käibemaksuta', 'Summa sõnadega:', 'Arve on koostatud ja kinnitatud elektrooniliselt'],
        ];
    }
}
