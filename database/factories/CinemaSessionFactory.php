<?php

namespace Database\Factories;

use App\Models\CinemaSession;
use App\Models\Film;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<CinemaSession>
 */
class CinemaSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = Carbon::parse(fake()->dateTimeBetween('+1 day', '+30 days'))->seconds(0);

        return [
            'film_id' => Film::factory(),
            'room_id' => Room::factory(),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes(120),
        ];
    }
}
