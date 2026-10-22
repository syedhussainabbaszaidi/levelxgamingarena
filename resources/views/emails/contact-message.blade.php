<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New contact message</title>
</head>
<body style="margin:0;padding:0;background:#0b0b10;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#d4d4d8;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0b0b10;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#17171d;border:1px solid #2a2a35;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#18181f,#2a0f1a);padding:24px 28px;border-bottom:1px solid #2a2a35;">
                            <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}"
                                 width="150" height="41"
                                 style="display:block;width:150px;max-width:150px;height:auto;border:0;outline:none;text-decoration:none;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 28px 8px;">
                            <p style="margin:0 0 6px;font-size:12px;letter-spacing:3px;text-transform:uppercase;color:#ff2d55;font-weight:700;">Contact form</p>
                            <h1 style="margin:0;font-size:22px;line-height:1.3;color:#ffffff;">
                                {{ $submission['subject'] ?: 'New website enquiry' }}
                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 28px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;">
                                @foreach([
                                    'Name' => $submission['name'],
                                    'Email' => $submission['email'],
                                    'Phone' => $submission['phone'] ?: '—',
                                    'Subject' => $submission['subject'] ?: '—',
                                    'Submitted' => $submission['submitted_at'],
                                ] as $label => $value)
                                    <tr>
                                        <td style="padding:9px 0;border-bottom:1px solid #23232c;color:#8b8b98;width:38%;vertical-align:top;">{{ $label }}</td>
                                        <td style="padding:9px 0;border-bottom:1px solid #23232c;color:#f4f4f5;font-weight:600;vertical-align:top;">{{ $value }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 28px 0;">
                            <p style="margin:0 0 10px;font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#a1a1aa;">Message</p>
                            <div style="background:#101015;border:1px solid #23232c;border-radius:10px;padding:16px;font-size:14px;line-height:1.6;color:#e4e4e7;white-space:pre-wrap;">{{ $submission['message'] }}</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 28px 30px;">
                            <a href="mailto:{{ $submission['email'] }}"
                               style="display:inline-block;background:#ff2d55;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:13px 26px;border-radius:10px;">
                                Reply to {{ $submission['name'] }}
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 28px 26px;border-top:1px solid #2a2a35;font-size:12px;color:#71717a;">
                            Sent from the {{ config('app.name') }} website contact form.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
