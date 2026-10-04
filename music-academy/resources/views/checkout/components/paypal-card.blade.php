{{-- PayPal Sub-Card --}}
<div class="payment-method-card"
     data-method="paypal"
     role="radio"
     aria-checked="false"
     tabindex="-1"
     aria-labelledby="label-method-paypal">
    
    <div class="payment-method-header">
        <div class="payment-method-header-left">
            <div class="payment-radio-indicator" aria-hidden="true">
                <div class="payment-radio-inner"></div>
            </div>

            <div class="gateway-icon-badge paypal-badge" aria-hidden="true">
                <i class="bi bi-paypal fs-4"></i>
            </div>

            <div>
                <h6 class="payment-method-title" id="label-method-paypal">
                    PayPal
                    @if($simulating->contains('paypal'))
                        <span class="badge bg-secondary-subtle border border-secondary text-secondary small py-0 px-2" style="font-size: 0.7rem;">Simulator</span>
                    @endif
                </h6>
                <p class="payment-method-subtitle">PayPal Balance, Linked Bank Accounts & Global Cards</p>
            </div>
        </div>

        <div class="payment-method-badges">
            <span class="trust-badge-pill"><i class="bi bi-shield-check text-info me-1"></i> Buyer Protection</span>
        </div>
    </div>

    {{-- Disclosed Inline Form Panel --}}
    <div class="payment-method-body" id="body-method-paypal">
        <div class="payment-alert-box info mb-0">
            <i class="bi bi-box-arrow-up-right fs-5 mt-1 text-info flex-shrink-0"></i>
            <div>
                <p class="mb-1"><strong>Secure External Authorization:</strong></p>
                <span>You will be securely redirected to PayPal to authorize your transaction. After completing authorization, you will automatically return here to access your course.</span>
            </div>
        </div>
    </div>
</div>
