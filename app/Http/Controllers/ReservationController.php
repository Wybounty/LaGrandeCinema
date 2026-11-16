<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\CinemaSession;
use App\Models\CinemaSetting;
use App\Models\Reservation;
use App\Models\User;
use App\ReservationStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'unitPrice' => CinemaSetting::ticketPrice(),
        ]);
    }

    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $reservation = DB::transaction(function () use ($request): Reservation {
            return $this->createPendingReservation(
                $request->user(),
                $request->integer('cinema_session_id'),
                $request->integer('number_of_places'),
            );
        });

        return redirect()->route('reservations.show', $reservation);
    }

    public function show(Reservation $reservation): Response
    {
        $this->authorize('view', $reservation);

        return Inertia::render('reservations/Show', [
            'reservation' => $this->reservationPayload($reservation),
            'unitPrice' => CinemaSetting::ticketPrice(),
        ]);
    }

    public function pay(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->authorize('view', $reservation);

        $request->validate(['simulate_success' => ['required', 'boolean']]);

        if (! $request->boolean('simulate_success')) {
            return back()->withErrors([
                'payment' => 'Le paiement simulé a échoué. Aucune réservation confirmée.',
            ]);
        }

        if ($reservation->status !== ReservationStatus::Pending) {
            return redirect()->route('reservations.confirmation', $reservation);
        }

        DB::transaction(fn () => $this->confirmReservation($reservation));

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
        $unitPrice = CinemaSetting::ticketPrice();
        $session = $this->lockSession($cinemaSessionId, 'cinema_session_id');

        if ($session->starts_at->isPast()) {
            throw ValidationException::withMessages([
                'cinema_session_id' => 'Cette séance est passée et ne peut plus être réservée.',
            ]);
        }

        $remaining = $session->remainingSeats();

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

    protected function confirmReservation(Reservation $reservation): void
    {
        $session = $this->lockSession($reservation->cinema_session_id, 'payment');

        $reservation = Reservation::query()
            ->lockForUpdate()
            ->find($reservation->id);

        if ($reservation === null || $reservation->status !== ReservationStatus::Pending) {
            return;
        }

        if ($session->starts_at->isPast()) {
            throw ValidationException::withMessages([
                'payment' => 'Cette séance est passée.',
            ]);
        }

        $remaining = $session->remainingSeats();

        if ($reservation->number_of_places > $remaining) {
            throw ValidationException::withMessages([
                'payment' => 'Une autre personne a réservé les dernières places entre-temps. '
                    .'Il reste '.$remaining.' place(s).',
            ]);
        }

        $reservation->update(['status' => ReservationStatus::Confirmed]);
    }

    protected function lockSession(int $cinemaSessionId, string $errorField): CinemaSession
    {
        $session = CinemaSession::query()
            ->lockForUpdate()
            ->with('room')
            ->find($cinemaSessionId);

        if ($session === null) {
            throw ValidationException::withMessages([
                $errorField => 'Cette séance n\'existe plus.',
            ]);
        }

        return $session;
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
