<?php

namespace App\Http\Controllers;

use App;
use App\Models\OrderString;
use App\Models\User;
use App\Services\Invoice\InvoiceSummaryRenderer;
use App\Services\Invoice\InvoiceTotalsCalculator;
use App\Services\Invoice\InvoiceVatResolver;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use URL;

class DynamicPDFController extends Controller
{
    private $summaryRenderer;
    private $totalsCalculator;
    private $vatResolver;

    public function __construct(
        InvoiceSummaryRenderer $summaryRenderer,
        InvoiceTotalsCalculator $totalsCalculator,
        InvoiceVatResolver $vatResolver
    ) {
        $this->summaryRenderer = $summaryRenderer;
        $this->totalsCalculator = $totalsCalculator;
        $this->vatResolver = $vatResolver;
    }

    public function getPDFFromOrder($order, $order_vr_id = null, $form_data = null)
    {
        $orderDataHtml = $this->getOrderDataInHtml($order, $order_vr_id, $form_data);
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($orderDataHtml);
        $filePath = public_path() . '/storage/pdf/' . $order['id'] . '.pdf';
        $pdf->setPaper('a4', 'portrait')->setWarnings(false)->save($filePath);
        $home_url = URL::to('/');

        return $home_url . '/storage/pdf/' . $order['id'] . '.pdf';
    }

