<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Reservation;
use App\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory()->pending(),
            'stripe_checkout_session_id' => 'cs_test_'.Str::random(24),
            'stripe_payment_intent_id' => 'pi_test_'.Str::random(24),
            'amount' => 2000,
            'currency' => 'cad',
            'status' => PaymentStatus::Pending,
            'paid_at' => null,
        ];
    }
}
