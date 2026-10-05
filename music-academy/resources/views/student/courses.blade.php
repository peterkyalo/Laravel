@extends('layouts.dashboard')

@section('title', 'My Enrolled Masterclasses — Baritone')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">My Conservatory Studies</h2>
        <span class="text-muted small">Your enrolled masterclasses, lesson progression, and diplomas.</span>
    </div>
    <a href="{{ route('courses.public.index') }}" class="btn btn-gold btn-sm">
        <i class="bi bi-compass me-1"></i> Enroll in New Course
    </a>
</div>

<div class="row g-4">
    @forelse($enrollments as $enr)
        <div class="col-md-6 col-lg-4">
            <div class="card card-glass h-100 overflow-hidden d-flex flex-column">
                <div class="position-relative">
                    <img src="{{ $enr->course->coverUrl() }}" 
                         alt="{{ $enr->course->title }}" 
                         class="w-100" 
                         style="height: 180px; object-fit: cover;"
                         onerror="this.onerror=null;this.src='{{ asset('images/course-default.svg') }}';">
                    <span class="position-absolute top-0 start-0 m-3 badge {{ $enr->course->levelBadgeClass() }} text-uppercase">
                        {{ $enr->course->level }}
                    </span>
                    <span class="position-absolute top-0 end-0 m-3 badge {{ $enr->statusBadgeClass() }} text-capitalize">
                        {{ $enr->status }}
                    </span>
                </div>

                <div class="card-body p-4 d-flex flex-column flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <img src="{{ $enr->course->instructor->avatarUrl() }}" alt="{{ $enr->course->instructor->name }}" class="rounded-circle" width="22" height="22" style="object-fit: cover;">
                        <small class="text-muted">{{ $enr->course->instructor->name }}</small>
                    </div>

                    <h5 class="font-serif fw-bold text-white mb-2">{{ $enr->course->title }}</h5>

                    <div class="mb-3 mt-auto">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Syllabus Progress</span>
                            <span class="text-gold fw-bold">{{ $enr->progress }}%</span>
                        </div>
                        <div class="progress-lux mb-1">
                            <div class="progress-bar {{ $enr->progress >= 100 ? 'bg-success' : 'bg-gold' }}" style="width: {{ $enr->progress }}%;"></div>
                        </div>
                    </div>

                    <div class="pt-3 border-top border-secondary d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            <i class="bi bi-play-circle text-gold me-1"></i> {{ $enr->course->lessons_count ?? $enr->course->lessons->count() }} Lessons
                        </span>
                        @if($enr->status === 'completed')
                            <span class="badge bg-success-subtle text-success border border-success">
                                <i class="bi bi-award-fill me-1"></i> Completed
                            </span>
                        @endif
                    </div>
                </div>

                <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0">
                    @if(in_array($enr->status, ['active', 'completed']))
                        <a href="{{ route('learning.course', $enr->course) }}" class="btn btn-gold w-100 fw-semibold shadow-sm">
                            <i class="bi bi-play-circle-fill me-1"></i> Enter Classroom
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
                            <a href="{{ route('checkout.show', $enr->course) }}" class="btn btn-warning w-100 text-dark fw-bold shadow-sm">
                                <i class="bi bi-lightning-charge-fill me-1"></i> Pay Tuition (KES {{ number_format($enr->course->fee, 2) }})
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card card-solid p-5 text-center">
                <i class="bi bi-music-note-beamed text-gold display-4 mb-3"></i>
                <h4 class="text-white font-serif">You have not enrolled in any masterclasses yet</h4>
                <p class="text-muted mb-4">Select an instrument course to begin studying with world-class faculty.</p>
                <div>
                    <a href="{{ route('courses.public.index') }}" class="btn btn-gold">
                        Browse Academy Catalog
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $enrollments->links('pagination::bootstrap-5') }}
</div>
@endsection
