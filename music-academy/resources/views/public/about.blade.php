@extends('layouts.app')

@section('title', 'About Us — ' . setting('site_name', 'Baritone Music Academy'))

@section('content')

<!-- About Hero Section -->
<section class="hero-gradient text-white text-center position-relative py-5">
    <div class="container position-relative z-1 py-lg-4">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3 border border-gold" style="background: rgba(245, 158, 11, 0.1);">
            <i class="bi bi-bank2 text-gold"></i>
            <span class="text-gold fw-semibold small text-uppercase" style="letter-spacing: 0.08em;">
                {{ setting('about_hero_badge', 'Conservatory Heritage') }}
            </span>
        </div>

        <h1 class="display-4 font-serif fw-bold mb-3 mx-auto" style="max-width: 850px; line-height: 1.18;">
            {!! setting('about_hero_title', 'A Century of Virtuosity & Academic Distinction.') !!}
        </h1>

        <p class="lead text-muted mx-auto mb-4" style="max-width: 720px; font-weight: 300;">
            {{ setting('about_hero_subtitle', 'Baritone blends European conservatory discipline with interactive digital scores, high-fidelity audio critique, and global recital masterclasses.') }}
        </p>

        <!-- Stats Counters -->
        <div class="row g-3 justify-content-center mt-4">
            <div class="col-6 col-md-3">
                <div class="card card-glass p-3 text-center">
                    <h2 class="font-serif text-gold mb-0 fw-bold">{{ $stats['students'] }}+</h2>
                    <span class="text-muted small">Active Musicians</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-glass p-3 text-center">
                    <h2 class="font-serif text-white mb-0 fw-bold">{{ $stats['courses'] }}</h2>
                    <span class="text-muted small">Curated Syllabi</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-glass p-3 text-center">
                    <h2 class="font-serif text-white mb-0 fw-bold">{{ $stats['instructors'] }}</h2>
                    <span class="text-muted small">Virtuoso Faculty</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-glass p-3 text-center">
                    <h2 class="font-serif text-gold mb-0 fw-bold">{{ $stats['certificates'] }}+</h2>
                    <span class="text-muted small">Graduated Diplomas</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Heritage Story & Mission Section -->
