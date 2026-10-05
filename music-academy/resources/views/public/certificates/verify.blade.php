@extends('layouts.app')

@section('title', 'Verify Certificate — Baritone Music Academy')

@section('content')
<div class="py-5">
    <div class="container py-lg-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">

                <span class="badge bg-gold p-3 rounded-circle mb-3"><i class="bi bi-award text-dark fs-3"></i></span>
                <h1 class="display-5 font-serif text-white fw-bold mb-2">Certificate Verification</h1>
                <p class="text-muted mx-auto mb-4" style="max-width: 560px;">
                    Enter the unique credential identifier found at the bottom of any Baritone Academy diploma to verify its authenticity.
                </p>

                <!-- Search Card -->
                <div class="card card-glass p-4 text-start mb-5">
                    <form action="{{ route('certificates.verify') }}" method="GET">
                        <label for="code" class="form-label text-white">Certificate Identification Code</label>
                        <div class="input-group">
                            <span class="input-group-text bg-surface-elevated text-gold border-secondary"><i class="bi bi-shield-check"></i></span>
                            <input type="text" id="code" name="code" class="form-control text-uppercase font-monospace" placeholder="e.g. HMA-9X72-KL41-Z89B" value="{{ request('code') }}" required>
                            <button type="submit" class="btn btn-gold px-4">
                                <i class="bi bi-search me-1"></i> Verify
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Result Section -->
                @if($searched)
                    @if($certificate)
                        <div class="card card-glass p-4 text-start border-success position-relative overflow-hidden">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="badge bg-success-subtle text-success p-2 rounded-circle fs-4">
                                    <i class="bi bi-patch-check-fill"></i>
                                </span>
                                <div>
                                    <h4 class="text-white font-serif fw-bold mb-0">Official Credential Verified</h4>
                                    <small class="text-success fw-semibold">Authenticated Record in Academy Registry</small>
                                </div>
                            </div>

                            <div class="row g-3 py-3 border-top border-bottom border-secondary mb-3">
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Graduate</small>
                                    <strong class="text-white fs-5">{{ $certificate->enrollment->user->name }}</strong>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Masterclass Course</small>
                                    <strong class="text-gold fs-5">{{ $certificate->enrollment->course->title }}</strong>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Instructor</small>
                                    <span class="text-light">{{ $certificate->enrollment->course->instructor->name }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Date of Convocaton</small>
                                    <span class="text-light">{{ $certificate->issued_at->format('F d, Y') }}</span>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">Unique Certificate Code</small>
                                    <code class="text-gold fs-6 font-monospace">{{ $certificate->code }}</code>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('certificates.show', $certificate) }}" class="btn btn-gold btn-sm">
                                    <i class="bi bi-eye me-1"></i> View Diploma
                                </a>
                                <a href="{{ route('certificates.download', $certificate) }}" class="btn btn-outline-light btn-sm border-secondary">
                                    <i class="bi bi-download me-1"></i> Download PDF
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-danger py-4 text-center">
                            <i class="bi bi-x-circle display-5 d-block mb-2 text-danger"></i>
                            <h5 class="fw-bold">No Matching Certificate Found</h5>
                            <p class="mb-0 small">The code <strong>{{ request('code') }}</strong> does not exist in our graduation records. Please verify the characters and try again.</p>
                        </div>
                    @endif
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
