<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory(),
            'place_number' => 1,
            'qr_code' => (string) Str::uuid(),
            'status' => 'valid',
            'generated_at' => now(),
        ];
    }
}
