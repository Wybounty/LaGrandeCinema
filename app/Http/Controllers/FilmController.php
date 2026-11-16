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
                ->orderBy('title')
                ->paginate(8)
                ->withQueryString(),
        ]);
    }

    public function show(Film $film): Response
    {
        return Inertia::render('films/Show', [
            'film' => $film,
        ]);
    }
}
