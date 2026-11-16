<?php

use App\Models\CinemaSession;
use App\Models\Film;
use App\Models\Room;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

function scheduleUpcomingSession(Film $film, ?Carbon $startsAt = null): CinemaSession
{
    $startsAt ??= Carbon::now()->addDays(3)->setTime(18, 0);

    return CinemaSession::factory()->create([
        'film_id' => $film->id,
        'room_id' => Room::factory(),
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->copy()->addMinutes($film->duration),
    ]);
}

test('films index only lists films with upcoming sessions', function () {
    $scheduled = Film::factory()->create(['title' => 'Programmé']);
    scheduleUpcomingSession($scheduled);
    Film::factory()->create(['title' => 'Sans séance']);

    $this->get(route('films.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('films.data', 1)
            ->where('films.data.0.title', 'Programmé'),
        );
});

test('films index is empty when no upcoming sessions exist', function () {
    Film::factory()->count(2)->create();

    $this->get(route('films.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('films.data', 0),
        );
});

test('film show displays upcoming sessions only', function () {
    $film = Film::factory()->create(['duration' => 100]);
    $session = scheduleUpcomingSession(
        $film,
        Carbon::parse('2030-06-15 19:30:00'),
    );

    CinemaSession::factory()->create([
        'film_id' => $film->id,
        'room_id' => Room::factory()->create()->id,
        'starts_at' => Carbon::now()->subDay(),
        'ends_at' => Carbon::now()->subDay()->addMinutes(100),
    ]);

    $this->get(route('films.show', $film))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('sessions', 1)
            ->where('sessions.0.id', $session->id)
            ->where('sessions.0.room_name', $session->room->name),
        );
});

test('film show without sessions displays an empty list', function () {
    $film = Film::factory()->create();

    $this->get(route('films.show', $film))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('sessions', 0));
});

test('unknown film returns not found', function () {
    $this->get(route('films.show', ['film' => 99999]))->assertNotFound();
});
