<?php

namespace Tests\Support;

use App\Models\CinemaSession;
use App\Models\CinemaSetting;
use App\Models\Film;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Support\StripeCheckout;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Stripe\Checkout\Session;

class StripePaymentHelpers
{
    public static function configureTestEnvironment(): void
    {
        config([
            'stripe.key' => 'pk_test_123',
            'stripe.secret' => 'sk_test_123',
            'stripe.webhook_secret' => 'test_webhook_secret',
            'stripe.currency' => 'cad',
        ]);
    }

    public static function mockCheckoutSession(string $sessionId = 'cs_test_abc'): void
    {
        test()->mock(StripeCheckout::class, function ($mock) use ($sessionId): void {
            $mock->shouldReceive('createSession')->andReturn(
                Session::constructFrom([
                    'id' => $sessionId,
                    'url' => 'https://checkout.stripe.com/c/pay/'.$sessionId,
                ]),
            );
        });
    }

    public static function makeBookableSession(int $capacity = 50, ?Carbon $startsAt = null): CinemaSession
    {
        $film = Film::factory()->create(['duration' => 100]);
        $room = Room::factory()->create(['capacity' => $capacity]);
        $startsAt ??= Carbon::now()->addDays(5)->setTime(19, 0);

        return CinemaSession::factory()->create([
            'film_id' => $film->id,
            'room_id' => $room->id,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes(100),
        ]);
    }

    public static function createPendingReservation(User $user, CinemaSession $session, int $places = 2): Reservation
    {
        CinemaSetting::query()->update(['ticket_price' => 10]);

        test()->actingAs($user)->post(route('reservations.store'), [
            'cinema_session_id' => $session->id,
            'number_of_places' => $places,
        ]);

        return Reservation::query()->where('user_id', $user->id)->latest('id')->firstOrFail();
    }

    public static function checkoutAndConfirmViaWebhook(User $user, Reservation $reservation, ?string $sessionId = null): void
    {
        $sessionId ??= 'cs_test_'.Str::random(8);

        self::mockCheckoutSession($sessionId);

        test()->actingAs($user)->post(route('reservations.checkout', $reservation));

        $payment = $reservation->payments()->latest('id')->firstOrFail();

        self::postWebhook(
            'checkout.session.completed',
            self::paidSession($payment, $reservation),
        )->assertOk();
    }

    public static function confirmReservationForUser(User $user, CinemaSession $session, int $places = 1): Reservation
    {
        $user = $user->fresh() ?? $user;
        $reservation = self::createPendingReservation($user, $session, $places);
        self::checkoutAndConfirmViaWebhook($user, $reservation);

        return $reservation->fresh(['tickets']);
    }

    public static function signPayload(string $payload, string $secret): string
    {
        $timestamp = time();
        $signedPayload = $timestamp.'.'.$payload;
        $signature = hash_hmac('sha256', $signedPayload, $secret);

        return "t={$timestamp},v1={$signature}";
    }

    /**
     * @return array<string, mixed>
     */
    public static function paidSession(Payment $payment, Reservation $reservation): array
    {
        return [
            'id' => $payment->stripe_checkout_session_id,
            'payment_status' => 'paid',
            'amount_total' => $payment->amount,
            'currency' => $payment->currency,
            'metadata' => [
                'reservation_id' => (string) $reservation->id,
                'payment_id' => (string) $payment->id,
            ],
            'client_reference_id' => (string) $reservation->id,
            'payment_intent' => 'pi_test_'.Str::random(8),
        ];
    }

    /**
     * @param  array<string, mixed>  $session
     */
    public static function postWebhook(string $type, array $session): TestResponse
    {
        $payload = json_encode([
            'id' => 'evt_'.Str::random(12),
            'type' => $type,
            'data' => [
                'object' => $session,
            ],
        ], JSON_THROW_ON_ERROR);

        $signature = self::signPayload(
            $payload,
            (string) config('stripe.webhook_secret'),
        );

        return test()->call(
            'POST',
            route('stripe.webhook'),
            [],
            [],
            [],
            [
                'HTTP_STRIPE_SIGNATURE' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload,
        );
    }
}
