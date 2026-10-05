/**
 * Baritone Music Academy — Multi-Gateway Checkout Payment Selector
 * Supports M-Pesa (Daraja STK Push), Stripe (Card Elements), PayPal & Cash
 */

(function () {
    'use strict';

    class PaymentSelector {
        constructor(config) {
            this.config = Object.assign({
                containerId: 'payment-selector-container',
                courseId: null,
                courseSlug: '',
                amount: 0,
                kesAmount: 0,
                currency: 'USD',
                defaultRegion: 'KE',
                defaultMethod: 'mpesa',
                stripePublishableKey: '',
                csrfToken: '',
                simulating: [],
                endpoints: {
                    mpesaStkPush: '/api/payments/mpesa/stk-push',
                    mpesaQuery: '/api/payments/mpesa/query',
                    stripeCreateIntent: '/api/payments/stripe/create-intent',
                    stripeConfirm: '/api/payments/stripe/confirm',
                    paypalCreateOrder: '/api/payments/paypal/create-order',
                    cashCreate: '/api/payments/cash/create'
                }
            }, config);

            // State management
            this.state = {
                selectedMethod: this.resolveInitialMethod(),
                transactionState: 'idle', // 'idle' | 'submitting' | 'awaiting_pin' | 'polling' | 'success' | 'error'
                errorMessage: '',
                currentPaymentId: null,
                currentCheckoutRequestId: null,
                stkCountdown: 60,
                stkTimerId: null,
                pollTimerId: null,
                resendCooldown: false
            };

            // Form inputs cache to preserve state when switching methods
            this.formCache = {
                phone: '',
                saveCard: false,
                cardNumber: '',
                cardExpiry: '',
                cardCvc: '',
                cashNotes: ''
            };

            this.stripeInstance = null;
            this.stripeCardElement = null;

            this.init();
        }

        resolveInitialMethod() {
            // Dynamic Localization:
            // If region is KE or East Africa, default selectedMethod to mpesa. Otherwise stripe.
            const eastAfrica = ['KE', 'UG', 'TZ', 'RW', 'BI', 'SS'];
            const isEastAfrica = eastAfrica.includes(String(this.config.defaultRegion).toUpperCase());
            const preferred = isEastAfrica ? 'mpesa' : 'stripe';

            // Verify if preferred method is in available methods on page
            return this.config.defaultMethod || preferred;
        }

        init() {
            this.cacheDom();
            this.bindEvents();
            this.initStripe();
            this.selectMethod(this.state.selectedMethod, false);
            this.updateCtaButton();
        }

        cacheDom() {
            this.container = document.getElementById(this.config.containerId);
            if (!this.container) return;

            this.cards = this.container.querySelectorAll('.payment-method-card');
            this.ctaButton = document.getElementById('btn-submit-payment');
            this.ctaText = document.getElementById('payment-cta-text');
            this.ctaSpinner = document.getElementById('payment-cta-spinner');
            this.globalErrorContainer = document.getElementById('payment-global-error');

            // Sub-card inputs
            this.phoneInput = document.getElementById('mpesa-phone-input');
            this.phoneError = document.getElementById('mpesa-phone-error');
            this.saveCardCheckbox = document.getElementById('stripe-save-card');
            this.cashNotesInput = document.getElementById('cash-notes-input');

            // STK Modal Elements
            this.stkModal = document.getElementById('stk-countdown-modal');
            this.stkCircle = document.getElementById('stk-circle-progress');
            this.stkSecondsText = document.getElementById('stk-seconds-number');
            this.stkStatusText = document.getElementById('stk-status-text');
            this.stkPhoneDisplay = document.getElementById('stk-phone-display');
            this.btnResendPrompt = document.getElementById('btn-resend-stk');
            this.btnCancelStk = document.getElementById('btn-cancel-stk');
            this.stkLiveRegion = document.getElementById('stk-live-announcer');
        }

        bindEvents() {
            if (!this.container) return;

            // Card selection via click and keyboard (accessibility)
            this.cards.forEach(card => {
                const method = card.getAttribute('data-method');

                card.addEventListener('click', (e) => {
                    // Do not toggle if clicking directly on an input or textarea inside active panel
                    if (['INPUT', 'TEXTAREA', 'BUTTON', 'A', 'LABEL'].includes(e.target.tagName)) {
                        return;
                    }
                    this.selectMethod(method);
                });

                card.addEventListener('keydown', (e) => {
                    if (e.key === ' ' || e.key === 'Enter') {
                        e.preventDefault();
                        this.selectMethod(method);
                    } else if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
                        e.preventDefault();
                        this.selectNextCard(card, 1);
                    } else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
                        e.preventDefault();
                        this.selectNextCard(card, -1);
                    }
                });
            });

            // Form inputs cache & validation
            if (this.phoneInput) {
                this.phoneInput.addEventListener('input', (e) => {
                    this.formatKenyanPhoneInput(e.target);
                    this.formCache.phone = e.target.value;
                    this.clearError(this.phoneInput, this.phoneError);
                });
            }

            if (this.saveCardCheckbox) {
                this.saveCardCheckbox.addEventListener('change', (e) => {
                    this.formCache.saveCard = e.target.checked;
                });
            }

            if (this.cashNotesInput) {
                this.cashNotesInput.addEventListener('input', (e) => {
                    this.formCache.cashNotes = e.target.value;
                });
            }

            // Primary CTA Click
            if (this.ctaButton) {
                this.ctaButton.addEventListener('click', () => {
                    this.handleSubmit();
                });
            }

            // STK Modal actions
            if (this.btnResendPrompt) {
                this.btnResendPrompt.addEventListener('click', () => {
                    this.resendStkPrompt();
                });
            }

            if (this.btnCancelStk) {
                this.btnCancelStk.addEventListener('click', () => {
                    this.closeStkModal();
                });
            }

            // Close modal on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.stkModal && this.stkModal.classList.contains('active')) {
                    this.closeStkModal();
                }
            });
        }

        selectNextCard(currentCard, direction) {
            const cardArray = Array.from(this.cards);
            const currentIndex = cardArray.indexOf(currentCard);
            const nextIndex = (currentIndex + direction + cardArray.length) % cardArray.length;
            const targetCard = cardArray[nextIndex];
            if (targetCard) {
                const method = targetCard.getAttribute('data-method');
                this.selectMethod(method);
                targetCard.focus();
            }
        }

        selectMethod(method, updateFocus = true) {
            if (!method) return;
            this.state.selectedMethod = method;
            
            // Set data attribute on container for global CSS theming
            if (this.container) {
                this.container.setAttribute('data-active-method', method);
            }

            this.cards.forEach(card => {
                const cardMethod = card.getAttribute('data-method');
                const isActive = (cardMethod === method);

                if (isActive) {
                    card.classList.add('active');
                    card.setAttribute('aria-checked', 'true');
                    card.setAttribute('tabindex', '0');
                    if (updateFocus) {
                        // Focus the first interactive input in disclosed body if present
                        const firstInput = card.querySelector('.payment-method-body input, .payment-method-body textarea');
                        if (firstInput) {
                            setTimeout(() => firstInput.focus(), 150);
                        }
                    }
                } else {
                    card.classList.remove('active');
                    card.setAttribute('aria-checked', 'false');
                    card.setAttribute('tabindex', '-1');
                }
            });

            this.clearGlobalError();
            this.updateCtaButton();
        }

        updateCtaButton() {
            if (!this.ctaText) return;

            const method = this.state.selectedMethod;
            const curr = this.config.currency || 'USD';
            const formattedAmount = Number(this.config.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const formattedKes = Number(this.config.kesAmount).toLocaleString('en-US');

            switch (method) {
                case 'mpesa':
                    this.ctaText.textContent = `Pay KES ${formattedKes} via M-Pesa`;
                    break;
                case 'stripe':
                    this.ctaText.textContent = `Pay ${curr} ${formattedAmount}`;
                    break;
                case 'paypal':
                    this.ctaText.textContent = `Continue to PayPal`;
                    break;
                case 'cash':
                    this.ctaText.textContent = `Confirm Cash Order`;
                    break;
                default:
                    this.ctaText.textContent = `Pay ${curr} ${formattedAmount}`;
            }
        }

        setSubmitting(isSubmitting) {
            if (!this.ctaButton) return;
            this.ctaButton.disabled = isSubmitting;
            if (this.ctaSpinner) {
                if (isSubmitting) {
                    this.ctaSpinner.classList.remove('d-none');
                } else {
                    this.ctaSpinner.classList.add('d-none');
                }
            }
        }

        // =========================================================================
        // M-PESA VALIDATION & NORMALIZATION
        // =========================================================================

        formatKenyanPhoneInput(input) {
            let val = input.value.replace(/\D/g, '');
            // Strip starting 254 if user pasted full international
            if (val.startsWith('254')) {
                val = val.substring(3);
            }
            // Strip leading 0
            if (val.startsWith('0')) {
                val = val.substring(1);
            }
            // Format as: 7XX XXX XXX or 1XX XXX XXX
            let formatted = val;
            if (val.length > 3 && val.length <= 6) {
                formatted = `${val.substring(0, 3)} ${val.substring(3)}`;
            } else if (val.length > 6) {
                formatted = `${val.substring(0, 3)} ${val.substring(3, 6)} ${val.substring(6, 9)}`;
            }
            input.value = formatted;
        }

        validateKenyanPhone(phoneRaw) {
            if (!phoneRaw) return { valid: false, error: 'Phone number is required.' };
            const digits = phoneRaw.replace(/\D/g, '');

            let normalized = '';
            if (/^0([17]\d{8})$/.test(digits)) {
                normalized = '254' + digits.substring(1);
            } else if (/^([17]\d{8})$/.test(digits)) {
                normalized = '254' + digits;
            } else if (/^254([17]\d{8})$/.test(digits)) {
                normalized = digits;
            }

            if (!normalized || !/^(2547\d{8}|2541\d{8})$/.test(normalized)) {
                return {
                    valid: false,
                    error: 'Please enter a valid Safaricom mobile number (e.g., 0712 345 678 or 0110 123 456).'
                };
            }

            return { valid: true, normalized: normalized };
        }

        // =========================================================================
        // SUBMIT ORCHESTRATION
        // =========================================================================

        async handleSubmit() {
            this.clearGlobalError();
            const method = this.state.selectedMethod;

            switch (method) {
                case 'mpesa':
                    await this.handleMpesaSubmit();
                    break;
                case 'stripe':
                    await this.handleStripeSubmit();
                    break;
                case 'paypal':
                    await this.handlePayPalSubmit();
                    break;
                case 'cash':
                    await this.handleCashSubmit();
                    break;
            }
        }

        // -------------------------------------------------------------------------
        // 1. M-PESA STK PUSH & MODAL POLLING FLOW
        // -------------------------------------------------------------------------

        async handleMpesaSubmit() {
            const rawPhone = this.phoneInput ? this.phoneInput.value : this.formCache.phone;
            const validation = this.validateKenyanPhone(rawPhone);

            if (!validation.valid) {
                this.showError(this.phoneInput, this.phoneError, validation.error);
                if (this.phoneInput) this.phoneInput.focus();
                return;
            }

            this.setSubmitting(true);
            this.state.transactionState = 'submitting';

            try {
                const response = await fetch(this.config.endpoints.mpesaStkPush, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken
                    },
                    body: JSON.stringify({
                        course_id: this.config.courseId,
                        phone: validation.normalized
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Failed to send STK push prompt. Please try again.');
                }

                // Success initiating STK Push
                this.state.currentPaymentId = data.payment_id;
                this.state.currentCheckoutRequestId = data.checkout_request_id;
                this.state.transactionState = 'awaiting_pin';

                // Open modal countdown and start polling
                this.openStkModal(data.phone || validation.normalized);
            } catch (err) {
                this.showGlobalError(err.message);
                this.state.transactionState = 'error';
            } finally {
                this.setSubmitting(false);
            }
        }

        openStkModal(phoneFormatted) {
            if (!this.stkModal) return;

            if (this.stkPhoneDisplay) {
                this.stkPhoneDisplay.textContent = `+${phoneFormatted}`;
            }

            this.state.stkCountdown = 60;
            this.updateStkCountdownUi(60);
            this.setStkStatus('Awaiting M-Pesa PIN...', 'amber');
            this.stkModal.classList.add('active');
            document.body.style.overflow = 'hidden';

            if (this.stkLiveRegion) {
                this.stkLiveRegion.textContent = `STK Push prompt sent to +${phoneFormatted}. Please unlock your device and enter your M-Pesa PIN. 60 seconds remaining.`;
            }

            // Start 60s countdown ticker
            clearInterval(this.state.stkTimerId);
            this.state.stkTimerId = setInterval(() => {
                this.state.stkCountdown--;
                this.updateStkCountdownUi(this.state.stkCountdown);

                if (this.state.stkCountdown <= 0) {
                    this.handleStkTimeout();
                }
            }, 1000);

            // Start 3s polling loop
            clearInterval(this.state.pollTimerId);
            this.state.pollTimerId = setInterval(() => {
                this.pollMpesaStatus();
            }, 3000);
        }

        updateStkCountdownUi(secondsRemaining) {
            if (this.stkSecondsText) {
                const s = Math.max(0, secondsRemaining);
                this.stkSecondsText.textContent = `0:${s < 10 ? '0' : ''}${s}`;
            }

            if (this.stkCircle) {
                // Circle circumference: 2 * PI * 54 ≈ 339.292
                const totalDash = 339.292;
                const progressRatio = Math.max(0, secondsRemaining) / 60;
                const offset = totalDash * (1 - progressRatio);
                this.stkCircle.style.strokeDashoffset = offset;
            }
        }

        setStkStatus(message, colorTone = 'amber') {
            if (this.stkStatusText) {
                this.stkStatusText.textContent = message;
            }
        }

        async pollMpesaStatus() {
            if (!this.state.currentPaymentId) return;

            try {
                const response = await fetch(this.config.endpoints.mpesaQuery, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken
                    },
                    body: JSON.stringify({
                        payment_id: this.state.currentPaymentId,
                        checkout_request_id: this.state.currentCheckoutRequestId
                    })
                });

                const data = await response.json();

                if (data.status === 'paid' || data.paid) {
                    this.handlePaymentSuccess(data.redirect_url);
                } else if (data.status === 'cancelled') {
                    this.handlePaymentFailure('Payment cancelled by user.');
                } else if (data.status === 'failed') {
                    this.handlePaymentFailure(data.result_desc || 'Payment failed.');
                }
            } catch (err) {
                console.warn('STK Polling transient error:', err);
            }
        }

        handleStkTimeout() {
            clearInterval(this.state.stkTimerId);
            clearInterval(this.state.pollTimerId);
            this.setStkStatus('Prompt timed out. Please try again.', 'crimson');

            if (this.btnResendPrompt) {
                this.btnResendPrompt.disabled = false;
                this.btnResendPrompt.classList.add('btn-warning');
            }

            if (this.stkLiveRegion) {
                this.stkLiveRegion.textContent = 'M-Pesa STK push timed out. You may resend prompt or cancel.';
            }
        }

        async resendStkPrompt() {
            if (this.state.resendCooldown) return;
            this.state.resendCooldown = true;
            if (this.btnResendPrompt) {
                this.btnResendPrompt.disabled = true;
                this.btnResendPrompt.textContent = 'Resending...';
            }

            clearInterval(this.state.stkTimerId);
            clearInterval(this.state.pollTimerId);

            const rawPhone = this.phoneInput ? this.phoneInput.value : this.formCache.phone;
            const validation = this.validateKenyanPhone(rawPhone);

            try {
                const response = await fetch(this.config.endpoints.mpesaStkPush, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken
                    },
                    body: JSON.stringify({
                        course_id: this.config.courseId,
                        phone: validation.normalized
                    })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Failed to resend prompt.');
                }

                this.state.currentPaymentId = data.payment_id;
                this.state.currentCheckoutRequestId = data.checkout_request_id;
                this.openStkModal(data.phone || validation.normalized);

                if (this.btnResendPrompt) {
                    this.btnResendPrompt.textContent = 'Resend Prompt';
                }
            } catch (err) {
                alert(err.message);
                if (this.btnResendPrompt) {
                    this.btnResendPrompt.disabled = false;
                    this.btnResendPrompt.textContent = 'Resend Prompt';
                }
            } finally {
                setTimeout(() => {
                    this.state.resendCooldown = false;
                }, 5000);
            }
        }

        closeStkModal() {
            clearInterval(this.state.stkTimerId);
            clearInterval(this.state.pollTimerId);
            if (this.stkModal) {
                this.stkModal.classList.remove('active');
            }
            document.body.style.overflow = '';
            this.state.transactionState = 'idle';
        }

        handlePaymentSuccess(redirectUrl) {
            clearInterval(this.state.stkTimerId);
            clearInterval(this.state.pollTimerId);
            this.setStkStatus('Payment Confirmed! Redirecting...', 'emerald');

            if (this.stkLiveRegion) {
                this.stkLiveRegion.textContent = 'M-Pesa payment successful! Redirecting to your classroom.';
            }

            setTimeout(() => {
                window.location.href = redirectUrl || `/courses/${this.config.courseSlug}`;
            }, 1000);
        }

        handlePaymentFailure(reason) {
            clearInterval(this.state.stkTimerId);
            clearInterval(this.state.pollTimerId);
            this.closeStkModal();
            this.showGlobalError(`M-Pesa Notice: ${reason}`);
        }

        // -------------------------------------------------------------------------
        // 2. STRIPE PAYMENT ELEMENTS INTEGRATION
        // -------------------------------------------------------------------------

        initStripe() {
            const stripeMount = document.getElementById('stripe-card-element');
            if (!stripeMount) return;

            // Check if Stripe.js is loaded from https://js.stripe.com/v3/
            if (window.Stripe && this.config.stripePublishableKey && !this.config.stripePublishableKey.includes('mock') && !this.config.stripePublishableKey.includes('simulated')) {
                try {
                    this.stripeInstance = window.Stripe(this.config.stripePublishableKey);
                    const elements = this.stripeInstance.elements();
                    this.stripeCardElement = elements.create('card', {
                        style: {
                            base: {
                                color: '#f8fafc',
                                fontFamily: "'Outfit', sans-serif",
                                fontSmoothing: 'antialiased',
                                fontSize: '15px',
                                '::placeholder': { color: '#64748b' }
                            },
                            invalid: {
                                color: '#ef4444',
                                iconColor: '#ef4444'
                            }
                        }
                    });
                    this.stripeCardElement.mount('#stripe-card-element');
                    return;
                } catch (e) {
                    console.warn('Real Stripe Elements mount fallback to simulator card UI:', e);
                }
            }

            // High-fidelity fallback / Simulator Card Interface
            this.mountSimulatorCardElement(stripeMount);
        }

        mountSimulatorCardElement(container) {
            container.innerHTML = `
                <div class="row g-2">
                    <div class="col-12">
                        <label class="payment-label small mb-1">Card Number</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-dark border-secondary text-gold"><i class="bi bi-credit-card-2-front"></i></span>
                            <input type="text" id="sim-card-number" class="form-control bg-dark border-secondary text-white" placeholder="4242 4242 4242 4242" maxlength="19" value="4242 4242 4242 4242">
                        </div>
                    </div>
                    <div class="col-7">
                        <label class="payment-label small mb-1">Expiration</label>
                        <input type="text" id="sim-card-exp" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="MM / YY" maxlength="7" value="12 / 28">
                    </div>
                    <div class="col-5">
                        <label class="payment-label small mb-1">CVC / CWW</label>
                        <input type="text" id="sim-card-cvc" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="CVC" maxlength="4" value="123">
                    </div>
                </div>
            `;

            const numInput = document.getElementById('sim-card-number');
            if (numInput) {
                numInput.addEventListener('input', (e) => {
                    let v = e.target.value.replace(/\D/g, '').substring(0, 16);
                    let parts = [];
                    for (let i = 0; i < v.length; i += 4) {
                        parts.push(v.substring(i, i + 4));
                    }
                    e.target.value = parts.join(' ');
                });
            }
        }

        async handleStripeSubmit() {
            this.setSubmitting(true);
            this.state.transactionState = 'submitting';

            try {
                // Step 1: Create Payment Intent on backend
                const response = await fetch(this.config.endpoints.stripeCreateIntent, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken
                    },
                    body: JSON.stringify({
                        course_id: this.config.courseId,
                        save_card: this.formCache.saveCard
                    })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Failed to initialize card payment.');
                }

                // If real Stripe is available and not in simulated mode
                if (this.stripeInstance && this.stripeCardElement && !data.simulated) {
                    const result = await this.stripeInstance.confirmCardPayment(data.client_secret, {
                        payment_method: {
                            card: this.stripeCardElement,
                            billing_details: {
                                name: this.config.userName || 'Student',
                                email: this.config.userEmail || ''
                            }
                        }
                    });

                    if (result.error) {
                        throw new Error(result.error.message || 'Card payment declined.');
                    }

                    // Confirm on backend
                    const confirmRes = await fetch(this.config.endpoints.stripeConfirm, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.config.csrfToken
                        },
                        body: JSON.stringify({
                            payment_id: data.payment_id,
                            payment_intent_id: result.paymentIntent.id
                        })
                    });

                    const confirmData = await confirmRes.json();
                    window.location.href = confirmData.redirect_url || `/courses/${this.config.courseSlug}`;
                    return;
                }

                // If simulated / test mode: call stripe confirmation directly
                const confirmRes = await fetch(this.config.endpoints.stripeConfirm, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken
                    },
                    body: JSON.stringify({
                        payment_id: data.payment_id,
                        payment_intent_id: data.client_secret ? data.client_secret.split('_secret_')[0] : 'pi_simulated'
                    })
                });

                const confirmData = await confirmRes.json();
                window.location.href = confirmData.redirect_url || `/courses/${this.config.courseSlug}`;
            } catch (err) {
                this.showGlobalError(err.message);
                this.state.transactionState = 'error';
            } finally {
                this.setSubmitting(false);
            }
        }

        // -------------------------------------------------------------------------
        // 3. PAYPAL REDIRECT ORDER FLOW
        // -------------------------------------------------------------------------

        async handlePayPalSubmit() {
            this.setSubmitting(true);
            this.state.transactionState = 'submitting';

            try {
                const response = await fetch(this.config.endpoints.paypalCreateOrder, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken
                    },
                    body: JSON.stringify({
                        course_id: this.config.courseId
                    })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Failed to initialize PayPal order.');
                }

                // Redirect to PayPal approve URL or simulate page
                if (data.approve_url) {
                    window.location.href = data.approve_url;
                } else {
                    throw new Error('No PayPal checkout approval URL returned.');
                }
            } catch (err) {
                this.showGlobalError(err.message);
                this.state.transactionState = 'error';
            } finally {
                this.setSubmitting(false);
            }
        }

        // -------------------------------------------------------------------------
        // 4. CASH ON DELIVERY / PICKUP ORDER FLOW
        // -------------------------------------------------------------------------

        async handleCashSubmit() {
            this.setSubmitting(true);
            this.state.transactionState = 'submitting';

            try {
                const response = await fetch(this.config.endpoints.cashCreate, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken
                    },
                    body: JSON.stringify({
                        course_id: this.config.courseId,
                        notes: this.formCache.cashNotes
                    })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Failed to confirm cash order.');
                }

                // Direct order created with status pending_payment -> redirect to instructions page
                window.location.href = data.redirect_url;
            } catch (err) {
                this.showGlobalError(err.message);
                this.state.transactionState = 'error';
            } finally {
                this.setSubmitting(false);
            }
        }

        // =========================================================================
        // ERROR HANDLING HELPERS
        // =========================================================================

        showError(inputElement, errorElement, message) {
            if (inputElement) inputElement.classList.add('is-invalid');
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.classList.add('visible');
            }
        }

        clearError(inputElement, errorElement) {
            if (inputElement) inputElement.classList.remove('is-invalid');
            if (errorElement) {
                errorElement.textContent = '';
                errorElement.classList.remove('visible');
            }
        }

        showGlobalError(message) {
            if (this.globalErrorContainer) {
                this.globalErrorContainer.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show bg-danger-subtle border-danger text-danger-emphasis mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                this.globalErrorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                alert(message);
            }
        }

        clearGlobalError() {
            if (this.globalErrorContainer) {
                this.globalErrorContainer.innerHTML = '';
            }
        }
    }

    // Export to global scope
    window.PaymentSelector = PaymentSelector;
})();
