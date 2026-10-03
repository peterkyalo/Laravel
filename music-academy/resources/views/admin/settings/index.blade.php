@extends('layouts.dashboard')

@php
    $tabMeta = [
        'colors' => [
            'badge' => 'Live Theme & Styles',
            'icon' => 'bi-palette2',
            'title' => 'Theme & Color Palette',
            'desc' => 'Customize primary accent, hover tones, background shades, and 1-click theme presets.',
        ],
        'branding' => [
            'badge' => 'Identity & Assets',
            'icon' => 'bi-badge-ad',
            'title' => 'Logo, Favicon & Brand Identity',
            'desc' => 'Configure academy name, taglines, navbar logo formatting, image upload, and browser tab icon.',
        ],
        'navigation' => [
            'badge' => 'Public Site Navigation',
            'icon' => 'bi-menu-button-wide',
            'title' => 'Public Site Header & Navigation Menus',
            'desc' => 'Customize public navbar links, labels, destination URLs, and CTA button settings.',
        ],
        'hero' => [
            'badge' => 'Homepage Showcase',
            'icon' => 'bi-megaphone',
            'title' => 'Landing Page Hero Section',
            'desc' => 'Customize the marquee headline, subtitle, action callouts, and student/faculty counter metrics.',
        ],
        'sections' => [
            'badge' => 'Landing Page Layout',
            'icon' => 'bi-grid-3x3',
            'title' => 'Landing Page Content Sections',
            'desc' => 'Manage discipline categories, featured masterclasses, pedagogical pillars, and admissions CTA.',
        ],
        'about' => [
            'badge' => 'Institutional Heritage',
            'icon' => 'bi-bank2',
            'title' => 'About Us: Heritage, Story & Leadership',
            'desc' => 'Narrate the conservatory history, Dean’s welcome address, founding statistics, and four pillars of excellence.',
        ],
        'student' => [
            'badge' => 'Student Experience',
            'icon' => 'bi-mortarboard',
            'title' => 'Student Portal Settings & Welcome Banner',
            'desc' => 'Configure student dashboard headers, broadcast announcements, practice tips, and active notifications.',
        ],
        'instructor' => [
            'badge' => 'Faculty Experience',
            'icon' => 'bi-music-note-beamed',
            'title' => 'Instructor Studio & Pedagogical Guidelines',
            'desc' => 'Configure instructor studio headers, evaluation instructions, and faculty announcements.',
        ],
        'footer' => [
            'badge' => 'Campus & Contact',
            'icon' => 'bi-telephone',
            'title' => 'Footer, Campus Address & Social Links',
            'desc' => 'Manage campus contact details, social media handles, copyright text, and footer bio.',
        ],
    ];

    $activeTab = request('tab', 'colors');
    if (!array_key_exists($activeTab, $tabMeta)) {
        $activeTab = 'colors';
    }
    $currentTab = $tabMeta[$activeTab];
@endphp

