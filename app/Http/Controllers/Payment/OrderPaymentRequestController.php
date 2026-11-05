<?php

namespace App\Http\Controllers\Payment;

use Throwable;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Libwebtopay\WebToPay;
use App\Models\OrderPaymentRequest;
use App\Services\Payment\OrderPaymentRequestService;
use App\Services\Payment\PayseraCallbackService;
use App\Services\Payment\PayPalCaptureService;
use App\Services\Payment\PayPal\OneTimePayPalService;

class OrderPaymentRequestController extends Controller
{
    private $orderPaymentRequestService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->orderPaymentRequestService = app(OrderPaymentRequestService::class);
    }

    public function show($token)
    {
        $paymentRequest = $this->resolvePaymentRequest($token);
        $page['title'] = __('account_new.payment_request_title');
        $paymentMethods = WebToPay::PAYSERA_METHODS_MAP;
        $paymentMethods['paypalOnetimePayment'] = [
            'title' => 'PayPal',
            'title_translate' => false,
            'img' => 'img/icons/PayPal.svg',
        ];

        $content = view(config('theme.resource') . 'payment_request.show')->with([
            'page' => $page,
            'paymentRequest' => $paymentRequest,
            'paymentMethods' => $paymentMethods,
        ]);

        $this->vars = Arr::add($this->vars, 'title', __('account_new.payment_request_title'));
        $this->vars = Arr::add($this->vars, 'meta_desc', __('account_new.payment_request_hint'));
        $this->vars = Arr::add($this->vars, 'content', $content);

        return $this->renderOutput();
    }

    public function start(HttpRequest $request, $token)
    {
        $paymentRequest = $this->resolvePaymentRequest($token);

        if ($paymentRequest->isPaid()) {
            return redirect()->to($paymentRequest->publicUrl())->with('payment_request_flash', 'paid');
        }

        $request->validate([
            'payment' => [
                'required',
                'string',
                Rule::in(array_merge(array_keys(WebToPay::PAYSERA_METHODS_MAP), ['paypalOnetimePayment'])),
            ],
        ]);

        return $this->orderPaymentRequestService->start($paymentRequest, $request->input('payment'));
    }

    public function payseraAccept()
    {
        $response = [];

        try {
            $response = WebToPay::validateAndParseData(
                $_REQUEST,
                (string) config('paysera.project_id'),
                (string) config('paysera.sign_password')
            );

            if ($response['status'] === '1' || $response['status'] === '3') {
                $paymentRequest = app(PayseraCallbackService::class)->confirmPaymentRequest($response);

                if ($paymentRequest) {
                    return Redirect::to($paymentRequest->publicUrl())->with('payment_request_flash', 'paid');
                }
            }
        } catch (Throwable $exception) {
            Log::warning('Paysera payment-request accept was rejected.', [
                'order_id' => $response['orderid'] ?? request('orderid'),
                'message' => $exception->getMessage(),
            ]);
        }

        $paymentRequest = OrderPaymentRequest::where('public_number', (string) request('orderid'))->first();
        if ($paymentRequest) {
            return Redirect::to($paymentRequest->publicUrl())->with('payment_request_flash', 'error');
        }

        return Redirect::to(url('/'));
    }

    public function payseraCancel()
    {
        $publicNumber = (string) request('orderid');
        $paymentRequest = OrderPaymentRequest::where('public_number', $publicNumber)->first();

        if ($paymentRequest) {
            return Redirect::to($paymentRequest->publicUrl())->with('payment_request_flash', 'cancelled');
        }

        return Redirect::to(url('/'));
    }

    public function payseraCallback()
    {
        $response = [];

        try {
            $response = WebToPay::validateAndParseData(
                $_REQUEST,
                (string) config('paysera.project_id'),
                (string) config('paysera.sign_password')
            );

            if ($response['status'] === '1' || $response['status'] === '3') {
                app(PayseraCallbackService::class)->confirmPaymentRequest($response);

                return response('OK', 200);
            }
        } catch (Throwable $exception) {
            Log::warning('Paysera payment-request callback was rejected.', [
                'order_id' => $response['orderid'] ?? request('orderid'),
                'message' => $exception->getMessage(),
            ]);

            return response('ERROR', 400);
        }

        return response('ERROR', 400);
    }

    public function paypalAccept(HttpRequest $request)
    {
        $paymentRequest = OrderPaymentRequest::where('billing_invoice_uuid', $request->token)->firstOrFail();

        if ($paymentRequest->isPaid()) {
            return Redirect::to($paymentRequest->publicUrl())->with('payment_request_flash', 'paid');
        }

        $captureResult = app(OneTimePayPalService::class)->capturePayment($request->token);
        if (! $captureResult) {
            return Redirect::to($paymentRequest->publicUrl())->with('payment_request_flash', 'error');
        }

        try {
            app(PayPalCaptureService::class)->confirmPaymentRequest($paymentRequest, $captureResult);

            return Redirect::to($paymentRequest->publicUrl())->with('payment_request_flash', 'paid');
        } catch (Throwable $exception) {
            Log::warning('PayPal payment-request capture was rejected.', [
                'payment_request_id' => $paymentRequest->id,
                'message' => $exception->getMessage(),
            ]);

            return Redirect::to($paymentRequest->publicUrl())->with('payment_request_flash', 'error');
        }
    }

    public function paypalCancel(HttpRequest $request)
    {
        $paymentRequest = OrderPaymentRequest::where('billing_invoice_uuid', $request->token)->firstOrFail();

        return Redirect::to($paymentRequest->publicUrl())->with('payment_request_flash', 'cancelled');
    }

    protected function resolvePaymentRequest($token): OrderPaymentRequest
    {
        return OrderPaymentRequest::with(['order', 'customer'])->where('token', (string) $token)->firstOrFail();
    }
}
