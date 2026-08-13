<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Viar Art Canvas</title>
    <style type="text/css">
        html {
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }

        body {
            margin: 0;
            background-color: #eaeaea;
            font-family: 'Arial', sans-serif;
        }

        table {
            border-spacing: 0;
        }

        td {
            padding: 0;
        }

        img {
            border: 0;
        }

        a {
            text-decoration: none;
            cursor: pointer;
        }

        a[href^="mailto:"] {
            color: #000000 !important;
            text-decoration: none !important;
        }

        a[href^="tel:"] {
            color: #000000 !important;
            text-decoration: none !important;
        }

        p {
            margin: 0!important;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #eaeaea;
            padding-bottom: 60px;
        }

        .main {
            width: 100%;
            max-width: 600px;
            background-color: #fffaf2;
            margin: 0 auto;
            border-spacing: 0;
            font-family: sans-serif;
        }

        .mobile {
            display: none !important;
        }

        @media screen and (max-width: 613px) {
            .desktop {
                display: none !important;
            }
            .mobile {
                display: block !important;
            }
        }
    </style>
</head>

<body>
    <!-- Preheader text -->
    <div style="display: none; max-height: 0; overflow: hidden;">
        Viar Art Canvas
    </div>
    <!-- Do not edit the div below, it hides other text from showing in preheader using &zwnj;&nbsp; -->
    <div style="display: none; max-height: 0; overflow: hidden;">
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
    </div>
    <!-- Preheader text end -->
    <center class="wrapper">
        <table class="main" width="100%">
            <tr>
                <td height="20" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <!-- logo and phone -->

			@include('.mail.main_head')

            <tr>
                <td height="40" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="left" valign="top" bgcolor="#F7E2E3" style="padding-left: 10px;padding-right: 10px;">
                    <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px;
						color: #1E2533;margin: 0;">
							@lang('mail.mail_account_for_payment_title_1')
							<span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px;
						color: #fa7846;">@lang('mail.mail_account_for_payment_title_2')</span>

                    </span>
                </td>
            </tr>
            <tr>
                <td height="20" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="left" valign="top" bgcolor="#F7E2E3" style="padding-left: 10px;padding-right: 10px;">
                    <p style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 20px;
					line-height: 33px;color: #1E2533;">@lang('mail.order_number'){{$order_id}}</p>
                </td>
            </tr>
            <tr>
                <td height="5" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>


			@php

			if(isset($order_vrv))
			{
				$order_vrv = (array)$order_vrv;
				//dd($order_vrv);
				if(isset($order_vrv['vrv_1']) && $order_vrv['vrv_1'] != null){
					$order_vr_id = 'VR00' . $order_vrv['vrv_1'];
				}

				else if(isset($order_vrv['vrv_2']) && $order_vrv['vrv_2'] != null){
					$order_vr_id = 'BAW' . $order_vrv['vrv_2'];
				}

				else if(isset($order_vrv['vrv_3']) && $order_vrv['vrv_3'] != null){
					$vrv_3 = $order_vrv['vrv_3'];
					if($vrv_3<10){
						$vrv_3_mod = '00'.$vrv_3;
					}
					else if($vrv_3<100 && $vrv_3>9){
						$vrv_3_mod = '0'.$vrv_3;
					} else{
						$vrv_3_mod = $vrv_3;
					}
					$order_vr_id = 'VRR445' . $vrv_3_mod;
				}
				else if(isset($order_vrv['vrv_4']) && $order_vrv['vrv_4'] != null){
					$order_vr_id = 'DS020' . $order_vrv['vrv_4'];
				}
				else{
					$order_vr_id = "";
				}
			}
			else
			{
				$order_vr_id = "";
			}
			@endphp

			@if($order_vr_id)
            <tr>
                <td align="left" valign="top" bgcolor="#F7E2E3" style="padding-left: 10px;padding-right: 10px;">
                    <p style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 20px;
					line-height: 33px;color: #1E2533;">@lang('mail.invoice_number'){{$order_vr_id}}</p>
                </td>
            </tr>
            <tr>
                <td align="left" valign="top" bgcolor="#F7E2E3" style="padding-left: 10px;">
                    <p style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 11px;line-height: 13px;color: #1E2533;">@lang('mail.invoice_number_text')</p>
                </td>
            </tr>
			@endif
            <tr>
                <td height="14" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="left" bgcolor="#F7E2E3" valign="top" style="padding-left: 10px;">
                    <p style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;
				line-height: 123.99%;color: #1E2533;">
                        <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;
					line-height: 123.99%;color: #FA7846;">@lang('mail.pay_for_the_order_text_1')</span> @lang('mail.pay_for_the_order_text_2')</p>
                </td>
            </tr>
            <!-- spacer 30px -->
            <tr>
                <td height="30" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <!-- list block -->
            <tr>
                <td align="left" bgcolor="#F7E2E3" valign="top" style="padding-left: 10px;">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td align="left" valign="middle" width="35">
                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 30px;line-height: 34px;
							text-align: center;color: #FA7846;">1.</span>
                            </td>
                            <td align="left" valign="middle">
                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;line-height: 17px;
							color: #1E2533;">@lang('mail.pay_for_the_order_1_text')</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="15" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="left" bgcolor="#F7E2E3" valign="top" style="padding-left: 10px;">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td align="left" valign="middle" width="35">
                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 30px;line-height: 34px;
							text-align: center;color: #FA7846;">2.</span>
                            </td>
                            <td align="left" valign="middle">
                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;line-height: 17px;
							color: #1E2533;">@lang('mail.pay_for_the_order_2_text')</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="15" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="left" bgcolor="#F7E2E3" valign="top" style="padding-left: 10px;">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td align="left" valign="middle" width="35">
                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 30px;line-height: 34px;
							  text-align: center;color: #FA7846;">3.</span>
                            </td>
                            <td align="left" valign="middle">
                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;line-height: 17px;
							  color: #1E2533;">@lang('mail.pay_for_the_order_3_text')</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- list block end -->
            <!-- spacer 30px -->
            <tr>
                <td height="30" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="left" bgcolor="#F7E2E3" valign="top" style="padding-left: 10px;">
                    <p style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;line-height: 18px;
				color: #1E2533;">@lang('mail.order_detail_text') </p>
                </td>
            </tr>

        </table>
        <table class="main" width="100%">
            <!-- 2 blocks -->
            <tr>
                <td height="20" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td class="desktop" align="center" valign="top" bgcolor="#F7E2E3" style="padding-left: 10px;padding-right: 10px;">
                    <table cellpadding="0" cellspacing="0" width="100%" style="width: 100%;max-width: 336px;background: #ffffff;display: inline-block;vertical-align: top;">
                        <tr>
                            <td align="center" valign="top" style="padding: 9px;">
                                <table cellpadding="0" cellspacing="0" width="100%" style="border: 1px solid #fa7846;">
                                    <tr>
                                        <td align="center" valign="top" style="padding: 10px;">
                                            <table>
                                                <tr>
													<td height="10" style="font-size:0; line-height:0;">&nbsp;</td>
												</tr>
												<tr>
													<td align="left" valign="top">
														<span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;
													line-height: 17px;color: #1E2533;">@lang('mail.order_detail_title')</span>
													</td>
												</tr>
												<tr>
													<td height="20" style="font-size:0; line-height:0;">&nbsp;</td>
												</tr>

												@foreach ($order['items'] as $product)
													@include('.mail.item', ['product'=> $product])
												@endforeach

												<tr>
													<td align="left" valign="middle">
														<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
														<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
												  line-height: 17px;color: #1E2533;">{{$ts3['data_zakaza']}} <strong>{{$now}}</strong></span>
													</td>
												</tr>
												<tr>
													<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
												</tr>

												@if(isset($order['delivery']['sposob']) && $order['delivery']['sposob'])
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
															<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: #1E2533;">@lang('mail.delivery_sposob'):
																<strong>
																	@if ($order['delivery']['sposob'] == 'to_the_door')
																		@lang('mail.delivery_sposob_to_the_door')
																	@elseif($order['delivery']['sposob'] == 'pickup_Riga')
																		@lang('mail.delivery_sposob_pickup_riga')
																	@elseif($order['delivery']['sposob'] == 'pickup_Daugavplis')
																		@lang('mail.delivery_sposob_pickup_daugavplis')
                                                                    @elseif($order['delivery']['sposob'] == 'venipak')
                                                                        @lang('cart_new.step_3_delivery_to_pick-up_point')
                                                                    @elseif($order['delivery']['sposob'] == 'pickup_at_viar_workshop')
                                                                        @lang('cart_new.step_3_pick_up_at_viar_workshop')
																	@endif
																</strong>

                                                                @if(isset($order['delivery']['deliv_price']) && $order['delivery']['deliv_price'] && $order['delivery']['deliv_price'] > 0)
                                                                    - {{ $order['delivery']['deliv_price'] }}€
                                                                @endif
															</span>
														</td>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endisset

												@if(isset($order['payment']) && $order['payment'])
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
															<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: #1E2533;">@lang('mail.delivery_payment'):
																<strong>
																	@if ($order['payment'] == 'cash_in_office')
																		@lang('mail.delivery_payment_cash_in_office')
																	@elseif($order['payment'] == 'on_delivery')
																		@lang('mail.delivery_payment_on_delivery')
																	@elseif($order['payment'] == 'prepayment')
                                                                        @lang('mail.prepayment')
                                                                    @elseif($order['payment'] == 'transfer')
																		@lang('mail.delivery_payment_transfer')
																	@elseif($order['payment'] == 'online_paysera')
                                                                        @lang('cart_new.step_4_payment_by_card')
                                                                    @elseif($order['payment'] == 'google_pay')
                                                                        Google Pay
                                                                    @elseif($order['payment'] == 'apple_pay')
                                                                        Apple Pay
                                                                    @elseif($order['payment'] == 'paypalOnetimePayment')
                                                                        PayPal
																	@elseif($order['payment'] == 'creditcart')
																		@lang('cart_new.step_4_by_card_online')
																	@endif
																</strong>
															</span>
														</td>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endisset


												@if(isset($order['items']['totalPrice']))
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
															<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
													  line-height: 17px;color: #1E2533;">@lang('mail.totalprice'): <strong>

                                                        @if(isset($order['delivery']['deliv_price']) && $order['delivery']['deliv_price'] && isset($order['items']['total_terms_price']) && $order['items']['total_terms_price'])
                                                            {{ $order['items']['totalPrice'] + $order['delivery']['deliv_price'] + $order['items']['total_terms_price'] }}
                                                        @elseif(isset($order['delivery']['deliv_price']) && $order['delivery']['deliv_price'])
                                                            {{ $order['items']['totalPrice'] + $order['delivery']['deliv_price'] }}
                                                        @elseif(isset($order['items']['total_terms_price']) && $order['items']['total_terms_price'])
                                                            {{ $order['items']['totalPrice'] + $order['items']['total_terms_price'] }}
                                                        @else
                                                            {{ $order['items']['totalPrice'] }}
                                                        @endif

                                                        €</strong></span>
														</td>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endif
                                            </table>
                                        </td>

                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <table cellpadding="0" cellspacing="0" width="100%" style="width: 100%;max-width: 23px;display: inline-block;vertical-align: top;">
                        <tr>
                            <td height="23" style="font-size:0px">&nbsp;</td>
                        </tr>
                    </table>
                    <table cellpadding="0" cellspacing="0" width="100%" style="width: 100%;max-width: 175px;display: inline-block;background: url('https://viarcanvas.com/letters/new/one-step-1.png');background-position: center;background-repeat: no-repeat;background-size: 100% 100%;width: 200px;height: 347px;vertical-align: bottom;">
                        <tr>
                            <td height="50" style="font-size:0; line-height:0;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="right" valign="top">
                                <table cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td align="right" valign="top">
                                            <table cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td height="7" style="font-size:0; line-height:0;">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td align="center" valign="top" style="padding-left: 36px;">
                                                        <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;line-height: 20px;
													color: #1E2533;">@lang('mail.only_one_step_left_1')</span><br />
                                                        <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;line-height: 20px;
									color: #fa7846;">@lang('mail.only_one_step_left_2')</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="desktop" height="53" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <!-- button -->
            <tr>
                <td class="desktop" align="center" bgcolor="#F7E2E3" valign="top">
                    <table align="center" border="0" cellpadding="0" style="border-spacing:0px;">
                        <tr>
                            <td style="border-radius: 8px;" bgcolor="#eff8fe">
                                <a href="https://viarcanvas.com/new/orders" target="_blank" style="font-size: 16px; font-weight: bold;text-decoration:none;color:#000000;background-color: #fed501;
                        border:1px solid #fed501;border-radius: 8px;padding: 16px 50px;display: inline-block;">@lang('mail.account_for_payment')</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="10" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td class="desktop" align="center" bgcolor="#F7E2E3" valign="top">
                    <p style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 11px;line-height: 13px;
				text-align: center;color: #1E2533;">@lang('mail.account_for_payment_text')</p>
                </td>
            </tr>
            <tr>
                <td class="desktop" height="65" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <!-- gift mobile -->
            <tr>
                <td class="mobile" align="center" valign="top" bgcolor="#F7E2E3" style="padding-left: 10px;padding-right: 10px;padding-bottom: 29px;">
                    <table cellpadding="0" cellspacing="0" width="100%" style="width: 100%;max-width: 332px;background: #ffffff;padding: 9px;display: inline-block;vertical-align: top;">
                        <tr>
                            <td align="center" valign="top">
                                <table cellpadding="0" cellspacing="0" width="100%" style="border: 1px solid #fa7846;">
                                    <tr>
                                        <td align="center" valign="top" style="padding: 10px;">
                                            <table>


												<tr>
													<td height="10" style="font-size:0; line-height:0;">&nbsp;</td>
												</tr>
												<tr>
													<td align="left" valign="top">
														<span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;
													line-height: 17px;color: #1E2533;">@lang('mail.order_detail_title')</span>
													</td>
												</tr>
												<tr>
													<td height="20" style="font-size:0; line-height:0;">&nbsp;</td>
												</tr>

												@foreach ($order['items'] as $product)
													@include('.mail.item', ['product'=> $product])
												@endforeach


												<tr>
													<td align="left" valign="middle">
														<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
														<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
												  line-height: 17px;color: #1E2533;">{{$ts3['data_zakaza']}} <strong>{{$now}}</strong></span>
													</td>
												</tr>
												<tr>
													<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
												</tr>


												@if(isset($order['delivery']['sposob']) && $order['delivery']['sposob'])
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
															<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: #1E2533;">@lang('mail.delivery_sposob'):
																<strong>
																	@if ($order['delivery']['sposob'] == 'to_the_door')
																		@lang('mail.delivery_sposob_to_the_door')
																	@elseif($order['delivery']['sposob'] == 'pickup_Riga')
																		@lang('mail.delivery_sposob_pickup_riga')
																	@elseif($order['delivery']['sposob'] == 'pickup_Daugavplis')
																		@lang('mail.delivery_sposob_pickup_daugavplis')
                                                                    @elseif($order['delivery']['sposob'] == 'venipak')
                                                                        @lang('cart_new.step_3_delivery_to_pick-up_point')
                                                                    @elseif($order['delivery']['sposob'] == 'pickup_at_viar_workshop')
                                                                        @lang('cart_new.step_3_pick_up_at_viar_workshop')
																	@endif
																</strong>

                                                                @if(isset($order['delivery']['deliv_price']) && $order['delivery']['deliv_price'] && $order['delivery']['deliv_price'] > 0)
                                                                    - {{ $order['delivery']['deliv_price'] }}€
                                                                @endif
															</span>
														</td>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endisset

												@if(isset($order['payment']) && $order['payment'])
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
															<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: #1E2533;">@lang('mail.delivery_payment'):
																<strong>
																	@if ($order['payment'] == 'cash_in_office')
																		@lang('mail.delivery_payment_cash_in_office')
																	@elseif($order['payment'] == 'on_delivery')
																		@lang('mail.delivery_payment_on_delivery')
																	@elseif($order['payment'] == 'transfer')
																		@lang('mail.delivery_payment_transfer')
                                                                    @elseif($order['payment'] == 'prepayment')
                                                                        @lang('mail.prepayment')
                                                                    @elseif($order['payment'] == 'online_paysera')
                                                                        @lang('cart_new.step_4_payment_by_card')
                                                                    @elseif($order['payment'] == 'google_pay')
                                                                        Google Pay
                                                                    @elseif($order['payment'] == 'apple_pay')
                                                                        Apple Pay
                                                                    @elseif($order['payment'] == 'paypalOnetimePayment')
                                                                        PayPal
																	@elseif($order['payment'] == 'creditcart')
																		@lang('cart_new.step_4_by_card_online')
																	@endif
																</strong>
															</span>
														</td>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endisset


												@if(isset($order['items']['totalPrice']))
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
															<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
													  line-height: 17px;color: #1E2533;">@lang('mail.totalprice'): <strong>

                                                        @if(isset($order['delivery']['deliv_price']) && $order['delivery']['deliv_price'] && isset($order['items']['total_terms_price']) && $order['items']['total_terms_price'])
                                                            {{ $order['items']['totalPrice'] + $order['delivery']['deliv_price'] + $order['items']['total_terms_price'] }}
                                                        @elseif(isset($order['delivery']['deliv_price']) && $order['delivery']['deliv_price'])
                                                            {{ $order['items']['totalPrice'] + $order['delivery']['deliv_price'] }}
                                                        @elseif(isset($order['items']['total_terms_price']) && $order['items']['total_terms_price'])
                                                            {{ $order['items']['totalPrice'] + $order['items']['total_terms_price'] }}
                                                        @else
                                                            {{ $order['items']['totalPrice'] }}
                                                        @endif

                                                        €</strong></span>
														</td>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endif
											</table>
										</td>
									</tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="mobile" bgcolor="#F7E2E3" align="center" valign="top">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td height="53" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
                        </tr>
                        <!-- button -->
                        <tr>
                            <td align="center" bgcolor="#F7E2E3" valign="top">
                                <table align="center" border="0" cellpadding="0" style="border-spacing:0px;">
                                    <tr>
                                        <td style="border-radius: 8px;" bgcolor="#eff8fe">
                                            <a href="https://viarcanvas.com/" target="_blank" style="font-size: 16px; font-weight: bold;text-decoration:none;color:#000000;background-color: #fed501;
                                    border:1px solid #fed501;border-radius: 8px;padding: 16px 50px;display: inline-block;">@lang('mail.account_for_payment')</a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td height="10" style="font-size:0; line-height:0;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="center" bgcolor="#F7E2E3" valign="top">
                                <p style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 11px;line-height: 13px;
						  text-align: center;color: #1E2533;">@lang('mail.account_for_payment_text')</p>
                            </td>
                        </tr>
                        <tr>
                            <td height="65" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- gift mobile end -->


        </table>
        <table class="main" width="100%">
            <tr>
                <td height="47" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="left" valign="top" style="padding-left: 10px;">
                    <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 580px;">
                        <tr>
                            <td align="left" valign="top">
                                <span style="font-family: Arial;font-style: normal;font-weight: 600;font-size: 30px;line-height: 36px;
							color: #FA7846;">@lang('mail.execution_steps_text_1') </span> <span style="font-family: Arial;font-style: normal;font-weight: 600;font-size: 30px;line-height: 36px;
							color: #1E2533;">@lang('mail.execution_steps_text_2')</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="9" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="left" valign="top" style="padding-left: 10px;">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td align="left" valign="top" width="135" style="padding-left: 53px;">
                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;line-height: 17px;
							color: #FA7846;">@lang('mail.execution_steps_text_3') </span><span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;line-height: 17px;
							color: #1E2533;">@lang('mail.execution_steps_text_4')</span>
                            </td>
                            <td align="left" valign="top">
                                <img src="https://viarcanvas.com/letters/new/letter-1-r.png" width="46" height="16" style="margin:0; padding:0; border:none; display:inline-block;" border="0" alt="arrow" />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <!-- START TWO COLUMNS -->
            <tr>
                <td align="center" valign="top">
                    <table width="100%" style="border-spacing: 0;">
                        <tr>
                            <td align="center" valign="top">
                                <table cellpadding="0" cellspacing="0" width="100%" style="width: 100%;max-width: 270px;display: inline-block;padding-bottom: 10px;">
                                    <tr>
                                        <td align="center" valign="top">
                                            <table cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td style="padding: 0; font-size: 0; text-align: center;">
                                                        <table style="border-spacing: 0; vertical-align: top; width: 100%; max-width: 265px;display: inline-block;">
                                                            <tr>
                                                                <td style="padding: 20px;border: 1px solid #FA7846;">
                                                                    <table style="border-spacing:0;text-align:left;">
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 50px;line-height: 57px;text-align: center;color: #FA7846;vertical-align: top;padding-right: 5px;">1.</span><img
                                                                                    src="https://viarcanvas.com/letters/new/pic-1.png" width="169" height="119" style="margin:0; padding:0; border:none; display:inline-block;" border="0" alt="pic-1" />
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="15" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;
															   line-height: 23px;color: #FA7846;">@lang('mail.execution_steps_1_title')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="5" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;line-height: 135%;text-align: center;color: #110F0F;">@lang('mail.execution_steps_1_text'))</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="33" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 170px;">
                                                                                    <tr>
                                                                                        <td align="left" valign="top" width="13">
                                                                                            <img src="https://viarcanvas.com/letters/new/star.png" width="12" height="12" style="margin:0; padding:0; border:none; display:inline-block;" border="0" alt="star" />
                                                                                        </td>
                                                                                        <td align="left" valign="top">
                                                                                            <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;
																		 line-height: 23px;text-align: center;color: #FA7846;">@lang('mail.execution_steps_1_time')</span>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                        </tr>

                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                <table cellpadding="0" cellspacing="0" width="100%" style="width: 100%;max-width: 270px;display: inline-block;padding-bottom: 10px;">
                                    <tr>
                                        <td align="center" valign="top">
                                            <table cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td style="padding: 0; font-size: 0; text-align: center;">
                                                        <table style="border-spacing: 0; vertical-align: top; width: 100%; max-width: 265px;display: inline-block;">
                                                            <tr>
                                                                <td style="padding: 20px;border: 1px solid #FA7846;">
                                                                    <table style="border-spacing:0;text-align:left;">
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 50px;line-height: 57px;text-align: center;color: #FA7846;vertical-align: top;padding-right: 5px;">2.</span><img
                                                                                    src="https://viarcanvas.com/letters/new/pic-2.png" width="169" height="119" style="margin:0; padding:0; border:none; display:inline-block;" border="0" alt="pic-2" />
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="15" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;
															   line-height: 23px;color: #FA7846;">@lang('mail.execution_steps_2_title')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="5" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;line-height: 135%;text-align: center;color: #110F0F;">@lang('mail.execution_steps_2_text')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="33" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 170px;">
                                                                                    <tr>
                                                                                        <td align="left" valign="top" width="13">
                                                                                            <img src="https://viarcanvas.com/letters/new/star.png" width="12" height="12" style="margin:0; padding:0; border:none; display:inline-block;" border="0" alt="star" />
                                                                                        </td>
                                                                                        <td align="left" valign="top">
                                                                                            <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;
																		 line-height: 23px;text-align: center;color: #FA7846;">@lang('mail.execution_steps_2_time')</span>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                        </tr>

                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- END TWO COLUMNS -->
            <!-- START TWO COLUMNS -->
            <tr>
                <td align="center" valign="top">
                    <table width="100%" style="border-spacing: 0;">
                        <tr>
                            <td align="center" valign="top">
                                <table cellpadding="0" cellspacing="0" width="100%" style="width: 100%;max-width: 270px;display: inline-block;padding-bottom: 10px;">
                                    <tr>
                                        <td align="center" valign="top">
                                            <table cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td style="padding: 0; font-size: 0; text-align: center;">
                                                        <table style="border-spacing: 0; vertical-align: top; width: 100%; max-width: 265px;display: inline-block;">
                                                            <tr>
                                                                <td style="padding: 20px;border: 1px solid #FA7846;">
                                                                    <table style="border-spacing:0;text-align:left;">
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 50px;line-height: 57px;text-align: center;color: #FA7846;vertical-align: top;padding-right: 5px;">3.</span><img
                                                                                    src="https://viarcanvas.com/letters/new/pic-3.png" width="169" height="119" style="margin:0; padding:0; border:none; display:inline-block;" border="0" alt="pic-1" />
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="15" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;
															   line-height: 23px;color: #FA7846;">@lang('mail.execution_steps_3_title')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="5" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;line-height: 135%;text-align: center;color: #110F0F;">@lang('mail.execution_steps_3_text')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="33" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 170px;">
                                                                                    <tr>
                                                                                        <td align="left" valign="top" width="13">
                                                                                            <img src="https://viarcanvas.com/letters/new/star.png" width="12" height="12" style="margin:0; padding:0; border:none; display:inline-block;" border="0" alt="star" />
                                                                                        </td>
                                                                                        <td align="left" valign="top" width="188">
                                                                                            <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;line-height: 23px;text-align: center;color: #FA7846;white-space: nowrap;">@lang('mail.execution_steps_3_time')</span>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                        </tr>

                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                <table cellpadding="0" cellspacing="0" width="100%" style="width: 100%;max-width: 270px;display: inline-block;padding-bottom: 10px;">
                                    <tr>
                                        <td align="center" valign="top">
                                            <table cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td style="padding: 0; font-size: 0; text-align: center;">
                                                        <table style="border-spacing: 0; vertical-align: top; width: 100%; max-width: 265px;display: inline-block;">
                                                            <tr>
                                                                <td style="padding: 20px;border: 1px solid #FA7846;">
                                                                    <table style="border-spacing:0;text-align:left;">
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 50px;line-height: 57px;text-align: center;color: #FA7846;vertical-align: top;padding-right: 5px;">4.</span><img
                                                                                    src="https://viarcanvas.com/letters/new/pic-4.png" width="169" height="119" style="margin:0; padding:0; border:none; display:inline-block;" border="0" alt="pic-2" />
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="15" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;
															   line-height: 23px;color: #FA7846;">@lang('mail.execution_steps_4_title')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="5" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;line-height: 135%;text-align: center;color: #110F0F;">@lang('mail.execution_steps_4_text')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="33" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 170px;">
                                                                                    <tr>
                                                                                        <td align="left" valign="top" width="13">
                                                                                            <img src="https://viarcanvas.com/letters/new/star.png" width="12" height="12" style="margin:0; padding:0; border:none; display:inline-block;" border="0" alt="star" />
                                                                                        </td>
                                                                                        <td align="left" valign="top" width="223">
                                                                                            <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;
																		 line-height: 23px;text-align: center;color: #FA7846;">@lang('mail.execution_steps_4_time')</span>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                        </tr>

                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- END TWO COLUMNS -->
            <tr>
                <td height="90" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="left" valign="top" style="padding-left: 10px;">
                    <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 545px;">
                        <tr>
                            <td align="center" valign="top">
                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 44px;
							text-align: center;color: #1E2533;">@lang('mail.questions')</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="13" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <!-- phone -->
            <tr>
                <td align="center" valign="top">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td align="center" valign="top">
                                <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 300px;">
                                    <tr>
                                        <td align="left" valign="middle" width="30">
                                            <img src="https://viarcanvas.com/letters/new/phone.png" width="30" height="30" style="margin:0; padding:0; border:none; display:inline-block;" border="0" alt="phone icon" />
                                        </td>
                                        <td align="left" valign="middle" style="padding-left: 10px; font-family: Arial;font-style: normal;font-weight: normal;font-size: 30px; line-height: 34px;color: #FA7846 !important;white-space: nowrap;">
											<a style="color: #FA7846;" href="@lang('mail.setting_phone_href')">@lang('mail.setting_phone')</a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="25" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="center" valign="top">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td align="center" valign="top">
                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 44px;
							text-align: center;color: #1E2533;">@lang('mail.questions_2') </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- WhatsApp icon link -->
            <tr>
                <td align="center" valign="top">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td align="center" valign="top">
                                <a href="https://wa.me/@lang('mail.setting_whatsapp_phone_href')"><img src="https://viarcanvas.com/letters/new/whatsap-icon.png" width="50" height="50" style="margin:0; padding:0; border:none; display:block;" border="0" alt="whatsap-icon" /></a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="80" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <!--footer  -->


			@include('.mail.main_footer')

            <tr>
                <td height="55" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
        </table>
    </center>
</body>

</html>
