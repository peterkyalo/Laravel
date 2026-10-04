{{-- M-Pesa Express Sub-Card --}}
<div class="payment-method-card"
     data-method="mpesa"
     role="radio"
     aria-checked="false"
     tabindex="-1"
     aria-labelledby="label-method-mpesa">
    
    <div class="payment-method-header">
        <div class="payment-method-header-left">
            <div class="payment-radio-indicator" aria-hidden="true">
                <div class="payment-radio-inner"></div>
            </div>

            <div class="gateway-icon-badge mpesa-badge" aria-hidden="true">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2Z" fill="#00A859"/>
                    <path d="M8 8V16L12 11L16 16V8" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <div>
                <h6 class="payment-method-title" id="label-method-mpesa">
                    M-Pesa Express
                    @if($simulating->contains('mpesa'))
                        <span class="badge bg-secondary-subtle border border-secondary text-secondary small py-0 px-2" style="font-size: 0.7rem;">Simulator</span>
                    @endif
                </h6>
                <p class="payment-method-subtitle">Instant Daraja STK PIN Push • Safaricom Kenya</p>
            </div>
        </div>

        <div class="payment-method-badges">
            <span class="trust-badge-pill"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Instant STK</span>
            <span class="trust-badge-pill text-gold">KES {{ number_format($kesAmount) }}</span>
        </div>
    </div>

    {{-- Disclosed Inline Form Panel --}}
    <div class="payment-method-body" id="body-method-mpesa">
        <div class="payment-form-group">
            <label for="mpesa-phone-input" class="payment-label">
                <i class="bi bi-phone-fill text-success me-1"></i> Safaricom Mobile Number
            </label>
            <div class="payment-input-group">
                <span class="payment-input-addon">
                    <span style="font-size: 1.1rem; line-height: 1;">🇰🇪</span> +254
                </span>
                <input type="tel"
                       id="mpesa-phone-input"
                       class="payment-input"
                       placeholder="712 345 678"
                       maxlength="13"
                       value="{{ preg_replace('/^(?:\+?254|0)/', '', $userPhone) }}"
                       autocomplete="tel-local">
            </div>
            <div id="mpesa-phone-error" class="payment-feedback-error" role="alert"></div>
            <div class="payment-hint d-flex justify-content-between align-items-center">
                <span>Enter 7XX... or 1XX... (e.g. 0712 345 678). Standard Safaricom rates apply.</span>
                <span class="text-gold fw-semibold">Tuition: KES {{ number_format($kesAmount) }}</span>
            </div>
        </div>

        <div class="payment-alert-box info mb-3">
            <i class="bi bi-info-circle-fill fs-5 mt-1 text-info flex-shrink-0"></i>
            <div>
                <strong>How STK Push works:</strong> When you click the payment button, a PIN prompt will pop up on your phone. Unlock your phone, enter your secret M-Pesa PIN, and your enrollment unlocks automatically.
            </div>
        </div>

        {{-- Fallback link for PayBill --}}
        <div class="pt-2 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
            <span class="text-muted small">Need to pay without your phone?</span>
            <form action="{{ route('checkout.mpesa.c2b', $course) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-link btn-sm text-gold p-0 text-decoration-none">
                    <i class="bi bi-receipt me-1"></i> Get Manual PayBill Details
                </button>
            </form>
        </div>
    </div>
</div>
