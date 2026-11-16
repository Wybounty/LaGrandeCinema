<?php

use App\Mail\ReservationTicketsMail;
use App\Models\CinemaSession;
use App\Models\Film;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

function makeBookableSessionForTickets(int $capacity = 50, ?Carbon $startsAt = null): CinemaSession
{
    $film = Film::factory()->create(['duration' => 100]);
    $room = Room::factory()->create(['capacity' => $capacity]);
    $startsAt ??= Carbon::now()->addDays(5)->setTime(19, 0);

    return CinemaSession::factory()->create([
        'film_id' => $film->id,
        'room_id' => $room->id,
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->copy()->addMinutes(100),
    ]);
}

function confirmReservationForUser(User $user, CinemaSession $session, int $places = 1): Reservation
{
    $user = $user->fresh() ?? $user;

    test()->actingAs($user)->post(route('reservations.store'), [
        'cinema_session_id' => $session->id,
        'number_of_places' => $places,
    ]);

    $reservation = Reservation::query()->where('user_id', $user->id)->latest('id')->firstOrFail();

    test()->actingAs($user)->post(route('reservations.pay', $reservation), [
        'simulate_success' => true,
    ]);

    return $reservation->fresh(['tickets']);
}

test('tickets are generated after reservation confirmation', function () {
    Mail::fake();

    $user = User::factory()->create();
    $session = makeBookableSessionForTickets();

    $reservation = confirmReservationForUser($user, $session, 1);

    expect($reservation->tickets)->toHaveCount(1)
        ->and($reservation->tickets->first()->status)->toBe('valid');
});

test('one ticket is created for each reserved place', function () {
    Mail::fake();

    $user = User::factory()->create();
    $session = makeBookableSessionForTickets();

    $reservation = confirmReservationForUser($user, $session, 3);

    expect($reservation->tickets)->toHaveCount(3)
        ->and($reservation->tickets->pluck('place_number')->sort()->values()->all())->toBe([1, 2, 3]);
});

test('qr codes are unique per ticket', function () {
    Mail::fake();

    $user = User::factory()->create();
    $session = makeBookableSessionForTickets();

    $reservation = confirmReservationForUser($user, $session, 3);

    $codes = $reservation->tickets->pluck('qr_code');

    expect($codes->unique())->toHaveCount(3);
});

test('confirmation does not duplicate tickets on retry', function () {
    Mail::fake();

    $user = User::factory()->create();
    $session = makeBookableSessionForTickets();
    $reservation = confirmReservationForUser($user, $session, 2);

    $this->actingAs($user)->post(route('reservations.pay', $reservation), [
        'simulate_success' => true,
    ]);

    expect(Ticket::query()->where('reservation_id', $reservation->id)->count())->toBe(2);
});

test('pending reservation has no tickets', function () {
    $user = User::factory()->create();
    $session = makeBookableSessionForTickets();

    $this->actingAs($user)->post(route('reservations.store'), [
        'cinema_session_id' => $session->id,
        'number_of_places' => 2,
    ]);

    expect(Ticket::query()->count())->toBe(0);
});

test('spectator can view their tickets on mes billets', function () {
    Mail::fake();

    $user = User::factory()->create();
    $session = makeBookableSessionForTickets();
    confirmReservationForUser($user, $session, 2);

    $this->actingAs($user)
        ->get(route('tickets.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tickets/Index')
            ->has('tickets', 2)
            ->missing('tickets.0.status'),
        );
});

test('non spectator cannot access mes billets', function () {
    $user = User::factory()->administrateur()->create();

    $this->actingAs($user)
        ->get(route('tickets.index'))
        ->assertForbidden();
});

test('user cannot view another users ticket qr image', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $session = makeBookableSessionForTickets();

    $reservation = confirmReservationForUser($owner, $session, 1);
    $ticket = $reservation->tickets->firstOrFail();

    $this->actingAs($intruder)
        ->get(route('tickets.qr', $ticket))
        ->assertForbidden();
});

test('confirmation sends tickets by email once', function () {
    Mail::fake();

    $user = User::factory()->create();
    $session = makeBookableSessionForTickets();
    $reservation = confirmReservationForUser($user, $session, 2);

    Mail::assertSent(ReservationTicketsMail::class, function (ReservationTicketsMail $mail) use ($user, $reservation) {
        return $mail->hasTo($user->email)
            && $mail->reservation->is($reservation)
            && count($mail->qrDataUris) === 2;
    });

    Mail::assertSentCount(1);

    $this->actingAs($user)->post(route('reservations.pay', $reservation), [
        'simulate_success' => true,
    ]);

    Mail::assertSentCount(1);
});

test('no email is sent when payment is not confirmed', function () {
    Mail::fake();

    $user = User::factory()->create();
    $session = makeBookableSessionForTickets();

    $this->actingAs($user)->post(route('reservations.store'), [
        'cinema_session_id' => $session->id,
        'number_of_places' => 1,
    ]);

    $reservation = Reservation::query()->firstOrFail();

    $this->actingAs($user)
        ->from(route('reservations.show', $reservation))
        ->post(route('reservations.pay', $reservation), ['simulate_success' => false]);

    Mail::assertNothingSent();
});

test('ticket qr route returns png for owner', function () {
    Mail::fake();

    $user = User::factory()->create();
    $session = makeBookableSessionForTickets();
    $reservation = confirmReservationForUser($user, $session, 1);
    $ticket = $reservation->tickets->firstOrFail();

    $this->actingAs($user)
        ->get(route('tickets.qr', $ticket))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');
});

test('administrator role is stored separately from spectator', function () {
    $admin = User::factory()->administrateur()->create();

    expect($admin->role)->toBe('administrateur');
});
