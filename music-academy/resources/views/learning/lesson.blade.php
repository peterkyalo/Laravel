@extends('layouts.classroom')

@section('title', $lesson->title . ' — ' . $course->title)

@section('content')
<!-- Classroom Top Header -->
<header class="navbar navbar-custom px-3 px-lg-4 border-bottom border-secondary sticky-top">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm text-white border-secondary" title="Return to Dashboard">
            <i class="bi bi-arrow-left me-1"></i> Exit Classroom
        </a>
        <div class="vr bg-secondary d-none d-sm-block" style="height: 24px;"></div>
        <div>
            <h6 class="text-white mb-0 font-serif fw-bold text-truncate" style="max-width: 400px;">{{ $course->title }}</h6>
            <small class="text-gold" style="font-size: 0.72rem;">Lesson {{ $lesson->position }}: {{ $lesson->title }}</small>
        </div>
    </div>

    <div class="d-flex align-items-center gap-3">
        @if($enrollment)
            <div class="d-none d-md-flex align-items-center gap-2">
                <small class="text-muted">Progress:</small>
                <div class="progress" style="width: 120px; height: 8px; background: rgba(255,255,255,0.1);">
                    <div class="progress-bar bg-gold" style="width: {{ $enrollment->progress }}%;"></div>
                </div>
                <small class="text-gold fw-bold">{{ $enrollment->progress }}%</small>
            </div>

            @if($enrollment->certificate)
                <a href="{{ route('certificates.show', $enrollment->certificate) }}" class="btn btn-sm btn-gold d-inline-flex align-items-center gap-1">
                    <i class="bi bi-award-fill"></i> View Diploma
                </a>
            @endif
        @endif

        <button class="btn btn-outline-secondary btn-sm text-white d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#syllabusOffcanvas">
            <i class="bi bi-list-ul"></i> Syllabus
        </button>
    </div>
</header>

