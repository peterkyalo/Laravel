@extends('layouts.app')

@section('title', 'Checkout — ' . $course->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/payment-selector.css') }}?v=1.0">
<style>
    .checkout-summary-card {
        background: #111827;
        border: 1.5px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
    }
    .checkout-feature-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 9999px;
        padding: 0.35rem 0.85rem;
        font-size: 0.8rem;
        color: #cbd5e1;
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Breadcrumb / Back Navigation --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <a href="{{ route('courses.public.show', $course) }}" class="text-gold small text-decoration-none d-inline-flex align-items-center">
                    <i class="bi bi-chevron-left me-1"></i> Back to Course Overview
                </a>
            </nav>

            <h1 class="h2 fw-bold text-white mb-2">Checkout & Enrollment</h1>
            <p class="text-secondary mb-4">Choose your preferred payment method below to unlock your course curriculum and learning portal.</p>

            {{-- Course Tuition Summary Card --}}
            <div class="checkout-summary-card mb-4 p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 pb-3 border-bottom border-secondary border-opacity-25">
                    <div>
                        <span class="badge bg-gold-subtle text-gold border border-warning border-opacity-25 mb-2 px-2 py-1">
                            {{ $course->instrument->name ?? 'Music Masterclass' }}
                        </span>
                        <h4 class="text-white fw-bold mb-1">{{ $course->title }}</h4>
                        <div class="text-muted small d-flex align-items-center gap-2">
                            <span><i class="bi bi-person-fill text-gold me-1"></i> {{ $course->instructor->name }}</span>
                            <span>•</span>
                            <span><i class="bi bi-bar-chart-fill text-gold me-1"></i> {{ ucfirst($course->level) }}</span>
                            <span>•</span>
                            <span><i class="bi bi-clock-fill text-gold me-1"></i> {{ $course->duration_weeks ?? 4 }} Weeks</span>
                        </div>
                    </div>
                    <div class="text-md-end">
                        <div class="text-muted small">Tuition Fee</div>
                        <div class="h3 fw-bold text-white mb-0">{{ $currency }} {{ number_format($course->price, 2) }}</div>
                        <div class="text-gold small">≈ KES {{ number_format($kesAmount) }}</div>
                    </div>
                </div>

                {{-- Tuition Balance Breakdown --}}
                <div class="pt-3">
                    <div class="d-flex justify-content-between text-secondary mb-2 small">
                        <span>Standard Tuition Rate</span>
                        <span>{{ $currency }} {{ number_format($course->price, 2) }}</span>
                    </div>
                    @if($amountPaid > 0)
                        <div class="d-flex justify-content-between text-success mb-2 small">
                            <span>Prior Credits / Paid</span>
                            <span>-{{ $currency }} {{ number_format($amountPaid, 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between fs-5 fw-bold text-white pt-2 border-top border-secondary border-opacity-25">
                        <span>Total Due Today</span>
                        <span class="text-gold">{{ $currency }} {{ number_format($balance, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Existing Pending Cash Alert --}}
            @if($pendingCash)
                <div class="alert bg-warning-subtle border-warning text-warning-emphasis mb-4 d-flex align-items-center justify-content-between" role="alert">
                    <div>
                        <i class="bi bi-clock-history me-2 fs-5"></i>
                        You have an existing pending cash reference ({{ $pendingCash->reference }}) for {{ $currency }} {{ number_format($pendingCash->amount, 2) }}.
                    </div>
                    <a href="{{ route('checkout.pending', $pendingCash) }}" class="btn btn-sm btn-warning text-dark fw-bold text-nowrap ms-3">
                        View Reference
                    </a>
                </div>
            @endif

            {{-- Payment Selector Header --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white fw-bold mb-0">Select Payment Method</h5>
                <div class="d-flex gap-2">
                    <span class="checkout-feature-pill">
                        <i class="bi bi-geo-alt-fill text-gold"></i>
                        Region: <strong>{{ $defaultRegion }}</strong>
                    </span>
                </div>
            </div>

            {{-- Modular Multi-Gateway Payment Selector Component --}}
            @include('checkout.components.payment-selector')

        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Load Stripe.js for Card Elements --}}
<script src="https://js.stripe.com/v3/"></script>
{{-- Multi-Gateway Payment Selector Controller --}}
<script src="{{ asset('js/payment-selector.js') }}?v=1.0"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.paymentSelectorApp = new PaymentSelector({
            containerId: 'payment-selector-container',
            courseId: {{ $course->id }},
            courseSlug: '{{ $course->slug }}',
            amount: {{ $balance }},
            kesAmount: {{ $kesAmount }},
            currency: '{{ $currency }}',
            defaultRegion: '{{ $defaultRegion }}',
            defaultMethod: '{{ $defaultMethod }}',
            stripePublishableKey: '{{ $stripePublishableKey ?? "" }}',
            csrfToken: '{{ csrf_token() }}',
            userName: '{{ addslashes($userName) }}',
            userEmail: '{{ addslashes($userEmail) }}',
            simulating: @json($simulating)
        });
    });
</script>
@endpush
