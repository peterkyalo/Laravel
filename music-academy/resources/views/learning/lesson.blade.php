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
                <div class="card card-solid px-3 py-2 mb-3 border-warning w-100 shadow-sm" style="background: rgba(245, 158, 11, 0.05);">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-soundwave text-gold"></i>
                        <h6 class="text-white fw-bold mb-0" style="font-size: 0.85rem;">Audio Repertoire Track / Metronome Accompaniment</h6>
                    </div>
                    @if(Str::endsWith(strtolower($lesson->audio_path), ['.mid', '.midi']))
                        <!-- MIDI Player Web Component -->
                        <script src="https://cdn.jsdelivr.net/combine/npm/tone@14.7.58,npm/@magenta/music@1.23.1/es6/core.js,npm/focus-visible@5,npm/html-midi-player@1.5.0"></script>
                        <midi-player src="{{ route('serve.file', ['path' => $lesson->audio_path], false) }}" sound-font style="width: 100%; height: 38px;"></midi-player>
                    @else
                        <audio controls class="w-100" style="height: 38px;">
                            <source src="{{ asset('storage/' . $lesson->audio_path) }}">
                            Your browser does not support the audio element.
                        </audio>
                    @endif
                </div>
            @endif

            <!-- 3. Sheet Music Score Viewer (PDF.js Custom Viewer with Zoom Controls) -->
            @if($lesson->sheet_music_path)
                <div class="card card-solid p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h5 class="text-white font-serif fw-bold mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-pdf text-info"></i> Sheet Music Score & Annotations
                        </h5>
                        
                        <!-- Toolbar Controls: Zoom & Download -->
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="btn-group border border-secondary rounded p-1 bg-surface-elevated">
                                <button id="pdf-zoom-out" class="btn btn-outline-secondary btn-sm text-white border-0" title="Zoom Out">
                                    <i class="bi bi-zoom-out"></i>
                                </button>
                                <span id="pdf-zoom-level" class="btn btn-sm text-gold fw-bold border-0 disabled px-2" style="opacity: 1;">100%</span>
                                <button id="pdf-zoom-in" class="btn btn-outline-secondary btn-sm text-white border-0" title="Zoom In">
                                    <i class="bi bi-zoom-in"></i>
                                </button>
                                <button id="pdf-fit-width" class="btn btn-outline-secondary btn-sm text-white border-0 ms-1" title="Reset Zoom">
                                    <i class="bi bi-arrows-angle-expand"></i> Reset
                                </button>
                            </div>

                            <a href="{{ route('serve.file', ['path' => $lesson->sheet_music_path], false) }}" target="_blank" download class="btn btn-outline-light btn-sm border-secondary">
                                <i class="bi bi-download me-1"></i> Download Score PDF
                            </a>
                        </div>
                    </div>
                    
                    <!-- Scrollable Canvas Viewer Box -->
                    <div id="pdf-container" class="w-100 overflow-auto bg-dark p-3 text-center rounded border border-secondary shadow-inner" style="max-height: 750px; min-height: 500px;">
                        <div id="pdf-loading" class="text-white py-5">
                            <div class="spinner-border text-gold me-2" role="status"></div>
                            <span class="fs-6 fw-bold">Rendering Sheet Music Score...</span>
                        </div>
                        <div id="pdf-pages-wrapper" class="d-flex flex-column align-items-center gap-3"></div>
                    </div>
                </div>

                <!-- PDF.js Engine Scripts -->
                <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        let pdfUrl = "{{ route('serve.file', ['path' => $lesson->sheet_music_path], false) }}";
                        if (window.location.protocol === 'https:' && pdfUrl.startsWith('http:')) {
                            pdfUrl = pdfUrl.replace('http:', 'https:');
                        }
                        
                        if (typeof pdfjsLib !== 'undefined') {
                            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                        }

                        let pdfDoc = null;
                        let baseScale = 1.3;
                        let zoomFactor = 1.0;

                        function renderAllPages() {
                            if (!pdfDoc) return;
                            const wrapper = document.getElementById('pdf-pages-wrapper');
                            wrapper.innerHTML = '';
                            const finalScale = baseScale * zoomFactor;

                            for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
                                pdfDoc.getPage(pageNum).then(function(page) {
                                    const viewport = page.getViewport({ scale: finalScale });
                                    const dpr = window.devicePixelRatio || 1;

                                    const canvas = document.createElement('canvas');
                                    canvas.className = 'shadow-lg rounded bg-white';
                                    canvas.style.maxWidth = '100%';
                                    canvas.style.height = 'auto';

                                    const context = canvas.getContext('2d');
                                    canvas.width = Math.floor(viewport.width * dpr);
                                    canvas.height = Math.floor(viewport.height * dpr);

                                    canvas.style.width = Math.floor(viewport.width) + 'px';
                                    canvas.style.height = Math.floor(viewport.height) + 'px';

                                    wrapper.appendChild(canvas);

                                    const transform = dpr !== 1 ? [dpr, 0, 0, dpr, 0, 0] : null;
                                    const renderContext = {
                                        canvasContext: context,
                                        transform: transform,
                                        viewport: viewport
                                    };
                                    page.render(renderContext);
                                });
                            }
                        }

                        if (typeof pdfjsLib !== 'undefined') {
                            const loading = document.getElementById('pdf-loading');

                            // Fetch PDF as ArrayBuffer to bypass Range/Stream 204 issues
                            fetch(pdfUrl)
                                .then(function(response) {
                                    if (!response.ok) throw new Error('HTTP status ' + response.status);
                                    return response.arrayBuffer();
                                })
                                .then(function(arrayBuffer) {
                                    return pdfjsLib.getDocument({ data: arrayBuffer }).promise;
                                })
                                .then(function(pdf) {
                                    pdfDoc = pdf;
                                    if (loading) loading.style.display = 'none';
                                    renderAllPages();
                                })
                                .catch(function(err) {
                                    console.error('PDF Fetch/Render Error:', err);
                                    if (loading) {
                                        loading.innerHTML = `
                                            <div class="alert alert-dark border-secondary text-light m-3">
                                                <i class="bi bi-exclamation-circle text-warning fs-3 d-block mb-2"></i>
                                                <h6>Unable to preview PDF directly in browser</h6>
                                                <p class="small text-muted mb-2">You can download the score file to view it locally:</p>
                                                <a href="${pdfUrl}" download class="btn btn-gold btn-sm"><i class="bi bi-download me-1"></i> Download Score PDF</a>
                                            </div>
                                        `;
                                    }
                                });
                        }

                        const zoomInBtn = document.getElementById('pdf-zoom-in');
                        const zoomOutBtn = document.getElementById('pdf-zoom-out');
                        const fitWidthBtn = document.getElementById('pdf-fit-width');
                        const zoomLevelEl = document.getElementById('pdf-zoom-level');

                        if (zoomInBtn) {
                            zoomInBtn.addEventListener('click', function() {
                                if (zoomFactor < 2.5) {
                                    zoomFactor += 0.25;
                                    if (zoomLevelEl) zoomLevelEl.innerText = Math.round(zoomFactor * 100) + '%';
                                    renderAllPages();
                                }
                            });
                        }

                        if (zoomOutBtn) {
                            zoomOutBtn.addEventListener('click', function() {
                                if (zoomFactor > 0.5) {
                                    zoomFactor -= 0.25;
                                    if (zoomLevelEl) zoomLevelEl.innerText = Math.round(zoomFactor * 100) + '%';
                                    renderAllPages();
                                }
                            });
                        }

                        if (fitWidthBtn) {
                            fitWidthBtn.addEventListener('click', function() {
                                zoomFactor = 1.0;
                                if (zoomLevelEl) zoomLevelEl.innerText = '100%';
                                renderAllPages();
                            });
                        }
                    });
                </script>
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

                <div class="text-light ql-editor" style="line-height: 1.8; padding: 0;">
                    {!! preg_replace('/<span class="ql-ui"[^>]*><\/span>/i', '', $lesson->content) !!}
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
