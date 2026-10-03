@extends('layouts.dashboard')

@section('title', 'Student Conservatory Dashboard')

@section('content')
<!-- Header & Welcome Banner -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <div class="music-bars">
                <span class="music-bar"></span>
                <span class="music-bar"></span>
                <span class="music-bar"></span>
                <span class="music-bar"></span>
            </div>
            <span class="text-gold fw-bold small text-uppercase" style="letter-spacing: 0.08em;">
                {{ setting('student_portal_title', 'Conservatory Virtual Studio') }}
            </span>
        </div>
        <h2 class="font-serif text-white fw-bold mb-0">Welcome back, {{ auth()->user()->name }}<span class="text-gold">.</span></h2>
        <span class="text-muted small">{{ setting('student_welcome_sub', 'Continue your musical journey where you left off. Practice makes virtuoso.') }}</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('courses.public.index') }}" class="btn btn-gold btn-sm px-3 shadow-sm">
            <i class="bi bi-compass me-1"></i> Browse Masterclasses
        </a>
    </div>
</div>

<!-- Dynamic Academy Broadcast Alert for Students -->
@if(setting('student_announcement_enabled', '1') == '1' && setting('student_announcement_text'))
    @php
        $sAlertType = setting('student_announcement_type', 'info');
    @endphp
    <div class="alert alert-{{ $sAlertType }} border-{{ $sAlertType }} d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 shadow-sm" role="alert">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon-wrapper bg-{{ $sAlertType }}-subtle text-{{ $sAlertType }} border border-{{ $sAlertType }}-subtle">
                <i class="bi bi-megaphone-fill fs-5"></i>
            </div>
            <div>
                <strong class="d-block text-white">{{ setting('student_announcement_title', 'Masterclass Practice Notice') }}</strong>
                <span class="small text-secondary">{{ setting('student_announcement_text') }}</span>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Top Stats Grid -->
