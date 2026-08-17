<?php

namespace Tests\Feature\Payment;

use App\Http\Controllers\Libwebtopay\PayseraController;
use App\Http\Controllers\Payment\PayPal\OneTimePayPalController;
use App\Services\Payment\PayPal\OneTimePayPalService;
use Illuminate\Http\Request;
use RuntimeException;
use Tests\TestCase;

class CheckoutGatewayStartTest extends TestCase
{
    public function test_paysera_returns_laravel_redirect_without_external_request(): void
    {
        config()->set('paysera.project_id', '123456');
        config()->set('paysera.sign_password', 'test-sign-password');
        config()->set('paysera.test', true);

        $response = PayseraController::index([
            'order_id' => 123,
            'payseraTotalPrice' => '6300',
            'country' => 'LV',
            'name' => 'Checkout',
            'last_name' => 'Test',
            'email' => 'checkout@example.test',
            'payment' => 'creditcart',
            'accepturl' => 'https://viar13.loc/en/pay_accept',
            'cancelurl' => 'https://viar13.loc/en/pay_cancel',
            'callbackurl' => 'https://viar13.loc/en/pay_callback',
        ]);

        $this->assertTrue($response->isRedirect());
        $this->assertSame('bank.paysera.com', parse_url($response->getTargetUrl(), PHP_URL_HOST));
    }

    public function test_paysera_configuration_error_returns_back_instead_of_debug_screen(): void
    {
        config()->set('paysera.project_id', null);
        config()->set('paysera.sign_password', null);

        $response = PayseraController::index([
            'order_id' => 123,
            'payment' => 'creditcart',
        ]);

        $this->assertTrue($response->isRedirect());
        $this->assertTrue(session()->has('error'));
    }

    public function test_paypal_success_returns_redirect_without_header_exit(): void
    {
        $service = new class extends OneTimePayPalService {
            protected function client()
            {
                return new class {
                    public function execute($request): object
                    {
                        return (object) [
                            'result' => (object) [
                                'id' => 'PAYPAL-TEST-ID',
                                'links' => [
                                    (object) [
                                        'rel' => 'approve',
                                        'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=test',
                                    ],
                                ],
                            ],
                        ];
                    }
                };
            }
        };

        $response = $service->getRequisites($this->paypalPayload());

        $this->assertTrue($response->isRedirect());
        $this->assertSame('www.sandbox.paypal.com', parse_url($response->getTargetUrl(), PHP_URL_HOST));
    }

    public function test_paypal_failure_returns_back_instead_of_plain_error_or_exit(): void
    {
        $service = new class extends OneTimePayPalService {
            protected function client()
            {
                return new class {
                    public function execute($request): void
                    {
                        throw new RuntimeException('Simulated PayPal outage');
                    }
                };
            }
        };

        $response = $service->getRequisites($this->paypalPayload());

        $this->assertTrue($response->isRedirect());
        $this->assertTrue(session()->has('error'));
    }

    public function test_paypal_callbacks_without_reference_redirect_safely(): void
    {
        $controller = app(OneTimePayPalController::class);
        $request = Request::create('/paypal/callback', 'GET');

        $this->assertTrue($controller->pay_accept($request)->isRedirect(route('cart.index')));
        $this->assertTrue($controller->pay_cancel($request)->isRedirect(route('cart.index')));
    }

    public function test_paypal_capture_returns_provider_result_without_external_request(): void
    {
        $service = new class extends OneTimePayPalService {
            protected function client()
            {
                return new class {
                    public function execute($request): object
                    {
                        return (object) [
                            'result' => (object) [
                                'status' => 'COMPLETED',
                                'purchase_units' => [],
                            ],
                        ];
                    }
                };
            }
        };

        $result = $service->capturePayment('PAYPAL-TEST-ID');

        $this->assertNotNull($result);
        $this->assertSame('COMPLETED', $result->status);
    }

    public function test_paypal_capture_error_returns_null_without_external_request(): void
    {
        $service = new class extends OneTimePayPalService {
            protected function client()
            {
                return new class {
                    public function execute($request): void
                    {
                        throw new RuntimeException('Simulated PayPal capture outage');
                    }
                };
            }
        };

        $this->assertNull($service->capturePayment('PAYPAL-TEST-ID'));
    }

    public function test_invalid_paysera_callbacks_return_controlled_responses(): void
    {
        $originalRequest = $_REQUEST;
        $_REQUEST = [];

        try {
            $controller = app(PayseraController::class);
            $this->assertTrue($controller->pay_accept()->isRedirect(route('cart.index')));

            $callback = $controller->pay_callback();
            $this->assertSame(400, $callback->getStatusCode());
            $this->assertSame('ERROR', $callback->getContent());
        } finally {
            $_REQUEST = $originalRequest;
        }
    }

    private function paypalPayload(): array
    {
        return [
            'order_id' => 0,
            'paypalTotalPrice' => '63.00',
            'return_url' => 'https://viar13.loc/en/paypal/pay_accept',
            'cancel_url' => 'https://viar13.loc/en/paypal/pay_cancel',
        ];
    }
}
