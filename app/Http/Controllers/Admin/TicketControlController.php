<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TicketControlRequest;
use App\Models\Reservation;
use App\Models\Ticket;
use App\ReservationStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TicketControlController extends Controller
{
    public function index(): Response
    {
        $this->authorize('checkAtEntrance', Ticket::class);

        return Inertia::render('admin/ticket-control/Index');
    }

    public function verify(TicketControlRequest $request): JsonResponse
    {
        $this->authorize('checkAtEntrance', Ticket::class);

        $ticket = $this->findTicketByQrCode($request->validated('qr_code'));

        return response()->json($this->buildCheckResponse($ticket));
    }

    public function markUsed(TicketControlRequest $request): JsonResponse
    {
        $this->authorize('checkAtEntrance', Ticket::class);

        $qrCode = trim($request->validated('qr_code'));

        return DB::transaction(function () use ($qrCode) {
            $ticket = Ticket::query()
                ->where('qr_code', $qrCode)
                ->lockForUpdate()
                ->first();

            if ($ticket === null) {
                return response()->json($this->buildCheckResponse(null));
            }

            $reservation = Reservation::query()
                ->whereKey($ticket->reservation_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($ticket->status === 'cancelled' || $reservation->status === ReservationStatus::Cancelled) {
                return response()->json($this->buildCheckResponse($ticket->fresh()));
            }

            if ($ticket->status === 'used') {
                return response()->json($this->buildCheckResponse($ticket->fresh()));
            }

            if ($reservation->status !== ReservationStatus::Confirmed || $ticket->status !== 'valid') {
                return response()->json($this->buildCheckResponse($ticket->fresh()));
            }

            $ticket->update(['status' => 'used']);

            return response()->json([
                'success' => true,
                'outcome' => 'used',
                'message' => 'Entrée confirmée. Billet marqué comme utilisé.',
                'ticket' => $this->ticketPayload($ticket->fresh()),
            ]);
        });
    }

    protected function findTicketByQrCode(string $qrCode): ?Ticket
    {
        return Ticket::query()
            ->where('qr_code', trim($qrCode))
            ->first();
    }

    /**
     * @return array{outcome: string, message: string, ticket: array<string, mixed>|null}
     */
    protected function buildCheckResponse(?Ticket $ticket): array
    {
        if ($ticket === null) {
            return [
                'outcome' => 'invalid',
                'message' => 'Billet invalide. Refus d\'entrée.',
                'ticket' => null,
            ];
        }

        $ticket->loadMissing('reservation.cinemaSession.film', 'reservation.cinemaSession.room');
        $reservation = $ticket->reservation;

        if ($reservation === null) {
            return [
                'outcome' => 'invalid',
                'message' => 'Billet invalide. Refus d\'entrée.',
                'ticket' => $this->ticketPayload($ticket),
            ];
        }

        if ($ticket->status === 'cancelled' || $reservation->status === ReservationStatus::Cancelled) {
            return [
                'outcome' => 'cancelled',
                'message' => 'Billet annulé. Refus d\'entrée.',
                'ticket' => $this->ticketPayload($ticket),
            ];
        }

        if ($ticket->status === 'used') {
            return [
                'outcome' => 'used',
                'message' => 'Billet déjà utilisé. Ce billet a déjà été contrôlé.',
                'ticket' => $this->ticketPayload($ticket),
            ];
        }

        if ($reservation->status !== ReservationStatus::Confirmed) {
            return [
                'outcome' => 'invalid',
                'message' => 'Billet invalide. La réservation n\'est pas confirmée.',
                'ticket' => $this->ticketPayload($ticket),
            ];
        }

        if ($ticket->status !== 'valid') {
            return [
                'outcome' => 'invalid',
                'message' => 'Billet invalide. Refus d\'entrée.',
                'ticket' => $this->ticketPayload($ticket),
            ];
        }

        return [
            'outcome' => 'valid',
            'message' => 'Billet valide. Vous pouvez confirmer l\'entrée.',
            'ticket' => $this->ticketPayload($ticket),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function ticketPayload(Ticket $ticket): array
    {
        $ticket->loadMissing('reservation.cinemaSession.film', 'reservation.cinemaSession.room');

        $reservation = $ticket->reservation;
        if ($reservation === null) {
            return [
                'id' => $ticket->id,
                'qr_code' => $ticket->qr_code,
                'place_number' => $ticket->place_number,
                'status' => $ticket->status,
                'film_title' => '',
                'session_starts_at' => null,
                'session_ends_at' => null,
                'room_name' => '',
            ];
        }

        $session = $reservation->cinemaSession;

        return [
            'id' => $ticket->id,
            'qr_code' => $ticket->qr_code,
            'place_number' => $ticket->place_number,
            'status' => $ticket->status,
            'film_title' => $session->film->title,
            'session_starts_at' => $session->starts_at->toIso8601String(),
            'session_ends_at' => $session->ends_at->toIso8601String(),
            'room_name' => $session->room->name,
        ];
    }
}
