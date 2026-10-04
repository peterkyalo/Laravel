@extends('layouts.classroom')

@section('title', 'Exam: ' . $quiz->title)

@section('content')
<div class="container py-4 my-auto" style="max-width: 800px;">

    <!-- Top Banner -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
        <div>
            <span class="badge bg-gold small mb-1">Academy Assessment</span>
            <h3 class="font-serif text-white fw-bold mb-0">{{ $quiz->title }}</h3>
            <small class="text-muted">{{ $quiz->course->title }} · {{ $quiz->questions->count() }} Questions</small>
        </div>
        <a href="{{ route('quizzes.show', $quiz) }}" class="btn btn-outline-secondary btn-sm text-white border-secondary" onclick="return confirm('Cancel this attempt?')">
            <i class="bi bi-x-lg"></i> Cancel
        </a>
    </div>

    <form action="{{ route('quizzes.submit', $quiz) }}" method="POST">
        @csrf

        @foreach($quiz->questions as $q)
            <div class="card card-solid p-4 mb-4">
                <div class="d-flex align-items-baseline gap-2 mb-3">
                    <span class="badge bg-surface-elevated text-gold fs-6 border border-secondary flex-shrink-0">{{ $loop->iteration }}</span>
                    <div class="text-white fw-semibold mb-0 font-serif fs-5" style="color: #ffffff !important;">
                        {!! preg_replace('/<span class="ql-ui"[^>]*><\/span>/i', '', $q->text) !!}
                    </div>
                </div>

                <div class="list-group">
                    @foreach($q->options as $opt)
                        <label class="list-group-item bg-surface-elevated text-white border-secondary p-3 mb-2 rounded cursor-pointer d-flex align-items-center gap-3">
                            <input class="form-check-input mt-0 fs-5 flex-shrink-0" type="radio" name="answers[{{ $q->id }}]" value="{{ $opt->id }}" required>
                            <div class="text-white fw-medium flex-grow-1" style="color: #ffffff !important;">
                                {!! preg_replace('/<span class="ql-ui"[^>]*><\/span>/i', '', $opt->text) !!}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="card card-glass p-4 text-center border-gold mb-5">
            <p class="text-muted small mb-3">Ensure you have selected an answer for all questions before submitting.</p>
            <button type="submit" class="btn btn-gold btn-lg px-5 mx-auto" onclick="return confirm('Ready to submit your examination?')">
                <i class="bi bi-check2-circle me-1"></i> Submit Examination for Grading
            </button>
        </div>
    </form>

</div>

@push('styles')
<style>
.cursor-pointer { cursor: pointer; }
.list-group-item:hover { background-color: rgba(255,255,255,0.06) !important; }
</style>
@endpush
@endsection
