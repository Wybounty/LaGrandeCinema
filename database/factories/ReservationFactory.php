<?php

namespace Database\Factories;

use App\Models\CinemaSession;
use App\Models\Reservation;
use App\Models\User;
use App\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $places = fake()->numberBetween(1, 4);
        $unitPrice = (float) config('cinema.ticket_price');

        return [
            'user_id' => User::factory(),
            'cinema_session_id' => CinemaSession::factory(),
            'number_of_places' => $places,
            'status' => ReservationStatus::Confirmed,
            'total_amount' => round($places * $unitPrice, 2),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReservationStatus::Pending,
        ]);
    }
}
