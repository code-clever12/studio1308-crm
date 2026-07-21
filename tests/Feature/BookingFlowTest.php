<?php

use App\Models\Appointment;
use App\Models\Category;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\User;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| One continuous customer journey — browse, book, pay, get confirmed via
| webhook, and see it reflected back — rather than the disconnected pieces
| covered elsewhere (Customer/BookingControllerTest, PaymentControllerTest,
| etc.). Catches regressions in the *linkage* between steps that per-step
| tests, each with their own isolated setup, would miss.
|--------------------------------------------------------------------------
*/

function bookingFlowNextMonday(): string
{
    return Carbon::now()->next(Carbon::MONDAY)->toDateString();
}

it('takes a customer from browsing through booking, payment, and confirmation', function () {
    Salon::factory()->create([
        'opens_at' => '09:00:00',
        'closes_at' => '18:00:00',
        'deposit_percentage' => 25,
        'sales_tax_rate' => 8,
    ]);

    $category = Category::factory()->create(['name' => 'Hair']);
    $service = Service::factory()->create([
        'category_id' => $category->id,
        'name' => 'Signature Cut',
        'price' => 100,
        'duration_minutes' => 60,
        'buffer_time_minutes' => 15,
        'deposit_amount' => null,
        'is_active' => true,
    ]);

    $staff = Staff::factory()->create(['status' => 'active']);
    $staff->services()->attach($service->id, ['is_available' => true]);

    $date = bookingFlowNextMonday();

    StaffSchedule::factory()->create([
        'staff_id' => $staff->id,
        'day_of_week' => Carbon::parse($date)->dayOfWeek,
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'is_working_day' => true,
    ]);

    $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
    $this->actingAs($customer);

    // 1. Browse: the service is discoverable.
    $browse = $this->get(route('customer.browse'));
    $browse->assertOk();
    expect($browse->viewData('services')->pluck('id'))->toContain($service->id);

    // 2. Slots: at least one opening exists on the scheduled day.
    $slots = $this->getJson(route('customer.booking.slots', ['service_id' => $service->id, 'staff_id' => $staff->id, 'date' => $date]));
    $slots->assertOk();
    $availableTime = $slots->json("slots.{$staff->id}.0");
    expect($availableTime)->not->toBeNull();

    // 3. Book: creates a pending appointment and returns its id for the payment step.
    $book = $this->postJson(route('customer.booking.store'), [
        'service_id' => $service->id,
        'staff_id' => $staff->id,
        'appointment_date' => $date,
        'start_time' => $availableTime,
    ]);
    $book->assertCreated();
    $appointmentId = $book->json('appointment_id');

    $appointment = Appointment::findOrFail($appointmentId);
    expect($appointment->status)->toBe('pending')
        ->and($appointment->customer_id)->toBe($customer->id);

    // 4. Payment page: Stripe creates a customer + PaymentIntent for the deposit.
    $http = mockStripeHttp();
    $http->shouldReceive('request')->once()
        ->withArgs(fn ($method, $url) => $method === 'post' && str_contains($url, '/v1/customers'))
        ->andReturn(stripeHttpResponse(['id' => 'cus_flow_test', 'object' => 'customer']));
    $http->shouldReceive('request')->once()
        ->withArgs(fn ($method, $url, $headers, $params) => $method === 'post'
            && str_contains($url, '/v1/payment_intents')
            && $params['amount'] === 2500)
        ->andReturn(stripeHttpResponse([
            'id' => 'pi_flow_test',
            'object' => 'payment_intent',
            'client_secret' => 'pi_flow_test_secret',
            'status' => 'requires_payment_method',
        ]));

    $payment = $this->get(route('customer.booking.payment', $appointment));
    $payment->assertOk()->assertSee('pi_flow_test_secret', false);

    // 5. Webhook: Stripe confirms the PaymentIntent succeeded.
    $payload = json_encode([
        'id' => 'evt_flow_test',
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => [
            'id' => 'pi_flow_test',
            'latest_charge' => 'ch_flow_test',
            'payment_method' => 'pm_flow_test',
        ]],
    ]);
    $signature = stripeWebhookSignature($payload, config('services.stripe.webhook_secret'));

    $webhook = $this->call('POST', route('webhooks.stripe'), [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $payload);
    $webhook->assertOk();

    // 6. Confirmed: the appointment now shows as confirmed with the deposit recorded.
    $appointment->refresh();
    expect($appointment->status)->toBe('confirmed')
        ->and((float) $appointment->deposit_paid)->toBe(25.0)
        ->and($customer->fresh()->stripe_payment_method_id)->toBe('pm_flow_test');

    // 7. Confirmation page and "my appointments" both reflect it.
    $confirmation = $this->get(route('customer.booking.confirmation', $appointment));
    $confirmation->assertOk()->assertSee("You're all set!");

    $myAppointments = $this->get(route('customer.appointments.index'));
    $myAppointments->assertOk();
    expect($myAppointments->viewData('upcoming')->pluck('id'))->toContain($appointment->id);
});
