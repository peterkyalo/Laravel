@extends('layouts.dashboard')

@section('title', 'Admin Conservatory Overview')

@section('content')
<!-- Header & Quick Actions -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">Conservatory Dashboard</h2>
        <span class="text-muted small">Welcome back, Academy Administrator. Here is the operational pulse.</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('courses.create') }}" class="btn btn-gold btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Course
        </a>
        <a href="{{ route('admin.users.create') }}" class="btn btn-outline-light btn-sm border-secondary">
            <i class="bi bi-person-plus me-1"></i> Add User
        </a>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-2">
        <div class="stat-card">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Students</span>
                <span class="text-info"><i class="bi bi-people-fill fs-5"></i></span>
            </div>
            <h3 class="font-serif text-white fw-bold mb-0">{{ $stats['students'] }}</h3>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="stat-card">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Faculty</span>
                <span class="text-gold"><i class="bi bi-mortarboard-fill fs-5"></i></span>
            </div>
            <h3 class="font-serif text-white fw-bold mb-0">{{ $stats['instructors'] }}</h3>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="stat-card">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Courses</span>
                <span class="text-primary"><i class="bi bi-collection-play-fill fs-5"></i></span>
            </div>
            <h3 class="font-serif text-white fw-bold mb-0">{{ $stats['courses'] }}</h3>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card border-gold">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Tuition Collected</span>
                <span class="text-gold"><i class="bi bi-cash-coin fs-5"></i></span>
            </div>
            <h3 class="font-serif text-gold fw-bold mb-0">${{ number_format($stats['revenue'], 2) }}</h3>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Pending Payments</span>
                <span class="text-warning"><i class="bi bi-hourglass-split fs-5"></i></span>
            </div>
            <h3 class="font-serif text-white fw-bold mb-0">
                {{ $stats['pending_payments'] }}
                @if($stats['pending_payments'] > 0)
                    <a href="{{ route('payments.index', ['status' => 'pending']) }}" class="badge bg-warning text-dark text-decoration-none ms-1 small" style="font-size: 0.7rem;">Review</a>
                @endif
            </h3>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Revenue Trend Chart -->
    <div class="col-lg-8">
        <div class="card card-solid p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-serif fw-bold mb-0">Tuition Revenue (Last 6 Months)</h5>
                <span class="badge bg-surface-elevated text-gold border border-secondary">USD</span>
            </div>
            <div style="height: 250px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Enrollments by Instrument Doughnut -->
    <div class="col-lg-4">
        <div class="card card-solid p-4 h-100">
            <h5 class="text-white font-serif fw-bold mb-3">Enrollments by Discipline</h5>
            <div style="height: 250px;">
                <canvas id="instrumentChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Pending Approvals & Activity -->
<div class="row g-4">
    <!-- Pending Payments Table -->
    <div class="col-lg-7">
        <div class="card card-solid p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-serif fw-bold mb-0">Pending Tuition Verifications</h5>
                <a href="{{ route('payments.index') }}" class="text-gold small text-decoration-none">All Payments <i class="bi bi-arrow-right"></i></a>
            </div>

            @if($pendingPayments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead class="text-muted small">
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Amount</th>
                                <th>Method / Ref</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingPayments as $p)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-white">{{ $p->enrollment->user->name }}</div>
                                        <small class="text-muted">{{ $p->enrollment->user->email }}</small>
                                    </td>
                                    <td class="small text-truncate" style="max-width: 140px;">{{ $p->enrollment->course->title }}</td>
                                    <td class="text-gold fw-bold">${{ number_format($p->amount, 2) }}</td>
                                    <td class="small">
                                        <span class="text-capitalize">{{ $p->methodLabel() }}</span>
                                        @if($p->reference)
                                            <div class="text-muted font-monospace" style="font-size: 0.72rem;">{{ $p->reference }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <form action="{{ route('admin.payments.approve', $p) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Approve & Activate"><i class="bi bi-check-lg"></i></button>
                                            </form>
                                            <form action="{{ route('admin.payments.reject', $p) }}" method="POST" class="ms-1">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Reject"><i class="bi bi-x-lg"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-check2-circle text-success fs-2 d-block mb-1"></i>
                    All student tuition submissions have been processed.
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Enrollments & Bulletins -->
    <div class="col-lg-5">
        <div class="card card-solid p-4 mb-4">
            <h5 class="text-white font-serif fw-bold mb-3">Recent Enrollments</h5>
            <div class="list-group list-group-flush border-top border-secondary">
                @forelse($recentEnrollments as $enr)
                    <div class="list-group-item bg-transparent text-white px-0 py-2 border-secondary d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $enr->user->avatarUrl() }}" alt="Avatar" class="rounded-circle" width="28" height="28" style="object-fit: cover;">
                            <div>
                                <div class="small fw-semibold">{{ $enr->user->name }}</div>
                                <div class="text-muted small" style="font-size: 0.72rem;">{{ $enr->course->title }}</div>
                            </div>
                        </div>
                        <span class="badge {{ $enr->statusBadgeClass() }} small text-capitalize">{{ $enr->status }}</span>
                    </div>
                @empty
                    <p class="text-muted small mb-0 py-2">No enrollments yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Academy Bulletins -->
        <div class="card card-solid p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white font-serif fw-bold mb-0">Academy Bulletins</h5>
                <a href="{{ route('announcements.index') }}" class="text-gold small text-decoration-none">Manage</a>
            </div>
            @forelse($announcements as $ann)
                <div class="mb-3 pb-3 border-bottom border-secondary">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        @if($ann->is_pinned)
                            <span class="badge bg-gold small"><i class="bi bi-pin-fill"></i> Pinned</span>
                        @endif
                        <strong class="text-white small">{{ $ann->title }}</strong>
                    </div>
                    <p class="text-muted small mb-1">{{ Str::limit($ann->body, 80) }}</p>
                    <small class="text-secondary" style="font-size: 0.7rem;">By {{ $ann->author->name }} · {{ $ann->created_at->diffForHumans() }}</small>
                </div>
            @empty
                <p class="text-muted small mb-0">No active bulletins.</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Revenue Line/Bar Chart
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    const revData = @json($revenue);

    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: Object.keys(revData),
            datasets: [{
                label: 'Tuition ($)',
                data: Object.values(revData),
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.15)',
                borderWidth: 2,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#fbbf24',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: { color: '#94a3b8' }
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: {
                        color: '#94a3b8',
                        callback: function(v) { return '$' + v; }
                    }
                }
            }
        }
    });

    // Instrument Doughnut Chart
    const instCtx = document.getElementById('instrumentChart').getContext('2d');
    const instData = @json($byInstrument);

    new Chart(instCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(instData),
            datasets: [{
                data: Object.values(instData),
                backgroundColor: ['#4f46e5', '#f59e0b', '#10b981', '#ec4899', '#06b6d4', '#8b5cf6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#cbd5e1', font: { size: 11 } }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
