@extends('layouts.app')

@section('title', setting('site_name', 'HARMONIA') . ' — ' . setting('site_tagline', 'World-Class Conservatory Online'))

@section('content')

@php
    $statsMode = setting('hero_stats_mode', 'auto');
    if ($statsMode === 'custom') {
        $statStudents = setting('hero_stat_students', '450+');
        $statCourses = setting('hero_stat_courses', '24');
        $statFaculty = setting('hero_stat_faculty', '18');
        $statCertificates = setting('hero_stat_certificates', '320+');
    } else {
        $statStudents = $stats['students'] . '+';
        $statCourses = $stats['courses'];
        $statFaculty = $stats['instructors'];
        $statCertificates = $stats['certificates'] . '+';
    }
@endphp

<!-- Hero Section -->
<section class="hero-gradient text-white text-center position-relative">
    <div class="container position-relative z-1 py-lg-4">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3 border border-gold" style="background: rgba(245, 158, 11, 0.1);">
            <div class="music-bars">
                <span class="music-bar"></span>
                <span class="music-bar"></span>
                <span class="music-bar"></span>
                <span class="music-bar"></span>
            </div>
            <span class="text-gold fw-semibold small text-uppercase" style="letter-spacing: 0.08em;">
                {{ setting('hero_badge', 'World-Class Conservatory Online') }}
            </span>
        </div>

        <h1 class="display-3 font-serif fw-bold mb-3 mx-auto" style="max-width: 900px; line-height: 1.15;">
            {!! setting('hero_title', 'Master Your Instrument with <span class="text-gold">Virtuoso</span> Instruction.') !!}
        </h1>

        <p class="lead text-muted mx-auto mb-4" style="max-width: 680px; font-weight: 300;">
            {{ setting('hero_subtitle', 'Study classical, jazz, and contemporary music through interactive video lessons, sheet music annotations, real-time practice feedback, and verified academy certifications.') }}
        </p>

        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="{{ setting('hero_cta_primary_link', route('courses.public.index')) }}" class="btn btn-gold btn-lg px-4 py-3">
                <i class="bi bi-compass me-2"></i> {{ setting('hero_cta_primary_text', 'Explore Masterclasses') }}
            </a>
            @guest
                <a href="{{ setting('hero_cta_secondary_link', route('register')) }}" class="btn btn-outline-light btn-lg px-4 py-3 border-secondary">
                    <i class="bi bi-mortarboard me-2"></i> {{ setting('hero_cta_secondary_text', 'Apply for Enrollment') }}
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="btn btn-outline-gold btn-lg px-4 py-3">
                    <i class="bi bi-speedometer2 me-2"></i> Open My Dashboard
                </a>
            @endguest
        </div>

        <!-- Academy Stats Bar -->
        <div class="row g-3 justify-content-center mt-5 pt-4">
            <div class="col-6 col-md-3">
                <div class="card card-glass p-3 text-center">
                    <h2 class="font-serif text-gold mb-0 fw-bold">{{ $statStudents }}</h2>
                    <span class="text-muted small">Enrolled Students</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-glass p-3 text-center">
                    <h2 class="font-serif text-white mb-0 fw-bold">{{ $statCourses }}</h2>
                    <span class="text-muted small">Academy Courses</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-glass p-3 text-center">
                    <h2 class="font-serif text-white mb-0 fw-bold">{{ $statFaculty }}</h2>
                    <span class="text-muted small">Virtuoso Faculty</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-glass p-3 text-center">
                    <h2 class="font-serif text-gold mb-0 fw-bold">{{ $statCertificates }}</h2>
                    <span class="text-muted small">Graduated Certificates</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Instruments Showcase -->
<section class="py-5" style="background-color: var(--bg-surface);">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
            <div>
                <span class="text-gold fw-bold text-uppercase small" style="letter-spacing: 0.08em;">
                    {{ setting('disciplines_subtitle', 'Disciplines') }}
                </span>
                <h2 class="font-serif text-white fw-bold mb-0">
                    {{ setting('disciplines_title', 'Browse by Instrument') }}
                </h2>
            </div>
            <a href="{{ route('courses.public.index') }}" class="text-gold text-decoration-none small fw-semibold">
                View All Courses <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="row g-3">
            @foreach($instruments as $instrument)
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ route('courses.public.index', ['instrument' => $instrument->slug]) }}" class="text-decoration-none">
                        <div class="card card-glass text-center p-3 h-100">
                            <div class="stat-icon bg-surface-elevated text-gold mx-auto mb-2 rounded-circle border border-secondary" style="width: 50px; height: 50px;">
                                <i class="bi bi-{{ $instrument->icon }} fs-4"></i>
                            </div>
                            <h6 class="text-white mb-1 fw-bold">{{ $instrument->name }}</h6>
                            <small class="text-muted">{{ $instrument->courses_count }} {{ Str::plural('course', $instrument->courses_count) }}</small>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Courses -->
