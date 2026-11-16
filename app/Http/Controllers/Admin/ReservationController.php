<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\ReservationStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Reservation::class);

        $search = $request->string('search')->trim()->toString();
        $reference = $request->string('reference')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $reservationDate = $request->string('reservation_date')->trim()->toString();

        $reservations = Reservation::query()
            ->with([
                'user:id,name,email',
                'cinemaSession.film:id,title',
                'cinemaSession.room:id,name',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHas('user', function ($userQuery) use ($search): void {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($reference !== '', function ($query) use ($reference): void {
                $id = Reservation::idFromReference($reference);

                if ($id !== null) {
                    $query->whereKey($id);
                } else {
                    $query->whereRaw('0 = 1');
                }
            })
            ->when(
                in_array($status, ['pending', 'confirmed', 'cancelled'], true),
                fn ($query) => $query->where('status', $status),
            )
            ->when($reservationDate !== '', fn ($query) => $query->whereDate('created_at', $reservationDate))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Reservation $reservation): array => $this->listRow($reservation));

        return Inertia::render('admin/reservations/Index', [
            'reservations' => $reservations,
            'filters' => [
                'search' => $search !== '' ? $search : null,
                'reference' => $reference !== '' ? $reference : null,
                'status' => in_array($status, ['pending', 'confirmed', 'cancelled'], true) ? $status : null,
                'reservation_date' => $reservationDate !== '' ? $reservationDate : null,
            ],
        ]);
    }

    public function show(Reservation $reservation): Response
    {
        $this->authorize('view', $reservation);

        $reservation->load([
            'user:id,name,email',
            'cinemaSession.film:id,title,duration',
            'cinemaSession.room:id,name',
            'tickets' => fn ($query) => $query->orderBy('place_number'),
        ]);

        return Inertia::render('admin/reservations/Show', [
            'reservation' => $this->detailPayload($reservation),
            'can' => [
                'cancel' => request()->user()?->can('cancel', $reservation) ?? false,
            ],
        ]);
    }

    public function cancel(Reservation $reservation): RedirectResponse
    {
        $this->authorize('cancel', $reservation);

        DB::transaction(function () use ($reservation): void {
            $locked = Reservation::query()
                ->lockForUpdate()
                ->find($reservation->id);

            if ($locked === null) {
                return;
            }

            if ($locked->status === ReservationStatus::Cancelled) {
                return;
            }

            if (! in_array($locked->status, [ReservationStatus::Pending, ReservationStatus::Confirmed], true)) {
                return;
            }

            $locked->update(['status' => ReservationStatus::Cancelled]);

            if ($locked->tickets()->exists()) {
                $locked->tickets()->update(['status' => 'cancelled']);
            }
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Réservation annulée.',
        ]);

        return redirect()->route('admin.reservations.show', $reservation);
    }

    /**
     * @return array<string, mixed>
     */
    protected function listRow(Reservation $reservation): array
    {
        $session = $reservation->cinemaSession;

        return [
            'id' => $reservation->id,
            'reference' => $reservation->reference(),
            'spectator_name' => $reservation->user->name,
            'spectator_email' => $reservation->user->email,
            'film_title' => $session->film->title,
            'session_starts_at' => $session->starts_at->toIso8601String(),
            'room_name' => $session->room->name,
            'number_of_places' => $reservation->number_of_places,
            'total_amount' => (float) $reservation->total_amount,
            'status' => $reservation->status->value,
            'created_at' => $reservation->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function detailPayload(Reservation $reservation): array
    {
        $session = $reservation->cinemaSession;

        return [
            'id' => $reservation->id,
            'reference' => $reservation->reference(),
            'status' => $reservation->status->value,
            'number_of_places' => $reservation->number_of_places,
            'total_amount' => (float) $reservation->total_amount,
            'created_at' => $reservation->created_at?->toIso8601String(),
            'spectator' => [
                'name' => $reservation->user->name,
                'email' => $reservation->user->email,
            ],
            'film' => [
                'title' => $session->film->title,
                'duration' => $session->film->duration,
            ],
            'session' => [
                'starts_at' => $session->starts_at->toIso8601String(),
                'ends_at' => $session->ends_at->toIso8601String(),
            ],
            'room_name' => $session->room->name,
            'tickets' => $reservation->tickets
                ->map(fn ($ticket): array => [
                    'id' => $ticket->id,
                    'place_number' => $ticket->place_number,
                    'status' => $ticket->status,
                    'generated_at' => $ticket->generated_at->toIso8601String(),
                ])
                ->all(),
        ];
    }
}
