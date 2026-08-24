<?php

namespace App\Services\Invoice;

class InvoiceTotalsCalculator
{
    public function calculateUsingVatAmount($grossAmount, $vatAmount, $vatRate, $sellerHasVat)
    {
        $grossCents = $this->toCents($grossAmount);

        if (!$sellerHasVat) {
            return $this->result($grossCents, $grossCents, 0, false, (float) $vatRate);
        }

        $vatCents = $this->toCents($vatAmount);

        return $this->result(
            $grossCents,
            $grossCents - $vatCents,
            $vatCents,
            true,
            (float) $vatRate
        );
    }

    public function toCents($amount)
    {
        if (is_string($amount)) {
            $amount = str_replace([chr(194) . chr(160), ' ', '€'], '', trim($amount));
            $amount = str_replace(',', '.', $amount);
        }

        return (int) round((float) $amount * 100, 0, PHP_ROUND_HALF_UP);
    }

    private function result($grossCents, $netCents, $vatCents, $hasVat, $vatRate)
    {
        return [
            'has_vat' => (bool) $hasVat,
            'vat_rate' => $vatRate,
            'gross_cents' => $grossCents,
            'net_cents' => $netCents,
            'vat_cents' => $vatCents,
            'gross' => $grossCents / 100,
            'net' => $netCents / 100,
            'vat' => $vatCents / 100,
            'gross_formatted' => $this->formatCents($grossCents),
            'net_formatted' => $this->formatCents($netCents),
            'vat_formatted' => $this->formatCents($vatCents),
        ];
    }

    private function formatCents($cents)
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
