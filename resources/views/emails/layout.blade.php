<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('subject', config('app.name'))</title>
</head>
<body style="margin:0; padding:0; background-color:#f6f3ec; font-family: Georgia, 'Times New Roman', serif; color:#16231c;">
    @isset($preview)
        <div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ $preview }}</div>
    @endisset

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f3ec;">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background-color:#ffffff; border-radius:12px; border:1px solid #e2ece4;">
                    <tr>
                        <td style="background-color:#0e1f17; padding:24px 32px; border-radius:12px 12px 0 0;">
                            <span style="color:#f6f3ec; font-size:20px; font-weight:600; letter-spacing:0.03em;">
                                {{ config('app.name') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px; font-size:15px; line-height:1.6; color:#16231c;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f0f5f1; padding:20px 32px; font-size:12px; line-height:1.6; color:#6b6155; border-radius:0 0 12px 12px;">
                            <strong>{{ $salon->name ?? config('app.name') }}</strong><br>
                            @if (! empty($salon))
                                {{ trim("{$salon->address}, {$salon->city}, {$salon->state} {$salon->zip_code}", ' ,') }}<br>
                                {{ $salon->phone }}
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
