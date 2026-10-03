@extends('layouts.dashboard')

@section('title', 'My Tuition & Payments — Harmonia')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">Tuition & Financial Account</h2>
        <span class="text-muted small">Manage course balances, submit wire transfer references, and view receipts.</span>
    </div>
</div>

<div class="row g-4">
    <!-- Enrolled Courses & Balance Due -->
    <div class="col-lg-7">
        <div class="card card-solid p-4 mb-4">
            <h5 class="text-white font-serif fw-bold mb-3">Enrolled Courses & Balances</h5>

            <div class="list-group list-group-flush border-top border-secondary">
                @forelse($enrollments as $enr)
                    <div class="list-group-item bg-transparent text-white px-0 py-3 border-secondary">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="text-white fw-bold mb-0">{{ $enr->course->title }}</h6>
                                <small class="text-muted">Total Tuition: ${{ number_format($enr->course->fee, 2) }} · Status: <span class="badge {{ $enr->statusBadgeClass() }} text-capitalize">{{ $enr->status }}</span></small>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Outstanding Balance</small>
                                <span class="fw-bold fs-5 {{ $enr->balance() > 0 ? 'text-warning' : 'text-success' }}">
                                    ${{ number_format($enr->balance(), 2) }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between small text-muted">
                            <span>Amount Paid: <strong class="text-white">${{ number_format($enr->amountPaid(), 2) }}</strong></span>
                            @if($enr->balance() <= 0)
                                <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Paid in Full</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted py-3 small mb-0">No active course enrollments.</p>
                @endforelse
            </div>
        </div>

        <!-- Payment History Table -->
        <div class="card card-solid p-4">
            <h5 class="text-white font-serif fw-bold mb-3">Transaction Receipts</h5>

            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead class="text-muted small">
                        <tr>
                            <th>Date</th>
                            <th>Masterclass</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                            <tr>
                                <td class="small text-muted">{{ $p->created_at->format('M j, Y') }}</td>
                                <td class="small text-truncate" style="max-width: 160px;">{{ $p->enrollment->course->title }}</td>
                                <td class="text-gold fw-bold">${{ number_format($p->amount, 2) }}</td>
                                <td class="small text-capitalize">{{ $p->methodLabel() }}</td>
                                <td><span class="badge {{ $p->statusBadgeClass() }} text-capitalize">{{ $p->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted small">No payment transactions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $payments->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Submit Payment Form -->
    <div class="col-lg-5">
        <div class="card card-solid p-4 sticky-top" style="top: 90px;">
            <h5 class="text-white font-serif fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-credit-card-2-front text-gold"></i> Submit Payment Reference
            </h5>
            <p class="text-muted small mb-3">
                After completing your bank transfer or tuition remittance, submit your transaction ID below for immediate enrollment activation.
            </p>

            <form action="{{ route('payments.submit') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Select Course Enrollment <span class="text-danger">*</span></label>
                    <select name="enrollment_id" class="form-select form-select-sm" required>
                        <option value="">Select course...</option>
                        @foreach($enrollments as $enr)
                            @if($enr->balance() > 0 || $enr->status === 'pending')
                                <option value="{{ $enr->id }}">
                                    {{ $enr->course->title }} (Due: ${{ number_format($enr->balance(), 2) }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Payment Amount ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" class="form-control form-control-sm" required placeholder="150.00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="method" class="form-select form-select-sm" required>
                            @foreach(\App\Models\Payment::METHODS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Bank Reference / Transaction Code</label>
                    <input type="text" name="reference" class="form-control form-control-sm font-monospace" placeholder="e.g. WIRE-892147 or CHK-4401">
                </div>

                <div class="mb-4">
                    <label class="form-label">Notes for Bursar / Administration</label>
                    <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Any details on payer name, remitting bank..."></textarea>
                </div>

                <button type="submit" class="btn btn-gold w-100">
                    <i class="bi bi-send me-1"></i> Submit Payment Proof
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
