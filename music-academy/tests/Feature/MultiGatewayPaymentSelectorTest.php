<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiGatewayPaymentSelectorTest extends TestCase
{
    protected User $student;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = User::where('role', 'student')->first() ?? User::factory()->create(['role' => 'student', 'phone' => '0712345678']);
        $this->course = Course::where('status', 'published')->where('fee', '>', 0)->first();

        // Ensure student has a pending enrollment for this test
        Enrollment::where('user_id', $this->student->id)
            ->where('course_id', $this->course->id)
            ->delete();
    }

    public function test_checkout_page_renders_payment_selector_with_dynamic_localization_for_kenya(): void
    {
        $response = $this->actingAs($this->student)
            ->withHeaders(['CF-IPCountry' => 'KE'])
            ->get(route('checkout.show', $this->course));

        $response->assertStatus(200);
        $response->assertSee('Payment Method Selector');
        $response->assertSee('M-Pesa Express');
        $response->assertSee('Credit or Debit Card');
        $response->assertSee('PayPal');
        $response->assertSee('Cash on Delivery / Pickup');
        $response->assertSee('defaultMethod: \'mpesa\'', false);
    }

    public function test_checkout_page_defaults_to_stripe_for_non_east_african_regions(): void
    {
        $response = $this->actingAs($this->student)
            ->get(route('checkout.show', ['course' => $this->course, 'region' => 'US']));

        $response->assertStatus(200);
        $response->assertSee('defaultMethod: \'stripe\'', false);
    }

    public function test_mpesa_stk_push_validates_kenyan_phone_format(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson(route('api.payments.mpesa.stk-push'), [
                'course_id' => $this->course->id,
                'phone' => '12345', // invalid format
            ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_mpesa_stk_push_initiates_successfully_with_valid_safaricom_number(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson(route('api.payments.mpesa.stk-push'), [
                'course_id' => $this->course->id,
                'phone' => '0712 345 678',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'currency' => 'KES',
            'phone' => '254712345678',
        ]);
        $response->assertJsonStructure(['payment_id', 'checkout_request_id', 'customer_message']);

        $paymentId = $response->json('payment_id');
        $this->assertDatabaseHas('payments', [
            'id' => $paymentId,
            'method' => 'mpesa',
            'status' => 'pending',
        ]);
    }

    public function test_mpesa_query_endpoint_handles_polling_and_simulation(): void
    {
        // Initiate STK push
        $initResponse = $this->actingAs($this->student)
            ->postJson(route('api.payments.mpesa.stk-push'), [
                'course_id' => $this->course->id,
                'phone' => '0722000000',
            ]);

        $paymentId = $initResponse->json('payment_id');

        // Query status with simulated auto approval
        $queryResponse = $this->actingAs($this->student)
            ->postJson(route('api.payments.mpesa.query'), [
                'payment_id' => $paymentId,
                'sim_action' => 'success',
            ]);

        $queryResponse->assertStatus(200);
        $queryResponse->assertJson([
            'status' => 'paid',
            'paid' => true,
            'result_code' => 0,
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $paymentId,
            'status' => 'paid',
        ]);
    }

    public function test_stripe_create_intent_and_confirm_lifecycle(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson(route('api.payments.stripe.create-intent'), [
                'course_id' => $this->course->id,
                'save_card' => true,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['payment_id', 'client_secret', 'publishable_key']);

        $paymentId = $response->json('payment_id');
        $intentSecret = $response->json('client_secret');

        // Confirm
        $confirmResponse = $this->actingAs($this->student)
            ->postJson(route('api.payments.stripe.confirm'), [
                'payment_id' => $paymentId,
                'payment_intent_id' => explode('_secret_', $intentSecret)[0],
            ]);

        $confirmResponse->assertStatus(200);
        $confirmResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('payments', [
            'id' => $paymentId,
            'method' => 'stripe',
            'status' => 'paid',
        ]);
    }

    public function test_paypal_create_order_returns_approval_url(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson(route('api.payments.paypal.create-order'), [
                'course_id' => $this->course->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['payment_id', 'approve_url']);
    }

    public function test_cash_order_creates_pending_payment_record(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson(route('api.payments.cash.create'), [
                'course_id' => $this->course->id,
                'notes' => 'Pickup at admissions desk on Tuesday.',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'pending_payment',
        ]);
        $response->assertJsonStructure(['payment_id', 'reference', 'redirect_url']);

        $paymentId = $response->json('payment_id');
        $this->assertDatabaseHas('payments', [
            'id' => $paymentId,
            'method' => 'cash',
            'status' => 'pending_payment',
        ]);
    }
}
