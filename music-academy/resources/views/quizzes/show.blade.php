@extends('layouts.dashboard')

@section('title', 'Quiz: ' . $quiz->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('learning.course', $quiz->course) }}" class="btn btn-sm btn-outline-secondary text-white border-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="font-serif text-white fw-bold mb-0">{{ $quiz->title }}</h2>
                <span class="text-gold small">{{ $quiz->course->title }}</span>
            </div>
        </div>

        <!-- Quiz Details Card -->
        <div class="card card-solid p-4 mb-4">
            <div class="row g-3 text-center mb-4">
                <div class="col-sm-4">
                    <div class="bg-surface-elevated p-3 rounded">
                        <small class="text-muted d-block">Questions</small>
                        <strong class="fs-4 text-white font-serif">{{ $quiz->questions->count() }}</strong>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="bg-surface-elevated p-3 rounded">
                        <small class="text-muted d-block">Pass Mark</small>
                        <strong class="fs-4 text-gold font-serif">{{ $quiz->pass_mark }}%</strong>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="bg-surface-elevated p-3 rounded">
                        <small class="text-muted d-block">Time Limit</small>
                        <strong class="fs-4 text-white font-serif">{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' min' : 'Untimed' }}</strong>
                    </div>
                </div>
            </div>

            @if($quiz->description)
                <div class="text-light ql-editor mb-4" style="line-height: 1.8; padding: 0;">
                    {!! preg_replace('/<span class="ql-ui"[^>]*><\/span>/i', '', $quiz->description) !!}
                </div>
            @endif

            @if($bestAttempt)
                <div class="alert alert-{{ $bestAttempt->passed ? 'success' : 'warning' }}-subtle border-{{ $bestAttempt->passed ? 'success' : 'warning' }} text-{{ $bestAttempt->passed ? 'success' : 'warning' }}-emphasis mb-4">
                    <i class="bi bi-{{ $bestAttempt->passed ? 'check-circle-fill' : 'exclamation-triangle-fill' }} me-2"></i>
                    Your best attempt score: <strong>{{ $bestAttempt->score }}%</strong> ({{ $bestAttempt->passed ? 'Passed!' : 'Needs higher score to pass' }}).
                </div>
            @endif

            <div class="text-center">
                <a href="{{ route('quizzes.take', $quiz) }}" class="btn btn-gold btn-lg px-5">
                    <i class="bi bi-play-circle-fill me-1"></i> {{ $bestAttempt ? 'Retake Theory Quiz' : 'Start Theory Quiz' }}
                </a>
            </div>
        </div>

        <!-- Attempts History -->
        @if($attempts->count() > 0)
            <div class="card card-solid p-4">
                <h5 class="text-white font-serif fw-bold mb-3">Attempt History</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead class="text-muted small">
                            <tr>
                                <th>#</th>
                                <th>Score</th>
                                <th>Result</th>
                                <th>Date Taken</th>
                                <th class="text-end">Review</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attempts as $att)
                                <tr>
                                    <td>{{ $attempts->count() - $loop->index }}</td>
                                    <td class="fw-bold text-gold">{{ $att->score }}%</td>
                                    <td>
                                        <span class="badge bg-{{ $att->passed ? 'success' : 'danger' }}-subtle text-{{ $att->passed ? 'success' : 'danger' }}">
                                            {{ $att->passed ? 'Passed' : 'Failed' }}
                                        </span>
                                    </td>
                                    <td class="small text-muted">{{ $att->completed_at ? $att->completed_at->format('M j, Y · g:i A') : '—' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('quizzes.result', [$quiz, $att]) }}" class="btn btn-sm btn-outline-light border-secondary">
                                            <i class="bi bi-eye"></i> Answers
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
