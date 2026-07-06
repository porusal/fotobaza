@props([
    'name' => 'circle',
])

@php
    $paths = [
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'back' => '<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>',
        'circle' => '<circle cx="12" cy="12" r="8"/>',
        'edit' => '<path d="m16.5 3.5 4 4L8 20H4v-4L16.5 3.5Z"/><path d="m14 6 4 4"/>',
        'external' => '<path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M20 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h5"/>',
        'folder' => '<path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/>',
        'image' => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8" cy="10" r="1.5"/><path d="m21 15-5-5L5 19"/>',
        'menu' => '<path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/>',
        'plus' => '<path d="M12 5v14"/><path d="M5 12h14"/>',
        'save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/>',
        'settings' => '<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.05.05a2 2 0 1 1-2.83 2.83l-.05-.05A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21a2 2 0 1 1-4 0v-.07A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.05.05a2 2 0 1 1-2.83-2.83l.05-.05A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3a2 2 0 1 1 0-4h.07A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.05-.05A2 2 0 1 1 7.04 3.84l.05.05A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3a2 2 0 1 1 4 0v.07A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.05-.05a2 2 0 1 1 2.83 2.83l-.05.05A1.7 1.7 0 0 0 19.4 9c.38.13.7.34 1 .6.28.28.48.62.6 1H21a2 2 0 1 1 0 4h-.07a1.7 1.7 0 0 0-1.53.4Z"/>',
        'sync' => '<path d="M21 12a9 9 0 0 1-15.2 6.5L3 16"/><path d="M3 21v-5h5"/><path d="M3 12A9 9 0 0 1 18.2 5.5L21 8"/><path d="M21 3v5h-5"/>',
        'trash' => '<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'eye' => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off' => '<path d="m3 3 18 18"/><path d="M10.6 10.6A3 3 0 0 0 14 14"/><path d="M9.9 4.3A10.6 10.6 0 0 1 12 4c6 0 10 8 10 8a18 18 0 0 1-3 4.2"/><path d="M6.1 6.1C3.5 8 2 12 2 12s4 8 10 8a10.2 10.2 0 0 0 5-1.4"/>',
    ];

    $path = $paths[$name] ?? $paths['circle'];
@endphp

<svg {{ $attributes->merge(['class' => 'admin-icon', 'viewBox' => '0 0 24 24', 'aria-hidden' => 'true']) }} fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    {!! $path !!}
</svg>