@section('title', $currentTab['title'] . ' - Site Customizer')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-gold text-dark fw-bold"><i class="bi {{ $currentTab['icon'] }} me-1"></i> {{ $currentTab['badge'] }}</span>
            <span class="text-gold fw-bold small text-uppercase" style="letter-spacing: 0.08em;">Site Customization</span>
        </div>
        <h2 class="font-serif text-white fw-bold mb-0">{{ $currentTab['title'] }}</h2>
        <span class="text-muted small">{{ $currentTab['desc'] }}</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-light btn-sm border-secondary">
            <i class="bi bi-box-arrow-up-right me-1"></i> Preview Public Site
        </a>
        <form action="{{ route('admin.settings.reset') }}" method="POST" onsubmit="return confirm('Are you sure you want to reset all site settings and colors back to factory defaults?');">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Defaults
            </button>
        </form>
    </div>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="active_tab" id="activeTabInput" value="{{ $activeTab }}">

    @if($activeTab === 'colors')
        <!-- ==================== PANE 1: COLORS & THEME ==================== -->
        <div class="card card-solid p-4 mb-4">
            <h5 class="font-serif text-white fw-bold mb-1"><i class="bi bi-palette-fill text-gold me-2"></i> Color Palette & Global Theming</h5>
            <p class="text-muted small mb-4">Changes to these color codes apply instantly across all public pages, headers, badges, buttons, student portals, and instructor studio pages.</p>

            <!-- 1-Click Preset Palettes -->
            <div class="mb-4 p-3 rounded-3 bg-surface-elevated border border-secondary">
                <label class="form-label fw-bold text-white small mb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-magic text-gold"></i> Quick Theme Presets (1-Click Apply)
                </label>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($presets as $key => $preset)
                        <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2 border-secondary text-white"
                            onclick="applyPreset('{{ $preset['accent'] }}', '{{ $preset['hover'] }}', '{{ $preset['primary'] }}', '{{ $preset['dark'] }}', '{{ $preset['surface'] }}', '{{ $preset['elevated'] }}')">
                            <span class="rounded-circle d-inline-block border border-secondary" style="width: 14px; height: 14px; background-color: {{ $preset['accent'] }};"></span>
                            <span class="rounded-circle d-inline-block border border-secondary" style="width: 14px; height: 14px; background-color: {{ $preset['primary'] }};"></span>
                            <span class="small">{{ $preset['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="row g-4">
                <!-- Accent / Gold Color -->
                <div class="col-md-6 col-lg-4">
                    <label class="form-label text-white small fw-bold">Primary Accent Color (Gold / Brand Focus)</label>
                    <div class="input-group">
                        <input type="color" class="form-control form-control-color bg-surface border-secondary" id="color_accent_picker" value="{{ $settings['color_accent'] }}" onchange="syncColor('color_accent_picker', 'color_accent')">
                        <input type="text" class="form-control bg-surface text-white border-secondary font-monospace" name="color_accent" id="color_accent" value="{{ $settings['color_accent'] }}" required oninput="syncPicker('color_accent', 'color_accent_picker')">
                    </div>
                    <small class="text-muted">Used for virtuoso highlights, star accents, icons, and hero buttons.</small>
                </div>

                <!-- Accent Hover Color -->
                <div class="col-md-6 col-lg-4">
                    <label class="form-label text-white small fw-bold">Accent Hover / Active Tone</label>
                    <div class="input-group">
                        <input type="color" class="form-control form-control-color bg-surface border-secondary" id="color_accent_hover_picker" value="{{ $settings['color_accent_hover'] }}" onchange="syncColor('color_accent_hover_picker', 'color_accent_hover')">
                        <input type="text" class="form-control bg-surface text-white border-secondary font-monospace" name="color_accent_hover" id="color_accent_hover" value="{{ $settings['color_accent_hover'] }}" oninput="syncPicker('color_accent_hover', 'color_accent_hover_picker')">
                    </div>
                    <small class="text-muted">Hover state shade for accent buttons and active pills.</small>
                </div>

                <!-- Primary Secondary Color -->
                <div class="col-md-6 col-lg-4">
                    <label class="form-label text-white small fw-bold">Secondary Theme Accent (Indigo / Sapphire)</label>
                    <div class="input-group">
                        <input type="color" class="form-control form-control-color bg-surface border-secondary" id="color_primary_picker" value="{{ $settings['color_primary'] }}" onchange="syncColor('color_primary_picker', 'color_primary')">
                        <input type="text" class="form-control bg-surface text-white border-secondary font-monospace" name="color_primary" id="color_primary" value="{{ $settings['color_primary'] }}" required oninput="syncPicker('color_primary', 'color_primary_picker')">
                    </div>
                    <small class="text-muted">Used for radial gradients, lesson badges, and secondary buttons.</small>
                </div>

                <!-- Background Dark Tone -->
                <div class="col-md-6 col-lg-4">
                    <label class="form-label text-white small fw-bold">Deep Body Background Tone</label>
                    <div class="input-group">
                        <input type="color" class="form-control form-control-color bg-surface border-secondary" id="color_bg_dark_picker" value="{{ $settings['color_bg_dark'] }}" onchange="syncColor('color_bg_dark_picker', 'color_bg_dark')">
                        <input type="text" class="form-control bg-surface text-white border-secondary font-monospace" name="color_bg_dark" id="color_bg_dark" value="{{ $settings['color_bg_dark'] }}" required oninput="syncPicker('color_bg_dark', 'color_bg_dark_picker')">
                    </div>
                    <small class="text-muted">Base background color for the application.</small>
                </div>

                <!-- Surface Tone -->
                <div class="col-md-6 col-lg-4">
                    <label class="form-label text-white small fw-bold">Card & Surface Background Tone</label>
                    <div class="input-group">
                        <input type="color" class="form-control form-control-color bg-surface border-secondary" id="color_bg_surface_picker" value="{{ $settings['color_bg_surface'] }}" onchange="syncColor('color_bg_surface_picker', 'color_bg_surface')">
                        <input type="text" class="form-control bg-surface text-white border-secondary font-monospace" name="color_bg_surface" id="color_bg_surface" value="{{ $settings['color_bg_surface'] }}" required oninput="syncPicker('color_bg_surface', 'color_bg_surface_picker')">
                    </div>
                    <small class="text-muted">Color for cards, sidebars, modal dialogs, and panels.</small>
                </div>

                <!-- Surface Elevated Tone -->
                <div class="col-md-6 col-lg-4">
                    <label class="form-label text-white small fw-bold">Elevated Cards & Stat Pill Tone</label>
                    <div class="input-group">
                        <input type="color" class="form-control form-control-color bg-surface border-secondary" id="color_bg_surface_elevated_picker" value="{{ $settings['color_bg_surface_elevated'] }}" onchange="syncColor('color_bg_surface_elevated_picker', 'color_bg_surface_elevated')">
                        <input type="text" class="form-control bg-surface text-white border-secondary font-monospace" name="color_bg_surface_elevated" id="color_bg_surface_elevated" value="{{ $settings['color_bg_surface_elevated'] }}" oninput="syncPicker('color_bg_surface_elevated', 'color_bg_surface_elevated_picker')">
                    </div>
                    <small class="text-muted">Subtle elevated surface for inputs and stat badges.</small>
                </div>
            </div>

            <!-- Live Preview Widget -->
            <div class="mt-4 p-3 rounded-3 border border-secondary" style="background: var(--bg-surface-elevated);">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2 rounded-3 border" style="background: {{ $settings['color_accent'] }}; color: #000;">
                            <i class="bi bi-music-note-beamed fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-white">Live Palette Contrast Verification</div>
                            <small class="text-muted">Preview button and badge appearance with your current color selection.</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm text-dark fw-bold px-3" style="background-color: {{ $settings['color_accent'] }};">
                            Accent Button
                        </button>
                        <button type="button" class="btn btn-sm text-white px-3" style="background-color: {{ $settings['color_primary'] }};">
                            Primary Button
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @elseif($activeTab === 'branding')
        <!-- ==================== PANE 2: BRANDING & LOGO ==================== -->
        <div class="card card-solid p-4 mb-4">
            <h5 class="font-serif text-white fw-bold mb-1"><i class="bi bi-shield-check text-gold me-2"></i> Academy Branding & Logo</h5>
            <p class="text-muted small mb-4">Control how your music academy name, logo, favicon, and brand identity appear across all headers, footers, and meta tags.</p>

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Academy / Website Name</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="site_name" value="{{ $settings['site_name'] }}" required>
                    <small class="text-muted">Displayed in the navbar, footer, and emails (e.g. HARMONIA).</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Academy Tagline</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="site_tagline" value="{{ $settings['site_tagline'] }}">
                    <small class="text-muted">Displayed in header badges and page title tags.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Navbar Logo Format</label>
                    <select class="form-select bg-surface text-white border-secondary" name="site_logo_type">
                        <option value="icon" {{ $settings['site_logo_type'] === 'icon' ? 'selected' : '' }}>Styled Text with Music Badge Icon</option>
                        <option value="image" {{ $settings['site_logo_type'] === 'image' ? 'selected' : '' }}>Custom Uploaded Logo Image</option>
                    </select>
                    <small class="text-muted">Choose whether to render custom image graphic or styled typography brand.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Badge Icon (Bootstrap Icons)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-surface-elevated text-gold border-secondary"><i class="bi {{ $settings['site_logo_icon'] ?: 'bi-music-note-beamed' }}"></i></span>
                        <input type="text" class="form-control bg-surface text-white border-secondary" name="site_logo_icon" value="{{ $settings['site_logo_icon'] }}" placeholder="bi-music-note-beamed">
                    </div>
                    <small class="text-muted">e.g. <code>bi-music-note-beamed</code>, <code>bi-vinyl-fill</code>, <code>bi-soundwave</code>, <code>bi-award-fill</code>.</small>
                </div>

                <!-- Custom Logo Upload -->
                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Upload Custom Academy Logo</label>
                    <input type="file" class="form-control bg-surface text-white border-secondary image-preview-input" name="site_logo_image_file" accept="image/*">
                    <small class="text-muted">PNG, SVG, or WEBP transparent graphic recommended (max 3MB).</small>

                    @if(!empty($settings['site_logo_image']))
                        <div class="mt-2 p-2 rounded-2 bg-surface-elevated border border-secondary d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset($settings['site_logo_image']) }}" alt="Current Logo" height="32" class="rounded">
                                <small class="text-muted">Current Logo Active</small>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_logo_image" value="1" id="removeLogo">
                                <label class="form-check-label text-danger small" for="removeLogo">Remove</label>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Custom Favicon Upload -->
                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Upload Custom Favicon</label>
                    <input type="file" class="form-control bg-surface text-white border-secondary image-preview-input" name="site_favicon_file" accept=".ico,.png,.svg,.webp,image/*">
                    <small class="text-muted">.ico, .png, or .svg icon displayed in browser tabs (max 1MB).</small>

                    @if(!empty($settings['site_favicon']))
                        <div class="mt-2 p-2 rounded-2 bg-surface-elevated border border-secondary d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset($settings['site_favicon']) }}" alt="Current Favicon" width="24" height="24">
                                <small class="text-muted">Custom Favicon Active</small>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_favicon" value="1" id="removeFavicon">
                                <label class="form-check-label text-danger small" for="removeFavicon">Remove</label>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-12">
                    <label class="form-label text-white small fw-bold">Global SEO Meta Description</label>
                    <textarea class="form-control bg-surface text-white border-secondary" rows="2" name="meta_description">{{ $settings['meta_description'] }}</textarea>
                    <small class="text-muted">Search engine synopsis shown when sharing academy links.</small>
                </div>
            </div>
        </div>

    @elseif($activeTab === 'navigation')
        <!-- ==================== PANE 3: PUBLIC SITE HEADER & NAVIGATION ==================== -->
        <div class="card card-solid p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="font-serif text-white fw-bold mb-1"><i class="bi bi-menu-button-wide text-gold me-2"></i> Public Site Header & Navigation Menus</h5>
                    <p class="text-muted small mb-0">Customize menu labels, destination URLs, and toggle visibility for links displayed in the public header.</p>
                </div>
                <span class="badge bg-surface-elevated text-gold border border-secondary px-3 py-2">
                    <i class="bi bi-eye-fill me-1"></i> Real-Time Public Header Config
                </span>
            </div>

            <!-- Table of Header Navigation Links -->
            <div class="table-responsive rounded-3 border border-secondary mb-4">
                <table class="table table-dark table-hover align-middle mb-0" style="background-color: var(--bg-surface);">
                    <thead style="background-color: var(--bg-surface-elevated);">
                        <tr>
                            <th class="text-white py-3 ps-3" style="width: 20%;">Menu Item</th>
                            <th class="text-white py-3" style="width: 35%;">Display Label / Title</th>
                            <th class="text-white py-3" style="width: 30%;">Target URL / Path</th>
                            <th class="text-white py-3 text-center" style="width: 15%;">Visible</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 1. Home Link -->
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-house-door-fill text-gold"></i>
                                    <div>
                                        <div class="fw-bold text-white small">Home</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Default: /</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm bg-surface text-white border-secondary" name="nav_home_label" value="{{ $settings['nav_home_label'] ?? 'Home' }}" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm bg-surface text-white border-secondary font-monospace" name="nav_home_url" value="{{ $settings['nav_home_url'] ?? '/' }}" required>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input" type="checkbox" name="nav_home_enabled" value="1" id="nav_home_enabled" {{ ($settings['nav_home_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </td>
                        </tr>

                        <!-- 2. About Us Link -->
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-bank2 text-gold"></i>
                                    <div>
                                        <div class="fw-bold text-white small">About Us</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Default: /about</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm bg-surface text-white border-secondary" name="nav_about_label" value="{{ $settings['nav_about_label'] ?? 'About Us' }}" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm bg-surface text-white border-secondary font-monospace" name="nav_about_url" value="{{ $settings['nav_about_url'] ?? '/about' }}" required>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input" type="checkbox" name="nav_about_enabled" value="1" id="nav_about_enabled" {{ ($settings['nav_about_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </td>
                        </tr>

                        <!-- 3. Courses Link -->
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-collection-play-fill text-gold"></i>
                                    <div>
                                        <div class="fw-bold text-white small">Courses</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Default: /courses</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm bg-surface text-white border-secondary" name="nav_courses_label" value="{{ $settings['nav_courses_label'] ?? 'Courses' }}" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm bg-surface text-white border-secondary font-monospace" name="nav_courses_url" value="{{ $settings['nav_courses_url'] ?? '/courses' }}" required>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input" type="checkbox" name="nav_courses_enabled" value="1" id="nav_courses_enabled" {{ ($settings['nav_courses_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </td>
                        </tr>

                        <!-- 4. Blog / Journal Link -->
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-journal-text text-gold"></i>
                                    <div>
                                        <div class="fw-bold text-white small">Journal / Blog</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Default: /blog</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm bg-surface text-white border-secondary" name="nav_blog_label" value="{{ $settings['nav_blog_label'] ?? 'Journal' }}" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm bg-surface text-white border-secondary font-monospace" name="nav_blog_url" value="{{ $settings['nav_blog_url'] ?? '/blog' }}" required>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input" type="checkbox" name="nav_blog_enabled" value="1" id="nav_blog_enabled" {{ ($settings['nav_blog_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </td>
                        </tr>

                        <!-- 5. Verify Certificate Link -->
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-award-fill text-gold"></i>
                                    <div>
                                        <div class="fw-bold text-white small">Verify Certificate</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Default: /verify-certificate</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm bg-surface text-white border-secondary" name="nav_verify_label" value="{{ $settings['nav_verify_label'] ?? 'Verify Certificate' }}" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm bg-surface text-white border-secondary font-monospace" name="nav_verify_url" value="{{ $settings['nav_verify_url'] ?? '/verify-certificate' }}" required>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input" type="checkbox" name="nav_verify_enabled" value="1" id="nav_verify_enabled" {{ ($settings['nav_verify_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Auth & Call to Action (CTA) Buttons -->
            <div class="p-3 rounded-3 bg-surface-elevated border border-secondary">
                <h6 class="text-gold fw-bold mb-3"><i class="bi bi-box-arrow-in-right me-2"></i> Header Action Buttons (Sign In & Join)</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-white small fw-bold">Sign In Link Text</label>
                        <input type="text" class="form-control bg-surface text-white border-secondary" name="nav_login_label" value="{{ $settings['nav_login_label'] ?? 'Log In' }}">
                        <small class="text-muted">Directs guest visitors to the login page.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white small fw-bold">Primary CTA Button Text</label>
                        <input type="text" class="form-control bg-surface text-white border-secondary" name="nav_cta_label" value="{{ $settings['nav_cta_label'] ?? 'Join Academy' }}">
                        <small class="text-muted">Prominent highlighted button in the navbar.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white small fw-bold">Primary CTA Button URL</label>
                        <input type="text" class="form-control bg-surface text-white border-secondary font-monospace" name="nav_cta_url" value="{{ $settings['nav_cta_url'] ?? '/register' }}">
                        <small class="text-muted">e.g. <code>/register</code> or <code>/courses</code>.</small>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="nav_cta_enabled" value="1" id="nav_cta_enabled" {{ ($settings['nav_cta_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label text-white small fw-semibold" for="nav_cta_enabled">Display the Primary CTA Button on the public header navbar</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @elseif($activeTab === 'hero')
        <!-- ==================== PANE 4: HERO SECTION ==================== -->
        <div class="card card-solid p-4 mb-4">
            <h5 class="font-serif text-white fw-bold mb-1"><i class="bi bi-megaphone-fill text-gold me-2"></i> Landing Page: Hero Section</h5>
            <p class="text-muted small mb-4">Customize the marquee hero section that first-time visitors see on the landing page.</p>

            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label text-white small fw-bold">Hero Top Badge Text</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="hero_badge" value="{{ $settings['hero_badge'] }}">
                    <small class="text-muted">Appears inside the glowing pill above the main headline.</small>
                </div>

                <div class="col-12">
                    <label class="form-label text-white small fw-bold">Hero Main Headline</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="hero_title" value="{{ $settings['hero_title'] }}" required>
                    <small class="text-muted">E.g. <code>Master Your Instrument with Virtuoso Instruction.</code></small>
                </div>

                <div class="col-12">
                    <label class="form-label text-white small fw-bold">Hero Sub-headline / Descriptive Lead</label>
                    <textarea class="form-control bg-surface text-white border-secondary" rows="3" name="hero_subtitle">{{ $settings['hero_subtitle'] }}</textarea>
                    <small class="text-muted">Compelling 1-2 sentence overview of the academy conservatory program.</small>
                </div>

                <!-- CTA Buttons -->
                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Primary Action Button Label</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="hero_cta_primary_text" value="{{ $settings['hero_cta_primary_text'] }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Primary Action Button Link (URL)</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="hero_cta_primary_link" value="{{ $settings['hero_cta_primary_link'] }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Secondary Action Button Label</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="hero_cta_secondary_text" value="{{ $settings['hero_cta_secondary_text'] }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Secondary Action Button Link (URL)</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="hero_cta_secondary_link" value="{{ $settings['hero_cta_secondary_link'] }}">
                </div>

                <!-- Academy Stats Section -->
                <div class="col-12">
                    <hr class="border-secondary my-3">
                    <h6 class="text-gold fw-bold mb-2">Academy Metric Counters Bar</h6>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Metric Calculation Mode</label>
                    <select class="form-select bg-surface text-white border-secondary" name="hero_stats_mode">
                        <option value="auto" {{ $settings['hero_stats_mode'] === 'auto' ? 'selected' : '' }}>Automatic (Live Database Totals: Students, Courses, Faculty, Certificates)</option>
                        <option value="custom" {{ $settings['hero_stats_mode'] === 'custom' ? 'selected' : '' }}>Custom Numbers / Marketing Figures (Specified Below)</option>
                    </select>
                    <small class="text-muted">Choose whether to display live database counts or custom marketing numbers.</small>
                </div>

                <div class="col-md-6">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label text-white small fw-semibold">Custom Students</label>
                            <input type="text" class="form-control bg-surface text-white border-secondary" name="hero_stat_students" value="{{ $settings['hero_stat_students'] }}" placeholder="450+">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-white small fw-semibold">Custom Courses</label>
                            <input type="text" class="form-control bg-surface text-white border-secondary" name="hero_stat_courses" value="{{ $settings['hero_stat_courses'] }}" placeholder="24">
                        </div>
                        <div class="col-6 mt-2">
                            <label class="form-label text-white small fw-semibold">Custom Faculty</label>
                            <input type="text" class="form-control bg-surface text-white border-secondary" name="hero_stat_faculty" value="{{ $settings['hero_stat_faculty'] }}" placeholder="18">
                        </div>
                        <div class="col-6 mt-2">
                            <label class="form-label text-white small fw-semibold">Custom Certificates</label>
                            <input type="text" class="form-control bg-surface text-white border-secondary" name="hero_stat_certificates" value="{{ $settings['hero_stat_certificates'] }}" placeholder="320+">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @elseif($activeTab === 'sections')
        <!-- ==================== PANE 5: LANDING SECTIONS & METHODOLOGY ==================== -->
        <div class="card card-solid p-4 mb-4">
            <h5 class="font-serif text-white fw-bold mb-1"><i class="bi bi-layout-text-window-reverse text-gold me-2"></i> Landing Page Content Sections</h5>
            <p class="text-muted small mb-4">Edit the titles, descriptions, and feature pillars on the landing page.</p>

            <!-- Disciplines Section -->
            <div class="p-3 rounded-3 bg-surface-elevated border border-secondary mb-4">
                <h6 class="text-gold fw-bold mb-3"><i class="bi bi-music-player me-1"></i> Disciplines / Browse by Instrument Header</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-white small fw-bold">Subtitle Badge</label>
                        <input type="text" class="form-control bg-surface text-white border-secondary" name="disciplines_subtitle" value="{{ $settings['disciplines_subtitle'] }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label text-white small fw-bold">Section Heading</label>
                        <input type="text" class="form-control bg-surface text-white border-secondary" name="disciplines_title" value="{{ $settings['disciplines_title'] }}">
                    </div>
                </div>
            </div>

            <!-- Featured Repertoire Section -->
            <div class="p-3 rounded-3 bg-surface-elevated border border-secondary mb-4">
                <h6 class="text-gold fw-bold mb-3"><i class="bi bi-collection-play me-1"></i> Curated Masterclasses Header</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-white small fw-bold">Subtitle Badge</label>
                        <input type="text" class="form-control bg-surface text-white border-secondary" name="featured_courses_subtitle" value="{{ $settings['featured_courses_subtitle'] }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label text-white small fw-bold">Section Heading</label>
                        <input type="text" class="form-control bg-surface text-white border-secondary" name="featured_courses_title" value="{{ $settings['featured_courses_title'] }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label text-white small fw-bold">Section Description</label>
                        <input type="text" class="form-control bg-surface text-white border-secondary" name="featured_courses_desc" value="{{ $settings['featured_courses_desc'] }}">
                    </div>
                </div>
            </div>

            <!-- Conservatory Advantage / 4 Pillars -->
            <div class="p-3 rounded-3 bg-surface-elevated border border-secondary mb-4">
                <h6 class="text-gold fw-bold mb-1"><i class="bi bi-award me-1"></i> Academy Methodology & 4 Feature Pillars</h6>
                <p class="text-muted small mb-3">Highlight the educational foundations that make your academy elite.</p>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label text-white small fw-bold">Methodology Badge</label>
                        <input type="text" class="form-control bg-surface text-white border-secondary" name="methodology_subtitle" value="{{ $settings['methodology_subtitle'] }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label text-white small fw-bold">Methodology Heading</label>
                        <input type="text" class="form-control bg-surface text-white border-secondary" name="methodology_title" value="{{ $settings['methodology_title'] }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label text-white small fw-bold">Methodology Description</label>
                        <input type="text" class="form-control bg-surface text-white border-secondary" name="methodology_desc" value="{{ $settings['methodology_desc'] }}">
                    </div>
                </div>

                <div class="row g-3">
                    <!-- Pillar 1 -->
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 rounded-2 bg-surface border border-secondary h-100">
                            <span class="badge bg-gold text-dark mb-2">Pillar 1</span>
                            <div class="mb-2">
                                <label class="form-label text-white small fw-bold">Icon</label>
                                <input type="text" class="form-control form-control-sm bg-surface-elevated text-white border-secondary" name="pillar1_icon" value="{{ $settings['pillar1_icon'] }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label text-white small fw-bold">Title</label>
                                <input type="text" class="form-control form-control-sm bg-surface-elevated text-white border-secondary" name="pillar1_title" value="{{ $settings['pillar1_title'] }}">
                            </div>
                            <div>
                                <label class="form-label text-white small fw-bold">Description</label>
                                <textarea class="form-control form-control-sm bg-surface-elevated text-white border-secondary" rows="2" name="pillar1_desc">{{ $settings['pillar1_desc'] }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Pillar 2 -->
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 rounded-2 bg-surface border border-secondary h-100">
                            <span class="badge bg-gold text-dark mb-2">Pillar 2</span>
                            <div class="mb-2">
                                <label class="form-label text-white small fw-bold">Icon</label>
                                <input type="text" class="form-control form-control-sm bg-surface-elevated text-white border-secondary" name="pillar2_icon" value="{{ $settings['pillar2_icon'] }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label text-white small fw-bold">Title</label>
                                <input type="text" class="form-control form-control-sm bg-surface-elevated text-white border-secondary" name="pillar2_title" value="{{ $settings['pillar2_title'] }}">
                            </div>
                            <div>
                                <label class="form-label text-white small fw-bold">Description</label>
                                <textarea class="form-control form-control-sm bg-surface-elevated text-white border-secondary" rows="2" name="pillar2_desc">{{ $settings['pillar2_desc'] }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Pillar 3 -->
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 rounded-2 bg-surface border border-secondary h-100">
                            <span class="badge bg-gold text-dark mb-2">Pillar 3</span>
                            <div class="mb-2">
                                <label class="form-label text-white small fw-bold">Icon</label>
                                <input type="text" class="form-control form-control-sm bg-surface-elevated text-white border-secondary" name="pillar3_icon" value="{{ $settings['pillar3_icon'] }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label text-white small fw-bold">Title</label>
                                <input type="text" class="form-control form-control-sm bg-surface-elevated text-white border-secondary" name="pillar3_title" value="{{ $settings['pillar3_title'] }}">
                            </div>
                            <div>
                                <label class="form-label text-white small fw-bold">Description</label>
                                <textarea class="form-control form-control-sm bg-surface-elevated text-white border-secondary" rows="2" name="pillar3_desc">{{ $settings['pillar3_desc'] }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Pillar 4 -->
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 rounded-2 bg-surface border border-secondary h-100">
                            <span class="badge bg-gold text-dark mb-2">Pillar 4</span>
                            <div class="mb-2">
                                <label class="form-label text-white small fw-bold">Icon</label>
                                <input type="text" class="form-control form-control-sm bg-surface-elevated text-white border-secondary" name="pillar4_icon" value="{{ $settings['pillar4_icon'] }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label text-white small fw-bold">Title</label>
                                <input type="text" class="form-control form-control-sm bg-surface-elevated text-white border-secondary" name="pillar4_title" value="{{ $settings['pillar4_title'] }}">
                            </div>
                            <div>
                                <label class="form-label text-white small fw-bold">Description</label>
                                <textarea class="form-control form-control-sm bg-surface-elevated text-white border-secondary" rows="2" name="pillar4_desc">{{ $settings['pillar4_desc'] }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call to Action Banner -->
            <div class="p-3 rounded-3 bg-surface-elevated border border-secondary">
                <h6 class="text-gold fw-bold mb-3"><i class="bi bi-bell-fill me-1"></i> Pre-Footer Call to Action Banner</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-white small fw-bold">Banner Headline</label>
                        <input type="text" class="form-control bg-surface text-white border-secondary" name="cta_banner_title" value="{{ $settings['cta_banner_title'] }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white small fw-bold">Banner Description</label>
                        <input type="text" class="form-control bg-surface text-white border-secondary" name="cta_banner_desc" value="{{ $settings['cta_banner_desc'] }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white small fw-bold">Button 1 Label & Link</label>
                        <div class="input-group">
                            <input type="text" class="form-control bg-surface text-white border-secondary" name="cta_banner_btn1_text" value="{{ $settings['cta_banner_btn1_text'] }}">
                            <input type="text" class="form-control bg-surface text-white border-secondary" name="cta_banner_btn1_link" value="{{ $settings['cta_banner_btn1_link'] }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white small fw-bold">Button 2 Label & Link</label>
                        <div class="input-group">
                            <input type="text" class="form-control bg-surface text-white border-secondary" name="cta_banner_btn2_text" value="{{ $settings['cta_banner_btn2_text'] }}">
                            <input type="text" class="form-control bg-surface text-white border-secondary" name="cta_banner_btn2_link" value="{{ $settings['cta_banner_btn2_link'] }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @elseif($activeTab === 'about')
        <!-- ==================== PANE 6: ABOUT US PAGE ==================== -->
        <!-- Header & Key Stats -->
        <div class="card card-solid p-4 mb-4">
            <h5 class="font-serif text-white fw-bold mb-1"><i class="bi bi-bank2 text-gold me-2"></i> About Us: Marquee Header & Academy Statistics</h5>
            <p class="text-muted small mb-4">Headline banner and key institutional stats showcased at the top of the dynamic <code>/about</code> conservatory page.</p>

            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label text-white small fw-bold">Header Badge</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="about_hero_badge" value="{{ $settings['about_hero_badge'] ?? 'Conservatory Heritage' }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label text-white small fw-bold">Hero Title</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="about_hero_title" value="{{ $settings['about_hero_title'] ?? 'A Century of Virtuosity & Academic Distinction.' }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label text-white small fw-bold">Hero Subtitle</label>
                    <textarea class="form-control bg-surface text-white border-secondary" rows="2" name="about_hero_subtitle">{{ $settings['about_hero_subtitle'] ?? '' }}</textarea>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white small fw-bold">Stat: Year Founded</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="about_stat_founded" value="{{ $settings['about_stat_founded'] ?? '1998' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white small fw-bold">Stat: Alumni / Graduates</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="about_stat_graduates" value="{{ $settings['about_stat_graduates'] ?? '3,400+' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white small fw-bold">Stat: Masterclasses Held</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="about_stat_masterclasses" value="{{ $settings['about_stat_masterclasses'] ?? '120+' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white small fw-bold">Stat: Countries Represented</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="about_stat_countries" value="{{ $settings['about_stat_countries'] ?? '42' }}">
                </div>
            </div>
        </div>

        <!-- Heritage & Story -->
        <div class="card card-solid p-4 mb-4">
            <h5 class="font-serif text-white fw-bold mb-1"><i class="bi bi-clock-history text-gold me-2"></i> Academy Heritage & Origin Story</h5>
            <p class="text-muted small mb-4">Detailed narrative of the conservatory's founding, artistic heritage, and pedagogy. Use the rich text toolbar to upload inline images anywhere within the narrative.</p>

            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label text-white small fw-bold">Story Subtitle</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="about_story_subtitle" value="{{ $settings['about_story_subtitle'] ?? 'OUR HERITAGE & ORIGINS' }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label text-white small fw-bold">Story Title</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="about_story_title" value="{{ $settings['about_story_title'] ?? 'Our Academy Heritage' }}">
                </div>
                <div class="col-12">
                    <label class="form-label text-white small fw-bold">Story Narrative (Rich Text with Inline Image Upload)</label>
                    <textarea class="form-control bg-surface text-white border-secondary richtext" name="about_story_content" rows="6">{!! $settings['about_story_content'] ?? '' !!}</textarea>
                </div>
            </div>
        </div>

        <!-- Artistic Mission & Dean's Welcome Letter -->
        <div class="card card-solid p-4 mb-4">
            <h5 class="font-serif text-white fw-bold mb-1"><i class="bi bi-chat-quote-fill text-gold me-2"></i> Mission, Guiding Quote & Dean's Letter</h5>
            <p class="text-muted small mb-4">Publish the artistic manifesto, inspirational motto, and official welcome address from the Conservatory Dean.</p>

            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label text-white small fw-bold">Mission Statement Title</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="about_mission_title" value="{{ $settings['about_mission_title'] ?? 'Artistic Mission & Pedagogical Vision' }}">
                </div>
                <div class="col-12">
                    <label class="form-label text-white small fw-bold">Mission Statement Content (Rich Text with Inline Image Upload)</label>
                    <textarea class="form-control bg-surface text-white border-secondary richtext" name="about_mission_content" rows="5">{!! $settings['about_mission_content'] ?? '' !!}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label text-white small fw-bold">Dean's Featured Inspirational Quote</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="about_dean_quote" value="{{ $settings['about_dean_quote'] ?? '' }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Dean's Name</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="about_dean_name" value="{{ $settings['about_dean_name'] ?? 'Prof. Franz Liszt' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Dean's Official Title / Instrument</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="about_dean_title" value="{{ $settings['about_dean_title'] ?? 'General Director & Dean of Faculty' }}">
                </div>
                <div class="col-12">
                    <label class="form-label text-white small fw-bold">Dean's Welcome Address (Rich Text with Inline Image Upload)</label>
                    <textarea class="form-control bg-surface text-white border-secondary richtext" name="about_dean_letter" rows="5">{!! $settings['about_dean_letter'] ?? '' !!}</textarea>
                </div>
            </div>
        </div>

        <!-- Core Values / 4 Pillars -->
        <div class="card card-solid p-4 mb-4">
            <h5 class="font-serif text-white fw-bold mb-1"><i class="bi bi-gem text-gold me-2"></i> Four Pillars of Excellence</h5>
            <p class="text-muted small mb-4">The core philosophical tenets that guide students and virtuoso faculty.</p>

            <div class="row g-4">
                <!-- Value 1 -->
                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-surface-elevated border border-secondary h-100">
                        <span class="badge bg-gold text-dark mb-2">Pillar 1</span>
                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Title</label>
                            <input type="text" class="form-control bg-surface text-white border-secondary" name="about_val1_title" value="{{ $settings['about_val1_title'] ?? 'Virtuoso Discipline' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Icon Class</label>
                            <input type="text" class="form-control bg-surface text-white border-secondary font-monospace" name="about_val1_icon" value="{{ $settings['about_val1_icon'] ?? 'bi-trophy-fill' }}">
                        </div>
                        <div>
                            <label class="form-label text-white small fw-bold">Description</label>
                            <textarea class="form-control bg-surface text-white border-secondary" rows="2" name="about_val1_desc">{{ $settings['about_val1_desc'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Value 2 -->
                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-surface-elevated border border-secondary h-100">
                        <span class="badge bg-gold text-dark mb-2">Pillar 2</span>
                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Title</label>
                            <input type="text" class="form-control bg-surface text-white border-secondary" name="about_val2_title" value="{{ $settings['about_val2_title'] ?? 'Urtext Fidelity' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Icon Class</label>
                            <input type="text" class="form-control bg-surface text-white border-secondary font-monospace" name="about_val2_icon" value="{{ $settings['about_val2_icon'] ?? 'bi-book-half' }}">
                        </div>
                        <div>
                            <label class="form-label text-white small fw-bold">Description</label>
                            <textarea class="form-control bg-surface text-white border-secondary" rows="2" name="about_val2_desc">{{ $settings['about_val2_desc'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Value 3 -->
                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-surface-elevated border border-secondary h-100">
                        <span class="badge bg-gold text-dark mb-2">Pillar 3</span>
                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Title</label>
                            <input type="text" class="form-control bg-surface text-white border-secondary" name="about_val3_title" value="{{ $settings['about_val3_title'] ?? 'Constructive Critique' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Icon Class</label>
                            <input type="text" class="form-control bg-surface text-white border-secondary font-monospace" name="about_val3_icon" value="{{ $settings['about_val3_icon'] ?? 'bi-soundwave' }}">
                        </div>
                        <div>
                            <label class="form-label text-white small fw-bold">Description</label>
                            <textarea class="form-control bg-surface text-white border-secondary" rows="2" name="about_val3_desc">{{ $settings['about_val3_desc'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Value 4 -->
                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-surface-elevated border border-secondary h-100">
                        <span class="badge bg-gold text-dark mb-2">Pillar 4</span>
                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Title</label>
                            <input type="text" class="form-control bg-surface text-white border-secondary" name="about_val4_title" value="{{ $settings['about_val4_title'] ?? 'Global Concert Stage' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white small fw-bold">Icon Class</label>
                            <input type="text" class="form-control bg-surface text-white border-secondary font-monospace" name="about_val4_icon" value="{{ $settings['about_val4_icon'] ?? 'bi-globe-americas' }}">
                        </div>
                        <div>
                            <label class="form-label text-white small fw-bold">Description</label>
                            <textarea class="form-control bg-surface text-white border-secondary" rows="2" name="about_val4_desc">{{ $settings['about_val4_desc'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @elseif($activeTab === 'student')
        <!-- ==================== PANE 7: STUDENT PORTAL ==================== -->
        <div class="card card-solid p-4 mb-4">
            <h5 class="font-serif text-white fw-bold mb-1"><i class="bi bi-mortarboard-fill text-gold me-2"></i> Student Portal Customization</h5>
            <p class="text-muted small mb-4">Configure the headlines, welcome messages, practice tips, and global academy broadcast banners for student accounts.</p>

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Student Dashboard Marquee Badge</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="student_portal_title" value="{{ $settings['student_portal_title'] }}">
                    <small class="text-muted">E.g. <code>Conservatory Virtual Studio</code>.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Student Subtitle / Welcome Motto</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="student_welcome_sub" value="{{ $settings['student_welcome_sub'] }}">
                    <small class="text-muted">Displayed under the "Welcome back, {Name}" heading.</small>
                </div>

                <!-- Student Practice Tip -->
                <div class="col-12">
                    <label class="form-label text-white small fw-bold">Virtuoso Practice Tip of the Day</label>
                    <textarea class="form-control bg-surface text-white border-secondary" rows="2" name="student_practice_tip">{{ $settings['student_practice_tip'] }}</textarea>
                    <small class="text-muted">Inspiring pedagogical advice displayed in the student study panel.</small>
                </div>

                <!-- Global Student Announcement Banner -->
                <div class="col-12">
                    <div class="p-3 rounded-3 bg-surface-elevated border border-secondary">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="student_announcement_enabled" name="student_announcement_enabled" value="1" {{ $settings['student_announcement_enabled'] == '1' ? 'checked' : '' }}>
                            <label class="form-check-label text-white fw-bold" for="student_announcement_enabled">
                                Display Prominent Academy Alert Banner on Student Dashboard
                            </label>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-white small fw-bold">Alert Style Tone</label>
                                <select class="form-select bg-surface text-white border-secondary" name="student_announcement_type">
                                    <option value="info" {{ $settings['student_announcement_type'] === 'info' ? 'selected' : '' }}>Blue / Info (General Notice)</option>
                                    <option value="warning" {{ $settings['student_announcement_type'] === 'warning' ? 'selected' : '' }}>Gold / Warning (Audition / Important)</option>
                                    <option value="primary" {{ $settings['student_announcement_type'] === 'primary' ? 'selected' : '' }}>Indigo / Primary (Special Event)</option>
                                    <option value="success" {{ $settings['student_announcement_type'] === 'success' ? 'selected' : '' }}>Green / Success (Celebration / Graduation)</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label text-white small fw-bold">Announcement Header</label>
                                <input type="text" class="form-control bg-surface text-white border-secondary" name="student_announcement_title" value="{{ $settings['student_announcement_title'] }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label text-white small fw-bold">Announcement Message</label>
                                <textarea class="form-control bg-surface text-white border-secondary" rows="2" name="student_announcement_text">{{ $settings['student_announcement_text'] }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @elseif($activeTab === 'instructor')
        <!-- ==================== PANE 8: INSTRUCTOR PORTAL ==================== -->
        <div class="card card-solid p-4 mb-4">
            <h5 class="font-serif text-white fw-bold mb-1"><i class="bi bi-music-player-fill text-gold me-2"></i> Instructor Portal Customization</h5>
            <p class="text-muted small mb-4">Customize the faculty workspace title, greeting, grading guidelines, and broadcast directives.</p>

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Faculty Studio Header Title</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="instructor_portal_title" value="{{ $settings['instructor_portal_title'] }}">
                    <small class="text-muted">E.g. <code>Faculty Studio Overview</code>.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-white small fw-bold">Faculty Greeting Subtitle</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="instructor_welcome_sub" value="{{ $settings['instructor_welcome_sub'] }}">
                    <small class="text-muted">Subtitle displayed below faculty name.</small>
                </div>

                <div class="col-12">
                    <label class="form-label text-white small fw-bold">Faculty Audio & Score Guidelines Notice</label>
                    <textarea class="form-control bg-surface text-white border-secondary" rows="2" name="instructor_guidelines">{{ $settings['instructor_guidelines'] }}</textarea>
                    <small class="text-muted">Displayed in the course & submission management area.</small>
                </div>

                <!-- Global Instructor Directive Banner -->
                <div class="col-12">
                    <div class="p-3 rounded-3 bg-surface-elevated border border-secondary">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="instructor_notice_enabled" name="instructor_notice_enabled" value="1" {{ $settings['instructor_notice_enabled'] == '1' ? 'checked' : '' }}>
                            <label class="form-check-label text-white fw-bold" for="instructor_notice_enabled">
                                Display Faculty Directive Banner on Instructor Dashboard
                            </label>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-white small fw-bold">Directive Style Tone</label>
                                <select class="form-select bg-surface text-white border-secondary" name="instructor_notice_type">
                                    <option value="warning" {{ $settings['instructor_notice_type'] === 'warning' ? 'selected' : '' }}>Gold / Warning (Grading Deadline)</option>
                                    <option value="info" {{ $settings['instructor_notice_type'] === 'info' ? 'selected' : '' }}>Blue / Info (General Faculty Memo)</option>
                                    <option value="primary" {{ $settings['instructor_notice_type'] === 'primary' ? 'selected' : '' }}>Indigo / Primary (Department Meeting)</option>
                                    <option value="success" {{ $settings['instructor_notice_type'] === 'success' ? 'selected' : '' }}>Green / Success (Term Completion)</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label text-white small fw-bold">Directive Header</label>
                                <input type="text" class="form-control bg-surface text-white border-secondary" name="instructor_notice_title" value="{{ $settings['instructor_notice_title'] }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label text-white small fw-bold">Directive Message</label>
                                <textarea class="form-control bg-surface text-white border-secondary" rows="2" name="instructor_notice_text">{{ $settings['instructor_notice_text'] }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @elseif($activeTab === 'footer')
        <!-- ==================== PANE 9: CONTACT & FOOTER ==================== -->
        <div class="card card-solid p-4 mb-4">
            <h5 class="font-serif text-white fw-bold mb-1"><i class="bi bi-geo-alt-fill text-gold me-2"></i> Contact Details & Academy Footer</h5>
            <p class="text-muted small mb-4">Update contact information, physical academy location, telephone, social channels, and copyright notice.</p>

            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label text-white small fw-bold">Footer Academy Bio / About Blurb</label>
                    <textarea class="form-control bg-surface text-white border-secondary" rows="2" name="footer_about">{{ $settings['footer_about'] }}</textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-white small fw-bold">Auditions / Office Address</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="contact_address" value="{{ $settings['contact_address'] }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label text-white small fw-bold">Admissions Email Address</label>
                    <input type="email" class="form-control bg-surface text-white border-secondary" name="contact_email" value="{{ $settings['contact_email'] }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label text-white small fw-bold">Admissions Hotline Phone</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="contact_phone" value="{{ $settings['contact_phone'] }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white small fw-bold"><i class="bi bi-youtube text-danger me-1"></i> YouTube URL</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="social_youtube" value="{{ $settings['social_youtube'] }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white small fw-bold"><i class="bi bi-instagram text-warning me-1"></i> Instagram URL</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="social_instagram" value="{{ $settings['social_instagram'] }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white small fw-bold"><i class="bi bi-spotify text-success me-1"></i> Spotify URL</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="social_spotify" value="{{ $settings['social_spotify'] }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white small fw-bold"><i class="bi bi-discord text-primary me-1"></i> Discord URL</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="social_discord" value="{{ $settings['social_discord'] }}">
                </div>

                <div class="col-12">
                    <label class="form-label text-white small fw-bold">Footer Copyright Notice</label>
                    <input type="text" class="form-control bg-surface text-white border-secondary" name="footer_copyright" value="{{ $settings['footer_copyright'] }}">
                    <small class="text-muted">The current year is automatically prefixed.</small>
                </div>
            </div>
        </div>
    @endif

    <!-- Sticky Save Bar -->
    <div class="card card-solid p-3 rounded-3 shadow sticky-bottom border-gold d-flex flex-row justify-content-between align-items-center mt-4" style="background: rgba(17, 24, 39, 0.95); backdrop-filter: blur(8px); z-index: 1020;">
        <div class="text-muted small">
            <i class="bi bi-info-circle text-gold me-1"></i> Saving will immediately update <strong>{{ $currentTab['title'] }}</strong> across the platform.
        </div>
        <button type="submit" class="btn btn-gold px-4 py-2 fw-bold">
            <i class="bi bi-check2-circle me-1"></i> Save {{ $currentTab['title'] }}
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Synchronize color picker to text field
    function syncColor(pickerId, textId) {
        const picker = document.getElementById(pickerId);
        const text = document.getElementById(textId);
        if (picker && text) {
            text.value = picker.value;
        }
    }

    // Synchronize text field to color picker
    function syncPicker(textId, pickerId) {
        const text = document.getElementById(textId);
        const picker = document.getElementById(pickerId);
        if (text && picker && /^#[0-9A-F]{6}$/i.test(text.value)) {
            picker.value = text.value;
        }
    }

    // Apply Preset Colors
    function applyPreset(accent, hover, primary, dark, surface, elevated) {
        const accentInput = document.getElementById('color_accent');
        const accentPicker = document.getElementById('color_accent_picker');
        if (accentInput) accentInput.value = accent;
        if (accentPicker) accentPicker.value = accent;

        const hoverInput = document.getElementById('color_accent_hover');
        const hoverPicker = document.getElementById('color_accent_hover_picker');
        if (hoverInput) hoverInput.value = hover;
        if (hoverPicker) hoverPicker.value = hover;

        const primaryInput = document.getElementById('color_primary');
        const primaryPicker = document.getElementById('color_primary_picker');
        if (primaryInput) primaryInput.value = primary;
        if (primaryPicker) primaryPicker.value = primary;

        const darkInput = document.getElementById('color_bg_dark');
        const darkPicker = document.getElementById('color_bg_dark_picker');
        if (darkInput) darkInput.value = dark;
        if (darkPicker) darkPicker.value = dark;

        const surfaceInput = document.getElementById('color_bg_surface');
        const surfacePicker = document.getElementById('color_bg_surface_picker');
        if (surfaceInput) surfaceInput.value = surface;
        if (surfacePicker) surfacePicker.value = surface;

        const elevatedInput = document.getElementById('color_bg_surface_elevated');
        const elevatedPicker = document.getElementById('color_bg_surface_elevated_picker');
        if (elevatedInput) elevatedInput.value = elevated;
        if (elevatedPicker) elevatedPicker.value = elevated;
    }
</script>
@endpush
