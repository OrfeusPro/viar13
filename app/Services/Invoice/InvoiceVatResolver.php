<?php

namespace App\Services\Invoice;

class InvoiceVatResolver
{
    public function sellerHasVat($invoiceNumber, $orderStrings, $customVatNumber = null)
    {
        if ($customVatNumber !== null) {
            return trim((string) $customVatNumber) !== '';
        }

        $invoiceNumber = (string) $invoiceNumber;

        if (strpos($invoiceNumber, 'VRR') !== false) {
            return $this->toBool($this->value($orderStrings, 'is_pvn_vrr'));
        }

        if (strpos($invoiceNumber, 'BAW') !== false) {
            return $this->toBool($this->value($orderStrings, 'is_pvn_vrv'));
        }

        if (strpos($invoiceNumber, 'DS020') !== false) {
            return $this->toBool($this->value($orderStrings, 'is_pvn_vra'));
        }

        return $this->toBool($this->value($orderStrings, 'is_pvn'));
    }

    private function value($source, $key)
    {
        if (is_array($source) || $source instanceof \ArrayAccess) {
            return isset($source[$key]) ? $source[$key] : false;
        }

        return is_object($source) && isset($source->{$key}) ? $source->{$key} : false;
    }

    private function toBool($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
