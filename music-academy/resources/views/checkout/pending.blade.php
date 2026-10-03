@extends('layouts.app')

@section('title', 'Payment Pending — ' . $course->title)

@section('content')
<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card bg-surface border-0 shadow-sm p-5">
                @if($payment->method === 'mpesa')
                    @if(isset($payment->meta['c2b_initiated']) && $payment->meta['c2b_initiated'])
                        <i class="bi bi-wallet2 display-1 text-success mb-4"></i>
                        <h2 class="h3 fw-bold text-white mb-3">M-Pesa PayBill Instructions</h2>
                        <p class="text-muted mb-4">Go to your M-Pesa menu, select Lipa na M-Pesa &rarr; PayBill, and enter the details below. This page will automatically update once we receive the payment.</p>
                        
                        <div class="row text-start justify-content-center mb-4">
                            <div class="col-sm-8 col-md-6">
                                <ul class="list-group list-group-flush bg-transparent border-secondary">
                                    <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between px-0">
                                        <span class="text-muted">Business Number</span>
                                        <strong>{{ config('payments.mpesa.shortcode') }}</strong>
                                    </li>
                                    <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between px-0">
                                        <span class="text-muted">Account Number</span>
                                        <strong class="text-success fs-5">HMA{{ $payment->id }}</strong>
                                    </li>
                                    <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between px-0">
                                        <span class="text-muted">Amount</span>
                                        <strong class="text-white fs-5">KES {{ number_format($payment->meta['amount_kes'] ?? 0) }}</strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @else
                        <div class="spinner-grow text-success mb-4" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h2 class="h3 fw-bold text-white mb-3">Awaiting M-Pesa Confirmation</h2>
                        <p class="text-muted mb-4">Please check your phone and enter your M-Pesa PIN. Do not close this page — it will automatically update once the payment is received.</p>
                        <div class="alert alert-dark border-secondary">
                            Payment Reference: <strong class="text-white">{{ $payment->reference }}</strong><br>
                            Amount: <strong class="text-white">KES {{ number_format($payment->meta['amount_kes'] ?? 0) }}</strong>
                        </div>
                    @endif

                    @if($simulating)
                        <div class="mt-4 p-3 bg-secondary bg-opacity-25 rounded border border-secondary">
                            <p class="mb-2 text-warning small"><i class="bi bi-tools me-1"></i> Sandbox Simulator Active</p>
                            <form action="{{ route('checkout.simulate.complete', $payment) }}" method="POST">
                                @csrf
                                <button name="outcome" value="success" class="btn btn-sm btn-success w-100 mb-2">Simulate Successful Payment</button>
                                <button name="outcome" value="fail" class="btn btn-sm btn-outline-danger w-100">Simulate Failed Payment</button>
                            </form>
                        </div>
                    @endif

                @elseif($payment->method === 'cash')
                    <i class="bi bi-upc-scan display-1 text-warning mb-4"></i>
                    <h2 class="h3 fw-bold text-white mb-3">Cash Payment Reference</h2>
                    <p class="text-muted mb-4">{{ config('payments.cash.instructions') }}</p>
                    <div class="alert bg-warning-subtle border-warning p-4">
                        <h4 class="text-warning-emphasis mb-1">{{ $payment->reference }}</h4>
                        <span class="small text-muted">Amount Due: {{ $payment->currency }} {{ number_format($payment->amount, 2) }}</span>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('learning.course', $course) }}" class="btn btn-outline-light">Return to Course Details</a>
                    </div>
                @endif
            </div>
            
            @if($payment->method === 'mpesa' && !$simulating)
                <div class="mt-4 text-muted small">
                    Did it fail or timeout? <a href="{{ route('checkout.cancel', $payment) }}" class="text-danger">Cancel and try again</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@if($payment->method === 'mpesa' && !$simulating)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let attempts = 0;
            const maxAttempts = 24; // 2 minutes total
            
            const checkStatus = setInterval(() => {
                attempts++;
                fetch('{{ route('checkout.status', $payment) }}', {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.paid && data.redirect) {
                        clearInterval(checkStatus);
                        window.location.href = data.redirect;
                    } else if (data.status === 'failed' || data.status === 'cancelled') {
                        clearInterval(checkStatus);
                        window.location.reload();
                    }
                });

                if (attempts >= maxAttempts) clearInterval(checkStatus);
            }, 5000);
        });
    </script>
    @endpush
@endif
