@php
    $accent = setting('color_accent', '#f59e0b');
    $accentHover = setting('color_accent_hover', '#d97706');
    $primary = setting('color_primary', '#4f46e5');
    $bgDark = setting('color_bg_dark', '#0a0e17');
    $bgSurface = setting('color_bg_surface', '#111827');
    $bgElevated = setting('color_bg_surface_elevated', '#1f2937');

    $accentRgb = hexToRgb($accent, '245, 158, 11');
    $primaryRgb = hexToRgb($primary, '79, 70, 229');
    $surfaceRgb = hexToRgb($bgSurface, '17, 24, 39');
    $darkRgb = hexToRgb($bgDark, '10, 14, 23');
@endphp

<style id="dynamic-academy-theme">
    :root {
        --gold: {{ $accent }};
        --gold-light: {{ $accent }};
        --gold-dark: {{ $accentHover }};
        --gold-glow: rgba({{ $accentRgb }}, 0.25);
        --border-gold: rgba({{ $accentRgb }}, 0.4);

        --primary: {{ $primary }};
        --bg-dark: {{ $bgDark }};
        --bg-surface: {{ $bgSurface }};
        --bg-surface-elevated: {{ $bgElevated }};
        --bg-surface-glass: rgba({{ $surfaceRgb }}, 0.85);
    }

    body {
        background-color: var(--bg-dark) !important;
    }

    .bg-dark {
        background-color: var(--bg-dark) !important;
    }

    .bg-surface {
        background-color: var(--bg-surface) !important;
    }

    .bg-surface-elevated {
        background-color: var(--bg-surface-elevated) !important;
    }

    .navbar-custom {
        background-color: rgba({{ $darkRgb }}, 0.95) !important;
        border-bottom-color: var(--border-gold) !important;
    }

    .btn-gold {
        background-color: var(--gold) !important;
        border-color: var(--gold) !important;
        color: #0a0e17 !important;
        font-weight: 600;
    }
    .btn-gold:hover, .btn-gold:focus, .btn-gold:active {
        background-color: var(--gold-dark) !important;
        border-color: var(--gold-dark) !important;
        color: #ffffff !important;
    }

    .btn-outline-gold {
        border-color: var(--gold) !important;
        color: var(--gold) !important;
    }
    .btn-outline-gold:hover, .btn-outline-gold:focus, .btn-outline-gold:active {
        background-color: var(--gold) !important;
        color: #0a0e17 !important;
    }

    .text-gold {
        color: var(--gold) !important;
    }

    .bg-gold {
        background-color: var(--gold) !important;
        color: #0a0e17 !important;
    }

    .border-gold {
        border-color: var(--border-gold) !important;
    }

    .hero-gradient {
        background: radial-gradient(circle at 50% 20%, rgba({{ $primaryRgb }}, 0.28) 0%, rgba({{ $darkRgb }}, 0.98) 75%) !important;
    }
</style>
