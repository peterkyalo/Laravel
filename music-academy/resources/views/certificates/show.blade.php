@extends('layouts.app')

@section('title', 'Certificate of Completion — ' . $certificate->code)

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm text-white border-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-gold btn-sm">
                <i class="bi bi-printer me-1"></i> Print Diploma
            </button>
            <a href="{{ route('certificates.download', $certificate) }}" class="btn btn-gold btn-sm">
                <i class="bi bi-download me-1"></i> Download PDF
            </a>
        </div>
    </div>

    <!-- Luxury Certificate Frame -->
    <div class="card certificate-frame mx-auto" style="max-width: 900px;">
        <div class="text-center">
            <div class="mb-3">
                <i class="bi bi-music-note-beamed text-gold fs-1"></i>
            </div>
            <h5 class="text-uppercase tracking-wide text-muted fw-bold mb-1" style="letter-spacing: 0.25em;">Baritone Music Academy</h5>
            <div class="text-muted small mb-4">CONSERVATORY OF CLASSICAL & CONTEMPORARY MUSIC</div>

            <h1 class="font-serif display-4 fw-bold mb-3" style="color: #1a237e;">Certificate of Mastery</h1>
            <p class="fst-italic text-secondary fs-5 mb-3">This document hereby certifies that</p>

            <h2 class="display-5 font-serif fw-bold text-dark border-bottom border-warning pb-2 d-inline-block px-4 mb-4">
                {{ $certificate->enrollment->user->name }}
            </h2>

            <p class="text-secondary mx-auto mb-4" style="max-width: 620px; font-size: 1.05rem;">
                has successfully fulfilled all conservatory requirements, masterclass etudes, performance submissions, and harmonic theory assessments for
            </p>

            <h3 class="font-serif fw-bold mb-4" style="color: #b45309;">
                {{ $certificate->enrollment->course->title }}
            </h3>

            <div class="row align-items-end mt-5 pt-4 text-center">
                <div class="col-4">
                    <div class="border-bottom border-dark pb-1 fw-bold font-serif">
                        {{ $certificate->enrollment->course->instructor->name }}
                    </div>
                    <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Course Instructor</small>
                </div>
                <div class="col-4">
                    <div class="rounded-circle border border-warning d-inline-flex align-items-center justify-content-center p-3" style="width: 80px; height: 80px; background: rgba(245, 158, 11, 0.08);">
                        <i class="bi bi-patch-check-fill text-gold fs-1"></i>
                    </div>
                    <div class="small fw-bold text-dark mt-1">SEAL OF MASTERY</div>
                </div>
                <div class="col-4">
                    <div class="border-bottom border-dark pb-1 fw-bold font-serif">
                        {{ $certificate->issued_at->format('F d, Y') }}
                    </div>
                    <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Date of Conferral</small>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top text-muted small font-monospace" style="font-size: 0.75rem;">
                Credential Identifier: <strong>{{ $certificate->code }}</strong> · Verify online at {{ route('certificates.verify', ['code' => $certificate->code]) }}
            </div>
        </div>
    </div>
</div>
@endsection
