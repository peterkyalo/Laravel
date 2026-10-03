<div class="p-3 border-bottom border-secondary bg-surface-elevated">
    <h6 class="text-white font-serif fw-bold mb-1">Course Repertoire</h6>
    <div class="d-flex justify-content-between align-items-center small text-muted">
        <span>{{ count($completedLessonIds) }} of {{ $course->lessons->count() }} lessons completed</span>
        <span class="text-gold fw-bold">{{ $enrollment?->progress ?? 0 }}%</span>
    </div>
</div>

<!-- Lessons List -->
<div class="py-2">
    @foreach($course->lessons as $item)
        @php
            $isItemDone = in_array($item->id, $completedLessonIds);
            $isCurrent = $item->id === $lesson->id;
        @endphp
        <a href="{{ route('learning.lesson', [$course, $item]) }}" class="lesson-item {{ $isCurrent ? 'active' : '' }}">
            <div class="me-2">
                @if($isItemDone)
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                @elseif($isCurrent)
                    <i class="bi bi-play-circle-fill text-gold fs-5"></i>
                @else
                    <i class="bi bi-circle text-muted fs-5"></i>
                @endif
            </div>
            <div class="flex-grow-1 overflow-hidden">
                <div class="text-truncate small fw-semibold {{ $isCurrent ? 'text-white' : 'text-light' }}">
                    {{ $item->position }}. {{ $item->title }}
                </div>
                <div class="text-muted d-flex align-items-center gap-2" style="font-size: 0.7rem;">
                    @if($item->duration_minutes)
                        <span><i class="bi bi-clock"></i> {{ $item->duration_minutes }}m</span>
                    @endif
                    @if($item->sheet_music_path)
                        <span class="text-info"><i class="bi bi-file-earmark-pdf"></i> Score</span>
                    @endif
                    @if($item->audio_path)
                        <span class="text-warning"><i class="bi bi-soundwave"></i> Audio</span>
                    @endif
                </div>
            </div>
        </a>
    @endforeach
</div>

<!-- Quizzes Section -->
@if($course->quizzes->count() > 0)
    <div class="p-3 border-top border-secondary mt-3">
        <h6 class="text-white font-serif fw-bold mb-2 small text-uppercase text-gold" style="letter-spacing: 0.05em;">
            <i class="bi bi-question-diamond-fill me-1"></i> Music Theory Quizzes
        </h6>
        @foreach($course->quizzes as $qz)
            @php $best = $qz->bestAttemptBy(auth()->user()); @endphp
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary">
                <div class="text-truncate me-2 small">
                    <div class="text-white fw-semibold">{{ $qz->title }}</div>
                    @if($best)
                        <span class="badge bg-{{ $best->passed ? 'success' : 'danger' }}-subtle text-{{ $best->passed ? 'success' : 'danger' }}" style="font-size: 0.65rem;">
                            Score: {{ $best->score }}% {{ $best->passed ? '(Passed)' : '(Retake)' }}
                        </span>
                    @else
                        <span class="text-muted" style="font-size: 0.65rem;">Not attempted yet</span>
                    @endif
                </div>
                <a href="{{ route('quizzes.take', $qz) }}" class="btn btn-sm btn-outline-gold" style="font-size: 0.72rem; padding: 0.2rem 0.6rem;">
                    {{ $best ? 'Retake' : 'Start' }}
                </a>
            </div>
        @endforeach
    </div>
@endif

<!-- Assignments Section -->
@if($course->assignments->count() > 0)
    <div class="p-3 border-top border-secondary mt-2">
        <h6 class="text-white font-serif fw-bold mb-2 small text-uppercase text-gold" style="letter-spacing: 0.05em;">
            <i class="bi bi-mic-fill me-1"></i> Practice Etudes
        </h6>
        @foreach($course->assignments as $asg)
            @php $sub = $asg->submissionBy(auth()->user()); @endphp
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary">
                <div class="text-truncate me-2 small">
                    <div class="text-white fw-semibold">{{ $asg->title }}</div>
                    @if($sub)
                        <span class="badge bg-{{ $sub->isGraded() ? 'success' : 'info' }}-subtle text-{{ $sub->isGraded() ? 'success' : 'info' }}" style="font-size: 0.65rem;">
                            {{ $sub->isGraded() ? 'Score: ' . $sub->score . '/' . $asg->max_score : 'Under Review' }}
                        </span>
                    @else
                        <span class="text-muted" style="font-size: 0.65rem;">Pending recording</span>
                    @endif
                </div>
                <a href="{{ route('assignments.show', $asg) }}" class="btn btn-sm btn-outline-light border-secondary" style="font-size: 0.72rem; padding: 0.2rem 0.6rem;">
                    {{ $sub ? 'View' : 'Record' }}
                </a>
            </div>
        @endforeach
    </div>
@endif
