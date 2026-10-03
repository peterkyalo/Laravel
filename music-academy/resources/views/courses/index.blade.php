@extends('layouts.dashboard')

@section('title', 'Manage Masterclasses — Harmonia')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">Masterclass Curricula</h2>
        <span class="text-muted small">Manage academy courses, lessons, scores, and rehearsals.</span>
    </div>
    <div>
        <a href="{{ route('courses.create') }}" class="btn btn-gold btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Create New Course
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-solid p-3 mb-4">
    <form action="{{ route('courses.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-surface-elevated text-gold border-secondary"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search courses..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="instrument" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Instruments</option>
                @foreach($instruments as $inst)
                    <option value="{{ $inst->id }}" {{ request('instrument') == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
        </div>
        <div class="col-md-1">
            <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary btn-sm text-white w-100"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>
</div>

<!-- Course List Table -->
<div class="card card-solid p-3">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead class="text-muted small">
                <tr>
                    <th>Course Title</th>
                    <th>Instrument</th>
                    <th>Level</th>
                    @if(auth()->user()->isAdmin())
                        <th>Instructor</th>
                    @endif
                    <th>Lessons</th>
                    <th>Enrolled</th>
                    <th>Fee</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $c)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $c->coverUrl() }}" alt="Cover" class="rounded" width="48" height="48" style="object-fit: cover;">
                                <div>
                                    <a href="{{ route('courses.show.manage', $c) }}" class="text-white text-decoration-none fw-semibold">
                                        {{ $c->title }}
                                    </a>
                                    <div class="text-muted small" style="font-size: 0.72rem;">{{ $c->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-surface-elevated text-gold border border-secondary">
                                <i class="bi bi-{{ $c->instrument->icon }} me-1"></i> {{ $c->instrument->name }}
                            </span>
                        </td>
                        <td><span class="badge {{ $c->levelBadgeClass() }} text-uppercase">{{ $c->level }}</span></td>
                        @if(auth()->user()->isAdmin())
                            <td class="small">{{ $c->instructor->name ?? '—' }}</td>
                        @endif
                        <td><span class="badge bg-surface-elevated text-white border border-secondary">{{ $c->lessons_count }}</span></td>
                        <td><span class="badge bg-surface-elevated text-info border border-secondary">{{ $c->enrollments_count }}</span></td>
                        <td class="text-gold fw-semibold">{{ $c->isFree() ? 'FREE' : '$' . number_format($c->fee, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $c->status === 'published' ? 'success' : 'secondary' }} text-capitalize">{{ $c->status }}</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('courses.show.manage', $c) }}" class="btn btn-outline-gold" title="Curriculum & Hub">
                                    <i class="bi bi-sliders"></i> Manage
                                </a>
                                <a href="{{ route('courses.edit', $c) }}" class="btn btn-outline-secondary text-white" title="Edit Settings">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('courses.destroy', $c) }}" method="POST" onsubmit="return confirm('Delete this course and all its lessons?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete Course">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No courses found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3 d-flex justify-content-center">
        {{ $courses->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
