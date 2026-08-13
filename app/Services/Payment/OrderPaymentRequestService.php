<?php

namespace App\Services\Payment;

use App\Http\Controllers\Libwebtopay\PayseraController;
use App\Http\Controllers\Libwebtopay\WebToPay;
use App\Models\OrderPaymentRequest;
use App\Models\Orders;
use App\Models\User;
use App\Services\Payment\PayPal\OneTimePayPalService;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class OrderPaymentRequestService
{
    public function createForOrder(Orders $order, array $attributes, ?User $creator = null): OrderPaymentRequest
    {
        $delivery = $this->decodeDelivery($order);
        $customer = $order->user_id ? User::find($order->user_id) : null;
        $nameParts = $this->resolveNameParts($delivery, $customer);

        $paymentRequest = new OrderPaymentRequest();
        $paymentRequest->order_id = (int) $order->id;
        $paymentRequest->user_id = $order->user_id ?: null;
        $paymentRequest->created_by = $creator ? $creator->id : null;
        $paymentRequest->token = Str::random(40);
        $paymentRequest->amount = round((float) $attributes['amount'], 2);
        $paymentRequest->currency = 'EUR';
        $paymentRequest->purpose = isset($attributes['purpose']) ? trim((string) $attributes['purpose']) : null;
        $paymentRequest->status = OrderPaymentRequest::STATUS_PENDING;
        $paymentRequest->customer_email = $this->resolveEmailFromSources($delivery, $customer);
        $paymentRequest->customer_first_name = $nameParts['first_name'];
        $paymentRequest->customer_last_name = $nameParts['last_name'];
        $paymentRequest->customer_country = $this->resolveCountryFromSources($delivery, $order, $customer);
        $paymentRequest->save();

        $paymentRequest->public_number = 'OPR-' . str_pad((string) $paymentRequest->id, 6, '0', STR_PAD_LEFT);
        $paymentRequest->save();

        return $paymentRequest;
    }

    public function start(OrderPaymentRequest $paymentRequest, string $paymentMethod)
    {
        $paymentRequest->selected_payment_method = $paymentMethod;
        $paymentRequest->save();

        $payload = $this->buildGatewayPayload($paymentRequest, $paymentMethod);

        if ($paymentMethod === 'paypalOnetimePayment') {
            return app(OneTimePayPalService::class)->getRequisites($payload);
        }

        return PayseraController::index($payload);
    }

    public function markAsPaidByPublicNumber(string $publicNumber): ?OrderPaymentRequest
    {
        $paymentRequest = OrderPaymentRequest::where('public_number', $publicNumber)->first();

        if (!$paymentRequest) {
            return null;
        }

        return $this->markAsPaid($paymentRequest);
    }

    public function markAsPaidByBillingInvoiceUuid(string $billingInvoiceUuid): ?OrderPaymentRequest
    {
        $paymentRequest = OrderPaymentRequest::where('billing_invoice_uuid', $billingInvoiceUuid)->first();

        if (!$paymentRequest) {
            return null;
        }

        return $this->markAsPaid($paymentRequest);
    }

    public function markAsPaid(OrderPaymentRequest $paymentRequest): OrderPaymentRequest
    {
        if (!$paymentRequest->isPaid()) {
            $paymentRequest->status = OrderPaymentRequest::STATUS_PAID;
            $paymentRequest->paid_at = now();
            $paymentRequest->save();
        }

        return $paymentRequest;
    }

    protected function buildGatewayPayload(OrderPaymentRequest $paymentRequest, string $paymentMethod): array
    {
        $locale = $paymentRequest->resolveLocale();
        $amount = round((float) $paymentRequest->amount, 2);

        return [
            'order_id' => $paymentRequest->public_number,
            'country' => $this->resolveCountry($paymentRequest),
            'name' => $this->resolveFirstName($paymentRequest),
            'last_name' => $this->resolveLastName($paymentRequest),
            'email' => $this->resolveEmail($paymentRequest),
            'payment' => $paymentMethod,
            'deliv_price' => '0.00',
            'terms_price' => '0.00',
            'totalPrice' => number_format($amount, 2, '.', ''),
            'payseraTotalPrice' => number_format($amount, 2, '', ''),
            'paypalTotalPrice' => number_format($amount, 2, '.', ''),
            'accepturl' => $this->localizedUrl($locale, 'payment-request/paysera/accept'),
            'cancelurl' => $this->localizedUrl($locale, 'payment-request/paysera/cancel'),
            'callbackurl' => $this->localizedUrl($locale, 'payment-request/paysera/callback'),
            'return_url' => $this->localizedUrl($locale, 'payment-request/paypal/pay_accept'),
            'cancel_url' => $this->localizedUrl($locale, 'payment-request/paypal/pay_cancel'),
            'billing_target_type' => 'payment_request',
            'billing_target_id' => $paymentRequest->id,
        ];
    }

    protected function resolveCountry(OrderPaymentRequest $paymentRequest): string
    {
        $country = strtoupper(trim((string) $paymentRequest->customer_country));

        if ($country !== '') {
            return $country;
        }

        $order = $paymentRequest->relationLoaded('order')
            ? $paymentRequest->order
            : $paymentRequest->order()->first();
        $customer = $paymentRequest->relationLoaded('customer')
            ? $paymentRequest->customer
            : $paymentRequest->customer()->first();
        $delivery = $order ? $this->decodeDelivery($order) : [];

        $country = $this->resolveCountryFromSources($delivery, $order, $customer);

        return $country !== '' ? $country : 'LV';
    }

    protected function resolveFirstName(OrderPaymentRequest $paymentRequest): string
    {
        $firstName = $this->normalizeString($paymentRequest->customer_first_name);

        if ($firstName !== '') {
            return $this->splitFullName($firstName)['first_name'] ?: $firstName;
        }

        $order = $paymentRequest->relationLoaded('order')
            ? $paymentRequest->order
            : $paymentRequest->order()->first();
        $customer = $paymentRequest->relationLoaded('customer')
            ? $paymentRequest->customer
            : $paymentRequest->customer()->first();
        $delivery = $order ? $this->decodeDelivery($order) : [];

        $nameParts = $this->resolveNameParts($delivery, $customer);

        return $nameParts['first_name'];
    }

    protected function resolveLastName(OrderPaymentRequest $paymentRequest): string
    {
        $lastName = $this->normalizeString($paymentRequest->customer_last_name);

        if ($lastName !== '') {
            return $lastName;
        }

        $lastName = $this->extractLastNameFromFirstNameField($paymentRequest->customer_first_name);
        if ($lastName !== '') {
            return $lastName;
        }

        $order = $paymentRequest->relationLoaded('order')
            ? $paymentRequest->order
            : $paymentRequest->order()->first();
        $customer = $paymentRequest->relationLoaded('customer')
            ? $paymentRequest->customer
            : $paymentRequest->customer()->first();
        $delivery = $order ? $this->decodeDelivery($order) : [];

        $nameParts = $this->resolveNameParts($delivery, $customer);

        return $nameParts['last_name'];
    }

    protected function resolveEmail(OrderPaymentRequest $paymentRequest): string
    {
        $email = $this->normalizeString($paymentRequest->customer_email);

        if ($email !== '') {
            return $email;
        }

        $order = $paymentRequest->relationLoaded('order')
            ? $paymentRequest->order
            : $paymentRequest->order()->first();
        $customer = $paymentRequest->relationLoaded('customer')
            ? $paymentRequest->customer
            : $paymentRequest->customer()->first();
        $delivery = $order ? $this->decodeDelivery($order) : [];

        return $this->resolveEmailFromSources($delivery, $customer);
    }

    protected function decodeDelivery(Orders $order): array
    {
        $delivery = json_decode($order->delivery, true);

        return is_array($delivery) ? $delivery : [];
    }

    protected function resolveCountryFromSources(array $delivery, ?Orders $order, ?User $customer): string
    {
        foreach ([
            Arr::get($delivery, 'country'),
            $order ? $order->country : null,
            $customer ? ($customer->country ?? null) : null,
        ] as $candidate) {
            $country = strtoupper($this->normalizeString($candidate));
            if ($country !== '') {
                return $country;
            }
        }

        return '';
    }

    protected function resolveEmailFromSources(array $delivery, ?User $customer): string
    {
        foreach ([
            Arr::get($delivery, 'email'),
            Arr::get($delivery, 'payer_email'),
            Arr::get($delivery, 'client_email'),
            $customer ? $customer->email : null,
        ] as $candidate) {
            $email = $this->normalizeString($candidate);
            if ($email !== '') {
                return $email;
            }
        }

        return '';
    }

    protected function resolveNameParts(array $delivery, ?User $customer): array
    {
        $fullNameParts = $this->splitFullName(
            $this->firstNonEmpty([
                Arr::get($delivery, 'full_name'),
                Arr::get($delivery, 'name'),
                Arr::get($delivery, 'fio'),
                Arr::get($delivery, 'payer_name'),
                Arr::get($delivery, 'client_name'),
                $customer ? ($customer->name ?? null) : null,
            ])
        );

        $firstName = $this->firstNonEmpty([
            Arr::get($delivery, 'first_name'),
            $fullNameParts['first_name'],
            $customer ? $customer->first_name : null,
        ]);

        $lastName = $this->firstNonEmpty([
            Arr::get($delivery, 'last_name'),
            $this->extractLastNameFromFirstNameField(Arr::get($delivery, 'first_name')),
            $fullNameParts['last_name'],
            $customer ? $customer->last_name : null,
        ]);

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];
    }

    protected function splitFullName($value): array
    {
        $value = $this->normalizeString($value);
        if ($value === '') {
            return ['first_name' => '', 'last_name' => ''];
        }

        $parts = preg_split('/\s+/u', $value, -1, PREG_SPLIT_NO_EMPTY);
        if (!$parts) {
            return ['first_name' => '', 'last_name' => ''];
        }

        $firstName = array_shift($parts);
        $lastName = implode(' ', $parts);

        return [
            'first_name' => $this->normalizeString($firstName),
            'last_name' => $this->normalizeString($lastName),
        ];
    }

    protected function extractLastNameFromFirstNameField($value): string
    {
        $parts = $this->splitFullName($value);

        return $parts['last_name'];
    }

    protected function firstNonEmpty(array $values): string
    {
        foreach ($values as $value) {
            $value = $this->normalizeString($value);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    protected function normalizeString($value): string
    {
        return trim((string) $value);
    }

    protected function localizedUrl(string $locale, string $path): string
    {
        $locale = trim($locale, '/');
        $path = ltrim($path, '/');

        return url($locale . '/' . $path);
    }
}
