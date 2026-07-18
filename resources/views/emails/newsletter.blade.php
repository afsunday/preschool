<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $campaign->subject }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f1ede9; }
        a { color: #ec1e79; }
        .nl-body { font-size: 15.5px; line-height: 1.75; color: #43414a; }
        .nl-body p { margin: 0 0 16px; }
        .nl-body img { max-width: 100%; height: auto; border-radius: 12px; }
        .nl-body h1, .nl-body h2, .nl-body h3 { color: #1f1d24; line-height: 1.3; margin: 26px 0 12px; }
        .nl-body h2 { font-size: 20px; }
        .nl-body ul, .nl-body ol { margin: 0 0 16px; padding-left: 22px; }
        .nl-body li { margin: 0 0 6px; }
        .nl-body a { text-decoration: underline; }
        .nl-body blockquote { margin: 16px 0; padding: 4px 0 4px 16px; border-left: 3px solid #f4c9dd; color: #6a6772; }
        @media (max-width: 600px) {
            .card-pad { padding: 28px 24px !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#f1ede9; -webkit-text-size-adjust:100%; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    {{-- Preheader: the inbox peek before opening. --}}
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ $campaign->subject }}</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1ede9;">
        <tr>
            <td align="center" style="padding:36px 16px 40px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px;">

                    {{-- Brand mark, sitting on the page (not in a heavy band) --}}
                    <tr>
                        <td align="center" style="padding:0 0 22px;">
                            <a href="{{ config('app.url') }}" style="text-decoration:none;">
                                <img src="{{ asset('images/brand/logo.png') }}" width="132" alt="{{ config('app.name') }}"
                                     style="display:inline-block; width:132px; max-width:55%; height:auto; border:0;">
                            </a>
                        </td>
                    </tr>

                    {{-- The card --}}
                    <tr>
                        <td style="background-color:#ffffff; border-radius:18px; box-shadow:0 6px 26px rgba(90,30,55,0.07); overflow:hidden;">
                            {{-- slim brand accent along the top --}}
                            <div style="height:4px; background-color:#ec1e79; line-height:4px; font-size:4px;">&nbsp;</div>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="card-pad" style="padding:40px 44px 36px;">
                                        <p style="margin:0 0 10px; font-size:12px; letter-spacing:1.2px; text-transform:uppercase; color:#ec1e79; font-weight:700;">
                                            From {{ config('app.name') }}
                                        </p>

                                        <h1 style="margin:0 0 24px; font-size:26px; line-height:1.25; font-weight:800; color:#1f1d24;">
                                            {{ $campaign->subject }}
                                        </h1>

                                        <div class="nl-body">
                                            {!! $campaign->body !!}
                                        </div>

                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:30px;">
                                            <tr>
                                                <td style="border-top:1px solid #f0eaed; padding-top:22px; font-size:14.5px; line-height:1.6; color:#8a868f;">
                                                    Warmly,<br>
                                                    <span style="color:#ec1e79; font-weight:700;">The {{ config('app.name') }} team</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer, on the page below the card --}}
                    <tr>
                        <td align="center" style="padding:26px 24px 0;">
                            <p style="margin:0 0 6px; font-size:12.5px; font-weight:700; color:#9b939a; letter-spacing:0.3px;">
                                {{ config('app.name') }}
                            </p>
                            <p style="margin:0; font-size:12px; line-height:1.7; color:#b3abb1;">
                                You're receiving this because you subscribed on our website.<br>
                                <a href="{{ $unsubscribeUrl }}" style="color:#ec1e79; text-decoration:underline;">Unsubscribe</a>
                                &nbsp;·&nbsp;
                                <a href="{{ config('app.url') }}" style="color:#b3abb1; text-decoration:underline;">Visit our site</a>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
