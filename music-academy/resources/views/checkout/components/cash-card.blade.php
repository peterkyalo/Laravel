{{-- Cash on Delivery / Pickup Sub-Card --}}
<div class="payment-method-card"
     data-method="cash"
     role="radio"
     aria-checked="false"
     tabindex="-1"
     aria-labelledby="label-method-cash">
    
    <div class="payment-method-header">
        <div class="payment-method-header-left">
            <div class="payment-radio-indicator" aria-hidden="true">
                <div class="payment-radio-inner"></div>
            </div>

            <div class="gateway-icon-badge cash-badge" aria-hidden="true">
                <i class="bi bi-cash-stack fs-4"></i>
            </div>

            <div>
                <h6 class="payment-method-title" id="label-method-cash">
                    Cash on Delivery / Pickup
                </h6>
                <p class="payment-method-subtitle">Pay in person upon physical pickup or at the Bursar Desk</p>
            </div>
        </div>

        <div class="payment-method-badges">
            <span class="trust-badge-pill"><i class="bi bi-geo-alt-fill text-warning me-1"></i> Campus / Pickup</span>
        </div>
    </div>

    {{-- Disclosed Inline Form Panel --}}
    <div class="payment-method-body" id="body-method-cash">
        <div class="payment-alert-box warning mb-3">
            <i class="bi bi-exclamation-circle-fill fs-5 mt-1 text-warning flex-shrink-0"></i>
            <div>
                <strong>Payment Instructions:</strong> Please ensure you have the exact amount ready upon delivery or physical pickup. A payment reference number will be generated immediately for verification.
            </div>
        </div>

        <div class="payment-form-group">
            <label for="cash-notes-input" class="payment-label">
                <i class="bi bi-pencil-square text-gold me-1"></i> Special Instructions / Pickup Notes (Optional)
            </label>
            <textarea id="cash-notes-input"
                      class="payment-input"
                      rows="2"
                      style="height: auto; resize: vertical;"
                      placeholder="e.g. Will visit Vienna main campus bursar desk on Friday afternoon..."></textarea>
            <div class="payment-hint">
                {{ config('payments.cash.instructions') }}
            </div>
        </div>
    </div>
</div>
