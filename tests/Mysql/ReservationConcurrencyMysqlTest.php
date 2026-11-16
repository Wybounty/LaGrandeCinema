<?php

use App\Models\CinemaSession;
use App\Models\Film;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\ReservationStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    if (config('database.default') !== 'mysql') {
        $this->markTestSkipped(
            'Test de concurrence MySQL/InnoDB : lancer avec DB_CONNECTION=mysql (voir README).',
        );
    }
});

test('mysql transactions and lockForUpdate prevent overbooking on payment', function () {
    $film = Film::factory()->create(['duration' => 100]);
    $room = Room::factory()->create(['capacity' => 5]);
    $startsAt = Carbon::now()->addDays(7)->setTime(20, 0);
    $session = CinemaSession::factory()->create([
        'film_id' => $film->id,
        'room_id' => $room->id,
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->copy()->addMinutes(100),
    ]);

    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();

    foreach ([$firstUser, $secondUser] as $user) {
        $this->actingAs($user)->post(route('reservations.store'), [
            'cinema_session_id' => $session->id,
            'number_of_places' => 4,
        ]);
    }

    $reservations = Reservation::query()->orderBy('id')->get();

    DB::transaction(function () use ($reservations): void {
        $session = CinemaSession::query()->lockForUpdate()->findOrFail($reservations[0]->cinema_session_id);

        expect($session->remainingSeats())->toBe(5);
    });

    $this->actingAs($firstUser)
        ->post(route('reservations.pay', $reservations[0]), ['simulate_success' => true])
        ->assertRedirect();

    $this->actingAs($secondUser)
        ->from(route('reservations.show', $reservations[1]))
        ->post(route('reservations.pay', $reservations[1]), ['simulate_success' => true])
        ->assertSessionHasErrors('payment');

    expect(
        Reservation::query()
            ->where('cinema_session_id', $session->id)
            ->where('status', ReservationStatus::Confirmed)
            ->sum('number_of_places'),
    )->toBe(4);
});
