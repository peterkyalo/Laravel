<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', setting('site_name', 'BARITONE') . ' — ' . setting('site_tagline', 'Master the Art of Music'))</title>
    <meta name="description" content="{{ setting('meta_description') }}">

    <!-- Favicon -->
    @php $customFavicon = setting('site_favicon'); @endphp
    @if($customFavicon)
        <link rel="icon" href="{{ asset($customFavicon) }}">
        <link rel="apple-touch-icon" href="{{ asset($customFavicon) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="alternate icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Academy Design System -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=2.2">
    @include('layouts.partials.dynamic-styles')
    @stack('styles')
</head>
<body>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                @include('layouts.partials.brand-logo')
            </a>

            <button class="navbar-toggler text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <i class="bi bi-list fs-2 text-white"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    @if(setting('nav_home_enabled', '1') == '1')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ url(setting('nav_home_url', '/')) }}">
                                {{ setting('nav_home_label', 'Home') }}
                            </a>
                        </li>
                    @endif
                    @if(setting('nav_about_enabled', '1') == '1')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ url(setting('nav_about_url', '/about')) }}">
                                {{ setting('nav_about_label', 'About Us') }}
                            </a>
                        </li>
                    @endif
                    @if(setting('nav_courses_enabled', '1') == '1')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('courses.public.*') ? 'active' : '' }}" href="{{ url(setting('nav_courses_url', '/courses')) }}">
                                {{ setting('nav_courses_label', 'Courses') }}
                            </a>
                        </li>
                    @endif
                    @if(setting('nav_blog_enabled', '1') == '1')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}" href="{{ url(setting('nav_blog_url', '/blog')) }}">
                                {{ setting('nav_blog_label', 'Journal') }}
                            </a>
                        </li>
                    @endif
                    @if(setting('nav_verify_enabled', '1') == '1')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('certificates.verify') ? 'active' : '' }}" href="{{ url(setting('nav_verify_url', '/verify-certificate')) }}">
                                {{ setting('nav_verify_label', 'Verify Certificate') }}
                            </a>
                        </li>
                    @endif
                </ul>

                <ul class="navbar-nav align-items-lg-center gap-2">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-1"></i> {{ setting('nav_login_label', 'Log In') }}</a>
                        </li>
                        @if(setting('nav_cta_enabled', '1') == '1')
                            <li class="nav-item">
                                <a class="btn btn-gold btn-sm px-3" href="{{ url(setting('nav_cta_url', '/register')) }}">{{ setting('nav_cta_label', 'Join Academy') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item">
                            <a class="btn btn-outline-gold btn-sm me-2" href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                                <img src="{{ auth()->user()->avatarUrl() }}" alt="Avatar" class="rounded-circle" width="30" height="30" style="object-fit: cover;">
                                <span>{{ auth()->user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark bg-surface shadow border-secondary">
                                <li class="px-3 py-1 text-muted small">
                                    Signed in as <strong class="text-white text-capitalize">{{ auth()->user()->role }}</strong>
                                </li>
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-grid me-2 text-gold"></i> Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2 text-gold"></i> Profile Settings</a></li>
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button class="dropdown-item text-danger" type="submit">
                                            <i class="bi bi-box-arrow-right me-2"></i> Log Out
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show bg-success-subtle border-success text-success-emphasis" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show bg-info-subtle border-info text-info-emphasis" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show bg-danger-subtle border-danger text-danger-emphasis" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show bg-danger-subtle border-danger text-danger-emphasis" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i> Please check the form errors below:
                <ul class="mb-0 mt-1 ps-3 small">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <!-- Page Content -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto border-top py-5" style="background-color: #070a10; border-color: var(--border-color) !important;">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        @include('layouts.partials.brand-logo')
                    </div>
                    <p class="text-muted small">
                        {{ setting('footer_about', 'Premier online music academy delivering masterclasses, instrument lessons, theory training, and verified certifications worldwide.') }}
                    </p>
                    <div class="d-flex gap-3 text-gold fs-5">
                        @if(setting('social_youtube'))
                            <a href="{{ setting('social_youtube') }}" target="_blank" class="text-gold"><i class="bi bi-youtube"></i></a>
                        @endif
                        @if(setting('social_instagram'))
                            <a href="{{ setting('social_instagram') }}" target="_blank" class="text-gold"><i class="bi bi-instagram"></i></a>
                        @endif
                        @if(setting('social_spotify'))
                            <a href="{{ setting('social_spotify') }}" target="_blank" class="text-gold"><i class="bi bi-spotify"></i></a>
                        @endif
                        @if(setting('social_discord'))
                            <a href="{{ setting('social_discord') }}" target="_blank" class="text-gold"><i class="bi bi-discord"></i></a>
                        @endif
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="text-white fw-bold mb-3">Instruments</h6>
                    <ul class="list-unstyled text-muted small">
                        <li class="mb-2"><a href="{{ route('courses.public.index', ['instrument' => 'piano']) }}" class="text-muted text-decoration-none hover-gold">Piano & Keys</a></li>
                        <li class="mb-2"><a href="{{ route('courses.public.index', ['instrument' => 'acoustic-guitar']) }}" class="text-muted text-decoration-none hover-gold">Guitars</a></li>
                        <li class="mb-2"><a href="{{ route('courses.public.index', ['instrument' => 'violin']) }}" class="text-muted text-decoration-none hover-gold">Strings</a></li>
                        <li class="mb-2"><a href="{{ route('courses.public.index', ['instrument' => 'vocal']) }}" class="text-muted text-decoration-none hover-gold">Vocals & Choir</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="text-white fw-bold mb-3">Academy</h6>
                    <ul class="list-unstyled text-muted small">
                        <li class="mb-2"><a href="{{ route('about') }}" class="text-muted text-decoration-none hover-gold">About Us</a></li>
                        <li class="mb-2"><a href="{{ route('courses.public.index') }}" class="text-muted text-decoration-none hover-gold">Browse Catalog</a></li>
                        <li class="mb-2"><a href="{{ route('blog.index') }}" class="text-muted text-decoration-none hover-gold">Academy Journal</a></li>
                        <li class="mb-2"><a href="{{ route('certificates.verify') }}" class="text-muted text-decoration-none hover-gold">Verify Certificate</a></li>
                        <li class="mb-2"><a href="{{ route('register') }}" class="text-muted text-decoration-none hover-gold">Enroll Now</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-white fw-bold mb-3">Auditions & Inquiries</h6>
                    <p class="text-muted small mb-2"><i class="bi bi-geo-alt text-gold me-2"></i> {{ setting('contact_address', '440 Symphony Hall Way, Vienna & Online Worldwide') }}</p>
                    <p class="text-muted small mb-2"><i class="bi bi-envelope text-gold me-2"></i> {{ setting('contact_email', 'admissions@baritone-academy.test') }}</p>
                    <p class="text-muted small"><i class="bi bi-telephone text-gold me-2"></i> {{ setting('contact_phone', '+1 (800) 427-6664') }}</p>
                </div>
            </div>
            <div class="pt-4 border-top text-center text-muted small" style="border-color: rgba(255,255,255,0.05) !important;">
                © {{ date('Y') }} {{ setting('footer_copyright', 'Baritone Music Academy. Built with Laravel 13, Bootstrap 5 & XAMPP MySQL. All rights reserved.') }}
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
