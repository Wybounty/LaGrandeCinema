<?php

namespace App\Support;

use App\Models\Payment;
use App\Models\Reservation;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class StripeCheckout
{
    public function __construct(
        private StripeClient $client,
    ) {}

    public function createSession(Reservation $reservation, Payment $payment): Session
    {
        $reservation->loadMissing('cinemaSession.film');

        $filmTitle = $reservation->cinemaSession->film->title;
        $places = $reservation->number_of_places;

        return $this->client->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $payment->currency,
                        'unit_amount' => $payment->amount,
                        'product_data' => [
                            'name' => "Réservation — {$filmTitle}",
                            'description' => "{$places} place(s)",
                        ],
                    ],
                ],
            ],
            'metadata' => [
                'reservation_id' => (string) $reservation->id,
                'payment_id' => (string) $payment->id,
            ],
            'client_reference_id' => (string) $reservation->id,
            'success_url' => route('reservations.payment.success', $reservation)
                .'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('reservations.payment.cancel', $reservation),
        ]);
    }
}
