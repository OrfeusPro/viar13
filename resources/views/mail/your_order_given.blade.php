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

        @media screen and (max-width: 612px) {
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
						color: #1E2533;margin: 0;">
							<span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px;
							color: #FA7846;">@lang('mail.mail_your_order_received_title_1')</span> @lang('mail.mail_your_order_received_title_2')
                    <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px;
						color: #1E2533;">@lang('mail.mail_your_order_received_title_number'){{$order_id}}</span>

                    </span>
                </td>
            </tr>
            <tr>
                <td height="20" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="left" valign="top" bgcolor="#F7E2E3" style="padding-left: 10px;padding-right: 10px;">
                    <p style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;
					line-height: 25px;color: #1E2533;">@lang('mail.mail_your_order_received_text_1')</p>
                </td>
            </tr>
            <tr>
                <td height="15" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="left" valign="top" bgcolor="#F7E2E3" style="padding-left: 10px;padding-right: 10px;">
                    <p style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
					line-height: 17px;color: #1E2533;">
                        <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
						line-height: 17px;color: #FA7846;">@lang('mail.mail_your_order_received_text_2')</span><br /> @lang('mail.mail_your_order_received_text_3')
                    </p>
                </td>
            </tr>

        </table>
        <table class="main" width="100%">
            <!-- 2 blocks -->
            <tr>
                <td height="20" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td class="desktop" align="center" valign="top" width="280" bgcolor="#F7E2E3" style="padding-left: 10px;padding-right: 10px;">
                    
                    @include('.mail.you_order_items')

                    <table cellpadding="0" cellspacing="0" width="100%" style="width: 100%;max-width: 23px;display: inline-block;vertical-align: top;">
                        <tr>
                            <td height="23" style="font-size:0px">&nbsp;</td>
                        </tr>
                    </table>
                    <table cellpadding="0" cellspacing="0" width="100%" style="width: 100%;max-width: 212px;display: inline-block;background: url('https://viarcanvas.com/letters/new/box-r-1.png');background-position: center;background-repeat: no-repeat;background-size: contain;width: 212px;height: 473px;background-color: #f7e2e3;">
                        <tr>
                            <td align="right" valign="top">
                                <table cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td align="right" valign="top" style="padding-left: 125px;">
                                            <table cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td height="20" style="font-size:0; line-height:0;">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td align="center" valign="top">
                                                        <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px;
								color: #fa7846;">@lang('mail.mail_your_order_received_discount')</span>
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
                <td height="29" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td class="desktop" align="left" bgcolor="#F7E2E3" valign="top" style="padding-left: 10px;padding-right: 10px;">
                    <p style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;line-height: 25px;
				color: #1E2533;">@lang('mail.mail_your_order_received_footer_1') <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;line-height: 25px;
				color: #fa7846;">@lang('mail.mail_your_order_received_footer_2')</span> @lang('mail.mail_your_order_received_footer_3')
                    </p>
                </td>
            </tr>
            <!-- gift mobile -->
            <tr>
                <td class="mobile" align="center" valign="top" bgcolor="#F7E2E3" style="padding-left: 10px;padding-right: 10px;padding-bottom: 29px;">
                    @include('.mail.you_order_items')
                </td>
            </tr>
            <tr>
                <td class="mobile" align="left" bgcolor="#F7E2E3" valign="top" style="padding-left: 10px;padding-right: 10px;">
                    <p style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;line-height: 25px;
				color: #1E2533;">@lang('mail.mail_your_order_received_footer_1') <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;line-height: 25px;
				color: #fa7846;">@lang('mail.mail_your_order_received_footer_2')</span> @lang('mail.mail_your_order_received_footer_3')
                    </p>
                </td>
            </tr>
            <tr>
                <td class="mobile" align="center" valign="top" bgcolor="#f7e2e3">
                    <table cellpadding="0" cellspacing="0" width="100%" style="width: 100%;max-width: 204px;display: inline-block;background: url('https://viarcanvas.com/letters/new/gift-mobile-1.png');background-position: center;background-repeat: no-repeat;background-size: 100% 100%;width: 204px;height: 123px;">
                        <tr>
                            <td align="right" valign="top">
                                <table cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td align="right" valign="top">
                                            <table cellpadding="0" cellspacing="0" width="100%" style="padding-left: 57px;">
                                                <tr>
                                                    <td height="70" style="font-size:0; line-height:0;">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td align="center" valign="top">
                                                        <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;line-height: 20px;
								color: #fa7846;">-30%</span>
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

            <!-- gift mobile end -->

							{{--
            <tr>
                <td height="15" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="left" valign="top" bgcolor="#F7E2E3" style="padding-left: 10px;">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td align="left" valign="middle" width="158" bgcolor="#F7E2E3">
                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;
							line-height: 23px;color: #1E2533;">@lang('mail.mail_your_order_received_promo')</span>
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
                <td height="23" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <!-- button -->
            <tr>
                <td align="center" bgcolor="#F7E2E3" style="padding-left: 10px;">
                    <table align="center" border="0" cellpadding="0" style="border-spacing:0px;">
                        <tr>
                            <td style="border-radius: 8px;" bgcolor="#eff8fe">
                                <a href="https://viarcanvas.com/" target="_blank" style="font-size: 16px; font-weight: bold;text-decoration:none;color:#fff;background-color: #FA7846; border:1px solid #FA7846;border-radius: 8px;padding: 20px 86px;display: inline-block;">@lang('mail.go_to_website')</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="54" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
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
                                <span style="font-family: Arial;font-style: normal;font-weight: 600;font-size: 30px;line-height: 36px; color: #FA7846;">@lang('mail.execution_steps_text_1') </span> <span style="font-family: Arial;font-style: normal;font-weight: 600;font-size: 30px;line-height: 36px; color: #1E2533;">@lang('mail.execution_steps_text_2')</span>
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
                                                                        <tr style="height: 46px;">
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;
															   line-height: 23px;color: #FA7846;">@lang('mail.execution_steps_1_title')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="5" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr style="height: 85px;">
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;line-height: 135%;text-align: center;color: #110F0F;">@lang('mail.execution_steps_1_text')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="33" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 170px;">
                                                                                    <tr style="height: 46px;">
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
                                                                        <tr style="height: 46px;">
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;
															   line-height: 23px;color: #FA7846;">@lang('mail.execution_steps_2_title')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="5" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr style="height: 85px;">
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
                                                                                    <tr style="height: 46px;">
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
                                                                        <tr style="height: 46px;">
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;
															   line-height: 23px;color: #FA7846;">@lang('mail.execution_steps_3_title')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="5" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr style="height: 85px;">
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
                                                                                    <tr style="height: 46px;">
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
                                                                        <tr style="height: 46px;">
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;
															   line-height: 23px;color: #FA7846;">@lang('mail.execution_steps_4_title')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="5" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr style="height: 85px;">
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
                                                                                    <tr style="height: 46px;">
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
            <tr>
                <td align="center" valign="top">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td align="center" valign="top">
                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;
							line-height: 124.99%;text-align: center;letter-spacing: -0.03em;color: #1E2533;">@lang('mail.mail_your_order_received_top_footer_title_1') <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;
							line-height: 124.99%;text-align: center;letter-spacing: -0.03em;color: #FA7846;text-transform: uppercase;">@lang('mail.mail_your_order_received_top_footer_title_2')</span> @lang('mail.mail_your_order_received_top_footer_title_3')</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="20" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="center" valign="top">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td align="center" valign="top">
                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
							line-height: 17px;text-align: center;color: #1E2533;">@lang('mail.mail_your_order_received_top_footer_text_1') <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
							line-height: 17px;text-align: center;color: #FA7846;">@lang('mail.mail_your_order_received_top_footer_text_2')</span>@lang('mail.mail_your_order_received_top_footer_text_3') <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
							line-height: 17px;text-align: center;color: #FA7846;">@lang('mail.mail_your_order_received_top_footer_text_4')</span> @lang('mail.mail_your_order_received_top_footer_text_5')
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="30" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <!-- START TWO COLUMNS -->
            <tr>
                <td align="center" valign="top">
                    <table width="100%" style="border-spacing: 0;">
                        <tr>
                            <td align="center" valign="top">
								@foreach($top_sale as $sale)
                                <table cellpadding="0" cellspacing="0" width="100%" style="width: 100%;max-width: 280px;display: inline-block;padding-bottom: 10px;">
                                    <tr>
                                        <td align="center" valign="top">
                                            <table cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td style="padding: 0; font-size: 0; text-align: center;">
                                                        <table style="border-spacing: 0; vertical-align: top; width: 100%; max-width: 270px;display: inline-block;">
                                                            <tr>
                                                                <td>
                                                                    <table style="border-spacing:0;text-align:left;">
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <img src="https://viarcanvas.com/storage/{{$sale['image']}}" width="270" height="347" style="border-radius: 10px; margin:0; padding:0; border:none; display:inline-block;" border="0" alt="Sharj" />
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="15" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;line-height: 135%;text-align: center;color: #110F0F;">@lang('mail.mail_your_order_received_top_footer_name_1')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="15" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 96px;">
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
                                                                            <td height="9" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;line-height: 23px;color: #1E2533;">{{$sale['price']}}€</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="7" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <!-- button -->
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <table align="center" border="0" cellpadding="0" style="border-spacing:0px;">
                                                                                    <tr>
                                                                                        <td style="border-radius: 8px;" bgcolor="#eff8fe">
                                                                                            <a href="{{$sale['link']}}" target="_blank" style="font-size: 16px; font-weight: bold;text-decoration:none;color:#fff;background-color: #FA7846; border:1px solid #FA7846;border-radius: 8px;padding: 16px 50px;display: inline-block;">@lang('mail.i_want_the_same_one')</a>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="10" style="font-size:0; line-height:0;">&nbsp;</td>
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
								@endforeach
                                <!-- column end -->
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- END TWO COLUMNS -->
            <tr>
                <td height="60" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <!-- button -->
            <tr>
                <td align="center" valign="top">
                    <table align="center" border="0" cellpadding="0" style="border-spacing:0px;">
                        <tr>
                            <td style="border-radius: 8px;" bgcolor="#eff8fe">
                                <a href="https://viarcanvas.com/" target="_blank" style="font-size: 16px; font-weight: bold;text-decoration:none;color:#fff;background-color: #FA7846;
						border:1px solid #FA7846;border-radius: 8px;padding: 16px 50px;display: inline-block;">@lang('mail.mail_your_order_received_top_footer_see_all_offers')</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="60" style="font-size:0; line-height:0;">&nbsp;</td>
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
