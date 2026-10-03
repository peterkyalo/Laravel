@extends('layouts.dashboard')

@section('title', 'Curriculum Hub: ' . $course->title)

@section('content')
<!-- Header -->
<div class="card card-solid p-4 mb-4">
    <div class="row align-items-center g-3">
        <div class="col-md-2">
            <img src="{{ $course->coverUrl() }}" alt="{{ $course->title }}" class="rounded w-100" style="max-height: 120px; object-fit: cover;">
        </div>
        <div class="col-md-7">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge {{ $course->levelBadgeClass() }} text-uppercase">{{ $course->level }}</span>
                <span class="badge bg-surface-elevated text-gold border border-secondary">{{ $course->instrument->name }}</span>
                <span class="badge bg-{{ $course->status === 'published' ? 'success' : 'secondary' }} text-capitalize">{{ $course->status }}</span>
            </div>
            <h3 class="font-serif text-white fw-bold mb-1">{{ $course->title }}</h3>
            <div class="text-muted small">
                Instructor: <strong>{{ $course->instructor->name ?? 'None' }}</strong> · Fee: <span class="text-gold fw-semibold">{{ $course->isFree() ? 'FREE' : '$' . number_format($course->fee, 2) }}</span>
            </div>
        </div>
        <div class="col-md-3 text-md-end">
            <a href="{{ route('courses.public.show', $course) }}" target="_blank" class="btn btn-outline-secondary btn-sm text-white border-secondary mb-1">
                <i class="bi bi-box-arrow-up-right me-1"></i> Public Page
            </a>
            <a href="{{ route('learning.course', $course) }}" class="btn btn-outline-gold btn-sm mb-1">
                <i class="bi bi-play-circle me-1"></i> Student View
            </a>
            <a href="{{ route('courses.edit', $course) }}" class="btn btn-gold btn-sm mb-1">
                <i class="bi bi-pencil me-1"></i> Edit Settings
            </a>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs border-secondary mb-4" id="courseHubTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active text-white" id="lessons-tab" data-bs-toggle="tab" data-bs-target="#lessons-pane" type="button">
            <i class="bi bi-collection-play me-1 text-gold"></i> Lessons ({{ $course->lessons->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link text-white" id="assignments-tab" data-bs-toggle="tab" data-bs-target="#assignments-pane" type="button">
            <i class="bi bi-mic me-1 text-gold"></i> Assignments ({{ $course->assignments->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link text-white" id="quizzes-tab" data-bs-toggle="tab" data-bs-target="#quizzes-pane" type="button">
            <i class="bi bi-question-diamond me-1 text-gold"></i> Quizzes ({{ $course->quizzes->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link text-white" id="sessions-tab" data-bs-toggle="tab" data-bs-target="#sessions-pane" type="button">
            <i class="bi bi-calendar-event me-1 text-gold"></i> Class Schedule ({{ $course->sessions->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link text-white" id="students-tab" data-bs-toggle="tab" data-bs-target="#students-pane" type="button">
            <i class="bi bi-people me-1 text-gold"></i> Enrolled Students ({{ $course->enrollments->count() }})
        </button>
    </li>
</ul>

<div class="tab-content" id="courseHubTabsContent">

    <!-- 1. LESSONS TAB -->
    <div class="tab-pane fade show active" id="lessons-pane" role="tabpanel">
        <div class="card card-solid p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-serif fw-bold mb-0">Course Lesson Repertoire</h5>
                <a href="{{ route('lessons.create', $course) }}" class="btn btn-gold btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Add New Lesson
                </a>
            </div>

            <div class="list-group list-group-flush border-top border-secondary">
                @forelse($course->lessons as $lesson)
                    <div class="list-group-item bg-transparent text-white px-0 py-3 border-secondary d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-surface-elevated text-gold rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                {{ $lesson->position }}
                            </span>
                            <div>
                                <div class="fw-semibold">{{ $lesson->title }}</div>
                                <div class="text-muted small">
                                    @if($lesson->duration_minutes)
                                        <span><i class="bi bi-clock me-1"></i> {{ $lesson->duration_minutes }} min</span> ·
                                    @endif
                                    @if($lesson->video_url || $lesson->video_path)
                                        <span class="text-primary me-2"><i class="bi bi-camera-video-fill me-1"></i> Video</span>
                                    @endif
                                    @if($lesson->sheet_music_path)
                                        <span class="text-info me-2"><i class="bi bi-file-earmark-pdf-fill me-1"></i> Score PDF</span>
                                    @endif
                                    @if($lesson->audio_path)
                                        <span class="text-warning me-2"><i class="bi bi-soundwave me-1"></i> Audio Track</span>
                                    @endif
                                    @if($lesson->is_preview)
                                        <span class="badge bg-success-subtle text-success border border-success">Preview</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('learning.lesson', [$course, $lesson]) }}" class="btn btn-sm btn-outline-secondary text-white border-secondary" title="Preview Lesson">
                                <i class="bi bi-play-fill"></i> Watch
                            </a>
                            <a href="{{ route('lessons.edit', [$course, $lesson]) }}" class="btn btn-sm btn-outline-gold" title="Edit Lesson">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('lessons.destroy', [$course, $lesson]) }}" method="POST" onsubmit="return confirm('Delete this lesson?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Lesson">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small">
                        No lessons added yet. Click "Add New Lesson" to upload scores and video.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 2. ASSIGNMENTS TAB -->
    <div class="tab-pane fade" id="assignments-pane" role="tabpanel">
        <div class="card card-solid p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-serif fw-bold mb-0">Practice Assignments & Submissions</h5>
                <a href="{{ route('assignments.create', $course) }}" class="btn btn-gold btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Create Assignment
                </a>
            </div>

            <div class="list-group list-group-flush border-top border-secondary">
                @forelse($course->assignments as $asg)
                    <div class="list-group-item bg-transparent text-white px-0 py-3 border-secondary d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="fw-semibold">{{ $asg->title }}</div>
                            <div class="text-muted small">
                                Max Points: {{ $asg->max_score }}
                                @if($asg->due_at) · Due: {{ $asg->due_at->format('M j, Y') }} @endif
                            </div>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('assignments.show', $asg) }}" class="btn btn-outline-gold btn-sm">
                                <i class="bi bi-mic me-1"></i> Review Submissions ({{ $asg->submissions->count() }})
                            </a>
                            <form action="{{ route('assignments.destroy', $asg) }}" method="POST" onsubmit="return confirm('Delete this assignment?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-muted py-3 small mb-0">No assignments created yet for this course.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 3. QUIZZES TAB -->
    <div class="tab-pane fade" id="quizzes-pane" role="tabpanel">
        <div class="card card-solid p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-serif fw-bold mb-0">Music Theory Quizzes</h5>
                <a href="{{ route('quizzes.create', $course) }}" class="btn btn-gold btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Add Quiz
                </a>
            </div>

            <div class="list-group list-group-flush border-top border-secondary">
                @forelse($course->quizzes as $quiz)
                    <div class="list-group-item bg-transparent text-white px-0 py-3 border-secondary d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="fw-semibold">{{ $quiz->title }}</div>
                            <div class="text-muted small">
                                Questions: {{ $quiz->questions->count() }} · Pass Mark: {{ $quiz->pass_mark }}%
                                @if($quiz->is_required) · <span class="text-warning">Required for Certificate</span> @endif
                            </div>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <form action="{{ route('quizzes.publish.toggle', $quiz) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-{{ $quiz->is_published ? 'success' : 'secondary' }}">
                                    {{ $quiz->is_published ? 'Published' : 'Draft' }}
                                </button>
                            </form>
                            <a href="{{ route('quizzes.manage', $quiz) }}" class="btn btn-outline-gold btn-sm">
                                <i class="bi bi-list-check me-1"></i> Manage Questions
                            </a>
                            <form action="{{ route('quizzes.destroy', $quiz) }}" method="POST" onsubmit="return confirm('Delete this quiz?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-muted py-3 small mb-0">No quizzes created yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 4. SESSIONS TAB -->
    <div class="tab-pane fade" id="sessions-pane" role="tabpanel">
        <div class="card card-solid p-4">
            <h5 class="text-white font-serif fw-bold mb-3">Schedule Live Class / Masterclass Session</h5>

            <!-- New Session Form -->
            <form action="{{ route('sessions.store', $course) }}" method="POST" class="row g-3 mb-4 p-3 bg-surface-elevated rounded">
                @csrf
                <div class="col-md-4">
                    <label class="form-label small">Session Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control form-control-sm" placeholder="e.g. Masterclass Rehearsal" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Start Date & Time <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="starts_at" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">End Date & Time <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="ends_at" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Location / Hall</label>
                    <input type="text" name="location" class="form-control form-control-sm" placeholder="Studio 4 or Online Zoom">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Meeting Link (Zoom / Meet URL)</label>
                    <input type="url" name="meeting_url" class="form-control form-control-sm" placeholder="https://zoom.us/j/...">
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-gold btn-sm"><i class="bi bi-calendar-plus me-1"></i> Add to Academy Calendar</button>
                </div>
            </form>

            <h6 class="text-white fw-bold mb-3">Scheduled Sessions</h6>
            <div class="list-group list-group-flush border-top border-secondary">
                @forelse($course->sessions as $sesh)
                    <div class="list-group-item bg-transparent text-white px-0 py-2 border-secondary d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold small">{{ $sesh->title }}</div>
                            <div class="text-muted small">
                                <i class="bi bi-clock text-gold me-1"></i> {{ $sesh->starts_at->format('M j, Y · g:i A') }} – {{ $sesh->ends_at->format('g:i A') }}
                                @if($sesh->location) · <i class="bi bi-geo-alt text-gold me-1"></i> {{ $sesh->location }} @endif
                            </div>
                        </div>
                        <form action="{{ route('sessions.destroy', $sesh) }}" method="POST" onsubmit="return confirm('Cancel this class session?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                @empty
                    <p class="text-muted small py-2 mb-0">No live sessions scheduled yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 5. ENROLLED STUDENTS TAB -->
    <div class="tab-pane fade" id="students-pane" role="tabpanel">
        <div class="card card-solid p-4">
            <h5 class="text-white font-serif fw-bold mb-3">Enrolled Academy Students</h5>

            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead class="text-muted small">
                        <tr>
                            <th>Student</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Enrolled Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($course->enrollments as $enr)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $enr->user->avatarUrl() }}" alt="Avatar" class="rounded-circle" width="30" height="30" style="object-fit: cover;">
                                        <div>
                                            <div class="fw-semibold text-white">{{ $enr->user->name }}</div>
                                            <small class="text-muted">{{ $enr->user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge {{ $enr->statusBadgeClass() }} text-capitalize">{{ $enr->status }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; width: 100px; background: rgba(255,255,255,0.1);">
                                            <div class="progress-bar bg-gold" style="width: {{ $enr->progress }}%;"></div>
                                        </div>
                                        <small class="text-gold fw-bold">{{ $enr->progress }}%</small>
                                    </div>
                                </td>
                                <td class="small text-muted">{{ $enr->enrolled_at ? $enr->enrolled_at->format('M j, Y') : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No students enrolled yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