<section class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="text-gold fw-bold text-uppercase small" style="letter-spacing: 0.08em;">
                {{ setting('featured_courses_subtitle', 'Curated Repertoire') }}
            </span>
            <h2 class="font-serif text-white fw-bold mb-2">
                {{ setting('featured_courses_title', 'Featured Masterclasses') }}
            </h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">
                {{ setting('featured_courses_desc', 'Comprehensive curricula designed by conservatory concert soloists and master educators.') }}
            </p>
        </div>

        <div class="row g-4">
            @foreach($featured as $course)
                <div class="col-md-6 col-lg-4">
                    <div class="card card-glass h-100 overflow-hidden d-flex flex-column">
                        <div class="position-relative">
                            <img src="{{ $course->coverUrl() }}" 
                                 alt="{{ $course->title }}" 
                                 class="w-100" 
                                 style="height: 200px; object-fit: cover;"
                                 onerror="this.onerror=null;this.src='{{ asset('images/course-default.svg') }}';">
                            <span class="position-absolute top-0 start-0 m-3 badge {{ $course->levelBadgeClass() }} text-uppercase">
                                {{ $course->level }}
                            </span>
                            <span class="position-absolute top-0 end-0 m-3 badge bg-dark bg-opacity-75 text-white border border-secondary">
                                <i class="bi bi-{{ $course->instrument->icon }} text-gold me-1"></i> {{ $course->instrument->name }}
                            </span>
                        </div>

                        <div class="card-body p-4 d-flex flex-column flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <img src="{{ $course->instructor->avatarUrl() }}" alt="{{ $course->instructor->name }}" class="rounded-circle" width="24" height="24" style="object-fit: cover;">
                                <small class="text-muted">{{ $course->instructor->name }}</small>
                            </div>

                            <h5 class="font-serif fw-bold text-white mb-2">
                                <a href="{{ route('courses.public.show', $course) }}" class="text-white text-decoration-none hover-gold">
                                    {{ $course->title }}
                                </a>
                            </h5>

                            <p class="text-muted small mb-4 flex-grow-1">
                                {{ Str::limit($course->short_description ?? $course->description, 100) }}
                            </p>

                            <div class="pt-3 border-top border-secondary d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    <i class="bi bi-play-circle text-gold me-1"></i> {{ $course->lessons_count }} Lessons
                                    @if($course->duration_weeks)
                                        <span class="ms-2">· {{ $course->duration_weeks }} wks</span>
                                    @endif
                                </div>
                                <div class="fw-bold fs-5 text-gold">
                                    {{ $course->isFree() ? 'FREE' : '$' . number_format($course->fee, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0">
                            <a href="{{ route('courses.public.show', $course) }}" class="btn btn-outline-gold w-100">
                                View Course & Syllabus
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('courses.public.index') }}" class="btn btn-gold px-4 py-2">
                Browse Full Academy Catalog <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</section>

<!-- Academy Pillars (Why Harmonia / Methodology) -->
<section class="py-5" style="background-color: var(--bg-surface);">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="text-gold fw-bold text-uppercase small" style="letter-spacing: 0.08em;">
                {{ setting('methodology_subtitle', 'Methodology') }}
            </span>
            <h2 class="font-serif text-white fw-bold mb-2">
                {{ setting('methodology_title', 'The Conservatory Advantage') }}
            </h2>
            <p class="text-muted mx-auto" style="max-width: 620px;">
                {{ setting('methodology_desc', 'Traditional academic rigor integrated seamlessly into a modern digital learning environment.') }}
            </p>
        </div>

        <div class="row g-4">
            <!-- Pillar 1 -->
            <div class="col-md-6 col-lg-3">
                <div class="card card-solid p-4 text-center h-100">
                    <div class="stat-icon bg-surface-elevated text-gold mx-auto mb-3 rounded-circle border border-gold" style="width: 60px; height: 60px;">
                        <i class="bi {{ Str::startsWith(setting('pillar1_icon', 'bi-file-earmark-pdf'), 'bi-') ? setting('pillar1_icon', 'bi-file-earmark-pdf') : 'bi-' . setting('pillar1_icon', 'file-earmark-pdf') }} fs-3"></i>
                    </div>
                    <h5 class="text-white fw-bold mb-2 font-serif">{{ setting('pillar1_title', 'Sheet Music Library') }}</h5>
                    <p class="text-muted small mb-0">{{ setting('pillar1_desc', 'Every lesson includes annotated master scores, fingerings, and printable PDFs.') }}</p>
                </div>
            </div>

            <!-- Pillar 2 -->
            <div class="col-md-6 col-lg-3">
                <div class="card card-solid p-4 text-center h-100">
                    <div class="stat-icon bg-surface-elevated text-gold mx-auto mb-3 rounded-circle border border-gold" style="width: 60px; height: 60px;">
                        <i class="bi {{ Str::startsWith(setting('pillar2_icon', 'bi-mic'), 'bi-') ? setting('pillar2_icon', 'bi-mic') : 'bi-' . setting('pillar2_icon', 'mic') }} fs-3"></i>
                    </div>
                    <h5 class="text-white fw-bold mb-2 font-serif">{{ setting('pillar2_title', 'Practice Recordings') }}</h5>
                    <p class="text-muted small mb-0">{{ setting('pillar2_desc', 'Record and upload your etudes for personalised audio feedback and grade rubrics from your instructor.') }}</p>
                </div>
            </div>

            <!-- Pillar 3 -->
            <div class="col-md-6 col-lg-3">
                <div class="card card-solid p-4 text-center h-100">
                    <div class="stat-icon bg-surface-elevated text-gold mx-auto mb-3 rounded-circle border border-gold" style="width: 60px; height: 60px;">
                        <i class="bi {{ Str::startsWith(setting('pillar3_icon', 'bi-question-circle'), 'bi-') ? setting('pillar3_icon', 'bi-question-circle') : 'bi-' . setting('pillar3_icon', 'question-circle') }} fs-3"></i>
                    </div>
                    <h5 class="text-white fw-bold mb-2 font-serif">{{ setting('pillar3_title', 'Theory Assessments') }}</h5>
                    <p class="text-muted small mb-0">{{ setting('pillar3_desc', 'Interactive quizzes test harmonic ear training, interval recognition, notation, and music history.') }}</p>
                </div>
            </div>

            <!-- Pillar 4 -->
            <div class="col-md-6 col-lg-3">
                <div class="card card-solid p-4 text-center h-100">
                    <div class="stat-icon bg-surface-elevated text-gold mx-auto mb-3 rounded-circle border border-gold" style="width: 60px; height: 60px;">
                        <i class="bi {{ Str::startsWith(setting('pillar4_icon', 'bi-award'), 'bi-') ? setting('pillar4_icon', 'bi-award') : 'bi-' . setting('pillar4_icon', 'award') }} fs-3"></i>
                    </div>
                    <h5 class="text-white fw-bold mb-2 font-serif">{{ setting('pillar4_title', 'Verified Credentials') }}</h5>
                    <p class="text-muted small mb-0">{{ setting('pillar4_desc', 'Receive a unique, cryptographically verifiable certificate of completion upon mastery.') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Faculty Highlights -->
<section class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="text-gold fw-bold text-uppercase small" style="letter-spacing: 0.08em;">
                {{ setting('faculty_subtitle', 'World-Class Mentors') }}
            </span>
            <h2 class="font-serif text-white fw-bold mb-2">
                {{ setting('faculty_title', 'Meet Your Master Instructors') }}
            </h2>
        </div>

        <div class="row g-4 justify-content-center">
            @foreach($instructors as $instructor)
                <div class="col-md-6 col-lg-3">
                    <div class="card card-glass text-center p-4 h-100">
                        <img src="{{ $instructor->avatarUrl() }}" alt="{{ $instructor->name }}" class="rounded-circle mx-auto mb-3 border border-2 border-gold shadow" width="90" height="90" style="object-fit: cover;">
                        <h5 class="text-white fw-bold mb-1 font-serif">{{ $instructor->name }}</h5>
                        <small class="text-gold mb-2 d-block">Senior Conservatory Faculty</small>
                        <p class="text-muted small mb-3">
                            {{ Str::limit($instructor->bio ?? 'Distinguished recitalist and mentor dedicated to advancing classical and modern technique.', 100) }}
                        </p>
                        <span class="badge bg-surface-elevated text-muted border border-secondary small">
                            {{ $instructor->taught_courses_count }} Masterclasses
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5">
    <div class="container">
        <div class="card card-glass p-5 text-center position-relative overflow-hidden" style="background: radial-gradient(circle at center, rgba({{ hexToRgb(setting('color_primary', '#4f46e5')) }}, 0.4) 0%, rgba({{ hexToRgb(setting('color_bg_surface', '#111827')) }}, 0.95) 80%); border-color: var(--border-gold);">
            <h2 class="display-5 font-serif fw-bold text-white mb-3">
                {{ setting('cta_banner_title', 'Begin Your Audition Today') }}
            </h2>
            <p class="lead text-muted mx-auto mb-4" style="max-width: 600px;">
                {{ setting('cta_banner_desc', 'Join hundreds of passionate musicians progressing through our structured conservatory syllabus.') }}
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="{{ setting('cta_banner_btn1_link', route('register')) }}" class="btn btn-gold btn-lg px-4">
                    {{ setting('cta_banner_btn1_text', 'Register as Student') }}
                </a>
                <a href="{{ setting('cta_banner_btn2_link', route('courses.public.index')) }}" class="btn btn-outline-light btn-lg px-4 border-secondary">
                    {{ setting('cta_banner_btn2_text', 'View Course Catalog') }}
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
