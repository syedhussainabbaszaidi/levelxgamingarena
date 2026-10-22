@php
    $currency = $booking->business?->currency ?? 'PKR';
    $customer = $booking->customer;

    // Pull a player/headcount answer out of the dynamic booking fields when configured.
    $players = null;
    foreach ($booking->fieldValues as $value) {
        $label = strtolower((string) $value->field?->label);
        if (str_contains($label, 'player') || str_contains($label, 'people') || str_contains($label, 'person') || str_contains($label, 'guest')) {
            $players = $value->value;
            break;
        }
    }

    $adminUrl = route('admin.bookings.show', $booking);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New booking {{ $booking->code }}</title>
</head>
<body style="margin:0;padding:0;background:#0b0b10;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#d4d4d8;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0b0b10;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#17171d;border:1px solid #2a2a35;border-radius:16px;overflow:hidden;">
                    {{-- Brand header --}}
                    <tr>
                        <td style="background:linear-gradient(135deg,#18181f,#2a0f1a);padding:24px 28px;border-bottom:1px solid #2a2a35;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align:middle;">
                                        <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}"
                                             width="150" height="41"
                                             style="display:block;width:150px;max-width:150px;height:auto;border:0;outline:none;text-decoration:none;">
                                    </td>
                                    <td align="right" style="vertical-align:middle;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#a1a1aa;">{{ $booking->code }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Title --}}
                    <tr>
                        <td style="padding:28px 28px 8px;">
                            <p style="margin:0 0 6px;font-size:12px;letter-spacing:3px;text-transform:uppercase;color:#ff2d55;font-weight:700;">New booking</p>
                            <h1 style="margin:0;font-size:24px;line-height:1.3;color:#ffffff;">{{ $booking->resource?->name ?? 'Gaming activity' }}</h1>
                            <p style="margin:8px 0 0;font-size:14px;color:#a1a1aa;">
                                {{ $booking->starts_at?->format('D, d M Y') }} ·
                                {{ $booking->starts_at?->format('g:i A') }} – {{ $booking->ends_at?->format('g:i A') }}
                            </p>
                        </td>
                    </tr>

                    {{-- Details --}}
                    <tr>
                        <td style="padding:20px 28px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;">
                                @php
                                    $rows = [
                                        'Booking reference' => $booking->code,
                                        'Customer name' => $customer?->name ?? $booking->user?->name ?? 'Guest',
                                        'Customer email' => $customer?->email ?? $booking->user?->email ?? '—',
                                        'Customer phone' => $customer?->phone ?? '—',
                                        'Activity / service' => ($booking->resource?->name ?? '—').($booking->resource?->category?->name ? ' ('.$booking->resource->category->name.')' : ''),
                                        'Booking date' => $booking->starts_at?->format('D, d M Y') ?? '—',
                                        'Booking time' => ($booking->starts_at?->format('g:i A') ?? '—').' – '.($booking->ends_at?->format('g:i A') ?? '—'),
                                        'Duration' => $booking->duration_minutes.' minutes',
                                    ];

                                    if ($players !== null) {
                                        $rows['Players'] = $players;
                                    }

                                    $rows['Price'] = number_format((float) $booking->final_cost, 0).' '.$currency;
                                    $rows['Payment status'] = ucfirst((string) $booking->payment_status);
                                    $rows['Booking status'] = ucfirst(str_replace('_', ' ', (string) $booking->status));
                                @endphp

                                @foreach($rows as $label => $value)
                                    <tr>
                                        <td style="padding:9px 0;border-bottom:1px solid #23232c;color:#8b8b98;width:44%;vertical-align:top;">{{ $label }}</td>
                                        <td style="padding:9px 0;border-bottom:1px solid #23232c;color:#f4f4f5;font-weight:600;vertical-align:top;">{{ $value }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    {{-- Additional details --}}
                    @if($booking->fieldValues->isNotEmpty() || $booking->notes)
                        <tr>
                            <td style="padding:22px 28px 0;">
                                <p style="margin:0 0 10px;font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#a1a1aa;">Additional details</p>
                                <div style="background:#101015;border:1px solid #23232c;border-radius:10px;padding:14px 16px;font-size:13px;color:#d4d4d8;">
                                    @foreach($booking->fieldValues as $value)
                                        <p style="margin:0 0 6px;"><strong style="color:#f4f4f5;">{{ $value->field?->label }}:</strong> {{ $value->value }}</p>
                                    @endforeach
                                    @if($booking->notes)
                                        <p style="margin:0;"><strong style="color:#f4f4f5;">Notes:</strong> {{ $booking->notes }}</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endif

                    {{-- CTA --}}
                    <tr>
                        <td style="padding:24px 28px 30px;">
                            <a href="{{ $adminUrl }}"
                               style="display:inline-block;background:#ff2d55;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:13px 26px;border-radius:10px;">
                                View booking in admin panel
                            </a>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:18px 28px 26px;border-top:1px solid #2a2a35;font-size:12px;color:#71717a;">
                            This notification was sent automatically by {{ config('app.name') }} when a customer booked online.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
