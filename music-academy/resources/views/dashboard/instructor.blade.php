@extends('layouts.dashboard')

@section('title', 'Instructor Studio Dashboard — Baritone Music Academy')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">{{ setting('instructor_portal_title', 'Faculty Studio Overview') }}</h2>
        <span class="text-muted small">Welcome, {{ auth()->user()->name }}. {{ setting('instructor_welcome_sub', 'Manage your courses, submissions, and rehearsals.') }}</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('courses.create') }}" class="btn btn-gold btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Create Masterclass
        </a>
    </div>
</div>

<!-- Dynamic Faculty Directive Banner -->
@if(setting('instructor_notice_enabled', '1') == '1' && setting('instructor_notice_text'))
    @php
        $iAlertType = setting('instructor_notice_type', 'warning');
    @endphp
    <div class="alert alert-{{ $iAlertType }} border-{{ $iAlertType }} d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 shadow-sm" role="alert">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon-wrapper bg-{{ $iAlertType }}-subtle text-{{ $iAlertType }} border border-{{ $iAlertType }}-subtle">
                <i class="bi bi-shield-exclamation fs-5"></i>
            </div>
            <div>
                <strong class="d-block text-white">{{ setting('instructor_notice_title', 'Academy Faculty Directive') }}</strong>
                <span class="small text-secondary">{{ setting('instructor_notice_text') }}</span>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small fw-semibold">My Courses</span>
                <span class="text-primary"><i class="bi bi-journal-bookmark-fill fs-5"></i></span>
            </div>
            <h3 class="font-serif text-white fw-bold mb-0">{{ $stats['courses'] }}</h3>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Active Students</span>
                <span class="text-info"><i class="bi bi-people-fill fs-5"></i></span>
            </div>
            <h3 class="font-serif text-white fw-bold mb-0">{{ $stats['students'] }}</h3>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card {{ $stats['to_grade'] > 0 ? 'border-warning' : '' }}">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Submissions to Grade</span>
                <span class="text-warning"><i class="bi bi-mic-fill fs-5"></i></span>
            </div>
            <h3 class="font-serif text-white fw-bold mb-0">
                {{ $stats['to_grade'] }}
                @if($stats['to_grade'] > 0)
                    <span class="badge bg-warning text-dark ms-1 small" style="font-size: 0.7rem;">Needs Review</span>
                @endif
            </h3>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Live Classes (7 Days)</span>
                <span class="text-gold"><i class="bi bi-calendar2-week-fill fs-5"></i></span>
            </div>
            <h3 class="font-serif text-white fw-bold mb-0">{{ $stats['sessions_week'] }}</h3>
        </div>
    </div>
</div>

@if(setting('instructor_guidelines'))
    <div class="card card-solid p-3 mb-4 border border-secondary" style="background: var(--bg-surface-elevated);">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-info-circle-fill text-gold"></i>
            <strong class="text-white small">Faculty Scoring & Repertoire Guidelines</strong>
        </div>
        <p class="text-secondary small mb-0">{{ setting('instructor_guidelines') }}</p>
    </div>
@endif

