<?php

use App\Models\CinemaSession;
use App\Models\Film;
use App\Models\Room;

test('a cinema session can be created with film and room', function () {
    $film = Film::factory()->create(['duration' => 110]);
    $room = Room::factory()->create();

    $session = CinemaSession::factory()->create([
        'film_id' => $film->id,
        'room_id' => $room->id,
    ]);

    expect($session->film->is($film))->toBeTrue()
        ->and($session->room->is($room))->toBeTrue()
        ->and($film->fresh()->cinemaSessions)->toHaveCount(1);
});
