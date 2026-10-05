@php
    $logoType = setting('site_logo_type', 'icon');
    $logoImage = setting('site_logo_image');
    $siteName = setting('site_name', 'BARITONE');
    $logoIcon = setting('site_logo_icon', 'bi-music-note-beamed');
    if (! Str::startsWith($logoIcon, 'bi-')) {
        $logoIcon = 'bi-' . $logoIcon;
    }
@endphp

@if($logoType === 'image' && !empty($logoImage))
    <img src="{{ asset($logoImage) }}" alt="{{ $siteName }}" height="36" style="max-height: 40px; object-fit: contain;">
@else
    <span class="badge bg-gold p-2 rounded-3 me-2"><i class="bi {{ $logoIcon }} text-dark fs-5"></i></span>
    <div class="d-flex flex-column justify-content-center lh-1">
        <span class="font-serif fw-bold text-white fs-4 m-0 p-0">{{ $siteName }}<span class="text-gold">.</span></span>
        <span class="text-danger" style="font-size: 0.75rem; font-weight: 500; margin-top: 2px;">Music Academy</span>
    </div>
@endif