<div class="row g-4">
    <!-- Submissions Needing Evaluation -->
    <div class="col-lg-7">
        <div class="card card-solid p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-serif fw-bold mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-mic text-gold"></i> Practice Submissions Awaiting Feedback
                </h5>
                <a href="{{ route('assignments.index') }}" class="text-gold small text-decoration-none">All Assignments</a>
            </div>

            @if($toGrade->count() > 0)
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead class="text-muted small">
                            <tr>
                                <th>Student</th>
                                <th>Assignment / Course</th>
                                <th>Submitted</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($toGrade as $sub)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-white">{{ $sub->student->name }}</div>
                                        <small class="text-muted">{{ $sub->student->email }}</small>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-white">{{ $sub->assignment->title }}</div>
                                        <small class="text-gold">{{ $sub->assignment->course->title }}</small>
                                    </td>
                                    <td class="small text-muted">
                                        {{ $sub->created_at->diffForHumans() }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('assignments.show', $sub->assignment) }}" class="btn btn-gold btn-sm">
                                            <i class="bi bi-pencil-square me-1"></i> Grade & Listen
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-check2-circle text-success fs-2 d-block mb-1"></i>
                    All practice recordings are evaluated. Bravo!
                </div>
            @endif
        </div>

        <!-- My Taught Masterclasses -->
        <div class="card card-solid p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-serif fw-bold mb-0">My Teaching Curriculum</h5>
                <a href="{{ route('courses.create') }}" class="text-gold small text-decoration-none">+ New Course</a>
            </div>

            <div class="row g-3">
                @forelse($courses as $c)
                    <div class="col-md-6">
                        <div class="card card-glass p-3 h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge {{ $c->levelBadgeClass() }} small text-uppercase">{{ $c->level }}</span>
                                <span class="badge bg-{{ $c->status === 'published' ? 'success' : 'secondary' }} small text-capitalize">{{ $c->status }}</span>
                            </div>
                            <h6 class="text-white fw-bold mb-1">{{ $c->title }}</h6>
                            <div class="text-muted small mb-3">
                                <span><i class="bi bi-play-circle text-gold me-1"></i> {{ $c->lessons_count }} Lessons</span> ·
                                <span><i class="bi bi-people text-info me-1"></i> {{ $c->enrollments_count }} Enrolled</span>
                            </div>
                            <div class="d-flex gap-2 mt-auto">
                                <a href="{{ route('courses.show.manage', $c) }}" class="btn btn-outline-gold btn-sm flex-grow-1">
                                    <i class="bi bi-gear me-1"></i> Manage
                                </a>
                                <a href="{{ route('learning.course', $c) }}" class="btn btn-outline-secondary btn-sm text-white border-secondary" title="Preview Student View">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 py-3 text-muted text-center small">
                        You have not published any courses yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Upcoming Sessions & Bulletins -->
    <div class="col-lg-5">
        <div class="card card-solid p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-serif fw-bold mb-0">Upcoming Class Sessions</h5>
                <a href="{{ route('schedule.index') }}" class="text-gold small text-decoration-none">Full Calendar</a>
            </div>

            <div class="list-group list-group-flush border-top border-secondary">
                @forelse($upcoming as $sesh)
                    <div class="list-group-item bg-transparent text-white px-0 py-2 border-secondary">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold small text-gold">{{ $sesh->course->title }}</div>
                                <div class="text-white small">{{ $sesh->title }}</div>
                            </div>
                            <span class="badge bg-surface-elevated text-muted border border-secondary small">
                                {{ $sesh->starts_at->format('M d · g:i A') }}
                            </span>
                        </div>
                        @if($sesh->location)
                            <div class="text-muted small" style="font-size: 0.72rem;"><i class="bi bi-geo-alt me-1"></i> {{ $sesh->location }}</div>
                        @endif
                    </div>
                @empty
                    <p class="text-muted small mb-0 py-2">No upcoming rehearsals scheduled.</p>
                @endforelse
            </div>
        </div>

        <div class="card card-solid p-4">
            <h5 class="text-white font-serif fw-bold mb-3">Academy Bulletins</h5>
            @forelse($announcements as $ann)
                <div class="mb-3 pb-3 border-bottom border-secondary">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        @if($ann->is_pinned)
                            <span class="badge bg-gold small"><i class="bi bi-pin-fill"></i></span>
                        @endif
                        <strong class="text-white small">{{ $ann->title }}</strong>
                    </div>
                    <p class="text-muted small mb-1">{{ Str::limit($ann->body, 90) }}</p>
                    <small class="text-secondary" style="font-size: 0.7rem;">{{ $ann->created_at->diffForHumans() }}</small>
                </div>
            @empty
                <p class="text-muted small mb-0">No bulletins.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
