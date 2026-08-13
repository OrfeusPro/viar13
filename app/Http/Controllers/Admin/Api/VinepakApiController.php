<?php

namespace App\Http\Controllers\Admin\Api;

use DB;
use App;
use Session;
use App\Models\CountryTel;
use App\Models\DeliveryPickupAtViarWorkshop;
use App\Models\VenipakData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class VinepakApiController extends Controller
{
    // test => http://venipak.uat.megodata.com/siunta2/contact.php

    // const USER = "viarstudiademo";

    // const PASS = "uh02gv663";

    // const LOGIN_ID = "17339";

    // const IMPORT_URL = "http://venipak.uat.megodata.com/import/send.php";

    // const PRINT_URL = "https://go.venipak.lt/ws/print_link";

    // main => https://go.venipak.lt/siunta2/contact.php

    // const USER = 'viarstudia';

    // const PASS = 'viarstudia';

    // const LOGIN_ID = '08354';

    // const IMPORT_URL = 'https://go.venipak.lt/import/send.php';

    // const PRINT_URL = 'https://go.venipak.lt/ws/print_label';

    public function __construct()
    {
        $this->venipak_user = VenipakData::where("is_active", true)->first()->user ?? '';
        $this->venipak_pass= VenipakData::where("is_active", true)->first()->pass ?? '';
        $this->venipak_login_id= VenipakData::where("is_active", true)->first()->login_id ?? '';
        $this->venipak_import_url= VenipakData::where("is_active", true)->first()->import_url ?? 'https://go.venipak.lt/import/send.php';
        $this->venipak_print_url= VenipakData::where("is_active", true)->first()->print_url ?? 'https://go.venipak.lt/ws/print_label';
    }

    public function get_towns(Request $request)
    {
        $arr = array();
        //dd($request);
        $arr['country'] = $_GET['country'];

        if($_GET['country'] == 'EE')
        {
            $arr['country'] = 'ET'; //убираем метод доставки для EE страны
        }
        //$arr['city'] = 'Aizkraukle';
        //$arr['zip'] = '';
        //$arr['pick_up_enabled'] = '1';
        //$arr['type'] = '1';

        $get_pickup_points_list = 'https://go.venipak.lt/ws/get_pickup_points?' . http_build_query($arr);
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $get_pickup_points_list,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POSTFIELDS => [
                'user' => $this->venipak_user,
                'pass' => $this->venipak_pass,
                'login_id' => $this->venipak_login_id,
            ],
        ]);

        $response = curl_exec($curl);
        $r_data = json_decode($response, true);

        curl_close($curl);

        $response = collect($r_data);

        $filtered = '';
        $filtered = $response->filter(function ($value, $key) {
            if ($value['type'] == '1') {
                return $value;
            }
        });

        $unique = $response->unique('city');

        $towns = view(env('THEME_RESOURCES') . '.cart.citys')->with('citys', $unique)->render();
        $pickup_points = view(env('THEME_RESOURCES') . '.cart.warehouses')->with('warehouses', $response)->render();

        $c_tels = CountryTel::where('country_code', $_GET['country'])->first();
        if (!$c_tels) {
            Log::warning('Api\VinepakApiController.php CountryTel not found for country', [
                '_GET[country]'     => $_GET['country'],
                'user_id'     => auth()->id(),
                'ip'          => request()->ip(),
                'url'         => request()->fullUrl(),
                'route'       => optional(request()->route())->getName(),
                'coupon_type' => session('coupon_type'),
                'user_agent'  => request()->header('User-Agent')
            ]);
        }

        $res = [];

        $coupon_type = Session::get('coupon_type');

        // dd($coupon_type);
        if(isset($coupon_type) && $coupon_type=='free_delivery'){
            $c_tels['deliv_price'] = 0;
            $c_tels['delivery_venipak'] = 0;
        }

        if(count($response))
        {
            $res['success'] = 1;
            $res['towns'] = $towns;
            $res['pickup_points'] = $pickup_points;
            $res['count'] = count($response);
            $res['delivery_price'] = (float)$c_tels['deliv_price'];
            $res['delivery_venipak'] = (float)$c_tels['delivery_venipak'];
            return json_encode($res);
        }
        else
        {
            $res['success'] = 0;
            $res['delivery_price'] = (float)$c_tels['deliv_price'];
            $res['delivery_venipak'] = (float)$c_tels['delivery_venipak'];
            return json_encode($res);
        }

        // dd($unique);
        // return $unique;
    }

    public function get_towns_for_admin(Request $request)
    {
        $arr = array();
        $arr['country'] = $_GET['country'];

        $get_pickup_points_list = 'https://go.venipak.lt/ws/get_pickup_points?' . http_build_query($arr);
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $get_pickup_points_list,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POSTFIELDS => [
                'user' => $this->venipak_user,
                'pass' => $this->venipak_pass,
                'login_id' => $this->venipak_login_id,
            ],
        ]);

        $response = curl_exec($curl);
        $r_data = json_decode($response, true);

        curl_close($curl);

        $response = collect($r_data);

        $filtered = '';
        $filtered = $response->filter(function ($value, $key) {
            if ($value['type'] == '1') {
                return $value;
            }
        });

        $unique = $response->unique('city')->values()->all();

        $towns = $unique;
        $pickup_points = $response;

        if(count($response))
        {
            $res['success'] = 1;
            $res['towns'] = $towns;
            $res['pickup_points'] = $pickup_points;
            $res['count'] = count($response);

            return json_encode($res);
        }
        else
        {
            $res['success'] = 0;

            return json_encode($res);
        }
    }


    public function get_warehouse(Request $request)
    {
        $arr = array();
        $arr['country'] = $_GET['country'];
        $arr['city'] = $_GET['city'];


        $get_pickup_points_list = 'https://go.venipak.lt/ws/get_pickup_points?' . http_build_query($arr);
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $get_pickup_points_list,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POSTFIELDS => [
                'user' => $this->venipak_user,
                'pass' => $this->venipak_pass,
                'login_id' => $this->venipak_login_id,
            ],
        ]);

        $response = curl_exec($curl);
        $r_data = json_decode($response, true);

        curl_close($curl);

        $response = collect($r_data);

        $filtered = '';
        $filtered = $response->filter(function ($value, $key) {
            if ($value['type'] == '1') {
                return $value;
            }
        });

         $pickup_points = view(env('THEME_RESOURCES') . '.cart.warehouses')->with('warehouses', $response)->render();

        $res = [];

        if(count($response))
        {
            $res['success'] = 1;
            $res['pickup_points'] = $pickup_points;
            $res['count'] = count($response);
            return json_encode($res);
        }
        else
        {
            $res['success'] = 0;
            return json_encode($res);
        }

    }


    public function send_courier(Request $request)
    {
        $date = explode('-', $request->input('metai_s'));

        // generate xml

        $xml_text = $this->generate_courier_xml($request, $year = $date[0], $month = $date[1], $day = $date[2]);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->venipak_import_url,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POSTFIELDS => [
                'user' => $this->venipak_user,
                'pass' => $this->venipak_pass,
                'login_id' => $this->venipak_login_id,
                'xml_text' => $xml_text,
            ],
        ]);

        $response = curl_exec($curl);
        $r_data = simplexml_load_string($response);
        $r_data = json_decode(json_encode((array) $r_data), true);

        curl_close($curl);
        if (isset($r_data['error'])) {
            return back()->with('error_vin', $r_data['error']['text']);
        } else {
            $succ = [
                'type' => $r_data['@attributes']['type'],
                'text' => $r_data['text'],
                'value' => '',
            ];

            return back()->with('success_vin', $succ);
        }
    }

    protected function generate_courier_xml($request, $year, $month, $day)
    {
        return '<?xml version="1.0" encoding="UTF-8"?>

        <description type="3">

            <sender>

                <name>' . $request->input('s_name') . '</name>

                <company_code>' . $request->input('s_code') . '</company_code>

                <country>' . $request->input('s_country') . '</country>

                <city>' . $request->input('s_city') . '</city>

                <address>' . $request->input('s_address') . '</address>

                <post_code>' . $request->input('s_post') . '</post_code>

                <contact_person>' . $request->input('s_contact_p') . '</contact_person>

                <contact_tel>' . $request->input('s_contact_t') . '</contact_tel>

                <contact_email>' . $request->input('contact_mail') . '</contact_email>

            </sender>

            <weight>' . $request->input('svoris_s') . '</weight>

            <volume>' . $request->input('turis_s') . '</volume>

            <pallets>' . $request->input('pallets_s') . '</pallets>

            <date_y>' . $year . '</date_y>

            <date_m>' . $month . '</date_m>

            <date_d>' . $day . '</date_d>

            <hour_from>' . $request->input('val_nuo_s') . '</hour_from>

            <min_from>' . $request->input('min_nuo_s') . '</min_from>

            <hour_to>' . $request->input('val_iki_s') . '</hour_to>

            <min_to>' . $request->input('min_iki_s') . '</min_to>

            <comment>' . $request->input('pastabos_s') . '</comment>

            <doc_no>' . $request->input('doknr_s') . '</doc_no>

        </description>';
    }

    public function create_label(Request $request)
    {
        $orderId = (int) $request->input('order_id');
        $order = DB::table('orders')->where('id', $orderId)->first();

        if (!$order) {
            return back()
                ->withErrors(['label' => 'Заказ не найден.'])
                ->with('error_vin', 'Заказ не найден.');
        }

        $delivery = json_decode((string) ($order->delivery ?? ''), true);
        if (!is_array($delivery)) {
            return back()
                ->withErrors(['label' => 'Невозможно прочитать данные доставки заказа.'])
                ->with('error_vin', 'Невозможно прочитать данные доставки заказа.');
        }

        $isPickupOrder = $this->isPickupDeliveryMethod((string) ($delivery['sposob'] ?? ''));

        [$labelPayload, $validationError] = $this->buildLabelPayload($request, $delivery, $isPickupOrder, $orderId);
        if ($validationError !== null) {
            Log::warning('Venipak label rejected due to payload conflict', [
                'order_id' => $orderId,
                'delivery_method' => $delivery['sposob'] ?? null,
                'requested_destination' => $request->input('destination'),
                'pickup_workshop_id' => (int) ($delivery['pickup_workshop_id'] ?? 0),
                'reason' => $validationError,
            ]);

            return back()
                ->withErrors(['label' => $validationError])
                ->withInput()
                ->with('error_vin', $validationError);
        }

        Log::info('Venipak label payload prepared', [
            'order_id' => $orderId,
            'delivery_method' => $delivery['sposob'] ?? null,
            'destination' => $labelPayload['destination'] ?? null,
            'pickup_workshop_id' => (int) ($delivery['pickup_workshop_id'] ?? 0),
        ]);

        $date = date('ymd');

        $packs = DB::table('orders')->where('id', $orderId)->pluck('items')->first();

        $r_data = json_decode(json_encode((array) $packs), true);

        $packs = $this->generate_label_packs(
            $request->input('p_svoris'),
            $request->input('p_turis'),

            // $request->input('p_doc_nr'),

            $request->input('p_pallet'),
            $orderId
        );

        // generate xml

        $man_no = $request->input('man_no');

        $xml_text = $this->generate_label_xml($labelPayload, $date, $packs, $man_no);

        $response = $this->sendVenipakLabelRequest($xml_text);

        $r_data = simplexml_load_string($response);

        $r_data = json_decode(json_encode((array) $r_data), true);

        if (isset($r_data['error'])) {
            dd(($r_data['error']));

            return back()->with('error_vin', $r_data['error']['text']);
        } else {
            $db_label = '';

            if (is_array($r_data['text'])) {
                foreach ($r_data['text'] as $label) {
                    $db_label .= $label . ',';
                }

                rtrim($db_label, ',');
            } else {
                $db_label = $r_data['text'];
            }

            DB::table('orders')->where('id', $request->input('order_id'))
                ->update(['labels' => $db_label]);

            $succ = [

                'type' => $r_data['@attributes']['type'],

                'text' => $r_data['text'],

                'value' => 'print_label',

            ];

            return back()->with('success_vin', $succ);
        }
    }

    protected function generate_label_packs(
        $weight,
        $vol,

        //  $n_doc,

        $pallet,
        $order_id
    ) {

        // number of pack 0000001

        $iter_count = count($weight);

        $packs = '';

        for ($i = 0; $i < $iter_count; $i++) {
            $n_pos_counter = $i + 1;

            $n_pos = str_pad($order_id + $n_pos_counter + 1, 7, 7, STR_PAD_LEFT);

            $packs .=

                '<pack>

                <pack_no>V' . $this->venipak_login_id . 'E' . $n_pos . '</pack_no>

                <doc_no></doc_no>

                <weight>' . $weight[$i] . '</weight>

                <volume>' . $vol[$i] . '</volume>

                <pallets>' . $pallet[$i] . '</pallets>

            </pack>';
        }

        return $packs;
    }

    protected function generate_label_xml(array $payload, $date, $packs, $man_no)
    {
        $man_no_mod = str_pad(01, 3, '0', STR_PAD_LEFT);

        $destination = (string) data_get($payload, 'destination', 'address');
        $full_addr = $destination === 'pickup'
            ? data_get($payload, 'g_address_pickup', '')
            : (data_get($payload, 'g_address', '') . ', ' . data_get($payload, 'g_house', '') . ' - ' . data_get($payload, 'g_flat', ''));

        return '<?xml version="1.0" encoding="UTF-8"?>

        <description type="1">

        <manifest title="' . $this->venipak_login_id . $date . $man_no_mod . '"

        name="Отправка' . $date . '" >

            <sender>

                <name>' . data_get($payload, 's_name') . '</name>

                <company_code>' . data_get($payload, 's_code') . '</company_code>

                <country>' . data_get($payload, 's_country') . '</country>

                <city>' . data_get($payload, 's_city') . '</city>

                <address>' . data_get($payload, 's_address') . '</address>

                <post_code>' . data_get($payload, 's_post') . '</post_code>

                <contact_person>' . data_get($payload, 's_contact_p') . '</contact_person>

                <contact_tel>' . data_get($payload, 's_contact_t') . '</contact_tel>

                <contact_email>' . data_get($payload, 'email_sender') . '</contact_email>

            </sender>

            <doc_no>' . data_get($payload, 'doc_no') . '</doc_no>

            <shipment>

                <consignee>

                    <name>' . data_get($payload, 'g_name') . '</name>

                    <company_code>' . data_get($payload, 'g_code') . '</company_code>

                    <country>' . data_get($payload, 'r_country') . '</country>

                    <city>' . ($destination === 'pickup' ? data_get($payload, 'g_city_pickup') : data_get($payload, 'g_city')) . '</city>

                    <address>' . $full_addr . '</address>

                    <post_code>' . ($destination === 'pickup' ? data_get($payload, 'g_post_pickup') : data_get($payload, 'g_post')) . '</post_code>

                    <contact_person>' . data_get($payload, 'g_contact_p') . '</contact_person>

                    <contact_tel>' . data_get($payload, 'g_contact_t') . '</contact_tel>

                    <contact_email>' . data_get($payload, 'email_receiver') . '</contact_email>

                </consignee>

                <comment_door_code>' . data_get($payload, 'door_code') . '</comment_door_code>

                <comment_office_no>' . data_get($payload, 'office_no') . '</comment_office_no>

                <comment_warehous_no>' . data_get($payload, 'warehous_no') . '</comment_warehous_no>

                <attribute>

                    <shipment_code>' . data_get($payload, 'order_id') . '</shipment_code>

                    <delivery_type>' . data_get($payload, 'delivery_type') . '</delivery_type>

                    <doc_no>' . data_get($payload, 'doc_no') . '</doc_no>

                    <delivery_type>' . data_get($payload, 'delivery_type') . '</delivery_type>

                    <delivery_mode>' . data_get($payload, 'delivery_express') . '</delivery_mode>

                    <cod>' . data_get($payload, 'cod') . '</cod>

                    <cod_type>' . data_get($payload, 'cod_type') . '</cod_type>

                    <comment_call>' . data_get($payload, 'comment_call') . '</comment_call>

                    <four_hands>' . data_get($payload, 'four_hands') . '</four_hands>

                </attribute>' . $packs . '

            </shipment>

        </manifest>

        </description>';
    }

    protected function buildLabelPayload(Request $request, array $delivery, bool $isPickupOrder, int $orderId): array
    {
        $destination = strtolower(trim((string) $request->input('destination', $isPickupOrder ? 'pickup' : 'address')));
        if ($isPickupOrder) {
            $destination = 'pickup';
        }

        $payload = [
            'order_id' => $orderId,
            'destination' => $destination,
            'doc_no' => trim((string) $request->input('doc_no', '')),
            's_name' => trim((string) $request->input('s_name', '')),
            's_code' => trim((string) $request->input('s_code', '')),
            's_country' => trim((string) $request->input('s_country', '')),
            's_city' => trim((string) $request->input('s_city', '')),
            's_address' => trim((string) $request->input('s_address', '')),
            's_post' => trim((string) $request->input('s_post', '')),
            's_contact_p' => trim((string) $request->input('s_contact_p', '')),
            's_contact_t' => trim((string) $request->input('s_contact_t', '')),
            'email_sender' => trim((string) $request->input('email_sender', '')),
            'g_name' => trim((string) $request->input('g_name', '')),
            'g_code' => trim((string) $request->input('g_code', '')),
            'r_country' => trim((string) $request->input('r_country', '')),
            'g_city' => trim((string) $request->input('g_city', '')),
            'g_address' => trim((string) $request->input('g_address', '')),
            'g_house' => trim((string) $request->input('g_house', '')),
            'g_flat' => trim((string) $request->input('g_flat', '')),
            'g_post' => trim((string) $request->input('g_post', '')),
            'g_contact_p' => trim((string) $request->input('g_contact_p', '')),
            'g_contact_t' => trim((string) $request->input('g_contact_t', '')),
            'email_receiver' => trim((string) $request->input('email_receiver', '')),
            'door_code' => trim((string) $request->input('door_code', '')),
            'office_no' => trim((string) $request->input('office_no', '')),
            'warehous_no' => trim((string) $request->input('warehous_no', '')),
            'delivery_type' => trim((string) $request->input('delivery_type', '')),
            'delivery_express' => trim((string) $request->input('delivery_express', '')),
            'cod' => trim((string) $request->input('cod', '')),
            'cod_type' => trim((string) $request->input('cod_type', '')),
            'comment_call' => trim((string) $request->input('comment_call', '')),
            'four_hands' => trim((string) $request->input('four_hands', '')),
        ];

        $pickupContext = $this->resolvePickupContext($delivery);
        $pickupContextTitle = trim((string) data_get($pickupContext, 'title', ''));

        if ($destination === 'pickup') {
            $payload['g_city_pickup'] = trim((string) $request->input('g_city_pickup', ''));
            $payload['g_address_pickup'] = trim((string) $request->input('g_address_pickup', ''));
            $payload['g_post_pickup'] = trim((string) $request->input('g_post_pickup', ''));
            $payload['g_name_pickup'] = trim((string) $request->input('g_name_pickup', ''));
            $payload['g_code_pickup'] = trim((string) $request->input('g_code_pickup', ''));

            if ($payload['g_city_pickup'] === '' || $payload['g_address_pickup'] === '' || $payload['g_post_pickup'] === '') {
                return [[], 'Для pickup-этикетки не заполнены обязательные поля точки выдачи.'];
            }

            if ($payload['g_name_pickup'] === '' || $payload['g_code_pickup'] === '') {
                return [[], 'Для pickup-этикетки выберите пункт выдачи из списка Venipak.'];
            }

            $payload['g_name'] = $payload['g_name_pickup'];
            $payload['g_code'] = $payload['g_code_pickup'];

            if ($isPickupOrder && $pickupContextTitle !== '') {
                Log::info('Venipak pickup label resolved from order delivery', [
                    'order_id' => $orderId,
                    'pickup_workshop_id' => data_get($pickupContext, 'id'),
                    'pickup_title' => $pickupContextTitle,
                ]);
            }

            return [$payload, null];
        }

        if ($isPickupOrder) {
            return [[], 'Заказ со способом доставки pickup нужно печатать в режиме "На отделение".'];
        }

        if ($payload['g_address'] === '' && $payload['g_city'] === '' && $payload['g_post'] === '') {
            return [[], 'Для этикетки на адрес не заполнены данные получателя.'];
        }

        return [$payload, null];
    }

    protected function resolvePickupContext(array $delivery): array
    {
        $pickupWorkshopId = (int) ($delivery['pickup_workshop_id'] ?? 0);
        $pickupRow = null;

        if ($pickupWorkshopId > 0 && DB::getSchemaBuilder()->hasTable('delivery_pickup_at_viar_workshop')) {
            $pickupRow = DeliveryPickupAtViarWorkshop::query()
                ->select('id', 'title', 'country_code')
                ->where('id', $pickupWorkshopId)
                ->first();
        }

        return [
            'id' => $pickupRow ? (int) $pickupRow->id : ($pickupWorkshopId > 0 ? $pickupWorkshopId : null),
            'title' => $pickupRow ? trim((string) $pickupRow->title) : trim((string) ($delivery['address'] ?? $delivery['city'] ?? '')),
            'country_code' => $pickupRow ? trim((string) ($pickupRow->country_code ?? '')) : null,
        ];
    }

    protected function isPickupDeliveryMethod(string $method): bool
    {
        return in_array($method, ['pickup_at_viar_workshop', 'pickup_Riga', 'pickup_Daugavplis', 'pickup_Daugavpils'], true);
    }

    protected function sendVenipakLabelRequest(string $xmlText): string
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->venipak_import_url,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POSTFIELDS => [
                'user' => $this->venipak_user,
                'pass' => $this->venipak_pass,
                'login_id' => $this->venipak_login_id,
                'xml_text' => $xmlText,
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return (string) $response;
    }

    public function print_label(Request $request)
    {
        $label_code = $request->input('label_code');

        $curl = curl_init();

        curl_setopt_array($curl, [

            CURLOPT_URL => $this->venipak_print_url,

            CURLOPT_RETURNTRANSFER => 1,

            CURLOPT_POST => 1,

            CURLOPT_POSTFIELDS => [

                'user' => $this->venipak_user,

                'pass' => $this->venipak_pass,

                'login_id' => $this->venipak_login_id,

                'pack_no' => $label_code,

            ],

        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $err = substr($response, 0, 5);

        if ($response != '' && $err != 'Error') {
            return response($response)
                ->withHeaders([

                    'Content-Type' => 'application/pdf',

                    'Cache-Control' => 'no-store, no-cache',

                    'Content-Disposition' => 'attachment; filename="' . $label_code . '.pdf',

                ]);
        } else {
            return back()->with('error_vin', $response);
        }
    }
}
