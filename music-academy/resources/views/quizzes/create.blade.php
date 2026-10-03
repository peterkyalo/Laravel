@extends('layouts.dashboard')

@section('title', 'New Music Theory Quiz: ' . $course->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('courses.show.manage', $course) }}" class="btn btn-sm btn-outline-secondary text-white border-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="font-serif text-white fw-bold mb-0">Create Theory Quiz</h2>
                <span class="text-gold small">{{ $course->title }}</span>
            </div>
        </div>

        <div class="card card-solid p-4">
            <form action="{{ route('quizzes.store', $course) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Quiz Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="e.g. Harmonic Cadences & Interval Recognition">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Passing Grade Percentage (%) <span class="text-danger">*</span></label>
                        <input type="number" name="pass_mark" class="form-control" value="{{ old('pass_mark', 70) }}" min="1" max="100" required>
                        <small class="text-muted">Minimum percent required to pass this quiz.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Time Limit (Minutes)</label>
                        <input type="number" name="time_limit_minutes" class="form-control" value="{{ old('time_limit_minutes', 15) }}" min="1" max="180">
                        <small class="text-muted">Leave empty for untimed exam.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Instructions / Description</label>
                    <textarea name="description" rows="4" class="form-control" placeholder="Test your recognition of dominant 7th chords, circle of fifths, and voice leading...">{{ old('description') }}</textarea>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_required" id="is_required" value="1" {{ old('is_required', true) ? 'checked' : '' }}>
                    <label class="form-check-label text-white" for="is_required">
                        Required for Certificate (Student must pass this quiz to earn their diploma)
                    </label>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}>
                    <label class="form-check-label text-white" for="is_published">
                        Publish immediately (Visible to students)
                    </label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('courses.show.manage', $course) }}" class="btn btn-outline-secondary text-white">Cancel</a>
                    <button type="submit" class="btn btn-gold">
                        <i class="bi bi-arrow-right me-1"></i> Create Quiz & Add Questions
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
