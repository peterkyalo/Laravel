@extends('layouts.dashboard')

@section('title', 'Add Lesson: ' . $course->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">

        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('courses.show.manage', $course) }}" class="btn btn-sm btn-outline-secondary text-white border-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="font-serif text-white fw-bold mb-0">Add Masterclass Lesson</h2>
                <span class="text-gold small">{{ $course->title }}</span>
            </div>
        </div>

        <div class="card card-solid p-4">
            <form action="{{ route('lessons.store', $course) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-9">
                        <label class="form-label">Lesson Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="e.g. Movement I: Allegro Cantabile Fingering Technique">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Order / Position <span class="text-danger">*</span></label>
                        <input type="number" name="position" class="form-control" value="{{ old('position', $nextPosition) }}" min="1" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Video Demonstration URL (YouTube or Vimeo)</label>
                        <input type="url" name="video_url" class="form-control" value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Duration (Minutes)</label>
                        <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', 15) }}" min="1">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Or Upload Direct Video File (MP4/WebM)</label>
                    <input type="file" name="video_file" class="form-control" accept="video/mp4,video/webm,video/quicktime">
                    <small class="text-muted">Max 50MB. Can be left empty if using YouTube/Vimeo URL.</small>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Sheet Music Score (PDF)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-surface-elevated text-info border-secondary"><i class="bi bi-file-earmark-pdf"></i></span>
                            <input type="file" name="sheet_music_file" class="form-control" accept="application/pdf">
                        </div>
                        <small class="text-muted">Interactive in-browser PDF viewer will be displayed to students.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Audio Track / Accompaniment (MP3/WAV)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-surface-elevated text-warning border-secondary"><i class="bi bi-soundwave"></i></span>
                            <input type="file" name="audio_file" class="form-control" accept="audio/*">
                        </div>
                        <small class="text-muted">Backing track, metronome pulse, or reference tempo audio.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Short Summary</label>
                    <input type="text" name="summary" class="form-control" value="{{ old('summary') }}" placeholder="Key takeaways and performance objectives...">
                </div>

                <div class="mb-3">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span>Lesson Notes & Performance Guide</span>
                        <span class="badge bg-gold text-dark small"><i class="bi bi-pen-fill me-1"></i> Rich Text</span>
                    </label>
                    <textarea name="content" rows="6" class="form-control richtext" placeholder="Detailed practice instructions, fingering notes, historical context, pedaling tips...">{{ old('content') }}</textarea>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="is_preview" id="is_preview" value="1" {{ old('is_preview') ? 'checked' : '' }}>
                    <label class="form-check-label text-white" for="is_preview">
                        Allow Free Preview (Students can view this lesson before enrolling)
                    </label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('courses.show.manage', $course) }}" class="btn btn-outline-secondary text-white">Cancel</a>
                    <button type="submit" class="btn btn-gold">
                        <i class="bi bi-check-lg me-1"></i> Save Lesson
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
