<?php

use App\Models\CinemaSession;
use App\Models\CinemaSetting;
use App\Models\Film;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\ReservationStatus;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\StripePaymentHelpers;

beforeEach(fn () => StripePaymentHelpers::configureTestEnvironment());

test('film show exposes a reserve link for each upcoming session', function () {
    $film = Film::factory()->create();
    $room = Room::factory()->create(['capacity' => 12]);
    $startsAt = Carbon::now()->addDays(4)->setTime(18, 0);
    $session = CinemaSession::factory()->create([
        'film_id' => $film->id,
        'room_id' => $room->id,
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->copy()->addMinutes(100),
    ]);

    $this->get(route('films.show', $film))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('sessions', 1)
            ->where('sessions.0.id', $session->id)
            ->where('sessions.0.remaining_seats', 12),
        );
});

test('guest is sent to login before the reservation form', function () {
    $session = StripePaymentHelpers::makeBookableSession();

    $response = $this->get(route('reservations.create', $session));

    $response->assertRedirect(route('login'));
    expect(session('url.intended'))->toBe(route('reservations.create', $session));
});

test('authenticated user can create a pending reservation', function () {
    $user = User::factory()->create();
    $session = StripePaymentHelpers::makeBookableSession();
    CinemaSetting::query()->update(['ticket_price' => 10]);

    $response = $this->actingAs($user)->post(route('reservations.store'), [
        'cinema_session_id' => $session->id,
        'number_of_places' => 2,
    ]);

    $reservation = Reservation::query()->first();

    $response->assertRedirect(route('reservations.show', $reservation));

    expect($reservation)->not->toBeNull()
        ->and($reservation->user_id)->toBe($user->id)
        ->and($reservation->cinema_session_id)->toBe($session->id)
        ->and($reservation->number_of_places)->toBe(2)
        ->and($reservation->status)->toBe(ReservationStatus::Pending)
        ->and((float) $reservation->total_amount)->toBe(20.0);
});

test('reservation is refused when places exceed remaining capacity', function () {
    $user = User::factory()->create();
    $session = StripePaymentHelpers::makeBookableSession(capacity: 10);

    Reservation::factory()->create([
        'cinema_session_id' => $session->id,
        'number_of_places' => 9,
        'status' => ReservationStatus::Confirmed,
    ]);

    $this->actingAs($user)
        ->from(route('reservations.create', $session))
        ->post(route('reservations.store'), [
            'cinema_session_id' => $session->id,
            'number_of_places' => 2,
        ])
        ->assertSessionHasErrors('number_of_places');
});

test('stripe payment confirms reservation', function () {
    $user = User::factory()->create();
    $session = StripePaymentHelpers::makeBookableSession();

    $this->actingAs($user)->post(route('reservations.store'), [
        'cinema_session_id' => $session->id,
        'number_of_places' => 3,
    ]);

    $reservation = Reservation::query()->firstOrFail();
    StripePaymentHelpers::checkoutAndConfirmViaWebhook($user, $reservation);

    $this->actingAs($user)
        ->get(route('reservations.confirmation', $reservation))
        ->assertOk();

    expect($reservation->fresh()->status)->toBe(ReservationStatus::Confirmed);
});

test('reservation stays pending without stripe webhook', function () {
    $user = User::factory()->create();
    $session = StripePaymentHelpers::makeBookableSession();

    $this->actingAs($user)->post(route('reservations.store'), [
        'cinema_session_id' => $session->id,
        'number_of_places' => 2,
    ]);

    expect(Reservation::query()->firstOrFail()->status)->toBe(ReservationStatus::Pending);
});

test('past session cannot be reserved', function () {
    $user = User::factory()->create();
    $session = StripePaymentHelpers::makeBookableSession(startsAt: Carbon::now()->subDay());

    $this->actingAs($user)
        ->post(route('reservations.store'), [
            'cinema_session_id' => $session->id,
            'number_of_places' => 1,
        ])
        ->assertSessionHasErrors('cinema_session_id');
});

test('user cannot view another users reservation', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $reservation = Reservation::factory()->pending()->create(['user_id' => $owner->id]);

    $this->actingAs($intruder)
        ->get(route('reservations.show', $reservation))
        ->assertForbidden();
});

test('only one user can confirm when five seats remain and both want four', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $session = StripePaymentHelpers::makeBookableSession(capacity: 5);

    foreach ([$firstUser, $secondUser] as $user) {
        $this->actingAs($user)->post(route('reservations.store'), [
            'cinema_session_id' => $session->id,
            'number_of_places' => 4,
        ]);
    }

    $reservations = Reservation::query()->orderBy('id')->get();

    StripePaymentHelpers::checkoutAndConfirmViaWebhook($firstUser, $reservations[0]);

    test()->actingAs($secondUser)
        ->from(route('reservations.show', $reservations[1]))
        ->post(route('reservations.checkout', $reservations[1]))
        ->assertSessionHasErrors('payment');

    expect(Reservation::query()->where('status', ReservationStatus::Confirmed)->sum('number_of_places'))->toBe(4);
});

test('failed store does not create a reservation when the room is full', function () {
    $user = User::factory()->create();
    $session = StripePaymentHelpers::makeBookableSession(capacity: 2);

    Reservation::factory()->create([
        'cinema_session_id' => $session->id,
        'number_of_places' => 2,
        'status' => ReservationStatus::Confirmed,
    ]);

    $this->actingAs($user)
        ->from(route('reservations.create', $session))
        ->post(route('reservations.store'), [
            'cinema_session_id' => $session->id,
            'number_of_places' => 1,
        ])
        ->assertSessionHasErrors('number_of_places');

    expect(Reservation::query()->where('user_id', $user->id)->count())->toBe(0);
});

test('reservation create form is reachable for the selected session', function () {
    $user = User::factory()->create();
    $session = StripePaymentHelpers::makeBookableSession();

    $this->actingAs($user)
        ->get(route('reservations.create', $session))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('reservations/Create')
            ->where('session.id', $session->id)
            ->has('remainingSeats')
            ->has('unitPrice'),
        );
});
