<?php

namespace App\Http\Controllers\Libwebtopay;

use Exception;
use Throwable;
use Redirect;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Libwebtopay\WebToPay;
use App\Models\Orders;
use App\Services\SynvolveWebhookService;

class PayseraController extends Controller
{
    private $WebToPay;
    public function __construct()
    {
        $this->WebToPay = app(WebToPay::class);
        //стартовый шаблон
        $this->template = env('THEME_RESOURCES') . '.index';
    }

    static public function index($request)
    {
        try {
            $requestData = $request instanceof Request ? $request->all() : (array) $request;

            $projectId = (string) config('paysera.project_id');
            $signPassword = (string) config('paysera.sign_password');
            if ($projectId === '' || $signPassword === '') {
                throw new Exception('Paysera is not configured.');
            }

            $acceptUrl = Arr::get($requestData, 'accepturl');
            $cancelUrl = Arr::get($requestData, 'cancelurl');
            $callbackUrl = Arr::get($requestData, 'callbackurl');
            if (! $acceptUrl || ! $cancelUrl || ! $callbackUrl) {
                $selfUrl = PayseraController::getSelfUrl();
                $acceptUrl = $acceptUrl ?: $selfUrl . '/pay_accept';
                $cancelUrl = $cancelUrl ?: $selfUrl . '/pay_cancel';
                $callbackUrl = $callbackUrl ?: $selfUrl . '/pay_callback';
            }

            $paymentUrl = WebToPay::buildPaymentUrl([
                'projectid' => $projectId,
                'sign_password' => $signPassword,
                'orderid' => Arr::get($requestData, 'order_id'),
                'amount' => (int) Arr::get($requestData, 'payseraTotalPrice'),
                'currency' => 'EUR',
                'country' => Arr::get($requestData, 'country'),
                'p_firstname' => Arr::get($requestData, 'name'),
                'p_lastname' => Arr::get($requestData, 'last_name'),
                'p_email' => Arr::get($requestData, 'email'),
                'accepturl' => $acceptUrl,
                'cancelurl' => $cancelUrl,
                'callbackurl' => $callbackUrl,
                'test' => config('paysera.test') ? 1 : 0,
                'payment' => Arr::get($requestData, 'payment'),
            ]);

            return redirect()->away($paymentUrl);
        } catch (Exception $exception) {
            Log::error('Unable to start Paysera checkout.', [
                'order_id' => Arr::get($requestData ?? [], 'order_id'),
                'payment' => Arr::get($requestData ?? [], 'payment'),
                'message' => $exception->getMessage(),
            ]);

            return redirect()->back()->with(
                'error',
                __('Unable to start online payment. Please try again or choose another payment method.')
            );
        }
    }
    //
    static public function getSelfUrl(): string
    {
        return rtrim(url(App::getLocale()), '/');
    }

    function isPaymentValid(array $order, array $response): bool
    {
        if (array_key_exists('payamount', $response) === false) {
            if ($order['amount'] !== $response['amount'] || $order['currency'] !== $response['currency']) {
                throw new Exception('Wrong payment amount');
            }
        } else {
            if ($order['amount'] !== $response['payamount'] || $order['currency'] !== $response['paycurrency']) {
                throw new Exception('Wrong payment amount');
            }
        }

        return true;
    }

