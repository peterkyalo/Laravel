@extends('layouts.dashboard')

@section('title', 'Grade Submissions: ' . $assignment->title)

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
    <div class="text-light small mt-2">
        <strong class="text-white">Instructions:</strong> {{ $assignment->instructions }}
    </div>
</div>

<!-- Student Submissions Evaluation Queue -->
<div class="card card-solid p-4">
    <h5 class="text-white font-serif fw-bold mb-3">Student Performance Submissions</h5>

    @forelse($assignment->submissions as $sub)
        <div class="card card-glass p-3 mb-3 border-secondary">
            <div class="row align-items-center g-3">
                <!-- Student Info -->
                <div class="col-md-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <img src="{{ $sub->student->avatarUrl() }}" alt="Avatar" class="rounded-circle" width="34" height="34" style="object-fit: cover;">
                        <div>
                            <div class="fw-bold text-white small">{{ $sub->student->name }}</div>
                            <small class="text-muted" style="font-size: 0.72rem;">{{ $sub->student->email }}</small>
                        </div>
                    </div>
                    <small class="text-muted d-block" style="font-size: 0.7rem;">
                        <i class="bi bi-clock me-1"></i> {{ $sub->created_at->format('M j, g:i A') }}
                    </small>
                </div>

                <!-- Media Player & Student Notes -->
                <div class="col-md-5">
                    @if($sub->notes)
                        <div class="bg-surface-elevated p-2 rounded small text-light mb-2 fst-italic" style="font-size: 0.75rem;">
                            "{{ $sub->notes }}"
                        </div>
                    @endif

                    @if($sub->mediaType() === 'audio')
                        <audio controls class="w-100" style="height: 38px;">
                            <source src="{{ $sub->fileUrl() }}">
                        </audio>
                    @elseif($sub->mediaType() === 'video')
                        <video controls class="w-100 rounded" style="max-height: 160px; background: #000;">
                            <source src="{{ $sub->fileUrl() }}">
                        </video>
                    @else
                        <a href="{{ $sub->fileUrl() }}" target="_blank" class="btn btn-outline-light btn-sm border-secondary">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i> Download {{ $sub->original_name ?? 'Recording' }}
                        </a>
                    @endif
                </div>

                <!-- Grade & Feedback Form -->
                <div class="col-md-4">
                    <form action="{{ route('submissions.grade', $sub) }}" method="POST">
                        @csrf
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-surface-elevated text-gold border-secondary">Score</span>
                            <input type="number" name="score" class="form-control" min="0" max="{{ $assignment->max_score }}" value="{{ old('score', $sub->score) }}" required placeholder="0 - {{ $assignment->max_score }}">
                            <span class="input-group-text bg-surface-elevated text-muted border-secondary">/ {{ $assignment->max_score }}</span>
                        </div>

                        <div class="mb-2">
                            <textarea name="feedback" rows="2" class="form-control form-control-sm" placeholder="Instructor feedback on tempo, touch, rhythm, dynamics..." required>{{ old('feedback', $sub->feedback) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-gold btn-sm w-100">
                            <i class="bi bi-check-lg me-1"></i> {{ $sub->isGraded() ? 'Update Critique' : 'Submit Grade & Critique' }}
                        </button>
                    </form>
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
