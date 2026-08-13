<?php

namespace App\Http\Controllers\Libwebtopay;

use Exception;
use Redirect;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Libwebtopay\WebToPay;
use App\Models\Orders;
use App\Services\SynvolveWebhookService;

class PayseraController extends Controller
{
    private $WebToPay;
    const projectid = '230308'; //номер проекта
    const sign_password = '79b7cdcd14db14e9cb498f1793817d69'; // пароль проекта

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

            WebToPay::redirectToPayment([
                'projectid' => self::projectid,
                'sign_password' => self::sign_password,
                'orderid' => Arr::get($requestData, 'order_id'),
                'amount' => (int) Arr::get($requestData, 'payseraTotalPrice'),
                'currency' => 'EUR',
                'country' => Arr::get($requestData, 'country'),
                'p_firstname' => Arr::get($requestData, 'name'),
                'p_lastname' => Arr::get($requestData, 'last_name'),
                'p_email' => Arr::get($requestData, 'email'),
                'accepturl' => Arr::get($requestData, 'accepturl', PayseraController::getSelfUrl() . '/pay_accept'),
                'cancelurl' => Arr::get($requestData, 'cancelurl', PayseraController::getSelfUrl() . '/pay_cancel'),
                'callbackurl' => Arr::get($requestData, 'callbackurl', PayseraController::getSelfUrl() . '/pay_callback'),
                'test' => 0,
                'payment' => Arr::get($requestData, 'payment'),
            ]);

        } catch (Exception $exception) {
            dd($exception->getMessage());
        }

        exit();
    }
    //
    static public function getSelfUrl(): string
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
        return $url;
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
                self::projectid,
                self::sign_password
            );

            if ($response['status'] === '1' || $response['status'] === '3') {
                //@ToDo: Validate payment amount and currency, example provided in isPaymentValid method.
                //@ToDo: Validate order status by $response['orderid']. If it is not already approved, approve it.

                $order = Orders::where('id', $response['orderid'])->get()->first();

                // get the id of current order
                // i want to check delivery data in this order by id , i have a function in Order model called getDeliveryData()
                // i want to check if has_invited_sale=1 then i want to know id of the user who invited this sale

                // $order_id = $response['orderid'];
                // $has_invited_sale= $order->getDeliveryData($order_id,'has_invited_sale');
                $order->payment_status = "payed";
                $order->save();
                app(SynvolveWebhookService::class)->notifyOrderSnapshotById((int) $order->id, 'payment_status_changed_paysera');

                echo 'OK';
                // $content = view(env('THEME_RESOURCES') . '.pay.callback')->with('response', $response)->render();
                return Redirect::route('thanks', ['order_id' => $response['orderid'], 'payed' => true ]);
            } else {
                throw new Exception('Payment was not successful');
                //$content = view(env('THEME_RESOURCES') . '.pay.callback')->with('exception', 'Payment was not successful')->render();
                return Redirect::route('thanks', ['order_id' => $response['orderid']]);
            }
        } catch (Exception $exception) {
            //$content = view(env('THEME_RESOURCES') . '.pay.callback')->with('exception', $exception->getMessage())->render();

            //dd($response, $exception);

            if(isset($response['orderid']))
            {
                return Redirect::route('thanks', ['order_id' => $response['orderid']]);
            }
            else
            {
                return Redirect::route('thanks', ['exception' => $exception->getMessage()]);
            }
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
                self::projectid,
                self::sign_password
            );

            if ($response['status'] === '1' || $response['status'] === '3') {
                //@ToDo: Validate payment amount and currency, example provided in isPaymentValid method.
                //@ToDo: Validate order status by $response['orderid']. If it is not already approved, approve it.

                $order = Orders::where('id', $response['orderid'])->get()->first();

                // get the id of current order

                // i want to check delivery data in this order by id , i have a function in Order model called getDeliveryData()
                // i want to check if has_invited_sale=1 then i want to know id of the user who invited this sale

                // $order_id = $response['orderid'];
                // $has_invited_sale= $order->getDeliveryData($order_id,'has_invited_sale');
                $order->payment_status = "payed";
                $order->save();
                app(SynvolveWebhookService::class)->notifyOrderSnapshotById((int) $order->id, 'payment_status_changed_paysera');

                echo 'OK';
                // $content = view(env('THEME_RESOURCES') . '.pay.callback')->with('response', $response)->render();
                return Redirect::route('thanks', ['order_id' => $response['orderid'], 'payed' => true ]);
            } else {
                throw new Exception('Payment was not successful');
                //$content = view(env('THEME_RESOURCES') . '.pay.callback')->with('exception', 'Payment was not successful')->render();
                return Redirect::route('thanks', ['order_id' => $response['orderid']]);
            }
        } catch (Exception $exception) {
            //$content = view(env('THEME_RESOURCES') . '.pay.callback')->with('exception', $exception->getMessage())->render();

            //dd($response, $exception);

            if(isset($response['orderid']))
            {
                return Redirect::route('thanks', ['order_id' => $response['orderid']]);
            }
            else
            {
                return Redirect::route('thanks', ['exception' => $exception->getMessage()]);
            }
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
                self::projectid,
                self::sign_password
            );

            if ($response['status'] === '1' || $response['status'] === '3') {

                $order = Orders::where('id', $response['orderid'])->get()->first();
                $order->payment_status = "payed";
                $order->save();
                app(SynvolveWebhookService::class)->notifyOrderSnapshotById((int) $order->id, 'payment_status_changed_paysera');

                echo 'OK';
                // $order_id = $response['orderid'];
                // $has_invited_sale= $order->getDeliveryData($order_id,'has_invited_sale');
            } else {
                throw new Exception('Payment was not successful');
            }
        } catch (Exception $exception) {
            echo get_class($exception) . ':' . $exception->getMessage();
        }
    }
}
