@extends('layouts.dashboard')

@section('title', 'Edit Lesson: ' . $lesson->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">

        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('courses.show.manage', $course) }}" class="btn btn-sm btn-outline-secondary text-white border-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="font-serif text-white fw-bold mb-0">Edit Lesson</h2>
                <span class="text-gold small">{{ $course->title }}</span>
            </div>
        </div>

        <div class="card card-solid p-4">
            <form action="{{ route('lessons.update', [$course, $lesson]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-md-9">
                        <label class="form-label">Lesson Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $lesson->title) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Order / Position <span class="text-danger">*</span></label>
                        <input type="number" name="position" class="form-control" value="{{ old('position', $lesson->position) }}" min="1" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Video Demonstration URL</label>
                        <input type="url" name="video_url" class="form-control" value="{{ old('video_url', $lesson->video_url) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Duration (Minutes)</label>
                        <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', $lesson->duration_minutes) }}" min="1">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Uploaded Video File</label>
                    @if($lesson->video_path)
                        <div class="small text-gold mb-1"><i class="bi bi-check-circle"></i> File uploaded: {{ basename($lesson->video_path) }}</div>
                    @endif
                    <input type="file" name="video_file" class="form-control" accept="video/mp4,video/webm,video/quicktime">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Sheet Music Score (PDF)</label>
                        @if($lesson->sheet_music_path)
                            <div class="small text-info mb-1"><i class="bi bi-file-earmark-pdf"></i> Score attached: {{ basename($lesson->sheet_music_path) }}</div>
                        @endif
                        <input type="file" name="sheet_music_file" class="form-control" accept="application/pdf">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Audio Track / Accompaniment (MP3/WAV)</label>
                        @if($lesson->audio_path)
                            <div class="small text-warning mb-1"><i class="bi bi-soundwave"></i> Audio attached: {{ basename($lesson->audio_path) }}</div>
                        @endif
                        <input type="file" name="audio_file" class="form-control" accept="audio/*">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Short Summary</label>
                    <input type="text" name="summary" class="form-control" value="{{ old('summary', $lesson->summary) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span>Lesson Notes & Performance Guide</span>
                        <span class="badge bg-gold text-dark small"><i class="bi bi-pen-fill me-1"></i> Rich Text</span>
                    </label>
                    <textarea name="content" rows="6" class="form-control richtext">{{ old('content', $lesson->content) }}</textarea>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="is_preview" id="is_preview" value="1" {{ old('is_preview', $lesson->is_preview) ? 'checked' : '' }}>
                    <label class="form-check-label text-white" for="is_preview">
                        Allow Free Preview (Students can view this lesson before enrolling)
                    </label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('courses.show.manage', $course) }}" class="btn btn-outline-secondary text-white">Cancel</a>
                    <button type="submit" class="btn btn-gold">
                        <i class="bi bi-save me-1"></i> Update Lesson
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
