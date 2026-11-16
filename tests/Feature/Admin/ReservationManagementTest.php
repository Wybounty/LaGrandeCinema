<?php

use App\Models\CinemaSession;
use App\Models\Film;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Ticket;
use App\Models\User;
use App\ReservationStatus;
use Illuminate\Support\Carbon;

test('spectator cannot access reservation management', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.reservations.index'))
        ->assertForbidden();

    $reservation = Reservation::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.reservations.show', $reservation))
        ->assertForbidden();
});

test('administrator can list reservations', function () {
    $admin = User::factory()->administrateur()->create();
    Reservation::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.reservations.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reservations/Index')
            ->has('reservations.data', 2));
});

test('administrator can filter reservations by status and reference', function () {
    $admin = User::factory()->administrateur()->create();
    $pending = Reservation::factory()->pending()->create();
    Reservation::factory()->create(['status' => ReservationStatus::Confirmed]);

    $this->actingAs($admin)
        ->get(route('admin.reservations.index', [
            'status' => 'pending',
            'reference' => $pending->reference(),
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('reservations.data', 1)
            ->where('reservations.data.0.id', $pending->id));

    $spectator = $pending->user;
    $spectator->update(['name' => 'Alice Martin']);

    $this->actingAs($admin)
        ->get(route('admin.reservations.index', ['search' => 'Alice']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('reservations.data', 1));
});

test('administrator can view reservation detail with tickets', function () {
    $admin = User::factory()->administrateur()->create();
    $reservation = Reservation::factory()->create();
    Ticket::factory()->create(['reservation_id' => $reservation->id]);

    $this->actingAs($admin)
        ->get(route('admin.reservations.show', $reservation))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reservations/Show')
            ->where('reservation.id', $reservation->id)
            ->has('reservation.tickets', 1));
});

test('administrator can cancel pending reservation', function () {
    $admin = User::factory()->administrateur()->create();
    $reservation = Reservation::factory()->pending()->create();

    $this->actingAs($admin)
        ->post(route('admin.reservations.cancel', $reservation))
        ->assertRedirect(route('admin.reservations.show', $reservation));

    expect($reservation->fresh()->status)->toBe(ReservationStatus::Cancelled);
});

test('cancelling confirmed reservation frees seats and keeps tickets', function () {
    $admin = User::factory()->administrateur()->create();
    $film = Film::factory()->create(['duration' => 100]);
    $room = Room::factory()->create(['capacity' => 10]);
    $startsAt = Carbon::now()->addDays(3);

    $session = CinemaSession::factory()->create([
        'film_id' => $film->id,
        'room_id' => $room->id,
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->copy()->addMinutes(100),
    ]);

    $reservation = Reservation::factory()->create([
        'cinema_session_id' => $session->id,
        'number_of_places' => 4,
        'status' => ReservationStatus::Confirmed,
    ]);

    foreach (range(1, 4) as $place) {
        Ticket::factory()->create([
            'reservation_id' => $reservation->id,
            'place_number' => $place,
        ]);
    }

    expect($session->remainingSeats())->toBe(6);

    $this->actingAs($admin)
        ->post(route('admin.reservations.cancel', $reservation))
        ->assertRedirect(route('admin.reservations.show', $reservation));

    $reservation->refresh();

    expect($reservation->status)->toBe(ReservationStatus::Cancelled)
        ->and($session->fresh()->remainingSeats())->toBe(10)
        ->and($reservation->tickets)->toHaveCount(4)
        ->and($reservation->tickets->every(fn (Ticket $ticket) => $ticket->status === 'cancelled'))->toBeTrue();
});

test('cannot cancel already cancelled reservation', function () {
    $admin = User::factory()->administrateur()->create();
    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::Cancelled,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.reservations.cancel', $reservation))
        ->assertForbidden();
});
