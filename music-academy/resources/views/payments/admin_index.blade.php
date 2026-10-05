@extends('layouts.dashboard')

@section('title', 'Tuition & Payment Management — Baritone')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">Academy Tuition & Financials</h2>
        <span class="text-muted small">Process remittances, verify wire references, and track conservatory revenue.</span>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card border-gold">
            <span class="text-muted small fw-semibold">Total Revenue Collected</span>
            <h3 class="font-serif text-gold fw-bold mb-0 mt-1">KES {{ number_format($stats['total_received'], 2) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <span class="text-muted small fw-semibold">Pending Verifications</span>
            <h3 class="font-serif text-warning fw-bold mb-0 mt-1">{{ $stats['pending_count'] }} Transactions</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <span class="text-muted small fw-semibold">Pending Amount</span>
            <h3 class="font-serif text-white fw-bold mb-0 mt-1">KES {{ number_format($stats['pending_amount'], 2) }}</h3>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-solid p-3 mb-4">
    <form action="{{ route('payments.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-surface-elevated text-gold border-secondary"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by student name..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Payment Statuses</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Verification</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Approved / Paid</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
        <div class="col-md-2">
            <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary btn-sm text-white w-100">Clear</a>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="card card-solid p-3">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead class="text-muted small">
                <tr>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Masterclass</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Status</th>
                    <th class="text-end">Verification Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td class="small text-muted">{{ $p->created_at->format('M j, Y') }}</td>
                        <td>
                            <div class="fw-semibold text-white">{{ $p->enrollment->user->name }}</div>
                            <small class="text-muted">{{ $p->enrollment->user->email }}</small>
                        </td>
                        <td class="small text-truncate" style="max-width: 160px;">{{ $p->enrollment->course->title }}</td>
                        <td class="text-gold fw-bold">KES {{ number_format($p->amount, 2) }}</td>
                        <td class="small text-capitalize">{{ $p->methodLabel() }}</td>
                        <td class="small font-monospace text-muted">
                            @if($p->isPaid())
                                <span class="text-success" title="Gateway Receipt/Capture ID">{{ $p->method === 'mpesa' && !empty($p->meta['mpesa_receipt']) ? $p->meta['mpesa_receipt'] : $p->reference }}</span>
                            @elseif($p->gateway_reference)
                                <span title="Gateway Transaction ID">{{ $p->gateway_reference }}</span>
                            @else
                                <span class="text-secondary" title="Local Order Reference (Gateway ID not generated)">{{ $p->reference }}</span>
                            @endif
                        </td>
                        <td>
                            @if($p->isPaid() && $p->method === 'mpesa' && empty($p->meta['mpesa_receipt']))
                                <span class="badge text-bg-info text-capitalize">Processed</span>
                            @else
                                <span class="badge {{ $p->statusBadgeClass() }} text-capitalize">{{ $p->status }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($p->status === 'pending')
                                <div class="btn-group btn-group-sm">
                                    <form action="{{ route('admin.payments.approve', $p) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve & Activate Course">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.payments.reject', $p) }}" method="POST" class="ms-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Reject Payment">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <small class="text-muted">{{ $p->paid_at ? 'Processed ' . $p->paid_at->format('M j') : 'Processed' }}</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No transactions matching your criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3 d-flex justify-content-center">
        {{ $payments->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
