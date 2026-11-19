<?php

namespace App\Http\Controllers\Admin;

use App\FilmGenre;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFilmRequest;
use App\Http\Requests\Admin\UpdateFilmRequest;
use App\Models\Film;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class FilmController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Film::class);

        $search = $request->string('search')->trim()->toString();
        $genre = $request->string('genre')->trim()->toString();

        $films = Film::query()
            ->withCount('cinemaSessions')
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($genre !== '', fn ($query) => $query->where('genre', $genre))
            ->orderBy('title')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Film $film): array => [
                'id' => $film->id,
                'title' => $film->title,
                'genre' => $film->genre->value,
                'duration' => $film->duration,
                'poster' => $film->poster,
                'cinema_sessions_count' => $film->cinema_sessions_count,
                'can' => [
                    'delete' => $film->cinema_sessions_count === 0,
                ],
            ]);

        return Inertia::render('admin/films/Index', [
            'films' => $films,
            'filters' => [
                'search' => $search !== '' ? $search : null,
                'genre' => $genre !== '' ? $genre : null,
            ],
            'genreOptions' => FilmGenre::values(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Film::class);

        return Inertia::render('admin/films/Create', [
            'genreOptions' => FilmGenre::values(),
        ]);
    }

    public function store(StoreFilmRequest $request): RedirectResponse
    {
        Film::query()->create([
            'title' => $request->string('title')->toString(),
            'synopsis' => $request->string('synopsis')->toString(),
            'duration' => $request->integer('duration'),
            'genre' => $request->string('genre')->toString(),
            'poster' => $this->storePoster($request->file('poster')),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Film créé.',
        ]);

        return redirect()->route('admin.films.index');
    }

    public function edit(Film $film): Response
    {
        $this->authorize('update', $film);

        return Inertia::render('admin/films/Edit', [
            'film' => [
                'id' => $film->id,
                'title' => $film->title,
                'synopsis' => $film->synopsis,
                'duration' => $film->duration,
                'genre' => $film->genre->value,
                'poster' => $film->poster,
            ],
            'genreOptions' => FilmGenre::values(),
        ]);
    }

    public function update(UpdateFilmRequest $request, Film $film): RedirectResponse
    {
        $data = [
            'title' => $request->string('title')->toString(),
            'synopsis' => $request->string('synopsis')->toString(),
            'duration' => $request->integer('duration'),
            'genre' => $request->string('genre')->toString(),
        ];

        if ($request->hasFile('poster')) {
            $this->deleteStoredPoster($film->poster);
            $data['poster'] = $this->storePoster($request->file('poster'));
        }

        $film->update($data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Film mis à jour.',
        ]);

        return redirect()->route('admin.films.index');
    }

    public function destroy(Film $film): RedirectResponse
    {
        $this->authorize('delete', $film);

        if ($film->cinemaSessions()->exists()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Impossible de supprimer ce film : des séances y sont rattachées.',
            ]);

            return redirect()->route('admin.films.index');
        }

        $this->deleteStoredPoster($film->poster);
        $film->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Film supprimé.',
        ]);

        return redirect()->route('admin.films.index');
    }

    protected function storePoster(UploadedFile $poster): string
    {
        $filename = Str::uuid()->toString().'.'.$poster->guessExtension();

        $path = Storage::disk('public')->putFileAs('posters', $poster, $filename);

        if ($path === false) {
            throw new \RuntimeException('Impossible d\'enregistrer l\'affiche du film.');
        }

        return $path;
    }

    protected function deleteStoredPoster(?string $poster): void
    {
        if ($poster === null || $poster === '') {
            return;
        }

        if (str_starts_with($poster, 'http://') || str_starts_with($poster, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($poster);
    }
}
