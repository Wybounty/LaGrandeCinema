<?php

use App\Mail\ReservationTicketsMail;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use App\PaymentStatus;
use App\ReservationStatus;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session;
use Stripe\StripeClient;
use Tests\Support\StripePaymentHelpers;

beforeEach(fn () => StripePaymentHelpers::configureTestEnvironment());

test('checkout creates a pending payment and redirects to stripe', function () {
    StripePaymentHelpers::mockCheckoutSession('cs_test_redirect');

    $user = User::factory()->create();
    $session = StripePaymentHelpers::makeBookableSession();
    $reservation = StripePaymentHelpers::createPendingReservation($user, $session, 2);

    $response = test()->actingAs($user)->post(route('reservations.checkout', $reservation));

    $response->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_redirect');

    $payment = Payment::query()->first();

    expect($payment)->not->toBeNull()
        ->and($payment->reservation_id)->toBe($reservation->id)
        ->and($payment->amount)->toBe(2000)
        ->and($payment->currency)->toBe('cad')
        ->and($payment->status)->toBe(PaymentStatus::Pending)
        ->and($payment->stripe_checkout_session_id)->toBe('cs_test_redirect');
});

test('inertia checkout responds with external location header', function () {
    StripePaymentHelpers::mockCheckoutSession('cs_test_inertia');

    $user = User::factory()->create();
    $reservation = StripePaymentHelpers::createPendingReservation($user, StripePaymentHelpers::makeBookableSession(), 1);

    test()->actingAs($user)
        ->withHeader('X-Inertia', 'true')
        ->post(route('reservations.checkout', $reservation))
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', 'https://checkout.stripe.com/c/pay/cs_test_inertia');
});

test('checkout amount always follows reservation total', function () {
    StripePaymentHelpers::mockCheckoutSession();

    $user = User::factory()->create();
    $session = StripePaymentHelpers::makeBookableSession();
    $reservation = StripePaymentHelpers::createPendingReservation($user, $session, 3);

    expect($reservation->amountInCents())->toBe(3000);

    test()->actingAs($user)->post(route('reservations.checkout', $reservation));

    expect(Payment::query()->first()?->amount)->toBe(3000);
});

test('checkout rejects tampered reservation totals', function () {
    $user = User::factory()->create();
    $session = StripePaymentHelpers::makeBookableSession();
    $reservation = StripePaymentHelpers::createPendingReservation($user, $session, 2);

    $reservation->update(['total_amount' => 1]);

    test()->actingAs($user)
        ->from(route('reservations.show', $reservation))
        ->post(route('reservations.checkout', $reservation))
        ->assertSessionHasErrors('payment');
});

test('another user cannot start checkout for a reservation', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $reservation = Reservation::factory()->pending()->create(['user_id' => $owner->id]);

    test()->actingAs($intruder)
        ->post(route('reservations.checkout', $reservation))
        ->assertForbidden();
});

test('paid webhook confirms reservation and sends one email', function () {
    Mail::fake();
    StripePaymentHelpers::mockCheckoutSession('cs_test_webhook_paid');

    $user = User::factory()->create();
    $session = StripePaymentHelpers::makeBookableSession();
    $reservation = StripePaymentHelpers::createPendingReservation($user, $session, 2);

    test()->actingAs($user)->post(route('reservations.checkout', $reservation));

    $payment = Payment::query()->firstOrFail();

    StripePaymentHelpers::postWebhook(
        'checkout.session.completed',
        StripePaymentHelpers::paidSession($payment, $reservation),
    )
        ->assertOk();

    expect($reservation->fresh()->status)->toBe(ReservationStatus::Confirmed)
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Succeeded)
        ->and($reservation->fresh()->tickets)->toHaveCount(2);

    Mail::assertSent(ReservationTicketsMail::class, 1);
});

