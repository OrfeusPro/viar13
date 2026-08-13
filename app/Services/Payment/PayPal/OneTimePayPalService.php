<?php

namespace App\Services\Payment\PayPal;

use App\Models\OrderPaymentRequest;
use App\Models\Orders;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class OneTimePayPalService
{
    /**
     * @param array $request
     * @return mixed
     */
    public function getRequisites(array $requestClient)
    {
        $request = new OrdersCreateRequest();
        $payLink = null;

        $request->prefer('return=representation');

        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => (string)$requestClient['order_id'],
                    "amount" => [
                        "value"         => $requestClient['paypalTotalPrice'],
                        "currency_code" => "EUR"
                    ]
                ]
            ],
            "application_context" => [
                "cancel_url" => $requestClient['cancel_url'] ?? ($this->getSelfUrl() . "/pay_cancel"),
                "return_url" => $requestClient['return_url'] ?? ($this->getSelfUrl() . "/pay_accept")
            ]
        ];

        try {
            $response = $this->client()->execute($request);
            $payLink  = collect($response->result->links)
                ->firstWhere('rel', 'approve')->href;

            $this->storeBillingInvoiceUuid($requestClient, (string) $response->result->id);

            Log::channel('paypal')->debug('Success Response pay Data: ', [
                'orderID'     => $response->result->id,
                'approveLink' => $payLink
            ]);
        } catch (\Exception $ex) {
            Log::channel('paypal')->error('Fail Response pay Data: ', [
                'orderID' => $requestClient['order_id'],
                'message' => $ex->getMessage()
            ]);
        }

        if (!empty($payLink)) {
            header("Location: $payLink", true);
            exit;
        }

        return response("Error receiving payment details! Please contact the administrator!");
    }

    private function storeBillingInvoiceUuid(array $requestClient, string $billingInvoiceUuid): void
    {
        $targetType = $requestClient['billing_target_type'] ?? 'order';
        $targetId = $requestClient['billing_target_id'] ?? ($requestClient['order_id'] ?? null);

        if (!$targetId) {
            return;
        }

        if ($targetType === 'payment_request') {
            $paymentRequest = OrderPaymentRequest::find((int) $targetId);
            if ($paymentRequest) {
                $paymentRequest->billing_invoice_uuid = $billingInvoiceUuid;
                $paymentRequest->save();
            }

            return;
        }

        $order = Orders::find($targetId);
        if ($order) {
            $order->billing_invoice_uuid = $billingInvoiceUuid;
            $order->save();
        }
    }

    private function client()
    {
        if (env('PAYPAL_SANDBOX')) {
            $environment = new SandboxEnvironment(env('PAYPAL_SANDBOX_ID'), env('PAYPAL_SANDBOX_SECRET'));
        } else {
            $environment = new ProductionEnvironment(env('PAYPAL_ID'), env('PAYPAL_SECRET'));
        }

        return new PayPalHttpClient($environment);
    }

    public function getSelfUrl(): string
    {
        $url = substr(strtolower($_SERVER['SERVER_PROTOCOL']), 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'));

        if (isset($_SERVER['HTTPS']) === true) {
            $url .= ($_SERVER['HTTPS'] === 'on') ? 's' : '';
        }

        $url .= '://' . $_SERVER['HTTP_HOST'];

        if (isset($_SERVER['SERVER_PORT']) === true && $_SERVER['SERVER_PORT'] !== '80') {
            $url .= ':' . $_SERVER['SERVER_PORT'];
        }

        $url .= dirname($_SERVER['SCRIPT_NAME']);
        $url = $url . App::getLocale();
        $url = str_replace("\\",'/', $url);

        return str_replace("\\",'/', $url) . "/paypal";
    }

    public function checkPayment($orderID)
    {
        $request = new OrdersCaptureRequest($orderID);
        $request->prefer('return=representation');

        try {
            $response = $this->client()->execute($request);
            Log::channel('paypal')->debug('Success callback payment: ', (array)$response->result);

            return true;
        } catch (\Exception $ex) {
            Log::channel('paypal')->error('Fail callback payment: ', [
                'message' => $ex->getMessage()
            ]);

            return false;
        }
    }
}
