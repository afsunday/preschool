<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $campaign->subject }}</title>
</head>
<body style="margin:0; padding:0; width:100%; background-color:#fff7f0; -webkit-text-size-adjust:100%; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#2b2b2b;">
    {{-- Preheader: the peek shown in the inbox before opening. --}}
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        {{ $campaign->subject }}
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#fff7f0;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px;">

                    {{-- Brand band --}}
                    <tr>
                        <td style="background-color:#ec1e79; border-radius:22px 22px 0 0; padding:32px 32px 28px; text-align:center;">
                            <a href="{{ config('app.url') }}" style="text-decoration:none;">
                                <img src="{{ asset('images/brand/logo.png') }}" width="128" alt="{{ config('app.name') }}"
                                     style="display:inline-block; width:128px; max-width:60%; height:auto; border:0;">
                            </a>
                            <div style="margin-top:12px; font-size:13px; letter-spacing:0.5px; color:#ffe1ee; text-transform:uppercase;">
                                News &amp; happenings
                            </div>
                        </td>
                    </tr>

                    {{-- Message card --}}
                    <tr>
                        <td style="background-color:#ffffff; padding:40px 36px 32px;">
                            <h1 style="margin:0 0 20px; font-size:25px; line-height:1.25; font-weight:800; color:#ec1e79;">
                                {{ $campaign->subject }}
                            </h1>

                            <div style="font-size:15px; line-height:1.75; color:#3a3a3a;">
                                {!! nl2br(e($campaign->body)) !!}
                            </div>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:32px;">
                                <tr>
                                    <td style="border-top:1px solid #f3e7ec; padding-top:22px; font-size:14px; line-height:1.6; color:#8a8a8a;">
                                        With love,<br>
                                        <span style="color:#ec1e79; font-weight:700;">The {{ config('app.name') }} team</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color:#ffffff; border-radius:0 0 22px 22px; padding:0 36px 36px; text-align:center;">
                            <div style="border-top:1px solid #f3e7ec; padding-top:24px; font-size:12px; line-height:1.7; color:#b3a8ad;">
                                You're receiving this because you subscribed at {{ config('app.name') }}.<br>
                                <a href="{{ $unsubscribeUrl }}" style="color:#ec1e79; text-decoration:underline;">Unsubscribe</a>
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
