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
                    <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 34px; color: #1E2533;margin: 0;">@lang('mail.mail_your_order_received_title_1')</span>
                </td>
            </tr>
            <tr>
                <td height="18" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td height="32" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <!-- form -->
            <tr>
                <td align="center" valign="top" bgcolor="#F4E4E4">
                    <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 368px;background: #ffffff;border: 2px solid #FC8C5F;">
                        <tr style="padding: 5px;">
                            <td align="left" valign="top" style="padding-left: 49px;border: 0.5px solid #FC8C5F;">
                                <table cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td height="33" style="font-size:0; line-height:0;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td align="left" valign="top">
                                            <span style="font-family: Arial;font-style: normal; font-size: 15px; line-height: 17px;color: #1E2533;">{!! $data['content'] !!}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="32" style="font-size:0; line-height:0;">&nbsp;</td>
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
        </table>
        <table class="main" width="100%">
            <tr>
                <td height="32" bgcolor="#F7E2E3" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td height="32" style="font-size:0; line-height:0;">&nbsp;</td>
            </tr>
            <!-- START 3 COLUMNS -->
            <tr>
                <td align="center" valign="top" style="padding-left: 10px;">
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
                                <span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 30px;line-height: 44px; text-align: center;color: #1E2533;">@lang('mail.questions_2') </span>
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