    function pay_accept()
    {

        $content = '';
        try {
            $response = WebToPay::validateAndParseData(
                $_REQUEST,
                (string) config('paysera.project_id'),
                (string) config('paysera.sign_password')
            );

            if ($response['status'] === '1' || $response['status'] === '3') {
                //@ToDo: Validate payment amount and currency, example provided in isPaymentValid method.
                //@ToDo: Validate order status by $response['orderid']. If it is not already approved, approve it.

                $order = Orders::where('id', $response['orderid'])->get()->first();
                if (! $order) {
                    throw new Exception('Payment order was not found.');
                }

                // get the id of current order
                // i want to check delivery data in this order by id , i have a function in Order model called getDeliveryData()
                // i want to check if has_invited_sale=1 then i want to know id of the user who invited this sale

                // $order_id = $response['orderid'];
                // $has_invited_sale= $order->getDeliveryData($order_id,'has_invited_sale');
                $order->payment_status = "payed";
                $order->save();
                app(SynvolveWebhookService::class)->notifyOrderSnapshotById((int) $order->id, 'payment_status_changed_paysera');

                // $content = view(env('THEME_RESOURCES') . '.pay.callback')->with('response', $response)->render();
                return Redirect::route('thanks', ['order_id' => $response['orderid'], 'payed' => true ]);
            } else {
                throw new Exception('Payment was not successful');
                //$content = view(env('THEME_RESOURCES') . '.pay.callback')->with('exception', 'Payment was not successful')->render();
                return Redirect::route('thanks', ['order_id' => $response['orderid']]);
            }
        } catch (Throwable $exception) {
            //$content = view(env('THEME_RESOURCES') . '.pay.callback')->with('exception', $exception->getMessage())->render();

            //dd($response, $exception);

            Log::warning('Paysera accept was rejected.', [
                'order_id' => $response['orderid'] ?? null,
                'message' => $exception->getMessage(),
            ]);

            return Redirect::route('cart.index')->with('error', __('Payment confirmation could not be verified.'));
        }


        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'title', 'Оформление заказа');
        return $this->renderOutput();
        // return Redirect::route('thanks');
    }

    function pay_cancel()
    {

        $content = '';
        try {
            $response = WebToPay::validateAndParseData(
                $_REQUEST,
                (string) config('paysera.project_id'),
                (string) config('paysera.sign_password')
            );

            if ($response['status'] === '1' || $response['status'] === '3') {
                //@ToDo: Validate payment amount and currency, example provided in isPaymentValid method.
                //@ToDo: Validate order status by $response['orderid']. If it is not already approved, approve it.

                $order = Orders::where('id', $response['orderid'])->get()->first();
                if (! $order) {
                    throw new Exception('Payment order was not found.');
                }

                // get the id of current order

                // i want to check delivery data in this order by id , i have a function in Order model called getDeliveryData()
                // i want to check if has_invited_sale=1 then i want to know id of the user who invited this sale

                // $order_id = $response['orderid'];
                // $has_invited_sale= $order->getDeliveryData($order_id,'has_invited_sale');
                $order->payment_status = "payed";
                $order->save();
                app(SynvolveWebhookService::class)->notifyOrderSnapshotById((int) $order->id, 'payment_status_changed_paysera');

                // $content = view(env('THEME_RESOURCES') . '.pay.callback')->with('response', $response)->render();
                return Redirect::route('thanks', ['order_id' => $response['orderid'], 'payed' => true ]);
            } else {
                throw new Exception('Payment was not successful');
                //$content = view(env('THEME_RESOURCES') . '.pay.callback')->with('exception', 'Payment was not successful')->render();
                return Redirect::route('thanks', ['order_id' => $response['orderid']]);
            }
        } catch (Throwable $exception) {
            //$content = view(env('THEME_RESOURCES') . '.pay.callback')->with('exception', $exception->getMessage())->render();

            //dd($response, $exception);

            Log::warning('Paysera cancel response was rejected.', [
                'order_id' => $response['orderid'] ?? null,
                'message' => $exception->getMessage(),
            ]);

            return Redirect::route('cart.index')->with('error', __('Payment cancellation could not be verified.'));
        }


        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'title', 'Оформление заказа');
        return $this->renderOutput();
        // return Redirect::route('thanks');
    }

    /// Function i want to check


    function pay_callback($redirect = false)
    {
        try {
            $response = WebToPay::validateAndParseData(
                $_REQUEST,
                (string) config('paysera.project_id'),
                (string) config('paysera.sign_password')
            );

            if ($response['status'] === '1' || $response['status'] === '3') {

                $order = Orders::where('id', $response['orderid'])->get()->first();
                if (! $order) {
                    throw new Exception('Payment order was not found.');
                }
                $order->payment_status = "payed";
                $order->save();
                app(SynvolveWebhookService::class)->notifyOrderSnapshotById((int) $order->id, 'payment_status_changed_paysera');

                return response('OK', 200);
                // $order_id = $response['orderid'];
                // $has_invited_sale= $order->getDeliveryData($order_id,'has_invited_sale');
            } else {
                throw new Exception('Payment was not successful');
            }
        } catch (Throwable $exception) {
            Log::warning('Paysera callback was rejected.', [
                'order_id' => $response['orderid'] ?? null,
                'message' => $exception->getMessage(),
            ]);

            return response('ERROR', 400);
        }
    }
}
