@props(['name', 'class' => 'h-5 w-5'])

@php
    // A small, hand-built line-icon set (24x24, stroke-based) so the whole UI
    // uses one consistent, professional visual language with no extra dependency.
    $icons = [
        'dashboard' => '<rect x="3" y="3" width="7.5" height="9" rx="1.6"/><rect x="13.5" y="3" width="7.5" height="5.5" rx="1.6"/><rect x="13.5" y="11.5" width="7.5" height="9.5" rx="1.6"/><rect x="3" y="15" width="7.5" height="6" rx="1.6"/>',
        'calendar-days' => '<rect x="3" y="4.5" width="18" height="16" rx="2"/><path d="M3 9h18"/><path d="M8 2.5v4M16 2.5v4"/><path d="M7.5 13h2M11 13h2M14.5 13h2M7.5 17h2M11 17h2"/>',
        'users' => '<path d="M16 20v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V20"/><circle cx="9.5" cy="7.5" r="3.5"/><path d="M21 20v-1.5a4 4 0 0 0-3-3.87"/><path d="M16 4.13a4 4 0 0 1 0 7.75"/>',
        'gamepad' => '<rect x="2.5" y="7" width="19" height="10" rx="4.5"/><path d="M7.5 10v4M5.5 12h4"/><circle cx="16" cy="11" r="1"/><circle cx="18.5" cy="13.2" r="1"/>',
        'tag' => '<path d="M3 12.5V5.5A2 2 0 0 1 5 3.5h7L21 12.5a2 2 0 0 1 0 2.83l-5.67 5.67a2 2 0 0 1-2.83 0z"/><circle cx="8" cy="8" r="1.6"/>',
        'banknotes' => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6.5 9.5v5M17.5 9.5v5"/>',
        'cog' => '<circle cx="12" cy="12" r="3"/><path d="M12 2.5v2.5M12 19v2.5M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M2.5 12H5M19 12h2.5M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8"/>',
        'sliders' => '<path d="M4 7h9M18.5 7H20M4 12h3M12.5 12H20M4 17h10M19 17h1"/><circle cx="15.5" cy="7" r="2.2"/><circle cx="9.5" cy="12" r="2.2"/><circle cx="16" cy="17" r="2.2"/>',
        'clock' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3.2 2"/>',
        'chart-bar' => '<path d="M3 21h18"/><rect x="5" y="11" width="3.6" height="7" rx="1"/><rect x="10.2" y="6" width="3.6" height="12" rx="1"/><rect x="15.4" y="9" width="3.6" height="9" rx="1"/>',
        'document' => '<path d="M6.5 3.5h7L18 8v12a1 1 0 0 1-1 1H6.5a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1z"/><path d="M13.5 3.5V8H18"/><path d="M8.5 12.5h7M8.5 16h5M8.5 9h2.5"/>',
        'user-circle' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="10" r="3"/><path d="M6.6 18.6a6 6 0 0 1 10.8 0"/>',
        'shield' => '<path d="M12 3l7 3v5c0 4.5-3 8.5-7 10-4-1.5-7-5.5-7-10V6z"/><path d="M9 12l2 2 4-4"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close' => '<path d="M6 6l12 12M18 6L6 18"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'pencil' => '<path d="M4 20h4L18.5 9.5a2 2 0 0 0-2.83-2.83L5 17z"/><path d="M13.5 7.5l3 3"/>',
        'trash' => '<path d="M4 7h16M9.5 7V5.2a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1V7"/><path d="M6.5 7l.9 12.1a1 1 0 0 0 1 .9h7.2a1 1 0 0 0 1-.9L17.5 7"/><path d="M10 11v6M14 11v6"/>',
        'eye' => '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/>',
        'star' => '<path d="M12 3.5l2.6 5.4 5.9.8-4.3 4.1 1.1 5.9L12 16.9 6.7 19.7l1.1-5.9L3.5 9.7l5.9-.8z"/>',
        'search' => '<circle cx="11" cy="11" r="6.5"/><path d="M15.8 15.8l4.7 4.7"/>',
        'check' => '<path d="M5 12.5l4.5 4.5L19 7"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/>',
        'x-circle' => '<circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/>',
        'warning' => '<path d="M12 3.5l9 16H3z"/><path d="M12 9.5v4M12 16.6h.01"/>',
        'arrow-right' => '<path d="M4 12h15M13 6l6 6-6 6"/>',
        'arrow-left' => '<path d="M20 12H5M11 6l-6 6 6 6"/>',
        'chevron-down' => '<path d="M6 9l6 6 6-6"/>',
        'chevron-right' => '<path d="M9 6l6 6-6 6"/>',
        'home' => '<path d="M4 10.5L12 4l8 6.5V20a1 1 0 0 1-1 1h-4.5v-6h-5v6H5a1 1 0 0 1-1-1z"/>',
        'logout' => '<path d="M15 12H4M8 8l-4 4 4 4"/><path d="M11 5V4a1 1 0 0 1 1-1h7a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-7a1 1 0 0 1-1-1v-1"/>',
        'phone' => '<path d="M6.6 3.5h2.9l1.5 4-2 1.4a12.5 12.5 0 0 0 6 6l1.4-2 4 1.5v2.9a2 2 0 0 1-2.1 2A15.5 15.5 0 0 1 4.6 5.6a2 2 0 0 1 2-2.1z"/>',
        'whatsapp' => '<path d="M3.6 20.4l1.3-4.1A8.4 8.4 0 1 1 8 19.1z"/><path d="M9.1 8.1c.2-.4.4-.4.7-.4h.5c.2 0 .4 0 .5.4l.7 1.6c.1.2 0 .4-.1.6l-.5.6c-.1.2-.2.3 0 .6.4.6 1 1.4 1.9 1.9.3.2.5.1.6 0l.6-.7c.2-.2.4-.2.6-.1l1.5.7c.3.1.4.2.4.4v.6c0 .7-.5 1.3-1.2 1.4-1.4.3-3-.2-4.4-1.4-1.2-1-2.1-2.5-2.3-3.7-.1-.8.1-1.5.5-2.1z"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M4 7.5l8 5.5 8-5.5"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5.5M12 7.8h.01"/>',
        'map-pin' => '<path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'lock' => '<rect x="4.5" y="10.5" width="15" height="10" rx="2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/>',
        'download' => '<path d="M12 3.5v11.5M7.5 10.5L12 15l4.5-4.5"/><path d="M5 20.5h14"/>',
        'sparkles' => '<path d="M12 4l1.6 4.4L18 10l-4.4 1.6L12 16l-1.6-4.4L6 10l4.4-1.6z"/><path d="M18.5 15l.7 1.8L21 17.5l-1.8.7-.7 1.8-.7-1.8L16 17.5l1.8-.7z"/>',
        'monitor' => '<rect x="2.5" y="4" width="19" height="12.5" rx="2"/><path d="M9 20.5h6M12 16.5v4"/>',
        'wifi' => '<path d="M2.5 8.5a15 15 0 0 1 19 0"/><path d="M5.5 12a10.5 10.5 0 0 1 13 0"/><path d="M8.5 15.5a6 6 0 0 1 7 0"/><path d="M12 19h.01"/>',
        'sofa' => '<path d="M5 11V7.5A1.5 1.5 0 0 1 6.5 6h11A1.5 1.5 0 0 1 19 7.5V11"/><path d="M5 11a2.5 2.5 0 0 0-2.5 2.5V17h19v-3.5A2.5 2.5 0 0 0 19 11"/><path d="M6.5 17v1.5M17.5 17v1.5"/>',
        'crown' => '<path d="M3.5 8l4 3.5L12 5l4.5 6.5 4-3.5-1.5 10h-15z"/><path d="M5 21h14"/>',
    ];
@endphp

<svg {{ $attributes->merge(['class' => $class, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.7', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
    {!! $icons[$name] ?? $icons['info'] !!}
</svg>
