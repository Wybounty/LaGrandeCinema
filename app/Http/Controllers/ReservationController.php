<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\CinemaSession;
use App\Models\CinemaSetting;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use App\PaymentStatus;
use App\ReservationStatus;
use App\Support\CompletesPaidReservations;
use App\Support\StripeCheckout;
use App\Support\StripePaymentCompleter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ReservationController extends Controller
{
    use CompletesPaidReservations;

    public function __construct(
        private StripeCheckout $stripeCheckout,
    ) {}

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

    public function checkout(Request $request, Reservation $reservation): RedirectResponse|HttpResponse
    {
        $this->authorize('view', $reservation);

        if ($reservation->status !== ReservationStatus::Pending) {
            return redirect()->route('reservations.confirmation', $reservation);
        }

        $this->assertReservationAmountIsValid($reservation);

        $currency = strtolower((string) config('stripe.currency', 'cad'));

        $checkoutUrl = DB::transaction(function () use ($reservation, $currency): string {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->find($reservation->id);

            if ($reservation === null || $reservation->status !== ReservationStatus::Pending) {
                throw ValidationException::withMessages([
                    'payment' => 'Cette réservation n\'est plus en attente de paiement.',
                ]);
            }

            $session = $this->lockCinemaSession($reservation->cinema_session_id);

            if ($session->starts_at->isPast()) {
                throw ValidationException::withMessages([
                    'payment' => 'Cette séance est passée.',
                ]);
            }

            $remaining = $session->remainingSeats();

            if ($reservation->number_of_places > $remaining) {
                throw ValidationException::withMessages([
                    'payment' => 'Les places ne sont plus disponibles pour cette séance.',
                ]);
            }

            if ($reservation->payments()->where('status', PaymentStatus::Succeeded)->exists()) {
                throw ValidationException::withMessages([
                    'payment' => 'Cette réservation a déjà été payée.',
                ]);
            }

            $payment = Payment::query()->create([
                'reservation_id' => $reservation->id,
                'amount' => $reservation->amountInCents(),
                'currency' => $currency,
                'status' => PaymentStatus::Pending,
            ]);

            $stripeSession = $this->stripeCheckout->createSession($reservation, $payment);

            $payment->update([
                'stripe_checkout_session_id' => $stripeSession->id,
            ]);

            if ($stripeSession->url === null || $stripeSession->url === '') {
                throw ValidationException::withMessages([
                    'payment' => 'Impossible d\'ouvrir la page de paiement Stripe.',
                ]);
            }

            return $stripeSession->url;
        });

        if ($request->header('X-Inertia')) {
            return Inertia::location($checkoutUrl);
        }

        return redirect()->away($checkoutUrl);
    }

    public function paymentSuccess(
        Request $request,
        Reservation $reservation,
        StripePaymentCompleter $paymentCompleter,
    ): Response|RedirectResponse {
        $this->authorize('view', $reservation);

        if ($reservation->status === ReservationStatus::Pending) {
            $sessionId = $request->string('session_id')->toString();

            if ($sessionId !== '') {
                $paymentCompleter->syncReservationFromSessionId($reservation, $sessionId);
                $reservation->refresh();
            }
        }

        if ($reservation->status === ReservationStatus::Confirmed) {
            return redirect()->route('reservations.confirmation', [
                'reservation' => $reservation,
                'paid' => 1,
            ]);
        }

        return Inertia::render('reservations/PaymentSuccess', [
            'reservation' => $this->reservationPayload($reservation),
        ]);
    }

    public function paymentCancel(Reservation $reservation): Response
    {
        $this->authorize('view', $reservation);

        return Inertia::render('reservations/PaymentCancel', [
            'reservation' => $this->reservationPayload($reservation),
        ]);
    }

    public function confirmation(Request $request, Reservation $reservation): Response|RedirectResponse
    {
        $this->authorize('view', $reservation);

        if ($reservation->status !== ReservationStatus::Confirmed) {
            return redirect()->route('reservations.show', $reservation);
        }

        if ($request->boolean('paid')) {
            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Paiement confirmé. Vos billets vous ont été envoyés par e-mail et sont disponibles dans Mes billets.',
            ]);
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
        $session = $this->lockCinemaSession($cinemaSessionId);

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

    protected function assertReservationAmountIsValid(Reservation $reservation): void
    {
        $expected = round($reservation->number_of_places * CinemaSetting::ticketPrice(), 2);

        if ((float) $reservation->total_amount !== $expected) {
            throw ValidationException::withMessages([
                'payment' => 'Le montant de la réservation est invalide.',
            ]);
        }
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