test('duplicate paid webhooks are idempotent', function () {
    Mail::fake();
    StripePaymentHelpers::mockCheckoutSession('cs_test_dup');

    $user = User::factory()->create();
    $session = StripePaymentHelpers::makeBookableSession();
    $reservation = StripePaymentHelpers::createPendingReservation($user, $session, 2);

    test()->actingAs($user)->post(route('reservations.checkout', $reservation));
    $payment = Payment::query()->firstOrFail();
    $payload = StripePaymentHelpers::paidSession($payment, $reservation);

    StripePaymentHelpers::postWebhook('checkout.session.completed', $payload)->assertOk();
    StripePaymentHelpers::postWebhook('checkout.session.completed', $payload)->assertOk();

    expect($reservation->fresh()->tickets)->toHaveCount(2);
    Mail::assertSent(ReservationTicketsMail::class, 1);
});

test('invalid webhook signature is rejected', function () {
    $payload = json_encode(['type' => 'checkout.session.completed']);

    test()->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        ['HTTP_STRIPE_SIGNATURE' => 'invalid', 'CONTENT_TYPE' => 'application/json'],
        (string) $payload,
    )->assertStatus(400);
});

test('expired checkout session marks payment cancelled', function () {
    StripePaymentHelpers::mockCheckoutSession('cs_test_expired');

    $user = User::factory()->create();
    $reservation = StripePaymentHelpers::createPendingReservation($user, StripePaymentHelpers::makeBookableSession(), 1);

    test()->actingAs($user)->post(route('reservations.checkout', $reservation));
    $payment = Payment::query()->firstOrFail();

    StripePaymentHelpers::postWebhook('checkout.session.expired', [
        'id' => $payment->stripe_checkout_session_id,
    ])->assertOk();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Cancelled)
        ->and($reservation->fresh()->status)->toBe(ReservationStatus::Pending);
});

test('mismatched payment amount fails webhook processing', function () {
    Mail::fake();
    StripePaymentHelpers::mockCheckoutSession('cs_test_amount');

    $user = User::factory()->create();
    $reservation = StripePaymentHelpers::createPendingReservation($user, StripePaymentHelpers::makeBookableSession(), 2);

    test()->actingAs($user)->post(route('reservations.checkout', $reservation));
    $payment = Payment::query()->firstOrFail();

    $badSession = StripePaymentHelpers::paidSession($payment, $reservation);
    $badSession['amount_total'] = 1;

    StripePaymentHelpers::postWebhook('checkout.session.completed', $badSession)->assertOk();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Failed)
        ->and($reservation->fresh()->status)->toBe(ReservationStatus::Pending);

    Mail::assertNothingSent();
});

test('success return page does not confirm reservation without webhook', function () {
    $user = User::factory()->create();
    $reservation = Reservation::factory()->pending()->create(['user_id' => $user->id]);

    test()->actingAs($user)
        ->get(route('reservations.payment.success', $reservation))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('reservations/PaymentSuccess'));

    expect($reservation->fresh()->status)->toBe(ReservationStatus::Pending);
});

test('payment success syncs a paid stripe session when webhook is delayed', function () {
    Mail::fake();
    StripePaymentHelpers::mockCheckoutSession('cs_test_sync_late');

    $user = User::factory()->create();
    $reservation = StripePaymentHelpers::createPendingReservation($user, StripePaymentHelpers::makeBookableSession(), 1);

    test()->actingAs($user)->post(route('reservations.checkout', $reservation));
    $payment = Payment::query()->firstOrFail();

    $sessionObject = Session::constructFrom(
        StripePaymentHelpers::paidSession($payment, $reservation),
    );

    $sessions = new class($sessionObject)
    {
        public function __construct(private Session $session) {}

        public function retrieve(string $id): Session
        {
            return $this->session;
        }
    };

    test()->mock(StripeClient::class, function ($mock) use ($sessions): void {
        $mock->checkout = (object) ['sessions' => $sessions];
    });

    test()->actingAs($user)
        ->get(route('reservations.payment.success', [
            'reservation' => $reservation,
            'session_id' => $payment->stripe_checkout_session_id,
        ]))
        ->assertRedirect();

    expect($reservation->fresh()->status)->toBe(ReservationStatus::Confirmed);
    Mail::assertSent(ReservationTicketsMail::class, 1);
});

test('confirmation shows toast after stripe payment', function () {
    $user = User::factory()->create();
    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'status' => ReservationStatus::Confirmed,
    ]);

    test()->actingAs($user)
        ->get(route('reservations.confirmation', ['reservation' => $reservation, 'paid' => 1]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('reservations/Confirmation'));
});
