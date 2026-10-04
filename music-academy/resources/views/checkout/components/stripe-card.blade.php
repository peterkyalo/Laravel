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

            <div class="gateway-icon-wrapper" aria-hidden="true" style="width: 50px; height: 35px; background: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; padding: 5px;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_Logo%2C_revised_2016.svg" alt="Stripe" style="max-height: 100%; max-width: 100%;">
            </div>

            <div>
                <h6 class="payment-method-title" id="label-method-stripe">
                    Credit / Debit Card
                    @if($simulating->contains('stripe'))
                        <span class="badge bg-secondary-subtle border border-secondary text-secondary small py-0 px-2" style="font-size: 0.7rem;">Simulator</span>
                    @endif
                </h6>
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
