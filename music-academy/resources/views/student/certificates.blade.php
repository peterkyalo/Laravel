@extends('layouts.dashboard')

@section('title', 'My Diplomas & Certificates — Baritone Music Academy')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <h2 class="font-serif text-white fw-bold mb-1">Graduation Certificates</h2>
        <p class="text-muted small">Your verified credentials earned upon masterclass completion.</p>
    </div>

    @forelse($certificates as $cert)
        <div class="col-md-6 col-lg-4">
            <div class="card card-glass h-100 p-4 d-flex flex-column border-gold">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge bg-gold p-2 rounded-circle">
                        <i class="bi bi-award-fill text-dark fs-4"></i>
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success">Verified</span>
                </div>

                <h5 class="font-serif text-white fw-bold mb-2">{{ $cert->enrollment->course->title }}</h5>
                <div class="text-muted small mb-1">
                    <i class="bi bi-person text-gold me-1"></i> Instructor: {{ $cert->enrollment->course->instructor->name }}
                </div>
                <div class="text-muted small mb-3">
                    <i class="bi bi-calendar text-gold me-1"></i> Awarded: {{ $cert->issued_at->format('M d, Y') }}
                </div>

                <div class="bg-surface-elevated p-2 rounded text-center font-monospace text-gold small mb-4">
                    {{ $cert->code }}
                </div>

                <div class="mt-auto d-flex gap-2">
                    <a href="{{ route('certificates.show', $cert) }}" class="btn btn-outline-gold btn-sm flex-grow-1">
                        <i class="bi bi-eye me-1"></i> View Diploma
                    </a>
                    <a href="{{ route('certificates.download', $cert) }}" class="btn btn-gold btn-sm">
                        <i class="bi bi-download"></i> PDF
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card card-solid p-5 text-center">
                <i class="bi bi-award text-muted display-4 mb-3"></i>
                <h4 class="text-white font-serif">No Certificates Earned Yet</h4>
                <p class="text-muted mb-4">Complete 100% of your course lessons and pass required music theory quizzes to automatically receive your verified diploma.</p>
                <div>
                    <a href="{{ route('student.courses') }}" class="btn btn-gold">
                        Continue My Studies
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>
@endsection
