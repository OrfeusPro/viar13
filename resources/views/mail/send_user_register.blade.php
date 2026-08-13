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
            display: none;
        }

        @media screen and (max-width: 596px) {
            .desktop {
                display: none;
            }
            .mobile {
                display: block;
                width: 100%;
            }
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 40px;
        }

        .text-content {
            max-width: 400px;
        }

        .text-content .note {
            color: #ff6347;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .text-content .label {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .copy-section {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            cursor: pointer;
            color: #ff6347;
            font-weight: bold;
            font-size: 18px;
        }

        .copy-section:hover {
            text-decoration: underline;
        }

        .promo-code {
            top: 80px;
            left: 70px;
            position: absolute;
            display: inline-block;
            font-weight: 800;
            text-shadow: 1px 1px 5px #db4e18;
            color: #FA7846;
            font-size: 15px;
        }

        .gift-image {
            max-width: 250px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column-reverse;
                text-align: center;
            }

            .promo-code {
                margin: 0 auto;
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
                    <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px; color: #1E2533;margin: 0;">@lang('mail.mail_create_title_1')
						<span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px; color: #FA7846;">@lang('mail.mail_create_title_2')</span>
						<span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px; color: #1E2533;">@lang('mail.mail_create_title_3')</span>
                    </span>
                </td>
            </tr>
            <tr>
                <td height="18" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <!-- form -->
            <tr>
                <td align="center" valign="top" bgcolor="#F4E4E4">
                    <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 368px;background: #ffffff;border: 2px solid #FC8C5F;padding: 5px;">
                        <tr>
                            <td align="left" valign="top" style="padding-left: 49px;border: 0.5px solid #FC8C5F;">
                                <table cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td height="33" style="font-size:0; line-height:0;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="left" valign="top">
                                            <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;
									line-height: 17px;color: #1E2533;">@lang('mail.your_data')</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="21" style="font-size:0; line-height:0;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="left" valign="top">
                                            <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;line-height: 17px;
								text-align: center;color: #1E2533;">@lang('mail.login') </span>&nbsp;&nbsp; <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;line-height: 17px;color: #FA7846;">{{$email}}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="21" style="font-size:0; line-height:0;">&nbsp;</td>
                                    </tr>
									@if($pass)
                                    <tr>
                                        <td align="left" valign="top">
                                            <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;line-height: 17px;
								text-align: center;color: #1E2533;">@lang('mail.password') </span>&nbsp;&nbsp; <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;line-height: 17px;color: #FA7846;">{{$pass}}</span>
                                        </td>
                                    </tr>
									@endif
                                    <tr>
                                        <td height="56" style="font-size:0; line-height:0;">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- form end -->
            <!-- spacer 32px -->
            <tr>
                <td height="32" bgcolor="#F4E4E4" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            @if($coupon)
                <tr>
                    <td bgcolor="#F4E4E4"
                        style="padding-left: 10px;font-family: Arial;font-weight: 700;font-size: 20px;line-height: 140%;letter-spacing: -3%;">
                        {!! __('mail.restore_basket_text10') !!}
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#F4E4E4" style="padding-left: 10px;">
                        <div class="container">
                            <div class="text-content">
                                <div class="note">
                                    * @lang('mail.restore_basket_text8')
                                </div>
                                <div class="label">@lang('mail.restore_basket_text9') :</div>
                                <div class="copy-section" onclick="copyCode()">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3.48047 0.0428181C3.17969 0.0935993 2.78125 0.242037 2.47266 0.410006C2.08594 0.624849 1.58594 1.14047 1.37891 1.54282C1.09766 2.08579 1.03516 2.35532 1.03516 3.00766C1.03516 3.64438 1.08594 3.89047 1.30859 4.35922C1.6875 5.15219 2.41406 5.72641 3.30078 5.94125C3.59375 6.01157 3.73437 6.01938 4.14062 6.00766C4.84766 5.98032 5.34766 5.79672 5.83594 5.39047C5.94141 5.30454 6.03906 5.23813 6.05469 5.24204C6.07031 5.24985 7.67578 6.17563 9.625 7.30063C12.457 8.93735 13.1641 9.35922 13.1406 9.41C13.0664 9.58969 13.0625 10.5194 13.1367 10.703C13.1562 10.7459 12.3125 11.2655 9.625 12.8514C7.67969 13.9998 6.07031 14.9491 6.05078 14.953C6.03125 14.9608 5.93359 14.8944 5.82812 14.8045C5.57031 14.5741 5.01172 14.3163 4.60937 14.2342C4.16016 14.1444 3.57031 14.1756 3.15625 14.3084C2.19531 14.617 1.47656 15.367 1.20703 16.3514C1.09375 16.7655 1.08984 17.3905 1.20703 17.8241C1.55469 19.1483 2.82812 20.0741 4.17969 19.9881C4.65625 19.9569 4.89453 19.8905 5.35156 19.6639C5.66016 19.5116 5.77734 19.4217 6.05469 19.1444C6.23828 18.9608 6.45312 18.6952 6.53516 18.5545C6.875 17.9647 6.99609 17.203 6.86719 16.4373C6.85937 16.3866 7.71094 15.867 10.4102 14.2655L13.9648 12.16L14.125 12.3006C14.5156 12.6405 15.0547 12.867 15.6367 12.9413C16.2539 13.0194 17.0156 12.8592 17.5625 12.535C17.6875 12.4608 17.9336 12.2616 18.1094 12.0897C18.4648 11.7381 18.6875 11.3788 18.8555 10.8788C18.9492 10.5975 18.9609 10.5077 18.9609 9.96079C18.9648 9.285 18.9102 9.01938 18.6523 8.51547C18.2969 7.82407 17.6445 7.32016 16.8477 7.12094C16.4375 7.01938 15.6914 7.0311 15.293 7.14047C14.9219 7.24594 14.5391 7.44125 14.2812 7.6561C14.1758 7.74594 14.0664 7.81235 14.0391 7.80454C14.0117 7.79672 12.4023 6.87875 10.4687 5.76157L6.94922 3.73032L6.98047 3.5936C7.03906 3.3436 7.01172 2.49985 6.9375 2.23032C6.64453 1.14829 5.875 0.394381 4.74609 0.0779743C4.53125 0.0193806 3.73828 -0.00405693 3.48047 0.0428181Z" fill="#FA7846"/>
                                    </svg> @lang('stock.modal_copy_code')
                                </div>

                            </div>
                            <div style="display: flex;position:relative;">
                                <div class="promo-code" id="promoCode">{{ $coupon }}</div>
                                <img class="gift-image" src="{{ asset('img/promo.png') }}" alt="Подарочная коробка">
                            </div>
                        </div>
                    </td>
                </tr>
            @endif
            <!-- button -->
            <tr>
                <td align="center" bgcolor="#F7E2E3" style="padding-left: 10px;">
                    <table align="center" border="0" cellpadding="0" style="border-spacing:0px;">
                        <tr>
                            <td style="border-radius: 8px;" bgcolor="#eff8fe">
                                <a href="https://viarcanvas.com/" target="_blank" style="font-size: 16px; font-weight: bold;text-decoration:none;color:#fff;background-color: #FA7846;
								border:1px solid #FA7846;border-radius: 8px;padding: 20px 86px;display: inline-block;">@lang('mail.go_to_website')</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- button end -->
            <!-- spacer 50px -->
            <tr>
                <td height="50" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="left" valign="top" bgcolor="#F7E2E3" style="padding-left: 10px;padding-right: 10px;">
                    <p style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 20px;
					line-height: 23px;color: #FA7846;">@lang('mail.for_what')</p>
                </td>
            </tr>
            <tr>
                <td height="10" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td align="left" valign="top" bgcolor="#F7E2E3" style="padding-left: 10px;padding-right: 10px;">
                    <p style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;
					line-height: 123.99%;color: #1E2533;">@lang('mail.for_what_text')</p>
                </td>
            </tr>

        </table>
        <table class="main" width="100%">
            <tr>
                <td height="32" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <!-- START 3 COLUMNS -->
            <tr>
                <td align="center" bgcolor="#F7E2E3" valign="top">
                    <table width="100%" style="border-spacing: 0;max-width: 580px;">
                        <tr>
                            <td align="center" valign="top">
                                <table cellpadding="0" cellspacing="0" width="100%" style="width: 180px;display: inline-block;padding-bottom: 10px;">
                                    <tr>
                                        <td align="center" valign="top" width="180">
                                            <table cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td style="padding: 0; font-size: 0; text-align: center;">
                                                        <table style="border-spacing: 0; vertical-align: top; width: 180px;display: inline-block;">
                                                            <tr>
                                                                <td align="left" valign="top" style="padding: 10px;border: 1px solid #FA7846;border-radius: 8px;background: #ffffff;">
                                                                    <table style="border-spacing:0;text-align:left;">
                                                                        <tr>
                                                                            <td align="left" valign="top" width="100" style="white-space: nowrap;">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 50px;line-height: 57px;text-align: center;color: #FA7846;vertical-align: top;padding-right: 5px;">1.</span>
                                                                                <img src="https://viarcanvas.com/letters/new/pic-5.png" width="100" height="80" style="margin:0; padding:0; border:none; display:inline-block;" border="0" alt="pic-1" />
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="35" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 17px;
															   line-height: 23px;color: #FA7846;">@lang('mail.for_what_1_title')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="13" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;line-height: 135%;text-align: center;color: #110F0F;">@lang('mail.for_what_1_text')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="40" style="font-size:0; line-height:0;">&nbsp;</td>
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
                                <table cellpadding="0" cellspacing="0" width="100%" style="width: 180px;display: inline-block;padding-bottom: 10px;">
                                    <tr>
                                        <td align="center" valign="top" width="180">
                                            <table cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td style="padding: 0; font-size: 0; text-align: center;">
                                                        <table style="border-spacing: 0; vertical-align: top; width: 180px;display: inline-block;">
                                                            <tr>
                                                                <td align="left" valign="top" style="padding: 10px;border: 1px solid #FA7846;border-radius: 8px;background: #ffffff;">
                                                                    <table style="border-spacing:0;text-align:left;">
                                                                        <tr>
                                                                            <td align="left" valign="top" width="100" style="white-space: nowrap;">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 50px;line-height: 57px;text-align: center;color: #FA7846;vertical-align: top;padding-right: 5px;">2.</span>
                                                                                <img src="https://viarcanvas.com/letters/new/pic-6.png" width="100" height="80" style="margin:0; padding:0; border:none; display:inline-block;" border="0" alt="pic-2" />
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="35" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 18px;
															   line-height: 23px;color: #FA7846;">@lang('mail.for_what_2_title') </span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="13" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;line-height: 135%;text-align: center;color: #110F0F;">@lang('mail.for_what_2_text')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="40" style="font-size:0; line-height:0;">&nbsp;</td>
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
                                <table cellpadding="0" cellspacing="0" width="100%" style="width: 180px;display: inline-block;padding-bottom: 10px;">
                                    <tr>
                                        <td align="center" valign="top" width="180">
                                            <table cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td style="padding: 0; font-size: 0; text-align: center;">
                                                        <table style="border-spacing: 0; vertical-align: top; width: 100%; max-width: 180px;display: inline-block;">
                                                            <tr>
                                                                <td align="left" valign="top" style="padding: 10px;border: 1px solid #FA7846;border-radius: 8px;background: #ffffff;">
                                                                    <table style="border-spacing:0;text-align:left;">
                                                                        <tr>
                                                                            <td align="left" valign="top" width="100" style="white-space: nowrap;">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 50px;line-height: 57px;text-align: center;color: #FA7846;vertical-align: top;padding-right: 5px;">3.</span>
                                                                                <img src="https://viarcanvas.com/letters/new/pic-7.png" width="100" height="80" style="margin:0; padding:0; border:none; display:inline-block;" border="0" alt="pic-1" />
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="35" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 18px; line-height: 23px;color: #FA7846;">@lang('mail.for_what_3_title') </span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="13" style="font-size:0; line-height:0;">&nbsp;</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="center" valign="top">
                                                                                <span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;line-height: 135%;text-align: center;color: #110F0F;">@lang('mail.for_what_3_text')</span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="40" style="font-size:0; line-height:0;">&nbsp;</td>
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
                <td height="30" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td height="40" style="font-size:0; line-height:0;">&nbsp;</td>
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
    <script>
        function copyCode() {
            const code = document.getElementById("promoCode").textContent;
            navigator.clipboard.writeText(code).then(() => {
                alert("Код скопирован: " + code);
            });
        }
    </script>
</body>

</html>
