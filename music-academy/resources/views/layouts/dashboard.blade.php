<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Academy Portal') — {{ setting('site_name', 'BARITONE') }}</title>

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
    <!-- Quill.js Rich Text Editor -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    @include('layouts.partials.dynamic-styles')
    @stack('styles')
</head>
<body class="bg-dark">

    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-lg-4">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary btn-sm d-lg-none text-white border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                    @include('layouts.partials.brand-logo')
                </a>
            </div>

            <div class="d-flex align-items-center gap-2 gap-md-3">
                <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex align-items-center gap-1 text-white border-secondary">
                    <i class="bi bi-globe"></i> Public Site
                </a>
                <a href="{{ route('messages.index') }}" class="btn btn-sm btn-outline-secondary text-white border-secondary position-relative">
                    <i class="bi bi-envelope"></i>
                    @php $unreadCount = \App\Models\Message::where('recipient_id', auth()->id())->whereNull('read_at')->count(); @endphp
                    @if($unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </a>

                <div class="dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <img src="{{ auth()->user()->avatarUrl() }}" alt="Avatar" class="rounded-circle border border-gold" width="34" height="34" style="object-fit: cover;">
                        <div class="d-none d-md-block text-start lh-1">
                            <div class="small fw-semibold">{{ auth()->user()->name }}</div>
                            <small class="text-gold text-capitalize" style="font-size: 0.72rem;">{{ auth()->user()->role }}</small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark bg-surface shadow border-secondary">
                        <li class="px-3 py-1 text-muted small">
                            {{ auth()->user()->email }}
                        </li>
                        <li><hr class="dropdown-divider border-secondary"></li>
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2 text-gold"></i> Profile & Password</a></li>
                        <li><a class="dropdown-item" href="{{ route('courses.public.index') }}"><i class="bi bi-compass me-2 text-gold"></i> Explore Courses</a></li>
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
                </div>
            </div>
        </div>
    </nav>

    <!-- Main App Container with Sidebar Layout -->
    <div class="container-fluid px-0">
        <div class="row g-0">

            <!-- Desktop Sidebar -->
            <div class="col-lg-2 d-none d-lg-block dashboard-sidebar">
                @include('layouts.partials.sidebar-nav')
            </div>

            <!-- Mobile Offcanvas Sidebar -->
            <div class="offcanvas offcanvas-start bg-surface text-white" tabindex="-1" id="sidebarOffcanvas" style="width: 280px;">
                <div class="offcanvas-header border-bottom border-secondary">
                    <h5 class="offcanvas-title font-serif text-gold">{{ setting('site_name', 'BARITONE') }} LMS</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body p-3">
                    @include('layouts.partials.sidebar-nav')
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-lg-10 p-3 p-md-4 min-vh-100">
                <!-- Flash Alerts -->
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

                @yield('content')
            </div>

        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Quill.js Rich Text Editor & Live Universal Media Previews -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script src="{{ asset('js/universal-editor.js') }}?v=1.0"></script>
    @stack('scripts')
</body>
</html>
