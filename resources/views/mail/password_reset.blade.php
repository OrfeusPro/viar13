<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ app()->getLocale() }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="telephone=no" name="format-detection">
    <title>@lang('passwords.reset_subject')</title>
    <style type="text/css">
        body, table, td, p, a { font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; }
        table { border-collapse: collapse; border-spacing: 0; }
        p { margin: 0; padding: 0; }
        .orange { color: #FA7846; }
        .bold { font-weight: 700; }
        @media only screen and (max-width: 600px) {
            .mail-shell, .mail-content { width: 100% !important; }
            .mail-body { padding: 30px 20px !important; }
            .mail-title { font-size: 26px !important; line-height: 32px !important; }
            .logo { width: 180px !important; height: auto !important; }
            .header-phone { font-size: 18px !important; line-height: 24px !important; }
            .header-phone-icon { width: 24px !important; height: 24px !important; margin-right: 5px !important; }
            .footer-column { display: block !important; width: 100% !important; padding: 0 0 24px !important; }
            .footer-brand td { display: block !important; width: 100% !important; text-align: center !important; }
            .footer-brand .logo { margin: 0 auto 20px !important; }
            .footer-title { font-size: 23px !important; line-height: 30px !important; }
            .fallback-link { word-break: break-all !important; }
        }
    </style>
</head>
<body style="width:100%;margin:0;padding:0;background-color:#F6F6F6;color:#1E2533;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
<table width="100%" cellspacing="0" cellpadding="0" role="presentation" style="width:100%;background-color:#F6F6F6;">
    <tr>
        <td align="center" valign="top" style="padding:0;">
            <table class="mail-shell" width="600" cellspacing="0" cellpadding="0" role="presentation" style="width:600px;background-color:#FBF2EA;">
                <tr>
                    @include('.mail.main_head_orange')
                </tr>
                <tr>
                    <td class="mail-body" style="padding:42px 40px 40px;">
                        <h1 class="mail-title" style="margin:0 0 18px;font-size:30px;line-height:38px;color:#1E2533;">@lang('passwords.reset_page_title')</h1>
                        <p style="margin:0 0 16px;font-size:17px;line-height:27px;">@lang('passwords.reset_greeting', ['name' => $user->first_name ?: trans('passwords.reset_customer')])</p>
                        <p style="margin:0 0 30px;font-size:17px;line-height:27px;color:#555E6D;">@lang('passwords.reset_intro')</p>

                        <table cellspacing="0" cellpadding="0" role="presentation" align="center" style="margin:0 auto 30px;background:#FA7846;width:290px;height:60px;border-radius:8px;box-shadow:0 3px 0 #E87145;">
                            <tr>
                                <td align="center" valign="middle">
                                    <a href="{{ $resetUrl }}" target="_blank" style="display:block;padding:19px 20px;color:#FFFFFF;font-size:16px;line-height:22px;font-weight:700;text-decoration:none;">@lang('passwords.reset_action')</a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 12px;font-size:15px;line-height:24px;color:#6D7481;">@lang('passwords.reset_expire', ['minutes' => $expires])</p>
                        <p style="margin:0 0 28px;font-size:15px;line-height:24px;color:#6D7481;">@lang('passwords.reset_ignore')</p>
                        <p style="margin:0 0 9px;font-size:14px;line-height:22px;color:#6D7481;">@lang('passwords.reset_help')</p>
                        <p class="fallback-link" style="margin:0;font-size:14px;line-height:22px;word-break:break-all;">
                            <a href="{{ $resetUrl }}" style="color:#FA7846;text-decoration:underline;">{{ $resetUrl }}</a>
                        </p>
                    </td>
                </tr>
            </table>

            @include('.mail.password_reset_contact_footer')
        </td>
    </tr>
</table>
</body>
</html>
