@extends('layouts.dashboard')

@section('title', 'Grade Submissions: ' . $assignment->title)

@section('content')
<!-- Include HTML MIDI Player web component scripts for instructor audio playback -->
<script src="https://cdn.jsdelivr.net/combine/npm/tone@14.7.58,npm/@magenta/music@1.23.1/es6/core.js,npm/focus-visible@5,npm/html-midi-player@1.5.0"></script>

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('assignments.index') }}" class="btn btn-sm btn-outline-secondary text-white border-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">{{ $assignment->title }}</h2>
        <span class="text-gold small">{{ $assignment->course->title }}</span>
    </div>
</div>

<!-- Assignment Info Banner -->
<div class="card card-solid p-4 mb-4">
    <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
        <div class="text-muted small">
            <span><i class="bi bi-trophy text-gold me-1"></i> Max Points: {{ $assignment->max_score }}</span> ·
            <span><i class="bi bi-calendar text-gold me-1"></i> Due: {{ $assignment->due_at ? $assignment->due_at->format('M j, Y') : 'None' }}</span>
        </div>
        <span class="badge bg-surface-elevated text-gold border border-secondary">
            {{ $assignment->submissions->count() }} Total Submissions
        </span>
    </div>
    <div class="text-light mt-3 pt-3 border-top border-secondary">
        <h6 class="text-gold fw-bold mb-2"><i class="bi bi-file-text me-1"></i> Assignment Instructions:</h6>
        <div class="ql-editor p-0" style="line-height: 1.8;">
            {!! preg_replace('/<span class="ql-ui"[^>]*><\/span>/i', '', $assignment->instructions) !!}
        </div>
    </div>
</div>

<!-- Student Submissions Evaluation Queue -->
<div class="card card-solid p-4">
    <h5 class="text-white font-serif fw-bold mb-3">Student Performance Submissions</h5>

    @forelse($assignment->submissions as $sub)
        <div class="card card-glass p-4 mb-4 border-secondary shadow-sm">
            <div class="row g-4 align-items-start">
                <!-- Left Column: Student Info, Notes & Performance Recording -->
                <div class="col-lg-7">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $sub->student->avatarUrl() }}" alt="Avatar" class="rounded-circle border border-gold" width="42" height="42" style="object-fit: cover;">
                            <div>
                                <div class="fw-bold text-white fs-6">{{ $sub->student->name }}</div>
                                <small class="text-muted" style="font-size: 0.75rem;">{{ $sub->student->email }}</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <small class="text-gold d-block fw-semibold" style="font-size: 0.75rem;">
                                <i class="bi bi-clock me-1"></i> Submitted {{ $sub->created_at->format('M j, Y · g:i A') }}
                            </small>
                            @if($sub->isGraded())
                                <span class="badge bg-success-subtle text-success border border-success mt-1">
                                    <i class="bi bi-check-circle-fill me-1"></i> Graded ({{ $sub->score }}/{{ $assignment->max_score }})
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning mt-1">
                                    <i class="bi bi-hourglass-split me-1"></i> Pending Critique
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($sub->notes)
                        <div class="mb-3">
                            <label class="form-label text-gold small fw-bold mb-1">
                                <i class="bi bi-chat-left-text me-1"></i> Student Practice Notes:
                            </label>
                            <div class="bg-surface-elevated p-2.5 rounded text-light small fst-italic border border-secondary" style="font-size: 0.82rem; line-height: 1.6;">
                                "{{ $sub->notes }}"
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="form-label text-white small fw-bold mb-1">
                            <i class="bi bi-play-circle me-1 text-gold"></i> Submission Performance Take:
                        </label>
                        @if($sub->mediaType() === 'audio')
                            @if($sub->isMidi())
                                <midi-player src="{{ route('serve.file', ['path' => $sub->file_path], false) }}" sound-font style="width: 100%; height: 40px;"></midi-player>
                            @else
                                <audio controls class="w-100" style="height: 40px;">
                                    <source src="{{ $sub->fileUrl() }}">
                                </audio>
                            @endif
                        @elseif($sub->mediaType() === 'video')
                            <video controls class="w-100 rounded border border-secondary" style="max-height: 220px; background: #000;">
                                <source src="{{ $sub->fileUrl() }}">
                            </video>
                        @else
                            <a href="{{ $sub->fileUrl() }}" target="_blank" class="btn btn-outline-light btn-sm border-secondary w-100 py-2">
                                <i class="bi bi-file-earmark-arrow-down me-1 text-gold"></i> Download {{ $sub->original_name ?? 'Recording File' }}
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Right Column: Instructor Grade & Critique Form -->
                <div class="col-lg-5">
                    <div class="bg-surface-elevated p-3 rounded-3 border border-secondary h-100">
                        <h6 class="text-gold fw-bold mb-3 d-flex align-items-center gap-2" style="font-size: 0.9rem;">
                            <i class="bi bi-pencil-square"></i> Instructor Evaluation & Critique
                        </h6>
                        <form action="{{ route('submissions.grade', $sub) }}" method="POST">
                            @csrf

                            <!-- Score Input Field -->
                            <div class="mb-3">
                                <label class="form-label text-light small fw-bold">Award Score</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark text-gold border-secondary fw-bold px-3">
                                        <i class="bi bi-award me-1"></i> Score
                                    </span>
                                    <input type="number" name="score" class="form-control bg-dark text-white border-secondary fs-6 fw-bold text-center" min="0" max="{{ $assignment->max_score }}" value="{{ old('score', $sub->score) }}" required placeholder="0 - {{ $assignment->max_score }}">
                                    <span class="input-group-text bg-dark text-muted border-secondary fw-semibold">
                                        / {{ $assignment->max_score }} pts
                                    </span>
                                </div>
                            </div>

                            <!-- Feedback Textarea Input Field -->
                            <div class="mb-3">
                                <label class="form-label text-light small fw-bold">Detailed Critique & Guidance</label>
                                <textarea name="feedback" rows="5" class="form-control bg-dark text-white border-secondary p-3" style="min-height: 110px; line-height: 1.6; font-size: 0.88rem; resize: vertical;" placeholder="Provide constructive evaluation on tempo accuracy, finger technique, dynamics, pedaling, and phrasing..." required>{{ old('feedback', $sub->feedback) }}</textarea>
                            </div>

                            <!-- Action Submit Button -->
                            <button type="submit" class="btn btn-gold w-100 py-2 fw-semibold shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> {{ $sub->isGraded() ? 'Update Instructor Critique' : 'Submit Grade & Critique' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-4 text-muted small">
            No students have submitted practice recordings for this assignment yet.
        </div>
    @endforelse
</div>
@endsection
