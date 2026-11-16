<?php

use App\Models\Film;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can view the films list', function () {
    Film::factory()->count(2)->create();

    $this->get(route('films.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('films/Index')
            ->has('films.data', 2),
        );
});

test('films list is empty when no films exist', function () {
    $this->get(route('films.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('films/Index')
            ->has('films.data', 0)
            ->where('films.total', 0),
        );
});

test('films list displays stored film data', function () {
    $film = Film::factory()->create([
        'title' => 'Film de test',
        'genre' => 'Comédie',
        'duration' => 105,
    ]);

    $this->get(route('films.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('films/Index')
            ->where('films.data.0.id', $film->id)
            ->where('films.data.0.title', 'Film de test')
            ->where('films.data.0.genre', 'Comédie')
            ->where('films.data.0.duration', 105)
            ->has('films.data.0.poster'),
        );
});

test('films list is paginated with eight films per page', function () {
    Film::factory()->count(10)->create();

    $this->get(route('films.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('films/Index')
            ->has('films.data', 8)
            ->where('films.per_page', 8)
            ->where('films.current_page', 1)
            ->where('films.last_page', 2),
        );

    $this->get(route('films.index', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('films.data', 2)
            ->where('films.current_page', 2),
        );
});

test('guests can view a film details page', function () {
    $film = Film::factory()->create([
        'title' => 'Film de test',
        'synopsis' => 'Un synopsis pour les tests.',
        'duration' => 105,
        'genre' => 'Comédie',
    ]);

    $this->get(route('films.show', $film))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('films/Show')
            ->where('film.id', $film->id)
            ->where('film.title', 'Film de test'),
        );
});

test('unknown film returns not found', function () {
    $this->get(route('films.show', ['film' => 99999]))->assertNotFound();
});

test('film without poster exposes null poster in list', function () {
    $film = Film::factory()->create(['poster' => null]);

    $this->get(route('films.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('films.data.0.id', $film->id)
            ->where('films.data.0.poster', null),
        );
});

test('films are persisted and retrieved from the database', function () {
    $film = Film::factory()->create();

    $this->assertDatabaseHas('films', [
        'id' => $film->id,
        'title' => $film->title,
    ]);
});
