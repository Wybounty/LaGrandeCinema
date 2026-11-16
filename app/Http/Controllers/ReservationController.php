<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmReservationPaymentRequest;
use App\Http\Requests\StoreReservationRequest;
use App\Models\CinemaSession;
use App\Models\Reservation;
use App\Models\User;
use App\ReservationStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    public function create(CinemaSession $cinemaSession): Response
    {
        abort_if($cinemaSession->starts_at->isPast(), 404);

        $cinemaSession->load(['film:id,title', 'room:id,name,capacity']);

        return Inertia::render('reservations/Create', [
            'session' => [
                'id' => $cinemaSession->id,
                'starts_at' => $cinemaSession->starts_at->toIso8601String(),
                'ends_at' => $cinemaSession->ends_at->toIso8601String(),
                'room_name' => $cinemaSession->room->name,
                'film' => [
                    'id' => $cinemaSession->film->id,
                    'title' => $cinemaSession->film->title,
                ],
            ],
            'remainingSeats' => $cinemaSession->remainingSeats(),
            'unitPrice' => (float) config('cinema.ticket_price'),
        ]);
    }

    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $reservation = DB::transaction(function () use ($validated, $request): Reservation {
            return $this->createPendingReservation(
                $request->user(),
                (int) $validated['cinema_session_id'],
                (int) $validated['number_of_places'],
            );
        });

        return redirect()->route('reservations.show', $reservation);
    }

    public function show(Reservation $reservation): Response
    {
        $this->authorize('view', $reservation);

        return Inertia::render('reservations/Show', [
            'reservation' => $this->reservationPayload($reservation),
            'unitPrice' => (float) config('cinema.ticket_price'),
        ]);
    }

    public function pay(
        ConfirmReservationPaymentRequest $request,
        Reservation $reservation,
    ): RedirectResponse {
        $this->authorize('pay', $reservation);

        if (! $request->boolean('simulate_success')) {
            return back()->withErrors([
                'payment' => 'Le paiement simulé a échoué. Aucune réservation confirmée.',
            ]);
        }

        if ($reservation->status !== ReservationStatus::Pending) {
            return redirect()->route('reservations.confirmation', $reservation);
        }

        try {
            DB::transaction(function () use ($reservation): void {
                $this->confirmReservationAfterSimulatedPayment($reservation);
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'payment' => 'Une erreur est survenue pendant la réservation. Veuillez réessayer.',
            ]);
        }

        return redirect()->route('reservations.confirmation', $reservation);
    }

    public function confirmation(Reservation $reservation): Response|RedirectResponse
    {
        $this->authorize('view', $reservation);

        if ($reservation->status !== ReservationStatus::Confirmed) {
            return redirect()->route('reservations.show', $reservation);
        }

        return Inertia::render('reservations/Confirmation', [
            'reservation' => $this->reservationPayload($reservation),
        ]);
    }

    protected function createPendingReservation(
        User $user,
        int $cinemaSessionId,
        int $numberOfPlaces,
    ): Reservation {
        $unitPrice = (float) config('cinema.ticket_price');

        $session = CinemaSession::query()
            ->lockForUpdate()
            ->with('room')
            ->find($cinemaSessionId);

        if ($session === null) {
            throw ValidationException::withMessages([
                'cinema_session_id' => 'Cette séance n\'existe plus.',
            ]);
        }

        if ($session->starts_at->isPast()) {
            throw ValidationException::withMessages([
                'cinema_session_id' => 'Cette séance est passée et ne peut plus être réservée.',
            ]);
        }

        $remaining = $session->remainingSeats();

        if ($numberOfPlaces < 1) {
            throw ValidationException::withMessages([
                'number_of_places' => 'Le nombre de places demandé est invalide.',
            ]);
        }

        if ($numberOfPlaces > $remaining) {
            throw ValidationException::withMessages([
                'number_of_places' => 'Les places demandées ne sont plus disponibles. Il reste '
                    .$remaining.' place(s) pour cette séance.',
            ]);
        }

        $user->reservations()
            ->where('cinema_session_id', $session->id)
            ->where('status', ReservationStatus::Pending)
            ->delete();

        return Reservation::query()->create([
            'user_id' => $user->id,
            'cinema_session_id' => $session->id,
            'number_of_places' => $numberOfPlaces,
            'status' => ReservationStatus::Pending,
            'total_amount' => round($numberOfPlaces * $unitPrice, 2),
        ]);
    }

    protected function confirmReservationAfterSimulatedPayment(Reservation $reservation): void
    {
        $session = CinemaSession::query()
            ->lockForUpdate()
            ->with('room')
            ->find($reservation->cinema_session_id);

        if ($session === null) {
            throw ValidationException::withMessages([
                'payment' => 'Cette séance n\'existe plus.',
            ]);
        }

        $lockedReservation = Reservation::query()
            ->lockForUpdate()
            ->find($reservation->id);

        if ($lockedReservation === null || $lockedReservation->status !== ReservationStatus::Pending) {
            return;
        }

        if ($session->starts_at->isPast()) {
            throw ValidationException::withMessages([
                'payment' => 'Cette séance est passée.',
            ]);
        }

        $remaining = $session->remainingSeats();

        if ($lockedReservation->number_of_places > $remaining) {
            throw ValidationException::withMessages([
                'payment' => 'Une autre personne a réservé les dernières places entre-temps. '
                    .'Il reste '.$remaining.' place(s).',
            ]);
        }

        $lockedReservation->update(['status' => ReservationStatus::Confirmed]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function reservationPayload(Reservation $reservation): array
    {
        $reservation->load([
            'cinemaSession.film:id,title',
            'cinemaSession.room:id,name',
        ]);

        $session = $reservation->cinemaSession;

        return [
            'id' => $reservation->id,
            'status' => $reservation->status->value,
            'number_of_places' => $reservation->number_of_places,
            'total_amount' => (float) $reservation->total_amount,
            'session' => [
                'starts_at' => $session->starts_at->toIso8601String(),
                'ends_at' => $session->ends_at->toIso8601String(),
                'room_name' => $session->room->name,
                'film_title' => $session->film->title,
            ],
        ];
    }
}
