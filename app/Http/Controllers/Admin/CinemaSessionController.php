<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CinemaSessionRequest;
use App\Models\CinemaSession;
use App\Models\Film;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class CinemaSessionController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CinemaSession::class);

        $search = $request->string('search')->trim()->toString();
        $filmId = $request->integer('film_id');
        $roomId = $request->integer('room_id');
        $sessionDate = $request->string('session_date')->trim()->toString();

        $sessions = CinemaSession::query()
            ->with(['film:id,title,poster,duration', 'room:id,name'])
            ->withCount('reservations')
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHas('film', fn ($filmQuery) => $filmQuery->where('title', 'like', "%{$search}%"));
            })
            ->when($filmId > 0, fn ($query) => $query->where('film_id', $filmId))
            ->when($roomId > 0, fn ($query) => $query->where('room_id', $roomId))
            ->when($sessionDate !== '', function ($query) use ($sessionDate): void {
                $date = Carbon::parse($sessionDate)->toDateString();
                $query->whereDate('starts_at', $date);
            })
            ->orderBy('starts_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (CinemaSession $session): array => [
                'id' => $session->id,
                'starts_at' => $session->starts_at->toIso8601String(),
                'ends_at' => $session->ends_at->toIso8601String(),
                'film' => [
                    'id' => $session->film->id,
                    'title' => $session->film->title,
                    'poster' => $session->film->poster,
                ],
                'room' => [
                    'id' => $session->room->id,
                    'name' => $session->room->name,
                ],
                'can' => [
                    'delete' => $session->reservations_count === 0,
                ],
            ]);

        return Inertia::render('admin/sessions/Index', [
            'sessions' => $sessions,
            'filters' => [
                'search' => $search !== '' ? $search : null,
                'film_id' => $filmId > 0 ? $filmId : null,
                'room_id' => $roomId > 0 ? $roomId : null,
                'session_date' => $sessionDate !== '' ? $sessionDate : null,
            ],
            'films' => Film::query()->orderBy('title')->get(['id', 'title', 'duration']),
            'rooms' => Room::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', CinemaSession::class);

        return Inertia::render('admin/sessions/Create', [
            'films' => Film::query()->orderBy('title')->get(['id', 'title', 'duration']),
            'rooms' => Room::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(CinemaSessionRequest $request): RedirectResponse
    {
        CinemaSession::query()->create([
            'film_id' => $request->integer('film_id'),
            'room_id' => $request->integer('room_id'),
            'starts_at' => Carbon::parse($request->input('starts_at'))->seconds(0),
            'ends_at' => Carbon::parse($request->input('ends_at'))->seconds(0),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Séance programmée.',
        ]);

        return redirect()->route('admin.sessions.index');
    }

    public function edit(CinemaSession $cinemaSession): Response
    {
        $this->authorize('update', $cinemaSession);

        return Inertia::render('admin/sessions/Edit', [
            'session' => [
                'id' => $cinemaSession->id,
                'film_id' => $cinemaSession->film_id,
                'room_id' => $cinemaSession->room_id,
                'session_date' => $cinemaSession->starts_at->toDateString(),
                'start_time' => $cinemaSession->starts_at->format('H:i'),
                'end_time' => $cinemaSession->ends_at->format('H:i'),
            ],
            'films' => Film::query()->orderBy('title')->get(['id', 'title', 'duration']),
            'rooms' => Room::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(CinemaSessionRequest $request, CinemaSession $cinemaSession): RedirectResponse
    {
        $cinemaSession->update([
            'film_id' => $request->integer('film_id'),
            'room_id' => $request->integer('room_id'),
            'starts_at' => Carbon::parse($request->input('starts_at'))->seconds(0),
            'ends_at' => Carbon::parse($request->input('ends_at'))->seconds(0),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Séance mise à jour.',
        ]);

        return redirect()->route('admin.sessions.index');
    }

    public function destroy(CinemaSession $cinemaSession): RedirectResponse
    {
        $this->authorize('delete', $cinemaSession);

        if ($cinemaSession->reservations()->exists()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Impossible de supprimer cette séance : des réservations y sont associées.',
            ]);

            return redirect()->route('admin.sessions.index');
        }

        $cinemaSession->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Séance supprimée.',
        ]);

        return redirect()->route('admin.sessions.index');
    }
}
