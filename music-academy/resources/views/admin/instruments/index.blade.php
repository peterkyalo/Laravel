@extends('layouts.dashboard')

@section('title', 'Manage Instruments — Baritone Music Academy')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">Instruments & Disciplines</h2>
        <span class="text-muted small">Manage academy instrumental departments, performance icons, and descriptions.</span>
    </div>
</div>

<div class="row g-4">
    <!-- Add Instrument Form -->
    <div class="col-lg-4">
        <div class="card card-solid p-4 sticky-top" style="top: 90px;">
            <h5 class="text-white font-serif fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle text-gold"></i> Add Discipline
            </h5>

            <form action="{{ route('admin.instruments.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Instrument Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control form-control-sm" required placeholder="e.g. Cello & Strings">
                </div>

                <div class="mb-3">
                    <label class="form-label">Bootstrap Icon Name</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-surface-elevated text-gold border-secondary">bi-</span>
                        <input type="text" name="icon" class="form-control" value="music-note-beamed" placeholder="music-note-beamed">
                    </div>
                    <small class="text-muted">Examples: music-note, music-player, soundwave, mic, boombox, bell.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label">Department Description</label>
                    <textarea name="description" rows="3" class="form-control form-control-sm" placeholder="Historical context, chamber repertoire, string ensemble training..."></textarea>
                </div>

                <button type="submit" class="btn btn-gold btn-sm w-100">
                    <i class="bi bi-plus-lg me-1"></i> Add Instrument
                </button>
            </form>
        </div>
    </div>

    <!-- Instruments Table -->
    <div class="col-lg-8">
        <div class="card card-solid p-3">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead class="text-muted small">
                        <tr>
                            <th>Icon</th>
                            <th>Instrument Name</th>
                            <th>Slug</th>
                            <th>Masterclasses</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($instruments as $inst)
                            <tr>
                                <td>
                                    <div class="stat-icon bg-surface-elevated text-gold rounded border border-secondary" style="width: 38px; height: 38px; font-size: 1.1rem;">
                                        <i class="bi bi-{{ $inst->icon }}"></i>
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-white small">{{ $inst->name }}</strong>
                                    @if($inst->description)
                                        <div class="text-muted small" style="font-size: 0.72rem;">{{ Str::limit($inst->description, 60) }}</div>
                                    @endif
                                </td>
                                <td><code class="text-gold small">{{ $inst->slug }}</code></td>
                                <td>
                                    <span class="badge bg-surface-elevated text-info border border-secondary">{{ $inst->courses_count }} Courses</span>
                                </td>
                                <td class="text-end">
                                    @if($inst->courses_count === 0)
                                        <form action="{{ route('admin.instruments.destroy', $inst) }}" method="POST" onsubmit="return confirm('Delete instrument {{ $inst->name }}?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small" title="Cannot delete while linked to courses">In Use</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No instruments defined yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
