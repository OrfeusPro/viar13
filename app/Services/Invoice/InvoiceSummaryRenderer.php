<?php

namespace App\Services\Invoice;

use Illuminate\Translation\Translator;

class InvoiceSummaryRenderer
{
    private $amountInWords;
    private $translator;

    public function __construct(InvoiceAmountInWords $amountInWords, Translator $translator)
    {
        $this->amountInWords = $amountInWords;
        $this->translator = $translator;
    }

    public function render(array $totals, $locale)
    {
        $locale = $locale === 'ee' ? 'et' : $locale;
        $html = '';

        if ($totals['has_vat']) {
            $html .= $this->amountRow(
                $this->translator->get('invoice.total_without_vat', [], $locale),
                $totals['net_formatted']
            );
            $html .= $this->amountRow(
                $this->translator->get('invoice.vat', ['rate' => $this->formatRate($totals['vat_rate'])], $locale),
                $totals['vat_formatted']
            );
        }

        $html .= $this->amountRow(
            $this->translator->get('invoice.total_payable', [], $locale),
            $totals['gross_formatted']
        );

        $amountWords = $this->amountInWords->formatCents($totals['gross_cents'], $locale);
        $html .= '<tr class="invoice-amount-words"><td colspan="6" style="padding-top:8px;">'
            . e($this->translator->get('invoice.amount_in_words', [], $locale)) . ' '
            . e($amountWords) . '</td></tr>';
        $html .= '<tr class="invoice-electronic-notice"><td colspan="6" style="padding-top:10px;font-size:10px;">'
            . e($this->translator->get('invoice.electronic_notice', [], $locale)) . '</td></tr>';

        return $html;
    }

    private function amountRow($label, $amount)
    {
        return '<tr style="border:1px solid #000;">'
            . '<td>' . e($label) . '</td><td></td><td></td><td></td><td></td>'
            . '<td class="tar" style="text-align:right;">' . e($amount) . '&euro;</td></tr>';
    }

    private function formatRate($rate)
    {
        return (float) $rate == (int) $rate ? (string) (int) $rate : rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.');
    }
}
