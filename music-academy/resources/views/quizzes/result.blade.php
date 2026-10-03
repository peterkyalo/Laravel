@extends('layouts.dashboard')

@section('title', 'Examination Results: ' . $quiz->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <!-- Score Banner -->
        <div class="card card-glass p-5 text-center mb-4 {{ $attempt->passed ? 'border-success' : 'border-warning' }}">
            <div class="mb-3">
                <i class="bi bi-{{ $attempt->passed ? 'check-circle-fill text-success' : 'exclamation-circle-fill text-warning' }} display-3"></i>
            </div>
            <h2 class="font-serif text-white fw-bold mb-1">
                {{ $attempt->passed ? 'Examination Passed!' : 'Needs Practice' }}
            </h2>
            <p class="text-muted small mb-3">{{ $quiz->title }} · {{ $quiz->course->title }}</p>

            <div class="d-inline-flex align-items-baseline gap-2 px-4 py-2 rounded-pill bg-surface-elevated border border-secondary mb-3">
                <span class="display-5 font-serif fw-bold text-gold">{{ $attempt->score }}%</span>
                <span class="text-muted small">/ Pass Mark: {{ $quiz->pass_mark }}%</span>
            </div>

            @if($attempt->passed)
                <p class="text-success small mb-4">Bravo! You have demonstrated harmonic mastery of this theory module.</p>
            @else
                <p class="text-warning small mb-4">You did not achieve the required {{ $quiz->pass_mark }}% pass mark. Review the explanations below and retake when ready.</p>
            @endif

            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('learning.course', $quiz->course) }}" class="btn btn-gold btn-sm">
                    <i class="bi bi-play-circle me-1"></i> Return to Classroom
                </a>
                <a href="{{ route('quizzes.take', $quiz) }}" class="btn btn-outline-light btn-sm border-secondary">
                    <i class="bi bi-arrow-repeat me-1"></i> Retake Exam
                </a>
            </div>
        </div>

        <!-- Answers Review -->
        <div class="card card-solid p-4">
            <h5 class="text-white font-serif fw-bold mb-3">Examination Answer Key & Review</h5>

            @php $userAnswers = $attempt->answers ?? []; @endphp

            @foreach($quiz->questions as $q)
                @php
                    $chosenOptionId = $userAnswers[$q->id] ?? null;
                    $correctOption = $q->options->firstWhere('is_correct', true);
                    $isCorrect = $correctOption && $correctOption->id === (int)$chosenOptionId;
                @endphp
                <div class="p-3 mb-3 rounded bg-surface-elevated border {{ $isCorrect ? 'border-success' : 'border-danger' }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <strong class="text-white small">
                            <span class="text-gold me-1">{{ $loop->iteration }}.</span> {{ $q->text }}
                        </strong>
                        <span class="badge bg-{{ $isCorrect ? 'success' : 'danger' }}-subtle text-{{ $isCorrect ? 'success' : 'danger' }}">
                            {{ $isCorrect ? 'Correct' : 'Incorrect' }}
                        </span>
                    </div>

                    <div class="list-group list-group-flush border-top border-secondary pt-2">
                        @foreach($q->options as $opt)
                            @php
                                $wasSelected = ((int)$opt->id === (int)$chosenOptionId);
                            @endphp
                            <div class="list-group-item bg-transparent px-0 py-1 border-0 small d-flex align-items-center gap-2">
                                @if($opt->is_correct)
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <span class="text-success fw-bold">{{ $opt->text }} (Correct Answer)</span>
                                @elseif($wasSelected)
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                    <span class="text-danger">{{ $opt->text }} (Your Selection)</span>
                                @else
                                    <i class="bi bi-circle text-muted"></i>
                                    <span class="text-muted">{{ $opt->text }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>
@endsection
