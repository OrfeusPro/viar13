<!doctype html>
<html lang="{{ $locale ?? app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@lang('mail.order_overdue_delay_subject', ['order_id' => $order->id ?? ''])</title>
    <style>
        @media only screen and (max-width: 600px) {
            .order-delay-logo {
                width: 170px !important;
                max-width: 170px !important;
            }

            .order-delay-phone {
                font-size: 18px !important;
                line-height: 24px !important;
            }

            .order-delay-phone-icon {
                width: 22px !important;
                height: 22px !important;
                margin-right: 6px !important;
            }

            .order-delay-footer-row {
                display: block !important;
                width: 100% !important;
            }

            .order-delay-footer-cell {
                display: inline-block !important;
                width: 48% !important;
                box-sizing: border-box !important;
                text-align: left !important;
                vertical-align: top !important;
            }

            .order-delay-footer-instagram {
                display: block !important;
                width: 100% !important;
                padding-top: 16px !important;
                text-align: left !important;
            }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#ffffff;font-family:Arial,Helvetica,sans-serif;color:#1E2533;">
@php
    $giftImageSrc = isset($message)
        ? $message->embed(public_path('letters/new/order-delay-gift.png'))
        : 'https://viarcanvas.com/letters/new/order-delay-gift.png';
    $whatsappIconSrc = isset($message)
        ? $message->embed(public_path('letters/new/whatsap-icon-orange.png'))
        : 'https://viarcanvas.com/letters/new/whatsap-icon-orange.png';
    $globeIconSrc = isset($message)
        ? $message->embed(public_path('letters/new/globe-icon-orange.png'))
        : 'https://viarcanvas.com/letters/new/globe-icon-orange.png';
    $mailIconSrc = isset($message)
        ? $message->embed(public_path('letters/new/mail-icon-orange.png'))
        : 'https://viarcanvas.com/letters/new/mail-icon-orange.png';
    $instagramIconSrc = isset($message)
        ? $message->embed(public_path('letters/new/instagram-icon-orange-outline.png'))
        : 'https://viarcanvas.com/letters/new/instagram-icon-orange-outline.png';
@endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#ffffff;">
    <tr>
        <td align="center" style="padding:24px 12px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;max-width:600px;background:#fff2f4;">
                <tr>
                    <td style="padding:24px 48px 16px 48px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                            <tr>
                                <td align="left">
                                    <img class="order-delay-logo" src="https://viarcanvas.com/letters/new/Logo.png" width="210" alt="ViarCanvas" style="display:block;border:0;max-width:210px;width:100%;height:auto;">
                                </td>
                                <td class="order-delay-phone" align="right" style="font-size:20px;line-height:26px;color:#1E2533;white-space:nowrap;">
                                    <img class="order-delay-phone-icon" src="https://viarcanvas.com/letters/new/phone.png" width="24" height="24" alt="" style="vertical-align:middle;border:0;margin-right:8px;">
                                    <a href="https://wa.me/37127044470" style="color:#1E2533;text-decoration:none;">+371 27044470</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:12px 48px 0 48px;">
                        <h1 style="margin:0 0 24px 0;font-size:28px;line-height:34px;color:#172033;font-weight:700;">
                            @lang('mail.order_overdue_delay_heading_1')<br>
                            <span style="color:#FA7846;">@lang('mail.order_overdue_delay_heading_2')</span>
                        </h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 48px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                            <tr>
                                <td valign="top" style="font-size:16px;line-height:24px;color:#1E2533;">
                                    <p style="margin:0 0 12px 0;">@lang('mail.order_overdue_delay_text_1')</p>
                                    <p style="margin:0 0 12px 0;">@lang('mail.order_overdue_delay_text_2')</p>
                                    <p style="margin:0 0 12px 0;">@lang('mail.order_overdue_delay_text_3')</p>
                                    <p style="margin:0;">@lang('mail.order_overdue_delay_text_4')</p>
                                </td>
                                <td valign="middle" align="right" width="170" style="padding-left:20px;">
                                    <img src="{{ $giftImageSrc }}" width="170" alt="" style="display:block;border:0;max-width:170px;width:100%;height:auto;">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px 48px 18px 48px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border:1px solid #FA7846;border-radius:8px;background:#fff8f8;">
                            <tr>
                                <td width="72" align="center" valign="middle" style="padding:18px 0 18px 18px;">
                                    <a href="https://wa.me/37127044470" style="text-decoration:none;">
                                        <img src="{{ $whatsappIconSrc }}" width="52" height="52" alt="WhatsApp" style="display:block;border:0;">
                                    </a>
                                </td>
                                <td valign="middle" style="padding:18px;font-size:16px;line-height:23px;color:#1E2533;">
                                    @lang('mail.order_overdue_delay_whatsapp_text')<br>
                                    <a href="https://wa.me/37127044470" style="color:#FA7846;text-decoration:none;font-weight:bold;">@lang('mail.order_overdue_delay_whatsapp_phone')</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 48px 24px 48px;font-size:16px;line-height:24px;color:#1E2533;">
                        <p style="margin:0 0 16px 0;">@lang('mail.order_overdue_delay_thanks')</p>
                        <p style="margin:0;">@lang('mail.order_overdue_delay_signature')</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 48px 28px 48px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-top:1px solid #ead7d8;">
                            <tr class="order-delay-footer-row">
                                <td class="order-delay-footer-cell" style="padding-top:18px;font-size:13px;line-height:20px;color:#1E2533;">
                                    <img src="{{ $globeIconSrc }}" width="24" height="24" alt="" style="display:inline-block;border:0;vertical-align:middle;margin-right:8px;">
                                    <a href="https://viarcanvas.com" style="color:#1E2533;text-decoration:none;">viarcanvas.com</a>
                                </td>
                                <td class="order-delay-footer-cell" align="center" style="padding-top:18px;font-size:13px;line-height:20px;color:#1E2533;">
                                    <img src="{{ $mailIconSrc }}" width="24" height="24" alt="" style="display:inline-block;border:0;vertical-align:middle;margin-right:8px;">
                                    <a href="mailto:orders@viarcanvas.com" style="color:#1E2533;text-decoration:none;">orders@viarcanvas.com</a>
                                </td>
                                <td class="order-delay-footer-instagram" align="right" style="padding-top:18px;font-size:13px;line-height:20px;color:#1E2533;">
                                    <img src="{{ $instagramIconSrc }}" width="24" height="24" alt="" style="display:inline-block;border:0;vertical-align:middle;margin-right:8px;">
                                    <a href="https://www.instagram.com/viarcanvas/" style="color:#1E2533;text-decoration:none;">@viarcanvas</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
