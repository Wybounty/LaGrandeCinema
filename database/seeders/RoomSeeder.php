<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            ['name' => 'Salle 1 — Grand Écran', 'capacity' => 280],
            ['name' => 'Salle 2 — Intimiste', 'capacity' => 120],
            ['name' => 'Salle 3 — Famille', 'capacity' => 180],
            ['name' => 'Salle 4 — Art et essai', 'capacity' => 95],
        ];

        foreach ($rooms as $room) {
            Room::query()->updateOrCreate(
                ['name' => $room['name']],
                ['capacity' => $room['capacity']],
            );
        }
    }
}
