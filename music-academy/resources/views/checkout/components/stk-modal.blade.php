{{-- Daraja M-Pesa STK Push 60-Second Countdown Modal Overlay --}}
<div id="stk-countdown-modal"
     class="stk-modal-overlay"
     role="dialog"
     aria-modal="true"
     aria-labelledby="stk-modal-heading"
     aria-describedby="stk-modal-desc">

    <div class="stk-modal-dialog">
        {{-- Circular Countdown Ring with Phone Radar Animation --}}
        <div class="stk-timer-wrapper" aria-hidden="true">
            <div class="radar-pulse-ring"></div>
            <svg class="stk-svg-ring" viewBox="0 0 120 120">
                <circle class="circle-bg" cx="60" cy="60" r="54" />
                <circle id="stk-circle-progress" class="circle-progress" cx="60" cy="60" r="54" />
            </svg>
            <div class="stk-timer-content">
                <span class="stk-seconds-digital" id="stk-seconds-number">0:60</span>
                <span class="stk-seconds-label">seconds</span>
            </div>
        </div>

        <div class="stk-status-pill" id="stk-status-pill">
            <span class="stk-status-dot"></span>
            <span id="stk-status-text">Awaiting M-Pesa PIN...</span>
        </div>

        <h4 class="fw-bold text-white mb-2" id="stk-modal-heading">STK Push Sent</h4>
        
        <p class="text-secondary small mb-4" id="stk-modal-desc">
            STK Push prompt sent to <strong class="text-white" id="stk-phone-display">+254...</strong>. Please unlock your device and enter your M-Pesa PIN.
        </p>

        {{-- Hidden Screen Reader Announcer --}}
        <div id="stk-live-announcer" class="visually-hidden" aria-live="polite"></div>

        <div class="stk-modal-actions">
            <button type="button" id="btn-resend-stk" class="btn-resend-prompt" disabled>
                <i class="bi bi-arrow-repeat me-1"></i> Resend Prompt
            </button>
            <button type="button" id="btn-cancel-stk" class="btn-cancel-stk">
                Cancel & Try Another Method
            </button>
        </div>
    </div>
</div>
