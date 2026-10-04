{{-- Multi-Gateway Payment Selector Component --}}
<div class="payment-selector-component">
    {{-- Global Error Feedback Container --}}
    <div id="payment-global-error"></div>

    {{-- Accordion Radiogroup Container --}}
    <div id="payment-selector-container"
         class="payment-selector-wrapper"
         role="radiogroup"
         aria-label="Payment Method Selector">

        {{-- Option A: M-Pesa Express (STK Push) --}}
        @if($methods->contains('mpesa'))
            @include('checkout.components.mpesa-card')
        @endif

        {{-- Option B: Stripe (Credit/Debit Card) --}}
        @if($methods->contains('stripe'))
            @include('checkout.components.stripe-card')
        @endif

        {{-- Option C: PayPal --}}
        @if($methods->contains('paypal'))
            @include('checkout.components.paypal-card')
        @endif

        {{-- Option D: Cash on Delivery / Pickup --}}
        @if($methods->contains('cash'))
            @include('checkout.components.cash-card')
        @endif

    </div>

    {{-- Dynamic Primary CTA Button --}}
    <div class="mt-4">
        <button type="button"
                id="btn-submit-payment"
                class="btn-primary-payment-cta">
            <span id="payment-cta-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            <span id="payment-cta-text">Pay Tuition</span>
            <i class="bi bi-arrow-right ms-1"></i>
        </button>
        <div class="text-center mt-3">
            <span class="text-muted small">
                <i class="bi bi-shield-check text-gold me-1"></i> 256-Bit SSL Encrypted • Zero Hidden Surcharges • Immediate Access
            </span>
        </div>
    </div>

    {{-- STK 60-Second Countdown Modal Overlay --}}
    @include('checkout.components.stk-modal')
</div>
