<?php

namespace App\Http\Controllers;

use App\Models\Film;
use Inertia\Inertia;
use Inertia\Response;

class FilmController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('films/Index', [
            'films' => Film::query()
                ->currentlyScheduled()
                ->orderBy('title')
                ->paginate(8)
                ->withQueryString(),
        ]);
    }

    public function show(Film $film): Response
    {
        $sessions = $film->cinemaSessions()
            ->with('room:id,name,capacity')
            ->upcoming()
            ->orderBy('starts_at')
            ->get()
            ->map(fn ($session): array => [
                'id' => $session->id,
                'starts_at' => $session->starts_at->toIso8601String(),
                'ends_at' => $session->ends_at->toIso8601String(),
                'room_name' => $session->room->name,
                'remaining_seats' => $session->remainingSeats(),
            ])
            ->all();

        return Inertia::render('films/Show', [
            'film' => $film,
            'sessions' => $sessions,
        ]);
    }
}