<section class="py-5" style="background-color: var(--bg-surface);">
    <div class="container py-4">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <span class="text-gold fw-bold text-uppercase small" style="letter-spacing: 0.08em;">Tradition & Modernity</span>
                <h2 class="font-serif text-white fw-bold mb-3">{{ setting('about_story_title', 'Our Academy Heritage') }}</h2>
                <div class="text-secondary rich-content mb-4" style="line-height: 1.75;">
                    {!! setting('about_story_content') !!}
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card card-solid p-4 p-lg-5 border-gold" style="background: radial-gradient(circle at 0% 0%, rgba(245, 158, 11, 0.08) 0%, var(--bg-surface-elevated) 75%);">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="stat-icon-wrapper bg-primary text-white"><i class="bi bi-compass-fill"></i></span>
                        <h4 class="font-serif text-white fw-bold mb-0">{{ setting('about_mission_title', 'Artistic Mission & Pedagogical Vision') }}</h4>
                    </div>
                    <div class="text-secondary rich-content" style="line-height: 1.75;">
                        {!! setting('about_mission_content') !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dean's Welcome Letter -->
<section class="py-5">
    <div class="container py-4">
        <div class="card card-glass p-4 p-lg-5 border border-secondary">
            <div class="row g-4 align-items-center">
                <div class="col-md-4 text-center border-end-md border-secondary">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="{{ asset('images/avatar-default.svg') }}" alt="Dean" class="rounded-circle border border-2 border-gold shadow" width="130" height="130" style="object-fit: cover;">
                        <span class="position-absolute bottom-0 end-0 badge bg-gold text-dark rounded-circle p-2"><i class="bi bi-mortarboard-fill fs-5"></i></span>
                    </div>
                    <h4 class="font-serif text-white fw-bold mb-0">{{ setting('about_dean_name', 'Prof. Franz Liszt') }}</h4>
                    <span class="text-gold small d-block mb-2">{{ setting('about_dean_title', 'General Director & Dean of Faculty') }}</span>
                    @if(setting('about_dean_quote'))
                        <p class="text-muted fst-italic small px-2 mb-0">"{{ setting('about_dean_quote') }}"</p>
                    @endif
                </div>
                <div class="col-md-8 ps-md-4">
                    <h3 class="font-serif text-white fw-bold mb-3">Welcome to the Academy</h3>
                    <div class="text-secondary rich-content" style="line-height: 1.8;">
                        {!! setting('about_dean_letter') !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 4 Core Pillars of Conservatory Excellence -->
<section class="py-5" style="background-color: var(--bg-surface);">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="text-gold fw-bold text-uppercase small" style="letter-spacing: 0.08em;">Academic Pillars</span>
            <h2 class="font-serif text-white fw-bold mb-2">Our Fundamental Values</h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">
                The core convictions underpinning all performance juries, auditions, and curriculum development.
            </p>
        </div>

        <div class="row g-4">
            <!-- Value 1 -->
            <div class="col-md-6 col-lg-3">
                <div class="card card-solid p-4 text-center h-100">
                    <div class="stat-icon bg-surface-elevated text-gold mx-auto mb-3 rounded-circle border border-gold" style="width: 58px; height: 58px;">
                        <i class="bi {{ Str::startsWith(setting('about_val1_icon', 'bi-trophy-fill'), 'bi-') ? setting('about_val1_icon', 'bi-trophy-fill') : 'bi-' . setting('about_val1_icon', 'trophy-fill') }} fs-3"></i>
                    </div>
                    <h5 class="text-white fw-bold mb-2 font-serif">{{ setting('about_val1_title', 'Virtuoso Discipline') }}</h5>
                    <p class="text-muted small mb-0">{{ setting('about_val1_desc') }}</p>
                </div>
            </div>

            <!-- Value 2 -->
            <div class="col-md-6 col-lg-3">
                <div class="card card-solid p-4 text-center h-100">
                    <div class="stat-icon bg-surface-elevated text-gold mx-auto mb-3 rounded-circle border border-gold" style="width: 58px; height: 58px;">
                        <i class="bi {{ Str::startsWith(setting('about_val2_icon', 'bi-book-half'), 'bi-') ? setting('about_val2_icon', 'bi-book-half') : 'bi-' . setting('about_val2_icon', 'book-half') }} fs-3"></i>
                    </div>
                    <h5 class="text-white fw-bold mb-2 font-serif">{{ setting('about_val2_title', 'Urtext Fidelity') }}</h5>
                    <p class="text-muted small mb-0">{{ setting('about_val2_desc') }}</p>
                </div>
            </div>

            <!-- Value 3 -->
            <div class="col-md-6 col-lg-3">
                <div class="card card-solid p-4 text-center h-100">
                    <div class="stat-icon bg-surface-elevated text-gold mx-auto mb-3 rounded-circle border border-gold" style="width: 58px; height: 58px;">
                        <i class="bi {{ Str::startsWith(setting('about_val3_icon', 'bi-soundwave'), 'bi-') ? setting('about_val3_icon', 'bi-soundwave') : 'bi-' . setting('about_val3_icon', 'soundwave') }} fs-3"></i>
                    </div>
                    <h5 class="text-white fw-bold mb-2 font-serif">{{ setting('about_val3_title', 'Constructive Critique') }}</h5>
                    <p class="text-muted small mb-0">{{ setting('about_val3_desc') }}</p>
                </div>
            </div>

            <!-- Value 4 -->
            <div class="col-md-6 col-lg-3">
                <div class="card card-solid p-4 text-center h-100">
                    <div class="stat-icon bg-surface-elevated text-gold mx-auto mb-3 rounded-circle border border-gold" style="width: 58px; height: 58px;">
                        <i class="bi {{ Str::startsWith(setting('about_val4_icon', 'bi-award-fill'), 'bi-') ? setting('about_val4_icon', 'bi-award-fill') : 'bi-' . setting('about_val4_icon', 'award-fill') }} fs-3"></i>
                    </div>
                    <h5 class="text-white fw-bold mb-2 font-serif">{{ setting('about_val4_title', 'Verified Excellence') }}</h5>
                    <p class="text-muted small mb-0">{{ setting('about_val4_desc') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5">
    <div class="container">
        <div class="card card-glass p-5 text-center position-relative overflow-hidden" style="background: radial-gradient(circle at center, rgba({{ hexToRgb(setting('color_primary', '#4f46e5')) }}, 0.4) 0%, rgba({{ hexToRgb(setting('color_bg_surface', '#111827')) }}, 0.95) 80%); border-color: var(--border-gold);">
            <h2 class="display-5 font-serif fw-bold text-white mb-3">Audition for Our Next Studio Term</h2>
            <p class="lead text-muted mx-auto mb-4" style="max-width: 600px;">
                Begin your conservatory studies today under world-renowned soloist mentors.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="{{ route('register') }}" class="btn btn-gold btn-lg px-4">Apply for Enrollment</a>
                <a href="{{ route('courses.public.index') }}" class="btn btn-outline-light btn-lg px-4 border-secondary">Browse Course Syllabi</a>
            </div>
        </div>
    </div>
</section>

@endsection
