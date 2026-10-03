@extends('layouts.dashboard')

@section('title', 'Manage Quiz: ' . $quiz->title)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('courses.show.manage', $quiz->course) }}" class="btn btn-sm btn-outline-secondary text-white border-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h2 class="font-serif text-white fw-bold mb-0">{{ $quiz->title }}</h2>
            <span class="text-gold small">{{ $quiz->course->title }}</span>
        </div>
    </div>

    <div class="d-flex gap-2 align-items-center">
        <form action="{{ route('quizzes.publish.toggle', $quiz) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-sm btn-{{ $quiz->is_published ? 'success' : 'secondary' }}">
                <i class="bi bi-broadcast me-1"></i> {{ $quiz->is_published ? 'Published' : 'Draft' }}
            </button>
        </form>
        <a href="{{ route('quizzes.show', $quiz) }}" class="btn btn-outline-gold btn-sm" target="_blank">
            <i class="bi bi-eye me-1"></i> Preview as Student
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Existing Questions List -->
    <div class="col-lg-7">
        <div class="card card-solid p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-serif fw-bold mb-0">Quiz Questions ({{ $quiz->questions->count() }})</h5>
                <span class="badge bg-surface-elevated text-gold border border-secondary">Pass Mark: {{ $quiz->pass_mark }}%</span>
            </div>

            @forelse($quiz->questions as $q)
                <div class="card card-glass p-3 mb-3 border-secondary">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="text-white fw-bold mb-0">
                            <span class="text-gold me-1">{{ $loop->iteration }}.</span> {{ $q->text }}
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-surface-elevated text-muted small">{{ $q->points }} {{ Str::plural('pt', $q->points) }}</span>
                            <form action="{{ route('quizzes.questions.delete', [$quiz, $q]) }}" method="POST" onsubmit="return confirm('Delete this question?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger p-0 px-2" style="font-size: 0.75rem;"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>

                    <div class="list-group list-group-flush border-top border-secondary pt-2">
                        @foreach($q->options as $opt)
                            <div class="list-group-item bg-transparent text-light px-0 py-1 border-0 small d-flex align-items-center gap-2">
                                @if($opt->is_correct)
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <strong class="text-success">{{ $opt->text }}</strong>
                                @else
                                    <i class="bi bi-circle text-muted"></i>
                                    <span>{{ $opt->text }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted small">
                    No questions added yet. Use the form on the right to add multiple-choice questions.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Add Question Form -->
    <div class="col-lg-5">
        <div class="card card-solid p-4 sticky-top" style="top: 90px;">
            <h5 class="text-white font-serif fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle text-gold"></i> Add Multiple Choice Question
            </h5>

            <form action="{{ route('quizzes.questions.add', $quiz) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Question Text <span class="text-danger">*</span></label>
                    <textarea name="text" rows="3" class="form-control form-control-sm" required placeholder="e.g. Which cadence concludes on the dominant (V) chord?"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Points Value</label>
                    <input type="number" name="points" class="form-control form-control-sm" value="1" min="1" max="50" required>
                </div>

                <label class="form-label">Answer Choices <span class="text-danger">*</span></label>
                <div class="text-muted small mb-2" style="font-size: 0.75rem;">Select the radio button beside the correct choice:</div>

                @for($i = 0; $i < 4; $i++)
                    <div class="input-group input-group-sm mb-2">
                        <div class="input-group-text bg-surface-elevated border-secondary">
                            <input class="form-check-input mt-0" type="radio" name="correct_option" value="{{ $i }}" {{ $i === 0 ? 'checked' : '' }} title="Mark as correct">
                        </div>
                        <input type="text" name="options[]" class="form-control" placeholder="Option {{ chr(65 + $i) }}" required>
                    </div>
                @endfor

                <button type="submit" class="btn btn-gold btn-sm w-100 mt-3">
                    <i class="bi bi-plus-lg me-1"></i> Save Question
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
