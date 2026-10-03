@extends('layouts.dashboard')

@section('title', 'Assignments & Practice Submissions — Harmonia')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">Assignments & Studio Submissions</h2>
        <span class="text-muted small">Evaluate student recording submissions, provide critique, and award grades.</span>
    </div>
</div>

<div class="card card-solid p-3">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead class="text-muted small">
                <tr>
                    <th>Assignment Title</th>
                    <th>Course</th>
                    <th>Submissions</th>
                    <th>Awaiting Review</th>
                    <th>Max Points</th>
                    <th>Due Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $asg)
                    <tr>
                        <td>
                            <a href="{{ route('assignments.show', $asg) }}" class="text-white text-decoration-none fw-semibold">
                                {{ $asg->title }}
                            </a>
                        </td>
                        <td><span class="text-gold small">{{ $asg->course->title }}</span></td>
                        <td><span class="badge bg-surface-elevated text-white border border-secondary">{{ $asg->submissions_count }}</span></td>
                        <td>
                            @if($asg->ungraded_count > 0)
                                <span class="badge bg-warning text-dark">{{ $asg->ungraded_count }} pending</span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success">All Graded</span>
                            @endif
                        </td>
                        <td class="small">{{ $asg->max_score }}</td>
                        <td class="small text-muted">{{ $asg->due_at ? $asg->due_at->format('M j, Y') : 'Open' }}</td>
                        <td class="text-end">
                            <a href="{{ route('assignments.show', $asg) }}" class="btn btn-outline-gold btn-sm">
                                <i class="bi bi-mic me-1"></i> Review Submissions
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No assignments created yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3 d-flex justify-content-center">
        {{ $assignments->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
