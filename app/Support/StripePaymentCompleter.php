<?php

namespace App\Support;

use App\Mail\ReservationTicketsMail;
use App\Models\Payment;
use App\Models\Reservation;
use App\PaymentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\StripeClient;

class StripePaymentCompleter
{
    use CompletesPaidReservations;

    public function __construct(
        private StripeClient $client,
    ) {}

    public function syncReservationFromSessionId(Reservation $reservation, string $sessionId): void
    {
        $sessionId = trim($sessionId);

        if ($sessionId === '' || (string) config('stripe.secret', '') === '') {
            return;
        }

        if (! $reservation->payments()->where('stripe_checkout_session_id', $sessionId)->exists()) {
            return;
        }

        try {
            $session = $this->client->checkout->sessions->retrieve($sessionId);
        } catch (\Throwable $exception) {
            Log::warning('Stripe session retrieval failed', [
                'session_id' => $sessionId,
                'reservation_id' => $reservation->id,
                'message' => $exception->getMessage(),
            ]);

            return;
        }

        if (($session->payment_status ?? null) === 'paid') {
            $this->processPaidCheckoutSession($session);
        }
    }

    public function processPaidCheckoutSession(object $session): void
    {
        $sessionId = $session->id ?? null;

        if (! is_string($sessionId) || $sessionId === '') {
            return;
        }

        $shouldSendMail = false;
        $reservationForMail = null;

        DB::transaction(function () use ($session, $sessionId, &$shouldSendMail, &$reservationForMail): void {
            $payment = Payment::query()
                ->where('stripe_checkout_session_id', $sessionId)
                ->lockForUpdate()
                ->first();

            if ($payment === null || $payment->status === PaymentStatus::Succeeded) {
                return;
            }

            $reservationId = $this->metadataInt($session, 'reservation_id') ?: (int) ($session->client_reference_id ?? 0);
            $paymentId = $this->metadataInt($session, 'payment_id');

            if ($reservationId !== $payment->reservation_id
                || ($paymentId > 0 && $paymentId !== $payment->id)
                || (int) ($session->amount_total ?? -1) !== $payment->amount
                || strtolower((string) ($session->currency ?? '')) !== strtolower($payment->currency)
            ) {
                $payment->update(['status' => PaymentStatus::Failed]);

                return;
            }

            $payment->update([
                'stripe_payment_intent_id' => $this->paymentIntentId($session),
                'status' => PaymentStatus::Succeeded,
                'paid_at' => now(),
            ]);

            $payment->load('reservation.user');
            $result = $this->finalizePaidReservation($payment->reservation);

            if ($result === 'confirmed') {
                $shouldSendMail = true;
                $reservationForMail = $payment->reservation->fresh([
                    'user',
                    'tickets',
                    'cinemaSession.film',
                    'cinemaSession.room',
                ]);
            } elseif ($result === 'unavailable' || $result === 'invalid_state') {
                $payment->update(['status' => PaymentStatus::Failed]);
            }
        });

        if ($shouldSendMail && $reservationForMail !== null) {
            Mail::to($reservationForMail->user)->send(new ReservationTicketsMail($reservationForMail));
        }
    }

    public function markPendingSession(string $sessionId, PaymentStatus $status): void
    {
        if ($sessionId === '' || ! in_array($status, [PaymentStatus::Cancelled, PaymentStatus::Failed], true)) {
            return;
        }

        Payment::query()
            ->where('stripe_checkout_session_id', $sessionId)
            ->where('status', PaymentStatus::Pending)
            ->update(['status' => $status]);
    }

    private function metadataInt(object $session, string $key): int
    {
        $metadata = $session->metadata ?? null;

        if ($metadata === null) {
            return 0;
        }

        $value = is_object($metadata) ? ($metadata->{$key} ?? null) : ($metadata[$key] ?? null);

        return (int) ($value ?? 0);
    }

    private function paymentIntentId(object $session): ?string
    {
        $paymentIntent = $session->payment_intent ?? null;

        return match (true) {
            is_string($paymentIntent) => $paymentIntent,
            is_object($paymentIntent) => $paymentIntent->id ?? null,
            default => null,
        };
    }
}