    public function getOrderDataInHtml($order, $order_vr_id, $form_data)
    {
        $order_vr_id = (string) $order_vr_id;

        if (isset($order['sale_eur']) && $order['sale_eur'] != '' && $order['sale_eur'] != null && $order['sale_eur'] != 0) {
            $order['sale_price'] = (float)$order['sale_price'] - (float)$order['sale_eur'];
        }
        if ($order['sale_percent'] != '' && $order['sale_percent'] != null && $order['sale_percent'] != 0) {
            $sp = (float)$order['sale_price'] * (floatval($order['sale_percent']) / 100);
            $order['sale_price'] = (float)$order['sale_price'] - $sp;
        }

        $salesumm=(float)$order['price']-(float)$order['sale_price'];
        // BAW => viar
        // vr00 => viarstudia
        // $cur_date = date("d.m.Y");

        if ($order['approved_date'] != 'null' && $order['approved_date'] != '') {
            $cur_date = $order['approved_date'];
        } else {
            $cur_date = date('d.m.Y');
        }

        $user_order = User::where('id', $order['user_id'])->get()->first();
        if (!$user_order) {
            $email = App\Models\Orders::getFieldPortraitCalc($order, 'Email');

            if ($email) {
                $user_order = User::where('email', $email)->get()->first();

                if (!$user_order) {
                    $random_pass = Str::random(8);
                    $user_order = new User();
                    $user_order->first_name = '';
                    $user_order->last_name = '';
                    $user_order->email = $email;
                    $user_order->phone = '';
                    $user_order->address = 'LV';
                    $user_order->postal_index = '';
                    $user_order->country = '';
                    $user_order->client_data = 'NO';
                    $user_order->news = 'YES';
                    $user_order->role_id = 2;
                    $user_order->avatar = 'users/default.png';
                    $user_order->active_coupon = null;
                    $user_order->password = Hash::make($random_pass);
                    $settings = $user_order->settings;
                    $settings['locale'] = '';
                    $user_order->settings = $settings;
                    $user_order->save();
                }

                App\Models\Orders::where('id', $order['id'])->update([
                    'user_id' => $user_order->id
                ]);
            }
        }

        $ord_strings = OrderString::where('id', 1)->get()->translate($user_order->preferredLocale(), 'ru')[0];

        $ord_strings['bank_code'] = $ord_strings['bank_code_place'];
        $is_from_formadata_var2 = 0;

        $user_loc = $user_order->preferredLocale();
        $pdf_locale = $user_order->DPFLocale();
        if($pdf_locale) $user_loc = $pdf_locale;

         if($user_loc == 'ee'){
             $user_loc = 'et';
         }

         if($user_order->preferredLocale() == null || $user_order->preferredLocale() == '' || $user_order->preferredLocale() == []){
             $user_loc = 'en';
         }

         // translated strings
         $ord_strings['invoice'] = trans('cart_new.invoice', [], $user_loc);
         $ord_strings['acc_num_place'] = trans('ord_strings.acc_num_place', [], $user_loc);
         $ord_strings['req_num_place'] = trans('ord_strings.req_num_place', [], $user_loc);
         $ord_strings['addr_place'] = trans('ord_strings.addr_place', [], $user_loc);
         $ord_strings['admin_name'] = trans('ord_strings.admin_name', [], $user_loc);
         $ord_strings['bank_name_place'] = trans('ord_strings.bank_name_place', [], $user_loc);
         $ord_strings['bank_name_place2'] = trans('ord_strings.bank_name_place2', [], $user_loc);
         $ord_strings['cel-platezha'] = trans('ord_strings.cel-platezha', [], $user_loc);
         $ord_strings['code_place'] = trans('ord_strings.code_place', [], $user_loc);
         $ord_strings['consignor_place'] = trans('ord_strings.consignor_place', [], $user_loc);
         $ord_strings['customer_place'] = trans('ord_strings.customer_place', [], $user_loc);
         $ord_strings['deliv_addr_place'] = trans('ord_strings.deliv_addr_place', [], $user_loc);
         $ord_strings['legal_addr_place'] = trans('ord_strings.legal_addr_place', [], $user_loc);
         $ord_strings['office_addr_place'] = trans('ord_strings.office_addr_place', [], $user_loc);
         $ord_strings['persons_bot_left'] = trans('ord_strings.persons_bot_left', [], $user_loc);
         $ord_strings['persons_bot_left_name'] = trans('ord_strings.persons_bot_left_name', [], $user_loc);
         $ord_strings['persons_bot_right'] = trans('ord_strings.persons_bot_right', [], $user_loc);
         $ord_strings['persons_bot_right_name'] = trans('ord_strings.persons_bot_right_name', [], $user_loc);
         $ord_strings['prod_am_place'] = trans('ord_strings.prod_am_place', [], $user_loc);
         $ord_strings['prod_name_place'] = trans('ord_strings.prod_name_place', [], $user_loc);
         $ord_strings['prod_nr_place'] = trans('ord_strings.prod_nr_place', [], $user_loc);
         $ord_strings['prod_price_place'] = trans('ord_strings.prod_price_place', [], $user_loc);
         $ord_strings['prod_qty_place'] = trans('ord_strings.prod_qty_place', [], $user_loc);
         $ord_strings['prod_unit_place'] = trans('ord_strings.prod_unit_place', [], $user_loc);
         $ord_strings['pvn_place'] = trans('ord_strings.pvn_place', [], $user_loc);
         $ord_strings['reg_num_place'] = trans('ord_strings.reg_num_place', [], $user_loc);
         $ord_strings['sign_place'] = trans('ord_strings.sign_place', [], $user_loc);
         $ord_strings['total_am_place'] = trans('ord_strings.total_am_place', [], $user_loc);
         $ord_strings['sale_price_text'] = trans('ord_strings.sale_price_text', [], $user_loc);
         $ord_strings['swift'] = trans('ord_strings.swift', [], $user_loc);

        if ($order_vr_id && $order_vr_id != null) {
            if (strpos($order_vr_id, 'BAW') !== false) { //Viar
                $is_from_formadata_var2 = 1;
                $ord_strings['consignor_text'] = $ord_strings['consignor_text_vrv']; //change ViarArt SIA
                $ord_strings['req_num_text'] = $ord_strings['req_num_text_vrv'];
                $ord_strings['addr_text'] = $ord_strings['addr_text_vrv'];
                $ord_strings['pvn_text'] = $ord_strings['pvn_text_vrv'];
                $ord_strings['bank_name_text'] = $ord_strings['bank_name_text_vrv'];
                $ord_strings['office_addr_text'] = $ord_strings['addr2_text_vrv'];
                $ord_strings['acc_num_text'] = $ord_strings['acc_num_text_vrv'];
                $ord_strings['is_pvn_vrv'] = $ord_strings['is_pvn_vrv'];
                $ord_strings['bank_code'] = $ord_strings['bank_code_place_vrv'];
            }

            if (strpos($order_vr_id, 'VRR') !== false) {
                $ord_strings['consignor_text'] = $ord_strings['consignor_text_vrr'];
                $ord_strings['req_num_text'] = $ord_strings['req_num_text_vrr'];
                $ord_strings['addr_text'] = $ord_strings['addr_text_vrr'];
                $ord_strings['pvn_text'] = $ord_strings['pvn_text_vrr'];
                $ord_strings['office_addr_text'] = $ord_strings['office_addr_text_vrr'];
                $ord_strings['acc_num_text'] = $ord_strings['acc_num_text_vrr'];
                $ord_strings['bank_name_text'] = $ord_strings['bank_name_text_vrr'];
                $ord_strings['is_pvn_vrr'] = $ord_strings['is_pvn_vrr'];
                $ord_strings['bank_code'] = $ord_strings['bank_code_place_vrr'];
            }

            if (strpos($order_vr_id, 'DS020') !== false) {
                $ord_strings['consignor_text'] = $ord_strings['consignor_text_vra'];
                $ord_strings['req_num_text'] = $ord_strings['req_num_text_vra'];
                $ord_strings['addr_text'] = $ord_strings['addr_text_vra'];
                $ord_strings['pvn_text'] = $ord_strings['pvn_text_vra'];
                $ord_strings['office_addr_text'] = $ord_strings['addr2_text_vra'];
                $ord_strings['acc_num_text'] = $ord_strings['acc_num_text_vra'];
                $ord_strings['bank_name_text'] = $ord_strings['bank_name_text_vra'];
                $ord_strings['is_pvn_vra'] = $ord_strings['is_pvn_vra'];
                $ord_strings['bank_code'] = $ord_strings['bank_code_place_vra'];
            }

        }

        if ($form_data && $form_data != null) {
            $ord_strings['consignor_text'] = $form_data['name']; // 1
            $ord_strings['req_num_text'] = $form_data['reg_num']; // 2
            $ord_strings['addr_text'] = $form_data['addr']; // 3
            $ord_strings['pvn_text'] = $form_data['vat_num']; // 4
            $ord_strings['bank_name_text'] = $form_data['bank']; //5
            $ord_strings['bank_code'] = $form_data['bank_code']; //6
            $ord_strings['office_addr_text'] = $form_data['office_addr']; // 7
            $ord_strings['acc_num_text'] = $form_data['acc_num']; // 8
        }

        $vat_rate = (float) ($ord_strings['nds'] ?? 0);
        $custom_vat_number = $form_data !== null
            ? (string) ($form_data['vat_num'] ?? '')
            : null;
        $seller_has_vat = $this->vatResolver->sellerHasVat(
            $order_vr_id,
            $ord_strings,
            $custom_vat_number
        );

        if (!$seller_has_vat) {
            $ord_strings['pvn_place'] = '';
            $ord_strings['pvn_text'] = '';
        }

//        $ord_strings['order_vr'] = '';
//        if ($order_vr_id != null) {
//            $order_id = $order_vr_id;
//        } else {
//            $order_id = $order['id'];
//        }

        $ur_name = $order['ur_name'];
        $ur_name_l = $order['ur_name_l'];
        $ur_reg_num = $order['ur_reg_num'];
        $ur_addr = $order['ur_legal_addr'];
        $ur_pnr_nr = $order['ur_pnr_nr'];
        $ur_bank_name = $order['ur_bank_name'];
        $ur_bank_code = $order['ur_bank_code'];
        $ur_bank_acc_code = $order['ur_bank_acc_code'];

        if ($ur_reg_num == '') {
            $ord_strings['reg_num_place'] = '';
        }

        if ($ur_name_l == '') {
            $ord_strings['ur_name_l'] = '';
        }

        if ($ur_addr == '') {
            $ord_strings['legal_addr_place'] = '';
        }

        if ($ur_bank_name == '') {
            $ord_strings['bank_name_place2'] = '';
        }

        if ($ur_pnr_nr == '') {
            $ord_strings['prn_nr_place'] = '';
        }

        if ($ur_bank_code == '') {
            $ord_strings['bank_code2'] = '';
        }

        if ($ur_bank_acc_code == '') {
            $ord_strings['code_place'] = '';
        }

        if($ur_name == "")
        {
            $ord_strings['reg_num_place'] = '';
            $ord_strings['ur_name_l'] = '';
            $ord_strings['legal_addr_place'] = '';
            $ord_strings['bank_name_place2'] = '';
            $ord_strings['prn_nr_place'] = '';
            $ord_strings['bank_code2'] = '';
            $ord_strings['code_place'] = '';

            $ur_reg_num = '';
            $ur_name_l = '';
            $ur_addr = '';
            $ur_bank_name = '';
            $ur_pnr_nr = '';
            $ur_bank_code = '';
            $ur_bank_acc_code = '';
        }

        $del_addr = $order['delivery']['address'] ?? '';

        if(isset($order['items']['total_terms_price']) && (float)$order['items']['total_terms_price'])
        {
            $price = str_replace(' €', '', $order['price']);
            $price = (float)$price + (float)$order['items']['total_terms_price'];
           // $order['sale_price'] = $price;
           // $order['price'] = $price . ' €';

        }
        else
        {
            $price = str_replace(' €', '', $order['price']);
        }

        if ($price != $order['sale_price']) {
            $tot_sale_price = $order['sale_price'];
        } else {
            $tot_sale_price = 0;
        }

        //$logo_img = \URL::to('/') . '/pdf_print_logo.png';
        $logo_img = public_path().'/pdf_print_logo.png';

        if ($is_from_formadata_var2 != 1) {
            if ($order_vr_id && $order_vr_id != null) {
                if (strpos($order_vr_id, 'VR00') !== false) { // ViarStudia
                } else { // Viar
                    // $ord_strings['req_num_place'] = '';
                    // $ord_strings['req_num_text'] = '';
                    // $ord_strings['pvn_place'] = '';
                    // $ord_strings['pvn_text'] = '';
                    // $ord_strings['bank_code_place'] = '';
                    // $ord_strings['acc_num_place'] = '';
                    // $ord_strings['acc_num_text'] = '';
                }
            }
        }


        if(!$ord_strings['req_num_text']){
            $ord_strings['req_num_place'] = '';
        }
        if(!$ord_strings['pvn_text']){
            $ord_strings['pvn_place'] = '';
        }
        if(!$ord_strings['bank_code']){
            $ord_strings['bank_code_place'] = '';
            $ord_strings['swift'] = '';
        }
        if(!$ord_strings['acc_num_text']){
            $ord_strings['acc_num_place'] = '';
        }

        $text = '
        <html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style>
            body { font-family: DejaVu Sans;font-size:12px; }
            .nb_all{ border-bottom: none!important; } .tac{ text-align:center; } .tar{ text-align:right; } table{ max-width:100%; width:100%;} .table__1 tr{ border-bottom: 1px solid #000; } .tr__border{ border-bottom: 1px solid #000; } .table__2 tr{ border: 1px solid #000; }
        </style>
        </head>
        <body>
        <img style="margin-left:auto;margin-right:auto;margin-bottom:20px;text-algin:center;max-width:70px;" src="data:image/png;base64,'.base64_encode(file_get_contents($logo_img)).'">
        <h3 align="center;margin-bottom:25px;">'.$ord_strings['invoice']." ".$order_vr_id. '</h3>
        <h3 class="tac" style="text-align: center;">' . $cur_date . '</h3>
        <table class="table__1" width="100%" style="border-collapse:collapse;max-width: 100%;width: 100%;">


        <tr class="tr__border" style="border-bottom: 1px solid #000;">
            <td style="border-bottom:1px solid #000;">' . $ord_strings['consignor_place'] . '</td>
            <td style="border-bottom:1px solid #000;">' . $ord_strings['consignor_text'] . '</td>
            <td style="border-bottom:1px solid #000;"></td>
            <td style="border-bottom:1px solid #000;text-align: right;">
            ' . $ord_strings['req_num_place'] . ' ' . $ord_strings['req_num_text'] . '</td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td style="border-bottom:1px solid #000;">' . $ord_strings['addr_place'] . '</td>
            <td style="border-bottom:1px solid #000;white-space:nowrap;">' . $ord_strings['addr_text'] . '</td>
            <td style="border-bottom:1px solid #000;"></td>
            <td style="border-bottom:1px solid #000;" class="tar" style="text-align: right;">
            ' . $ord_strings['pvn_place'] . ' ' . $ord_strings['pvn_text'] . '</td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td style="border-bottom:1px solid #000;">' . $ord_strings['bank_name_place'] . '</td>
            <td style="border-bottom:1px solid #000;">' . $ord_strings['bank_name_text'] . '</td>
            <td style="border-bottom:1px solid #000;"></td>
            <td style="border-bottom:1px solid #000;" class="tar" style="text-align: right;">
            ' . $ord_strings['swift'] . ' ' . $ord_strings['bank_code'] . '</td>
        </tr>

        ';
        $text .= '
            <tr style="border-bottom: 1px solid #000;">
                <td style="border-bottom:1px solid #000;white-space:nowrap;">' . $ord_strings['office_addr_place'] . '</td>
                <td style="border-bottom:1px solid #000;white-space:nowrap;">' . $ord_strings['office_addr_text'] . '</td>
                <td style="border-bottom:1px solid  #000;"></td>
                <td style="border-bottom:1px solid #000;white-space:nowrap;" class="tar" style="text-align: right;">
                ' . $ord_strings['acc_num_place'] . ' ' . $ord_strings['acc_num_text'] . '</td>
            </tr>';
        $text .= '
            <tr style="border-bottom: 1px solid #000;">
                <td style="border-bottom:1px solid #000;white-space:nowrap;">' . $ord_strings['cel-platezha'] . '</td>
                <td style="border-bottom:1px solid #000;white-space:nowrap;">' . $order_vr_id . '</td>
                <td style="border-bottom:1px solid  #000;"></td>
                <td style="border-bottom:1px solid #000;white-space:nowrap;" class="tar" style="text-align: right;"></td>
            </tr>';
        $text .= '
        <tr class="nb_all" style="border-bottom: none!important;">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="nb_all" style="border-bottom: none!important;">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="nb_all" style="border-bottom: none!important;">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="nb_all" style="border-bottom: none!important;">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="nb_all" style="border-bottom: none!important;">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="nb_all" style="border-bottom: none!important;">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="nb_all" style="border-bottom: none!important;">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="nb_all" style="border-bottom: none!important;">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        <tr style="border-bottom: 1px solid #000;">
            <td style="border-bottom:1px solid #000;">' . $ord_strings['customer_place'] . '</td>
            <td style="border-bottom:1px solid #000;">' . ($ur_name_l ? $ur_name_l : (($order['delivery']['first_name'] ?? null) . ' ' . ($order['delivery']['last_name'] ?? null))) . '</td>
            <td style="border-bottom:1px solid #000;"></td>
            <td style="border-bottom:1px solid #000;" class="tar" style="text-align: right;">
            ' . $ord_strings['reg_num_place'] . ' ' . $ur_reg_num . '</td>
        </tr>';

        if ($ur_addr != '' || $ur_pnr_nr != '') {
            $text .= '
            <tr style="border-bottom: 1px solid #000;">
                <td style="border-bottom:1px solid #000;">' . $ord_strings['legal_addr_place'] . '</td>
                <td style="border-bottom:1px solid #000;">' . $ur_addr . '</td>
                <td style="border-bottom:1px solid #000;"></td>
                <td style="border-bottom:1px solid #000;" class="tar" style="text-align: right;">
                ' . __('ord_strings.reg_num_place') . ' ' . $ur_pnr_nr . '</td>
            </tr>';
        }

        if ($ur_bank_name != '' || $ur_bank_code != '') {
            $text .= '
            <tr style="border-bottom: 1px solid #000;">
                <td style="border-bottom:1px solid #000;">' . $ord_strings['bank_name_place2'] . '</td>
                <td style="border-bottom:1px solid #000;">' . $ur_bank_name . '</td>
                <td style="border-bottom:1px solid #000;"></td>
                <td style="border-bottom:1px solid #000;" class="tar" style="text-align: right;">
                ' . $ord_strings['bank_code2'] . ' ' . $ur_bank_code . '</td>
            </tr>';
        }

        if ($del_addr != '' || $ur_bank_acc_code != '') {
            $text .= '
            <tr style="border-bottom: 1px solid #000;">
                <td style="border-bottom:1px solid #000;">' . $ord_strings['deliv_addr_place'] . '</td>
                <td style="border-bottom:1px solid #000;">' . $del_addr . '</td>
                <td style="border-bottom:1px solid #000;"></td>
                <td style="border-bottom:1px solid #000;" class="tar" style="text-align: right;">
                ' . $ord_strings['code_place'] . ' ' . $ur_bank_acc_code . '</td>
            </tr>';
        }

        $text .= '
        </table>
        <br>

        <table class="table__2" width="100%" style="border-collapse: collapse;border: 0px;max-width: 100%;width: 100%;">
            <tr style="border: 1px solid #000;">
                <td style="border-bottom:1px solid #000;">' . $ord_strings['prod_nr_place'] . '</td>
                <td style="border-bottom:1px solid #000;">' . $ord_strings['prod_name_place'] . '</td>
                <td style="border-bottom:1px solid #000;">' . $ord_strings['prod_unit_place'] . '</td>
                <td style="border-bottom:1px solid #000;">' . $ord_strings['prod_qty_place'] . '</td>
                <td style="border-bottom:1px solid #000;">' . $ord_strings['prod_price_place'] . '</td>
                <td style="border-bottom:1px solid #000;" class="tar" style="text-align: right;">' . $ord_strings['prod_am_place'] . '</td>
            </tr>
            ';

        $i = 0;
        $raw_terms_price=0;
        $legacy_vat_base = 0;

        foreach ($order['items'] as $product) {
            if (!isset($product['sumPrice'])) {
                continue;
            }

            // if (isset($product['total_item_price'])) {
            //     $product['sumPrice'] = $product['total_item_price'];
            // }

            $prod_add_name = '';

            if (isset($product['show'])) {
                if (isset($product['show']['size'])) {
                    $prod_add_name = $product['show']['size'];
                }
            }

            if (isset($product['size_name'])) {
                $prod_add_name = $product['size_name'];
            }

            if (isset($product['count'])) {
                $quant = $product['count'];
            } else {
                $quant = 1;
            }

            $i++;

            $pr_price = $seller_has_vat
                ? (float) $product['sumPrice'] / 1.21
                : (float) $product['sumPrice'];



            $pr_price_final = number_format($pr_price, 2, '.', '');
            $legacy_vat_base += (float) $pr_price_final;

            $price_for_one=$pr_price_final/$quant;
            $price_for_one=number_format($price_for_one, 2, '.', '');

            $text .= '<tr style="border: 1px solid #000;">
                        <td style="border-bottom:1px solid #000;">' . $i . '</td>
                        <td style="border-bottom:1px solid #000;">' . $product['name'] . ' ' . $prod_add_name . '</td>
                        <td style="border-bottom:1px solid #000;">1</td>
                        <td style="border-bottom:1px solid #000;">' . $quant . '</td>
                        <td style="border-bottom:1px solid #000;">' . $price_for_one . '</td>
                        <td style="border-bottom:1px solid #000;" class="tar" style="text-align: right;">' . $pr_price_final . '&euro;</td>
                    </tr>';

            if(isset($product['terms_price']) && (float)$product['terms_price'] > 0)
            {
                $raw_terms_price +=(float)$product['terms_price'];
                $terms_price = (float)$product['terms_price'];

                if ($seller_has_vat) {
                    $terms_price /= 1.21;
                }

                $terms_price = number_format($terms_price, 2, '.', '');
                $legacy_vat_base += (float) $terms_price;

                $i++;
                    $text .= '<tr style="border: 1px solid #000;">
                        <td style="border-bottom:1px solid #000;">' . $i . '</td>
                        <td style="border-bottom:1px solid #000;">' . $product['name'] .' '. trans('gl.express', [], $user_loc) . '</td>
                        <td style="border-bottom:1px solid #000;">1</td>
                        <td style="border-bottom:1px solid #000;">1</td>
                        <td style="border-bottom:1px solid #000;">' . $terms_price . '</td>
                        <td style="border-bottom:1px solid #000;" class="tar" style="text-align: right;">' . $terms_price . '&euro;</td>
                    </tr>';
            }
        }


        if (isset($order['delivery']['deliv_price'])) {
            $delivery_full_price = number_format($order['delivery']['deliv_price'], 2, '.', '');
            $dost_calc_price = $seller_has_vat
                ? number_format($delivery_full_price / 121 * 100, 2, '.', '')
                : $delivery_full_price;
            $delivery_vat = $seller_has_vat ? $delivery_full_price - $dost_calc_price : 0;
        } else {
            $dost_calc_price = 0;
            $delivery_vat = 0;
        }

        $legacy_vat_amount = $seller_has_vat
            ? number_format($delivery_vat + ($legacy_vat_base / 100 * number_format($ord_strings['nds'], 2, '.', '')), 2, '.', '')
            : 0;
        $pr = str_replace(' €', '', $order['price']);






        if ($salesumm!=0 && ($order['sale_price']!=NULL || $order['sale_price']!=0)){
           // if ($pr != $order['sale_price'] && $order['sale_price'] != null && $order['sale_price'] != '') {
            $pr = $order['sale_price'];

        }


        if (isset($order['delivery']['deliv_price'])) {
            $dp = (float) $order['delivery']['deliv_price'];
        } else {
            $dp = 0;
        }

        $price_shown = number_format((float)$pr + (float)$dp + (float)$raw_terms_price, 2, '.', '');
        $invoice_totals = $this->totalsCalculator->calculateUsingVatAmount(
            $price_shown,
            $legacy_vat_amount,
            $vat_rate,
            $seller_has_vat
        );





        /// TODO:  PDF тут вставляем строку со скидкой если есть
        ///

     $sale_html='<tr style="border: 1px solid #000;">
                <td>' .  $ord_strings['sale_price_text'] . '</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="tar" style="text-align: right;">-'.$salesumm.'&euro;</td>
            </tr>';


        if ($salesumm!=0 && ($order['sale_price']!=NULL || $order['sale_price']!=0)){  $text .=$sale_html;  }

        $text .= ' <tr style="border: 1px solid #000;">
                <td>' . $ord_strings['deliv_price'] . '</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="tar" style="text-align: right;">' . $dost_calc_price . '&euro;</td>
            </tr>';
        $text .= $this->summaryRenderer->render($invoice_totals, $user_loc);
        $text .= '</table>
        </body>
        </html>';

        return $text;
    }

}
