<?php

namespace App\Support;

use App\Models\CinemaSession;
use App\Models\Reservation;
use App\Models\Ticket;
use App\ReservationStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait CompletesPaidReservations
{
    /**
     * Confirme une réservation en attente après paiement validé (idempotent côté billets).
     *
     * @return 'confirmed'|'already_confirmed'|'unavailable'|'invalid_state'
     */
    protected function finalizePaidReservation(Reservation $reservation): string
    {
        return DB::transaction(function () use ($reservation): string {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->find($reservation->id);

            if ($reservation === null) {
                return 'invalid_state';
            }

            if ($reservation->status === ReservationStatus::Confirmed) {
                return 'already_confirmed';
            }

            if ($reservation->status !== ReservationStatus::Pending) {
                return 'invalid_state';
            }

            $session = $this->lockCinemaSession($reservation->cinema_session_id);

            if ($session->starts_at->isPast()) {
                return 'invalid_state';
            }

            $remaining = $session->remainingSeats();

            if ($reservation->number_of_places > $remaining) {
                return 'unavailable';
            }

            $reservation->update(['status' => ReservationStatus::Confirmed]);
            $this->issueTicketsForReservation($reservation);

            return 'confirmed';
        });
    }

    protected function lockCinemaSession(int $cinemaSessionId): CinemaSession
    {
        $session = CinemaSession::query()
            ->lockForUpdate()
            ->with('room')
            ->find($cinemaSessionId);

        if ($session === null) {
            throw ValidationException::withMessages([
                'payment' => 'Cette séance n\'existe plus.',
            ]);
        }

        return $session;
    }

    protected function issueTicketsForReservation(Reservation $reservation): void
    {
        if ($reservation->tickets()->exists()) {
            return;
        }

        $generatedAt = now();

        for ($place = 1; $place <= $reservation->number_of_places; $place++) {
            Ticket::query()->create([
                'reservation_id' => $reservation->id,
                'place_number' => $place,
                'qr_code' => (string) Str::uuid(),
                'status' => 'valid',
                'generated_at' => $generatedAt,
            ]);
        }
    }
}
