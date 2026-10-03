@extends('layouts.dashboard')

@section('title', 'My Practice Assignments — Harmonia')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">My Practice Assignments</h2>
        <span class="text-muted small">Submit your audio and video performance recordings to receive instructor critiques.</span>
    </div>
</div>

<div class="row g-4">
    @forelse($assignments as $asg)
        @php $sub = $asg->submissions->first(); @endphp
        <div class="col-md-6 col-lg-4">
            <div class="card card-glass h-100 p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-gold small fw-semibold text-truncate" style="max-width: 180px;">{{ $asg->course->title }}</span>
                    @if($sub)
                        @if($sub->isGraded())
                            <span class="badge bg-success-subtle text-success border border-success">
                                Graded: {{ $sub->score }}/{{ $asg->max_score }}
                            </span>
                        @else
                            <span class="badge bg-info-subtle text-info border border-info">Submitted</span>
                        @endif
                    @else
                        <span class="badge bg-warning-subtle text-warning border border-warning">Pending</span>
                    @endif
                </div>

                <h5 class="font-serif text-white fw-bold mb-2">{{ $asg->title }}</h5>
                <p class="text-muted small mb-3 flex-grow-1">
                    {{ Str::limit($asg->instructions, 100) }}
                </p>

                <div class="pt-3 border-top border-secondary mb-3 small text-muted d-flex justify-content-between">
                    <span><i class="bi bi-trophy text-gold me-1"></i> Max: {{ $asg->max_score }} pts</span>
                    <span><i class="bi bi-calendar text-gold me-1"></i> {{ $asg->due_at ? $asg->due_at->format('M j, Y') : 'No deadline' }}</span>
                </div>

                <a href="{{ route('assignments.show', $asg) }}" class="btn btn-outline-gold btn-sm w-100 mt-auto">
                    <i class="bi bi-mic me-1"></i> {{ $sub ? 'View Submission & Grade' : 'Record & Submit' }}
                </a>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card card-solid p-5 text-center">
                <i class="bi bi-mic text-muted display-4 mb-3"></i>
                <h4 class="text-white font-serif">No Assignments Due</h4>
                <p class="text-muted mb-0">Your enrolled courses currently have no pending practice etudes assigned.</p>
            </div>
        </div>
    @endforelse
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $assignments->links('pagination::bootstrap-5') }}
</div>
@endsection
