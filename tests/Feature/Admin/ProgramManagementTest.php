<?php

use App\Models\CinemaSession;
use App\Models\Film;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('spectator cannot access program management', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.films.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('admin.rooms.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('admin.sessions.index'))
        ->assertForbidden();
});

test('administrator can create film with poster', function () {
    $admin = User::factory()->administrateur()->create();

    $this->actingAs($admin)
        ->post(route('admin.films.store'), [
            'title' => 'Nouveau film',
            'synopsis' => 'Un synopsis de test suffisamment long.',
            'duration' => 95,
            'genre' => 'Drame',
            'poster' => UploadedFile::fake()->image('affiche.jpg', 400, 600),
        ])
        ->assertRedirect(route('admin.films.index'));

    $film = Film::query()->where('title', 'Nouveau film')->first();

    expect($film)->not->toBeNull()
        ->and($film->poster)->not->toBeNull()
        ->and(Storage::disk('public')->exists((string) $film->poster))->toBeTrue();
});

test('film cannot be deleted when sessions exist', function () {
    $admin = User::factory()->administrateur()->create();
    $film = Film::factory()->create();
    CinemaSession::factory()->create(['film_id' => $film->id]);

    $this->actingAs($admin)
        ->delete(route('admin.films.destroy', $film))
        ->assertRedirect(route('admin.films.index'));

    expect($film->fresh())->not->toBeNull();
});

test('film without sessions can be deleted', function () {
    $admin = User::factory()->administrateur()->create();
    $film = Film::factory()->create(['poster' => 'posters/test.jpg']);
    Storage::disk('public')->put('posters/test.jpg', 'content');

    $this->actingAs($admin)
        ->delete(route('admin.films.destroy', $film))
        ->assertRedirect(route('admin.films.index'));

    expect(Film::query()->find($film->id))->toBeNull()
        ->and(Storage::disk('public')->exists('posters/test.jpg'))->toBeFalse();
});

test('film update keeps poster when none uploaded', function () {
    $admin = User::factory()->administrateur()->create();
    $film = Film::factory()->create([
        'title' => 'Avant',
        'poster' => 'posters/keep.jpg',
    ]);
    Storage::disk('public')->put('posters/keep.jpg', 'content');

    $this->actingAs($admin)
        ->patch(route('admin.films.update', $film), [
            'title' => 'Après',
            'synopsis' => $film->synopsis,
            'duration' => $film->duration,
            'genre' => $film->genre->value,
        ])
        ->assertRedirect(route('admin.films.index'));

    $film->refresh();

    expect($film->title)->toBe('Après')
        ->and($film->poster)->toBe('posters/keep.jpg');
});

test('room cannot be deleted when sessions exist', function () {
    $admin = User::factory()->administrateur()->create();
    $room = Room::factory()->create();
    CinemaSession::factory()->create(['room_id' => $room->id]);

    $this->actingAs($admin)
        ->delete(route('admin.rooms.destroy', $room))
        ->assertRedirect(route('admin.rooms.index'));

    expect($room->fresh())->not->toBeNull();
});

test('room without sessions can be deleted', function () {
    $admin = User::factory()->administrateur()->create();
    $room = Room::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.rooms.destroy', $room))
        ->assertRedirect(route('admin.rooms.index'));

    expect(Room::query()->find($room->id))->toBeNull();
});

test('session cannot be deleted when reservations exist', function () {
    $admin = User::factory()->administrateur()->create();
    $session = CinemaSession::factory()->create();
    Reservation::factory()->create(['cinema_session_id' => $session->id]);

    $this->actingAs($admin)
        ->delete(route('admin.sessions.destroy', $session))
        ->assertRedirect(route('admin.sessions.index'));

    expect($session->fresh())->not->toBeNull();
});

test('overlapping sessions in same room are rejected', function () {
    $admin = User::factory()->administrateur()->create();
    $film = Film::factory()->create(['duration' => 120]);
    $room = Room::factory()->create();

    $startsAt = Carbon::now()->addDays(5)->setTime(14, 0, 0);

    CinemaSession::factory()->create([
        'film_id' => $film->id,
        'room_id' => $room->id,
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->copy()->addMinutes(120),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.sessions.store'), [
            'film_id' => $film->id,
            'room_id' => $room->id,
            'session_date' => $startsAt->toDateString(),
            'start_time' => '15:00',
            'end_time' => '17:00',
        ])
        ->assertSessionHasErrors('room_id');
});

test('simultaneous sessions in different rooms are allowed', function () {
    $admin = User::factory()->administrateur()->create();
    $film = Film::factory()->create(['duration' => 120]);
    $roomA = Room::factory()->create();
    $roomB = Room::factory()->create();

    $startsAt = Carbon::now()->addDays(6)->setTime(14, 0, 0);

    CinemaSession::factory()->create([
        'film_id' => $film->id,
        'room_id' => $roomA->id,
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->copy()->addMinutes(120),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.sessions.store'), [
            'film_id' => $film->id,
            'room_id' => $roomB->id,
            'session_date' => $startsAt->toDateString(),
            'start_time' => '14:00',
            'end_time' => '16:00',
        ])
        ->assertRedirect(route('admin.sessions.index'));

    expect(CinemaSession::query()->where('room_id', $roomB->id)->count())->toBe(1);
});

test('session end time must match film duration', function () {
    $admin = User::factory()->administrateur()->create();
    $film = Film::factory()->create(['duration' => 100]);
    $room = Room::factory()->create();
    $date = Carbon::now()->addDays(7)->toDateString();

    $this->actingAs($admin)
        ->post(route('admin.sessions.store'), [
            'film_id' => $film->id,
            'room_id' => $room->id,
            'session_date' => $date,
            'start_time' => '18:00',
            'end_time' => '20:00',
        ])
        ->assertSessionHasErrors('ends_at');
});

test('invalid film genre is rejected', function () {
    $admin = User::factory()->administrateur()->create();

    $this->actingAs($admin)
        ->post(route('admin.films.store'), [
            'title' => 'Film invalide',
            'synopsis' => 'Synopsis suffisant pour la validation.',
            'duration' => 90,
            'genre' => 'Western',
            'poster' => UploadedFile::fake()->image('affiche.jpg'),
        ])
        ->assertSessionHasErrors('genre');
});

test('administrator can list films with filters', function () {
    $admin = User::factory()->administrateur()->create();
    Film::factory()->create(['title' => 'Alpha', 'genre' => 'Action']);
    Film::factory()->create(['title' => 'Beta', 'genre' => 'Comédie']);

    $this->actingAs($admin)
        ->get(route('admin.films.index', ['search' => 'Alpha']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/films/Index')
            ->has('films.data', 1)
            ->where('films.data.0.title', 'Alpha'));
});
