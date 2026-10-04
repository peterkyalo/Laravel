@extends('layouts.dashboard')

@section('title', 'My Tuition & Payments — Harmonia')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">Tuition & Financial Account</h2>
        <span class="text-muted small">Manage course balances, access multi-gateway instant checkout, and submit payment records.</span>
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
                        <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                            <div>
                                <h6 class="text-white fw-bold mb-1">{{ $enr->course->title }}</h6>
                                <small class="text-muted">
                                    Session Ref: <span class="font-monospace text-gold">HMA-ENR-{{ $enr->id }}</span> · 
                                    Total Tuition: KES {{ number_format($enr->course->fee, 2) }} · 
                                    Status: <span class="badge {{ $enr->statusBadgeClass() }} text-capitalize">{{ $enr->status }}</span>
                                </small>
                            </div>
                            <div class="text-md-end">
                                <small class="text-muted d-block">Outstanding Balance</small>
                                <span class="fw-bold fs-5 {{ $enr->balance() > 0 ? 'text-warning' : 'text-success' }}">
                                    KES {{ number_format($enr->balance(), 2) }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center small text-muted flex-wrap gap-2 mt-2">
                            <span>Amount Paid: <strong class="text-white">KES {{ number_format($enr->amountPaid(), 2) }}</strong></span>
                            @if($enr->balance() <= 0)
                                <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Paid in Full</span>
                            @else
                                <div class="d-flex gap-2">
                                    <a href="{{ route('checkout.show', $enr->course) }}" class="btn btn-sm btn-gold">
                                        <i class="bi bi-lightning-charge-fill me-1"></i> Pay Now (Instant Checkout)
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted py-3 small mb-0">No course enrollments found. <a href="{{ route('courses.public.index') }}" class="text-gold">Browse courses</a> to enroll.</p>
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
                                <td class="small text-truncate" style="max-width: 160px;">{{ $p->enrollment->course->title ?? 'Course Tuition' }}</td>
                                <td class="text-gold fw-bold">KES {{ number_format($p->amount, 2) }}</td>
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

    <!-- Submit Payment Form & Session Lock -->
    <div class="col-lg-5">
        <div class="card card-solid p-4 sticky-top" style="top: 90px;">
            @if($selectedEnrollment)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-white font-serif fw-bold mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-credit-card-2-front text-gold"></i> Tuition Payment
                    </h5>
                    <span class="badge bg-warning-subtle text-warning border border-warning border-opacity-25 px-2 py-1">
                        Active Session
                    </span>
                </div>

                {{-- Direct Quick-Checkout Banner --}}
                <div class="alert bg-gold-subtle border-warning border-opacity-50 text-white mb-3 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="text-gold"><i class="bi bi-lightning-charge-fill me-1"></i> Recommended: Instant Checkout</strong>
                    </div>
                    <p class="small text-secondary mb-3">
                        Pay with <strong>M-Pesa Express (STK PIN prompt)</strong>, <strong>Stripe Card Elements</strong>, or <strong>PayPal</strong> for immediate classroom activation without waiting for bursar approval.
                    </p>
                    <a href="{{ route('checkout.show', $selectedEnrollment->course) }}" class="btn btn-gold btn-sm w-100 fw-bold py-2">
                        <i class="bi bi-credit-card me-1"></i> Open Multi-Gateway Checkout (KES {{ number_format($selectedEnrollment->balance(), 2) }})
                    </a>
                </div>

                {{-- Selected Course Session Info (No Dropdown - Locked to Selected Course) --}}
                <div class="p-3 bg-dark bg-opacity-75 border border-secondary rounded-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="text-muted small d-block">Course Selected</span>
                            <strong class="text-white">{{ $selectedEnrollment->course->title }}</strong>
                        </div>
                        <span class="badge bg-secondary font-monospace">HMA-ENR-{{ $selectedEnrollment->id }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center text-muted small pt-2 border-top border-secondary border-opacity-25">
                        <span>Instructor: <span class="text-secondary">{{ $selectedEnrollment->course->instructor->name }}</span></span>
                        <span>Balance: <strong class="text-gold">KES {{ number_format($selectedEnrollment->balance(), 2) }}</strong></span>
                    </div>
                </div>

                {{-- Other Pending Enrollments Switcher (if student has multiple pending courses) --}}
                @php
                    $otherPending = $enrollments->filter(fn($e) => $e->id !== $selectedEnrollment->id && ($e->balance() > 0 || $e->status === 'pending'));
                @endphp
                @if($otherPending->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label text-muted small">Switch Course Session:</label>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($otherPending as $op)
                                <a href="{{ route('payments.index', ['enrollment_id' => $op->id]) }}" class="btn btn-outline-secondary btn-sm text-truncate" style="max-width: 200px; font-size: 0.75rem;">
                                    {{ $op->course->title }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Manual Reference Submission Form --}}
                <div class="pt-2 border-top border-secondary border-opacity-50">
                    <h6 class="text-white fw-bold mb-2 small text-uppercase" style="letter-spacing: 0.5px;">Or Submit Payment Proof</h6>
                    <p class="text-muted small mb-3">
                        If you already paid via bank transfer, M-Pesa PayBill manual menu, or cash, submit your reference below:
                    </p>

                    <form action="{{ route('payments.submit') }}" method="POST">
                        @csrf
                        {{-- Automatic Session ID: Locked to course selected --}}
                        <input type="hidden" name="enrollment_id" value="{{ $selectedEnrollment->id }}">

                        {{-- Amount: Automatic from Session, Read-only --}}
                        <div class="mb-3">
                            <label class="form-label text-muted small">Tuition Due (KES) — Automatically Set</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-dark border-secondary text-gold fw-bold">KES</span>
                                <input type="text"
                                       name="amount"
                                       class="form-control form-control-sm bg-dark border-secondary text-white fw-bold"
                                       value="{{ number_format($selectedEnrollment->balance(), 2, '.', '') }}"
                                       readonly>
                            </div>
                            <small class="text-muted" style="font-size: 0.75rem;">Automatically calculated from selected course enrollment session.</small>
                        </div>

                        {{-- Payment Gateway: Radio Buttons Instead of Dropdown --}}
                        <div class="mb-3">
                            <label class="form-label text-muted small d-block mb-2">Payment Gateway / Method <span class="text-danger">*</span></label>
                            <div class="d-flex flex-column gap-2">
                                <label class="p-2 border border-secondary rounded-2 bg-dark d-flex align-items-center gap-2 cursor-pointer">
                                    <input type="radio" name="method" value="mpesa" class="form-check-input mt-0" checked>
                                    <i class="bi bi-phone text-success fs-5"></i>
                                    <div>
                                        <div class="text-white fw-semibold small">M-Pesa (Express / PayBill)</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Safaricom STK or PayBill receipt code</div>
                                    </div>
                                </label>

                                <label class="p-2 border border-secondary rounded-2 bg-dark d-flex align-items-center gap-2 cursor-pointer">
                                    <input type="radio" name="method" value="stripe" class="form-check-input mt-0">
                                    <i class="bi bi-credit-card text-primary fs-5"></i>
                                    <div>
                                        <div class="text-white fw-semibold small">Credit / Debit Card</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Visa, Mastercard, or Amex receipt</div>
                                    </div>
                                </label>

                                <label class="p-2 border border-secondary rounded-2 bg-dark d-flex align-items-center gap-2 cursor-pointer">
                                    <input type="radio" name="method" value="paypal" class="form-check-input mt-0">
                                    <i class="bi bi-paypal text-info fs-5"></i>
                                    <div>
                                        <div class="text-white fw-semibold small">PayPal</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">PayPal transaction ID / email</div>
                                    </div>
                                </label>

                                <label class="p-2 border border-secondary rounded-2 bg-dark d-flex align-items-center gap-2 cursor-pointer">
                                    <input type="radio" name="method" value="cash" class="form-check-input mt-0">
                                    <i class="bi bi-cash-stack text-warning fs-5"></i>
                                    <div>
                                        <div class="text-white fw-semibold small">Cash on Delivery / Pickup</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">In-person bursar desk or campus receipt</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Transaction Code / Receipt Reference</label>
                            <input type="text" name="reference" class="form-control form-control-sm bg-dark border-secondary text-white font-monospace" placeholder="e.g. MPESA QWE12345 or WIRE-8921">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Notes for Bursar / Administration</label>
                            <textarea name="notes" rows="2" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="Any details on remitting name, date..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-outline-gold w-100 btn-sm py-2">
                            <i class="bi bi-send me-1"></i> Submit Payment Proof
                        </button>
                    </form>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-check-circle-fill text-success fs-1 mb-2 d-block"></i>
                    <h5 class="text-white fw-bold">All Fees Cleared</h5>
                    <p class="text-muted small mb-3">You have no pending tuition payments due right now.</p>
                    <a href="{{ route('courses.public.index') }}" class="btn btn-gold btn-sm">
                        <i class="bi bi-compass me-1"></i> Explore New Masterclasses
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
