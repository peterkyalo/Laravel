@extends('layouts.app')

@section('title', $course->title . ' — Harmonia')

@section('content')
<div class="py-5">
    <div class="container">

        <!-- Top Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-muted text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('courses.public.index') }}" class="text-muted text-decoration-none">Courses</a></li>
                <li class="breadcrumb-item text-gold active" aria-current="page">{{ $course->instrument->name }}</li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Left Main Column -->
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge {{ $course->levelBadgeClass() }} text-uppercase">{{ $course->level }}</span>
                    <span class="badge bg-surface-elevated text-white border border-secondary">
                        <i class="bi bi-{{ $course->instrument->icon }} text-gold me-1"></i> {{ $course->instrument->name }}
                    </span>
                    @if($course->duration_weeks)
                        <span class="badge bg-dark text-muted border border-secondary">{{ $course->duration_weeks }} Weeks Curriculum</span>
                    @endif
                </div>

                <h1 class="display-5 font-serif fw-bold text-white mb-3">{{ $course->title }}</h1>
                <p class="lead text-muted mb-4">{{ $course->short_description }}</p>

                <!-- Instructor Info Pill -->
                <div class="card card-solid p-3 mb-4 d-flex flex-row align-items-center gap-3">
                    <img src="{{ $course->instructor->avatarUrl() }}" alt="{{ $course->instructor->name }}" class="rounded-circle border border-gold" width="55" height="55" style="object-fit: cover;">
                    <div>
                        <div class="text-muted small">Course Instructor</div>
                        <h6 class="text-white mb-0 fw-bold">{{ $course->instructor->name }}</h6>
                        <small class="text-gold">{{ $course->instructor->email }}</small>
                    </div>
                </div>

                <!-- Course Overview / Description -->
                <div class="card card-solid p-4 mb-4">
                    <h4 class="font-serif text-white fw-bold mb-3">About This Masterclass</h4>
                    <div class="text-light ql-editor" style="line-height: 1.8; padding: 0;">
                        {!! preg_replace('/<span class="ql-ui"[^>]*><\/span>/i', '', $course->description) !!}
                    </div>
                </div>

                <!-- Syllabus / Lesson List -->
                <div class="card card-solid p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="font-serif text-white fw-bold mb-0">Course Curriculum</h4>
                        <span class="text-muted small">{{ $course->lessons->count() }} Lessons · {{ $course->totalMinutes() }} mins total</span>
                    </div>

                    <div class="list-group list-group-flush border-top border-secondary">
                        @forelse($course->lessons as $lesson)
                            <div class="list-group-item bg-transparent text-white px-0 py-3 border-secondary d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-surface-elevated text-gold rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        {{ $lesson->position }}
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $lesson->title }}</div>
                                        <div class="text-muted small">
                                            @if($lesson->duration_minutes)
                                                <i class="bi bi-clock me-1"></i> {{ $lesson->duration_minutes }} min
                                            @endif
                                            @if($lesson->sheet_music_path)
                                                <span class="ms-2 badge bg-surface-elevated text-info border border-secondary"><i class="bi bi-file-earmark-pdf me-1"></i> Sheet Music</span>
                                            @endif
                                            @if($lesson->audio_path)
                                                <span class="ms-2 badge bg-surface-elevated text-warning border border-secondary"><i class="bi bi-music-note me-1"></i> Audio Track</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    @if($lesson->is_preview)
                                        <span class="badge bg-success-subtle text-success border border-success">Free Preview</span>
                                    @else
                                        <i class="bi bi-lock-fill text-muted"></i>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted py-3 mb-0">No lessons published yet.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Live Class Schedule -->
                @if($upcomingSessions->count() > 0)
                    <div class="card card-solid p-4 mb-4">
                        <h4 class="font-serif text-white fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-calendar-event text-gold"></i> Live Masterclass Schedule
                        </h4>
                        <div class="row g-3">
                            @foreach($upcomingSessions as $session)
                                <div class="col-md-6">
                                    <div class="card card-glass p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="text-white fw-bold mb-0">{{ $session->title }}</h6>
                                            <span class="badge bg-gold small">Live</span>
                                        </div>
                                        <div class="text-muted small mb-2">
                                            <i class="bi bi-clock me-1 text-gold"></i> {{ $session->starts_at->format('M j, Y · g:i A') }}
                                        </div>
                                        @if($session->location)
                                            <div class="text-muted small"><i class="bi bi-geo-alt me-1 text-gold"></i> {{ $session->location }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Sticky Sidebar: Enrollment & Pricing -->
            <div class="col-lg-4">
                <div class="card card-glass p-4 sticky-top" style="top: 90px;">
                    <div class="position-relative mb-3 rounded overflow-hidden">
                        <img src="{{ $course->coverUrl() }}" 
                             alt="{{ $course->title }}" 
                             class="w-100 rounded" 
                             style="max-height: 220px; object-fit: cover;"
                             onerror="this.onerror=null;this.src='{{ asset('images/course-default.svg') }}';">
                    </div>

                    <div class="d-flex align-items-baseline gap-2 mb-3">
                        <span class="display-6 font-serif fw-bold text-gold">
                            {{ $course->isFree() ? 'FREE' : '$' . number_format($course->fee, 2) }}
                        </span>
                        @if(!$course->isFree())
                            <span class="text-muted small">one-time tuition</span>
                        @endif
                    </div>

                    @auth
                        @if($enrollment && in_array($enrollment->status, ['active', 'completed']))
                            <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                                <i class="bi bi-check-circle-fill fs-5"></i>
                                <div>You are currently enrolled ({{ $enrollment->progress }}% completed).</div>
                            </div>
                            <a href="{{ route('learning.course', $course) }}" class="btn btn-gold btn-lg w-100 mb-2">
                                <i class="bi bi-play-circle-fill me-1"></i> Open Classroom
                            </a>
                        @elseif($enrollment && $enrollment->status === 'pending')
                            <div class="alert alert-warning mb-3 small">
                                <i class="bi bi-hourglass-split me-1"></i> Enrollment pending payment verification.
                            </div>
                            <a href="{{ route('payments.index') }}" class="btn btn-gold btn-lg w-100 mb-2">
                                <i class="bi bi-credit-card me-1"></i> View Payment Details
                            </a>
                        @else
                            <form action="{{ route('courses.enroll', $course) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-gold btn-lg w-100 mb-2">
                                    {{ $course->isFree() ? 'Enroll for Free' : 'Enroll Now ($' . number_format($course->fee, 2) . ')' }}
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('register') }}" class="btn btn-gold btn-lg w-100 mb-2">
                            Register to Enroll
                        </a>
                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-muted small text-decoration-none">Already a student? Log in</a>
                        </div>
                    @endauth

                    <div class="border-top border-secondary pt-3 mt-3">
                        <h6 class="text-white fw-bold mb-3">This course includes:</h6>
                        <ul class="list-unstyled text-muted small mb-0">
                            <li class="mb-2"><i class="bi bi-play-btn-fill text-gold me-2"></i> {{ $course->lessons->count() }} full masterclass video lessons</li>
                            <li class="mb-2"><i class="bi bi-file-earmark-music-fill text-gold me-2"></i> Downloadable PDF scores & annotations</li>
                            <li class="mb-2"><i class="bi bi-mic-fill text-gold me-2"></i> Audio practice recording submissions</li>
                            <li class="mb-2"><i class="bi bi-question-diamond-fill text-gold me-2"></i> Music theory proficiency quizzes</li>
                            <li class="mb-2"><i class="bi bi-award-fill text-gold me-2"></i> Verified graduation certificate</li>
                            <li><i class="bi bi-infinity text-gold me-2"></i> Lifetime access from any device</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
