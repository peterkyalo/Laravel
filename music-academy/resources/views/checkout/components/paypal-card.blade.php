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

            <div class="gateway-icon-wrapper" aria-hidden="true" style="width: 50px; height: 35px; background: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; padding: 5px;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal" style="max-height: 100%; max-width: 100%;">
            </div>

            <div>
                <h6 class="payment-method-title" id="label-method-paypal">
                    PayPal
                    @if($simulating->contains('paypal'))
                        <span class="badge bg-secondary-subtle border border-secondary text-secondary small py-0 px-2" style="font-size: 0.7rem;">Simulator</span>
                    @endif
                </h6>
            </div>
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
