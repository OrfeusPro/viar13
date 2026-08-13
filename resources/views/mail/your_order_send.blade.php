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

			@include('.mail.main_head')

            <tr>
                <td height="40" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="left" valign="top" bgcolor="#F7E2E3" style="padding-left: 10px;padding-right: 10px;">
                    <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px;
						color: #1E2533;margin: 0;">@lang('mail.mail_your_order_is_ready_title')
							<span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px;
							color: #FA7846;">@lang('mail.mail_your_order_is_ready_title_2')</span>
                    </span>
                </td>
            </tr>
            <tr>
                <td height="15" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="left" valign="top" bgcolor="#F7E2E3" style="padding-left: 10px;padding-right: 10px;">
                    <p style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;
					line-height: 23px;color: #1E2533;">@lang('mail.mail_your_order_is_ready_text')</p>
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
                                                @if(isset($order['labels']))
                                                <tr>
													<td height="10" style="font-size:0; line-height:0;">&nbsp;</td>
												</tr>
												<tr>
													<td align="left" valign="top">
														<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
													line-height: 17px;color: #1E2533;">{{$data['tracking_number']}}</span>
														<span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;
													line-height: 17px;color: #1E2533;">{{$order['labels']}}</span>

													</td>
												</tr>
                                                @endif
												<tr>
													<td height="10" style="font-size:0; line-height:0;">&nbsp;</td>
												</tr>
												<tr>
													<td align="left" valign="top">
														<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
													line-height: 17px;color: #1E2533;">{{$data['sended_date_zak']}}</span>
														<span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;
													line-height: 17px;color: #1E2533;">{{$created_at}}</span>

													</td>
												</tr>
												<tr>
													<td height="10" style="font-size:0; line-height:0;">&nbsp;</td>
												</tr>
												<tr>
													<td align="left" valign="top">
														<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
													line-height: 17px;color: #1E2533;">{{$data['sended_date_otp']}}</span>
														<span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;
													line-height: 17px;color: #1E2533;">{{$now}}</span>

													</td>
												</tr>
												<tr>
													<td height="20" style="font-size:0; line-height:0;">&nbsp;</td>
												</tr>
												<tr>
													<td align="left" valign="middle">
														<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
														<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;line-height: 17px;color: #1E2533;">@lang('mail.delivery_date_info_1') </span>
													</td>
												</tr>
												<tr>
													<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
												</tr>
												<tr>
													<td align="center" valign="top">
														<table cellpadding="0" cellspacing="0" width="100%">
															<tr>
																<td align="left" valign="top">
																	<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
																</td>
																<td align="left" valign="middle">
																	<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
															  line-height: 17px;color: #1E2533;">@lang('mail.delivery_date_info_2')</span>
																</td>
															</tr>
														</table>
													</td>
												</tr>
												<tr>
													<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
												</tr>
												<tr>
													<td align="center" valign="top">
														<table cellpadding="0" cellspacing="0" width="100%">
															<tr>
																<td align="left" valign="top">
																	<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
																</td>
																<td align="left" valign="middle">
																	<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
															  line-height: 17px;color: #1E2533;">@lang('mail.delivery_date_info_3')</span>
																</td>
															</tr>
														</table>
													</td>
												</tr>
												<tr>
													<td height="44" style="font-size:0; line-height:0;">&nbsp;</td>
												</tr>
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
                    <table cellpadding="0" cellspacing="0" width="100%" style="width: 100%;max-width: 202px;display: inline-block;background: url('https://viarcanvas.com/letters/new/bus-1.png');background-position: center;background-repeat: no-repeat;background-size: 100% 100%;width: 202px;height: 235px;background-color: #f7e2e3;">
                        <tr>
                            <td height="50" style="font-size:0; line-height:0;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="right" valign="top">
                                <table cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td align="right" valign="top" style="padding-left: 79px;">
                                            <table cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td height="90" style="font-size:0; line-height:0;">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td align="center" valign="top">
                                                        <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;line-height: 20px;	color: #fa7846;">@lang('mail.order_already_on_my_way')</span>
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
        <table class="main" width="100%">
            <tr>
                <td height="80" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td class="desktop" align="center" bgcolor="#F7E2E3" valign="top" style="padding-left: 10px;padding-right: 10px;">
                    <p style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px;
				  color: #1E2533;">@lang('mail.super_deals_1') <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px;
				  color: #fa7846;">@lang('mail.super_deals_2')</span> @lang('mail.super_deals_3')
                    </p>
                </td>
            </tr>
            <tr>
                <td class="desktop" align="center" bgcolor="#F7E2E3" valign="top" style="padding-left: 10px;padding-right: 10px;">
                    <p style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px;
				  color: #1E2533;"><span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px;
				  color: #fa7846;">@lang('mail.super_deals_4')</span> @lang('mail.super_deals_5')
                    </p>
                </td>
            </tr>

            <!-- gift mobile -->
            <tr>
                <td class="mobile" align="center" valign="top" bgcolor="#F7E2E3" style="padding-left: 10px;padding-right: 10px;">
                    <table cellpadding="0" cellspacing="0" width="100%" style="width: 100%;max-width: 336px;background: #ffffff;padding: 9px;display: inline-block;vertical-align: top;">
                        <tr>
                            <td align="center" valign="top">
                                <table cellpadding="0" cellspacing="0" width="100%" style="border: 1px solid #fa7846;padding: 10px;">
                                    <tr>
                                        <td height="10" style="font-size:0; line-height:0;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="left" valign="top">
                                            <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
										line-height: 17px;color: #1E2533;">{{$data['sended_date_zak']}}</span>
                                            <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;
										line-height: 17px;color: #1E2533;">{{$created_at}}</span>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="10" style="font-size:0; line-height:0;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="left" valign="top">
                                            <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
										line-height: 17px;color: #1E2533;">{{$data['sended_date_otp']}}</span>
                                            <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;
										line-height: 17px;color: #1E2533;">{{$now}}</span>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="20" style="font-size:0; line-height:0;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="left" valign="middle">
                                            <img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
                                            <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;line-height: 17px;color: #1E2533;">@lang('mail.delivery_date_info_1') </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="center" valign="top">
                                            <table cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td align="left" valign="top">
                                                        <img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
                                                    </td>
                                                    <td align="left" valign="middle">
                                                        <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
												  line-height: 17px;color: #1E2533;">@lang('mail.delivery_date_info_2')</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="center" valign="top">
                                            <table cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td align="left" valign="top">
                                                        <img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
                                                    </td>
                                                    <td align="left" valign="middle">
                                                        <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
												  line-height: 17px;color: #1E2533;">@lang('mail.delivery_date_info_3')</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="44" style="font-size:0; line-height:0;">&nbsp;</td>
                                    </tr>

                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="80" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td class="mobile" align="center" bgcolor="#F7E2E3" valign="top" style="padding-left: 10px;padding-right: 10px;">
                    <p style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px;
				  color: #1E2533;">@lang('mail.super_deals_1') <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px;
				  color: #fa7846;">@lang('mail.super_deals_2')</span> @lang('mail.super_deals_3')
                    </p>
                </td>
            </tr>
            <tr>
                <td class="mobile" align="center" bgcolor="#F7E2E3" valign="top" style="padding-left: 10px;padding-right: 10px;">
                    <p style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px;
				  color: #1E2533;"><span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px;
				  color: #fa7846;">@lang('mail.super_deals_4')</span> @lang('mail.super_deals_5')</p>
                </td>
            </tr>
            <!-- gift mobile end -->
			{{--
            <tr>
                <td height="15" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="center" valign="top" bgcolor="#F7E2E3">
                    <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 374px;">
                        <tr>
                            <td align="center" valign="middle" width="200" bgcolor="#F7E2E3">
                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;
							line-height: 23px;color: #1E2533;">@lang('mail.super_deals_discounts_text') </span>
                            </td>
                            <td align="left" valign="top" bgcolor="#F7E2E3">
                                <table cellpadding="0" cellspacing="0" width="100%" style="background: url('https://viarcanvas.com/letters/new/badge-bg.png');background-position: center;background-repeat: no-repeat;background-size: 100% 100%;width: 155px;height: 140px;display: inline-block;">
                                    <tr>
                                        <td height="53" style="font-size:0; line-height:0;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="center" valign="top" style="padding-left: 13px;">
                                            <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px;
									text-align: center;color: #FA7846;padding-right: 3px;">234аbf10 </span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
			--}}
            <tr>
                <td height="29" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="center" valign="top" bgcolor="#F7E2E3">
                    <p style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;line-height: 18px;
				text-align: center;color: #1E2533;">@lang('mail.super_deals_discount_valid_1') <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;line-height: 18px;
				text-align: center;color: #FA7846;">@lang('mail.super_deals_discount_valid_2')</span></p>
                </td>
            </tr>


            <!-- 1 card start -->
			@foreach($top_sale as $sale)
            <tr>
                <td height="20" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="center" bgcolor="#F7E2E3" valign="top">
                    <img src="https://viarcanvas.com/storage/{{$sale['image']}}" width="430" style="margin:0; padding:0; border:none; display:block;" border="0" alt="pic-1" />
                </td>
            </tr>
            <tr>
                <td height="15" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="center" bgcolor="#F7E2E3" valign="top">
                    <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 95px;">
                        <tr>
                            <td align="left" valign="middle" width="20">
                                <img src="https://viarcanvas.com/letters/new/size.png" width="14" height="14" style="margin:0; padding:0; border:none; display:inline-block;" border="0" alt="size" />
                            </td>
                            <td align="left" valign="middle">
                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 16px;line-height: 22px;color: #8e9299;">
							 {{$sale['size']}}
						 </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="9" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="center" bgcolor="#F7E2E3" valign="top">
                    <table cellpadding="0" cellspacing="0" width="100%" style="padding: 10px;background: #ffffff;max-width: 97px;border-radius: 4px;">
                        <tr>
                            <td align="center" valign="top">
                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;line-height: 23px;color: #FA7846;">{{$sale['price']}}€</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="30" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <!-- button -->
            <tr>
                <td align="center" bgcolor="#F7E2E3" valign="top">
                    <table align="center" border="0" cellpadding="0" style="border-spacing:0px;">
                        <tr>
                            <td style="border-radius: 8px;" bgcolor="#eff8fe">
                                <a href="{{$sale['link']}}" target="_blank" style="font-size: 16px; font-weight: bold;text-decoration:none;color:#fff;background-color: #FA7846;
						border:1px solid #FA7846;border-radius: 8px;padding: 16px 50px;display: inline-block;">@lang('mail.i_want_the_same_one')</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
			@endforeach
            <!-- 1 card end -->

            <tr>
                <td height="73" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
        </table>

        <table class="main" width="100%">
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
