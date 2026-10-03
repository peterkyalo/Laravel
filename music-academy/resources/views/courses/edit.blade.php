@extends('layouts.dashboard')

@section('title', 'Edit Course: ' . $course->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">

        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('courses.show.manage', $course) }}" class="btn btn-sm btn-outline-secondary text-white border-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="font-serif text-white fw-bold mb-0">Edit Course Settings</h2>
        </div>

        <div class="card card-solid p-4">
            <form action="{{ route('courses.update', $course) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Course Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $course->title) }}" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Instrument / Discipline <span class="text-danger">*</span></label>
                        <select name="instrument_id" class="form-select" required>
                            @foreach($instruments as $inst)
                                <option value="{{ $inst->id }}" {{ old('instrument_id', $course->instrument_id) == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Proficiency Level <span class="text-danger">*</span></label>
                        <select name="level" class="form-select" required>
                            @foreach(\App\Models\Course::LEVELS as $lvl)
                                <option value="{{ $lvl }}" {{ old('level', $course->level) == $lvl ? 'selected' : '' }}>{{ ucfirst($lvl) }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if(auth()->user()->isAdmin())
                        <div class="col-md-4">
                            <label class="form-label">Assigned Instructor <span class="text-danger">*</span></label>
                            <select name="instructor_id" class="form-select" required>
                                @foreach($instructors as $inst)
                                    <option value="{{ $inst->id }}" {{ old('instructor_id', $course->instructor_id) == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="col-md-4">
                            <label class="form-label">Tuition Fee (USD) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-surface-elevated text-gold border-secondary">$</span>
                                <input type="number" step="0.01" min="0" name="fee" class="form-control" value="{{ old('fee', $course->fee) }}" required>
                            </div>
                        </div>
                    @endif
                </div>

                @if(auth()->user()->isAdmin())
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tuition Fee (USD) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-surface-elevated text-gold border-secondary">$</span>
                                <input type="number" step="0.01" min="0" name="fee" class="form-control" value="{{ old('fee', $course->fee) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Duration (Weeks)</label>
                            <input type="number" min="1" max="52" name="duration_weeks" class="form-control" value="{{ old('duration_weeks', $course->duration_weeks) }}">
                        </div>
                    </div>
                @else
                    <div class="mb-3">
                        <label class="form-label">Duration (Weeks)</label>
                        <input type="number" min="1" max="52" name="duration_weeks" class="form-control" value="{{ old('duration_weeks', $course->duration_weeks) }}">
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Short Description</label>
                    <input type="text" name="short_description" class="form-control" value="{{ old('short_description', $course->short_description) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span>Full Masterclass Syllabus / Overview</span>
                        <span class="badge bg-gold text-dark small"><i class="bi bi-pen-fill me-1"></i> Rich Text</span>
                    </label>
                    <textarea name="description" rows="5" class="form-control richtext">{{ old('description', $course->description) }}</textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label">Cover Artwork</label>
                        @if($course->cover_image)
                            <div class="mb-2">
                                <img src="{{ $course->coverUrl() }}" alt="Current Cover" height="60" class="rounded border border-secondary">
                            </div>
                        @endif
                        <input type="file" name="cover_image" class="form-control image-preview-input" accept="image/*">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Publishing Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="draft" {{ old('status', $course->status) == 'draft' ? 'selected' : '' }}>Draft (Private)</option>
                            <option value="published" {{ old('status', $course->status) == 'published' ? 'selected' : '' }}>Published (Public)</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('courses.show.manage', $course) }}" class="btn btn-outline-secondary text-white">Cancel</a>
                    <button type="submit" class="btn btn-gold">
                        <i class="bi bi-save me-1"></i> Update Settings
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
