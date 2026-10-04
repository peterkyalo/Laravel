{{-- Stripe Card Elements Sub-Card --}}
<div class="payment-method-card"
     data-method="stripe"
     role="radio"
     aria-checked="false"
     tabindex="-1"
     aria-labelledby="label-method-stripe">
    
    <div class="payment-method-header">
        <div class="payment-method-header-left">
            <div class="payment-radio-indicator" aria-hidden="true">
                <div class="payment-radio-inner"></div>
            </div>

            <div class="gateway-icon-badge stripe-badge" aria-hidden="true">
                <i class="bi bi-credit-card-2-front-fill fs-4"></i>
            </div>

            <div>
                <h6 class="payment-method-title" id="label-method-stripe">
                    Credit or Debit Card
                    @if($simulating->contains('stripe'))
                        <span class="badge bg-secondary-subtle border border-secondary text-secondary small py-0 px-2" style="font-size: 0.7rem;">Simulator</span>
                    @endif
                </h6>
                <p class="payment-method-subtitle">Visa, Mastercard, American Express & Discover</p>
            </div>
        </div>

        <div class="payment-method-badges">
            <div class="card-logos-inline" aria-label="Supported cards">
                {{-- Visa SVG --}}
                <span class="badge bg-light text-dark fw-bold px-2 py-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">VISA</span>
                {{-- Mastercard badge --}}
                <span class="badge bg-danger text-white fw-bold px-2 py-1" style="font-size: 0.7rem; background-color: #eb001b !important;">MC</span>
                {{-- Amex badge --}}
                <span class="badge bg-primary text-white fw-bold px-2 py-1" style="font-size: 0.7rem; background-color: #007bc1 !important;">AMEX</span>
            </div>
        </div>
    </div>

    {{-- Disclosed Inline Form Panel --}}
    <div class="payment-method-body" id="body-method-stripe">
        <div class="payment-form-group">
            <label class="payment-label">
                <i class="bi bi-shield-lock-fill text-primary me-1"></i> Card Information
            </label>
            {{-- Stripe Elements Mount Container --}}
            <div id="stripe-card-element" class="stripe-element-container">
                <div class="text-center py-2 text-muted small">
                    <span class="spinner-border spinner-border-sm me-2 text-gold"></span> Initializing secure card elements...
                </div>
            </div>
            <div id="stripe-card-error" class="payment-feedback-error" role="alert"></div>
        </div>

        <div class="payment-form-group mb-3">
            <label class="payment-checkbox-label">
                <input type="checkbox" id="stripe-save-card" name="save_card" value="1">
                <span class="payment-custom-checkbox">
                    <i class="bi bi-check" style="font-size: 14px; font-weight: bold;"></i>
                </span>
                <span>Save card details for future payments</span>
            </label>
        </div>

        <div class="payment-alert-box info mb-0">
            <i class="bi bi-lock-fill fs-5 mt-1 text-primary flex-shrink-0"></i>
            <div>
                <strong>Bank-Grade Encryption:</strong> Your card data is encrypted end-to-end via Stripe. 3D-Secure authentication is supported automatically for participating banks.
            </div>
        </div>
    </div>
</div>
