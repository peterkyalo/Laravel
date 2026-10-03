@extends('layouts.dashboard')

@section('title', 'New Assignment: ' . $course->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('courses.show.manage', $course) }}" class="btn btn-sm btn-outline-secondary text-white border-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="font-serif text-white fw-bold mb-0">Create Practice Assignment</h2>
                <span class="text-gold small">{{ $course->title }}</span>
            </div>
        </div>

        <div class="card card-solid p-4">
            <form action="{{ route('assignments.store', $course) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Assignment Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="e.g. Nocturne Op. 9 No. 2: Measures 1-16 Rubato Recording">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Linked Lesson (Optional)</label>
                        <select name="lesson_id" class="form-select">
                            <option value="">General Course Assignment</option>
                            @foreach($lessons as $l)
                                <option value="{{ $l->id }}" {{ old('lesson_id') == $l->id ? 'selected' : '' }}>
                                    Lesson {{ $l->position }}: {{ $l->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Max Score <span class="text-danger">*</span></label>
                        <input type="number" name="max_score" class="form-control" value="{{ old('max_score', 100) }}" min="10" max="1000" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_at" class="form-control" value="{{ old('due_at') }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Instructions & Rubric for Students <span class="text-danger">*</span></label>
                    <textarea name="instructions" rows="6" class="form-control" required placeholder="Specify tempo (BPM), phrasing expectations, dynamics, fingering adherence, and whether audio or video recording is preferred...">{{ old('instructions') }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('courses.show.manage', $course) }}" class="btn btn-outline-secondary text-white">Cancel</a>
                    <button type="submit" class="btn btn-gold">
                        <i class="bi bi-check-lg me-1"></i> Save Assignment
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
