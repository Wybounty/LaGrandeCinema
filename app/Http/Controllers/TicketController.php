<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TicketController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $tickets = Ticket::query()
            ->whereHas('reservation', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with([
                'reservation.cinemaSession.film:id,title',
                'reservation.cinemaSession.room:id,name',
            ])
            ->orderByDesc('generated_at')
            ->get()
            ->map(fn (Ticket $ticket) => $this->ticketPayload($ticket));

        return Inertia::render('tickets/Index', [
            'tickets' => $tickets,
            'cinemaName' => config('services.cinema.name'),
        ]);
    }

    public function qrCode(Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        return response(
            Ticket::qrPng($ticket->qr_code),
            200,
            ['Content-Type' => 'image/png'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function ticketPayload(Ticket $ticket): array
    {
        $session = $ticket->reservation->cinemaSession;

        return [
            'id' => $ticket->id,
            'qr_code' => $ticket->qr_code,
            'place_number' => $ticket->place_number,
            'places_total' => $ticket->reservation->number_of_places,
            'generated_at' => $ticket->generated_at->toIso8601String(),
            'film_title' => $session->film->title,
            'starts_at' => $session->starts_at->toIso8601String(),
            'room_name' => $session->room->name,
            'qr_image_url' => route('tickets.qr', $ticket),
        ];
    }
}
