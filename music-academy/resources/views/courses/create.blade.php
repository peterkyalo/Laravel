@extends('layouts.dashboard')

@section('title', 'Create New Masterclass — Baritone Music Academy')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">

        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('courses.index') }}" class="btn btn-sm btn-outline-secondary text-white border-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="font-serif text-white fw-bold mb-0">Create New Masterclass</h2>
        </div>

        <div class="card card-solid p-4">
            <form action="{{ route('courses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Course Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="e.g. Chopin Nocturnes: Phrasing, Touch & Rubato">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Instrument / Discipline <span class="text-danger">*</span></label>
                        <select name="instrument_id" class="form-select" required>
                            <option value="">Select Instrument...</option>
                            @foreach($instruments as $inst)
                                <option value="{{ $inst->id }}" {{ old('instrument_id') == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Proficiency Level <span class="text-danger">*</span></label>
                        <select name="level" class="form-select" required>
                            @foreach(\App\Models\Course::LEVELS as $lvl)
                                <option value="{{ $lvl }}" {{ old('level') == $lvl ? 'selected' : '' }}>{{ ucfirst($lvl) }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if(auth()->user()->isAdmin())
                        <div class="col-md-4">
                            <label class="form-label">Assigned Instructor <span class="text-danger">*</span></label>
                            <select name="instructor_id" class="form-select" required>
                                <option value="">Select Faculty...</option>
                                @foreach($instructors as $inst)
                                    <option value="{{ $inst->id }}" {{ old('instructor_id') == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="col-md-4">
                            <label class="form-label">Tuition Fee (KES) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-surface-elevated text-gold border-secondary">KES</span>
                                <input type="number" step="0.01" min="0" name="fee" class="form-control" value="{{ old('fee', '0.00') }}" required>
                            </div>
                            <small class="text-muted">Set 0 for free masterclass.</small>
                        </div>
                    @endif
                </div>

                @if(auth()->user()->isAdmin())
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tuition Fee (KES) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-surface-elevated text-gold border-secondary">KES</span>
                                <input type="number" step="0.01" min="0" name="fee" class="form-control" value="{{ old('fee', '0.00') }}" required>
                            </div>
                            <small class="text-muted">Set 0 for free masterclass.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Duration (Weeks)</label>
                            <input type="number" min="1" max="52" name="duration_weeks" class="form-control" value="{{ old('duration_weeks', '8') }}">
                        </div>
                    </div>
                @else
                    <div class="mb-3">
                        <label class="form-label">Duration (Weeks)</label>
                        <input type="number" min="1" max="52" name="duration_weeks" class="form-control" value="{{ old('duration_weeks', '8') }}">
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Short Description</label>
                    <input type="text" name="short_description" class="form-control" value="{{ old('short_description') }}" placeholder="One sentence summary for catalog preview...">
                </div>

                <div class="mb-3">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span>Full Masterclass Syllabus / Overview</span>
                        <span class="badge bg-gold text-dark small"><i class="bi bi-pen-fill me-1"></i> Rich Text</span>
                    </label>
                    <textarea name="description" rows="5" class="form-control richtext" placeholder="Detailed curriculum, prerequisites, techniques covered, repertoire studied...">{{ old('description') }}</textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label">Cover Artwork</label>
                        <input type="file" name="cover_image" class="form-control image-preview-input" accept="image/*">
                        <small class="text-muted">High resolution JPG or PNG, max 3MB.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Publishing Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft (Private)</option>
                            <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published (Public)</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary text-white">Cancel</a>
                    <button type="submit" class="btn btn-gold">
                        <i class="bi bi-check-lg me-1"></i> Create Course & Add Lessons
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
