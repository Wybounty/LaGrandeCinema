<?php

namespace Database\Seeders;

use App\Models\CinemaSession;
use App\Models\Film;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CinemaSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $films = Film::query()->orderBy('id')->get();
        $rooms = Room::query()->orderBy('id')->get();

        if ($films->isEmpty() || $rooms->isEmpty()) {
            return;
        }

        $startsAt = Carbon::now()->addDays(3)->setTime(14, 0, 0);

        foreach ($films->take(12) as $index => $film) {
            $room = $rooms[$index % $rooms->count()];
            $slot = $startsAt->copy()
                ->addDays(intdiv($index, $rooms->count()))
                ->addHours(($index % 4) * 4);

            CinemaSession::query()->updateOrCreate(
                [
                    'room_id' => $room->id,
                    'starts_at' => $slot,
                ],
                [
                    'film_id' => $film->id,
                    'ends_at' => $slot->copy()->addMinutes($film->duration),
                ],
            );
        }
    }
}
