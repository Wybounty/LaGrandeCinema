<?php

use App\Models\CinemaSession;
use App\Models\Film;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\ReservationStatus;
use Illuminate\Support\Carbon;

test('spectator dashboard has no statistics payload', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('statistics', null));
});

test('administrator sees statistics on dashboard', function () {
    $admin = User::factory()->administrateur()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('statistics.summary'));
});

test('statistics summary excludes cancelled reservations from confirmed metrics', function () {
    $admin = User::factory()->administrateur()->create();
    $session = CinemaSession::factory()->create([
        'starts_at' => now()->subDay(),
        'ends_at' => now()->subDay()->addHours(2),
    ]);

    Reservation::factory()->create([
        'cinema_session_id' => $session->id,
        'number_of_places' => 3,
        'status' => ReservationStatus::Confirmed,
        'total_amount' => 37.50,
        'created_at' => now(),
    ]);

    Reservation::factory()->create([
        'cinema_session_id' => $session->id,
        'number_of_places' => 2,
        'status' => ReservationStatus::Cancelled,
        'total_amount' => 25.00,
        'created_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard', ['period' => 'month']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('statistics.summary.reservations_total', 2)
            ->where('statistics.summary.reservations_confirmed', 1)
            ->where('statistics.summary.reservations_cancelled', 1)
            ->where('statistics.summary.places_confirmed', 3)
            ->where('statistics.summary.amount_confirmed', 37.50));
});

test('occupancy uses only past sessions and confirmed places', function () {
    $admin = User::factory()->administrateur()->create();
    $room = Room::factory()->create(['capacity' => 100]);
    $film = Film::factory()->create();

    $pastSession = CinemaSession::factory()->create([
        'film_id' => $film->id,
        'room_id' => $room->id,
        'starts_at' => Carbon::now()->subDays(2),
        'ends_at' => Carbon::now()->subDays(2)->addHours(2),
    ]);

    CinemaSession::factory()->create([
        'film_id' => $film->id,
        'room_id' => $room->id,
        'starts_at' => Carbon::now()->addDays(5),
        'ends_at' => Carbon::now()->addDays(5)->addHours(2),
    ]);

    Reservation::factory()->create([
        'cinema_session_id' => $pastSession->id,
        'number_of_places' => 25,
        'status' => ReservationStatus::Confirmed,
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard', ['period' => 'month']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('statistics.occupancy.global.reserved_places', 25)
            ->where('statistics.occupancy.global.total_capacity', 100)
            ->where('statistics.occupancy.global.rate', 25));
});

test('popular films are ranked by confirmed places', function () {
    $admin = User::factory()->administrateur()->create();
    $popular = Film::factory()->create(['title' => 'Film Star']);
    $other = Film::factory()->create(['title' => 'Film B']);

    $popularSession = CinemaSession::factory()->create([
        'film_id' => $popular->id,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->subDay()->addHours(2),
    ]);

    $otherSession = CinemaSession::factory()->create([
        'film_id' => $other->id,
        'starts_at' => now()->subDays(2),
        'ends_at' => now()->subDays(2)->addHours(2),
    ]);

    Reservation::factory()->create([
        'cinema_session_id' => $popularSession->id,
        'number_of_places' => 4,
        'status' => ReservationStatus::Confirmed,
    ]);

    Reservation::factory()->create([
        'cinema_session_id' => $otherSession->id,
        'number_of_places' => 1,
        'status' => ReservationStatus::Confirmed,
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard', ['period' => 'month']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('statistics.popularFilms', 2)
            ->where('statistics.popularFilms.0.title', 'Film Star')
            ->where('statistics.popularFilms.0.places_sum', 4));
});

test('period filter limits reservation totals', function () {
    $admin = User::factory()->administrateur()->create();

    Reservation::factory()->create([
        'created_at' => now()->subMonths(2),
        'status' => ReservationStatus::Confirmed,
    ]);

    Reservation::factory()->create([
        'created_at' => now(),
        'status' => ReservationStatus::Confirmed,
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard', ['period' => 'month']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('statistics.summary.reservations_total', 1));

    $this->actingAs($admin)
        ->get(route('dashboard', ['period' => 'year']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('statistics.summary.reservations_total', 2));
});
