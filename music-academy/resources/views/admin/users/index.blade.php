@extends('layouts.dashboard')

@section('title', 'Manage Academy Users — Baritone')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">Academy Member Directory</h2>
        <span class="text-muted small">Manage faculty credentials, student profiles, and administrative permissions.</span>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-gold btn-sm">
        <i class="bi bi-person-plus me-1"></i> Add New User
    </a>
</div>

<!-- Search & Filter Bar -->
<div class="card card-solid p-3 mb-4">
    <form action="{{ route('admin.users.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-7">
            <div class="input-group">
                <span class="input-group-text bg-surface-elevated text-gold border-secondary"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name or email..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Academy Roles</option>
                <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Students</option>
                <option value="instructor" {{ request('role') == 'instructor' ? 'selected' : '' }}>Instructors</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrators</option>
            </select>
        </div>
        <div class="col-md-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm text-white w-100">Clear</a>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="card card-solid p-3">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead class="text-muted small">
                <tr>
                    <th>Member</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Courses Activity</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $u->avatarUrl() }}" alt="Avatar" class="rounded-circle" width="34" height="34" style="object-fit: cover;">
                                <div>
                                    <div class="fw-semibold text-white small">{{ $u->name }}</div>
                                    <small class="text-muted">{{ $u->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $u->role === 'admin' ? 'danger' : ($u->role === 'instructor' ? 'primary' : 'success') }}-subtle text-{{ $u->role === 'admin' ? 'danger' : ($u->role === 'instructor' ? 'primary' : 'success') }} text-capitalize">
                                {{ $u->role }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ $u->phone ?? '—' }}</td>
                        <td class="small">
                            @if($u->isInstructor())
                                <span class="badge bg-surface-elevated text-gold border border-secondary">{{ $u->taught_courses_count }} Taught</span>
                            @else
                                <span class="badge bg-surface-elevated text-info border border-secondary">{{ $u->enrollments_count }} Enrolled</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $u->is_active ? 'success' : 'secondary' }}-subtle text-{{ $u->is_active ? 'success' : 'secondary' }}">
                                {{ $u->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ $u->created_at->format('M j, Y') }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-outline-secondary text-white" title="Edit Member">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($u->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $u) }}" method="POST" onsubmit="return confirm('Delete user {{ $u->name }}?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete User">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3 d-flex justify-content-center">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
