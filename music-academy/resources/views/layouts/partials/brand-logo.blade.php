@php
    $logoType = setting('site_logo_type', 'icon');
    $logoImage = setting('site_logo_image');
    $siteName = setting('site_name', 'HARMONIA');
    $logoIcon = setting('site_logo_icon', 'bi-music-note-beamed');
    if (! Str::startsWith($logoIcon, 'bi-')) {
        $logoIcon = 'bi-' . $logoIcon;
    }
@endphp

@if($logoType === 'image' && !empty($logoImage))
    <img src="{{ asset($logoImage) }}" alt="{{ $siteName }}" height="36" style="max-height: 40px; object-fit: contain;">
@else
    <span class="badge bg-gold p-2 rounded-3 me-1"><i class="bi {{ $logoIcon }} text-dark fs-5"></i></span>
    <span class="font-serif fw-bold text-white">{{ $siteName }}<span class="text-gold">.</span></span>
@endif
