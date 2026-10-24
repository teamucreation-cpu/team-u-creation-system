<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $heading }}</title>
</head>
<body style="margin:0; padding:24px 12px; background:#f3f4f6; color:#202124; font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb;">
        <tr>
            <td style="padding:22px 28px; background:#111111; border-bottom:4px solid #d4a017; color:#f0d060; font-size:18px; font-weight:700;">TEAM U CREATIONS</td>
        </tr>
        <tr>
            <td style="padding:28px;">
                <h1 style="margin:0 0 14px; color:#171717; font-size:22px; line-height:1.3;">{{ $heading }}</h1>
                <p style="margin:0 0 20px; color:#4b5563; font-size:15px; line-height:1.6;">{{ $intro }}</p>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                    @foreach($details as $label => $value)
                        <tr>
                            <th align="left" style="width:38%; padding:10px 8px; border-top:1px solid #e5e7eb; color:#6b7280; font-size:13px; font-weight:600; vertical-align:top;">{{ $label }}</th>
                            <td style="padding:10px 8px; border-top:1px solid #e5e7eb; color:#202124; font-size:14px; line-height:1.5; vertical-align:top;">{{ $value }}</td>
                        </tr>
                    @endforeach
                </table>
                <p style="margin:24px 0 0;">
                    <a href="{{ $actionUrl }}" style="display:inline-block; padding:12px 18px; background:#d4a017; color:#111111; font-size:14px; font-weight:700; text-decoration:none;">{{ $actionLabel }}</a>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:16px 28px; border-top:1px solid #e5e7eb; color:#6b7280; font-size:12px; line-height:1.5;">Team U Creations<br>This is an automated notification about your event coordination request.</td>
        </tr>
    </table>
</body>
</html>