<div class="row g-3 mb-4">
    <!-- Active Studies -->
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card stat-card-glow-primary h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">Active Studies</span>
                <div class="stat-icon-wrapper bg-primary-subtle text-primary border border-primary-subtle">
                    <i class="bi bi-music-note-list"></i>
                </div>
            </div>
            <div>
                <h3 class="font-serif text-white fw-bold mb-1">{{ $stats['active'] }}</h3>
                <span class="text-secondary small" style="font-size: 0.75rem;">
                    {{ $stats['active'] === 1 ? '1 masterclass in progress' : $stats['active'] . ' masterclasses active' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Completed Masterclasses -->
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card stat-card-glow-success h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">Completed</span>
                <div class="stat-icon-wrapper bg-success-subtle text-success border border-success-subtle">
                    <i class="bi bi-award-fill"></i>
                </div>
            </div>
            <div>
                <h3 class="font-serif text-white fw-bold mb-1">{{ $stats['completed'] }}</h3>
                @if($stats['completed'] > 0)
                    <a href="{{ route('student.certificates') }}" class="text-success small text-decoration-none fw-semibold" style="font-size: 0.75rem;">
                        <i class="bi bi-patch-check-fill me-1"></i> View Issued Diplomas
                    </a>
                @else
                    <span class="text-muted small" style="font-size: 0.75rem;">Earn certificates upon 100% mastery</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Overall Syllabus Progress -->
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card stat-card-glow-gold h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">Curriculum Progress</span>
                <div class="stat-icon-wrapper bg-warning-subtle text-gold border border-warning-subtle">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
            </div>
            <div>
                <h3 class="font-serif text-gold fw-bold mb-1">{{ $stats['avg_progress'] }}%</h3>
                <div class="progress-lux mb-1">
                    <div class="progress-bar bg-gold" style="width: {{ $stats['avg_progress'] }}%;"></div>
                </div>
                <span class="text-secondary small" style="font-size: 0.75rem;">Average across enrolled courses</span>
            </div>
        </div>
    </div>

    <!-- Tuition & Balance -->
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card {{ $stats['balance'] > 0 ? 'stat-card-glow-gold border-warning' : 'stat-card-glow-info' }} h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">Tuition Balance</span>
                <div class="stat-icon-wrapper {{ $stats['balance'] > 0 ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-info-subtle text-info border border-info-subtle' }}">
                    <i class="bi bi-credit-card-fill"></i>
                </div>
            </div>
            <div>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="font-serif text-white fw-bold mb-1">${{ number_format($stats['balance'], 2) }}</h3>
                    @if($stats['balance'] > 0)
                        <a href="{{ route('payments.index') }}" class="btn btn-warning btn-sm py-1 px-2 text-dark fw-bold" style="font-size: 0.75rem;">
                            Pay Tuition <i class="bi bi-arrow-right"></i>
                        </a>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle small">Settled</span>
                    @endif
                </div>
                <span class="text-secondary small" style="font-size: 0.75rem;">
                    {{ $stats['balance'] > 0 ? 'Pending enrollment verification' : 'All conservatory fees clear' }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Active Enrolled Courses (Left Column) -->
    <div class="col-lg-7">
        <div class="card card-solid p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="text-white font-serif fw-bold mb-0">My Enrolled Courses</h4>
                    <span class="text-muted small">Your active performance modules &amp; practice syllabus</span>
                </div>
                <a href="{{ route('student.courses') }}" class="text-gold small text-decoration-none fw-semibold">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            @forelse($enrollments as $enr)
                <div class="course-study-pass mb-3">
                    <div class="row align-items-center g-3">
                        <!-- Thumbnail Artwork with Fallback -->
                        <div class="col-md-3">
                            <div class="course-thumb-box">
                                <img src="{{ $enr->course->coverUrl() }}" 
                                     alt="{{ $enr->course->title }}"
                                     onerror="this.onerror=null;this.src='{{ asset('images/course-default.svg') }}';">
                                <span class="position-absolute bottom-0 start-0 m-1 badge bg-dark bg-opacity-75 text-white border border-secondary" style="font-size: 0.65rem;">
                                    <i class="bi bi-{{ $enr->course->instrument->icon }} text-gold me-1"></i> {{ $enr->course->instrument->name }}
                                </span>
                            </div>
                        </div>

                        <!-- Course Info & Meta -->
                        <div class="col-md-5">
                            <div class="d-flex align-items-center gap-1 mb-1">
                                <span class="badge {{ $enr->course->levelBadgeClass() }} text-uppercase" style="font-size: 0.62rem; letter-spacing: 0.05em;">
                                    {{ $enr->course->level }}
                                </span>
                            </div>
                            <h6 class="text-white fw-bold mb-1 font-serif">
                                <a href="{{ route('courses.public.show', $enr->course) }}" class="text-white text-decoration-none hover-gold">
                                    {{ $enr->course->title }}
                                </a>
                            </h6>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <img src="{{ $enr->course->instructor->avatarUrl() }}" alt="{{ $enr->course->instructor->name }}" class="rounded-circle" width="20" height="20" style="object-fit: cover;">
                                <small class="text-muted">{{ $enr->course->instructor->name }}</small>
                            </div>
                        </div>

                        <!-- Progress & Action Button -->
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small fw-semibold" style="font-size: 0.75rem;">Progress</span>
                                @if($enr->status === 'completed')
                                    <span class="text-success fw-bold small"><i class="bi bi-check-circle-fill me-1"></i>100%</span>
                                @elseif($enr->status === 'active')
                                    <span class="text-gold fw-bold small">{{ $enr->progress }}%</span>
                                @else
                                    <span class="text-warning fw-bold small">0%</span>
                                @endif
                            </div>

                            <div class="progress-lux mb-2">
                                @if($enr->status === 'completed')
                                    <div class="progress-bar bg-success" style="width: 100%;"></div>
                                @elseif($enr->status === 'active')
                                    <div class="progress-bar bg-gold" style="width: {{ $enr->progress }}%;"></div>
                                @else
                                    <div class="progress-bar bg-warning bg-opacity-50" style="width: 15%;"></div>
                                @endif
                            </div>

                            <!-- Contextual Action Buttons -->
                            @if($enr->status === 'completed')
                                <a href="{{ route('learning.course', $enr->course) }}" class="btn btn-gold btn-sm w-100 py-1 mb-1 fw-semibold shadow-sm">
                                    <i class="bi bi-play-circle-fill me-1"></i> Revisit Classroom
                                </a>
                                @if($enr->certificate)
                                    <a href="{{ route('certificates.show', $enr->certificate) }}" class="btn btn-outline-gold btn-sm w-100 py-1" style="font-size: 0.75rem;">
                                        <i class="bi bi-award me-1"></i> View Diploma
                                    </a>
                                @endif
                            @elseif($enr->status === 'active')
                                <a href="{{ route('learning.course', $enr->course) }}" class="btn btn-gold btn-sm w-100 py-1 fw-semibold shadow-sm">
                                    <i class="bi bi-play-circle-fill me-1"></i> Continue Lesson
                                </a>
                            @else
                                @if($enr->hasPendingPayment())
                                    <div class="d-flex flex-column gap-1">
                                        <div class="badge bg-warning-subtle text-warning border border-warning-subtle py-2 text-center" style="font-size: 0.72rem;">
                                            <i class="bi bi-hourglass-split me-1"></i> Payment Under Review
                                        </div>
                                        <a href="{{ route('payments.index') }}" class="btn btn-outline-warning btn-sm w-100 py-1" style="font-size: 0.75rem;">
                                            <i class="bi bi-receipt me-1"></i> View Receipt Status
                                        </a>
                                    </div>
                                @else
                                    <a href="{{ route('payments.index') }}" class="btn btn-warning btn-sm w-100 py-2 text-dark fw-bold shadow-sm">
                                        <i class="bi bi-credit-card-fill me-1"></i> Submit Payment (${{ number_format($enr->course->fee, 2) }})
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <div class="mb-3">
                        <span class="badge bg-surface-elevated text-gold p-3 rounded-circle border border-secondary">
                            <i class="bi bi-music-note-beamed fs-3"></i>
                        </span>
                    </div>
                    <h5 class="text-white font-serif fw-bold">No Enrolled Masterclasses Yet</h5>
                    <p class="text-muted small mx-auto mb-3" style="max-width: 380px;">
                        Begin your conservatory studies by exploring our virtuoso curriculum across classical piano, violin, acoustic guitar, and bel canto vocals.
                    </p>
                    <a href="{{ route('courses.public.index') }}" class="btn btn-gold btn-sm px-4">
                        <i class="bi bi-compass me-1"></i> Explore Academy Catalog
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Practice Assignments Due -->
        <div class="card card-solid p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="text-white font-serif fw-bold mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-mic text-gold"></i> Practice Assignments Due
                    </h5>
                    <span class="text-muted small">Recordings and etudes pending instructor submission</span>
                </div>
                <a href="{{ route('assignments.index') }}" class="text-gold small text-decoration-none fw-semibold">
                    All Assignments <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="list-group list-group-flush border-top border-secondary">
                @forelse($dueAssignments as $due)
                    <div class="list-group-item bg-transparent text-white px-0 py-3 border-secondary d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="fw-semibold">{{ $due->title }}</div>
                            <small class="text-gold">{{ $due->course->title }}</small>
                            @if($due->due_at)
                                <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                                    <i class="bi bi-calendar-event me-1"></i> Due {{ $due->due_at->format('M j, Y · g:i A') }}
                                </div>
                            @endif
                        </div>
                        <a href="{{ route('assignments.show', $due) }}" class="btn btn-outline-gold btn-sm">
                            <i class="bi bi-upload me-1"></i> Record &amp; Submit
                        </a>
                    </div>
                @empty
                    <div class="py-4 text-center text-muted">
                        <i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>
                        <span class="text-white fw-semibold small">All Practice Recordings Up To Date!</span>
                        <p class="text-muted small mb-0 mt-1">No pending etudes due right now. Keep practicing your repertoire.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Live Classes & Announcements (Right Column) -->
    <div class="col-lg-5">
        <!-- Upcoming Sessions Card -->
        <div class="card card-solid p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="text-white font-serif fw-bold mb-0">Upcoming Masterclasses</h5>
                    <span class="text-muted small">Live studio sectionals &amp; workshops</span>
                </div>
                <a href="{{ route('schedule.index') }}" class="text-gold small text-decoration-none fw-semibold">
                    Calendar <i class="bi bi-calendar3 ms-1"></i>
                </a>
            </div>

            <div class="list-group list-group-flush border-top border-secondary">
                @forelse($upcoming as $sesh)
                    <div class="list-group-item bg-transparent text-white px-0 py-3 border-secondary">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                            <div>
                                <span class="text-gold small fw-bold d-block">{{ $sesh->course->title }}</span>
                                <span class="text-white fw-semibold">{{ $sesh->title }}</span>
                            </div>
                            <span class="badge bg-surface-elevated text-gold border border-warning-subtle small text-nowrap">
                                <i class="bi bi-calendar2-week me-1"></i> {{ $sesh->starts_at->format('M d · g:i A') }}
                            </span>
                        </div>
                        @if($sesh->location)
                            <div class="text-muted small mb-2" style="font-size: 0.75rem;">
                                <i class="bi bi-geo-alt text-gold me-1"></i> {{ $sesh->location }}
                            </div>
                        @endif
                        @if($sesh->meeting_url)
                            <div class="mt-2">
                                <a href="{{ $sesh->meeting_url }}" target="_blank" class="btn btn-primary-gradient btn-sm py-1 px-3" style="font-size: 0.78rem;">
                                    <i class="bi bi-camera-video-fill me-1"></i> Join Sectional Room
                                </a>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-4 text-center text-muted">
                        <i class="bi bi-calendar-check text-muted fs-3 d-block mb-1"></i>
                        <span class="small">No live masterclass sessions scheduled this week.</span>
                    </div>
                @endforelse
            </div>
        </div>

        @if(setting('student_practice_tip'))
            <!-- Virtuoso Practice Tip of the Day -->
            <div class="card card-solid p-3 mb-3 border-gold" style="background: radial-gradient(circle at 100% 0%, rgba(245, 158, 11, 0.12) 0%, var(--bg-surface) 70%);">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-lightbulb-fill text-gold fs-5"></i>
                    <strong class="text-gold small text-uppercase" style="letter-spacing: 0.05em;">Academy Practice Advice</strong>
                </div>
                <p class="text-secondary small mb-0 fst-italic">"{{ setting('student_practice_tip') }}"</p>
            </div>
        @endif

        <!-- Academy Bulletins Card -->
        <div class="card card-solid p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="text-white font-serif fw-bold mb-0">Academy Bulletins</h5>
                    <span class="text-muted small">Notices from conservatory faculty</span>
                </div>
                <a href="{{ route('announcements.index') }}" class="text-gold small text-decoration-none fw-semibold">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            @forelse($announcements as $ann)
                <div class="p-3 rounded mb-3 {{ $ann->is_pinned ? 'border border-gold' : 'border border-secondary' }}" style="background-color: var(--bg-surface-glass);">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="d-flex align-items-center gap-2">
                            @if($ann->is_pinned)
                                <span class="badge bg-gold text-dark small"><i class="bi bi-pin-fill"></i> Pinned</span>
                            @endif
                            <strong class="text-white small font-serif">{{ $ann->title }}</strong>
                        </div>
                        <small class="text-muted" style="font-size: 0.7rem;">{{ $ann->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="text-muted small mb-2" style="line-height: 1.5;">{{ Str::limit($ann->body, 110) }}</p>
                    <div class="d-flex align-items-center gap-2 text-secondary" style="font-size: 0.72rem;">
                        <img src="{{ $ann->author->avatarUrl() }}" alt="{{ $ann->author->name }}" class="rounded-circle" width="16" height="16" style="object-fit: cover;">
                        <span>{{ $ann->author->name }} (<span class="text-gold text-capitalize">{{ $ann->author->role }}</span>)</span>
                    </div>
                </div>
            @empty
                <p class="text-muted small mb-0 py-3 text-center">No bulletins currently posted.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
