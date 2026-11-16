<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use App\ReservationStatus;

class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function view(User $user, Reservation $reservation): bool
    {
        if ($user->canManageUsers()) {
            return true;
        }

        return $user->id === $reservation->user_id;
    }

    public function cancel(User $user, Reservation $reservation): bool
    {
        if (! $user->canManageUsers()) {
            return false;
        }

        return in_array($reservation->status, [
            ReservationStatus::Pending,
            ReservationStatus::Confirmed,
        ], true);
    }
}
