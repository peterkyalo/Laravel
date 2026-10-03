@extends('layouts.app')

@section('title', 'Checkout — ' . $course->title)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="h2 fw-bold text-white mb-4">Complete Your Enrollment</h1>

            <div class="card bg-surface border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title text-gold mb-3">Tuition Summary</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Course</span>
                        <strong>{{ $course->title }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Instructor</span>
                        <span>{{ $course->instructor->name }}</span>
                    </div>
                    <hr class="border-secondary">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Tuition</span>
                        <span>{{ $currency }} {{ number_format($course->price, 2) }}</span>
                    </div>
                    @if($amountPaid > 0)
                        <div class="d-flex justify-content-between text-success mb-2">
                            <span>Amount Paid</span>
                            <span>-{{ $currency }} {{ number_format($amountPaid, 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between fs-4 fw-bold text-white mt-3 pt-3 border-top border-secondary">
                        <span>Balance Due</span>
                        <span>{{ $currency }} {{ number_format($balance, 2) }}</span>
                    </div>
                </div>
            </div>

            @if($pendingCash)
                <div class="alert bg-warning-subtle border-warning text-warning-emphasis mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i> You have a pending cash payment of {{ $currency }} {{ number_format($pendingCash->amount, 2) }}.
                    <a href="{{ route('checkout.pending', $pendingCash) }}" class="fw-bold">View Instructions</a>
                </div>
            @endif

            <h5 class="mb-3 text-white">Select Payment Method</h5>

            <div class="row g-3">
                @foreach($methods as $method)
                    <div class="col-md-6">
                        <div class="card bg-surface border-secondary h-100 hover-gold-border cursor-pointer" onclick="document.getElementById('form-{{ $method }}').classList.remove('d-none'); document.querySelectorAll('.payment-form').forEach(f => f.id !== 'form-{{ $method }}' ? f.classList.add('d-none') : null);">
                            <div class="card-body text-center p-4">
                                @if($method === 'stripe')
                                    <i class="bi bi-credit-card fs-1 text-primary mb-2 d-block"></i>
                                    <h6 class="mb-0">Credit / Debit Card</h6>
                                @elseif($method === 'paypal')
                                    <i class="bi bi-paypal fs-1 text-info mb-2 d-block"></i>
                                    <h6 class="mb-0">PayPal</h6>
                                @elseif($method === 'mpesa')
                                    <i class="bi bi-phone fs-1 text-success mb-2 d-block"></i>
                                    <h6 class="mb-0">M-Pesa Express</h6>
                                @elseif($method === 'cash')
                                    <i class="bi bi-cash-stack fs-1 text-warning mb-2 d-block"></i>
                                    <h6 class="mb-0">Pay in Cash</h6>
                                @endif
                                
                                @if($simulating->contains($method))
                                    <span class="badge bg-secondary mt-2">Simulator</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                @if($methods->contains('stripe'))
                    <form action="{{ route('checkout.stripe', $course) }}" method="POST" id="form-stripe" class="payment-form d-none">
                        @csrf
                        <div class="card bg-surface border-primary">
                            <div class="card-body text-center">
                                <p class="mb-3">You will be securely redirected to Stripe to complete your payment.</p>
                                <button type="submit" class="btn btn-primary px-4 py-2">
                                    <i class="bi bi-credit-card me-2"></i> Pay {{ $currency }} {{ number_format($balance, 2) }} with Stripe
                                </button>
                            </div>
                        </div>
                    </form>
                @endif

                @if($methods->contains('paypal'))
                    <form action="{{ route('checkout.paypal', $course) }}" method="POST" id="form-paypal" class="payment-form d-none">
                        @csrf
                        <div class="card bg-surface border-info">
                            <div class="card-body text-center">
                                <p class="mb-3">You will be securely redirected to PayPal to authorize the payment.</p>
                                <button type="submit" class="btn btn-info text-dark px-4 py-2">
                                    <i class="bi bi-paypal me-2"></i> Pay {{ $currency }} {{ number_format($balance, 2) }} with PayPal
                                </button>
                            </div>
                        </div>
                    </form>
                @endif

                @if($methods->contains('mpesa'))
                    <div id="form-mpesa" class="payment-form d-none">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <form action="{{ route('checkout.mpesa', $course) }}" method="POST">
                                    @csrf
                                    <div class="card bg-surface border-success h-100">
                                        <div class="card-body">
                                            <h6 class="text-success mb-3"><i class="bi bi-phone-vibrate me-2"></i> STK Push (Instant)</h6>
                                            <div class="mb-3">
                                                <label class="form-label text-muted small">Safaricom Phone Number</label>
                                                <input type="text" name="phone" class="form-control bg-dark border-secondary text-white" placeholder="07XX XXX XXX" required>
                                                <div class="form-text">You'll get a PIN prompt for KES {{ number_format($kesAmount) }}.</div>
                                            </div>
                                            <div class="text-center">
                                                <button type="submit" class="btn btn-success w-100">Send Prompt</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form action="{{ route('checkout.mpesa.c2b', $course) }}" method="POST">
                                    @csrf
                                    <div class="card bg-surface border-success border-opacity-50 h-100">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <div>
                                                <h6 class="text-success mb-3"><i class="bi bi-wallet2 me-2"></i> Pay manually (PayBill)</h6>
                                                <p class="text-muted small mb-0">Don't have your phone right now? Generate an account number and pay manually via the M-Pesa menu later.</p>
                                            </div>
                                            <div class="text-center mt-3">
                                                <button type="submit" class="btn btn-outline-success w-100">Get PayBill Instructions</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                @if($methods->contains('cash'))
                    <form action="{{ route('checkout.cash', $course) }}" method="POST" id="form-cash" class="payment-form d-none">
                        @csrf
                        <div class="card bg-surface border-warning">
                            <div class="card-body text-center">
                                <p class="mb-3">{{ config('payments.cash.instructions') }}</p>
                                <button type="submit" class="btn btn-warning px-4 py-2">
                                    <i class="bi bi-cash me-2"></i> Generate Cash Reference
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
