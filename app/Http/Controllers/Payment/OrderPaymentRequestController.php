<?php

namespace App\Http\Controllers\Payment;

use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Libwebtopay\WebToPay;
use App\Http\Controllers\Libwebtopay\PayseraController;
use App\Models\OrderPaymentRequest;
use App\Services\Payment\OrderPaymentRequestService;
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
        try {
            $response = WebToPay::validateAndParseData(
                $_REQUEST,
                PayseraController::projectid,
                PayseraController::sign_password
            );

            if ($response['status'] === '1' || $response['status'] === '3') {
                $paymentRequest = $this->orderPaymentRequestService->markAsPaidByPublicNumber((string) $response['orderid']);

                if ($paymentRequest) {
                    return Redirect::to($paymentRequest->publicUrl())->with('payment_request_flash', 'paid');
                }
            }
        } catch (Exception $exception) {
            // ignore, fallback below
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
        try {
            $response = WebToPay::validateAndParseData(
                $_REQUEST,
                PayseraController::projectid,
                PayseraController::sign_password
            );

            if ($response['status'] === '1' || $response['status'] === '3') {
                $this->orderPaymentRequestService->markAsPaidByPublicNumber((string) $response['orderid']);
                echo 'OK';

                return;
            }
        } catch (Exception $exception) {
            echo get_class($exception) . ':' . $exception->getMessage();

            return;
        }

        echo 'Payment was not successful';
    }

    public function paypalAccept(HttpRequest $request)
    {
        $paymentRequest = OrderPaymentRequest::where('billing_invoice_uuid', $request->token)->firstOrFail();

        if ((new OneTimePayPalService())->checkPayment($request->token)) {
            $this->orderPaymentRequestService->markAsPaid($paymentRequest);

            return Redirect::to($paymentRequest->publicUrl())->with('payment_request_flash', 'paid');
        }

        return Redirect::to($paymentRequest->publicUrl())->with('payment_request_flash', 'error');
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