<!-- Main Classroom Layout: Left Content, Right Syllabus -->
<div class="container-fluid flex-grow-1 p-0">
    <div class="row g-0">

        <!-- Left Column: Video, Audio, Sheet Music, Content -->
        <div class="col-lg-8 col-xl-9 p-3 p-md-4 overflow-y-auto" style="height: calc(100vh - 65px);">

            <!-- Completion Banner if course is 100% -->
            @if($enrollment && $enrollment->progress >= 100 && $enrollment->certificate)
                <div class="card card-glass border-gold p-3 mb-4 text-center">
                    <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
                        <i class="bi bi-trophy-fill text-gold fs-2"></i>
                        <div>
                            <h5 class="text-white font-serif fw-bold mb-0">Congratulations! You have completed this Masterclass!</h5>
                            <small class="text-muted">Your verified diploma is ready for printing and digital verification.</small>
                        </div>
                        <a href="{{ route('certificates.show', $enrollment->certificate) }}" class="btn btn-gold btn-sm">
                            <i class="bi bi-award me-1"></i> Claim Certificate
                        </a>
                    </div>
                </div>
            @endif

            <!-- 1. Video Player Area -->
            @if($lesson->embedUrl() || $lesson->video_path)
                <div class="card card-solid p-2 mb-4 overflow-hidden shadow-lg border-secondary">
                    <div class="ratio ratio-16x9 rounded overflow-hidden bg-black">
                        @if($lesson->video_path)
                            <video controls class="w-100 h-100" style="background: #000;">
                                <source src="{{ asset('storage/' . $lesson->video_path) }}" type="video/mp4">
                                Your browser does not support HTML5 video.
                            </video>
                        @elseif($lesson->embedUrl())
                            <iframe src="{{ $lesson->embedUrl() }}" title="{{ $lesson->title }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        @endif
                    </div>
                </div>
            @endif

            <!-- 2. Audio Accompaniment / Backing Track Player -->
            @if($lesson->audio_path)
                <div class="card card-solid p-3 mb-4 border-warning" style="background: rgba(245, 158, 11, 0.05);">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="badge bg-gold p-2 rounded-circle">
                            <i class="bi bi-soundwave text-dark fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-white fw-bold mb-0">Audio Repertoire Track / Metronome Accompaniment</h6>
                            <small class="text-muted">Play along with this backing track during your practice sessions.</small>
                            @if(Str::endsWith(strtolower($lesson->audio_path), ['.mid', '.midi']))
                                <!-- MIDI Player Web Component -->
                                <script src="https://cdn.jsdelivr.net/combine/npm/tone@14.7.58,npm/@magenta/music@1.23.1/es6/core.js,npm/focus-visible@5,npm/html-midi-player@1.5.0"></script>
                                <midi-player src="{{ asset('storage/' . $lesson->audio_path) }}" sound-font visualizer="#myVisualizer" style="width: 100%; margin-top: 10px;"></midi-player>
                                <midi-visualizer type="piano-roll" id="myVisualizer" style="width: 100%; height: 100px; background: #fff; border-radius: 8px; margin-top: 5px;"></midi-visualizer>
                            @else
                                <audio controls class="w-100 mt-2" style="border-radius: 8px;">
                                    <source src="{{ asset('storage/' . $lesson->audio_path) }}">
                                    Your browser does not support the audio element.
                                </audio>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- 3. Sheet Music Score Viewer (PDF) -->
            @if($lesson->sheet_music_path)
                <div class="card card-solid p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-white font-serif fw-bold mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-pdf text-info"></i> Sheet Music Score & Annotations
                        </h5>
                        <a href="{{ asset('storage/' . $lesson->sheet_music_path) }}" target="_blank" download class="btn btn-outline-light btn-sm border-secondary">
                            <i class="bi bi-download me-1"></i> Download Score PDF
                        </a>
                    </div>
                    <div class="ratio ratio-16x9 rounded overflow-hidden bg-surface-elevated border border-secondary" style="min-height: 600px;">
                        <object data="{{ asset('storage/' . $lesson->sheet_music_path) }}" type="application/pdf" width="100%" height="100%">
                            <p>It appears you don't have a PDF plugin for this browser. <a href="{{ asset('storage/' . $lesson->sheet_music_path) }}">Click here to download the PDF file.</a></p>
                        </object>
                    </div>
                </div>
            @endif

            <!-- 4. Lesson Lecture Notes & Content -->
            <div class="card card-solid p-4 mb-4">
                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <div>
                        <span class="badge bg-surface-elevated text-gold border border-secondary mb-2">Lesson {{ $lesson->position }}</span>
                        <h3 class="text-white font-serif fw-bold mb-1">{{ $lesson->title }}</h3>
                        @if($lesson->duration_minutes)
                            <small class="text-muted"><i class="bi bi-clock me-1"></i> Estimated: {{ $lesson->duration_minutes }} minutes</small>
                        @endif
                    </div>

                    <!-- Mark as Complete Toggle -->
                    <form action="{{ route('learning.lesson.toggle', [$course, $lesson]) }}" method="POST">
                        @csrf
                        @php $isDone = in_array($lesson->id, $completedLessonIds); @endphp
                        <button type="submit" class="btn btn-{{ $isDone ? 'success' : 'outline-gold' }}">
                            <i class="bi bi-{{ $isDone ? 'check-circle-fill' : 'circle' }} me-1"></i>
                            {{ $isDone ? 'Completed' : 'Mark as Completed' }}
                        </button>
                    </form>
                </div>

                @if($lesson->summary)
                    <div class="alert alert-dark bg-surface-elevated border-secondary text-light mb-4">
                        <strong class="text-gold">Focus Area:</strong> {{ $lesson->summary }}
                    </div>
                @endif

                <div class="text-light" style="line-height: 1.8;">
                    {!! nl2br(e($lesson->content)) !!}
                </div>

                <!-- Previous / Next Navigation Footer -->
                <div class="d-flex justify-content-between align-items-center pt-4 mt-4 border-top border-secondary">
                    @if($prevLesson)
                        <a href="{{ route('learning.lesson', [$course, $prevLesson]) }}" class="btn btn-outline-secondary text-white border-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Prev: {{ Str::limit($prevLesson->title, 25) }}
                        </a>
                    @else
                        <div></div>
                    @endif

                    @if($nextLesson)
                        <a href="{{ route('learning.lesson', [$course, $nextLesson]) }}" class="btn btn-gold">
                            Next: {{ Str::limit($nextLesson->title, 25) }} <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    @else
                        <a href="{{ route('student.courses') }}" class="btn btn-gold">
                            <i class="bi bi-check2-all me-1"></i> Finish Syllabus
                        </a>
                    @endif
                </div>
            </div>

        </div>

        <!-- Right Column: Course Syllabus (Desktop) -->
        <div class="col-lg-4 col-xl-3 d-none d-lg-block lesson-sidebar">
            @include('learning.partials.syllabus-list')
        </div>

    </div>
</div>

<!-- Mobile Offcanvas Syllabus -->
<div class="offcanvas offcanvas-end bg-surface text-white" tabindex="-1" id="syllabusOffcanvas" style="width: 320px;">
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title font-serif text-gold">Course Curriculum</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        @include('learning.partials.syllabus-list')
    </div>
</div>
@endsection
