@extends('layouts.dashboard')

@section('title', 'Practice Etude: ' . $assignment->title)

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('assignments.index') }}" class="btn btn-sm btn-outline-secondary text-white border-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">{{ $assignment->title }}</h2>
        <span class="text-gold small">{{ $assignment->course->title }}</span>
    </div>
</div>

<div class="row g-4">
    <!-- Assignment Instructions -->
    <div class="col-lg-7">
        <div class="card card-solid p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-surface-elevated text-gold border border-secondary">Etude Instructions</span>
                <div class="text-muted small">
                    <span><i class="bi bi-trophy text-gold me-1"></i> Max: {{ $assignment->max_score }} pts</span>
                    @if($assignment->due_at) · <span><i class="bi bi-calendar text-gold me-1"></i> Due {{ $assignment->due_at->format('M j, Y') }}</span> @endif
                </div>
            </div>

            <div class="text-light" style="line-height: 1.8;">
                {!! nl2br(e($assignment->instructions)) !!}
            </div>
        </div>

        <!-- Instructor Feedback (if graded) -->
        @if($submission && $submission->isGraded())
            <div class="card card-glass p-4 border-gold">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-white font-serif fw-bold mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-mortarboard-fill text-gold"></i> Faculty Evaluation
                    </h5>
                    <span class="badge bg-gold text-dark fs-6 px-3 py-1">
                        {{ $submission->score }} / {{ $assignment->max_score }} Points
                    </span>
                </div>
                <div class="text-light fst-italic p-3 bg-surface-elevated rounded border border-secondary mb-2">
                    "{{ $submission->feedback }}"
                </div>
                <small class="text-muted">
                    Graded by {{ $submission->grader->name ?? 'Instructor' }} on {{ $submission->graded_at->format('M j, Y · g:i A') }}
                </small>
            </div>
        @endif
    </div>

    <!-- Practice Recording Upload Card -->
    <div class="col-lg-5">
        <div class="card card-solid p-4">
            <h5 class="text-white font-serif fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-mic text-gold"></i> Submit Performance Recording
            </h5>

            @if($submission)
                <div class="alert alert-info bg-surface-elevated border-secondary text-light small mb-3">
                    <i class="bi bi-info-circle me-1 text-gold"></i> You uploaded a recording on {{ $submission->created_at->format('M j, Y · g:i A') }}.
                    @if($submission->isGraded())
                        <strong class="text-success d-block mt-1">This submission has been graded. You may submit a new take to improve your technique.</strong>
                    @else
                        <span class="text-warning d-block mt-1">Your instructor has not yet graded this take. Submitting again will update your file.</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label small text-muted">Your Current Recording Take</label>
                    @if($submission->mediaType() === 'audio')
                        <audio controls class="w-100">
                            <source src="{{ $submission->fileUrl() }}">
                        </audio>
                    @elseif($submission->mediaType() === 'video')
                        <video controls class="w-100 rounded" style="max-height: 180px; background: #000;">
                            <source src="{{ $submission->fileUrl() }}">
                        </video>
                    @endif
                </div>
            @endif

            <form action="{{ route('assignments.submit', $assignment) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Upload Audio or Video File <span class="text-danger">*</span></label>
                    <input type="file" name="recording" class="form-control" required accept="audio/*,video/mp4,video/webm,video/quicktime,.pdf">
                    <small class="text-muted">MP3, WAV, M4A, or MP4 video (Max 50MB).</small>
                </div>

                <div class="mb-4">
                    <label class="form-label">Practice Notes / Questions for Instructor</label>
                    <textarea name="notes" rows="3" class="form-control" placeholder="Mention tempo attempted, measures where you felt finger tension, pedaling questions...">{{ old('notes', $submission?->notes) }}</textarea>
                </div>

                <button type="submit" class="btn btn-gold w-100">
                    <i class="bi bi-upload me-1"></i> {{ $submission ? 'Upload New Performance Take' : 'Submit Performance Recording' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
