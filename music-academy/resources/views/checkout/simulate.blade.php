@extends('layouts.app')

@section('title', 'Sandbox Simulator — ' . $course->title)

@section('content')
<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card border-warning bg-surface p-4">
                <i class="bi bi-cone-striped display-3 text-warning mb-3"></i>
                <h3 class="fw-bold text-white mb-2">Sandbox Simulator</h3>
                <p class="text-muted mb-4">No API credentials were provided for <strong>{{ ucfirst($payment->method) }}</strong>, so you have been redirected to this mock gateway.</p>
                
                <div class="alert alert-dark text-start border-secondary mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Payment ID</span>
                        <span class="text-white">{{ $payment->id }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Method</span>
                        <span class="text-white text-capitalize">{{ $payment->method }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Amount</span>
                        <span class="text-white fw-bold">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</span>
                    </div>
                </div>

                <form action="{{ route('checkout.simulate.complete', $payment) }}" method="POST" class="d-grid gap-2">
                    @csrf
                    <button type="submit" name="outcome" value="success" class="btn btn-success py-2">
                        <i class="bi bi-check-circle me-2"></i> Approve Payment
                    </button>
                    <button type="submit" name="outcome" value="fail" class="btn btn-outline-danger py-2">
                        <i class="bi bi-x-circle me-2"></i> Decline Payment
                    </button>
                </form>
                
                <div class="mt-4">
                    <a href="{{ route('checkout.cancel', $payment) }}" class="text-muted small">Cancel and go back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
